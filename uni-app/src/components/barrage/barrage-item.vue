@ -1,108 +0,0 @@
<template>
  <view :style="itemStyle">
    <view class="danmu-item" :style="{ backgroundColor: bgColor, borderRadius: rounded, color: textColor }">
		  <image class="w-[40rpx] h-[40rpx] rounded-[100rpx]" :src="data.headimg?img(data.headimg):img('static/resource/images/default_headimg.png')" :mode="'aspectFill'"
      />
      <text class="username max-w-[160rpx] truncate">{{ data.nickname }}</text>
      <text class="time whitespace-nowrap">{{ data.time }}下单</text>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue'
import { img } from '@/utils/common';
const props = defineProps({
  bgColor: {
    type: String,
    default: '#B0B0B0'
  },
  opacity: {
      type: String,
      default: '1'
  },
  textColor: {
    type: String,
    default: '#FFFFFF'
  },
  rounded: {
    type: Number,
    default: 20
  },
  data: {
    type: Object,
    required: true
  },
  delay: {
    type: Number,
    default: 0
  },
  speed: {
    type: Number,
    default: 8
  }
})

const itemStyle = computed(() => {
  return {
    '--speed': `${props.speed}s`,
    '--delay': `${props.delay}s`,
    '--height': `-300rpx`
  }
})
</script>

<style scoped>
.danmu-item {
  position: absolute;
  left: 30rpx;
  bottom: 0;
  min-width: 180rpx;
  white-space: nowrap;
  padding: 10rpx;
  padding-right: 20rpx;
  background-color: rgba(255, 255, 255, 0.9);
  border-radius: 40rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.1);
  font-size: 28rpx;
  color: #333;
  transform: translateY(0);
  opacity: 0;
  animation: moveUp linear var(--speed) forwards;
  animation-delay: var(--delay);
  display: flex;
  align-items: center;
  margin-bottom: 0;
}

.username {
  margin-left: 8rpx;
  font-size: 24rpx;
}

.time {
  font-size: 24rpx;
}

@keyframes moveUp {
  0% {
    transform: translateY(0);
    opacity: 0;
  }

  10% {
    opacity: 1;
  }

  90% {
    opacity: 1;
  }

  100% {
    transform: translateY(var(--height));
    opacity: 0;
  }
}
</style>