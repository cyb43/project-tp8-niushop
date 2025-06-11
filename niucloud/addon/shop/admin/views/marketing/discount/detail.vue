<template>
    <div class="main-container" v-loading="loading">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <el-form class="page-form mt-[15px]" :model="formData" label-width="100px" ref="formRef" label-position="left" v-if="Object.keys(formData).length">
            <el-card class="box-card !border-none relative" shadow="never" v-if="formData">
                <h3 class="panel-title">{{ t('baseInfo') }}</h3>
                <div class="px-[30px] mb-[20px]">
                    <el-row>
                        <el-col :span="8">
                            <el-form-item :label="t('name')">
                                <div class="input-width">
                                    <span>{{formData.active_name}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('title')">
                                <div class="input-width">
                                    <span>{{formData.active_desc}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('status')">
                                <div class="input-width">
                                    <span>{{formData.active_status_name}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('paymentAmount')">
                                <div class="input-width">
                                    <span>{{formData.active_order_money}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('memberCount')">
                                <div class="input-width">
                                    <span>{{formData.active_member_num}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('orderCount')">
                                <div class="input-width">
                                    <span>{{formData.active_order_num}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('startTime')">
                                <div class="input-width">
                                    <span>{{formData.start_time}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('endTime')">
                                <div class="input-width">
                                    <span>{{formData.end_time}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('createTime')">
                                <div class="input-width">
                                    <span>{{formData.create_time}}</span>
                                </div>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
            </el-card>
        </el-form>
        <el-card class="box-card !border-none relative" shadow="never" v-if="Object.keys(formData).length">
            <el-tabs v-model="activeName" class="py-[10px]" @tab-change="handleClick">
                <el-tab-pane label="活动商品" name="goodsList" />
                <el-tab-pane label="活动订单" name="orderList" />
                <el-tab-pane label="活动会员" name="memberList" />
            </el-tabs>
            <div v-if="activeName == 'goodsList'">
                <el-form :inline="true" :model="goodsParams.searchParam">
                    <el-form-item :label="t('keyword')" prop="keyword">
                        <el-input v-model.trim="goodsParams.searchParam.keyword" clearable :placeholder="t('keywordPlaceholder')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="getActiveDiscountGoodsPageListFn()">{{ t('search') }}</el-button>
                    </el-form-item>
                </el-form>
                <el-table :data="goodsParams.data" size="large" v-loading="goodsParams.loading">
                    <template #empty>
                        <span>{{ !goodsParams.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="goods_id" :label="t('goodsInfo')" min-width="300">
                        <template #default="{ row }">
                            <div v-if="row.goods" class="flex items-center cursor-pointer" @click="previewEvent(row)">
                                <div class="min-w-[70px] h-[70px] flex items-center justify-center">
                                    <el-image v-if="row.goods.goods_cover_thumb_small" class="w-[70px] h-[70px]" :src="img(row.goods.goods_cover_thumb_small)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" />
                                            </div>
                                        </template>
                                    </el-image>
                                    <img v-else class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                </div>
                                <div class="ml-2">
                                    <span :title="row.goods.goods_name" class="multi-hidden">{{ row.goods.goods_name }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('price')" min-width="120">
                        <template #default="{ row }">
                            <span v-if="row.goodsSku">￥{{ row.goodsSku.price }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="active_goods_order_money" :label="t('paymentAmount')" min-width="100" />
                    <el-table-column prop="active_goods_order_num" :label="t('orderCount')" min-width="100" />
                    <el-table-column prop="active_goods_member_num" :label="t('activeMemberNum')" min-width="100" />
                    <el-table-column prop="active_goods_success_num" :label="t('activeSuccessNum')" min-width="100" />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="goodsParams.page" v-model:page-size="goodsParams.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="goodsParams.total"
                        @size-change="getActiveDiscountGoodsPageListFn()" @current-change="getActiveDiscountGoodsPageListFn" />
                </div>
            </div>
            <div v-if="activeName == 'orderList'">
                <el-form :inline="true" :model="orderParams.searchParam" ref="orderSearchFormRef">
                    <el-form-item :label="t('orderInfo')" prop='search_name'>
                        <el-input class="input-item" v-model.trim="orderParams.searchParam.search_name" />
                    </el-form-item>
                    <el-form-item :label="t('payType')" prop='status'>
                        <el-select v-model="orderParams.searchParam.status" clearable class="input-item">
                            <el-option :label="t('toBePaid')" value="1"></el-option>
                            <el-option :label="t('toBeShipped')" value="2"></el-option>
                            <el-option :label="t('shipped')" value="3"></el-option>
                            <el-option :label="t('completed')" value="5"></el-option>
                            <el-option :label="t('closed')" value="-1"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="orderParams.searchParam.create_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item :label="t('payTime')" prop="pay_time">
                        <el-date-picker v-model="orderParams.searchParam.pay_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="getActiveDiscountOrderPageListFn()">{{ t('search') }}</el-button>
                        <el-button @click="orderResetForm(orderSearchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
                <el-table :data="orderParams.data" size="large" v-loading="orderParams.loading">
                    <template #empty>
                        <span>{{ !orderParams.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="order_no" :label="t('orderNo')" min-width="100" />
                    <el-table-column prop="order_money" :label="t('orderMoney')" min-width="100" />
                    <el-table-column :label="t('buyInfo')" min-width="120">
                        <template #default="{row}">
                            <div class="flex flex-col">
                                <span class="text-[12px] text-primary cursor-pointer" @click="detailEvent(row.member.member_id)">{{ row.member.nickname }}</span>
                                <span class="text-[12px] mt-[5px]">{{ row.taker_name }} {{row.taker_mobile }}</span>
                                <span class="text-[12px] mt-[5px]">{{ row.taker_full_address }}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('payType')" min-width="120">
                        <template #default="{row}">
                            <span>{{ row.pay && row.pay.type_name ? row.pay.type_name : ''}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('orderStatus')" min-width="100">
                        <template #default="{row}">
                            <span class="text-[14px]">{{ row.order_status_data.name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" :label="t('createTime')" min-width="100" />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="orderParams.page" v-model:page-size="orderParams.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="orderParams.total"
                        @size-change="getActiveDiscountOrderPageListFn()" @current-change="getActiveDiscountOrderPageListFn" />
                </div>
            </div>
            <div v-if="activeName == 'memberList'">
                <el-table :data="memberParams.data" size="large" v-loading="memberParams.loading">
                    <template #empty>
                        <span>{{ !memberParams.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="goods_id" :label="t('memberInfo')" min-width="200">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer" @click="detailEvent(row.member.member_id)">
                                <div class="min-w-[50px] h-[50px] flex items-center justify-center">
                                    <el-image v-if="row.member.headimg" class="w-[50px] h-[50px]" :src="img(row.member.headimg)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[50px] h-[50px] rounded-full" src="@/app/assets/images/member_head.png" alt="">
                                            </div>
                                        </template>
                                    </el-image>
                                    <img class="w-[50px] h-[50px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                </div>
                                <div class="ml-2">
                                    <span :title="(row.member.nickname || row.member.username)" class="multi-hidden">{{row.member.nickname || row.member.username}}</span>
                                    <span class="text-primary text-[12px]">{{row.mobile || ''}}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="active_order_money" :label="t('consumptionMoney')" min-width="100" />
                    <el-table-column prop="member_count" :label="t('participationNum')" min-width="100" />
                    <el-table-column prop="create_time" :label="t('orderTime')" min-width="100" />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="memberParams.page" v-model:page-size="memberParams.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="memberParams.total"
                        @size-change="getActiveDiscountMemberPageList()" @current-change="getActiveDiscountMemberPageList" />
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, reactive } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import {FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import {getActiveDiscountInfo, getActiveDiscountGoodsPageList, getActiveDiscountOrderPageList, getActiveDiscountMemberPageList} from "@/addon/shop/api/marketing";
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const back = () => {
    router.push('/shop/marketing/discount/list')
}

const formData:Record<string, any> = ref({})
const loading = ref(false)
const activeName = ref('goodsList')

const handleClick = (data:string) => {
    activeName.value = data
}

onMounted(() => {
    if(route.query.id) getActiveDiscountInfoFn(Number(route.query.id))
})
const getActiveDiscountInfoFn = (id:number)=>{
    loading.value = true
    getActiveDiscountInfo(id).then((res:any)=>{
        formData.value = Object.assign(formData.value,res.data)
        loading.value = false
    })
}
// 活动商品
const goodsParams = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        keyword: '',
        active_id: route.query.id
    }
})
const getActiveDiscountGoodsPageListFn = (page: number = 1)=>{
    goodsParams.loading = true
    goodsParams.page = page
    getActiveDiscountGoodsPageList({
        page: goodsParams.page,
        limit: goodsParams.limit,
        ...goodsParams.searchParam
    }).then(res=>{
        goodsParams.loading = false
        goodsParams.data = res.data.data
        goodsParams.total = res.data.total
    }).catch(() => {
        goodsParams.loading = false
    })
}
getActiveDiscountGoodsPageListFn()
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
// 活动订单
const orderSearchFormRef = ref()
const orderParams = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        search_name: '',
        status: '',
        create_time: [],
        pay_time: [],
        active_id: route.query.id
    }
})
const getActiveDiscountOrderPageListFn = (page: number = 1)=>{
    orderParams.loading = true
    orderParams.page = page
    getActiveDiscountOrderPageList({
        page: orderParams.page,
        limit: orderParams.limit,
        ...orderParams.searchParam
    }).then(res => {
        orderParams.loading = false
        orderParams.data = res.data.data
        orderParams.total = res.data.total
    }).catch(() => {
        orderParams.loading = false
    })
}
getActiveDiscountOrderPageListFn()
const orderResetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    getActiveDiscountOrderPageListFn()
}
//活动会员
const memberParams = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        active_id:route.query.id
    }
})
const getActiveDiscountMemberPageListFn= (page: number = 1)=>{
    memberParams.loading = true
    memberParams.page = page
    getActiveDiscountMemberPageList({
        page: memberParams.page,
        limit: memberParams.limit,
        ...memberParams.searchParam
    }).then((res:any)=>{
        memberParams.loading = false
        memberParams.data = res.data.data
        memberParams.total = res.data.total
    }).catch(() => {
        memberParams.loading = false
    })
}
getActiveDiscountMemberPageListFn()

//查看会员详情
const detailEvent = (member_id:number)=> {
    let routeData = router.resolve(`/member/detail?id=${member_id}`)
    window.open(routeData.href, ' blank');
}
</script>

<style lang="scss" scoped></style>
