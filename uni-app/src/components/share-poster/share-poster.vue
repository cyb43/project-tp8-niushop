<template>
    <!-- 分享弹窗 -->
    <view @touchmove.prevent.stop class="share-popup">
        <u-popup :show="sharePopupShow" @close="sharePopupClose" overlayOpacity="0.8">
            <view @touchmove.prevent.stop>
                <view class="poster-img-wrap" :style="{'top': shareTop}">
                    <image v-if="isPosterAnimation" class="poster-animation" :src="img('addon/shop/poster_animation.gif')" mode="aspectFit"/>
                    <image v-if="isPosterImg" class="poster-img" :src="img(poster)" mode="aspectFit" :show-menu-by-longpress="true"/>
                </view>
                <view class="share-content">
                    <!-- #ifdef MP -->
                    <view class="share-box">
                        <button class="share-btn" :plain="true" open-type="share">
                            <view class="text-[#07c160] iconfont iconweixin11"></view>
                            <text>分享给好友</text>
                        </button>
                    </view>

                    <view class="share-box">
                        <button class="share-btn" :plain="true" @click="savePoster()">
                            <view class="text-[#07c160] iconfont iconpengyouquan"></view>
                            <text>保存海报</text>
                        </button>
                    </view>
                    <!-- #endif -->

                    <!-- #ifdef H5 -->
                    <view class="share-box" @click="copyUrl">
                        <button class="share-btn" :plain="true">
                            <view class="text-[#07c160] iconfont iconfuzhilianjie"></view>
                            <text>复制链接</text>
                        </button>
                    </view>
                    <!-- #endif -->
                    
                    <!-- #ifdef APP-PLUS  -->
                    <view class="share-box">
                        <button class="share-btn" :plain="true" @click="shareSession">
                            <view class="text-[#07c160] iconfont iconweixin11"></view>
                            <text>分享给好友</text>
                        </button>
                    </view>
                    
                    <view class="share-box">
                        <button class="share-btn" :plain="true" @click="shareWechatMoments">
                            <image :src="img('static/resource/images/app/wechat_moments.png')"></image>
                            <text>分享到朋友圈</text>
                        </button>
                    </view>
                    
                    <view class="share-box">
                        <button class="share-btn" :plain="true" @click="savePoster()">
                            <view class="text-[#07c160] iconfont iconpengyouquan"></view>
                            <text>保存海报</text>
                        </button>
                    </view>
                    <!-- #endif -->
                </view>
                <view class="share-footer" @click="sharePopupClose">
                    <text>取消分享</text>
                </view>
            </view>
        </u-popup>
        <u-popup :show="show" mode="center" :round="10" :closeable="true" @close="show = false" :safe-area-inset-bottom="false">
            <view class="dialog-popup">
                <view class="title">提示</view>
                <view class="message">您拒绝了保存图片到相册的授权请求，无法保存图片到相册，如需正常使用，请授权之后再进行操作。</view>
                <view class="action-wrap">
                    <view @click="closeDialog">取消</view>
                    <view>
                        <button type="default" class="authorization-btn" open-type="openSetting" @opensetting="closeDialog" hover-class="none">立即授权</button>
                    </view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { img, copy } from '@/utils/common';
import { getPoster } from '@/app/api/system'
import useSystemStore from "@/stores/system";
import { useShare } from '@/hooks/useShare'

const props = defineProps({
    posterId: {
        type: [String, Number],
        default: 0
    },
    posterType: {
        type: String,
        default: ''
    },
    posterParam: {
        type: Object,
        default: {}
    },
    copyUrl: { // 例 "/wap/addon/shop/pages/goods"
        type: String,
        default: ''
    },
    copyUrlParam: {
        type: String,
        default: ''
    },
    isPreload: {
        type: Boolean,
        default: true
    }
})

const emits = defineEmits(['close'])

const sharePopupShow = ref(false);

// 复制
const copyUrl = () => {
    let data = ''
    if (props.copyUrl) {
        let pathName = location.pathname;
        let packageArr: any = ['/app/', '/addon/'];
        for (let i = 0; i < packageArr.length; i++) {
            if (pathName.indexOf(packageArr[i]) != -1) {
                pathName = pathName.substr(0, pathName.indexOf(packageArr[i]));
            }
        }
        data = location.origin + pathName + props.copyUrl + props.copyUrlParam;
    } else {
        data = location.origin + location.pathname + props.copyUrlParam;
    }
    copy(data, () => {
        sharePopupShow.value = false;
    });
}

const openShare = () => {
    sharePopupShow.value = true
    loadPoster();
}

//生成海报
const isPosterAnimation = ref(false)
const isPosterImg = ref(false)
// 获取分享海报
const poster = ref('');
const loadPoster = () => {
    if (poster.value && props.isPreload) {
        // 预加载
        isPosterAnimation.value = false;
        isPosterImg.value = true;
    } else {
        isPosterAnimation.value = true;
        isPosterImg.value = false;
        let obj = {
            id: props.posterId,
            type: props.posterType,
            param: props.posterParam
        }
        let startTime = Date.parse(new Date());
        getPoster(obj).then((res: any) => {
            poster.value = res.data && img(res.data) || '';

            let endTime = Date.parse(new Date());
            let time = endTime - startTime;
            let periodTime = 2200;
            if (time < periodTime) {
                setTimeout(() => {
                    isPosterAnimation.value = false;
                    isPosterImg.value = true;
                }, (periodTime - time))
            } else {
                isPosterAnimation.value = false;
                isPosterImg.value = true;
            }
        }).catch(() => {
            sharePopupClose();
        })
    }
}
const show = ref(false);

