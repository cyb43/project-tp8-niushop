<template>
    <view :style="themeColor()">
        <view class="flex items-center px-[20rpx] h-[120rpx]">
            <view class="h-[68rpx] bg-[var(--temp-bg)] px-[30rpx] flex items-center rounded-[100rpx] flex-1">
                <text class="nc-iconfont nc-icon-sousuo-duanV6xx1 text-[var(--text-color-light9)] text-[26rpx] mr-[18rpx]"></text>
                <input class="text-[28rpx] flex-1" maxlength="50" type="text" v-model="inputValue"
                       :placeholder="config.default_word ? config.default_word : '请搜索您想要的商品'"
                       confirm-type="search"
                       placeholderClass="text-[var(--text-color-light9)] text-[28rpx]" @confirm="search">
                <text v-if="inputValue"
                      class="nc-iconfont nc-icon-cuohaoV6xx1 text-[24rpx] text-[var(--text-color-light9)]" @click="inputValue = ''"></text>
            </view>
            <text @click.stop="search()" class="text-[26rpx] ml-[32rpx] -mb-[2rpx]">搜索</text>
        </view>

        <view class="search-content">
            <!-- 历史搜索 -->
            <view class="history" v-if="historyList.length">
                <view class="history-box">
                    <view class="history-top">
                        <view class="title font-500">历史搜索</view>
                        <view class="icon nc-iconfont nc-icon-shanchu-yuangaizhiV6xx !text-[24rpx] text-[var(--text-color-light6)]" @click="deleteHistoryList"></view>
                    </view>
                    <view class="history-bottom " id="history-list" :style="{ maxHeight: !isAllHistory ? '100%' : '148rpx' }">
                        <template v-for="(item, index) in historyList" :key="index">
                            <view class="history-li" @click="otherSearch(item)" v-if="item">
                                <view>{{ item }}</view>
                            </view>
                        </template>
                        <view class="history-li history_more" v-if="isAllHistory" @click="isAllHistory = false">
                            <view class="content-box">
                                <text class="text-[30rpx] nc-iconfont nc-icon-xiaV6xx"></text>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="history" v-if="config.search_words && config.search_words.length">
                <view class="history-box">
                    <view class="history-top">
                        <view class="title font-500">热门搜索</view>
                    </view>
                    <view class="history-bottom">
                        <template v-for="(item, index) in config.search_words" :key="index">
                            <view class="history-li" @click="otherSearch(item)" v-if="item">
                                <view>{{ item }}</view>
                            </view>
                        </template>
                    </view>
                </view>
            </view>
        </view>
<!--        <view class="px-[20rpx] pt-[10rpx]">-->
<!--            <diy-shop-goods-ranking :component="rankingComponent" />-->
<!--        </view>-->
    </view>
</template>
<script setup lang="ts">
import { onLoad, onShow } from '@dcloudio/uni-app'
import { ref, nextTick } from 'vue';
import { redirect } from '@/utils/common';
import { getGoodsConfigSearch } from '@/addon/shop/api/goods';
import useConfigStore from "@/stores/config";
import diyShopGoodsRanking from '@/addon/shop/components/diy/shop-goods-ranking/index.vue';

const inputValue = ref('') //搜索框的值
const historyList = ref([]) //历史搜索记录
const isAllHistory = ref(false)
const config = ref<any>({})

onLoad((options: any) => {
    uni.getStorageSync('goodsSearchHistory') ? '' : uni.setStorageSync('goodsSearchHistory', []);
});

onShow(() => {
    inputValue.value = ''
    findHistoryList()
    getGoodsConfigSearchFn()
    nextTick(() => {
        getHistoryHeight();
    });
})

//获取历史搜索记录
const findHistoryList = () => {
    historyList.value = uni.getStorageSync('goodsSearchHistory').reverse();
}

// 删除所有历史记录
const deleteHistoryList = () => {
    uni.showModal({
        title: '提示',
        content: '确认删除全部历史记录？',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: res => {
            if (res.confirm) {
                uni.setStorageSync('goodsSearchHistory', []);
                findHistoryList();
            }
        }
    });
}

