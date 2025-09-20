<template>
    <view :style="warpCss">
        <view :style="maskLayer"></view>
        <view :class="['rubik-cube relative', diyStore.mode]">

            <!-- 1左2右 -->
            <template v-if="diyComponent.mode == 'row1-lt-of2-rt'">
                <view class="template-left">
                    <view @click="diyStore.toRedirect(diyComponent.list[0].link)" :class="['item', diyComponent.mode]"
                          :style="{ marginRight: diyComponent.imageGap * 2 + 'rpx', width: diyComponent.list[0].imgWidth, height: diyComponent.list[0].imgHeight + 'px' }">
                        <image v-if="diyComponent.list[0].imageUrl" :src="img(diyComponent.list[0].imageUrl)" mode="scaleToFill" :style="diyComponent.list[0].pageItemStyle" :show-menu-by-longpress="true" />
                        <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" :style="diyComponent.list[0].pageItemStyle" :show-menu-by-longpress="true" />
                    </view>
                </view>

                <view class="template-right">
                    <template v-for="(item, index) in diyComponent.list" :key="index">
                        <template v-if="index > 0">
                            <view @click="diyStore.toRedirect(item.link)" :class="['item', diyComponent.mode]" :style="{ marginBottom: diyComponent.imageGap * 2 + 'rpx', width: item.imgWidth, height: item.imgHeight + 'px' }">
                                <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="scaleToFill" :style="item.pageItemStyle" :show-menu-by-longpress="true" />
                                <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" :style="item.pageItemStyle" :show-menu-by-longpress="true" />
                            </view>
                        </template>
                    </template>
                </view>
            </template>

            <!-- 1左3右 -->
            <template v-else-if="diyComponent.mode == 'row1-lt-of1-tp-of2-bm'">
                <view class="template-left">
                    <view @click="diyStore.toRedirect(diyComponent.list[0].link)" :class="['item', diyComponent.mode]"
                          :style="{ marginRight: diyComponent.imageGap * 2 + 'rpx', width: diyComponent.list[0].imgWidth, height: diyComponent.list[0].imgHeight + 'px' }">
                        <image v-if="diyComponent.list[0].imageUrl" :src="img(diyComponent.list[0].imageUrl)" mode="scaleToFill" :style="diyComponent.list[0].pageItemStyle" :show-menu-by-longpress="true" />
                        <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" :style="diyComponent.list[0].pageItemStyle" :show-menu-by-longpress="true" />
                    </view>
                </view>

                <view class="template-right">
                    <view @click="diyStore.toRedirect(diyComponent.list[1].link)" :class="['item', diyComponent.mode]"
                          :style="{ marginBottom: diyComponent.imageGap * 2 + 'rpx', width: diyComponent.list[1].imgWidth, height: diyComponent.list[1].imgHeight + 'px' }">
                        <image v-if="diyComponent.list[1].imageUrl" :src="img(diyComponent.list[1].imageUrl)" mode="scaleToFill" :style="diyComponent.list[1].pageItemStyle" :show-menu-by-longpress="true" />
                        <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" :style="diyComponent.list[1].pageItemStyle" :show-menu-by-longpress="true" />
                    </view>
                    <view class="template-bottom">
                        <template v-for="(item, index) in diyComponent.list" :key="index">
                            <template v-if="index > 1">
                                <view @click="diyStore.toRedirect(item.link)" :class="['item', diyComponent.mode]"
                                      :style="{ marginRight: diyComponent.imageGap * 2 + 'rpx', width: item.imgWidth, height: item.imgHeight + 'px' }">
                                    <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="scaleToFill" :style="item.pageItemStyle" :show-menu-by-longpress="true" />
                                    <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" :style="item.pageItemStyle" :show-menu-by-longpress="true" />
                                </view>
                            </template>
                        </template>
                    </view>
                </view>
            </template>

            <template v-else>
                <view :class="['item', diyComponent.mode]" v-for="(item, index) in diyComponent.list" :key="index" @click="diyStore.toRedirect(item.link)"
                      :style="{ marginRight: diyComponent.imageGap * 2 + 'rpx', marginBottom: diyComponent.imageGap * 2 + 'rpx', width: item.widthStyle, height: item.imgHeight + 'px' }">
                    <image v-if="item.imageUrl" :src="img(item.imageUrl)" :mode="'scaleToFill'" :style="item.pageItemStyle" :show-menu-by-longpress="true" />
                    <image v-else :src="img('static/resource/images/diy/figure.png')" :mode="'scaleToFill'" :style="item.pageItemStyle" :show-menu-by-longpress="true" />
                </view>
            </template>

        </view>
    </view>
