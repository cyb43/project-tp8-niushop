<template>
    <el-dialog v-model="dialogVisible" :title="t('accountSettings')" width="500">
        <el-form :model="saveInfo" label-width="90px" ref="formRef" class="page-form">
            <el-form-item :label="t('headImg')">
                <upload-image v-model="saveInfo.head_img" :limit="1" imageFit="cover" />
            </el-form-item>
            <el-form-item :label="t('userName')">
                <span>{{saveInfo.username}}</span>
            </el-form-item>
            <el-form-item :label="t('realName')">
                <el-input v-model.trim="saveInfo.real_name" :placeholder="t('realNamePlaceholder')" clearable class="input-width" />
            </el-form-item>
        </el-form>
        <template #footer>
        <div class="flex justify-end">
            <el-button type="primary" @click="submitForm(formRef)" :loading="loading">{{ t('save') }}</el-button>
            <el-button @click="dialogVisible = false">{{ t('cancel') }}</el-button>
        </div>
        </template>
    </el-dialog>
</template>
<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import type { FormInstance } from 'element-plus'
import { deepClone } from '@/utils/common'
import { getUserInfo, setUserInfo } from '@/app/api/personal'
import useUserStore from '@/stores/modules/user'

const userStore = useUserStore()
// 提交信息
const saveInfo: any = reactive({})
const formRef = ref<FormInstance>()
const loading = ref(true)
const dialogVisible = ref(false)
/**
 * 获取用户信息
 */
const getUserInfoFn = () => {
    getUserInfo().then(res => {
        loading.value = false
        const data = res.data
        saveInfo.head_img = data.head_img
        saveInfo.real_name = data.real_name
        saveInfo.username = data.username
    })
}
getUserInfoFn()
const open = ()=>{
    dialogVisible.value = true
    getUserInfoFn()
}
const submitForm = (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    formEl.validate((valid) => {
        if (valid) {
            loading.value = true
            setUserInfo(saveInfo).then((res: any) => {
                loading.value = false
                dialogVisible.value = false
                let data: any = deepClone(userStore.userInfo)
                data.head_img = saveInfo.head_img
                userStore.setUserInfo(data)
            }).catch(() => {
                loading.value = false
            })
        } else {
            return false
        }
    })
}
defineExpose({
    open
})
</script>
<style lang="scss" scoped>
</style>
