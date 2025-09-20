<template>
    <div>
        <el-dialog v-model="visible" :title="t('选择签名')" width="1200px" destroy-on-close :close-on-click-modal="false">
            <el-alert type="warning" :closable="false" class="!mb-[10px]">
                <template #default>
                    <p>签名数据的变更（新增 / 删除）需经过五分钟的生效周期，在此期间系统将完成数据同步与更新</p>
                </template>
            </el-alert>
            <div class="flex justify-between items-center mb-[16px]">
                <el-button type="primary" @click="addEvent">{{ t('添加短信签名') }}</el-button>
            </div>
            <div class="mb-[10px] flex items-center">
                <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                <el-button @click="batchDeleteEvent" size="small">{{t("批量删除")}}</el-button>
            </div>
            <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="smsSignListTableRef" @selection-change="handleSelectionChange">
                <template #empty>
                    <span>{{ !tableData.loading ? t("emptyData") : "" }}</span>
                </template>
                <el-table-column type="selection" :selectable="checkSelectable" width="55" />
                <el-table-column prop="sign" :label="t('签名名称')" min-width="200" />
                <el-table-column prop="is_default" :label="t('使用状态')" min-width="120">
                    <template #default="{ row }">
                        <div>{{ row.is_default? t('使用中') : t('未使用') }}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="auditResultName" :label="t('审核状态')" min-width="200">
                    <template #default="{ row }">
                        <div>
                            <div :class="[row.auditResult == 2 ? 'text-green-600' : '']">{{ row.auditResultName }}</div>
                            <div class="text-red-600" v-if="row.auditResult != 2">{{ row.auditMsg }}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="realNameDx" :label="t('实名状态')" min-width="200">
                    <template #header>
                        <div style="display: inline-flex; align-items: center">
                            <span class="mr-[5px]">{{ t('实名状态') }}</span>
                            <el-tooltip class="box-item" effect="light" placement="top">
                                <template #content>
                                    状态标识说明：<br />未实名状态显示为灰色；<br />实名通过状态显示为绿色；<br />实名失败状态显示为红色。<br />
                                    短信接收条件：仅当手机号在对应运营商处实名通过后，才可接收短信。
                                </template>
                                <el-icon color="#666">
                                    <QuestionFilled />
                                </el-icon>
                            </el-tooltip>
                        </div>
                    </template>
                    <template #default="{ row }">
                        <div class="flex gap-[5px]">
                            <el-tag :type="row.realNameLt == 0 ? 'info' : row.realNameLt == 1 ? 'success' : 'danger'">{{ t("联通") }}</el-tag>
                            <el-tag :type="row.realNameYd == 0 ? 'info' : row.realNameYd == 1 ? 'success' : 'danger'">{{ t("移动") }}</el-tag>
                            <el-tag :type="row.realNameDx == 0 ? 'info' : row.realNameDx == 1 ? 'success' : 'danger'">{{ t("电信") }}</el-tag>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="create_time" :label="t('createTime')" min-width="120">
                    <template #default="{ row }">
                        <div>{{ row.createTime }}</div>
                    </template>
                </el-table-column>
                <el-table-column :label="t('操作')" fixed="right" align="right" min-width="120">
                    <template #default="{ row }">
                        <el-button type="primary" link @click="selectTemplate(row)" v-if="!row.is_default && row.auditResult == 2">
                            {{ t("使用") }}
                        </el-button>
                        <el-button type="primary" link @click="deleteTemplate(row)" v-if="!row.is_default">
                            {{ t("删除") }}
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"  @size-change="loadSignList()" @current-change="loadSignList" />
            </div>

            <template #footer>
                <el-button @click="visible = false">{{ t("cancel") }}</el-button>
            </template>
        </el-dialog>
        <el-dialog v-model="visibleAdd" :title="t('添加签名')" width="800px" destroy-on-close :close-on-click-modal="false">
            <el-form label-width="150px" :model="formData" ref="formRef" :rules="formRules" class="page-form ml-[20px]">
                <el-form-item :label="t('短信签名')" prop="signature">
                    <el-input v-model="formData.signature" placeholder="请输入短信签名" class="input-width" maxlength="20"  show-word-limit clearable />
                </el-form-item>
                <div class="ml-[150px] text-[12px] text-[#999] leading-[20px]">必须由【】包裹，例如：【test】</div>
                <div class="my-[5px] ml-[150px] text-[12px] text-[#999] leading-[20px]">字数要求在2-20个字符，不能使用空格和特殊符号“ - + = * & % # @ ~等;</div>
                <el-form-item :label="t('短信示例内容')" prop="contentExample">
                    <el-input v-model="formData.contentExample" placeholder="请输入短信示例内容" clearable  maxlength="50"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('企业名称')" prop="companyName">
                    <el-input v-model="formData.companyName" placeholder="请输入企业名称" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('社会统一信用代码')" prop="creditCode">
                    <el-input v-model="formData.creditCode" placeholder="请输入社会统一信用代码" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('法人姓名')" prop="legalPerson">
                    <el-input v-model="formData.legalPerson" placeholder="请输入法人姓名" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('经办人姓名')" prop="principalName">
                    <el-input v-model="formData.principalName" placeholder="请输入经办人姓名" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('经办人手机号')" prop="principalMobile">
                    <el-input v-model="formData.principalMobile" placeholder="请输入经办人手机号" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('经办人身份证')" prop="principalIdCard">
                    <el-input v-model="formData.principalIdCard" placeholder="请输入经办人身份证" clearable  maxlength="18"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('签名来源')">
                    <el-radio-group v-model="formData.signSource" >
                        <el-radio v-for="item in signConfig.signSourceList" :key="item.type" :label="item.type" >{{item.name}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('签名类型')">
                    <el-radio-group v-model="formData.signType">
                        <el-radio v-for="item in signConfig.signTypeList" :key="item.type" :label="item.type" >{{item.name}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('上传图片')" prop="imgUrl">
                    <upload-image v-model="formData.imgUrl" :limit="1" />
                </el-form-item>
                <div class="ml-[150px] text-[12px] text-[#999] leading-[20px]">当签名来源为商标、APP、小程序、事业单位简称或企业名称简称时，需必填此字段</div>
                <div class="my-[5px] ml-[150px] text-[12px] text-[#999] leading-[20px]">当签名来源为事业单位全称或企业名称全称时，选填此字段。</div>
                <el-form-item :label="t('是否默认')">
                    <el-radio-group v-model="formData.defaultSign" >
                        <el-radio :label="1">是</el-radio>
                        <el-radio :label="0">否</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="visibleAdd = false">{{ t("cancel") }}</el-button>
                <el-button type="primary" @click="onSave()">{{ t("confirm") }}</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, reactive } from 'vue'
import { getSignList, addSign, getSmsSignConfig, deleteSign } from '@/app/api/notice'
import { t } from '@/lang'

const visible = ref(false)
const visibleAdd = ref(false)
const emit = defineEmits(['select'])
const props = defineProps({
    username: {
        type: String,
        default: ''
    }
})
const initialFormData = {
    defaultSign: 0,
    imgUrl: '',
    contentExample: '',
    signType: '',
    signSource: '',
    principalIdCard: '',
    principalName: '',
    principalMobile: '',
    legalPerson: '',
    creditCode: '',
    companyName: '',
    signature: ''
}
const formData = reactive({ ...initialFormData })

const signConfig = reactive({
    signTypeList: [],
    signSourceList: []
})
const getSmsSignConfigFn = () => {
    getSmsSignConfig().then(res => {
        signConfig.signTypeList = res.data.sign_type_list
        signConfig.signSourceList = res.data.sign_source_list
        formData.signSource = res.data.sign_source_list[0].type
        formData.signType = res.data.sign_type_list[0].type
    })
}

getSmsSignConfigFn()

const formRef = ref()
const formRules = computed(() => {
    return {
        signature: [
            { required: true, message: '请输入短信签名', trigger: 'blur' },
            {
                validator: (rule, value, callback) => {
                    const singleBracketValid = /^【[^【】]*】$/.test(value)
                    if (!singleBracketValid) {
                        return callback(new Error('短信签名必须被【】包裹'))
                    }

                    const content = value.slice(1, -1)

                    const lengthValid = content.length >= 2 && content.length <= 20
                    if (!lengthValid) {
                        return callback(new Error('短信签名内容需在 2-20 个字符之间'))
                    }

                    const invalidChars = /[\s\-+=*&%#@~;]/
                    if (invalidChars.test(content)) {
                        return callback(new Error('短信签名不能包含空格或特殊字符 - + = * & % # @ ~ ;'))
                    }

                    callback()
                },
                trigger: 'blur'
            }
        ],
        principalMobile: [
            { required: true, message: '请输入经办人手机号', trigger: 'blur' },
            { validator: phoneVerify, trigger: 'blur' }
        ],
        companyName: [
            { required: true, message: '请输入企业名称', trigger: 'blur' }
        ],
        contentExample: [
            { required: true, message: '请输入短信示例内容', trigger: 'blur' }
        ],
        creditCode: [
            { required: true, message: '请输入社会统一信用代码', trigger: 'blur' }
        ],
        legalPerson: [
            { required: true, message: '请输入法人姓名', trigger: 'blur' }
        ],
        principalName: [
            { required: true, message: '请输入经办人姓名', trigger: 'blur' }
        ],
        principalIdCard: [
            { required: true, message: '请输入经办人身份证', trigger: 'blur' },
            { validator: idCardVerify, trigger: 'blur' }
        ],
        imgUrl: [
            {
                validator: (rule, value, callback) => {
                    const needImage = [3, 4, 5].includes(formData.signSource) || formData.signType === 1
                    if (needImage) {
                        if (!value || value.length === 0) {
                            callback(new Error('请上传图片'))
                        } else {
                            callback()
                        }
                    } else {
                        callback() // 不需要校验
                    }
                },
                trigger: 'blur'
            }
        ]

    }
})

const idCardVerify = (rule: any, value: any, callback: any) => {
    if (value && !/^[1-9]\d{5}(19|20)\d{2}((0\d)|(1[0-2]))(([0-2]\d)|3[0-1])\d{3}([0-9Xx])$/.test(value)) {
        callback(new Error(t('请输入正确的身份证号码')))
    } else {
        callback()
    }
}

const phoneVerify = (rule: any, value: any, callback: any) => {
    if (value && !/^1[3-9]\d{9}$/.test(value)) {
        callback(new Error(t('请输入正确的手机号码')))
    } else {
        callback()
    }
}

const onSave = async () => {
    await formRef.value?.validate(async (valid) => {
        if (valid) {
            addSign(props.username, formData).then((res) => {
                setTimeout(() => {
                    visibleAdd.value = false
                    loadSignList()
                }, 500)
            })
        }
    })
}

// 表单内容
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {}
})

