<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" :style="themeColor()">
        <view class="fixed left-0 top-0 right-0 z-10">
            <scroll-view scroll-x="true" class="tab-style-2">
                <view class="tab-content">
                    <view class="tab-items" :class="{ 'class-select': evaluateStatus === item.status }" @click="statusClickFn(item.status, item.value)" v-for="(item, index) in statusList">{{ item.name }}</view>
                </view>
            </scroll-view>
        </view>
        <mescroll-body ref="mescrollRef" top="88rpx" @init="mescrollInit" :down="{ use: false }" @up="getEvaluateListFn">
            <view class="sidebar-margin pt-[var(--top-m)]" v-if="list.length">
                <template v-for="(item, index) in list">
                    <view class="mb-[var(--top-m)] card-template !pb-[20rpx]">
                        <view class="flex items-center  justify-between">
                            <view class="flex items-center">
                                <u-avatar :src="img(item.member_head)" :default-url="img('static/resource/images/default_headimg.png')" :size="'50rpx'" leftIcon="none" />
                                <text class="text-[30rpx] font-500 ml-[10rpx]">{{ item.member_name }}</text>
                            </view>
                            <text class="text-[24rpx] text-[var(--text-color-light9)]">{{ item.create_time ? item.create_time.slice(0, 10) : '' }}</text>
                        </view>

                        <view class="pt-[30rpx] flex items-center">
                            <u-rate :count="5" v-model="item.scores" active-color="var(--primary-color)" :size="'36rpx'" gutter="1" readonly></u-rate>
                            <text class="ml-[20rpx] text-[26rpx] text-[var(--text-color-light9)]">{{ item.scores === 1 ? '差评' : item.scores === 2 || item.scores === 3 ? '中评' : '好评' }}</text>
                        </view>
                        <view class="text-[28rpx] break-all leading-[1.2] text-[#333] my-[20rpx] overflow-clip">{{ item.content }}</view>
                        <template v-if="item.image_mid.length === 1">
                            <view class="w-[420rpx] mt-[10rpx]">
                                <u--image width="420rpx" height="420rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0],item.images)">
                                    <template #error>
                                        <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                    </template>
                                </u--image>
                            </view>
                        </template>
                        <template v-if="item.image_mid.length === 2">
                            <view class="flex justify-between mt-[10rpx]">
                                <view class="w-[322rpx]">
                                    <u--image width="322rpx" height="322rpx" :src="img(item.image_mid[0])" :radius="'var(--goods-rounded-big)'" model="aspectFill" @click="imgListPreview(item.images[0],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                                <view class="w-[322rpx]">
                                    <u--image width="322rpx" height="322rpx" :src="img(item.image_mid[1])" :radius="'var(--goods-rounded-big)'" model="aspectFill" @click="imgListPreview(item.images[1],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                            </view>
                        </template>
                        <template v-if="item.image_mid.length === 3">
                            <view class="flex justify-between mt-[10rpx]">
                                <u--image width="430rpx" height="430rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0],item.images)">
                                    <template #error>
                                        <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                    </template>
                                </u--image>
                                <view>
                                    <u--image width="205rpx" height="205rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[1])" model="aspectFill" @click="imgListPreview(item.images[1],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                    <view class="mt-[20rpx]">
                                        <u--image width="205rpx" height="205rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[2])" model="aspectFill" @click="imgListPreview(item.images[2],item.images)">
                                            <template #error>
                                                <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                            </template>
                                        </u--image>
                                    </view>
                                </view>
                            </view>
                        </template>

                        <template v-if="item.image_mid.length === 4">
                            <view class="flex flex-wrap mt-[10rpx]">
                                <view class="mr-[15rpx] mb-[15rpx]">
                                    <u--image width="215rpx" height="215rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                                <view class="mr-[15rpx] mb-[15rpx]">
                                    <u--image width="215rpx" height="215rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[1])" model="aspectFill" @click="imgListPreview(item.images[1],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                                <view class="mr-[15rpx]">
                                    <u--image width="215rpx" height="215rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[2])" model="aspectFill" @click="imgListPreview(item.images[2],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                                <view class="mr-[15rpx]">
                                    <u--image width="215rpx" height="215rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.image_mid[3])" model="aspectFill" @click="imgListPreview(item.images[3],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                            </view>
                        </template>
                        <template v-if="item.image_mid.length > 4">
                            <view class="flex flex-wrap mt-[10rpx]">
                                <view v-for="(imageItem, imageIndex) in item.image_mid" class="mb-[10rpx]" :class="{'mr-[10rpx]':(imageIndex + 1) % 3 != 0}">
                                    <u--image width="211rpx" height="211rpx" :src="img(imageItem)" model="aspectFill"
                                              :radius="'var(--goods-rounded-big)'"
                                              @click="imgListPreview(item.images[imageIndex],item.images)">
                                        <template #error>
                                            <u-icon name="photo" color="var(--text-color-light9)" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                            </view>
                        </template>
                        <view v-if="item.explain_first !=''" class="text-[26rpx]  !text-[var(--text-color-light6)] mt-[20rpx] pt-[20rpx] border-0 border-t-[2rpx] border-solid border-[#ebebec] w-[100%] overflow-clip leading-[1.2] break-all">
                            <text class=" text-[var(--primary-color)]">商家回复：</text>
                            {{ item.explain_first }}
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!list.length && loading" :option="{tip : '暂无评价'}"></mescroll-empty>
        </mescroll-body>
        <pay ref="payRef"></pay>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { img } from '@/utils/common'
import { getEvaluateList } from '@/addon/shop/api/evaluate'
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue'
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import useMescroll from '@/components/mescroll/hooks/useMescroll.js'
import { onLoad, onUnload, onPageScroll, onReachBottom } from '@dcloudio/uni-app'

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom)
const list = ref<Array<Object>>([]);
const loading = ref<boolean>(false);
const statusList = <Array<Object>>([
    { status: 1, name: '全部', value: [] },
    { status: 2, name: '好评', value: [4, 5] },
    { status: 3, name: '中评', value: [2, 3] },
    { status: 4, name: '差评', value: [1] },
]);
const evaluateStatus = ref(1);
const evaluateValue = ref([]);
const goodsId = ref('')
onLoad((option: any) => {
    goodsId.value = option.goods_id || ''
})

const statusClickFn = (status: any, value: any) => {
    evaluateStatus.value = status;
    evaluateValue.value = value
    list.value = []; //如果是第一页需手动制空列表
    getMescroll().resetUpScroll();
};

const getEvaluateListFn = (mescroll: any) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        goods_id: goodsId.value,
        scores: evaluateValue.value
    };

    getEvaluateList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);

        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}
//预览图片
const imgListPreview = (current: string, all: string[]) => {
	if (!current || !all?.length) return

	const urlList = all.map(img)
	const currentUrl = img(current)

	uni.previewImage({
		current: currentUrl,
		urls: urlList,
		indicator: 'number',
		loop: true
	})
}

//关闭预览图片
onUnload(() => {
    // #ifdef  H5 || APP
    try {
        uni.closePreviewImage()
    } catch (e) {

    }
    // #endif
})
</script>

<style lang="scss" scoped>
.class-select {
    position: relative;
    font-weight: bold;

    &::before {
        content: "";
        position: absolute;
        bottom: 0;
        height: 6rpx;
        background-color: var(--primary-color);
        width: 90%;
        left: 50%;
        transform: translateX(-50%);
    }
}
</style>
