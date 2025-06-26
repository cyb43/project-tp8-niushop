<template>
    <el-drawer v-model="showDialog" title="活动详情" direction="rtl" :before-close="handleClose" class="member-detail-drawer">
        <div class="main-container" v-loading="loading">
            <el-tabs v-model="activeName" class="pb-[10px]" @tab-change="handleClick">
                <el-tab-pane label="基础信息" name="basicInfo" />
                <el-tab-pane label="活动商品" name="goodsList" />
                <el-tab-pane label="活动订单" name="orderList" />
                <el-tab-pane label="活动会员" name="memberList" />
            </el-tabs>
            <div v-if="activeName == 'basicInfo'">
                <el-form class="mt-[15px]" :model="formData" label-width="100px" ref="formRef" label-position="left" v-if="Object.keys(formData).length">
                    <div class="relative" shadow="never" v-if="formData">
                        <el-row>
                            <el-col :span="8">
                                <el-form-item :label="t('activeName')">
                                    <div class="input-width">
                                        <span>{{formData.name}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('title')">
                                    <div class="input-width">
                                        <span>{{formData.remark}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('status')">
                                    <div class="input-width">
                                        <span>{{formData.status_name}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('paymentAmount')">
                                    <div class="input-width">
                                        <span>{{formData.order_money}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('participationMemberCount')">
                                    <div class="input-width">
                                        <span>{{formData.member_num}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('payOrderCount')">
                                    <div class="input-width">
                                        <span>{{formData.order_num}}</span>
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
                </el-form>
            </div>
            <div v-if="activeName == 'goodsList'">
                <el-form :inline="true" :model="goodsParams.searchParam">
                    <el-form-item :label="t('keyword')" prop="keyword">
                        <el-input v-model.trim="goodsParams.searchParam.keyword" :placeholder="t('keywordPlaceholder')" />
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
                            <div v-if="row" class="flex items-center cursor-pointer" @click="previewEvent(row)">
                                <div class="min-w-[70px] h-[70px] flex items-center justify-center">
                                    <el-image v-if="row.goods_cover" class="w-[70px] h-[70px]" :src="img(row.goods_cover)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" />
                                            </div>
                                        </template>
                                    </el-image>
                                    <img v-else class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                </div>
                                <div class="ml-2">
                                    <span :title="row.goods_name" class="multi-hidden">{{ row.goods_name }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('price')" min-width="120">
                        <template #default="{ row }">
                            <span v-if="row">￥{{ row.price }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="order_money" :label="t('paymentAmount')" min-width="100" />
                    <el-table-column prop="order_num" :label="t('orderCount')" min-width="100" />
                    <el-table-column prop="member_num" :label="t('activeMemberNum')" min-width="100" />
                    <el-table-column prop="success_num" :label="t('activeSuccessNum')" min-width="100" />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="goodsParams.page" v-model:page-size="goodsParams.limit" :page-sizes="[6,10,20,30,50,100]"
                        layout="total, sizes, prev, pager, next, jumper" :total="goodsParams.total"
                        @size-change="getActiveDiscountGoodsPageListFn()" @current-change="getActiveDiscountGoodsPageListFn" />
                </div>
            </div>
            <div v-if="activeName == 'orderList'">
                <el-form :inline="true" :model="orderParams.searchParam" ref="orderSearchFormRef">

                    <el-form-item :label="t('orderInfo')" prop='search_name'>
                        <el-input class="input-item" v-model.trim="orderParams.searchParam.search_name" />
                    </el-form-item>
                    <el-form-item :label="t('orderStatus')" prop='status'>
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
                    <el-table-column prop="order_no" :label="t('orderNo')" min-width="100">
                        <template #default="{ row }">
                          <span class="cursor-pointer text-primary" @click="toGoodsCategoryEvent(row.order_id)">{{ row.order_no }}</span>
                        </template>
                    </el-table-column>

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
                    <el-pagination v-model:current-page="orderParams.page" v-model:page-size="orderParams.limit"  :page-sizes="[4,10,20,30,50,100]"
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
        </div>
    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getActiveDiscountGoodsPageList, getActiveDiscountOrderPageList, getActiveDiscountMemberPageList,getBasicInformation} from "@/addon/shop/api/marketing";
import { FormInstance } from 'element-plus'
import { useRouter } from 'vue-router'
import { img } from '@/utils/common'

const router = useRouter()
const showDialog = ref(false)
const loading = ref(false)
let id = ''
const activeName = ref('basicInfo')
const formData:Record<string, any> = ref({})

const handleClick = (data:string) => {
    activeName.value = data
}

const handleClose = (done: () => void) => {
    activeName.value = 'basicInfo';
    showDialog.value = false;
}

const getActiveDiscountInfoFn = (id:number)=>{
    loading.value = true
    getBasicInformation(id).then((res:any)=>{
        formData.value = Object.assign(formData.value,res.data)
        loading.value = false
    })
}

// 活动商品
const goodsParams = reactive({
    page: 1,
    limit: 6,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        keyword: '',
        active_id: id
    }
})
const getActiveDiscountGoodsPageListFn = (page: number = 1)=> {
    goodsParams.loading = true
    goodsParams.page = page
    getActiveDiscountGoodsPageList({
        page: goodsParams.page,
        limit: goodsParams.limit,
        ...goodsParams.searchParam
    }).then(res => {
        goodsParams.loading = false
        goodsParams.data = res.data.data
        goodsParams.total = res.data.total
    }).catch(() => {
        goodsParams.loading = false
    })
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

// 活动订单
const orderSearchFormRef = ref()
const orderParams = reactive({
    page: 1,
    limit: 4,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        search_name: '',
        status: '',
        create_time: [],
        pay_time: [],
        active_id: ''
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
const orderResetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    orderParams.searchParam.create_time = [];
    getActiveDiscountOrderPageListFn()
}

// 活动会员
const memberParams = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        active_id: id
    }
})
const getActiveDiscountMemberPageListFn = (page: number = 1) => {
    memberParams.loading = true
    memberParams.page = page
    getActiveDiscountMemberPageList({
        page: memberParams.page,
        limit: memberParams.limit,
        ...memberParams.searchParam
    }).then((res: any) => {
        memberParams.loading = false
        memberParams.data = res.data.data
        memberParams.total = res.data.total
    }).catch(() => {
        memberParams.loading = false
    })
}

// 查看会员详情
const detailEvent = (member_id:number)=> {
    let routeData = router.resolve(`/member/detail?id=${ member_id }`)
    window.open(routeData.href, ' blank');
}

const setFormData = async (row: any = null) => {
    id = row.id;

    memberParams.searchParam.active_id = row.id;
    orderParams.searchParam.active_id = row.id;
    goodsParams.searchParam.active_id = row.id;

    getActiveDiscountMemberPageListFn()
    getActiveDiscountOrderPageListFn()
    getActiveDiscountGoodsPageListFn()
    getActiveDiscountInfoFn(Number(id))
}

const toGoodsCategoryEvent = (order_id:any) => {
    // 你可以在这里根据 orderNo 来动态传递参数
    const url = router.resolve({
        path: "/shop/order/detail",
        query: { order_id } // 传递 orderNo 到商品分类页面
    })
    window.open(url.href)
}

defineExpose({
    showDialog,
    setFormData
})
</script>
<style lang="scss">
.member-detail-drawer{
    width: 1300px !important;
}
</style>
