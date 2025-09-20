<template>
    <el-drawer v-model="showDialog" :title="popTitle" direction="rtl" :before-close="handleClose" class="member-detail-drawer" size="1300px">
        <div class="main-container" v-loading="loading">
            <div class="bg-page py-[20px] pr-[30px] relative flex">
                <div class="member-info w-[250px]">
                    <div class="flex items-center">
                        <div class="text-[14px] min-w-[110px] text-right mr-[20px]">{{ t('headimg') }}</div>
                        <div class="flex items-end text-[14px]">
                            <div class="w-[50px] h-[50px] flex items-center justify-center">
                                <img class="max-w-[50px] max-h-[50px] inline-block" v-if="formData.headimg" :src="img(formData.headimg)" alt="">
                                <img class="max-w-[50px] max-h-[50px] inline-block rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                            </div>
                            <el-icon @click="editMemberInfo('headimg')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                                <EditPen color="#273CE2" />
                            </el-icon>
                        </div>
                    </div>
                    <div class="flex items-center mt-[20px]">
                        <span class="text-[14px] min-w-[110px] text-right mr-[20px]">UID</span>
                        <span class="member-info-item text-[14px] text-[#666666] font-bold w-[150px]">
                            {{ formData.member_no || t('notAvailable') }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-1 justify-between">
                    <div class="statistic-card">
                        <el-statistic :value="formData.point">
                            <template #title>
                                <div style="display: inline-flex; align-items: center">
                                    <span class="text-[14px]">
                                        {{ t('point') }}
                                    </span>
                                    <el-tooltip effect="dark" :content="t('adjust')" placement="top">
                                        <el-icon @click="adjustPoint(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <EditPen color="#273CE2" />
                                        </el-icon>
                                    </el-tooltip>
                                    <el-tooltip effect="dark" :content="t('detail')" placement="top">
                                        <el-icon @click="infoPoint(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <View />
                                        </el-icon>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-statistic>
                        <div class="statistic-footer">
                            <div class="footer-item text-[14px] text-secondary">
                                <span>{{ t('accumulative') }}</span>
                                <span class="green ml-1">
                                    {{ formData.point_get }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="statistic-card">
                        <el-statistic :value="formData.balance">
                            <template #title>
                                <div style="display: inline-flex; align-items: center">
                                    <span class="text-[14px]">
                                        {{ t('balance') }}
                                    </span>
                                    <el-tooltip effect="dark" :content="t('adjust')" placement="top">
                                        <el-icon @click="adjustBalance(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <EditPen color="#273CE2" />
                                        </el-icon>
                                    </el-tooltip>
                                    <el-tooltip effect="dark" :content="t('detail')" placement="top">
                                        <el-icon @click="infoBalance(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <View />
                                        </el-icon>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-statistic>
                        <div class="statistic-footer">
                            <div class="footer-item text-[14px] text-secondary">
                                <span>{{ t('accumulative') }}</span>
                                <span class="red ml-1">
                                    {{ formData.balance_get }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="statistic-card">
                        <el-statistic :value="formData.growth">
                            <template #title>
                                <div style="display: inline-flex; align-items: center">
                                    <span class="text-[14px]">
                                        {{ t('growth') }}
                                    </span>
                                    <el-tooltip effect="dark" :content="t('detail')" placement="top">
                                        <el-icon @click="infoGrowth(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <View />
                                        </el-icon>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-statistic>
                    </div>
                    <div class="statistic-card">
                        <el-statistic :value="formData.money" title="New transactions today">
                            <template #title>
                                <div style="display: inline-flex; align-items: center">
                                    <span class="text-[14px]">
                                        {{ t("money") }}
                                    </span>
                                    <el-tooltip effect="dark" :content="t('detail')" placement="top">
                                        <el-icon @click="infoBalance(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <View />
                                        </el-icon>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-statistic>
                        <div class="statistic-footer">
                            <div class="footer-item text-[14px] text-secondary">
                                <span>{{ t('accumulative') }}</span>
                                <span class="green ml-1">
                                    {{ formData.money_get }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="statistic-card">
                        <el-statistic :value="formData.commission" title="New transactions today">
                            <template #title>
                                <div style="display: inline-flex; align-items: center ">
                                    <span class="text-[14px]">
                                        {{ t("commission") }}
                                    </span>
                                    <el-tooltip effect="dark" :content="t('detail')" placement="top">
                                        <el-icon @click="infoCommission(formData)" class="ml-2 cursor-pointer" :size="12">
                                            <View />
                                        </el-icon>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-statistic>
                        <div class="statistic-footer">
                            <div class="footer-item text-[14px] text-secondary">
                                <span>{{ t('accumulative') }}</span>
                                <span class="green ml-1">
                                    {{ formData.commission_get }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap mt-[20px]">
                <div class="flex items-center w-[33.3%] mt-[15px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('urserName') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.username || t('notAvailable') }}
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('nickname') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.nickname || t('notAvailable') }}
                    </span>
                </div>

                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('mobile') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.mobile || t('notAvailable') }}<el-icon @click="editMemberInfo('mobile')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('memberLevel') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.member_level_name || t('notAvailable') }}<el-icon @click="editMemberInfo('member_level')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('memberLabel') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.member_label_name&&formData.member_label_name.toString() || t('notAvailable') }}<el-icon @click="editMemberInfo('member_label')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('birthday') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.birthday || t('notAvailable') }}<el-icon @click="editMemberInfo('birthday')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('sex') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.sex == 1 && t('manSex') || formData.sex == 2 && t('girlSex') || t('secrecySex') }}<el-icon @click="editMemberInfo('sex')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('身份证') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.id_card || t('notAvailable') }}<el-icon @click="editMemberInfo('id_card')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('备注') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.remark || t('notAvailable') }}<el-icon @click="editMemberInfo('remark')" class="-bottom-[2px] -right-[4px] cursor-pointer">
                            <EditPen color="#273CE2" />
                        </el-icon>
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('wxUnionid') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.wx_unionid || t('notAvailable') }}
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('weappOpenid') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.weapp_openid || t('notAvailable') }}
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('wxOpenid') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.wx_openid || t('notAvailable') }}
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('registeredSource') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.register_channel_name || t('notAvailable') }}
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('createTime') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.create_time || t('notAvailable') }}
                    </span>
                </div>
                <div class="flex mt-[15px] w-[33.3%] break-all leading-[21px]">
                    <span class="text-[14px] w-[130px] text-right flex-shrink-0 mr-[20px]">{{ t('lastVisitTime') }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ formData.last_visit_time || t('notAvailable') }}
                    </span>
                </div>
            </div>
        </div>

        <point-edit ref="pointDialog" @complete="getMemberInfoFn" />
        <balance-edit ref="balanceDialog" @complete="getMemberInfoFn" />
        <edit-member ref="editMemberDialog" @complete="getMemberInfoFn()" />
    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getMemberInfo } from '@/app/api/member'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'
