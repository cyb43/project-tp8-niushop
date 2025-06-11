<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" :style="themeColor()">
        <view class="fixed top-0 left-0 right-0 z-200">
            <view class="tab-style-1 py-[20rpx] bg-[#fff] border-0  border-solid border-b-[1rpx] border-[#f6f6f6] ">
                <view class="tab-left text-[28rpx]">
                    <text>共</text>
                    <text class="text-primary">{{ browseTotal }}</text>
                    <text>条</text>
                </view>
                <view class="tab-right !items-center">
                    <view class="flex items-center" @click="handleSelect">
                        <view class="tab-right-date">日期</view>
                        <view class="nc-iconfont nc-icon-a-riliV6xx-36 tab-right-icon"></view>
                    </view>
                    <view class="w-[2rpx] h-[28rpx] mx-[20rpx] bg-gradient-to-b from-[#333] to-[#fff]"></view>
                    <view @click="isEdit = !isEdit" class="text-[#333] text-[28rpx]">{{ isEdit ? '完成' : '管理' }}</view>
                </view>
            </view>
        </view>
        <mescroll-body ref="mescrollRef" top="76" bottom="168" @init="mescrollInit" :down="{ use: false }" @up="getBrowseListFn">
            <view v-if="browseList.length">
                <view class="bg-[#fff] mb-[20rpx] pt-[30rpx] px-[20rpx]" v-for="(item,index) in browseList" :key="index">
                    <view class="flex items-center h-[34rpx]  mb-[20rpx]">
                        <view class="self-center w-[58rpx]  flex items-center" v-if="isEdit" @click.stop="isSelectGroup(item)">
                            <view class="bg-[#fff] w-[34rpx] h-[34rpx] rounded-[17rpx] flex items-center justify-center">
                                <text class=" iconfont text-primary text-[34rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden shrink-0"
                                    :class="{ 'iconxuanze1':item.checked,'bg-[#F5F5F5]':!item.checked}"></text>
                            </view>
                        </view>
                        <view class="text-[28rpx] font-500 text-[#333] ">{{ item.date }}</view>
                    </view>
                    <view class="flex flex-wrap">
                        <view class="w-[230rpx] mb-[20rpx]" :class="{'mr-[10rpx]': (subIndex + 1) % 3 }" v-for="(subItem,subIndex) in item.list" :key="subIndex" @click="toDetail(subItem)">
                            <view class="relative w-[230rpx] h-[230rpx] rounded-[var(--goods-rounded-mid)] overflow-hidden mb-[10rpx]">
                                <u--image width="230rpx" height="230rpx" :radius="'var(--goods-rounded-mid)'" :src="img(subItem.goods_cover_thumb_mid ? subItem.goods_cover_thumb_mid : '')" mode="aspectFill">
                                    <template #error>
                                        <image class="w-[230rpx] h-[230rpx] rounded-[var(--goods-rounded-mid)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                                <view v-if="subItem.status == 0 " class="absolute left-0 top-0  w-[230rpx] h-[230rpx]  leading-[230rpx] text-center " style="background-color: rgba(0,0,0,0.3);">
                                    <text class="text-[#fff] text-[28rpx]">已失效</text>
                                </view>
                                <view class="absolute top-0 left-0 right-0 bottom-0 p-[10rpx] flex justify-end items-start z-100"
                                    v-if="isEdit" @click.stop="changeItem(item,subItem)">
                                    <view class="bg-[#fff] w-[34rpx] h-[34rpx] rounded-[17rpx] flex items-center justify-center">
                                        <text class="iconfont text-primary text-[34rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden shrink-0"
                                            :class="{ 'iconxuanze1':subItem.checked,'bg-[#F5F5F5]':!subItem.checked}"></text>
                                    </view>
                                </view>
                            </view>
                            <view class="text-[var(--price-text-color)] price-font">
                                <text class="text-[24rpx] font-500">￥</text>
                                <text class="text-[40rpx] font-500">{{ parseFloat(subItem.show_price).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[24rpx] font-500">.{{ parseFloat(subItem.show_price).toFixed(2).split('.')[1] }}</text>
                                <image v-if="subItem.show_type == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
                                <image v-else-if="subItem.show_type  == 'newcomer_price'" class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
                                <image v-else-if="subItem.show_type  == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <mescroll-empty v-if="!browseList.length && loading" :option="{tip : '暂无浏览的商品'}"></mescroll-empty>
        </mescroll-body>
        <view v-if="browseList.length && isEdit" class="fixed left-0 right-0 bottom-0 z-200 bg-[#fff]  pb-ios">
            <view v-if="checkedNum"
                  class="h-[66rpx] flex items-center justify-between pl-[30rpx] pr-[20rpx] border-0  border-b-[1rpx] border-solid border-[#f6f6f6]">
                <view class="text-[24rpx]">
                    <text>已选</text>
                    <text class="text-primary">{{ checkedNum }}</text>
                    <text>件宝贝</text>
                </view>
                <view class="text-[24rpx] text-[#999]" @click="clearBrowseFn">一键清空宝贝足迹</view>
            </view>
            <view class="flex h-[100rpx] items-center  justify-between pl-[30rpx] pr-[20rpx]">
                <view class="flex items-center" @click="allChange">
                    <text class="self-center iconfont text-primary text-[34rpx] mr-[10rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden flex-shrink-0"
                        :class="{'iconxuanze1': isSelectAll, 'bg-color': !isSelectAll } "></text>
                    <text class="font-400 text-[#303133] text-[26rpx]">全选</text>
                </view>
                <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border"
                    @click="deleteBrowseFn">删除</button>
            </view>
        </view>

        <!-- 时间选择 -->
        <select-date ref="selectDateRef" @confirm="confirmFn" />
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { img, redirect } from '@/utils/common';
import { getBrowse, delBrowse } from '@/addon/shop/api/goods';
import selectDate from '@/components/select-date/select-date.vue';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onPageScroll, onReachBottom } from '@dcloudio/uni-app';

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);

