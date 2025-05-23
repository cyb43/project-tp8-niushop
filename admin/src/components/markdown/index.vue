<template>
    <div id="vditor" />
    <upload-attachment type="image" ref="imageRef" limit="" @confirm="imageSelect" />
    <upload-attachment type="video" ref="videoRef" @confirm="videoSelect" />
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import Vditor from 'vditor'
import 'vditor/dist/index.css'
import { img } from '@/utils/common'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    height: {
        type: Number,
        default: 600
    },
    mode: {
        type: String,
        default: 'wysiwyg'
    }
})

const emits = defineEmits(['update:modelValue', 'handleBlur'])

const content = computed({
    get() {
        return props.modelValue
    },
    set(value) {
        emits('update:modelValue', value)
    }
})

const imageRef: Record<string, any> | null = ref(null)
const videoRef: Record<string, any> | null = ref(null)
const vditor = ref<Vditor | null>(null)

onMounted(() => {
    vditor.value = new Vditor('vditor', {
        height: props.height,
        after: () => {
            vditor.value!.setValue(content.value)
        },
        mode: props.mode,
        toolbar: [
            'emoji', 'headings', 'bold', 'italic', 'strike', '|', 'line', 'quote', 'list', 'ordered-list', 'check', 'outdent', 'indent',
            {
                name: 'image',
                tip: '插入图片',
                icon: '<svg t="1743135560768" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="32640" width="200" height="200"><path d="M734.608696 155.826087a200.347826 200.347826 0 0 1 200.347826 200.347826v311.652174c0 32.122435-7.568696 62.464-20.992 89.35513L410.000696 461.401043a100.173913 100.173913 0 0 0-111.549218 6.811827L89.043478 628.357565V356.173913a200.347826 200.347826 0 0 1 200.347826-200.347826h445.217392zM376.208696 518.989913L874.362435 811.408696A199.68 199.68 0 0 1 734.608696 868.173913H289.391304c-96.478609 0-177.040696-68.185043-196.073739-159.009391l245.693218-187.881739a33.391304 33.391304 0 0 1 37.175652-2.29287zM289.391304 89.043478C141.868522 89.043478 22.26087 208.65113 22.26087 356.173913v311.652174c0 147.522783 119.607652 267.130435 267.130434 267.130435h445.217392c147.522783 0 267.130435-119.607652 267.130434-267.130435V356.173913c0-147.522783-119.607652-267.130435-267.130434-267.130435H289.391304z m445.217392 356.173913a89.043478 89.043478 0 1 0 0-178.086956 89.043478 89.043478 0 0 0 0 178.086956z" fill="#333333" p-id="32641"></path></svg>',
                click: () => {
                    imageRef.value.showDialog = true
                }
            },
            {
                name: 'video',
                tip: '插入视频',
                icon: '<svg t="1743136124938" class="icon" viewBox="0 0 1256 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="34055" width="200" height="200"><path d="M201.681455 74.472727a111.709091 111.709091 0 0 0-111.709091 111.709091v651.636364a111.709091 111.709091 0 0 0 111.709091 111.709091h837.818181a111.709091 111.709091 0 0 0 111.709091-111.709091V186.181818a111.709091 111.709091 0 0 0-111.709091-111.709091h-837.818181z m0-74.472727h837.818181a186.181818 186.181818 0 0 1 186.181819 186.181818v651.636364a186.181818 186.181818 0 0 1-186.181819 186.181818h-837.818181a186.181818 186.181818 0 0 1-186.181819-186.181818V186.181818a186.181818 186.181818 0 0 1 186.181819-186.181818zM798.72 533.085091a9.309091 9.309091 0 0 0-1.861818-13.032727l-248.226909-186.181819a9.309091 9.309091 0 0 0-14.894546 7.447273v372.363637a9.309091 9.309091 0 0 0 14.894546 7.447272l248.226909-186.181818a9.309091 9.309091 0 0 0 1.861818-1.861818z m-205.405091 247.621818a83.781818 83.781818 0 0 1-134.050909-67.025454v-372.363637a83.781818 83.781818 0 0 1 134.050909-67.025454l248.226909 186.181818a83.781818 83.781818 0 0 1 0 134.050909l-248.226909 186.181818z" fill="#000000" p-id="34056"></path></svg>',
                click: () => {
                    videoRef.value.showDialog = true
                }
            },
            'code', 'inline-code', 'insert-after', 'insert-before ', 'undo', 'redo', 'link', 'table', 'record', 'edit-mode', 'both', 'preview', 'fullscreen', 'outline', 'code-theme', 'content-theme', 'export', 'br'
        ],
        blur: () => {
            content.value = vditor.value!.getValue()
        }
    })
})

const imageSelect = (data: Record<string, any>) => {
    data.forEach((item: any) => {
        vditor.value.insertValue(`![描述](${img(item.url)})`)
    })
}

const videoSelect = (data: Record<string, any>) => {
    vditor.value.insertValue(`<video src="${img(data.url)}">`)
}
</script>

<style lang="scss" scoped></style>
