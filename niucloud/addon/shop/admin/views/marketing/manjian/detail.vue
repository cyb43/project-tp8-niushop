<template>
    <el-drawer v-model="showDialog" :title="t('detailTitle')" direction="rtl" :before-close="handleClose" size="1300px">
        <div class="main-container" v-loading="loading">
            <el-tabs v-model="activeName" class="pb-[10px]" @tab-change="handleClick">
                <el-tab-pane :label="t('basicInfo')" name="basicInfo" />
                <el-tab-pane :label="t('activeMember')" name="memberList" />
            </el-tabs>
            <div v-if="activeName == 'basicInfo'">
                <el-card class="mb-[15px]" >
                    <h3 class="panel-title">{{ t('basicInfo') }}</h3>
                    <el-form class="mt-[15px]" :model="formData" label-width="120px" ref="formRef" label-position="left" v-if="Object.keys(formData).length">
                    <div class="relative" v-if="formData">
                        <el-row>
                            <el-col :span="8">
                                <el-form-item :label="t('name')">
                                    <div class="input-width">
                                        <span>{{formData.manjian_name}}</span>
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
                                <el-form-item :label="t('totalGoodsNum')">
                                    <div class="input-width">
                                        <span>{{formData.total_goods_num}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('totalCouponNum')">
                                    <div class="input-width">
                                        <span>{{formData.total_coupon_num}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('totalBalance')">
                                    <div class="input-width">
                                        <span>{{formData.total_balance}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('totalPoint')">
                                    <div class="input-width">
                                        <span>{{formData.total_point}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('activeOrderMoney')">
                                    <div class="input-width">
                                        <span>{{formData.total_order_money}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('activeMemberNum')">
                                    <div class="input-width">
                                        <span>{{formData.total_member_num}}</span>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('activeOrderNum')">
                                    <div class="input-width">
                                        <span>{{formData.total_order_num}}</span>
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
                </el-card>
                <el-card class="mt-4">
                    <h3 class="panel-title">{{ t('activeDetail') }}</h3>
                    <el-table :data="formData.rule_json" size="large">
                        <el-table-column :label="t('discountThreshold')" prop="limit">
                            <template #default="{ row }">
                                <span v-if="row.limit">满{{ row.limit }}{{ formData.condition_type == 'over_n_yuan' ? '元' : '件' }}</span>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('discountMoney')" prop="discount_money">
                            <template #default="{ row }">
                                <span v-if="row.discount_money">{{ row.discount_money }}{{row.discount_type==1 ? '元' : '折'}}</span>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('givePoint')" prop="point">
                            <template #default="{ row }">
                                <span v-if="row.is_give_point">{{ row.point }}</span>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('giveBalance')" prop="balance">
                            <template #default="{ row }">
                                <span v-if="row.is_give_balance">{{ row.balance }}</span>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('freeShipping')" prop="is_free_shipping">
                            <template #default="{row}">
                                <span v-if="row.is_free_shipping">{{ t('freeShipping')}}</span>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('giveCoupon')" prop="coupon" min-width="150">
                            <template #default="{ row }">
                                <div v-if="row.coupon && row.coupon.length && row.is_give_coupon">
                                    <div v-for="(coupon, index) in row.coupon" :key="index" class="flex items-center">
                                        <div class="goods-name">{{ coupon.title }} </div>
                                        <div>* {{ coupon.num }}</div>
                                    </div>
                                </div>
                                <div v-else>-</div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('giveGoods')" prop="goods" min-width="200">
                            <template #default="{ row }">
                                <div v-if="row.goods && row.goods.length &&row.is_give_goods">
                                    <div v-for="(goods, index) in row.goods" :key="index" class="flex items-center">
                                        <div class="goods-name">{{ goods.goods_name }} </div>
                                        <div>* {{ goods.num }}</div>
                                    </div>
                                </div>
                                <div v-else>-</div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-card>

            </div>
            <div v-if="activeName == 'memberList'">
                <el-table :data="memberParams.data" size="large" v-loading="memberParams.loading">
                    <template #empty>
                        <span>{{ !memberParams.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="goods_id" :label="t('memberInfo')" min-width="200">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer" @click="detailEvent(row.member_id)">
                                <div class="min-w-[50px] h-[50px] flex items-center justify-center">
                                    <el-image v-if="row.headimg" class="w-[50px] h-[50px]" :src="img(row.headimg)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[50px] h-[50px] rounded-full" src="@/app/assets/images/member_head.png" alt="">
                                            </div>
                                        </template>
                                    </el-image>
                                    <img class="w-[50px] h-[50px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                </div>
                                <div class="ml-2">
                                    <span :title="(row.nickname || row.username)" class="multi-hidden">{{row.nickname || row.username}}</span>
                                    <span class="text-primary text-[12px]">{{row.mobile || ''}}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_order_money" :label="t('consumptionMoney')" min-width="100" />
                    <el-table-column prop="total_num" :label="t('participationNum')" min-width="100" />
                    <el-table-column prop="finally_order_time" :label="t('orderTime')" min-width="100" />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="memberParams.page" v-model:page-size="memberParams.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="memberParams.total"
                        @size-change="getManjianMemberPageListFn()" @current-change="getManjianMemberPageListFn" />
                </div>
            </div>
        </div>
    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getManjianInfo, getManjianMemberPageList } from '@/addon/shop/api/marketing'
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
    activeName.value = 'basicInfo'
    showDialog.value = false
}

const getManjianInfoFn = (id:number) => {
    loading.value = true
    const data = {
        manjian_id: id
    }
    getManjianInfo(data).then((res:any) => {
        formData.value = Object.assign(formData.value, res.data.manjian_info)
        loading.value = false
    })
}

// 活动会员
const memberParams = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        id
    }
})
const getManjianMemberPageListFn = (page: number = 1) => {
    memberParams.loading = true
    memberParams.page = page
    getManjianMemberPageList({
        page: memberParams.page,
        limit: memberParams.limit,
        ...memberParams.searchParam
    }).then((res:any) => {
        memberParams.loading = false
        memberParams.data = res.data.data
        memberParams.total = res.data.total
    }).catch(() => {
        memberParams.loading = false
    })
}

// 查看会员详情
const detailEvent = (member_id:number) => {
    const routeData = router.resolve(`/member/detail?id=${member_id}`)
    window.open(routeData.href, ' blank')
}

const setFormData = async (row: any = null) => {
    id = row.id
    memberParams.searchParam.id = row.id

    getManjianMemberPageListFn()
    getManjianInfoFn(Number(id))
}

defineExpose({
    showDialog,
    setFormData
})
</script>
<style lang="scss" scoped>
.goods-name {
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
