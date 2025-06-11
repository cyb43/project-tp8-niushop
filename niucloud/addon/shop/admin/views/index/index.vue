<template>
    <div class="main-container">
        <!-- 实时概况 -->
        <el-card shadow="never" class="!border-none">
            <template #header>
                <span class="text-lg font-extrabold mr-[10px]">{{t('realtimeOverview')}}</span>
                <span class="text-sm text-[#a19f98]">{{t('updateTime')}}</span>
                <span class="text-sm text-[#a19f98]">{{ time }}</span>
            </template>
            <el-row>
                <el-col :span="6">
                    <div class="ml-[10px]">
                        <div class="text-sm text-[#a19f98] leading-8">
                            <el-statistic :value="statToday.order_num">
                                <template #title>
                                    <div style="display: inline-flex; align-items: center">
                                        <span class="mr-[5px]">{{t('todayOrderCount')}}</span>
                                        <el-tooltip class="box-item" effect="light" :content="t('todayOrderCount')" placement="top">
                                            <el-icon>
                                                <QuestionFilled />
                                            </el-icon>
                                        </el-tooltip>
                                    </div>
                                </template>
                            </el-statistic>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8">
                            <span>{{t('yesterday')}}</span>
                            <span>{{statYesterday.order_num}}</span>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8 mt-[15px]">
                            <el-statistic :title="t('orderCount')" :value="statTotal.order_num" />
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="ml-[10px]">
                        <div class="text-sm text-[#a19f98] leading-8">
                            <el-statistic :value="statToday.sale_money">
                                <template #title>
                                    <div style="display: inline-flex; align-items: center">
                                        <span class="mr-[5px]">{{t('todayOrderSale')}}</span>
                                        <el-tooltip class="box-item" effect="light" :content="t('todayOrderSale')" placement="top">
                                            <el-icon>
                                                <QuestionFilled />
                                            </el-icon>
                                        </el-tooltip>
                                    </div>
                                </template>
                            </el-statistic>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8">
                            <span>{{t('yesterday')}}</span>
                            <span>{{statYesterday.sale_money}}</span>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8 mt-[15px]">
                            <el-statistic :title="t('salesTotal')" :value="statTotal.sale_money" />
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="ml-[10px]">
                        <div class="text-sm text-[#a19f98] leading-8">
                            <el-statistic :value="statToday.refund_money">
                                <template #title>
                                    <div style="display: inline-flex; align-items: center">
                                        <span class="mr-[5px]">{{t('todayAddMemberCount')}}</span>
                                        <el-tooltip class="box-item" effect="light" :content="t('todayAddMemberCount')" placement="top">
                                            <el-icon>
                                                <QuestionFilled />
                                            </el-icon>
                                        </el-tooltip>
                                    </div>
                                </template>
                            </el-statistic>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8">
                            <span>{{t('yesterday')}}</span>
                            <span>{{statYesterday.refund_money}}</span>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8 mt-[15px]">
                            <el-statistic :title="t('memberTotal')" :value="statTotal.refund_money" />
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="ml-[10px]">
                        <div class="text-sm text-[#a19f98] leading-8">
                            <el-statistic :value="statToday.access_sum">
                                <template #title>
                                    <div style="display: inline-flex; align-items: center">
                                        <span class="mr-[5px]">{{t('todayBrowseCount')}}</span>
                                        <el-tooltip class="box-item" effect="light" :content="t('todayBrowseCount')" placement="top">
                                            <el-icon>
                                                <QuestionFilled />
                                            </el-icon>
                                        </el-tooltip>
                                    </div>
                                </template>
                            </el-statistic>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8">
                            <span>{{t('yesterday')}}</span>
                            <span>{{statYesterday.access_sum}}</span>
                        </div>
                        <div class="text-sm text-[#a19f98] leading-8 mt-[15px]">
                            <el-statistic :title="t('browseTotal')" :value="statTotal.access_sum" />
                        </div>
                    </div>
                </el-col>
            </el-row>
        </el-card>
        <!-- 实时概况 end -->

        <!-- 代办事项 -->
        <el-card shadow="never" class="mt-[15px] !border-none">
            <template #header>
                <span class="text-lg font-extrabold">{{t('agentMatters')}}</span>
            </template>
            <el-row>
                <el-col :span="4" class="cursor-pointer" @click="router.push({ path: '/shop/order/index', query: {status: 1}})">
                    <div class="ml-[10px]">
                        <el-statistic :value="statOrder.wait_pay_order">
                            <template #title>
                                <div style="display: inline-flex; align-items: center">
                                    <span class="mr-[5px]">{{t('waitPayOrder')}}</span>
                                    <el-tooltip class="box-item" effect="light" :content="t('waitPayOrder')" placement="top">
                                        <el-icon>
                                            <QuestionFilled />
                                        </el-icon>
                                    </el-tooltip>
                                </div>
                            </template>
                        </el-statistic>
                    </div>
                </el-col>
                <el-col :span="4" class="cursor-pointer" @click="router.push({ path: '/shop/order/index', query: {status: 2}})">
                    <el-statistic :value="statOrder.wait_delivery_order">
                        <template #title>
                            <div style="display: inline-flex; align-items: center">{{t('waitDeliveryOrder')}}</div>
                        </template>
                    </el-statistic>
                </el-col>
                <el-col :span="4" class="cursor-pointer" @click="router.push({ path: '/shop/order/index', query: {status: 3}})">
                    <el-statistic :value="statOrder.wait_take_order">
                        <template #title>
                            <div style="display: inline-flex; align-items: center">{{t('waitTakeOrder')}}</div>
                        </template>
                    </el-statistic>
                </el-col>
                <el-col :span="4" class="cursor-pointer" @click="router.push({ path: '/shop/order/refund'})">
                    <el-statistic :value="statOrder.refund_order">
                        <template #title>
                            <div style="display: inline-flex; align-items: center">退款订单</div>
                        </template>
                    </el-statistic>
                </el-col>
                <el-col :span="4" class="cursor-pointer" @click="router.push({ path: '/shop/goods/list'})">
                    <el-statistic :value="statGoods.sale_goods_num">
                        <template #title>
                            <div style="display: inline-flex; align-items: center">{{t('saleGoodsNum')}}</div>
                        </template>
                    </el-statistic>
                </el-col>
                <el-col :span="4" class="cursor-pointer" @click="router.push({ path: '/shop/goods/list', query: {status: 0}})">
                    <el-statistic :value="statGoods.warehouse_goods_num">
                        <template #title>
                            <div style="display: inline-flex; align-items: center">{{t('warehouseGoodsNum')}}</div>
                        </template>
                    </el-statistic>
                </el-col>
            </el-row>
        </el-card>
        <!-- 代办事项 end -->

        <!-- 订单趋势 -->
        <el-row :gutter="15" class="mt-[15px]">
            <el-col :span="12">
                <el-card shadow="never" class="!border-none">
                    <template #header>
                        <span class="text-lg font-extrabold">订单量趋势</span>
                    </template>
                    <div ref="visitStat" :style="{ width: '100%', height: '300px' }"></div>
                </el-card>
            </el-col>
            <el-col :span="12">
                <el-card shadow="never" class="!border-none">
                    <template #header>
                        <span class="text-lg font-extrabold">销售额（元）</span>
                    </template>
                    <div ref="hourStat" :style="{ width: '100%', height: '300px' }"></div>
                </el-card>
            </el-col>
        </el-row>
        <!-- 订单趋势 end -->
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import {
    getShopCountList,
    getShopTodayCountList,
    getShopYesterdayCountList,
    getShopStat,
    getShopOrderStat,
    getShopGoodsStat
} from '@/addon/shop/api/shop'
import * as echarts from 'echarts'
import { useRouter } from 'vue-router'

