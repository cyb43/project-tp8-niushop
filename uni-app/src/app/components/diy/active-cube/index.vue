<template>
    <view :style="warpCss">
        <view :style="maskLayer"></view>

        <view class="diy-active-cube relative">
            <view class="active-cube-wrap pt-[28rpx] px-[20rpx] pb-[24rpx]">
                <view class="flex items-center" v-if="diyComponent.titleStyle.value == 'style-1'">
                    <view class="mr-[10rpx] font-500 text-[30rpx]" :style="{color: diyComponent.titleColor }" @click="diyStore.toRedirect(diyComponent.textLink)">{{ diyComponent.text }}</view>
                    <view v-if="diyComponent.subTitle.text" @click="diyStore.toRedirect(diyComponent.subTitle.link)"
                          class="text-center text-[22rpx] rounded-[40rpx] rounded-tl-[10rpx] py-[6rpx] px-[14rpx]"
                          :style="{'color': diyComponent.subTitle.textColor, background: 'linear-gradient(90deg, '+ diyComponent.subTitle.startColor + ', '+ diyComponent.subTitle.endColor + ')'}">{{ diyComponent.subTitle.text }}</view>
                </view>
                <view class="flex items-center" v-if="diyComponent.titleStyle.value == 'style-2'">
                    <view class="mr-[10rpx] font-500 text-[30rpx]" :style="{color: diyComponent.titleColor }" @click="diyStore.toRedirect(diyComponent.textLink)">{{ diyComponent.text }}</view>
                    <view v-if="diyComponent.subTitle.text" @click="diyStore.toRedirect(diyComponent.subTitle.link)"
                          class="text-center text-[22rpx] rounded-[6rpx] py-[6rpx] px-[14rpx]"
                          :style="{'color': diyComponent.subTitle.textColor, background: 'linear-gradient(90deg, '+ diyComponent.subTitle.startColor + ', '+ diyComponent.subTitle.endColor + ')'}">{{ diyComponent.subTitle.text }}</view>
                </view>
                <view class="flex items-center" v-if="diyComponent.titleStyle.value == 'style-3'">
                    <view class="mr-[10rpx] font-500 text-[30rpx]" @click="diyStore.toRedirect(diyComponent.textLink)" :style="{color: diyComponent.titleColor }">{{ diyComponent.text }}</view>
                    <view class="relative h-[36rpx]" @click="diyStore.toRedirect(diyComponent.subTitle.link)">
                        <view v-if="diyComponent.subTitle.text"
                              class="flex items-center text-[22rpx] leading-0 min-w-[60rpx] h-[34rpx] pl-[10rpx] pr-[34rpx]"
                              :style="{'color': diyComponent.subTitle.textColor, 'background-image':'url('+img('static/resource/images/diy/active_cube/bg_2.png')+')','background-size': '100% 100%','background-repeat': 'no-repeat'}">{{ diyComponent.subTitle.text }}</view>
                        <!-- 	<image class="absolute left-0 top-0 bottom-0 !w-[16rpx] !h-[36rpx]" :src="img('static/resource/images/diy/active_cube/block_style2_1.png')" mode="scaleToFill"/>
                                    <image class="absolute right-0 top-0 bottom-0 !w-[28rpx] !h-[36rpx]" :src="img('static/resource/images/diy/active_cube/block_style2_2.png')" mode="scaleToFill"/> -->
                    </view>
                </view>
                <view class="flex items-center justify-between" v-if="diyComponent.titleStyle.value == 'style-4'">
                    <view class="font-500 text-[30rpx]" @click="diyStore.toRedirect(diyComponent.textLink)" :style="{color: diyComponent.titleColor }">{{ diyComponent.text }}</view>
                    <view v-if="diyComponent.subTitle.text" @click="diyStore.toRedirect(diyComponent.subTitle.link)" class="text-[22rpx] rounded-[40rpx] pl-[16rpx] pr-[8rpx] h-[42rpx] flex-center" :style="{'color': diyComponent.subTitle.textColor, background: 'linear-gradient(90deg, '+ diyComponent.subTitle.startColor + ', '+ diyComponent.subTitle.endColor + ')'}">
                        <text>{{ diyComponent.subTitle.text }}</text>
                        <text class="nc-iconfont nc-icon-youV6xx !text-[26rpx]"></text>
                    </view>
                </view>
                <view class="flex items-center" v-if="diyComponent.titleStyle.value == 'style-5'">
                    <view class="h-[32rpx] flex items-center" v-if="diyComponent.textImg" @click="diyStore.toRedirect(diyComponent.textLink)">
                        <image class="h-[100%] w-[auto]" :src="img(diyComponent.textImg)" mode="heightFix" />
                    </view>
                    <view v-if="diyComponent.subTitle.text && diyComponent.textImg" class="mx-[16rpx] w-[2rpx] h-[24rpx]" :style="{'background': diyComponent.subTitle.textColor}"></view>
                    <view v-if="diyComponent.subTitle.text" @click="diyStore.toRedirect(diyComponent.subTitle.link)" class="text-center text-[22rpx] py-[6rpx]"
                          :style="{'color': diyComponent.subTitle.textColor, background: 'linear-gradient(90deg, '+ diyComponent.subTitle.startColor + ', '+ diyComponent.subTitle.endColor + ')'}">{{ diyComponent.subTitle.text }}</view>
                </view>

                <view class="bd flex flex-wrap justify-between">
                    <template v-for="item in diyComponent.list" :key="item.id">
                        <view v-if="diyComponent.blockStyle.value == 'style-1'" @click="diyStore.toRedirect(item.link)" class="item flex justify-between px-[20rpx] py-[30rpx] bg-white mt-[20rpx]" :style="commonTempCss(item)">
                            <view class="flex-1 flex items-baseline flex-col">
                                <view class="text-[28rpx] pb-[10rpx] text-[#333]" :style="{ fontWeight : diyComponent.blockStyle.fontWeight }">{{ item.title.text }}</view>
                                <view class="text-[22rpx] text-[#999] pb-[30rpx]">{{ item.subTitle.text }}</view>
                                <view class="link relative text-[22rpx] leading-[40rpx] flex items-center text-white rounded-r-[20rpx] h-[40rpx] pl-[26rpx] pr-[10rpx]"
                                    :style="btnCss(item.moreTitle)" v-if="item.moreTitle.text">
                                    <text class="mr-[8rpx]">{{ item.moreTitle.text }}</text>
                                    <text class="iconfont iconjiantou-you-cuxiantiao-fill !text-[20rpx] text-[#fff]"></text>
                                    <image class="absolute left-0 top-0 bottom-0 !w-[28rpx]" :src="img('static/resource/images/diy/active_cube/block_style1_1.png')" mode="scaleToFill" />
                                </view>
                            </view>
                            <view class="img-box ml-[10rpx] w-[130rpx]" v-if="item.imageUrl">
                                <image :src="img(item.imageUrl)" mode="aspectFit" />
                            </view>
                            <view class="img-box ml-[10rpx] flex items-center justify-center w-[130rpx] bg-[#f3f4f6]" v-else>
                                <u-icon name="photo" color="#999" size="50"></u-icon>
                            </view>
                        </view>

                        <view v-if="diyComponent.blockStyle.value == 'style-2'" @click="diyStore.toRedirect(item.link)" class="item h-[150rpx] flex justify-between p-[20rpx] bg-white mt-[20rpx]"
                              :style="commonTempCss(item)">
                            <view class="flex-1 flex items-baseline flex-col">
                                <view class="text-[26rpx] mt-[10rpx] pb-[16rpx]" :style="{ fontWeight : diyComponent.blockStyle.fontWeight }">{{ item.title.text }}</view>
                                <view class="text-[22rpx] text-gray-500 pb-[26rpx]">{{ item.subTitle.text }}</view>
                                <view class="link relative text-[22rpx] leading-[40rpx] flex items-center text-white rounded-[20rpx] h-[40rpx] pl-[20rpx] pr-[10rpx]" :style="btnCss(item.moreTitle)" v-if="item.moreTitle.text">
                                    <text class="mr-[8rpx]" :class="{'italic': diyComponent.blockStyle.btnText == 'italics'}">{{ item.moreTitle.text }}</text>
                                    <text class="iconfont iconjiantou-you-cuxiantiao-fill !text-[20rpx] text-[#fff]"></text>
                                </view>
                            </view>
                            <view class="img-box ml-[10rpx] w-[130rpx]" v-if="item.imageUrl">
                                <image :src="img(item.imageUrl)" mode="aspectFit" />
                            </view>
                            <view class="img-box ml-[10rpx] flex items-center justify-center w-[130rpx] bg-[#f3f4f6]" v-else>
                                <u-icon name="photo" color="#999" size="50"></u-icon>
                            </view>
                        </view>
                    </template>
                </view>
                <scroll-view :scroll-x="true" class="whitespace-nowrap" :id="'warpStyle3-'+diyComponent.id" v-if="diyComponent.blockStyle.value == 'style-3'">
                    <view v-for="(item,index) in diyComponent.list" :key="item.id" class="inline-flex">
                        <view :id="'item'+index+diyComponent.id" @click="diyStore.toRedirect(item.link)"
                              class="flex flex-col items-center justify-between p-[10rpx] bg-white mt-[20rpx] w-[157rpx] h-[200rpx] box-border"
                              :style="itemStyle3 + commonTempCss(item)"
                              :class="{'!mr-[0rpx]': index+1 === diyComponent.list.length}">
                            <view class="w-[141rpx] h-[141rpx] rounded-[var(--rounded-small)] overflow-hidden" v-if="item.imageUrl">
                                <image class="w-[141rpx] h-[141rpx]" :src="img(item.imageUrl)" mode="aspectFit" />
                            </view>
                            <view class="w-[141rpx] h-[141rpx] relative flex-shrink-0" v-else>
                                <view class="absolute left-0 top-0 flex items-center justify-center w-[141rpx] h-[141rpx] bg-[#f3f4f6]">
                                    <u-icon name="photo" color="#999" size="50"></u-icon>
                                </view>
                            </view>
                            <view class="mt-[10rpx] mb-[2rpx] text-[26rpx]" :style="{ color : item.title.textColor,fontWeight : diyComponent.blockStyle.fontWeight }">{{ item.title.text }}</view>
                        </view>
                    </view>
                </scroll-view>

                <scroll-view scroll-x="true" class="whitespace-nowrap" :id="'warpStyle4-'+diyComponent.id" v-if="diyComponent.blockStyle.value == 'style-4'">
                    <view v-for="(item,index) in diyComponent.list" :key="item.id" class="inline-flex">
                        <view :id="'item'+index+diyComponent.id" @click="diyStore.toRedirect(item.link)"
                              class="flex flex-col items-center justify-between p-[4rpx] bg-[#F93D02] mt-[20rpx] box-border"
                              :class="{'!mr-[0rpx]': index+1 === diyComponent.list.length}"
                              :style="commonTempCss(item) + itemStyle4">
                            <view class="w-[149rpx] h-[149rpx] box-border px-[18rpx] pt-[16rpx] pb-[6rpx] bg-[#fff] flex flex-col items-center rounded-[var(--rounded-small)]">
                                <view class="w-[112rpx] h-[102rpx]" v-if="item.imageUrl"><image class="w-[112rpx] h-[102rpx]" :src="img(item.imageUrl)" mode="aspectFit" /></view>
                                <view class="w-[112rpx] h-[102rpx] relative flex-shrink-0" v-else>
                                    <view class="absolute left-0 top-0 flex items-center justify-center w-[112rpx] h-[102rpx] bg-[#f3f4f6]">
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </view>
                                </view>
                                <view class="relative -mt-[10rpx] text-[20rpx] bg-[#F3DAC5] text-[#ED6E00] rounded-[16rpx] px-[12rpx] h-[34rpx] flex-center"
                                    :style="{ color : item.subTitle.textColor, background : 'linear-gradient(to right,'+ item.subTitle.startColor +','+ item.subTitle.endColor + ')' }">{{ item.subTitle.text }}</view>
                            </view>
                            <view class="mt-[12rpx] mb-[12rpx] text-[26rpx] text-[#fff]"
                                  :style="{ fontWeight : (diyComponent.blockStyle.fontWeight == 'bold' ? diyComponent.blockStyle.fontWeight : '500') }">{{ item.title.text }}</view>
                        </view>
                    </view>
                </scroll-view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
