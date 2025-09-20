<template>
    <view class="x-skeleton" :style="variableStr">
        <!-- 骨架屏 -->
        <view v-if="skeletonLoading" class="x-skeleton__wrapper" :class="[ startFadeOut && 'fade-out' ]" :style="{ padding: skeletonConfig.padding }">
            <view v-for="(row, rowIndex) in gridRowsArr" :key="rowIndex" class="x-skeleton__wrapper__rows" :style="{ marginBottom: rowIndex < gridRowsArr.length - 1 ? skeletonConfig.gridRowsGap : 0 }">
                <view v-for="(column, columnIndex) in gridColumnsArr" :key="columnIndex"
                    class="x-skeleton__wrapper__columns"
                    :style="{ flexDirection: skeletonConfig.itemDirection, alignItems: skeletonConfig.itemAlign, marginRight: columnIndex < gridColumnsArr.length - 1 ? skeletonConfig.gridColumnsGap : 0 }">
                    <view v-if="skeletonConfig.headShow"
                        class="x-skeleton__wrapper__head" :class="[ animate && 'animate' ]"
                        :style="{ width: skeletonConfig.headWidth, height: skeletonConfig.headHeight, borderRadius: skeletonConfig.headBorderRadius, marginRight: (skeletonConfig.itemDirection == 'row' && skeletonConfig.textShow) ? skeletonConfig.itemGap : 0, marginBottom: (skeletonConfig.itemDirection == 'column' && skeletonConfig.textShow) ? skeletonConfig.itemGap : 0 }"
                    ></view>
                    <view v-if="skeletonConfig.textShow" class="x-skeleton__wrapper__text">
                        <view v-for="(text, textIndex) in textRowsArr" :key="textIndex"
                            class="x-skeleton__wrapper__text__row" :class="[animate && 'animate']"
                            :style="{ width: text.width, height: text.height, borderRadius: skeletonConfig.textBorderRadius, marginBottom: textIndex < textRowsArr.length - 1 ? skeletonConfig.textRowsGap : 0 }"
                        ></view>
                    </view>
                </view>
            </view>
        </view>
        <!-- 插槽 -->
        <view v-else>
            <slot></slot>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { bannerConfig, infoConfig, textConfig, menuConfig, listConfig, waterfallConfig } from './config.js'

const props = defineProps({
    // 骨架屏类型
    type: {
        type: String,
        default: '', //banner 轮播图、info 个人信息、text 段落、menu 菜单、list 列表、waterfall 瀑布流
        required: true
    },
    // 是否展示骨架组件
    loading: {
        type: Boolean,
        default: true
    },
    // 是否开启动画效果
    animate: {
        type: Boolean,
        default: true
    },
    // 动画效果持续时间，单位秒
    animateTime: {
        type: Number,
        default: 1.8
    },
    // 是否开启淡出动画
    fadeOut: {
        type: Boolean,
        default: true
    },
    // 淡出效果持续时间，单位秒
    fadeOutTime: {
        type: Number,
        default: 0.5
    },
    // 骨架的背景色
    bgColor: {
        type: String,
        default: '#EAEDF5' // #EAEDF5
    },
    // 骨架的动画高亮背景色
    highlightBgColor: {
        type: String,
        default: '#F9FAFF' // #F9FAFF
    },
    // 自定义配置
    config: {
        type: Object,
        default: () => {
            return {
                // padding: '30rpx',		   				  //内边距

                // gridRows: 3, 			 	   			  //行数
                // gridColumns: 2,	  		  	   			  //列数
                // gridRowsGap: '40rpx',		   			  //行间隔
                // gridColumnsGap: '24rpx',	   			  	  //竖间距

                // itemDirection: 'column',   				  //head与text之间的排列方向（row、column）
                // itemGap: '16rpx', 				  		  //head与text之间的间隔
                // itemAlign: 'center',  	   				  //head与text之间的纵轴对齐方式（center、flex-start、flex-end、baseline）

                // headShow: true,		 	   				  //head是否展示
                // headWidth: '100%',	  	   				  //head宽度，支持百分比
                // headHeight: '400rpx',	   				  //head高度
                // headBorderRadius: '12rpx', 				  //head圆角，支持百分比

                // textShow: true,		   				      //文本是否展示
                // textRows: 3,		 	   				  	  //文本的行数
                // textRowsGap: '12rpx',		   			  //文本间距
                // textWidth: ['40%', '85%', '60%'], 		  //文本的宽度，可以为百分比，数值，带单位字符串等，可通过数组传入指定每个段落行的宽度
                // textHeight: ['30rpx', '20rpx', '20rpx'],   //文本的高度，可以为数值，带单位字符串等，可通过数组传入指定每个段落行的高度
                // textBorderRadius: '6rpx',  			      //文本的圆角，支持百分比
            }
        }
    }
});

