<template>
    <view :style="themeColor()">

        <loading-page :loading="diy.getLoading()"></loading-page>

        <view v-show="requestData.status == 1 && requestData.error && requestData.error.length === 0 && !diy.getLoading()">

            <!-- 自定义模板渲染 -->
            <view class="diy-template-wrap bg-index" :style="diy.pageStyle()">

                <diy-group ref="diyGroupRef" :data="diy.data" />

            </view>

        </view>
        <view class="w-screen h-screen flex flex-col " v-if="requestData.error && requestData.error.length > 0">
            <view class="flex-1 flex flex-col items-center pt-[180rpx] px-[60rpx]" v-for="(item, index) in requestData.error.slice(0, 1)" :key="index">
                <text class="nc-iconfont nc-icon-tanhaoV6mm text-[#ccc] mb-[30rpx] !text-[100rpx]"></text>
                <view class="text-[38rpx] font-bold mt-3">{{ item.title }}</view>
                <view class="p-[30rpx] mt-10 w-full ">
                    <view class="flex w-full">
                        <view class="w-[30%] text-[#999] text-left">{{ item.type }}</view>
                        <view class="w-[70%] text-left">{{ item.desc }}</view>
                    </view>
                </view>
            </view>
            <view class="pb-[260rpx]">
                <button class="w-[380rpx] !border-0 h-[80rpx] text-[28rpx] text-[#333] !bg-[#f2f2f2] flex-center font-500 rounded-[20rpx]" :plain="true" @click="finishFn">{{ t('close') }}</button>
            </view>
        </view>

        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->

    </view>
</template>

<script setup lang="ts">
import { ref, nextTick, computed } from 'vue';
import { useDiyForm } from '@/hooks/useDiyForm'
import { useShare } from '@/hooks/useShare'
import { onLoad, onUnload } from '@dcloudio/uni-app'
import { img, redirect } from '@/utils/common';
import { t } from '@/locale'
import diyGroup from '@/addon/components/diy/group/index.vue'
import { getFormRecord } from '@/app/api/diy_form'

const { setShare } = useShare()

const diy = useDiyForm({
    needLogin: true // 检测登录
})

onLoad((data: any) => {
    if(data.form_record_id) {
        uni.setStorageSync('personalFormRecordId', data.form_record_id)
    }
})
onUnload(()=>{
    uni.removeStorageSync('personalFormRecordId')  
})

const diyGroupRef = ref(null)

const wxPrivacyPopupRef: any = ref(null)

const requestData = computed(() => {
    return diy.requestData;
})

const finishFn = () => {
    redirect({
        url: '/app/pages/index/index',
        mode: 'reLaunch'
    });
}

diy.onLoad((data: any) => {
    let share = data.share ? data.share : null;
    setShare(share);
    diyGroupRef.value?.refresh();

    getFormRecord({
        record_id: data.form_record_id
    }).then((res: any) => {
        let formDetailData = {}
        if(res.data && res.data.recordsFieldList){
            res.data.recordsFieldList.forEach((item: any) => {
                formDetailData[item.field_key] = item.field_value;
            })
        }
        diy.data.value.forEach((item: any) => {
            if(formDetailData[item.id]){
                try {
                    item.field.value = JSON.parse(formDetailData[item.id])
                    if (item.componentName=="FormNumber" || item.componentName=="FormIdentity") {
                        item.field.value = String(formDetailData[item.id])
                    }
                } catch (e) {
                    item.field.value = formDetailData[item.id]
                }
            }
        });
    }).catch(() => {
    })
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
});

// 监听页面显示
diy.onShow((data: any) => {
    let share = data.share ? data.share : null;
    if (share) {
        setShare(share);
    }
    diyGroupRef.value?.refresh();
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
});

// 监听页面隐藏
diy.onHide();

// 监听页面卸载
diy.onUnload();

// 监听滚动事件
diy.onPageScroll()
</script>
<style lang="scss" scoped>
@import '@/styles/diy.scss';
</style>
<style lang="scss">
.diy-template-wrap {
    /* #ifdef MP */
    .child-diy-template-wrap {
        ::v-deep .diy-group {
            > .draggable-element.top-fixed-diy {
                display: block !important;
            }
        }
    }

    /* #endif */
}
</style>