const closeDialog = () => {
    show.value = false;
}

// #ifdef MP || APP-PLUS
//小程序中保存海报
const savePoster = () => {
    let url = img(poster.value);
    uni.downloadFile({
        url: url,
        success: (res) => {
            if (res.errMsg == "downloadFile:ok") {
                uni.saveImageToPhotosAlbum({
                    filePath: res.tempFilePath,
                    success: () => {
                        uni.showToast({
                            title: '保存成功',
                            icon: 'none'
                        });
                    },
                    fail: (err) => {
                        if (err.errno == 104) {
                            let msg = '用户未授权隐私权限，将图像保存到相册失败';
                            uni.showToast({ title: msg, icon: 'none' })
                        } else if (err.errMsg == "saveImageToPhotosAlbum:fail auth deny" ||
                            err.errMsg == "saveImageToPhotosAlbum:fail:auth denied") {
                            show.value = true;
                        } else if (err.errMsg == "saveImageToPhotosAlbum:fail cancel") {
                            let msg = '用户取消将图片保存到相册';
                            uni.showToast({ title: msg, icon: 'none' })
                        } else {
                            uni.showToast({ title: err.errMsg, icon: 'none' })
                        }
                    }
                });
            }
        },
        fail: (err) => {
            uni.showToast({
                title: err.errMsg,
                icon: 'none'
            });
        }
    });
}
// #endif

// #ifdef APP-PLUS
const shareSession = () => {
    useShare().onShareAppMessage();
}

const shareWechatMoments = () => {
    useShare().onShareTimeline();
}
// #endif

const shareTop: any = ref(0)
/************ 获取微信头部-start ****************/
const systemStore = useSystemStore()
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
shareTop.value = systemStore.menuButtonInfo.top + systemStore.menuButtonInfo.height + 'px';
// #endif
/************ 获取微信头部-end ****************/

const sharePopupClose = () => {
    sharePopupShow.value = false;
    isPosterAnimation.value = false;
    isPosterImg.value = false;
    emits('close');
}

defineExpose({
    openShare,
    loadPoster
})
</script>
<style lang="scss" scoped>
.share-popup {
    //  苹果手机下要白色背景
    // :deep(.u-transition), :deep(.u-popup__content) {
    //     background-color: transparent;
    // }

    .share-content {
        border-top-left-radius: 40rpx;
        border-top-right-radius: 40rpx;
        overflow: hidden;
        display: flex;
        display: -webkit-flex;
        -webkit-flex-wrap: wrap;
        -moz-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        -o-flex-wrap: wrap;
        flex-wrap: wrap;
        padding: 40rpx 15rpx;
        background-color: #fff;

        .share-box {
            flex: 1;
            text-align: center;

            .share-btn {
                margin: 0;
                padding: 0;
                border: none;
                line-height: 1;
                height: auto;
                background: none;

                text {
                    margin-top: 20rpx;
                    font-size: 24rpx;
                    display: block;
                    color: #333;
                }
            }
            
            image {
                width: 80rpx;
                height: 80rpx;
            }

            .iconfont {
                font-size: 80rpx;
                line-height: initial;
            }

            .icon-fuzhilianjie,
            .icon-pengyouquan,
            .icon-haowuquan,
            .icon-share-friend {
                color: #07c160;
            }
        }
    }

    .share-footer {
        background-color: #fff;
        height: 90rpx;
        line-height: 90rpx;
        border-top: 2rpx solid #eee;
        text-align: center;
        font-size: 26rpx;
    }

}

.poster-img-wrap {
    position: fixed;
    top: 0;
    left: 0;
    bottom: calc(constant(safe-area-inset-bottom) + 300rpx); //300是海报弹窗下面高度的问题
    bottom: calc(env(safe-area-inset-bottom) + 300rpx);
    right: 0;
    display: flex;
    justify-content: center;
    align-items: center;

    .poster-img {
        width: 80%;
        height: 80%;
    }

    .poster-animation {
        width: 60%;
        height: 65%;
    }
}

.dialog-popup {
    width: 580rpx;
    background: #fff;
    box-sizing: border-box;
    border-radius: 10rpx;
    overflow: hidden;
    height: initial;

    .title {
        padding: 30rpx 30rpx 0 30rpx;
        text-align: center;
        font-size: 32rpx;
        font-weight: bold;
    }

    .message {
        padding: 0 30rpx;
        color: #666;
        text-align: center;
        line-height: 1.3;
        margin-top: 30rpx;
    }

    .action-wrap {
        margin-top: 50rpx;
        height: 80rpx;
        display: flex;
        border-top: 2rpx solid #eee;

        & > view {
            flex: 1;
            text-align: center;
            line-height: 80rpx;

            &:first-child {
                border-right: 2rpx solid #eee;
                color: #999;
            }

            button {
                border: none;
                line-height: 80rpx;
                padding: 0;
                margin: 0;
                width: 100%;
                height: 100%;
            }
        }
    }

    .authorization-btn {
        background-color: #07c160;
        color: #fff;
        font-size: 28rpx;
        border-radius: 0;
    }
}
</style>