const skeletonConfig = ref(props.config || {});
const skeletonLoading = ref(props.loading);
const startFadeOut = ref(false);
const width = ref(0);

const gridRowsArr = computed(() => {
    return new Array(Number(skeletonConfig.value?.gridRows || []));
})

const gridColumnsArr = computed(() => {
    return new Array(Number(skeletonConfig.value?.gridColumns || []));
})

const textRowsArr = computed(() => {
    if (!skeletonConfig.value?.textShow) return [];
    if (/%$/.test(skeletonConfig.value.textHeight)) {
        console.error('x-skeleton: textHeight参数不支持百分比单位');
    }
    const rows = []
    for (let i = 0; i < skeletonConfig.value.textRows; i++) {
        const { gridRows, textWidth, textHeight } = skeletonConfig.value;
        let item: any = {},
            // 需要预防超出数组边界的情况
            rowWidth = isArray(textWidth) ? (textWidth[i] || (i === gridRows - 1 ? '70%' : '100%')) : i === gridRows - 1 ? '70%' : textWidth,
            rowHeight = isArray(textHeight) ? (textHeight[i] || '30rpx') : textHeight
        // 非百分比的宽度时，调整像素单位
        if (/%$/.test(rowWidth)) {
            item.width = rowWidth;
        } else {
            item.width = addUnit(rowWidth)
        }
        item.height = addUnit(rowHeight)
        rows.push(item)
    }
    return rows
})

const variableStr = computed(() => {
    let keys = ['animateTime', 'fadeOutTime', 'bgColor', 'highlightBgColor'];
    let str = keys.map(item => {
        if (item.indexOf('Time') > -1) {
            return `--${ item }:${ props[item] }s`
        } else {
            return `--${ item }:${ props[item] }`
        }
    }).join(";");
    return str;
})

watch(
    () => props.loading,
    (value) => {
        if (value) {
            skeletonLoading.value = true;
        } else {
            if (props.fadeOut) {
                startFadeOut.value = true;
                setTimeout(() => {
                    skeletonLoading.value = false;
                    startFadeOut.value = false;
                }, props.fadeOutTime * 1000);
            } else {
                skeletonLoading.value = false;
            }
        }
    },
    { immediate: true }
)

watch(
    () => props.type,
    (value) => {
        if (value === 'banner') {
            skeletonConfig.value = bannerConfig(props.config);
        } else if (value === 'info') {
            skeletonConfig.value = infoConfig(props.config);
        } else if (value === 'text') {
            skeletonConfig.value = textConfig(props.config);
        } else if (value === 'menu') {
            skeletonConfig.value = menuConfig(props.config);
        } else if (value === 'list') {
            skeletonConfig.value = listConfig(props.config);
        } else if (value === 'waterfall') {
            skeletonConfig.value = waterfallConfig(props.config);
        } else {
            skeletonConfig.value = props.config || {};
        }
    },
    { immediate: true }
)

/**
 * @param {object} value 需要判断的对象
 */
const isArray = (value: any) => {
    if (typeof Array.isArray === 'function') {
        return Array.isArray(value)
    }
    return Object.prototype.toString.call(value) === '[object Array]'
}

/**
 * @description 添加单位，如果有rpx，upx，%，px等单位结尾或者值为auto，直接返回，否则加上px单位结尾
 * @param {string|number} value 需要添加单位的值
 * @param {string} unit 添加的单位名 比如px
 */
const addUnit = (value = 'auto', unit = 'px') => {
    value = String(value);
    // 用uView内置验证规则中的number判断是否为数值
    return /^[\+-]?(\d+\.?\d*|\.\d+|\d\.\d+e\+\d+)$/.test(value) ? `${ value }${ unit }` : value;
}

</script>

<style lang="scss" scoped>
@mixin background {
    //background: linear-gradient(90deg, var(--bgColor) 25%, var(--highlightBgColor) 37%, var(--bgColor) 50%);
    background: linear-gradient(90deg, #EAEDF5 25%, #F9FAFF 37%, #EAEDF5 50%);

    background-size: 400% 100%;
}

.x-skeleton {
    width: 100%;
    box-sizing: border-box;

    .x-skeleton__wrapper {
        display: flex;
        flex-direction: column;

        &__rows {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        &__columns {
            display: flex;
            align-items: center;
            flex: 1;
        }

        &__head {
            width: 100%;
            @include background;
        }

        &__text {
            flex: 1;
            width: 100%;

            &__row {
                @include background;
            }
        }
    }

    .fade-out {
        opacity: 0;
        //animation: fadeOutAnim var(--fadeOutTime);
        animation: fadeOutAnim 0.5s;
    }

    @keyframes fadeOutAnim {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    .animate {
        //animation: skeletonAnim var(--animateTime) ease infinite;
        animation: skeletonAnim 1.8s ease infinite;
    }

    @keyframes skeletonAnim {
        0% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0 50%;
        }
    }
}
</style>
