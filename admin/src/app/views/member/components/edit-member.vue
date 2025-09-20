<template>
    <el-dialog v-model="showDialog" :title="title || t('updateMember')" width="500px" :destroy-on-close="true">

        <el-form :model="saveData" label-width="90px" :rules="formRules" ref="formRef" class="page-form" @submit.prevent v-loading="loading">
            <el-form-item :label="t('headimg')" v-if="type == 'headimg'">
                <upload-image v-model="saveData.headimg" />
            </el-form-item>
            <el-form-item :label="t('nickname')" v-if="type == 'nickname'">
                <el-input v-model.trim="saveData.nickname" clearable :placeholder="t('nickNamePlaceholder')" class="input-width" />
            </el-form-item>
            <el-form-item :label="t('mobile')" v-if="type == 'mobile'" prop="mobile">
                <el-input v-model.trim="saveData.mobile" clearable :placeholder="t('mobilePlaceholder')" maxlength="11" class="input-width" />
            </el-form-item>
            <el-form-item :label="t('idCard')" v-if="type == 'id_card'" prop="id_card">
                <el-input v-model.trim="saveData.id_card" clearable :placeholder="t('idCardPlaceholder')" maxlength="18" class="input-width" />
            </el-form-item>
            <el-form-item :label="t('remark')" v-if="type == 'remark'" prop="remark">
                <el-input v-model.trim="saveData.remark"  type="textarea" clearable :placeholder="t('remarkPlaceholder')" maxlength="100" :rows="5" class="input-width" />
            </el-form-item>
            <el-form-item :label="t('birthday')" v-if="type == 'birthday'">
                <el-date-picker v-model="saveData.birthday" value-format="YYYY-MM-DD" type="date" :placeholder="t('birthdayTip')" />
            </el-form-item>
            <el-form-item :label="t('sex')" v-if="type == 'sex'">
                <el-select v-model="saveData.sex" clearable :placeholder="t('sexPlaceholder')" class="input-width">
                    <el-option :label="item['name']" :value="item['id']" v-for="(item,index) in sexSelectData" :key="index" />
                </el-select> 
            </el-form-item>
            <el-form-item :label="t('memberLabel')" v-if="type == 'member_label'">
                <el-select v-model="saveData.member_label" multiple collapse-tags :placeholder="t('memberLabelPlaceholder')" class="input-width">
                    <el-option :label="item['label_name']" :value="item['label_id']" v-for="(item,index) in labelSelectData"  :key="index"/>
                </el-select>
            </el-form-item>
            <div v-if="type == 'member_level'">
                <el-form-item :label="t('memberLevelUpdate')" prop="member_level">
                    <el-select v-model="saveData.member_level" :placeholder="t('memberLevelPlaceholder')"  clearable class="input-width">
                        <!-- <el-option :label="t('memberLevelPlaceholder')" :value="0" /> -->
                        <el-option :label="item['level_name']" :value="item['level_id']" v-for="(item,index) in levelSelectData"  :key="index"/>
                    </el-select>
                    <div class="text-sm text-gray-400">{{ t('memberLevelUpdateTips') }}</div>
                </el-form-item>
            </div>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loading" v-if="method=='batchSet'" @click="batchSetConfirm(formRef)">{{t('confirm')}}</el-button>
                <el-button type="primary" :loading="loading" v-else @click="confirm(formRef)">{{t('confirm')}}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import { deepClone } from '@/utils/common'
import type { FormInstance } from 'element-plus'
import { editMemberDetail, getMemberLabelAll, getMemberLevelAll, memberBatchModify } from '@/app/api/member'
import Test from '@/utils/test'

// 修改类型
const type = ref('')
const title = ref('')
// 会员id
const memberId = ref('')
const showDialog = ref(false)
const loading = ref(false)
const repeat = ref(false)
const formRef = ref(null)

const sexSelectData = ref([
    {
        id: 0,
        name: t('secrecySex')
    },
    {
        id: 1,
        name: t('manSex')
    },
    {
        id: 2,
        name: t('girlSex')
    }
])
const labelSelectData:any = ref(null)
// 获取全部标签
const getMemberLabelAllFn = async () => {
    labelSelectData.value = await (await getMemberLabelAll()).data
}
getMemberLabelAllFn()

const levelSelectData = ref([])
getMemberLevelAll().then(({ data }) => {
    levelSelectData.value = data
})

