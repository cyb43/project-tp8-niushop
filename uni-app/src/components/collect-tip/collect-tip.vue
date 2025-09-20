<template>
	<view v-if="visible" class="add-tip" :style="style">
		<view class="bubble">
			<view>
					点击 <span class="nc-iconfont nc-icon-xiaochengxu !text-[33rpx] !text-[#333] "></span>添加到我的小程序<br>微信首页下拉即可快速访问店铺
			</view>
			
			<span class="nc-iconfont nc-icon-guanbiV6xx !text-[24rpx] !text-[#333] absolute top-[6rpx] right-[10rpx]" @click="hide"></span>
			<view class="arrow"></view>
		</view>
	</view>
</template>

<script setup lang="ts">
	import { ref, nextTick } from 'vue'

	const visible = ref(false)
	const style = ref({ top: '0px', right: '0px' })

	const show = () => {
		const closed = uni.getStorageSync('addedTipClosed')
		if (!closed) {
			wx.checkIsAddedToMyMiniProgram({
				success(res) {
					if (!res.added) {
						const menuButton = wx.getMenuButtonBoundingClientRect()
						nextTick(() => {
							const top = menuButton.bottom + 10 
							const right = wx.getSystemInfoSync().windowWidth - menuButton.right + 4 
							// const right = menuButton.left - 10
							style.value = {
								top: `${top}px`,
								right: `${right}px`
							}
							visible.value = true
						})
					}
				}
			})
		}
	}


	const hide = () => {
		visible.value = false
		uni.setStorageSync('addedTipClosed', true)
	}

	defineExpose({
		show
	})
</script>

<style scoped lang="scss">
	.add-tip {
		position: fixed;
		z-index: 999999;
	}

	.bubble {
		position: relative;
		background-color: #fff;
		opacity: 0.9;
		color: #333;
		padding: 20rpx 28rpx;
		border-radius: 20rpx;
		font-size: 24rpx;
		line-height: 1.5;
		width: 330rpx;
		box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.1);
	}

	.arrow {
		position: absolute;
		top: -10rpx;
		right: 28%; 
		width: 0;
		height: 0;
		border-left: 12rpx solid transparent;
		border-right: 12rpx solid transparent;
		border-bottom: 12rpx solid  rgba(255, 255, 255, 0.9);
	}
</style>