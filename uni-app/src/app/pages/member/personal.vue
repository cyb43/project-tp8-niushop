<template>
    <view class="w-full min-h-screen bg-page personal-wrap !pb-[20rpx] overflow-hidden" v-if="info" :style="themeColor()">
        <view class="my-[var(--top-m)] sidebar-margin overflow-hidden card-template py-[20rpx]">
            <u-cell-group :border="false" class="cell-group">
                <u-cell :title="t('headimg')" :titleStyle="{'font-size': '28rpx'}" :is-link="true">
                    <template #value>
                        <!-- #ifdef MP-WEIXIN -->
                        <button open-type="chooseAvatar" @chooseavatar="onChooseAvatar" :plain="true" class="border-0" @click="checkWxPrivacy">
                            <u-avatar :src="img(info.headimg)" :default-url="img('static/resource/images/default_headimg.png')" size="40" leftIcon="none" />
                        </button>
                        <!-- #endif -->
                        <!-- #ifndef MP-WEIXIN -->
                        <u-upload @afterRead="afterRead" :maxCount="1">
                            <u-avatar :src="img(info.headimg)" :default-url="img('static/resource/images/default_headimg.png')" size="40" leftIcon="none" />
                        </u-upload>
                        <!-- #endif -->
                    </template>
                </u-cell>
                <u-cell :title="t('nickname')" :titleStyle="{'font-size': '28rpx'}" :is-link="true" :value="info.nickname" @click="updateNickname.modal = true"></u-cell>
                <u-cell :title="t('sex')" :titleStyle="{'font-size': '28rpx'}" :is-link="true" :value="info.sex_name || t('unknown')" @click="sexSheetShow = true"></u-cell>
                <u-cell :title="t('mobile')" :titleStyle="{'font-size': '28rpx'}">
                    <template #value>
                        <view v-if="info.mobile" class="mr-[10rpx]">{{ mobileConceal(info.mobile) }}</view>
                        <view v-else>

                            <!-- #ifdef H5 -->
                            <button @click="redirect({ url: '/app/pages/auth/bind' })" class="bg-transparent w-[170rpx] p-[0] rounded-[100rpx] text-[var(--primary-color)] !border-[2rpx] !border-solid border-[var(--primary-color)] text-[24rpx] h-[54rpx] flex-center">{{ t('bindMobile') }}</button>
                            <!-- #endif -->

                            <!-- #ifdef MP-WEIXIN -->
                            <button class="bg-transparent w-[170rpx] p-[0] rounded-[100rpx] text-[var(--primary-color)] !border-[2rpx] !border-solid border-[var(--primary-color)] text-[24rpx] h-[54rpx] flex-center"
                                open-type="getPhoneNumber" @getphonenumber="memberStore.bindMobile">{{ t('bindMobile') }}</button>
                            <!-- #endif -->

                        </view>
                    </template>
                </u-cell>
                <u-cell :title="t('birthday')" :titleStyle="{'font-size': '28rpx'}" :is-link="true" :value="formatDate(info.birthday) || t('unknown')" @click="birthdayPicker = true"></u-cell>
            </u-cell-group>
        </view>

        <!-- 商品的万能表单信息 -->
        <view @click="redirect({ url: '/app/pages/member/personal_form', param: { form_id: info.form_id, form_record_id: info.form_record_id} })" >
            <personal-form-detail class="personal-form" :data="personalFormData" completeLayout="style-2" v-if="personalFormData && Object.keys(personalFormData).length"/>
        </view>

        <!-- 修改昵称 -->
        <u-popup class="popup-type" :safeAreaInsetBottom="false" round="var(--rounded-big)" :show="updateNickname.modal" mode="center" @close="updateNickname.modal = false">
            <view class="w-[620rpx] popup-common pb-[40rpx]" @touchmove.prevent.stop>
                <view class="title !pt-[50rpx] !pb-[60rpx]">{{ t('updateNickname') }}</view>
                <view class="mx-[50rpx] border-0 border-b border-[#eee] border-solid">
                    <input type="nickname" class="h-[88rpx] text-[26rpx]" v-model="updateNickname.value" :placeholder="t('nicknamePlaceholder')" placeholderClass="text-[26rpx] h-[88rpx] flex items-center" @blur="bindNickname" />
                </view>
                <view class="px-[60rpx] pt-[70rpx]">
                    <button hover-class="none" class="primary-btn-bg text-[#fff] h-[80rpx] font-500 leading-[80rpx] rounded-[100rpx] text-[26rpx]" @click="updateNicknameConfirm">{{ t('confirm') }}</button>
                </view>
            </view>
        </u-popup>

        <!-- 修改性别 -->
        <u-action-sheet :actions="sexList" :show="sexSheetShow" :closeOnClickOverlay="true" :safeAreaInsetBottom="true" @close="sexSheetShow = false" @select="updateSex"></u-action-sheet>

        <!-- 修改生日 -->
        <u-datetime-picker v-model="info.birthday" :show="birthdayPicker" mode="date" :confirm-text="t('confirm')"
                           :maxDate="new Date().valueOf()" :minDate="0"
                           :cancel-text="t('cancel')" @cancel="birthdayPicker = false"
                           @confirm="updateBirthday"></u-datetime-picker>

        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, computed, reactive, nextTick } from 'vue'
