<template>
    <el-card class="box-card !border-none p-[10px]" shadow="never" v-loading="loadingPackage">
        <div class="panel-title">选择套餐</div>
        <div class="flex flex-wrap mb-[30px]">
            <div v-for="(item,index) in smsPackages" :key="index" :span="4">
                <div class="package-card mr-[10px] mb-[10px]" :class="{ active: selectedPackage?.id === item.id }" @click="selectPackage(item)">
                    <div class="text-[14px] mb-1 using-hidden">{{ item.package_name }}</div>
                    <div class="text-[24px] text-primary ">{{ item.sms_num }}条</div>
                    <div class="flex mt-2 text-[14px] justify-center items-center ">
                        <div>￥{{ item.price }}</div>
                        <div class="line-through ml-2">￥{{ item.original_price }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-title">选择支付方式</div>
        <el-radio-group v-model="payMethod" class="mb-4">
            <el-radio label="alipay">支付宝</el-radio>
            <!-- 可扩展其他方式 -->
        </el-radio-group>

        <div class="mb-4 text-[14px] ml-[10px] mt-[10px]">
            应付：<span class="text-[24px] font-semibold text-primary"><span class="text-[14px] font-400">￥</span>{{ totalAmount }}</span>
        </div>

        <div class="ml-[50px]">
            <el-button type="primary" :disabled="!selectedPackage || loading" :loading="loading" @click="submitPayment">支付</el-button>
            <el-button @click="goBack">返回</el-button>
        </div>
    </el-card>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { getSmsPackagesList, smsOrderCreate, getOrderPayInfo, getOrderPayStatus, calculateOrderPay } from '@/app/api/notice'

const props = defineProps({
    username: {
        type: String,
        default: ''
    },
    isRecharge: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['back', 'complete'])

const smsPackages = ref<any[]>([])
const selectedPackage = ref<any | null>(null)
const payMethod = ref('alipay')
const loadingPackage = ref(false)
const totalAmount = ref(0)
const getSmsPackagesListFn = () => {
    loadingPackage.value = true
    getSmsPackagesList().then(res => {
        smsPackages.value = res.data.data
        loadingPackage.value = false
        if (smsPackages.value.length > 0 && props.username) {
            selectPackage(smsPackages.value[0])
        }
    }).catch(() => {
        loadingPackage.value = false
    })
}

const selectPackage = (pkg: any) => {
    selectedPackage.value = pkg
    calculateAmount()
}

// 计算金额
const calculateAmount = () => {
    if (!selectedPackage.value) return
    calculateOrderPay(props.username, {
        package_id: selectedPackage.value.id
    }
    ).then(res => {
        totalAmount.value = res.data.pay_money
    })
}
const loading = ref(false)
const submitPayment = async () => {
    if (!selectedPackage.value || loading.value) return
    loading.value = true
    try {
        const res = await smsOrderCreate(props.username, {
            package_id: selectedPackage.value.id
        })

        if (res.data.order_status === 'payment') {
            emit('complete')
        } else {
            const orderNo = res.data.out_trade_no
            const payInfo = await getOrderPayInfo(props.username, { out_trade_no: orderNo })

            window.open(payInfo.data.pay_info.url, '_blank')
            await ElMessageBox.confirm('请确认支付是否完成', '支付提示', {
                confirmButtonText: '已完成支付',
                cancelButtonText: '返回',
                type: 'warning'
            })
            emit('complete')
        }
    } catch (err) {
        ElMessage.error('支付失败，请重试')
    } finally {
        loading.value = false
    }
}

const goBack = () => {
    emit('back')
}

const showRecharge = ref(false)
watch(() => props.isRecharge, (newVal) => {
    showRecharge.value = newVal
    if (newVal) {
        getSmsPackagesListFn()
    }
})

</script>

<style lang="scss" scoped>
.package-card {
    cursor: pointer;
    text-align: center;
    border: 1px solid #e4e7ed;
    transition: all 0.2s;
    padding: 10px;
    width: 170px;
    border-radius: 5px;

    &.active {
        border-color: var(--el-color-primary);
        box-shadow: 0 0 6px rgba(64, 158, 255, 0.3);
    }
}
</style>