const router = useRouter()
const visitStat = ref<any>(null)
const hourStat = ref<any>(null)

interface statTotalType{
    order_num:  [number,string],
    sale_money: [number,string],
    refund_money:  [number,string],
    access_sum:  [number,string]
}
interface statTodayType{
    order_num:  [number,string],
    sale_money: [number,string],
    refund_money:  [number,string],
    access_sum:  [number,string]
}
interface statYesterdayType{
    order_num:  [number,string],
    sale_money: [number,string],
    refund_money:  [number,string],
    access_sum:  [number,string]
}
interface statOrderType{
    wait_pay_order:  [number,string],
    wait_delivery_order:  [number,string],
    wait_take_order:  [number,string],
    refund_order:  [number,string]
}
interface statGoodsType{
    sale_goods_num:  [number,string],
    warehouse_goods_num:  [number,string]
}
interface statCountType{
    order_num:  [number,string],
    time: string,
    sale_money:  [number,string]
}
const statTotal = ref<statTotalType|any>([])
const statToday = ref<statTodayType|any>([])
const statYesterday = ref<statYesterdayType|any>([])
const statCount = ref<statCountType|any>([])
const statOrder = ref<statOrderType|any>([])
const statGoods = ref<statGoodsType|any>([])

const getStatInfoFn = async () => {
    let statTotalData = await (await getShopCountList()).data
    for (let i in statTotalData) {
        statTotalData[i] = Number(statTotalData[i])
    }
    statTotal.value = statTotalData
    statToday.value = await (await getShopTodayCountList()).data
    statToday.value.sale_money = statToday.value.sale_money == '0.00' ? 0 : Number(statToday.value.sale_money)
    statToday.value.refund_money = statToday.value.refund_money == '0.00' ? 0 : Number(statToday.value.refund_money)
    statToday.value.order_num = Number(statToday.value.order_num)
    statToday.value.access_sum = Number(statToday.value.access_sum)
    statYesterday.value = await (await getShopYesterdayCountList()).data
    statOrder.value = await (await getShopOrderStat()).data
    statGoods.value = await (await getShopGoodsStat()).data
    statCount.value = await (await getShopStat()).data
    setTimeout(() => {
        drawChart('')
        drawChartTo('')
    }, 20)
}

