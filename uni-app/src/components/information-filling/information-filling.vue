<template>
    <u-popup :show="show" :round="10" @close="show = false">
        <view class="information-filling">
            <view class="relative">
                <view class="mx-[30rpx] pb-[20rpx] pt-[36rpx] border-t">
                    <view class="text-[30rpx]">{{ t('getAvatarNickname') }}</view>
                    <view class="text-[24rpx] mt-[18rpx] leading-[1.4] text-gray-400">{{ t('getAvatarNicknameTips') }}</view>
                </view>
                <text class="nc-iconfont nc-icon-guanbiV6xx2 !text-[40rpx] absolute top-[28rpx] right-[30rpx]" @click="show = false"></text>
            </view>
            <u-form labelPosition="left" :model="formData" errorType='toast' :rules="rules" ref="formRef" labelWidth="65">
                <view class="mx-[30rpx]">
                    <view class="mt-[20rpx]">
                        <u-form-item :label="t('headimg')" prop="headimg" :border-bottom="true">
                            <button class="m-0 p-0 w-[120rpx] h-[120rpx]" open-type="chooseAvatar" @chooseavatar="onChooseAvatar" @click="checkAuth($event, 'headimg')">
                                <view class="w-full h-full flex items-center justify-center overflow-hidden">
                                    <u-image :src="img(formData.headimg)" width="120rpx" height="120rpx" v-if="formData.headimg" mode="aspectFill"/>
                                    <u-icon name="plus" v-else></u-icon>
                                </view>
                            </button>
                        </u-form-item>
                        <u-form-item :label=" t('nickname')" prop="nickname" :border-bottom="true">
                            <input type="nickname" v-model="formData.nickname" :placeholder="t('nicknamePlaceholder')" placeholderClass="text-[28rpx]" class="text-[28rpx]" @blur="bindNickname" @click="checkAuth($event, 'nickname')"/>
                        </u-form-item>
                        <u-form-item :label="t('mobile')" prop="mobile" :border-bottom="true" v-if="isBindMobile">
                            <input type="mobile" v-model="formData.mobile" :disabled="true" v-if="formData.mobile">
                            <template v-else>
                                <u-button v-if="info" :customStyle="{border:'none',color: 'var(--primary-color)',width:'140rpx', textAlign:'left',margin:'0rpx'}" :text="t('getMobile')" open-type="getPhoneNumber" @getphonenumber="memberStore.bindMobile"></u-button>
                                <u-button v-else :customStyle="{border:'none',color: 'var(--primary-color)',width:'140rpx', textAlign:'left',margin:'0rpx'}" :text="t('getMobile')" open-type="getPhoneNumber" @getphonenumber="getPhoneNumber"></u-button>
                            </template>
                        </u-form-item>
                    </view>
                </view>
                <view class="p-[30rpx] mt-[20rpx]">
                    <button :loading="loading" class="bg-[var(--primary-color)] text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[28rpx]" @click="confirm">{{ t('confirm') }}</button>
                </view>
            </u-form>
        </view>
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </u-popup>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import { t } from '@/locale'
import useMemberStore from '@/stores/member'
import { img } from '@/utils/common'
import { modifyMember, getMobile } from '@/app/api/member'
import { fetchBase64Image } from '@/app/api/system'
import { useLogin } from '@/hooks/useLogin'
import useConfigStore from '@/stores/config'

const show = ref(false)
const loading = ref(false)
const memberStore = useMemberStore()
const info = computed(() => memberStore.info)
const config = useConfigStore()

const formData = reactive({
    nickname: '',
    headimg: '',
    mobile: '',
})

watch(() => info.value, () => {
    if (info.value) {
        formData.nickname = info.value.nickname
        formData.headimg = info.value.headimg
        formData.mobile = info.value.mobile
    }
}, { immediate: true })

const isBindMobile = ref(null)

const wxPrivacyPopupRef: any = ref(null)

