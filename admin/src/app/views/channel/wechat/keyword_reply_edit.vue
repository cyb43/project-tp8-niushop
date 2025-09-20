<template>
    <!--关键字回复添加/编辑-->
    <div class="main-container">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <el-form class="page-form mt-[15px]" :model="formData" label-width="150px" ref="formRef" :rules="formRules" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">

                <el-form-item :label="t('ruleName')" prop="name">
                    <el-input v-model.trim="formData.name" :placeholder="t('ruleNamePlaceholder')" class="input-width" clearable maxlength="60"/>
                    <div class="form-tip">{{ t('ruleNameTips') }}</div>
                </el-form-item>

                <el-form-item :label="t('keyword')" prop="keyword">
                    <el-input v-model.trim="formData.keyword" :placeholder="t('keywordPlaceholder')" class="input-width" clearable >
                        <template #prepend>
                            <el-select v-model="formData.matching_type" style="width: 115px">
                                <el-option :label="t('allMatching')" value="full" />
                                <el-option :label="t('fuzzyMatching')" value="like" />
                            </el-select>
                        </template>
                    </el-input>
                </el-form-item>

                <el-form-item :label="t('content')" prop="content">
                    <div class="flex flex-col">
                        <div class="flex items-center" v-for="(item, index) in formData.content">
                            <div class="w-[300px] bg-page p-[10px] mr-[10px] mb-[10px] rounded leading-none" v-if="item.msgtype == 'text'">
                                {{ item.text.content }}
                            </div>
                            <div class="w-[300px] bg-page p-[10px] mr-[10px] mb-[10px] rounded" v-if="item.msgtype == 'image'">
                                <upload-image :limit="1" width="120px" height="120px" v-model="item.image.url"/>
                            </div>
                            <div class="w-[300px] bg-page p-[10px] mr-[10px] mb-[10px] rounded" v-if="item.msgtype == 'video'">
                                <upload-video :limit="1" width="120px" height="120px" v-model="item.video.url"/>
                            </div>
                            <div class="w-[300px] bg-page p-[10px] mr-[10px] mb-[10px] rounded" v-if="item.msgtype == 'mpnewsarticle'">
                                <news-card v-model="item.mpnewsarticle" mode="show"/>
                            </div>
                            <div class="w-[300px] bg-page p-[10px] mr-[10px] mb-[10px] rounded" v-if="item.msgtype == 'miniprogrampage'">
                                小程序卡片【{{ item.miniprogrampage.appid }}】
                            </div>
                            <icon name="element Delete" class="cursor-pointer" @click="removeContent(index)"/>
                        </div>
                        <div class="mt-[10px]">
                            <el-button type="primary" @click="showDialog = true">{{ t('addReplyContent') }}</el-button>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('replyMethod')" prop="reply_method">
                    <el-radio-group v-model="formData.reply_method">
                        <el-radio label="all">{{ t('replyMethodAll') }}</el-radio>
                        <el-radio label="rand">{{ t('replyMethodRand') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-card>
        </el-form>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" :loading="loading" @click="save(formRef)">{{ t('save') }}</el-button>
            </div>
        </div>

        <el-dialog v-model="showDialog" :title="t('addReplyContent')" width="60%" :destroy-on-close="true">
            <reply-form v-model="replyContent" ref="ReplyRef"/>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="addReplyContent">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getKeywordsReplyInfo, editKeywordsReply, addKeywordsReply } from '@/app/api/wechat'
import { FormInstance, FormRules } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRoute, useRouter } from 'vue-router'
import ReplyForm from '@/app/views/channel/wechat/components/reply-form.vue'
import NewsCard from '@/app/views/channel/wechat/components/news-card.vue'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const back = () => {
    router.push('/channel/wechat/reply')
}

const showDialog = ref(false)

const formData: any = reactive({
    id: 0,
    name: '',
    keyword: '',
    content: [],
    matching_type: 'full',
    reply_method: 'all'
})

const replyContent = ref({})
const ReplyRef = ref(null)

const addReplyContent = () => {
    ReplyRef.value?.verify().then(res => {
        if (res) {
            formData.content.push(replyContent.value)
            replyContent.value = {}
            showDialog.value = false
        }
    })
}

const removeContent = (index: number) => {
    formData.content.splice(index, 1)
}

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({
    name: [
        { required: true, message: t('ruleNamePlaceholder'), trigger: 'blur' }
    ],
    keyword: [
        { required: true, message: t('keywordPlaceholder'), trigger: 'blur' }
    ],
    content: [
        {
            validator: (rule: any, value: any, callback: any) => {
                if (!formData.content.length) callback(new Error(t('contentPlaceholder')))
                callback()
            },
            trigger: 'blur'
        }
    ]
})

const loading = ref(false)

if (route.query.id) {
    getKeywordsReplyInfo(route.query.id).then(({ data }) => {
        Object.keys(formData).forEach((key: string) => {
            if (data[key] != undefined) formData[key] = data[key]
        })
        loading.value = false
    }).catch()
} else {
    loading.value = false
}

/**
 * 保存
 */
const save = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            const api = formData.id ? editKeywordsReply : addKeywordsReply
            loading.value = true
            api(formData).then(() => {
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

</script>
