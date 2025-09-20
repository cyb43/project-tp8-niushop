<template>
    <view class="easy-load-image" :style="imageStyle" :id="uid">
        <image class="origin-img" :src="img(imageSrc)" :mode="mode"
               v-if="loadImg && !isLoadError"
               v-show="showImg"
               :style="imageStyle"
               :class="[imageClass, !openTransition ? 'no-transition' : '', showTransition && openTransition ? 'show-transition' : '']"
               @load="handleImgLoad" @error="handleImgError">
        </image>
        <view class="load-fail-img" v-else-if="isLoadError"></view>
        <view :class="['loading-img','spin-circle',loadingMode]" v-show="!showImg && !isLoadError"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick, getCurrentInstance } from 'vue'
import { img, debounce } from '@/utils/common'
import useSystemStore from '@/stores/system'

// 生成全局唯一id
const generateUUID = () => {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        let r = Math.random() * 16 | 0,
            v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    })
}

const props = defineProps({
    imageSrc: {
        type: String,
        default: ''
    },
    imageClass:{
        type: String,
        default: ''
    },
    imageStyle:{
        type: String,
        default: ''
    },
    mode: {
        type: String,
        default: 'aspectFill'
    },
    loadingMode: {
        type: String,
        default: 'looming-gray'
    },
    openTransition: {
        type: Boolean,
        default: true,
    },
    viewHeight: {
        type: Number,
        default() {
            return useSystemStore().systemInfo.windowHeight
        }
    }
});

const uid = ref('uid-' + generateUUID())
const loadImg = ref(false)
const showImg = ref(false)
const isLoadError = ref(false)
const showTransition = ref(false)
const instance = getCurrentInstance();

const scrollFn = debounce(() => {
    // 加载img时才执行滚动监听判断是否可加载
    if (loadImg.value || isLoadError.value) return;
    const id = uid.value
    const query = uni.createSelectorQuery().in(instance);
    query.select('#' + id).boundingClientRect((data: any) => {
        if (!data) return;
        if (data.top - props.viewHeight < 0) {
            loadImg.value = !!props.imageSrc
            isLoadError.value = !loadImg.value
        }
    }).exec()
}, 200)

const handleImgLoad = () => {
    showImg.value = true;
    setTimeout(() => {
        showTransition.value = true
    }, 50)
}

const handleImgError = () => {
    isLoadError.value = true;
}

const init = () => {
    nextTick(onScroll)
}

const onScroll = () => {
    scrollFn();
}

onMounted(() => {
    init()
    uni.$on('scroll', scrollFn)
    onScroll()
})

onBeforeUnmount(() => {
    uni.$off('scroll', scrollFn)
})
</script>

<style scoped lang="scss">
.easy-load-image {
    position: relative;
}

/* 官方优化图片tips */
image {
    will-change: transform;
    /* 渐变过渡效果处理 */
    &.origin-img {
        width: 100%;
        height: 100%;
        opacity: 0.3;

        &.show-transition {
            transition: opacity .5s;
            opacity: 1;
        }

        &.no-transition {
            opacity: 1;
        }
    }
}

/* 加载失败、加载中的占位图样式控制 */
.load-fail-img {
    height: 100%;
    background: url('./fail.png') no-repeat center;
    background-size: 50%;
}

.loading-img {
    height: 100%;
}

/* 转圈 */
.spin-circle {
    background: url('./loading.png') no-repeat center;
    background-size: 60%;
}

/* 动态灰色若隐若现 */
.looming-gray {
    animation: looming-gray 1s infinite linear;
    background-color: #E3E3E3;
    border-radius: 12rpx;
}

@keyframes looming-gray {
    0% {
        background-color: #E3E3E3AA;
    }

    50% {
        background-color: #E3E3E3;
    }

    100% {
        background-color: #E3E3E3AA;
    }
}
</style>