onMounted(() => {
    uni.getStorageSync('openid') && (Object.assign(formData, { openid: uni.getStorageSync('openid') }))
    uni.getStorageSync('pid') && (Object.assign(formData, { pid: uni.getStorageSync('pid') }))
    uni.getStorageSync('unionid') && (Object.assign(formData, { unionid: uni.getStorageSync('unionid') }))
    isBindMobile.value = uni.getStorageSync('isBindMobile');
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
})

const onChooseAvatar = (e) => {
    uni.getFileSystemManager().readFile({
        filePath: e.detail.avatarUrl, //选择图片返回的相对路径
        encoding: 'base64', //编码格式
        success: res => {
            fetchBase64Image({ content: res.data }).then(uploadRes => {
                formData.headimg = uploadRes.data.url
            })
        }
    })
}

const bindNickname = (e) => {
    formData.nickname = e.detail.value
}

const rules = {
    'headimg': {
        type: 'string',
        required: true,
        message: t('headimgPlaceholder'),
        trigger: ['blur', 'change'],
    },
    'nickname': {
        type: 'string',
        required: true,
        message: t('nicknamePlaceholder'),
        trigger: ['blur', 'change'],
    },
    'mobile': {
        type: 'string',
        required: config.login.is_bind_mobile ? true : false,
        message: t('mobileTips'),
        trigger: ['blur', 'change'],
    }
}

const formRef: any = ref(null)

// 获取手机号
const getPhoneNumber = (e: any) => {
    if (e.detail.errMsg == 'getPhoneNumber:ok') {
        uni.showLoading({ title: '' })
        getMobile({
            mobile_code: e.detail.code
        }).then((res: any) => {
            formData.mobile = res.data.mobile
            uni.hideLoading()
        }).catch(() => {
            setTimeout(() => {
                uni.hideLoading()
            }, 2000);
        })
    }

    if (e.detail.errno == 104) {
        let msg = '用户未授权隐私权限';
        uni.showToast({ title: msg, icon: 'none' })
    }
    if (e.detail.errMsg == "getPhoneNumber:fail user deny") {
        let msg = '用户拒绝获取手机号码';
        uni.showToast({ title: msg, icon: 'none' })
    }
}

const confirm = async () => {
    formRef.value.validate().then(async () => {
        if (loading.value) return;
        loading.value = true;

        try {
            if (info.value) {
                // 修改头像
                await modifyMember({ field: 'headimg', value: formData.headimg });
                memberStore.info.headimg = formData.headimg;

                // 修改昵称
                await modifyMember({ field: 'nickname', value: formData.nickname });
                memberStore.info.nickname = formData.nickname;

                // 更新openid
                if (info.value && !info.value.weapp_openid) {
                    const login = useLogin();
                    await login.getAuthCode({ updateFlag: true }); // 更新openid
                }

                // 关闭弹窗
                show.value = false;

            } else {
                // todo 如果没有登录过，则注册
                // #ifdef MP-WEIXIN
                const login = useLogin();
                await login.getAuthCode({ backFlag: true, ...formData }); // 注册
				show.value = false; 
                // #endif
            }
        } catch (error) {
            // 处理任何错误
            console.error("发生错误：", error);
        } finally {
            loading.value = false; // 加载结束时停止 loading
        }
    })
}


const checkAuth = (e, type) => {
    // #ifdef MP-WEIXIN
    wx.getUserInfo({
        success: function(res) {},
        fail: function(res) {
            let content = ''
            if (type == 'nickname') {
                content = '获取昵称失败'
            } else if(type == 'headimg') {
                content = '获取头像失败'
            }

            if (res.errMsg && res.errno) {
                if (res.errno == 104) {
                    let msg = `用户未授权隐私权限，${content}`;
                    uni.showToast({ title: msg, icon: 'none' })
                } else if (res.errno == 112) {
                    let msg = `隐私协议中未声明，${content}`;
                    uni.showToast({ title: msg, icon: 'none' })
                } else {
                    uni.showToast({ title: res.errMsg, icon: 'none' })
                }
            }
        }
    });
    // #endif
}

defineExpose({
    show
})
</script>

<style lang="scss" scoped></style>
