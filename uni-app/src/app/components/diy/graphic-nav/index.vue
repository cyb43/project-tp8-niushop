<template>
    <view :style="warpCss">
        <view :style="maskLayer"></view>
        <view class="diy-graphic-nav relative">
            <view v-if="diyComponent.layout == 'vertical'" class="graphic-nav">
                <view class="graphic-nav-item" v-for="(item, index) in diyComponent.list" :key="item.id">

                    <view @click="diyStore.toRedirect(item.link)" :class="['flex items-center justify-between py-3 px-4', index == 0 ? 'border-t-0':'border-t']">

                        <view class="graphic-img relative flex items-center w-10 h-10 mr-[20rpx]" v-if="diyComponent.mode != 'text'" :style="{ width: diyComponent.imageSize * 2 + 'rpx', height: diyComponent.imageSize * 2 + 'rpx' }">
                            <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                            <image v-else :src="img('static/resource/images/diy/figure.png')" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                            <text v-if="item.label.control"
                                  class="tag absolute -top-[10rpx] -right-[24rpx] text-white rounded-[24rpx] rounded-bl-none transform scale-80 py-1 px-2 text-xs"
                                  :style="{ color: item.label.textColor, backgroundImage: 'linear-gradient(' + item.label.bgColorStart + ',' + item.label.bgColorEnd + ')' }">{{ item.label.text }}</text>
                        </view>

                        <text v-if="diyComponent.mode != 'img'" class="graphic-text w-full truncate leading-normal"
                              :style="{ fontSize: diyComponent.font.size * 2 + 'rpx', fontWeight: diyComponent.font.weight, color: diyComponent.font.color }">{{ item.title }}</text>
                        <u-icon name="arrow-right" color="#999999" size="12"></u-icon>

                    </view>

                </view>

            </view>

            <view class="pt-[10rpx]" v-else-if="diyComponent.layout == 'horizontal' && diyComponent.showStyle == 'pageSlide'">
                <swiper class="graphic-nav swiper relative" :style="{ height: swiperHeight, width: '95%', margin: '0 auto',opacity : swiperHeight ? 1 : 0 }" circular @change="swiperChange">
                    <swiper-item class="graphic-nav-wrap flex flex-wrap" v-for="(numItem, numIndex) in Math.ceil(diyComponent.list.length / (diyComponent.pageCount * diyComponent.rowCount))">

                        <template v-for="(item, index) in diyComponent.list">

                            <view :class="[diyComponent.mode]" :key="item.id" v-if="swiperCondition(index,numItem)" :style="{ width: 100 / diyComponent.rowCount + '%' }">

                                <view @click="diyStore.toRedirect(item.link)" class="graphic-nav-item flex flex-col items-center py-2">

                                    <view class="graphic-img relative flex items-center justify-center w-10 h-10" v-if="diyComponent.mode != 'text'"
                                          :style="{ width: diyComponent.imageSize * 2 + 'rpx', height: diyComponent.imageSize * 2 + 'rpx' }">
                                        <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                                        <image v-else :src="img('static/resource/images/diy/figure.png')" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />

                                        <text
                                            class="tag absolute -top-[10rpx] -right-[24rpx] text-white rounded-[24rpx] rounded-bl-none transform scale-80 py-1 px-2 text-xs"
                                            v-if="item.label.control"
                                            :style="{ color: item.label.textColor, backgroundImage: 'linear-gradient(' + item.label.bgColorStart + ',' + item.label.bgColorEnd + ')' }">{{ item.label.text }}</text>
                                    </view>

                                    <text v-if="diyComponent.mode != 'img'"
                                          class="graphic-text w-full text-center truncate leading-normal"
                                          :class="{ 'pt-[16rpx]' : diyComponent.mode != 'text' }"
                                          :style="{ fontSize: diyComponent.font.size * 2 + 'rpx', fontWeight: diyComponent.font.weight, color: diyComponent.font.color }">{{ item.title }}</text>
                                </view>

                            </view>
                        </template>
                    </swiper-item>
                </swiper>
                <view class="graphic-nav-indicator-dot" v-if="shouldShowIndicator && swiperHeight">
                    <view class="dots-wrap" :class="[diyComponent.swiper.indicatorAlign]">
                        <view v-for="(item,index) in Math.ceil(diyComponent.list.length / (diyComponent.pageCount * diyComponent.rowCount))"
                            :class="['dot',index == swiperIndex ? 'dot-active' : '',diyComponent.swiper.indicatorStyle]"
                            :style="{ background : index == swiperIndex ? diyComponent.swiper.indicatorActiveColor : diyComponent.swiper.indicatorColor}"></view>
                    </view>
                </view>
            </view>

            <view :style="{width: '98%', margin: '0 auto' }"
                  v-else-if="diyComponent.layout == 'horizontal' && diyComponent.pageCount == 2 && diyComponent.showStyle == 'singleSlide'"
                  :class="['graphic-nav multiple-lines','graphic-nav-' + diyComponent.showStyle]" class="py-[10rpx]">
                <!-- #ifdef MP -->
                <view class="uni-scroll-view-content">
                    <!-- #endif -->
                    <scroll-view class="graphic-nav-wrap whitespace-nowrap" :scroll-x="diyComponent.showStyle == 'singleSlide'" v-for="(numItem, numIndex) in getSlideRowNum()">
                        <template v-for="(item, index) in diyComponent.list">

                            <view v-if="isShowSlideTemp(index,numItem)" @click="diyStore.toRedirect(item.link)"
                                  :style="horizontalSingleSlideItemStyle(numIndex)"
                                  class="graphic-nav-item inline-flex flex-col items-center box-border py-2">
                                <view class="graphic-img relative flex items-center justify-center w-10 h-10"
                                      v-if="diyComponent.mode != 'text'"
                                      :style="{ width: diyComponent.imageSize * 2 + 'rpx', height: diyComponent.imageSize * 2 + 'rpx' }">
                                    <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                                    <image v-else :src="img('static/resource/images/diy/figure.png')" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                                    <text :class="['tag absolute -top-[10rpx] -right-[24rpx] text-white rounded-[24rpx] rounded-bl-none transform scale-80 py-1 px-2 text-xs']"
                                        v-if="item.label.control"
                                        :style="{ color: item.label.textColor, backgroundImage: 'linear-gradient(' + item.label.bgColorStart + ',' + item.label.bgColorEnd + ')' }">{{ item.label.text }}</text>
                                </view>
                                <text v-if="diyComponent.mode != 'img'"
                                      class="graphic-text w-full text-center truncate leading-normal"
                                      :class="{ 'pt-[16rpx]' : diyComponent.mode != 'text' }"
                                      :style="{ fontSize: diyComponent.font.size * 2 + 'rpx', fontWeight: diyComponent.font.weight, color: diyComponent.font.color }">{{ item.title }}</text>
                            </view>
                        </template>
                    </scroll-view>
                    <!-- #ifdef MP -->
                </view>
                <!-- #endif -->

            </view>

            <scroll-view v-else :scroll-x="diyComponent.showStyle == 'singleSlide'" :class="['graphic-nav','graphic-nav-' + diyComponent.showStyle]" class=" py-[10rpx]">
                <!-- #ifdef MP -->
                <view class="uni-scroll-view-content">
                    <!-- #endif -->

                    <view class="graphic-nav-item" :class="{'flex-shrink-0' : diyComponent.showStyle == 'singleSlide'}"
                          v-for="(item, index) in diyComponent.list" :key="item.id"
                          :style="{ width: 100 / diyComponent.rowCount + '%' }">

                        <view @click="diyStore.toRedirect(item.link)" class="flex flex-col items-center box-border py-2">
                            <view class="graphic-img relative flex items-center justify-center w-10 h-10"
                                  v-if="diyComponent.mode != 'text'"
                                  :style="{ width: diyComponent.imageSize * 2 + 'rpx', height: diyComponent.imageSize * 2 + 'rpx' }">
                                <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                                <image v-else :src="img('static/resource/images/diy/figure.png')" mode="aspectFill" :style="{ maxWidth: diyComponent.imageSize * 2 + 'rpx', maxHeight: diyComponent.imageSize * 2 + 'rpx', borderRadius: diyComponent.aroundRadius * 2 + 'rpx' }" />
                                <text :class="['tag absolute -top-[10rpx] -right-[24rpx] text-white rounded-[24rpx] rounded-bl-none transform scale-80 py-1 px-2 text-xs']"
                                    v-if="item.label.control"
                                    :style="{ color: item.label.textColor, backgroundImage: 'linear-gradient(' + item.label.bgColorStart + ',' + item.label.bgColorEnd + ')' }">{{ item.label.text }}</text>
                            </view>
                            <text v-if="diyComponent.mode != 'img'"
                                  class="graphic-text w-full text-center truncate leading-normal"
                                  :class="{ 'pt-[16rpx]' : diyComponent.mode != 'text' }"
                                  :style="{ fontSize: diyComponent.font.size * 2 + 'rpx', fontWeight: diyComponent.font.weight, color: diyComponent.font.color }">{{ item.title }}</text>
                        </view>
                    </view>

                    <!-- #ifdef MP -->
                </view>
                <!-- #endif -->

            </scroll-view>

        </view>
    </view>