getStatInfoFn()

const drawChart = (item:any) => {
    let value = statCount.value.order_num
    if (item) value = item
    if (!visitStat.value) return
    const visitStatChart = echarts.init(visitStat.value)
    const visitStatOption = ref({
        // title: {
        //     text: '订单量趋势'
        // },
        legend: {},
        xAxis: {
            data: []
        },
        yAxis: {},
        tooltip: {
            trigger: 'axis',
            formatter: (params: any[]) => {
                if (!params.length) return '';
                const date = params[0].axisValue; // 时间
                const data = params[0].data;
                return `${date}<br/>订单量: ${data} 单`;
            }
        },

        series: [
            {
                type: 'line',
                data: []
            }
        ]
    })
    visitStatOption.value.xAxis.data = statCount.value.time
    visitStatOption.value.series[0].data = value
    visitStatChart.setOption(visitStatOption.value)
}
const drawChartTo = (item:any) => {
    let valueTo = statCount.value.sale_money
    if (item) valueTo = item
    if (!hourStat.value) return
    const hourStatChart = echarts.init(hourStat.value)
    const hourStatOption = ref({
        // title: {
        //     text: '销售额（元）'
        // },
        legend: {},
        xAxis: {
            data: []
        },
        yAxis: {},
        tooltip: {
            trigger: 'axis',
            formatter: (params: any[]) => {
                if (!params.length) return '';
                const date = params[0].axisValue;
                const data = params[0].data;
                return `${date}<br/>销售额: ${data} 元`;
            }
        },
        series: [
            {
                type: 'line',
                data: []
            }
        ]
    })
    hourStatOption.value.xAxis.data = statCount.value.time
    hourStatOption.value.series[0].data = valueTo
    hourStatChart.setOption(hourStatOption.value)
}
const time = ref('')
const nowTime = () => {
    const date = new Date()
    const year = date.getFullYear()
    const month = date.getMonth() + 1
    const day = date.getDate()
    const hh = checkTime(date.getHours())
    const mm = checkTime(date.getMinutes())
    const ss = checkTime(date.getSeconds())
    function checkTime (i:any) {
        if (i < 10) {
            return '0' + i
        }
        return i
    }
    time.value = year + '-' + month + '-' + day + ' ' + hh + ':' + mm + ':' + ss
}
nowTime()
</script>

<style lang="scss" scoped></style>
