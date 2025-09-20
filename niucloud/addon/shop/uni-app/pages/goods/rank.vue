<template>
    <view class="min-h-[100vh]" :style="themeColor()">
        <!-- #ifdef MP-WEIXIN || APP-PLUS -->
        <top-tabbar :data="param" :isFill="false" />
        <!-- #endif -->
        <!-- 顶部图片 -->
        <view class="rank-head">
            <image class="w-[100%] h-[435rpx]" :src="img(rankConfig.rank_images)" mode="aspectFill" />
            <view class="content-box">
                <!-- 榜单分类按钮 -->
                <scroll-view scroll-x="true" class="category-slider" scroll-with-animation :scroll-into-view="'id' + activeIndex">
                    <view class="category-con" :style="{ justifyContent: centered ? 'center' : 'flex-start' }">
                        <view class="category-btn" v-for="(item, index) in rankList" :key="index" :id="'id' + index" @click="selectCategory(item, index)"
                              :style="{ color: activeIndex === index ? rankConfig.select_color : rankConfig.no_color, background: activeIndex === index ? `linear-gradient(to right, ${rankConfig.select_bg_color_start}, ${rankConfig.select_bg_color_end})`: 'transparent'}">
                            <view>{{ item.name }}</view>
                        </view>
                    </view>

                </scroll-view>
                <!-- <view class="content">
                  <text class="text-[26rpx]">{{rankConfig.rank_name}}</text>
                </view> -->
            </view>
            <view class="side-tab" :style="{ top: topStyle }" @click="rankPopup = true" v-if="rankConfig.rank_remark">
                <text class="iconfont icona-paihangbangpc30 icon"></text>
                <text class="desc">{{ t('rankingRules') }}</text>
            </view>

        </view>
        <view class="rank-list p-[20rpx] relative -mt-[42rpx]">

            <!-- 列表 -->
            <mescroll-body ref="mescrollRef" :height="listHeight" @init="mescrollInit" :down="{ use: false }" @up="getRankGoodsListFn">
                <view class="bg-[#fff] flex rounded-[var(--rounded-mid)] p-[20rpx]"
                      v-for="(item,index) in rankGoodsList" :key="item.goods_id"
                      :class="{'mb-[20rpx]': (rankGoodsList.length-1) != index}" v-if="rankGoodsList.length"
                      @click="toLink(item.goods_id)">
                    <view class="w-[240rpx] h-[240rpx] flex items-center justify-center relative">
                        <!-- 榜单排名图片 -->
                        <image v-if="index < 5" class="absolute top-[7rpx] left-[10rpx] w-[50rpx] h-[58rpx]" :style="{ zIndex:9 }" :src="getRankBadge(item.rank_num)" mode="aspectFill" />
                        <view class="absolute top-[15rpx] left-[10rpx] flex items-center justify-center w-[50rpx] h-[50rpx]" v-if="index < 5" :style="{ zIndex: 10 }">
                            <text class="text-[24rpx] font-bold text-[#fff]">{{ index + 1 }}</text>
                        </view>

                        <image v-if="item.goods_cover_thumb_mid" class="w-[250rpx] h-[250rpx] rounded-[var(--rounded-mid)]"
                               :src="img(item.goods_cover_thumb_mid)" :mode="'aspectFill'"
                               @error="item.goods_cover_thumb_mid='static/resource/images/diy/shop_default.jpg'" />
                        <image class="w-[240rpx] h-[240rpx] rounded-[var(--rounded-mid)]" v-else
                               :src="img('static/resource/images/diy/shop_default.jpg')" :mode="'aspectFill'" />

                    </view>
                    <view class="flex flex-col flex-1 justify-between ml-[20rpx] pt-[4rpx]">
                        <view class="text-[28rpx] text-[#333] leading-[40rpx] multi-hidden mb-[10rpx]">
                            <view class="brand-tag" v-if="item.goods_brand" :style="diyGoods.baseTagStyle(item.goods_brand)">
                                {{ item.goods_brand.brand_name }}
                            </view>
                            {{ item.goods_name }}
                        </view>
                        <view v-if="item.goods_label_name && item.goods_label_name.length" class="flex flex-wrap">
                            <template v-for="(tagItem, tagIndex) in item.goods_label_name">
                                <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')" />
                                <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">{{ tagItem.label_name }}</view>
                            </template>
                        </view>
                        <view class="flex items-center">
                            <view class="text-[var(--price-text-color)] price-font flex items-baseline">
                                <text class="text-[24rpx] font-500">￥</text>
                                <text class="text-[40rpx] font-500">{{ diyGoods.goodsPrice(item).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[24rpx] font-500">.{{ diyGoods.goodsPrice(item).toFixed(2).split('.')[1] }}</text>
                            </view>
							<image v-if="diyGoods.priceType(item) == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
							<image v-else-if="diyGoods.priceType(item) == 'newcomer_price'"  class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
							<image v-else-if="diyGoods.priceType(item) == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />
                            <view :id="'itemCart' + index" class="w-[102rpx] box-border ml-auto text-center text-[#fff] primary-btn-bg h-[46rpx] text-[22rpx] leading-[46rpx] rounded-[100rpx]">去购买</view>
                        </view>
                    </view>
                </view><mescroll-empty v-if="!rankGoodsList.length && loading" :option="{tip : '暂无商品', btnText:'去逛逛'}"
                                @emptyclick="redirect({ url: '/addon/shop/pages/goods/list'})"></mescroll-empty>
            </mescroll-body>

            <view @touchmove.prevent.stop>
                <u-popup :show="rankPopup" @close="closeFn" mode="center" round="var(--rounded-big)">
                    <view class="w-[570rpx] px-[32rpx] popup-common center">
                        <view class="title">{{ t('rankingRules') }}</view>
                        <scroll-view :scroll-y="true" class="px-[30rpx] box-border max-h-[260rpx]">
                            <view class="text-[28rpx] leading-[40rpx] mb-[20rpx]">{{ rankConfig.rank_remark }}</view>
                        </scroll-view>
                        <view class="btn-wrap !pt-[40rpx]">
                            <button class="primary-btn-bg w-[480rpx] h-[70rpx] text-[26rpx] leading-[70rpx] rounded-[35rpx] !text-[#fff] font-500" @click="rankPopup = false">我知道了</button>
                        </view>
                    </view>
                </u-popup>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { reactive, ref, computed, nextTick } from 'vue'
import { t } from '@/locale'
import { redirect, img, pxToRpx } from '@/utils/common';
import { getRankList, getRankGoodsList, getRankConfig } from '@/addon/shop/api/rank';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import { onLoad, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import { topTabar } from '@/utils/topTabbar'
import { useGoods } from '@/addon/shop/hooks/useGoods'
import useSystemStore from "@/stores/system";

const systemStore = useSystemStore()
const diyGoods = useGoods();
const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const mescrollRef = ref(null);
const loading = ref<boolean>(false);
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '' })
const topStyle = computed(() => {
    let style = pxToRpx(Number(systemStore.menuButtonInfo.height) + systemStore.menuButtonInfo.top + 8) + 30 + 'rpx;'
    return style
})
/********* 自定义头部 - end ***********/

// 获取系统信息
const topImageHeight = 450;
const screenHeight = systemStore.systemInfo.windowHeight;
// 将屏幕高度转换为 rpx
const screenHeightInRpx = (screenHeight / systemStore.systemInfo.screenWidth) * 750;
// 计算列表高度
const listHeight = computed(() => {
    const listHeightValue = screenHeightInRpx - topImageHeight;
    return `${ listHeightValue }rpx`;
});
/**************** 榜单规则 ********************/
const rankPopup = ref(false)
const closeFn = () => {
    rankPopup.value = false
}
const rankList = ref<Array<any>>([]);
const rankGoodsList = ref<Array<any>>([]);

const centered = ref(false); // 是否居中
const calculateCentered = () => {
    nextTick(() => {
        const query = uni.createSelectorQuery();
        query.selectAll('.category-btn').boundingClientRect((rects) => {
            if (rects && rects.length > 0) {
                const totalMarginRight = uni.upx2px(20) * (rects.length - 1); // 记得把 rpx 转成 px
                const totalWidth = rects.reduce((sum, rect) => sum + rect.width, 0) + totalMarginRight;
                const screenWidth = systemStore.systemInfo.windowWidth;
                centered.value = totalWidth <= screenWidth * 0.93; // 判断是否需要居中
            } else {
                console.error('Failed to get .category-btn elements.');
            }
        }).exec();
    });
};

// 加载分类数据
const getRankListFn = (isFirstLoad = false) => {
    getRankList().then((res) => {
        rankList.value = res.data
        // 仅在首次加载时选择第一个分类
        if (rankId.value) {
            for (let i = 0; i < rankList.value.length; i++) {
                if (rankId.value == rankList.value[i].rank_id) {
                    selectCategory(rankList.value[i], i);
                    break;
                }
            }
        } else if (isFirstLoad && rankList.value && rankList.value.length) {
            selectCategory(rankList.value[0], 0);
        } else if (!rankList.value.length) {
            loading.value = true;
        }
        calculateCentered();

    }).catch((error) => {
        console.error("加载分类数据失败", error);
    })
};

const rankConfig = reactive({});

// 榜单设置
const getRankConfigFn = () => {
    getRankConfig().then((res: any) => {
        Object.assign(rankConfig, res.data); // 合并新数据
    });
};

// 当前选中的分类的索引
const activeIndex = ref(0)
// 当前选中的分类的rank_id
const rankId = ref(0)
// 当前选中的分类的goods_source
const goodsSource = ref()

// 点击分类按钮时，更新选中的分类
function selectCategory(rank: any, index: any) {
    loading.value = false;
    // 清空之前选中的商品列表
    rankGoodsList.value = [];
    activeIndex.value = index
    rankId.value = rank.rank_id
    goodsSource.value = rank.goods_source
    getMescroll()?.resetUpScroll();
}

//获取榜单商品
const getRankGoodsListFn = (mescroll: any) => {
    if (rankList.value.length == 0) return;
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        rank_id: rankId.value
    };

    getRankGoodsList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>).map((el: any) => {
            return el
        })
        let ifPage = true
        //设置列表数据
        if (mescroll.num == 1) {
            rankGoodsList.value = []; //如果是第一页需手动制空列表
        }
        rankGoodsList.value = rankGoodsList.value.concat(newArr);
        if (goodsSource.value == 'goods') {
            ifPage = false
        } else {
            ifPage = true
        }
        mescroll.endSuccess(newArr.length, ifPage);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