</template>

<script lang="ts" setup>
// 图文导航
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';

const props = defineProps(['component', 'index']);

const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})
const shouldShowIndicator = computed(() => {
    const totalItems = diyComponent.value.list.length // 总项数
    const itemsPerPage = diyComponent.value.pageCount * diyComponent.value.rowCount // 每页可显示的项数
    return totalItems > itemsPerPage // 如果总项数大于每页显示的项数，显示指示器
})
const warpCss = computed(() => {
    var style = '';
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
    var style = '';
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

const horizontalSingleSlideItemStyle = (index: any) => {
    let style = { width: "" };
    style.width = `${ 100 / diyComponent.value.rowCount }%`;
    return style;
}

const swiperIndex = ref(0);

const swiperChange = e => {
    swiperIndex.value = e.detail.current;
};

const swiperCondition = (index, numItem) => {
    let count = diyComponent.value.pageCount * diyComponent.value.rowCount;
    let result = index >= [(numItem - 1) * (count)] && index < [numItem * (count)];
    return result;
}

const storageKey = 'graphic_nav_horizontal_page_slide_swiperheight_' + props.index + '_' + diyComponent.value.list.length;
const swiperHeight = ref(uni.getStorageSync(storageKey) || '');

const handleData = () => {
    if (diyComponent.value.layout == 'horizontal' && diyComponent.value.showStyle == 'pageSlide') {
        var height = 0;
        const query = uni.createSelectorQuery().in(instance);
        query.select('.graphic-nav-item').boundingClientRect((data: any) => {
            let len = 1;
            if (diyComponent.value.pageCount == 2) {
                len = (diyComponent.value.list.length / diyComponent.value.rowCount) > 1 ? 2 : 1;
            }
            height = data.height * len;
            swiperHeight.value = height + 'px';
            uni.setStorageSync(storageKey, swiperHeight.value);
        }).exec();
    }
};

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'GraphicNav') {
                    refresh();
                }
            }
        )
    }
});