</template>

<script setup lang="ts">
// 魔方
import { ref, onMounted, computed, watch, nextTick, getCurrentInstance } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { img } from '@/utils/common';
import useSystemStore from "@/stores/system";

const props = defineProps(['component', 'index']);

const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const systemStore = useSystemStore()
systemStore.systemInfo = uni.getSystemInfoSync();

/**
 * 处理rpx渲染之后变成rem存在小数的问题
 * @param rpx
 */
const upx2px = (rpx: number) => {
    return uni.upx2px(rpx) + 1
}

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

const countBorderRadius = (type: any, index: any) => {
    let obj = '';
    if (diyComponent.value.elementAngle == 'right') {
        return obj;
    }
    const defaultData: any = {
        'row1-lt-of2-rt': [
            ['border-top-right-radius', 'border-bottom-right-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius', 'border-bottom-right-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius', 'border-top-right-radius']
        ],
        'row1-lt-of1-tp-of2-bm': [
            ['border-top-right-radius', 'border-bottom-right-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius', 'border-bottom-right-radius'],
            ['border-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius', 'border-top-right-radius']
        ],
        'row1-tp-of2-bm': [
            ['border-bottom-left-radius', 'border-bottom-right-radius'],
            ['border-top-left-radius', 'border-bottom-right-radius', 'border-top-right-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius', 'border-top-right-radius']
        ],
        'row2-lt-of2-rt': [
            ['border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius'],
            ['border-top-left-radius', 'border-bottom-right-radius', 'border-bottom-left-radius'],
            ['border-top-left-radius', 'border-bottom-right-radius', 'border-top-right-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius', 'border-top-right-radius']
        ],
        'row1-of4': [
            ['border-top-right-radius', 'border-bottom-right-radius'],
            ['border-radius'],
            ['border-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius']
        ],
        'row1-of3': [
            ['border-top-right-radius', 'border-bottom-right-radius'],
            ['border-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius']
        ],
        'row1-of2': [
            ['border-top-right-radius', 'border-bottom-right-radius'],
            ['border-top-left-radius', 'border-bottom-left-radius']
        ]
    };

    defaultData[type][index].forEach((item: any, index: any) => {
        obj += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
        obj += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
        obj += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
        obj += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    });
    return obj;
}

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'RubikCube') {
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
    if (diyStore.mode == 'decorate') {
        diyComponent.value.list.forEach((item: any) => {
            // 装修模式下设置默认图
            if (item.imageUrl == '') {
                item.imgWidth = 690;
                item.imgHeight = 330;
            }
        });
    }
    handleData()
    nextTick(() => {
        const query = uni.createSelectorQuery().in(instance);
        query.select('.rubik-cube').boundingClientRect((data: any) => {
            height.value = data.height;
        }).exec();
    })
}

const handleData = () => {
    const singleRow: any = {
        'row1-of2': {
            ratio: 2,
            width: 'calc((100% - ' + upx2px(diyComponent.value.imageGap * 2) + 'px) / 2)'
        },
        'row1-of3': {
            ratio: 3,
            width: 'calc((100% - ' + upx2px(diyComponent.value.imageGap * 4) + 'px) / 3)'
        },
        'row1-of4': {
            ratio: 4,
            width: 'calc((100% - ' + upx2px(diyComponent.value.imageGap * 6) + 'px) / 4)'
        }
    };

    diyComponent.value.list.forEach((item: any, index: any) => {
        item.pageItemStyle = countBorderRadius(diyComponent.value.mode, index);
    });

    if (singleRow[diyComponent.value.mode]) {
        calcSingleRow(singleRow[diyComponent.value.mode]);
    } else if (diyComponent.value.mode == 'row2-lt-of2-rt') {
        calcFourSquare();
    } else if (diyComponent.value.mode == 'row1-lt-of2-rt') {
        calcRowOneLeftOfTwoRight();
    } else if (diyComponent.value.mode == 'row1-tp-of2-bm') {
        calcRowOneTopOfTwoBottom();
    } else if (diyComponent.value.mode == 'row1-lt-of1-tp-of2-bm') {
        calcRowOneLeftOfOneTopOfTwoBottom();
    }
};

/**
 * 魔方：单行多个，平分宽度
 * 公式：
 * 宽度：屏幕宽度/2，示例：375/2=187.5
 * 比例：原图高/原图宽，示例：322/690=0.46
 * 高度：宽度*比例，示例：187.5*0.46=86.25
 */