// 跳转商品详情
const toLink = (goods_id: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id } })
}

// 获取排行榜名次图片的函数
function getRankBadge(sort: any) {
    switch (sort) {
        case 1:
            return img('addon/shop/rank/rank_first.png');
        case 2:
            return img('addon/shop/rank/rank_second.png');
        case 3:
            return img('addon/shop/rank/rank_third.png');
        default:
            return img('addon/shop/rank/rank.png');
    }
}

onLoad(async(option: any) => {
    rankId.value = option.rank_id || 0;
    getRankConfigFn()
    getRankListFn(true)
})

</script>

<style lang="scss" scoped>
@import '@/addon/shop/styles/common.scss';

.rank-head {
    position: relative;

    .content-box {
        width: 100%;
        position: absolute;
        top: 328rpx;

        .category-slider {
            width: 95%;
            margin: 0 auto;
            line-height: 100rpx;
            white-space: nowrap;
            flex-direction: row;
            justify-content: center;
            align-items: center;

            .category-con {
                width: 100%;
                display: flex;
                align-items: center;

                .category-btn {
                    display: inline-block;
                    padding: 0 20rpx;
                    height: 55rpx;
                    text-align: center;
                    line-height: 55rpx;
                    justify-content: center;
                    align-items: center;
                    border-radius: 40rpx;
                    font-size: 24rpx;
                    margin-right: 20rpx;
                }
            }

        }

        // 	.content {
        // 		display: flex;
        // 		justify-content: center;
        // 		align-items: center;
        // 		border-radius: 30rpx;
        // 		padding: 0 20rpx;
        // 		height: 44rpx;
        // 		font-size: 26rpx;
        // 		color: var(--primary-color);
        // 		background: linear-gradient(to right, #FFEBD7, #FFFFFF, #FFEBD7);
        // }
    }

}

.rank-list {
    background: #F8F8F8;
    border-radius: 34rpx 34rpx 0 0;

    .bank-buying {
        width: 100rpx;
        height: 44rpx;
        border-radius: 10rpx;
        font-family: PingFang SC;
        font-weight: 500;
        font-size: 24rpx;
        color: #FFFFFF;
        line-height: 44rpx;
        text-align: center;
    }
}
</style>