const instance = getCurrentInstance();
const height = ref(0)

const refresh = () => {
    nextTick(() => {
        handleData()
        const query = uni.createSelectorQuery().in(instance);
        query.select('.diy-graphic-nav').boundingClientRect((data: any) => {
            if(data) height.value = data.height;
        }).exec();
    })
}
// 获取滑动行数
const getSlideRowNum = () => {
    let num = 1;
    if (diyComponent.value.pageCount == 2) {
        num = diyComponent.value.list.length > diyComponent.value.rowCount ? 2 : 1;
    }
    return num;
}
const isShowSlideTemp = (index, numItem) => {
    let indent = index + 1;
    if (diyComponent.value.pageCount == 2) {
        let num = Math.ceil(diyComponent.value.list.length / diyComponent.value.rowCount)
        for (let i = 1; i <= num; i++) {
            if (numItem == 1 && (i % 2) != 0) {
                if (indent > ((i - 1) * diyComponent.value.rowCount) && indent <= (i * diyComponent.value.rowCount)) {
                    return true;
                }
            }
            if (numItem == 2 && (i % 2) == 0) {
                if (indent > ((i - 1) * diyComponent.value.rowCount) && indent <= (i * diyComponent.value.rowCount)) {
                    return true;
                }
            }
        }
    }

    return false;
}
</script>

<style>
/* 固定显示 */
.graphic-nav-fixed >>> .uni-scroll-view-content {
    display: flex;
    flex-wrap: wrap;
}

/* 单行滑动 */
.graphic-nav-singleSlide >>> .uni-scroll-view-content {
    display: flex;
}

/* 多行滑动 */
.multiple-lines.graphic-nav-singleSlide >>> .uni-scroll-view-content {
    display: block;
}
</style>
<style lang="scss" scoped>
.graphic-nav-indicator-dot {
    width: 95%;
    margin: auto;

    .dots-wrap {
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;

        &.left {
            justify-content: flex-start;
            padding-left: 30rpx;
        }

        &.right {
            justify-content: flex-end;
            padding-right: 30rpx;
        }

        .dot {
            display: inline-block;
            width: 12rpx;
            height: 12rpx;
            cursor: pointer;
            transition-property: background-color;
            transition-timing-function: ease;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            margin-right: 16rpx;

            &:last-child {
                margin-right: 0;
            }

            // 指示器样式二
            &.style-2 {
                width: 18rpx;
                height: 6rpx;
                border-radius: 4rpx;
            }

            &dot-active.style-2 {
                width: 36rpx;
            }

            // 指示器样式三
            &.style-3 {
                width: 10rpx;
                height: 10rpx !important;
                border-radius: 12rpx;
                margin-right: 14rpx;

                &:last-of-type {
                    margin-right: 0;
                }
            }

            &.dot-active.style-3 {
                width: 32rpx;
            }
        }
    }
}
</style>