const formRules = computed(() => {
    return {
        mobile: [
            {
                validator (rule, value, callback) {
                // 允许为空，直接通过验证
                    if (!value) return callback()

                    // 非空值时，验证手机号格式
                    const reg = /^1[3-9]\d{9}$/
                    if (!reg.test(value)) {
                        callback(new Error('请输入正确的手机号'))
                    } else {
                        callback()
                    }
                },
                trigger: 'blur'
            }
        ],
        id_card: [
            {
                validator (rule, value, callback) {
                // 允许为空，直接通过验证
                    if (!value) return callback()

                    // 非空值时，验证身份证号格式
                    const reg = /^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/
                    if (!reg.test(value)) {
                        callback(new Error('请输入正确的身份证号'))
                    } else {
                        callback()
                    }
                },
                trigger: 'blur'
            }
        ]
        // member_level: [
        //     {
        //         validator: (rule: any, value: any, callback: Function) => {
        //             if (Test.empty(saveData.member_level)) {
        //                 callback(t('memberLevelPlaceholder'))
        //             }
        //             callback()
        //         }
        //     }
        // ]
    }
})

/**
 * 表单数据
 */
const initialFormData = {
    headimg: '',
    nickname: '',
    member_label: '',
    member_level: '',
    sex: '',
    birthday: ''
}
const saveData: Record<string, any> = reactive({ ...initialFormData })

const emit = defineEmits(['complete'])

/**
 * 确认
 * @param formEl
 */
const confirm = async (formEl: FormInstance | undefined) => {
    await formRef.value?.validate((valid) => {
        if (valid) {
            loading.value = true

            if (repeat.value) return
            repeat.value = true

            let val = saveData[type.value]
            if (type.value == 'member_label') {
                // 将saveData[type.value]中的值，转换为字符串
                val = saveData[type.value] && saveData[type.value].length ? deepClone(saveData[type.value]).join(',').split(',') : ''
            }

            const data = ref({
                member_id: memberId.value,
                field: type.value,
                value: val
            })

            editMemberDetail(data.value).then(res => {
                loading.value = false
                repeat.value = false
                showDialog.value = false
                emit('complete')
            }).catch(() => {
                loading.value = false
                repeat.value = false
            })
        }
    })
}
// 修改
const setDialogType = async (row: any = null) => {
    loading.value = true
    type.value = row.type
    title.value = row.title
    memberId.value = row.id
    saveData[type.value] = row.data[type.value]
    if (type.value == 'member_label' && saveData[type.value]) {
        saveData[type.value].forEach((item: any, index: any) => {
            let isExist = false
            for (let i = 0; i < labelSelectData.value.length; i++) {
                if (labelSelectData.value[i].label_id == item) {
                    isExist = true
                    break
                }
            }
            if (isExist) {
                saveData[type.value][index] = Number.parseFloat(item)
            } else {
                saveData[type.value].splice(index, 1) // 删除不存在的id
            }
        })
    }
    loading.value = false
}

// 批量设置
const method = ref(null)
const batchInfo = ref({
    is_all: 0,
    ids: [],
    where: {}
})
const batchSetDialogType = (data) => {
    loading.value = true
    type.value = data.type
    method.value = data.method
    batchInfo.value.is_all = data.data.is_all
    batchInfo.value.ids = data.data.ids
    batchInfo.value.where = data.data.where
    title.value = data.title
    saveData[type.value] = null
    loading.value = false
}

const batchSetConfirm = async (formEl: FormInstance | undefined) => {
    await formRef.value?.validate((valid) => {
        if (valid) {
            loading.value = true

            if (repeat.value) return
            repeat.value = true

            let val = saveData[type.value]
            if (type.value == 'member_label') {
                val = saveData[type.value] && saveData[type.value].length ? deepClone(saveData[type.value]).join(',').split(',') : ''
            }

            const data = ref({
                is_all: batchInfo.value.is_all,
                member_ids: batchInfo.value.ids,
                where: batchInfo.value.where,
                field: type.value,
                value: val
            })
            console.log(data.value)
            memberBatchModify(data.value).then(res => {
                loading.value = false
                repeat.value = false
                showDialog.value = false
                emit('complete')
            }).catch(() => {
                loading.value = false
                repeat.value = false
            })
        }
    })
}

defineExpose({
    showDialog,
    setDialogType,
    batchSetDialogType
})
</script>

<style lang="scss" scoped></style>
