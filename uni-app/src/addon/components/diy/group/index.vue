<template>
    <view class="diy-group" id="componentList">
        <top-tabbar v-if="data.global && Object.keys(data.global).length && data.global.topStatusBar && data.global.topStatusBar.isShow"
                    :scrollBool="diyGroup.componentsScrollBool.TopTabbar" ref="topTabbarRef" :data="data.global" />
        <pop-ads v-if="data.global && Object.keys(data.global).length && data.global.popWindow && data.global.popWindow.show" ref="popAbsRef" :data="data.global" />
        <template v-for="(component, index) in data.value" :key="component.id">
            <view
                v-show="component.componentIsShow"
                @click="diyStore.changeCurrentIndex(index, component)"
                :class="diyGroup.getComponentClass(index,component)" :style="component.pageStyle"
            >
                <view class="relative" :style="{ marginTop : component.margin.top < 0 ? (component.margin.top * 2) + 'rpx' : '0' }">

                    <!-- 装修模式下，设置负上边距后超出的内容，禁止选中设置 -->
                    <view v-if="diyGroup.isShowPlaceHolder(index,component)" class="absolute w-full z-1"
                          :style="{ height : (component.margin.top * 2 * -1) + 'rpx' }"
                          @click.stop="diyGroup.placeholderEvent"></view>

                    <template v-if="component.componentName == 'GraphicNav'">
                        <diy-graphic-nav :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'HorzBlank'">
                        <diy-horz-blank :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'HorzLine'">
                        <diy-horz-line :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'HotArea'">
                        <diy-hot-area :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'ImageAds'">
                        <diy-image-ads :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'MemberInfo'">
                        <diy-member-info :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'MemberLevel'">
                        <diy-member-level :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'Notice'">
                        <diy-notice :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'RubikCube'">
                        <diy-rubik-cube :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'Text'">
                        <diy-text :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'RichText'">
                        <diy-rich-text :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'ActiveCube'">
                        <diy-active-cube :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FloatBtn'">
                        <diy-float-btn :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'CarouselSearch'">
                        <diy-carousel-search :scrollBool="diyGroup.componentsScrollBool.CarouselSearch" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'PictureShow'">
                        <diy-picture-show :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormSubmit'">
                        <diy-form-submit :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormInput'">
                        <diy-form-input ref="diyFormInputRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormTextarea'">
                        <diy-form-textarea ref="diyFormTextareaRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormIdentity'">
                        <diy-form-identity ref="diyFormIdentityRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormEmail'">
                        <diy-form-email ref="diyFormEmailRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormMobile'">
                        <diy-form-mobile ref="diyFormMobileRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormWechatName'">
                        <diy-form-wechat-name ref="diyFormWechatNameRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormNumber'">
                        <diy-form-number ref="diyFormNumberRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormRadio'">
                        <diy-form-radio ref="diyFormRadioRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormCheckbox'">
                        <diy-form-checkbox ref="diyFormCheckboxRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormTable'">
                        <diy-form-table ref="diyFormTableRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormDate'">
                        <diy-form-date ref="diyFormDateRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormDateScope'">
                        <diy-form-date-scope ref="diyFormDateScopeRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormTime'">
                        <diy-form-time ref="diyFormTimeRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormTimeScope'">
                        <diy-form-time-scope ref="diyFormTimeScopeRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormLocation'">
                        <diy-form-location ref="diyFormLocationRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormAddress'">
                        <diy-form-address ref="diyFormAddressRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormImage'">
                        <diy-form-image ref="diyFormImageRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormVideo'">
                        <diy-form-video ref="diyFormVideoRef" :component="component" :global="data.global" :index="index" />
                    </template>
                    <template v-if="component.componentName == 'FormFile'">
                        <diy-form-file ref="diyFormFileRef" :component="component" :global="data.global" :index="index" />
                    </template>
                </view>
            </view>
        </template>
        <template v-if="diyStore.mode == '' && data.global && data.global.bottomTabBar && data.global.bottomTabBar.isShow">
            <view class="pt-[20rpx]"></view>
            <tabbar />
        </template>
    </view>
</template>
<script lang="ts" setup>
import topTabbar from '@/components/top-tabbar/top-tabbar.vue'
import popAds from '@/components/pop-ads/pop-ads.vue'
import { useDiyGroup } from './useDiyGroup'
import useDiyStore from '@/app/stores/diy';
import { ref, getCurrentInstance } from 'vue';

const props = defineProps(['data']);
const instance: any = getCurrentInstance();
const getFormRef = () => {
    return {
        componentRefs: instance.refs
    }
}

const diyStore = useDiyStore();

const diyGroup = useDiyGroup({
    ...props,
    getFormRef
});

const data = ref(diyGroup.data)

// 监听页面加载完成
diyGroup.onMounted()

// 监听滚动事件
diyGroup.onPageScroll()

defineExpose({
    refresh: diyGroup.refresh,
    getFormRef
})
</script>
<style lang="scss" scoped>
@import './index.scss';
</style>