// 活动魔方组件
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { img } from '@/utils/common';

const props = defineProps(['component', 'index']);

const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    let style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }

    if (diyComponent.value.componentBgUrl) {
        style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
        style += 'background-size: cover;background-repeat: no-repeat;';
    }

    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';

    return style;
})

// 背景图加遮罩层
const maskLayer = computed(() => {
    let style = '';
    if (diyComponent.value.componentBgUrl) {
        style += 'position:absolute;top:0;width:100%;';
        style += `background: rgba(0,0,0,${ diyComponent.value.componentBgAlpha / 10 });`;
        style += `height:${ height.value }px;`;

        if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    }

    return style;
});
//商品样式三
const itemStyle3 = ref('');
const setItemStyle3 = () => {
    // #ifdef MP-WEIXIN
    uni.createSelectorQuery().in(instance).select('#warpStyle3-' + diyComponent.value.id).boundingClientRect((res: any) => {
        uni.createSelectorQuery().in(instance).select('#item0' + diyComponent.value.id).boundingClientRect((data: any) => {
            itemStyle3.value = `margin-right:${ (res.width - data.width * 4) / 3 }px;`
        }).exec()
    }).exec()
    // #endif
    // #ifdef H5
    itemStyle3.value = 'margin-right:14rpx;'
    // #endif
};
//商品样式四
const itemStyle4 = ref('');
const setItemStyle4 = () => {
    // #ifdef MP-WEIXIN
    uni.createSelectorQuery().in(instance).select('#warpStyle4-' + diyComponent.value.id).boundingClientRect((res: any) => {
        uni.createSelectorQuery().in(instance).select('#item0' + diyComponent.value.id).boundingClientRect((data: any) => {
            itemStyle4.value = `margin-right:${ (res.width - data.width * 4) / 3 }px;`
        }).exec()
    }).exec()
    // #endif
    // #ifdef H5
    itemStyle4.value = 'margin-right:14rpx;'
    // #endif
};