const calcSingleRow = (params: any) => {
    let maxHeight = 0;
    diyComponent.value.list.forEach((item: any, index: any) => {
        const ratio = item.imgHeight / item.imgWidth;

        let width = systemStore.systemInfo.windowWidth - upx2px(diyComponent.value.margin.both * 2); // 减去左右间距
        if (diyComponent.value.imageGap > 0) {
            width -= upx2px(params.ratio * diyComponent.value.imageGap * 2); // 减去间隙
        }
        item.imgWidth = width / params.ratio;
        item.imgHeight = item.imgWidth * ratio;

        if (maxHeight == 0 || maxHeight < item.imgHeight) maxHeight = item.imgHeight;
    })

    diyComponent.value.list.forEach((item: any, index: any) => {
        item.widthStyle = params.width;
        item.imgHeight = maxHeight;
    });
};

/**
 * 魔方：四方型，各占50%
 */
const calcFourSquare = () => {
    let maxHeightFirst = 0;
    let maxHeightTwo = 0;
    diyComponent.value.list.forEach((item: any, index: any) => {
        const ratio = item.imgHeight / item.imgWidth;
        item.imgWidth = systemStore.systemInfo.windowWidth;
        item.imgWidth -= upx2px(diyComponent.value.margin.both * 4);
        if (diyComponent.value.imageGap > 0) {
            item.imgWidth -= upx2px(diyComponent.value.imageGap * 2);
        }
        item.imgWidth = item.imgWidth / 2;
        item.imgHeight = item.imgWidth * ratio;

        // 获取每行最大高度
        if (index <= 1) {
            if (maxHeightFirst == 0 || maxHeightFirst < item.imgHeight) {
                maxHeightFirst = item.imgHeight;
            }
        } else if (index > 1) {
            if (maxHeightTwo == 0 || maxHeightTwo < item.imgHeight) {
                maxHeightTwo = item.imgHeight;
            }
        }
    });
    diyComponent.value.list.forEach((item: any, index: any) => {
        item.imgWidth = 'calc((100% - ' + upx2px(diyComponent.value.imageGap * 2) + 'px) / 2)';
        item.widthStyle = item.imgWidth;
        if (index <= 1) {
            item.imgHeight = maxHeightFirst;
        } else if (index > 1) {
            item.imgHeight = maxHeightTwo;
        }
    });
}

/**
 * 魔方：1左2右
 */
const calcRowOneLeftOfTwoRight = () => {
    let rightHeight = 0; // 右侧两图平分高度
    let divide = 'left'; // 划分规则，left：左，right：右
    if (diyComponent.value.list[1].imgWidth === diyComponent.value.list[2].imgWidth) divide = 'right';
    diyComponent.value.list.forEach((item: any, index: any) => {
        if (index == 0) {
            const ratio = item.imgHeight / item.imgWidth; // 获取左图的尺寸比例
            item.imgWidth = systemStore.systemInfo.windowWidth - upx2px(diyComponent.value.margin.both * 4) - upx2px(diyComponent.value.imageGap * 2);
            item.imgWidth = item.imgWidth / 2;
            item.imgHeight = item.imgWidth * ratio;
            rightHeight = (item.imgHeight - upx2px(diyComponent.value.imageGap * 2)) / 2;
            item.imgWidth += 'px';
        } else {
            item.imgWidth = diyComponent.value.list[0].imgWidth;
            item.imgHeight = rightHeight;
        }
    });
}

/**
 * 魔方：1上2下
 */
const calcRowOneTopOfTwoBottom = () => {
    let maxHeight = 0;
    diyComponent.value.list.forEach((item: any, index: any) => {

        const ratio = item.imgHeight / item.imgWidth; // 获取左图的尺寸比例
        if (index == 0) {
            item.imgWidth = systemStore.systemInfo.windowWidth - upx2px(diyComponent.value.margin.both * 4);
        } else if (index > 0) {
            item.imgWidth = systemStore.systemInfo.windowWidth - upx2px(diyComponent.value.margin.both * 4) - upx2px(diyComponent.value.imageGap * 2);
            item.imgWidth = item.imgWidth / 2;
        }

        item.imgHeight = item.imgWidth * ratio;

        // 获取最大高度
        if (index > 0 && (maxHeight == 0 || maxHeight < item.imgHeight)) maxHeight = item.imgHeight;

    });
    diyComponent.value.list.forEach((item: any, index: any) => {
        item.imgWidth += 'px';
        item.widthStyle = item.imgWidth;
        if (index > 0) item.imgHeight = maxHeight;
    });
}

