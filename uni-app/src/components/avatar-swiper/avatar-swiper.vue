<template>
    <view class="carousel-container">
        <view class="carousel-track" :style="trackStyle">
            <view
                v-for="(item, index) in displayList"
                :key="item.key" 
                class="carousel-item"
                :style="itemStyle(index)"
                :class="{ 'scale-in': item.isNew }"
            >
                <u--image width="30rpx" height="30rpx" radius="15rpx" :src="img(item.src)" mode="aspectFill">
                    <template #error>
                        <image
                            class="w-[30rpx] h-[30rpx] rounded-full"
                            :src="img('static/resource/images/default_headimg.png')"
                            mode="aspectFill"
                        />
                    </template>
                </u--image>
            </view>
        </view>
    </view>
</template>
<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { img } from '@/utils/common';

const props = defineProps<{
    avatars: string[];
    interval?: number; // 动画间隔时间（默认2000ms）
}>();

// 配置参数（优化动画连贯性）
const VISIBLE_COUNT = 3;
const ITEM_WIDTH = 30;
const OVERLAP = 10;
const ANIMATION_DURATION = 500; // 稍长动画，减少急促感
const EASING = 'cubic-bezier(0.25, 0.1, 0.25, 1)'; // 平滑曲线
const slideDistance = ITEM_WIDTH - OVERLAP;

// 状态管理
const displayList = ref<{ 
    src: string; 
    isNew: boolean; 
    key: string; // 唯一key，避免v-for复用导致的闪烁
}[]>([]);
let currentIndex = 0;
let timer: ReturnType<typeof setInterval> | null = null;
const isTransitioning = ref(false);
const trackOffset = ref(0);

/**
 * 初始化列表：确保初始状态稳定
 */
const initList = () => {
    if (props.avatars.length <= VISIBLE_COUNT) {
        displayList.value = props.avatars.map((src, i) => ({
            src,
            isNew: true,
            key: `init-${i}-${src}` // 唯一key
        }));
    } else {
        displayList.value = props.avatars.slice(0, VISIBLE_COUNT).map((src, i) => ({
            src,
            isNew: true,
            key: `init-${i}-${src}`
        }));
        currentIndex = VISIBLE_COUNT;
    }
};

/**
 * 单个头像样式：同步动画参数
 */
const itemStyle = (index: number) => ({
    zIndex: VISIBLE_COUNT + 1 - index,
    transition: `transform ${ANIMATION_DURATION}ms ${EASING}`, // 与轨道动画完全同步
});

/**
 * 轨道样式：启用硬件加速，优化过渡
 */
const trackStyle = computed(() => ({
    transform: `translateX(${trackOffset.value}rpx)`,
    transition: isTransitioning.value 
        ? `transform ${ANIMATION_DURATION}ms ${EASING}` 
        : 'none',
    display: 'flex',
    // 硬件加速：减少重绘闪烁
    willChange: isTransitioning.value ? 'transform' : 'auto',
    backfaceVisibility: 'hidden',
    perspective: '1000px',
}));

/**
 * 滑动动画：优化时序，避免闪烁
 */
const slide = async () => {
    if (props.avatars.length <= VISIBLE_COUNT || isTransitioning.value) return;
    isTransitioning.value = true;

    try {
        // 1. 提前插入新头像（带唯一key，避免复用闪烁）
        const nextKey = `slide-${currentIndex}-${Date.now()}`; // 用时间戳确保唯一
        const nextAvatar = {
            src: props.avatars[currentIndex % props.avatars.length],
            isNew: false, // 初始隐藏
            key: nextKey
        };
        displayList.value.unshift(nextAvatar);
        await nextTick(); // 等待DOM稳定渲染

        // 2. 重置轨道位置（无动画，为滑动做准备）
        trackOffset.value = -slideDistance;
        isTransitioning.value = false;
        await nextTick(); // 确保位置重置生效

        // 3. 启动向右滑动动画（核心步骤）
        isTransitioning.value = true;
        trackOffset.value = 0; // 向右移动到目标位置

        // 4. 同步显示新头像（与滑动动画同步开始）
        setTimeout(() => {
            nextAvatar.isNew = true; // 新头像缩放显示，与滑动同步
        }, 500); // 轻微延迟，模拟“滑入时逐渐显示”

        // 5. 等待动画完全结束（关键：确保动画结束后再操作）
        await new Promise(resolve => {
            setTimeout(resolve, ANIMATION_DURATION);
        });

        // 6. 无痕移除最右侧旧头像（动画结束后，无视觉闪烁）
        displayList.value.pop();

        // 7. 更新索引，为下次动画准备
        currentIndex = (currentIndex + 1) % props.avatars.length;

    } finally {
        isTransitioning.value = false; // 解锁
    }
};

/**
 * 组件挂载与清理
 */
onMounted(() => {
    if (props.avatars?.length) {
        initList();
        if (props.avatars.length > VISIBLE_COUNT) {
            const delay = props.interval || 2000;
            timer = setInterval(slide, delay);
        }
    }
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<style scoped lang="scss">
.carousel-container {
    width: 70rpx;
    height: 30rpx;
    overflow: hidden;
    position: relative;
    display: flex;
    justify-content: flex-end; // 不足3个时右对齐
}

.carousel-track {
    height: 100%;
    position: relative;
}

.carousel-item {
    width: 30rpx;
    height: 30rpx;
    position: relative;
    flex-shrink: 0;
    &:not(:first-child) {
        margin-left: -15rpx; // 固定重叠，避免计算误差
    }
    transform: scale(0); // 初始隐藏
}

// 缩放动画与滑动动画完全同步
.carousel-item.scale-in {
    transform: scale(0.9);
}
</style>