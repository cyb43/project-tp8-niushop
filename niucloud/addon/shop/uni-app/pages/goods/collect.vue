<template>
    <view :style="themeColor()">
        <view class="bg-page min-h-screen overflow-hidden ">
            <mescroll-body ref="mescrollRef" top="0"  @init="mescrollInit" :down="{ use: false }" @up="getCollectListFn">
                <view class="py-[var(--top-m)] sidebar-margin" v-if="goodsList.length">
                    <view class="bg-[#fff] pb-[10rpx] box-border rounded-[var(--rounded-big)]" >
                        <view class="flex mx-[var(--rounded-big)] pt-[var(--pad-top-m)] justify-between items-center box-border font-400 text-[24rpx] mb-[24rpx] leading-[30rpx]">
                            <view class="flex items-baseline text-[24rpx] text-[#333]">
                                <text>共</text>
                                <text class="text-[32rpx] mx-[2rpx] text-[var(--price-text-color)]">{{ goodsList.length }}</text>
                                <text>件商品</text>
                            </view>
                            <text @click="isEdit = !isEdit" class="text-[var(--text-color-light6)] text-[24rpx]">{{ isEdit ? '完成' : '管理' }}</text>
                        </view>
                        <u-swipe-action ref="swipeActive">
                            <template v-for="(item, index) in goodsList" :key="index">
                                <view class="py-[20rpx] overflow-hidden w-full">
                                    <u-swipe-action-item :options="cartOptions" @click="swipeClick(item)">
                                        <view class="flex px-[var(--pad-sidebar-m)]">
                                            <view class="self-center w-[58rpx] h-[60rpx] flex items-center" v-if="isEdit" @click.stop="item.checked = !item.checked">
                                                <text class=" iconfont text-primary text-[34rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden shrink-0" :class="{ 'iconxuanze1':item.checked,'bg-[#F5F5F5]':!item.checked}"></text>
                                            </view>
                                            <view  class="flex flex-1" @click="toDetail(item)">
                                                <view class="relative w-[200rpx] h-[200rpx] flex items-center justify-center rounded-[var(--goods-rounded-big)] overflow-hidden">
                                                    <u--image radius="var(--goods-rounded-big)" width="200rpx" height="200rpx" :src="img(item.goods_cover_thumb_mid||'')" model="aspectFill">
                                                        <template #error>
                                                            <image class="w-[200rpx] h-[200rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                                        </template>
                                                    </u--image>
                                                    <view v-if="item.status == 0 " class="absolute left-0 top-0 w-[200rpx] h-[200rpx]  leading-[200rpx] text-center " style="background-color: rgba(0,0,0,0.3);">
                                                        <text class="text-[#fff] text-[28rpx]">已失效</text>
                                                    </view>
                                                </view>
                                                <view class="flex flex-1 flex-wrap ml-[20rpx]">
                                                    <view class="w-[100%] flex flex-col items-baseline">
                                                        <view class="text-[#333] text-[28rpx] max-h-[80rpx] leading-[40rpx] multi-hidden font-400">{{ item.goods_name }}</view>
                                                        <view class="box-border max-w-[376rpx] mt-[10rpx] px-[14rpx] h-[36rpx] leading-[36rpx] truncate text-[var(--text-color-light6)] bg-[#F5F5F5] text-[22rpx] rounded-[20rpx]" v-if="item.sku_name">{{ item.sku_name }}</view>
                                                    </view>
                                                    <view class="flex justify-between items-end self-end mt-[10rpx] w-[100%]">
                                                        <view class="text-[var(--price-text-color)] price-font truncate max-w-[200rpx]">
                                                            <text class="text-[24rpx] font-500">￥</text>
                                                            <text class="text-[40rpx] font-500">{{ parseFloat(item.show_price).toFixed(2).split('.')[0] }}</text>
                                                            <text class="text-[24rpx] font-500">.{{ parseFloat(item.show_price).toFixed(2).split('.')[1] }}</text>
                                                            <image v-if="item.show_type == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
                                                            <image v-else-if="item.show_type  == 'newcomer_price'" class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
                                                            <image v-else-if="item.show_type  == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />	
                                                        </view>
                                                    </view>
                                                </view>
                                            </view>
                                        </view>
                                    </u-swipe-action-item>
                                </view>
                            </template>
                        </u-swipe-action>
                    </view>
                </view>
                <mescroll-empty v-if="!goodsList.length && loading" :option="{tip : '暂无收藏的商品'}"></mescroll-empty>
            </mescroll-body>
            <view v-if="goodsList.length && isEdit" class="flex h-[96rpx] items-center bg-[#fff] fixed left-0 right-0 bottom-0 pl-[30rpx] pr-[20rpx] box-solid mb-ios justify-between">
                <view class="flex items-center" @click="selectAll">
                    <text class="self-center iconfont text-primary text-[34rpx] mr-[10rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden flex-shrink-0" :class="{'iconxuanze1': goodsList.length == checkedNum, 'bg-color': goodsList.length != checkedNum }"></text>
                    <text class="font-400 text-[#303133] text-[26rpx]">全选</text>
                </view>
                <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border" @click="deleteCollectFn">取消收藏</button>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed,} from 'vue'