/**
 * 魔方：1左3右
 */
const calcRowOneLeftOfOneTopOfTwoBottom = () => {
    diyComponent.value.list.forEach((item: any, index: any) => {
        // 左图
        if (index == 0) {
            const ratio = item.imgHeight / item.imgWidth; // 获取左图的尺寸比例
            item.imgWidth = systemStore.systemInfo.windowWidth - upx2px(diyComponent.value.margin.both * 4) - upx2px(diyComponent.value.imageGap * 2);
            item.imgWidth = item.imgWidth / 2;
            item.imgHeight = item.imgWidth * ratio;
        } else if (index == 1) {
            item.imgWidth = diyComponent.value.list[0].imgWidth;
            item.imgHeight = (diyComponent.value.list[0].imgHeight - upx2px(diyComponent.value.imageGap * 2)) / 2;
        } else if (index > 1) {
            item.imgWidth = (diyComponent.value.list[0].imgWidth - upx2px(diyComponent.value.imageGap * 2)) / 2;
            item.imgHeight = diyComponent.value.list[1].imgHeight;
        }
    });

    diyComponent.value.list.forEach((item: any, index: any) => {
        item.imgWidth += 'px';
    });
}
</script>

<style lang="scss">
.rubik-cube {
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;

    .item {
        text-align: center;
        line-height: 0;
        overflow: hidden;

        image {
            width: 100%;
            max-width: 100%;
            height: 100%;
        }
    }
}

// 一行两个
.rubik-cube .item.row1-of2 {
    box-sizing: border-box;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

.rubik-cube .item.row1-of2:nth-child(1) {
    margin-left: 0 !important;
}

.rubik-cube .item.row1-of2:nth-child(2) {
    margin-right: 0 !important;
}

// 一行三个
.rubik-cube .item.row1-of3 {
    box-sizing: border-box;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

.rubik-cube .item.row1-of3:nth-child(1) {
    margin-left: 0 !important;
}

.rubik-cube .item.row1-of3:nth-child(3) {
    margin-right: 0 !important;
}

// 一行四个
.rubik-cube .item.row1-of4 {
    box-sizing: border-box;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

.rubik-cube .item.row1-of4:nth-child(1) {
    margin-left: 0 !important;
}

.rubik-cube .item.row1-of4:nth-child(4) {
    margin-right: 0 !important;
}

// 两左两右
.rubik-cube .item.row2-lt-of2-rt {
    // width: 50%;
    display: inline-block;
    box-sizing: border-box;
}

.rubik-cube .item.row2-lt-of2-rt:nth-child(1) {
    margin-left: 0 !important;
    margin-top: 0 !important;
}

.rubik-cube .item.row2-lt-of2-rt:nth-child(2) {
    margin-right: 0 !important;
    margin-top: 0 !important;
}

.rubik-cube .item.row2-lt-of2-rt:nth-child(3) {
    margin-left: 0 !important;
    margin-bottom: 0 !important;
}

.rubik-cube .item.row2-lt-of2-rt:nth-child(4) {
    margin-right: 0 !important;
    margin-bottom: 0 !important;
}

// 一左两右
.rubik-cube .template-left,
.rubik-cube .template-right {
    // width: 50%;
    box-sizing: border-box;
}

.rubik-cube .template-left .item.row1-lt-of2-rt:nth-child(1) {
    margin-bottom: 0;
}

.rubik-cube .template-right .item.row1-lt-of2-rt:nth-child(2) {
    margin-bottom: 0 !important;
}

.rubik-cube.row1-lt-of2-rt .template-right {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

// 一上两下
.rubik-cube .item.row1-tp-of2-bm:nth-child(1) {
    width: 100%;
    box-sizing: border-box;
    margin-top: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.rubik-cube .item.row1-tp-of2-bm:nth-child(2) {
    // width: 50%;
    box-sizing: border-box;
    margin-left: 0 !important;
    margin-bottom: 0 !important;
}

.rubik-cube .item.row1-tp-of2-bm:nth-child(3) {
    // width: 50%;
    box-sizing: border-box;
    margin-right: 0 !important;
    margin-bottom: 0 !important;
}

// 一左三右
.rubik-cube .template-left .item.row1-lt-of1-tp-of2-bm {
    width: 100%;
    box-sizing: border-box;
}

.rubik-cube .template-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.rubik-cube .template-bottom .item:nth-child(2) {
    margin-right: 0 !important;
}
</style>