const open = () => {
    visible.value = true
    loadSignList()
}
// 获取列表
const loadSignList = () => {
    tableData.loading = true
    const params = {
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }
    getSignList(props.username, params).then((res) => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
    }).catch(() => {
        tableData.loading = false
    })
}

const addEvent = () => {
    Object.assign(formData, initialFormData)
    formData.signSource = signConfig.signSourceList[0].type
    formData.signType = signConfig.signTypeList[0].type
    visibleAdd.value = true
}

const deleteTemplate = (row:any) => {
    ElMessageBox.confirm(t('确定删除该签名吗？'), t('提示'), {
        confirmButtonText: t('确定'),
        cancelButtonText: t('取消'),
        type: 'warning'
    }).then(() => {
        deleteSign(props.username, { signatures: [row.sign] }).then((res) => {
            // loadSignList()
            tableData.loading = true
            setTimeout(() => {
                loadSignList()
            }, 1000)
        })
    })
}

// 批量复选框
const toggleCheckbox = ref()

// 复选框中间状态
const isIndeterminate = ref(false)

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    smsSignListTableRef.value.toggleAllSelection()
}

const smsSignListTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < tableData.data.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == tableData.data.length && tableData.data.length && multipleSelection.value.length) {
        toggleCheckbox.value = true
    }
}
const checkSelectable = (row: any, index: number) => {
    return !row.is_default // 只有不是“使用中”的行可选
}

// 批量删除
const batchDeleteEvent = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('请选择要删除的签名')}`
        })
        return
    }

    ElMessageBox.confirm(t('确定删除选中的签名吗？'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        const signatures: any = []
        multipleSelection.value.forEach((item: any) => {
            signatures.push(item.sign)
        })

        deleteSign(props.username, {
            signatures
        }).then(() => {
            tableData.loading = true
            setTimeout(() => {
                loadSignList()
            }, 1000)
        }).catch(() => {
        })
    })
}

const selectTemplate = (row:any) => {
    visible.value = false
    emit('select', row)
}

defineExpose({ open })
</script>

<style lang="scss" scoped></style>
