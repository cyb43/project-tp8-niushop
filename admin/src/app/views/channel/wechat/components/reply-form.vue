<template>
    <div class="border border-br-light rounded">
        <div class="py-[10px] px-[30px] flex text-sm border-0 border-b border-br-light text-tx-regular">
            <div class="pr-[25px] cursor-pointer flex items-center" :class="{'text-primary': formData.msgtype == 'text'}" @click="switchMsgType('text')">
                <icon name="iconfont iconxingzhuang-wenzi" size="18" class="mr-[5px]"/>
                文本
            </div>
            <div class="pr-[25px] cursor-pointer flex items-center" :class="{'text-primary': formData.msgtype == 'image'}" @click="switchMsgType('image')">
                <icon name="iconfont icontupian" size="18px" class="mr-[5px]"/>
                图片
            </div>
            <div class="pr-[25px] cursor-pointer flex items-center" :class="{'text-primary': formData.msgtype == 'video'}" @click="switchMsgType('video')">
                <icon name="iconfont iconshipin1" size="18" class="mr-[5px]"/>
                视频
            </div>
            <div class="pr-[25px] cursor-pointer flex items-center" :class="{'text-primary': formData.msgtype == 'mpnewsarticle'}" @click="switchMsgType('mpnewsarticle')">
                <icon name="iconfont icontuwendaohang2" size="13px" class="mr-[5px]"/>
                图文
            </div>
            <div class="pr-[25px] cursor-pointer flex items-center" :class="{'text-primary': formData.msgtype == 'miniprogrampage'}" @click="switchMsgType('miniprogrampage')">
                <icon name="iconfont iconxiaochengxu" size="14px" class="mr-[5px]"/>
                小程序卡片
            </div>
        </div>
        <div class="py-[20px] px-[30px] h-[350px]">
            <div v-if="formData.msgtype == 'text'">
                <el-input v-model.trim="formData.text.content" :rows="5" type="textarea" placeholder="" maxlength="600" :show-word-limit="true" resize="none" input-style="box-shadow: none;height:300px" />
            </div>
            <div v-if="formData.msgtype == 'image'" class="flex w-full h-full justify-center items-center image-media">
                <div class="w-full h-full" v-if="formData.image.url">
                    <upload-image :limit="1" width="150px" height="150px" v-model="formData.image.url"/>
                </div>
                <div v-else class="flex w-full h-full justify-center items-center image-media">
                    <div class="flex flex-1 h-full border border-br-light cursor-pointer select-media">
                        <select-wechat-media type="image" @success="setImageMedia">
                            <div class="flex items-center justify-center flex-col">
                                <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                                <div class="leading-none text-xs mt-[10px] text-secondary">从素材库选择</div>
                            </div>
                        </select-wechat-media>
                    </div>
                    <div class="flex flex-1 h-full ml-[20px] border border-br-light cursor-pointer">
                        <upload-media type="image" class="w-full h-full flex items-center justify-center" @success="setImageMedia">
                            <div class="flex items-center justify-center flex-col">
                                <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                                <div class="leading-none text-xs mt-[10px] text-secondary">上传图片</div>
                            </div>
                        </upload-media>
                    </div>
                </div>
            </div>
            <div v-if="formData.msgtype == 'video'" class="flex w-full h-full justify-center items-center video-media">
                <div class="w-full h-full" v-if="formData.video.url">
                    <upload-video :limit="1" width="150px" height="150px" v-model="formData.video.url"/>
                </div>
                <div v-else class="flex w-full h-full justify-center items-center video-media">
                    <div class="flex flex-1 h-full border border-br-light cursor-pointer select-media">
                        <select-wechat-media type="video" @success="setVideoMedia">
                            <div class="flex items-center justify-center flex-col">
                                <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                                <div class="leading-none text-xs mt-[10px] text-secondary">从素材库选择</div>
                            </div>
                        </select-wechat-media>
                    </div>
                    <div class="flex flex-1 h-full ml-[20px] border border-br-light cursor-pointer">
                        <upload-media type="video" class="w-full h-full flex items-center justify-center" @success="setVideoMedia">
                            <div class="flex items-center justify-center flex-col">
                                <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                                <div class="leading-none text-xs mt-[10px] text-secondary">上传视频</div>
                            </div>
                        </upload-media>
                    </div>
                </div>
            </div>
            <div v-if="formData.msgtype == 'mpnewsarticle'" class="flex w-full h-full justify-center items-center image-media">
                <div class="w-full h-full" v-if="formData.mpnewsarticle">
                    <news-card v-model="formData.mpnewsarticle"/>
                </div>
                <div v-else class="flex w-full h-full justify-center items-center image-media">
                    <div class="flex flex-1 h-full border border-br-light cursor-pointer select-media">
                        <select-wechat-media type="news" @success="setNewsMedia">
                            <div class="flex items-center justify-center flex-col">
                                <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                                <div class="leading-none text-xs mt-[10px] text-secondary">从素材库选择</div>
                            </div>
                        </select-wechat-media>
                    </div>
                </div>
            </div>
            <div v-if="formData.msgtype == 'miniprogrampage'">
                <el-form :model="formData.miniprogrampage" label-width="140px" class="page-form" ref="formRef" :rules="formRules">
                    <el-form-item label="小程序APPID" prop="appid">
                        <el-input v-model.trim="formData.miniprogrampage.appid" class="input-width"/>
                        <div class="form-tip">小程序需已经与公众号关联</div>
                    </el-form-item>
                    <el-form-item label="小程序卡片标题" prop="title">
                        <el-input v-model.trim="formData.miniprogrampage.title" class="input-width"/>
                    </el-form-item>
                    <el-form-item label="小程序的页面路径" prop="pagepath">
                        <el-input v-model.trim="formData.miniprogrampage.pagepath" class="input-width"/>
                    </el-form-item>
                    <el-form-item label="小程序卡片图片" prop="thumb_media_url">
                        <upload-image :limit="1" width="100px" height="100px" v-model="formData.miniprogrampage.thumb_media_url" v-if="formData.miniprogrampage.thumb_media_url"/>
                        <select-wechat-media type="image" @success="setWeappImageMedia" v-else>
                            <div class="rounded cursor-pointer overflow-hidden relative border border-solid border-color image-wrap mr-[10px] w-[100px] h-[100px]">
                                <div class="w-full h-full flex items-center justify-center flex-col content-wrap">
                                    <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                                    <div class="leading-none text-xs mt-[10px] text-secondary">{{ t('upload.root') }}</div>
                                </div>
                            </div>
                        </select-wechat-media>
                        <div class="form-tip">小程序卡片图片建议大小为520*416</div>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { computed, reactive, ref, watch } from 'vue'
