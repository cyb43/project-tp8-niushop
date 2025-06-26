<template>
    <div class="main-container">
        <!-- 商品概况 -->
        <el-card shadow="never" class="!border-none goods-stat">
            <template #header>
               <div class="flex justify-between items-start">
                    <span class="text-lg font-extrabold mr-[10px]">{{  t('goodsOverview') }}</span>
                    <div class="flex items-center">
                        <el-form :inline="true">
                            <el-form-item :label="t('timeFilter')" prop="date">
                                <el-date-picker v-model="date" type="daterange" value-format="YYYY/MM/DD" :start-placeholder="t('startTime')" :end-placeholder="t('endTime')" :shortcuts="shortcuts" />
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary"  @click="getGoodsStatisticsFn()">{{ t('search') }}</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </template>
            <div class="flex flex-wrap">
                <div class="w-[25%] flex flex-shrink-0 mb-[30px]">
                    <div class="w-[32px] h-[32px] bg-[#1890ff] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-a-Group840V6xx text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.access_num">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('goodsAccessNum') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('goodsAccessNumTip')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0 mb-[30px]">
                    <div class="w-[32px] h-[32px] bg-[#00c050] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-a-zuji34 text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.goods_visit_member_count">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('goodsVisitCount') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('goodsVisitCountTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0 mb-[30px]">
                    <div class="w-[32px] h-[32px] bg-[#ffab2b] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-gouwucheV6xx-11 text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.cart_num">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('cartNum') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('cartNumTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0 mb-[30px]">
                    <div class="w-[32px] h-[32px] bg-[#b37feb] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-xiadanjianshu text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.sale_num">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('saleNum') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('saleNumTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0 mb-[30px]">
                    <div class="w-[32px] h-[32px] bg-[#627DFE] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-zhifujianshu text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.pay_num">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('payNum') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('payNumTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0">
                    <div class="w-[32px] h-[32px] bg-[#F76A6C] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-zhifujine text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.pay_money" class="flex-1" precision="2">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('payMoney') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('payMoneyTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0">
                    <div class="w-[32px] h-[32px] bg-[#43C5FF] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-tuikuanjine text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.refund_money" class="flex-1" precision="2">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('refundMoney') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('refundMoneyTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
                <div class="w-[25%] flex flex-shrink-0">
                    <div class="w-[32px] h-[32px] bg-[#11D0EA] rounded-full flex justify-center items-center mr-[15px]">
                        <span class="nc-iconfont nc-icon-tuikuanjianshu text-[#fff]"></span>
                    </div>
                    <el-statistic :value="goodsTotal.refund_num">
                        <template #title>
                            <div class="flex items-center">
                                <span class="mr-[5px] text-[14px]">{{ t('refundNum') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('refundNumTips')" placement="top">
                                    <el-icon>
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-statistic>
                </div>
            </div>
        </el-card>
        <div class="bg-[white] mt-[20px]">
            <div ref="incomeChartRef" class="h-[400px] w-full pt-[30px]"></div>
        </div>
        <el-card shadow="never" class="!border-none goods-stat mt-[20px]">
            <template #header>
               <div>
                    <div class="text-lg font-extrabold mb-[20px]">{{ t('goodsRank') }}</div>
                    <el-form :inline="true" :model="goodsData.searchParam" ref="searchFormRef">
                        <el-form-item :label="t('goodsName')" prop="goods_name">
                            <el-input v-model.trim="goodsData.searchParam.goods_name" :placeholder="t('goodsNamePlaceholder')" maxlength="60" />
                        </el-form-item>
                        <el-form-item :label="t('goodsCategory')" prop="category_ids">
                            <el-cascader v-model="goodsData.searchParam.category_ids" :options="goodsCategoryOptions" :placeholder="t('all')" clearable :props="{ value: 'value', label: 'label', emitPath:false }" @change="handleSearch"/>
                        </el-form-item>
                        <el-form-item :label="t('totalType')" prop="type">
                            <el-select v-model="goodsData.searchParam.type" :placeholder="t('totalTypePlaceholder')" clearable @change="handleSearch">
                                <el-option v-for="item in goodsStatisticsType" :key="item.value" :label="item.label" :value="item.value" />
                            </el-select>
                        </el-form-item>
                        <el-form-item :label="t('timeFilter')" prop="date">
                            <el-date-picker v-model="goodsData.searchParam.date" type="daterange" value-format="YYYY/MM/DD" :start-placeholder="t('startTime')" :end-placeholder="t('endTime')" :shortcuts="shortcuts" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary"  @click="loadGoodsList()">{{ t('search') }}</el-button>
                            <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </template>
            <div>
                <el-table :data="goodsData.data" size="large" v-loading="goodsData.loading" ref="goodsListTableRef" >
                    <template #empty>
                        <span>{{ !goodsData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column :label="t('goodsInfo')" min-width="300">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer" @click="previewEvent(row)">
                                <div class="min-w-[70px] h-[70px] flex items-center justify-center">
                                    <el-image v-if="row.goods_cover_thumb_small" class="w-[70px] h-[70px]" :src="img(row.goods_cover_thumb_small)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" />
                                            </div>
                                        </template>
                                    </el-image>
                                    <img v-else class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                </div>
                                <div class="ml-2 flex flex-col items-start">
                                    <span :title="row.goods_name" class="multi-hidden">{{ row.goods_name }}</span>
                                    <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.is_gift == 1">赠品</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="access_num" :label="t('accessNum')" min-width="100"  />
                    <el-table-column prop="goods_visit_member_count" :label="t('visitCount')" min-width="100"  />
                    <el-table-column prop="cart_num" :label="t('cartNumber')" min-width="100"  />
                    <el-table-column prop="sale_num" :label="t('saleNumber')" min-width="100"  />
                    <el-table-column prop="pay_num" :label="t('payNum')" min-width="100"  />
                    <el-table-column prop="pay_money" :label="t('payTotal')" min-width="100" />
                    <el-table-column prop="collect_num" :label="t('collectNum')" min-width="100"  />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="goodsData.page" v-model:page-size="goodsData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="goodsData.total"
                        @size-change="loadGoodsList()" @current-change="loadGoodsList" />
                </div>
            </div>
        </el-card>
    </div>
</template>
<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { img } from '@/utils/common'
import { t } from '@/lang'
import * as echarts from 'echarts'
import { useRouter } from 'vue-router'
import { getCategoryTree } from '@/addon/shop/api/goods'
import { getGoodsStatisticsBasic, getGoodsStatisticsTrend, getGoodsStatisticsType, getGoodsStatisticsRank } from '@/addon/shop/api/stat'
import { FormInstance } from 'element-plus'

const router = useRouter()
const curDateRange = () => {
    const end = new Date()
    const start = new Date()
    start.setTime(start.getTime() - 3600 * 1000 * 24 * 29)
    const format = (date: any) => {
        const year = date.getFullYear()
        const month = date.getMonth() + 1
        const day = date.getDate()

        return year + '/' + month + '/' + day
    }
    return [format(start), format(end)]
}
const date = ref<any>([])
date.value = curDateRange()

const shortcuts = [
    {
        text: '今天',
        value: new Date()
    }, {
        text: '昨天',
        value: () => {
            const end = new Date()
            const start = new Date()
            start.setTime(start.getTime() - 3600 * 1000 * 24)
            return [start, end]
        }
    }, {
        text: '最近7天',
        value: () => {
            const end = new Date()
            const start = new Date()
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7)
            return [start, end]
        }
    }, {
        text: '最近30天',
        value: () => {
            const end = new Date()
            const start = new Date()
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 29)
            return [start, end]
        }
    }, {
        text: '上月',
        value: () => {
            const date = new Date()
            const year = date.getFullYear()
            const month = date.getMonth()
            const start = new Date(year, month - 1, 1)
            const end = new Date(year, month, 0)
            return [start, end]
        }
    }, {
        text: '本月',
        value: () => {
            const date = new Date()
            const year = date.getFullYear()
            const month = date.getMonth()
            const start = new Date(year, month, 1)
            const end = new Date()
            return [start, end]
        }
    }, {
        text: '本年',
        value: () => {
            const date = new Date()
            const year = date.getFullYear()
            const start = new Date(year, 0, 1)
            const end = new Date()
            return [start, end]
        }
    }
]

const goodsTotal = ref<any>([]) // 基本信息
const goodsTrend = ref<any>([]) // 图表数据
const getGoodsStatisticsFn = async () => {
    const data = date.value ? date.value.join('-') : ''
    goodsTotal.value = await (await getGoodsStatisticsBasic({ date: data })).data
    goodsTrend.value = await (await getGoodsStatisticsTrend({ date: data })).data
    setTimeout(() => {
        initIncomeChart()
    }, 20)
}
getGoodsStatisticsFn()

// 折线图
const incomeChartRef = ref(null)
const initIncomeChart = () => {
    const goodsData = reactive<any>([])
    const goodsLegend = reactive<any>([])

    goodsTrend.value.data.forEach((item: any) => {
        if (item.name == 'goods_visit_member_count') {
            goodsData.push({
                name: '商品访客数',
                type: 'line',
                smooth: true,
                data: item.data
            })
            goodsLegend.push('商品访客数')
        } else if (item.name == 'access_count') {
            goodsData.push({
                name: '浏览量',
                type: 'line',
                smooth: true,
                data: item.data
            })
            goodsLegend.push('浏览量')
        } else if (item.name == 'cart_num') {
            goodsData.push({
                name: '加购件数',
                type: 'bar',
                data: item.data
            })
            goodsLegend.push('加购件数')
        } else if (item.name == 'sale_num') {
            goodsData.push({
                name: '下单件数',
                type: 'bar',
                data: item.data
            })
            goodsLegend.push('下单件数')
        } else if (item.name == 'pay_num') {
            goodsData.push({
                name: '支付件数',
                type: 'bar',
                data: item.data
            })
            goodsLegend.push('支付件数')
        } else if (item.name == 'pay_money') {
            goodsData.push({
                name: '支付金额',
                type: 'bar',
                data: item.data
            })
            goodsLegend.push('支付金额')
        } else if (item.name == 'refund_money') {
            goodsData.push({
                name: '退款金额',
                type: 'bar',
                data: item.data
            })
            goodsLegend.push('退款金额')
        } else if (item.name == 'refund_num') {
            goodsData.push({
                name: '退款件数',
                type: 'bar',
                data: item.data
            })
            goodsLegend.push('退款件数')
        }
    })
    if (incomeChartRef.value !== null) {
        const incomeChart = echarts.init(incomeChartRef.value)
        // 配置项
        const option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross'
                }
            },
            color: ['#1890ff', '#00c050', '#F76A6C', '#43C5FF', '#ffab2b', '#b37feb', '#627DFE', '#11D0EA'],
            legend: {
                data: [],
                selected: {
                    '加购件数': false,
                    '下单件数': false,
                    '支付件数': false,
                    '退款件数': false
                }
            },
            xAxis: {
                type: 'category',
                axisLabel: {
                    interval: 0,
                    rotate: 45
                },
                data: goodsTrend.value.xAxis
            },
            yAxis: {
                type: 'value'
            },
            grid: [{
                left: '3%',
                right: '3%',
                bottom: '40',
                containLabel: true
            }],
            series: []
        }
        // 使用配置项初始化图表
        option.series = goodsData
        option.legend.data = goodsLegend
        incomeChart.setOption(option)
        incomeChart.resize({
            width: 'auto'
        })
        window.addEventListener('resize', () => {
            // 页面大小变化后Echarts也更改大小
            incomeChart.resize()
        })
    }
}

// 商品分类
const goodsCategoryOptions: any = reactive([])

// 商品类型
const goodsStatisticsType: any = reactive([])
const initData = () => {
    // 查询商品分类树结构
    getCategoryTree().then((res) => {
        const data = res.data
        if (data) {
            const goodsCategoryTree: any = []
            // 增加全部筛选选择
            goodsCategoryTree.push({
                value: '',
                label: '全部',
                children: []
            })
            data.forEach((item: any) => {
                const children: any = []
                if (item.child_list) {
                    children.push({
                        value: item.category_id,
                        label: '全部'
                    })
                    item.child_list.forEach((childItem: any) => {
                        children.push({
                            value: childItem.category_id,
                            label: childItem.category_name
                        })
                    })
                }
                goodsCategoryTree.push({
                    value: item.category_id,
                    label: item.category_name,
                    children
                })
            })
            goodsCategoryOptions.splice(0, goodsCategoryOptions.length, ...goodsCategoryTree)
        }
    })

    // 统计类型
    getGoodsStatisticsType().then((res) => {
        if (res.data) {
            for (const k in res.data) {
                goodsStatisticsType.push({
                    label: res.data[k],
                    value: k
                })
            }
        }
    })
}
initData()

const goodsData = reactive<any>({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        goods_name: '',
        date: [],
        category_ids: '',
        type: ''
    }
})

const loadGoodsList = (page: number = 1) => {
    goodsData.loading = true
    goodsData.page = page
    const data = {
        page: goodsData.page,
        limit: goodsData.limit,
        goods_name: goodsData.searchParam.goods_name,
        type: goodsData.searchParam.type,
        date: goodsData.searchParam.date ? goodsData.searchParam.date.join('-') : '',
        category_ids: goodsData.searchParam.category_ids ? [goodsData.searchParam.category_ids] : []
    }
    getGoodsStatisticsRank(data).then((res: any) => {
        goodsData.data = res.data.data
        goodsData.total = res.data.total
        goodsData.loading = false
    })
}
loadGoodsList()

const handleSearch = () => {
    loadGoodsList()
}

const searchFormRef = ref<FormInstance>()
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadGoodsList()
}
// 商品预览
const previewEvent = (data: any) => {
    const url = router.resolve({
        path: '/preview/wap',
        query: {
            page: `/addon/shop/pages/goods/detail?goods_id=${data.goods_id}`
        }
    })
    window.open(url.href)
}
</script>
<style lang="scss" scoped>
:deep(.goods-stat .el-card__header){
    border-bottom: none;
    padding-bottom: 0;
}

</style>
