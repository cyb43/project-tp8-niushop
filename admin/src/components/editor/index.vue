<template>
    <upload-attachment type="image" ref="imageRef" limit="" @confirm="imageSelect" />
    <upload-attachment type="video" ref="videoRef" @confirm="videoSelect" />
    <vue-ueditor-wrap v-model="content" :config="editorConfig"
        :editorDependencies="['ueditor.config.js', 'ueditor.all.js']" ref="editorRef"
        @ready="handleEditorReady"></vue-ueditor-wrap>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { getToken, img } from '@/utils/common'
import { VueUeditorWrap } from 'vue-ueditor-wrap'
import storage from '@/utils/storage'

const editorRef = ref()

const prop = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    mode: {
        type: String,
        default: 'simple'
    },
    height: {
        type: Number,
        default: 600
    }
})

const emit = defineEmits(['update:modelValue', 'handleBlur'])

const imageRef: Record<string, any> | null = ref(null)
const videoRef: Record<string, any> | null = ref(null)

const content = computed({
    get() {
        return prop.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
})

let editorEl = null

const serverHeaders = {}
serverHeaders[import.meta.env.VITE_REQUEST_HEADER_TOKEN_KEY] = getToken()

const editorConfig = ref({
    debug: false,
    UEDITOR_HOME_URL: import.meta.env.MODE == 'development' ? '/public/ueditor/' : '/admin/ueditor/',
    UEDITOR_CORS_URL: import.meta.env.MODE == 'development' ? location.origin + '/ueditor/' : location.origin + '/admin/ueditor/',
    serverUrl: `${import.meta.env.VITE_APP_BASE_URL}sys/ueditor`,
    serverHeaders,
    // 编辑器不自动被内容撑高
    autoHeightEnabled: false,
    // 初始容器高度
    initialFrameHeight: prop.height,
    // 初始容器宽度
    initialFrameWidth: '100%',
    wordCount: true,
    toolbarCallback: function (cmd, editor) {
        editorEl = editor
        switch (cmd) {
            case 'insertimage':
                imageRef.value.showDialog = true
                return true
            case 'insertvideo':
                videoRef.value.showDialog = true
                return true
        }
    }
})
// 监听编辑器准备就绪事件
const handleEditorReady = (editor) => {
    // 方案一：通过内容变化事件手动统计（推荐）
    // editorInstance.addListener('contentChange', () => {
    //     const rawContent = editorInstance.getContent()
    //     // 过滤所有空白字符
    //     charCount.value = rawContent.replace(/\s/g, '').length
    //     // 同步到统计栏（需操作DOM）
    //     updateStatsDisplay(charCount.value)
    // })
    // console.log('扩展原型链', editor)
    editor.addListener('blur', () => {
        // console.log('失焦了')
        emit('handleBlur', editor.getContent()) // 把内容传出去
    })

    // 方案二：原型链扩展（如果编辑器版本支持）
    const originalCount = editor.getContentLength; // 原生统计方法

    // 覆盖方法：去除空格后统计
    editor.getContentLength = function () {
        const rawContent = editor.getContent();
        return rawContent.replace(/[\s\u3000]+/g, '').length;
    };
}

const imageSelect = (data: Record<string, any>) => {
    data.forEach((item: any) => {
        editorEl?.execCommand('insertHtml', `<img src="${img(item.url)}">`)
    })
}

const videoSelect = (data: Record<string, any>) => {
    editorEl?.execCommand('insertHtml', `<video src="${img(data.url)}">`)
}
</script>

<style lang="scss" scoped></style>