import { t } from '@/lang'
import UploadMedia from '@/app/views/channel/wechat/components/upload-media.vue'
import SelectWechatMedia from '@/app/views/channel/wechat/components/select-wechat-media.vue'
import Test from '@/utils/test'
import { ElMessage, FormInstance, FormRules } from 'element-plus'
import NewsCard from '@/app/views/channel/wechat/components/news-card.vue'

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => {
            return {}
        }
    }
})
const emit = defineEmits(['update:modelValue'])

const formData = ref({
    msgtype: 'text',
    text: {
        content: ''
    },
    image: {
        media_id: '',
        url: ''
    },
    video: {
        media_id: '',
        url: ''
    },
    miniprogrampage: {
        appid: '',
        title: '',
        pagepath: '',
        thumb_media_url: '',
        thumb_media_id: ''
    },
    mpnewsarticle: null
})

const value = computed({
    get () {
        return props.modelValue
    },
    set (value) {
        emit('update:modelValue', value)
    }
})

watch(() => value.value, (nval, oval) => {
    if ((!oval || !Object.keys(oval).length) && Object.keys(nval).length) {
        formData.value = value.value
    }
}, { immediate: true })

watch(() => formData.value, () => {
    value.value = formData.value
}, { deep: true })

const switchMsgType = (type: string) => {
    formData.value.msgtype = type
}

const setImageMedia = (data: any) => {
    formData.value.image.media_id = data.media_id
    formData.value.image.url = data.value
}

const setVideoMedia = (data: any) => {
    formData.value.video.media_id = data.media_id
    formData.value.video.url = data.value
}

const setWeappImageMedia = (data: any) => {
    formData.value.miniprogrampage.thumb_media_id = data.media_id
    formData.value.miniprogrampage.thumb_media_url = data.value
}

const setNewsMedia = (data: any) => {
    formData.value.mpnewsarticle = {
        article_id: data.media_id,
        value: data.value
    }
}

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({
    appid: [
        { required: true, message: '请填写小程序appid', trigger: 'blur' }
    ],
    title: [
        { required: true, message: '请填写小程序卡片标题', trigger: 'blur' }
    ],
    pagepath: [
        { required: true, message: '请填写小程序卡片跳转页面', trigger: 'blur' }
    ],
    thumb_media_url: [
        { required: true, message: '请上传小程序卡片封面', trigger: 'blur' }
    ]
})

/**
 * 验证数据
 */
const verify = async () => {
    let verify = true
    switch (formData.value.msgtype) {
        case 'text':
            if (Test.empty(formData.value.text.content)) {
                ElMessage({ message: '请输入回复内容', type: 'warning' })
                verify = false
            }
            break
        case 'image':
            if (Test.empty(formData.value.image.url)) {
                ElMessage({ message: '请上传回复图片', type: 'warning' })
                verify = false
            }
            break
        case 'video':
            if (Test.empty(formData.value.video.url)) {
                ElMessage({ message: '请上传回复视频', type: 'warning' })
                verify = false
            }
            break
        case 'miniprogrampage':
            await formRef.value.validate(async (valid) => {
                verify = valid
            })
            break
        case 'mpnewsarticle':
            if (Test.empty(formData.value.mpnewsarticle)) {
                ElMessage({ message: '请选择图文', type: 'warning' })
                verify = false
            }
            break
    }
    return verify
}

defineExpose({
    verify
})
</script>

<style lang="scss" scoped>
:deep(.image-media, .video-media) {
    .el-upload {
        width: 100%;
        height: 100%;
    }
}
:deep(.select-media) {
    & > div {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
}
</style>
