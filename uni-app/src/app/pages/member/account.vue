<template>
    <view class="w-screen h-screen bg-[var(--page-bg-color)]" :style="themeColor()">
        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" @up="getCashOutAccountListFn">
            <!-- 多嵌套一层是为了微信小程序兼容 -->
            <view class="sidebar-margin my-[var(--top-m)] rounded-[var(--rounded-big)] overflow-hidden"
                  v-for="(item, index) in accountList" :key="index">
                <u-swipe-action-item :options="accountOptions" @click="swipeClick(index)">
                    <view class="card-template flex justify-between">
                        <view class="flex items-center">
                            <view class="w-[80rpx] h-[80rpx] flex items-center justify-center" @click="handleClick(item)">
                                <image class="w-[80rpx] h-[52rpx] align-middle" :src="img('static/resource/images/member/apply_withdrawal/bank-icon.png')" mode="widthFix" v-if="item.account_type == 'bank'" />
                                <image class="w-[80rpx] h-[52rpx] align-middle" :src="img('static/resource/images/member/apply_withdrawal/wechat_code.png')" mode="widthFix" v-else-if="item.account_type == 'wechat_code'" />
                                <image class="h-[78rpx] w-[78rpx] align-middle" v-else :src="img('static/resource/images/member/apply_withdrawal/alipay-icon.png')" mode="widthFix" />
                            </view>
                            <view class="flex flex-col ml-[20rpx]" @click="handleClick(item)">
                                <view class="text-[#333] text-[28rpx]">{{ item.account_type == 'bank' ? item.bank_name : item.account_type == 'wechat_code' ? t('wechatCode') : t('alipayAccountNo') }}</view>
                                <view v-if="item.account_type == 'bank'" class="text-[var(--text-color-light9)] text-[24rpx] mt-[12rpx]">{{ t('endNumber') }} {{ item.account_no.substring(item.account_no.length - 4) }}{{ t('bankCard') }}</view>
                                <view v-else class="text-[var(--text-color-light9)] text-[24rpx] mt-[12rpx]">{{ item.account_no }}</view>
                            </view>
                        </view>

                        <text class="flex items-center nc-iconfont nc-icon-xiugaiV6xx flex-shrink-0 text-[28rpx] p-[20rpx] pr-0" @click="editAccount(item)"></text>
                    </view>
                </u-swipe-action-item>
            </view>
            <view class="card-template sidebar-margin my-[var(--top-m)] flex items-center" @click="redirect({ url: '/app/pages/member/account_edit', param: { type: accountType, mode } })">
                <text class="nc-iconfont nc-icon-jiahaoV6xx text-[30rpx]"></text>
                <text class="text-[28rpx] ml-[10rpx] flex-1">{{ accountType == 'bank' ? t('addBankCard') : accountType == 'wechat_code' ? t('addWechatCode') : t('addAlipayAccount') }}</text>
                <text class="nc-iconfont nc-icon-youV6xx text-[24rpx] text-[var(--text-color-light6)]"></text>
            </view>
        </mescroll-body>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { redirect, img } from '@/utils/common'
import { getCashOutAccountList, deleteCashoutAccount } from '@/app/api/member'
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue'
import useMescroll from '@/components/mescroll/hooks/useMescroll.js'
import { onPageScroll, onReachBottom, onLoad } from '@dcloudio/uni-app'
import { t } from '@/locale'

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom)
const accountList = ref<Array<any>>([])
const loading = ref(false)
const accountType = ref('bank')
const mescrollRef = ref(null)
const mode = ref('get')

onLoad((data: any) => {
    data.type && (accountType.value = data.type)
    data.mode && (mode.value = data.mode)
})

const getCashOutAccountListFn = (mescroll: any) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        account_type: accountType.value
    };

    getCashOutAccountList(data).then((res: any) => {
        const newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (mescroll.num == 1) {
            accountList.value = []; //如果是第一页需手动制空列表
        }
        accountList.value = accountList.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const editAccount = (item: any) => {
    const url = `/app/pages/member/account_edit`
    redirect({ url, param: { id: item.account_id, type: item.account_type, mode: mode.value } })
}

const accountOptions = ref([
    {
        text: t('delete'),
        style: {
            backgroundColor: '#F56C6C'
        }
    }
])

const swipeClick = (index: any) => {
    const data = accountList.value[index]
    deleteCashoutAccount(data.account_id).then(() => {
        accountList.value.splice(index, 1)
    }).catch()
}

const handleClick = (data: AnyObject) => {
    if (mode.value == 'get') redirect({
        url: '/app/pages/member/account_edit',
        param: { id: data.account_id, type: accountType.value, mode: mode.value },
        mode: 'redirectTo'
    })
    else redirect({
        url: '/app/pages/member/apply_cash_out',
        param: { account_id: data.account_id, type: accountType.value },
        mode: 'redirectTo'
    })
}
</script>

<style lang="scss" scoped>
:deep(.u-swipe-action-item__right__button__wrapper) {
    padding: 0 10rpx !important;
}

:deep(.u-swipe-action-item__right__button__wrapper__text) {
    font-size: 24rpx !important;
}
</style>