const getGoodsConfigSearchFn = () => {
    getGoodsConfigSearch().then((res: any) => {
        config.value = res.data;
        if (config.value.search_words) {
            config.value.search_words = config.value.search_words.filter((word: string) => word && word.trim() !== '');
        }
    })
}

//搜索
const search = () => {
    // if (inputValue.value.trim() != '') {
	if (config.value.default_word && inputValue.value.trim() == '') inputValue.value = config.value.default_word;
    // 对历史搜索处理,判断有无,最近搜索显示在最前
    let historyList = uni.getStorageSync('goodsSearchHistory');
    let array = [];
    if (historyList.length) {
        array = historyList.filter(v => {
            return v != inputValue.value.trim();
        });
        if(inputValue.value.trim()) array.push(inputValue.value.trim());
    } else if(inputValue.value.trim()) {
        array.push(inputValue.value.trim());
    }
    uni.setStorageSync('goodsSearchHistory', array);

    redirect({
        url: '/addon/shop/pages/goods/list',
        param: { goods_name: encodeURIComponent(inputValue.value) },
        mode: 'navigateTo'
    })
    // }
}

// 点击历史搜索
const otherSearch = (e: any) => {
    inputValue.value = e;
    search();
}

// 获取元素高度
const getHistoryHeight = () => {
    const query = uni.createSelectorQuery().in(this);
    query.select('#history-list').boundingClientRect((data: any) => {
        if (data && data.height > uni.upx2px(70) * 2 + uni.upx2px(35) * 2) {
            isAllHistory.value = true;
        }
    }).exec();
}

// 排行榜初始数据
const rankingComponent = {
    "list": [
        {
            "bgUrl": "addon/shop/rank/rank_bg_01.jpg",
            "text": "热销排行榜",
            "textColor": "#FFFFFF",
            "imgUrl": "addon/shop/rank/rank_trophy.png",
            "subTitle": {
                "text": "查看更多",
                "textColor": "#FFFFFF",
                "link": {
                    "name": "SHOP_GOODS_RANK",
                    "parent": "SHOP_LINK",
                    "title": "商品排行榜",
                    "url": "/addon/shop/pages/goods/rank",
                    "action": ""
                }
            },
            "listFrame": {
                "startColor": "#FEA715",
                "endColor": "#FE1E00"
            },
            "source": "default",
            "rankIds": []
        }
    ]
}

</script>
<style lang="scss" scoped>
.content {
    width: 100vw;
    /* #ifdef MP */
    height: 100vh;
    /* #endif */
    /* #ifdef H5 */
    height: calc(100vh - env(safe-area-inset-bottom) - var(--status-bar-height));
    height: calc(100vh - constant(safe-area-inset-bottom) - var(--status-bar-height));
    /* #endif */
    /* #ifdef APP-PLUS */
    height: calc(100vh - 44px - env(safe-area-inset-bottom));
    height: calc(100vh - 44px - constant(safe-area-inset-bottom));
    /* #endif */
    background: #ffffff;
}

.search-content {
    box-sizing: border-box;
    background: #ffffff;
}

.history {
    width: 100%;
    box-sizing: border-box;

    .history-box {
        width: 100%;
        height: 100%;
        background: #ffffff;
        padding: 16rpx 20rpx 0rpx 20rpx;

        box-sizing: border-box;
        overflow: hidden;

        .history-top {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;

            .title {
                font-size: 28rpx;
            }

            .iconfont {
                color: var(--text-color-light9);
                font-size: 28rpx;
            }
        }

        .history-bottom {
            width: 100%;
            padding-top: 20rpx;
            position: relative;

            .history-li {
                display: inline-block;
                margin-right: 20rpx;
                margin-bottom: 15rpx;
                max-width: 100%;

                view {
                    line-height: 56rpx;
                    background: var(--temp-bg) !important;
                    height: 56rpx;
                    color: #333 !important;
                    margin: 0 0rpx 4rpx 0 !important;
                    padding: 0 24rpx;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    border-radius: 100rpx;
                    font-size: 24rpx;
                }

                &.history_more {
                    margin-right: 0;
                    position: absolute;
                    bottom: 0;
                    right: 0;

                    .content-box {
                        box-shadow: 2rpx 2rpx 8rpx rgba(0, 0, 0, .1);
                    }
                }
            }
        }
    }
}
</style>
