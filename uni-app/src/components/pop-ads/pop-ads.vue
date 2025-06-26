<template>
    <view @touchmove.prevent.stop class="share-popup">
        <u-popup :show="adsPopShow" mode="center" @close="adsPopupClose" overlayOpacity="0.3" bgColor="transparent">
            <view @touchmove.prevent.stop class="flex flex-col items-center" v-if="data && data.imgUrl">
                <image :src="img(data.imgUrl)" :style="popWindowStyle()"  @click="diyStore.toRedirect(data.link)"/>
                <text @click="adsPopupClose" class="mt-[50rpx] nc-iconfont nc-icon-cuohaoV6xx1 !text-[50rpx] text-[#fff]"></text>  
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue';
import { redirect, img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
const diyStore = useDiyStore();

const props = defineProps({
    data: {
        type: Object,
        default: {}
    }
})

const data = computed(() => {
    return props.data.popWindow;
})

const adsPopShow = ref(false);
onMounted(() => {
    nextTick(() => {
        if (data.value.count == 'always'){
            uni.removeStorageSync(data.value.id + '_pop_window_count');
        }
        if (uni.getStorageSync('isOnLoad')){
            adsPopupShow()
            uni.removeStorageSync('isOnLoad')
        }
    })
})

const popWindowStyle =() => {
    // 做大展示宽高
    const MAX_WIDTH = 290;
    const MAX_HEIGHT = 410;
    // 参照宽高
    const REFER_WIDTH = 290;
    const REFER_HEIGHT = 290;

    const scale = data.value.imgHeight / data.value.imgWidth;
    let width, height;
    if (scale <= REFER_HEIGHT / REFER_WIDTH) {
        width = MAX_WIDTH;
        height = width * scale;
    } else {
        height = MAX_HEIGHT;
        width = height / scale;
    }

    return `height:${height * 2}rpx;width:${width * 2}rpx;`;
}

const adsPopupShow = () => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate' || !data.value.show) return false
    const popWindowCount = uni.getStorageSync(data.value.id + '_pop_window_count');
    if (popWindowCount === 'always' || popWindowCount == '') {
        adsPopShow.value = true;
    }
}

const adsPopupClose = () => {
    adsPopShow.value = false;
    uni.setStorageSync(data.value.id + '_pop_window_count', data.value.count);
}

// onBeforeUnmount(()=>{
//     uni.removeStorageSync(data.value.id + '_pop_window_count');
// })
</script>

<style scoped lang="scss">

</style>