// 公共模块颜色
const commonTempCss = (data: any) => {
    let style = '';
    if (data.listFrame.startColor && data.listFrame.endColor) {
        style += `background:linear-gradient(${ data.listFrame.startColor },${ data.listFrame.endColor });`;
    } else {
        style += `background:${ data.listFrame.startColor || data.listFrame.endColor };`;
    }
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    style += 'overflow: hidden;';
    return style;
}

const btnCss = (item: any) => {
    let style = '';
    style += `background:linear-gradient(90deg,${ item.startColor },${ item.endColor });`;
    return style;
};

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ActiveCube') {
                    refresh();
                }
            }
        )
    } else {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                refresh();
            }
        )
    }
});

const instance = getCurrentInstance();
const height = ref(0)

const refresh = () => {
    nextTick(() => {
        const query = uni.createSelectorQuery().in(instance);
        query.select('.diy-active-cube').boundingClientRect((data: any) => {
            height.value = data.height;
        }).exec();

        if (diyComponent.value.blockStyle.value == 'style-3') setItemStyle3()
        if (diyComponent.value.blockStyle.value == 'style-4') setItemStyle4()
    })
}
</script>

<style lang="scss" scoped>
.active-cube-wrap {
    .bd {
        .item {
            width: calc(46% - 20rpx);

            image {
                width: 100%;
                height: 100%;
            }

        }
    }
}
</style>