import { img } from '@/utils/common'
import PointEdit from '@/app/views/member/components/member-point-edit.vue'
import BalanceEdit from '@/app/views/member/components/member-balance-edit.vue'
import EditMember from '@/app/views/member/components/edit-member.vue'

const showDialog = ref(false)
const loading = ref(false)
let popTitle: string = '会员详情'
let id = '';

const router = useRouter()
const emit = defineEmits(['load'])

const handleClose = (done: () => void) => {
    showDialog.value = false;
}

/**
 * 表单数据
 */
const initialFormData = {
    member_id: '',
    nickname: '',
    member_no: '',
    init_member_no: '',
    mobile: '',
    password: '',
    password_copy: ''
}
const formData: Record<string, any> = reactive({ ...initialFormData })

const pointDialog: Record<string, any> | null = ref(null)
const balanceDialog: Record<string, any> | null = ref(null)
const editMemberDialog: Record<string, any> | null = ref(null)

/**
 *  修改会员信息
 */
const editMemberInfo = (type: any) => {
    const data = ref({
        type,
        id,
        data: formData
    })
    editMemberDialog.value.setDialogType(data.value)
    editMemberDialog.value.showDialog = true
}

/**
 * 调整积分
 */
const adjustPoint = (data: any) => {
    pointDialog.value.setFormData(data)
    pointDialog.value.showDialog = true
}

/**
 * 调整余额
 */
const adjustBalance = (data: any) => {
    balanceDialog.value.setFormData(data)
    balanceDialog.value.showDialog = true
}

/**
 * 积分详情
 */
const infoPoint = () => {
    router.push(`/member/point?id=${id}`)
}

/**
 * 余额详情
 */
const infoBalance = () => {
    router.push(`/member/balance?id=${id}`)
}

/**
 * 成长值明细
 */
const infoGrowth = () => {
    router.push(`/member/growth?id=${id}`)
}

/**
 * 佣金详情
 */
const infoCommission = () => {
    router.push(`/member/commission?id=${id}`)
}

const getMemberInfoFn = async (bool=false) => {
    loading.value = true
    if (id) {
        const data = await (await getMemberInfo(id)).data
        if (!data || Object.keys(data).length == 0) {
            ElMessage.error(t('memberNull'))
            setTimeout(() => {
                router.go(-1)
            }, 2000)
            return false
        }

        Object.keys(data).forEach((item) => {
            formData[item] = data[item]
        })
        if(!data.member_label_array){
            formData.member_label_array =[]
             formData.member_label_name=''
        }
        if (formData?.member_label_array && Object.keys(formData.member_label_array)?.length) {
            formData.member_label = Object.values(formData.member_label_array).map((item: any, index) => {
                return item.label_id
            })
            
            formData.member_label_name = Object.values(formData.member_label_array).map((item: any, index) => {
                return item.label_name
            })
        }
         loading.value = false
    } else {
        loading.value = false
    }
    if(!bool){
        emit('load');
    }
}

const setFormData = async (row: any = null) => {
    id = row.id;
    Object.assign(formData, initialFormData)
    await getMemberInfoFn(true);
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
