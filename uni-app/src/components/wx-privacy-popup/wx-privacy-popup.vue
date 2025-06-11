<template>
    <view @touchmove.prevent.stop>
		<u-popup :show="showPop" type="bottom" @close="disPopUp">
			<view class="privacy-popup-wrap">
				<view class="p-[30rpx]">
					<view class="privacy-title">用户隐私保护提示</view>
					<view class="privacy-desc">感谢您使用本小程序，在使用前您应当阅读并同意<text class="privacy-link" @tap="openPrivacyContract">{{ privacyContractName }}</text>， 当点击同意并继续时，即表示您已理解并同意该条款内容，该条款将对您产生法律约束力；如您不同意，将无法继续使用小程序相关功能。</view>
					<view class="privacy-button-flex">
						<button class="privacy-button-btn bg-disagree" @tap="handleDisagree">拒绝</button>
						<button id="agree-btn" class="privacy-button-btn bg-agree" open-type="agreePrivacyAuthorization" @agreeprivacyauthorization="handleAgree">同意</button>
					</view>
				</view>
			</view>
		</u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import useConfigStore from "@/stores/config";

const showPop = ref(false)
const privacyAuthorization: any = ref(null)

const privacyResolves = new Set()
const closeOtherPagePopUpHooks = new Set()

const privacyContractName = ref('用户隐私保护指引')

const emits = defineEmits(['agree', 'disagree'])

// 监听何时需要提示用户阅读隐私政策
const init = () => {
    if (wx.onNeedPrivacyAuthorization) {
        wx.onNeedPrivacyAuthorization((resolve: any) => {
            if (typeof privacyAuthorization.value === 'function') {
                privacyAuthorization.value(resolve)
            }
        })
    }
}

//初始化监听程序
const curPageShow = () => {
    privacyAuthorization.value = (resolve: any) => {
        privacyResolves.add(resolve)
        //打开弹窗
        popUp()
        // 额外逻辑：当前页面的隐私弹窗弹起的时候，关掉其他页面的隐私弹窗
        closeOtherPagePopUp(disPopUp)
    }
    closeOtherPagePopUpHooks.add(disPopUp)
}

onMounted(() => {
    // 查询微信侧记录的用户是否有待同意的隐私政策信息
    try {
        wx.getPrivacySetting({
            success(res: any) {
                // console.log('隐私政策信息', res);
                // console.log(res.privacyContractName);
                privacyContractName.value = res.privacyContractName
            }
        });
        init();
        curPageShow()
    } catch (e) {
        // console.log("=========低版本基础库==========")
    }
});

// 打开隐私协议
const openPrivacyContract = () => {
    wx.openPrivacyContract({
        success(res) {
            // console.log('打开隐私协议', res);
        },
        fail(err) {
            // console.error('打开隐私协议失败', err)
        }
    });
}

// 额外逻辑：当前页面的隐私弹窗弹起的时候，关掉其他页面的隐私弹窗
const closeOtherPagePopUp = (closePopUp: any) => {
    closeOtherPagePopUpHooks.forEach((hook: any) => {
        if (closePopUp !== hook) {
            hook()
        }
    })
}

// 不同意
const handleDisagree = () => {
    privacyResolves.forEach((resolve: any) => {
        resolve({
            event: 'disagree',
        })
    })
    privacyResolves.clear()
    // 关闭弹窗
    disPopUp()
    // 退出小程序
    uni.showModal({
        content: '未同意隐私协议，无法使用相关功能',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: () => {
            emits('disagree')
        }
    })
}

// 同意并继续
const handleAgree = () => {
    privacyResolves.forEach((resolve: any) => {
        resolve({
            event: 'agree',
            buttonId: 'agree-btn'
        })
    })
    privacyResolves.clear()
    //关闭弹窗
    disPopUp()
    emits('agree')
}

// 打开弹窗
const popUp = () => {
    if (showPop.value === false) {
        showPop.value = true
    }
}

// 关闭弹窗
const disPopUp = () => {
    if (showPop.value === true) {
        showPop.value = false
    }
}

// 主动打开协议弹出框（如果已经同意，则不会弹出）
const proactive = () => {
    if (wx.getPrivacySetting) {
        wx.getPrivacySetting({
            success: (res: any) => {
                if (res.needAuthorization) {
                    popUp()
                    // 额外逻辑：当前页面的隐私弹窗弹起的时候，关掉其他页面的隐私弹窗
                    closeOtherPagePopUp(disPopUp)
                } else {
                    emits('agree')
                }
            },
        });
    } else {
        emits('agree')
    }
}

defineExpose({
    proactive
})
</script>

<style lang="scss" scoped>
.privacy-mask {
    position: fixed;
    z-index: 20000;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.privacy-wrap {
    width: 632rpx;
    padding: 48rpx 30rpx;
    box-sizing: border-box;
    background: #fff;
    border-radius: 16rpx;
}

.privacy-title {
    padding: 10rpx 40rpx;
    font-weight: 700;
    font-size: 30rpx;
    // text-align: center;
}

.privacy-desc {
    font-size: 28rpx;
    color: #555;
    line-height: 1.5;
    text-align: left;
    padding: 15rpx 40rpx;
}

.privacy-link {
    color: #2f80ed;
}

.privacy-button-flex {
    display: flex;
    padding: 20rpx 100rpx;
}

.privacy-button-btn {
    color: #FFF;
    font-size: 30rpx;
    font-weight: 500;
    line-height: 80rpx;
    text-align: center;
    height: 80rpx;
    border-radius: 10rpx;
    border: none;
    background: #07c160;
    flex: 1;
    margin-right: 30rpx;
    justify-content: center;
}

.privacy-button-btn::after {
    border: none;
}

.bg-disagree {
    color: #07c160;
    background: #f2f2f2;
}

.bg-agree {
    margin-right: 0rpx;
}
.privacy-popup-wrap {
	padding-bottom: constant(safe-area-inset-bottom);
	/*兼容 IOS<11.2*/ 
	padding-bottom: env(safe-area-inset-bottom);
}

</style>