import { t } from '@/locale'
import useMemberStore from '@/stores/member'
import { img, redirect, mobileConceal } from '@/utils/common'
import { modifyMember } from '@/app/api/member'
import { fetchBase64Image, uploadImage } from '@/app/api/system'
import { onLoad } from '@dcloudio/uni-app'
import { getMemberFormRecord } from '@/app/api/diy'
import personalFormDetail from '@/app/pages/member/components/personal_form_detail.vue'

const memberStore = useMemberStore()
const info = computed(() => memberStore.info)

const wxPrivacyPopupRef: any = ref(null)

onLoad(() => {
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
})

/**
 * 修改昵称
 */
const updateNickname = reactive({
    modal: false,
    value: info.nickname || ''
})

const bindNickname = (e: any) => {
    updateNickname.value = e.detail.value
}

let personalFormData = ref(null)
const getMemberFormRecordFn = () => {
    getMemberFormRecord().then((res: any) => {
        personalFormData.value = res.data
    }) 
}
getMemberFormRecordFn()

const updateNicknameConfirm = () => {
    if (uni.$u.test.isEmpty(updateNickname.value)) {
        uni.showToast({ title: t('nicknamePlaceholder'), icon: 'none' });
        return
    }

    modifyMember({
        field: 'nickname',
        value: updateNickname.value
    }).then(res => {
        memberStore.info.nickname = updateNickname.value
        updateNickname.modal = false
    })
}

/**
 * 修改性别
 */
const sexSheetShow = ref(false)
const sexList = computed(() => {
    return [
        { name: t('man'), value: 1 },
        { name: t('woman'), value: 2 }
    ]
})
const updateSex = (e: any) => {
    modifyMember({
        field: 'sex',
        value: e.value
    }).then(res => {
        memberStore.info.sex_name = e.name
    })
}

/**
 * 修改用户头像
 */
const onChooseAvatar = (e: any) => {
    uni.getFileSystemManager().readFile({
        filePath: e.detail.avatarUrl, //选择图片返回的相对路径
        encoding: 'base64', //编码格式
        success: res => {
            fetchBase64Image({ content: res.data }).then((uploadRes: any) => {
                modifyMember({
                    field: 'headimg',
                    value: uploadRes.data.url
                }).then(res => {
                    memberStore.info.headimg = uploadRes.data.url
                })
            })
        }
    })
}

const afterRead = (event: any) => {
    uploadImage({
        filePath: event.file.url,
        name: 'file'
    }).then((res: any) => {
        modifyMember({
            field: 'headimg',
            value: res.data.url
        }).then(() => {
            memberStore.info.headimg = res.data.url
        })
    }).catch(() => {
    })
}

/**
 * 编辑生日
 */
const birthdayPicker = ref(false)
const updateBirthday = (e: any) => {
    modifyMember({
        field: 'birthday',
        value: uni.$u.date(e.value, 'yyyy-mm-dd')
    }).then(() => {
        memberStore.info.birthday = uni.$u.date(e.value || e.value + 1, 'yyyy-mm-dd')
        birthdayPicker.value = false
    })
}
const formatDate = (date: any) => {
    return date ? uni.$u.date(new Date(date), 'yyyy-mm-dd') : ''
}
</script>

<style lang="scss" scoped>
page {
    background: var(--page-bg-color);
}

:deep(.u-upload ) {
    flex: none;
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
            color: #333 !important;
        }

        .u-line {
            display: none;
        }
    }
}

:deep(button, button:after) {
    border: none;
}

// 修改性别
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

:deep(.u-picker) .u-toolbar {
    font-size: 32rpx !important;
    padding: 26rpx 10rpx;
    height: auto !important;
    font-weight: 500;
}
</style>
<style lang="scss">
.personal-wrap .u-cell--clickable {
    background-color: transparent !important;
}
.personal-form{
    ::v-deep .draggable-element {
        &:last-of-type{
            .detail-two-content{
                margin-bottom: 0 !important;
            }    
        }
    	.detail-two-content{
            padding-top: 30rpx !important;
            padding-bottom: 30rpx !important;
            margin-bottom: 0 !important;
            & > view{
                width: 72% !important;
                .image-item{
                    width: 136rpx !important;
                    height: 136rpx !important;
                }
            }
            & > view, .detail-two-content-label{
                font-size: 26rpx!important;
            }
        }
    }
}
</style>

