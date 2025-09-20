<template>
    <div class="main-container">
        <el-card class="box-card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <el-card class="box-card !border-none mb-[15px]" shadow="never">
            <el-result v-if="status === 'payment'" icon="success" title="支付成功">
                <template #extra>
                    <el-button type="primary" @click="back()">{{ t('back') }}</el-button>
                </template>
            </el-result>

            <el-result v-else-if="status === 'close'" icon="error" title="订单已关闭">
                <template #extra>
                    <el-button type="primary" @click="back()">{{ t('back') }}</el-button>
                </template>
            </el-result>

            <el-result v-else icon="info" title="支付状态查询中" sub-title="请稍候，正在确认支付状态..." />
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { t } from '@/lang'
import { ArrowLeft } from '@element-plus/icons-vue'
import { getOrderPayStatus } from '@/app/api/notice'

const router = useRouter()
const route = useRoute()
const pageName = route.meta.title
const username = route.query.username || ''
const back = () => {
    router.push('/setting/niusms/setting')
}

// 当前支付状态：wait、payment、close
const status = ref('wait')

let timer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
    const orderNo = route.query.out_trade_no as string
    if (!orderNo) return

    // 初始调用一次
    checkStatus(orderNo)

    // 开始轮询
    timer = setInterval(() => {
        checkStatus(orderNo)
    }, 2000)
})

onBeforeUnmount(() => {
    if (timer) clearInterval(timer)
})

const checkStatus = async (orderNo: string) => {
    try {
        const res = await getOrderPayStatus(username, {
            out_trade_no: orderNo
        })
        status.value = res.data.order_status

        if (res.data.order_status === 'payment' || res.data.order_status === 'close') {
            if (timer) clearInterval(timer)
        }
    } catch (err) {
        console.error('获取支付状态失败', err)
    }
}
</script>

<style lang="scss" scoped></style>
