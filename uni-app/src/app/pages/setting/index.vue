<template>
    <view class="w-full h-screen box-border pt-[var(--top-m)] bg-[var(--page-bg-color)] setting-wrap" :style="themeColor()">
        <view class="mb-[var(--top-m)] sidebar-margin card-template !py-[20rpx]">
            <u-cell-group :border="false" class="cell-group">
                <u-cell :title="t('personalSettings')" :is-link="true" url="/app/pages/member/personal"></u-cell>
                <u-cell :title="t('switchLang')" :is-link="true" :value="lang" @click="langSheetShow = true"></u-cell>
                <u-cell :title="t('version')" :value="version"></u-cell>
            </u-cell-group>
        </view>
        <view class="mb-[var(--top-m)] sidebar-margin card-template !py-[20rpx]">
            <u-cell-group :border="false" class="cell-group">
                <u-cell :title="t('userAgreement')" :is-link="true" url="/app/pages/auth/agreement?key=service"></u-cell>
                <u-cell :title="t('privacyAgreement')" :is-link="true" url="/app/pages/auth/agreement?key=privacy"></u-cell>
            </u-cell-group>
        </view>

        <view class="h-[88rpx] flex-center bg-[#fff] mx-[var(--sidebar-m)] rounded-[var(--rounded-big)] text-[26rpx]" @click="memberStore.logout(true)">{{ t('logout') }}</view>

        <u-action-sheet :actions="langList" :show="langSheetShow" :closeOnClickOverlay="true"
                        :safeAreaInsetBottom="true"
                        @close="langSheetShow = false" @select="switchLang"></u-action-sheet>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import useMemberStore from '@/stores/member'
import { t, language } from '@/locale'

const memberStore = useMemberStore()

const version = ref(import.meta.env.VITE_APP_VERSION)

/**
 * 支持的语言列表
 */
const langList = reactive({
    'zh-Hans': { name: '简体中文', fontSize: '14', value: 'zh-Hans' },
    'en': { name: 'English', fontSize: '14', value: 'en' }
})
const langSheetShow = ref(false)

// 当前语言
const lang = computed(() => {
    const lang = uni.getLocale()
    return langList[lang].name
})

/**
 * 切换语言
 */
const switchLang = (lang) => {
    language.loadAllLocaleMessages('app', lang.value)
}
</script>

<style lang="scss" scoped>
page {
    background: var(--page-bg-color);
}

:deep(.cell-group), :deep(.u-cell-group) {
    .u-cell {
        .u-cell__body {
            padding: 0;
            height: 80rpx;
            margin-top: 16rpx;
        }

        &:first-of-type .u-cell__body {
            margin-top: 0;
        }

        .u-cell__title-text {
            font-size: 26rpx;
            line-height: 40rpx;
        }

        .u-icon__icon {
            font-size: 24rpx !important;
        }

        .u-cell__value {
            line-height: 1;
            font-size: 26rpx;
        }

        .u-line {
            display: none;
        }
    }
}

:deep(.u-action-sheet) {
    .u-line {
        margin: 0 30rpx !important;
        width: auto !important;
        border-color: #ddd !important;
    }

    .u-action-sheet__cancel {
        padding: 0;

    }

    .u-action-sheet__item-wrap__item__name {
        font-size: 26rpx !important;
    }
}
</style>
<style>
.setting-wrap .u-cell--clickable {
    background-color: transparent !important;
}
</style>