const isEdit = ref(false)
let loading = ref<boolean>(false);
const optionLoading = ref(false)
const browseTotal = ref<number>(0);
let browseList = ref<any>([]);
const create_time = ref([])
const getBrowseListFn = (mescroll: any) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        date: create_time.value
    };
    getBrowse(data).then((res: any) => {
        browseTotal.value = res.data.total;
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (Number(mescroll.num) === 1) {
            browseList.value = []; //如果是第一页需手动制空列表
        }
        // 按日期分组
        const groupedData = newArr.reduce((acc: any, item: any) => {
            const date = item.browse_time.split(' ')[0]; // 提取日期部分
            if (!acc[date]) {
                acc[date] = [];
            }
            acc[date].push(item);
            return acc;
        }, {});

        // 转换为所需格式
        const formattedData = Object.keys(groupedData).map(date => ({
            date: date,
            list: groupedData[date]
        }));
        formattedData.forEach((item: any) => {
            item.checked = false;
            item.list.forEach((subItem: any) => {
                subItem.checked = false; // 初始化选中状态
            });
        });
        // 合并相同日期的数据
        formattedData.forEach((newItem: any) => {
            const existingItemIndex = browseList.value.findIndex((item: any) => item.date === newItem.date);
            if (existingItemIndex !== -1) {
                // 合并到已有的日期数据中
                browseList.value[existingItemIndex].list = [...browseList.value[existingItemIndex].list, ...newItem.list];
            } else {
                // 新增日期数据
                browseList.value.push(newItem);
            }
        });
        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

// 选择数量
const checkedNum = computed(() => {
    let num = 0
    browseList.value.forEach((item: any) => {
        item.list.forEach((subItem: any) => {
            subItem.checked && (num += 1)
        })
    })
    return num
})

/**
 * 全选
 */
//判断是否全选状态
const isSelectAll = ref(false)
const isallclick = () => {
    const isActive = browseList.value.every((item: any) => {
        return item.checked
    })
    if (isActive) {
        isSelectAll.value = true
    } else {
        isSelectAll.value = false
    }
}
// 选择每天的
const isSelectGroup = (data: any) => {
    data.checked = !data.checked
    data.list.forEach((item: any) => {
        item.checked = data.checked
    })
    isallclick()
}
// 全选
const allChange = () => {
    isSelectAll.value = !isSelectAll.value
    browseList.value.forEach((item: any) => {
        item.checked = isSelectAll.value
        item.list.forEach((subItem: any) => {
            subItem.checked = isSelectAll.value
        })
    })
}
// 选择单个商品
const changeItem = (data: any, value: any) => {
    value.checked = !value.checked
    const isActive = data.list.every((item: any) => {
        return item.checked
    })
    if (isActive) {
        data.checked = true
    } else {
        data.checked = false
    }
    isallclick()
}

// 删除
const deleteBrowseFn = () => {
    if (!checkedNum.value) {
        uni.showToast({ title: '还没有选择商品', icon: 'none' })
        return
    }
    if (optionLoading.value) return
    optionLoading.value = true

    const ids: any = []
    browseList.value.forEach((item: any) => {
        item.list.forEach((subItem: any) => {
            subItem.checked && ids.push(subItem.goods_id)
        })
    })
    delBrowse({ goods_ids: ids }).then((res: any) => {
        optionLoading.value = false
        getMescroll().resetUpScroll();
    })
}

const clearBrowseFn = () => {
    if (optionLoading.value) return
    optionLoading.value = true

    const ids: any = []
    browseList.value.forEach((item: any) => {
        item.list.forEach((subItem: any) => {
            ids.push(subItem.goods_id)
        })
    })

    delBrowse({ goods_ids: ids }).then((res: any) => {
        getMescroll().resetUpScroll();
        optionLoading.value = false
    })
}
//日期筛选
const selectDateRef = ref<any>(null)
const handleSelect = () => {
    selectDateRef.value.show = true
}
// 确定时间筛选
const confirmFn = (data: any) => {
    create_time.value = data;
    browseList.value = []
    getMescroll().resetUpScroll();
}

const toDetail = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: data.goods_id } })
}
</script>

<style lang="scss" scoped>
.bg-color {
    background-color: #f5f5f5;
}
</style>