import {t} from "@/locale";
import { img, redirect } from '@/utils/common'
import { getCollectList, cancelCollect } from '@/addon/shop/api/goods';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onPageScroll, onReachBottom } from '@dcloudio/uni-app';

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const loading = ref<boolean>(false);
const optionLoading = ref(false)
const goodsList = ref<Array<any>>([]);
const isEdit = ref(false)

interface mescrollStructure {
	num: number,
	size: number,
	endSuccess: Function,
	[propName: string]: any
}
const getCollectListFn = (mescroll: mescrollStructure) => {
    loading.value = false
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size
    };
    getCollectList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (Number(mescroll.num) === 1) {
            goodsList.value = []; //如果是第一页需手动制空列表
        }
        newArr = newArr.map((item: any) => {
            item.checked = false
            return item
        })
        goodsList.value = goodsList.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const cartOptions = ref([
    {
        text: t('delete'),
        style: {
            backgroundColor: '#EF000C',
            width: '100rpx',
            height: '100%',
            borderRadius: '10rpx'
        }
    }
]);

// 取消单个收藏
const swipeClick = (data: any) => {
    if (optionLoading.value) return
    optionLoading.value = true
    cancelCollect({ goods_ids: [data.goods_id] }).then((res: any) => {
        optionLoading.value = false
        getMescroll().resetUpScroll();
    })
}

//取消全部收藏
const  deleteCollectFn = () => {
    if (!checkedNum.value) {
        uni.showToast({ title: '请先选择收藏的商品', icon: 'none' })
        return
    }
    if (optionLoading.value) return
    optionLoading.value = true
    const ids: any = []
    goodsList.value.forEach((item: any) => {
        if (item.checked) ids.push(item.goods_id)
    })
    cancelCollect({ goods_ids: ids }).then((res: any) => {
        optionLoading.value = false
        getMescroll().resetUpScroll();
    })
}

// 选中数量
const checkedNum = computed(() => {
    let num = 0
    goodsList.value.forEach((item: any) => {
        item.checked && (num += 1)
    })
    return num
})

/**
 * 全选
 */
 const selectAll = () => {
    const checked = goodsList.value.length == checkedNum.value ? false : true
    goodsList.value.forEach((item: any) => {
        item.checked = checked
    })
}

const toDetail = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: data.goods_id } })
}
</script>

<style lang="scss" scoped>
.bg-color{
	background-color: #f5f5f5;
}
:deep(.u-swipe-action-item__right){
	padding: 2rpx;
}
:deep(.u-swipe-action-item__right__button__wrapper){
    padding:0 10rpx !important;
}
:deep(.u-swipe-action-item__right__button__wrapper__text){
    font-size:24rpx !important;
}
</style>
