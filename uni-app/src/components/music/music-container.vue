<template>
    <view class="music-container" :class="{ playing: isPlaying }" :style="musicStyle">
      <!-- 增加透明遮罩层，用于后台返回时捕获用户交互 -->
      <view  class="interactive-mask"  v-if="needUserInteraction"  @click="handleInteraction"></view>
      
      <view class="music-control" :style="defaultStyleCss" @click="togglePlay">
            <view class="music-disc " :class="{ rotate: isPlaying }">
                <image v-if="discImage" class="disc-image" :src="discImage" mode="aspectFill"></image>
                <block v-else>
                    <text class="default-style iconfont iconyinfuV6mm" v-if="isPlaying"></text>
                    <text class="default-style disable iconfont iconyinfuV6mm" v-else></text>
                </block>
            </view>
            <view class="music-icon">
                <text v-if="isPlaying" class="iconfont icon-pause"></text>
                <text v-else class="iconfont icon-play"></text>
            </view>
        </view>
    </view>
</template>
  
<script lang="ts" setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { img } from '@/utils/common';
  
  // 组件属性
const props = defineProps({
  // 音乐文件地址
  src: {
    type: String,
    default: ''
  },
  // 是否自动播放
  autoplay: {
    type: Boolean,
    default: true
  },
  // 是否循环播放
  loop: {
    type: Boolean,
    default: true
  },
  // 自定义唱片图片
  discImage: {
    type: String,
    default: ''
  },
  defaultBgColor: {
      type: String,
      default: '#B0B0B0'
  },
  defaultTextColor: {
      type: String,
      default: '#FFFFFF'
  },
  defaultRounded: {
      type: Number,
      default: 20
  }
});
  
// 状态管理
const isPlaying = ref(false); // 实际播放状态
const userWantsPlay = ref(false); // 用户期望状态
const lastPlayPosition = ref(0); // 播放进度
const needUserInteraction = ref(false); // 是否需要用户交互激活
let audioContext: any = null;
let currentPage: any = null;
let backgroundTimer: NodeJS.Timeout | null = null;
  
  // 样式计算
const defaultStyleCss = computed(() => {
    let style = '';
    style += `background:${props.defaultBgColor};`;
    if (props.defaultTextColor) style += `color:${props.defaultTextColor};`;
    if (props.defaultRounded) { 
      style += `border-top-left-radius:${props.defaultRounded * 2}rpx;`;
      style += `border-bottom-left-radius:${props.defaultRounded * 2}rpx;`;
    }
    return style;
});
  
  // 创建音频实例
const createAudioContext = () => {
    audioContext = uni.createInnerAudioContext();
    if (!audioContext) {
        console.error('无法创建音频实例');
        return;
    }
    
    audioContext.src = props.src;
    audioContext.loop = props.loop;
    
    // 监听播放事件
    audioContext.onPlay(() => {
        console.log('音乐开始播放');
        isPlaying.value = true;
        needUserInteraction.value = false; // 播放成功，不需要用户交互
    });
    
    // 监听暂停事件
    audioContext.onPause(() => {
        console.log('音乐暂停播放');
        lastPlayPosition.value = audioContext.currentTime;
        isPlaying.value = false;
    });
    
    // 监听停止事件
    audioContext.onStop(() => {
        console.log('音乐停止播放');
        lastPlayPosition.value = 0;
        isPlaying.value = false;
    });
    
    // 监听结束事件
    audioContext.onEnded(() => {
        console.log('音乐播放结束');
        lastPlayPosition.value = 0;
        isPlaying.value = false;
        userWantsPlay.value = false;
    });
    
    // 监听错误事件（重点处理系统拦截）
    audioContext.onError((res: any) => {
        console.error('音频播放错误:', res.errMsg || res);
        isPlaying.value = false;
        
        // 错误码判断：1007表示需要用户交互（iOS常见）
        if (res.errCode === 1007 || res.errMsg?.includes('user interaction')) {
            needUserInteraction.value = true; // 标记需要用户交互
        }
      
      // 错误重试
        if (userWantsPlay.value) {
            setTimeout(() => playMusic(), 1000);
        }
    });
    
    // 初始化自动播放
    if (props.autoplay && props.src) {
         /* #ifdef H5 */
        console.log('H5环境，不自动播放音乐');
        isPlaying.value = false;
        userWantsPlay.value = false;
        /* #endif */
        
        /* #ifndef H5 */
        console.log('准备自动播放音乐');
        userWantsPlay.value = true;
        setTimeout(() => playMusic(), 300);
        /* #endif */
    }
};
  
  // 播放音乐（增强版）
const playMusic = () => {
    if (!audioContext || !props.src) {
        console.error('无法播放音乐，audioContext或src为空');
        return;
    }
    
    console.log('尝试播放音乐，进度:', lastPlayPosition.value);
    
    // 恢复进度
    if (lastPlayPosition.value > 0) {
        audioContext.seek(lastPlayPosition.value);
    }
    
    // 播放核心逻辑（带权限判断）
    const playPromise = audioContext.play();
    
    // 现代浏览器/小程序支持Promise返回
    if (playPromise && playPromise.catch) {
        playPromise.catch((err: any) => {
            console.warn('播放被系统拦截，需要用户交互:', err);
            needUserInteraction.value = true; // 标记需要用户点击
        });
    }
    
    isPlaying.value = true;
    userWantsPlay.value = true;
  };
  
  // 暂停音乐
const pauseMusic = () => {
    if (!audioContext) return;
    audioContext.pause();
    isPlaying.value = false;
    userWantsPlay.value = false;
    needUserInteraction.value = false;
};
  
  // 切换播放状态
const togglePlay = () => {
    if (isPlaying.value) {
      pauseMusic();
    } else {
      playMusic();
    }
};
  
  // 处理用户交互（用于唤醒被系统拦截的播放）
const handleInteraction = () => {
    console.log('用户交互激活播放');
    needUserInteraction.value = false;
    if (userWantsPlay.value) {
      playMusic();
    }
};
  
  // 页面生命周期监听（增强版）
const initPageLifecycle = () => {
    currentPage = getCurrentPages()[getCurrentPages().length - 1];
    const originalOnShow = currentPage.onShow;
    const originalOnHide = currentPage.onHide;
    const originalOnUnload = currentPage.onUnload;
    
    // 切入后台时处理
    currentPage.onHide = function (...args: any[]) {
      originalOnHide && originalOnHide.apply(this, args);
      console.log('页面切入后台');
      
      // 记录后台停留时间
      backgroundTimer = setTimeout(() => {
        // 超过3秒的后台停留，标记为需要用户交互
        if (userWantsPlay.value) {
          needUserInteraction.value = true;
        }
      }, 3000);
    };
    
    // 返回前台时处理
    currentPage.onShow = function (...args: any[]) {
      originalOnShow && originalOnShow.apply(this, args);
      console.log('页面返回前台');
      
      // 清除后台计时器
      if (backgroundTimer) {
        clearTimeout(backgroundTimer);
        backgroundTimer = null;
      }
      
      // 尝试恢复播放
      if (userWantsPlay.value && audioContext && props.src) {
        // 先尝试自动恢复
        setTimeout(() => {
          playMusic();
        }, 300);
      }
    };
    
    // 页面卸载时清理
    currentPage.onUnload = function (...args: any[]) {
      originalOnUnload && originalOnUnload.apply(this, args);
      if (backgroundTimer) {
        clearTimeout(backgroundTimer);
      }
    };
};
  
  // 监听音乐地址变化
watch(() => props.src, (newSrc) => {
    if (!audioContext) return;
    const wasPlaying = userWantsPlay.value;
    audioContext.src = newSrc;
    lastPlayPosition.value = 0;
    if (wasPlaying && newSrc) {
      playMusic();
    }
});
  
  // 监听自动播放状态变化
watch(() => props.autoplay, (newAutoplay) => {
    if (newAutoplay && audioContext && !isPlaying.value && props.src) {
      userWantsPlay.value = true;
      playMusic();
    }
});
  
  // 组件挂载
onMounted(() => {
    createAudioContext();
    initPageLifecycle();
});
  
  // 组件卸载
onBeforeUnmount(() => {
    // 恢复页面生命周期
    if (currentPage) {
      const originalOnShow = currentPage.onShow;
      const originalOnHide = currentPage.onHide;
      const originalOnUnload = currentPage.onUnload;
      currentPage.onShow = originalOnShow;
      currentPage.onHide = originalOnHide;
      currentPage.onUnload = originalOnUnload;
    }
    
    // 清理音频资源
    if (audioContext) {
      audioContext.stop();
      audioContext.destroy();
      audioContext = null;
    }
    
    if (backgroundTimer) {
      clearTimeout(backgroundTimer);
    }
});

const musicStyle = computed(() => {
  return {
    '--color': `${props.defaultTextColor}`
  }
})
  
  // 暴露方法
defineExpose({
    play: playMusic,
    pause: pauseMusic,
    toggle: togglePlay,
    isPlaying: () => isPlaying.value
});
</script>
  
<style lang="scss" scoped>
.music-container {
  position: fixed;
  right: 0;
  top: calc(120rpx + var(--status-bar-height));
  z-index: 999;  
    // 交互遮罩层：覆盖整个组件，用于捕获用户点击激活播放
    .interactive-mask {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 10; // 确保在按钮上方
      background: transparent; // 透明不影响视觉
    }
    
    .music-control {
      width: 80rpx;
      height: 80rpx;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(0, 0, 0, 0.5);
      border-top-left-radius: 20rpx;
      border-bottom-left-radius: 20rpx;
      z-index: 1; // 低于遮罩层
    }
    
    .music-disc {
      transition: all 0.3s;
      
      &.rotate {
        animation: rotate 5s linear infinite;
        animation-play-state: running;
      }
      
      .disc-image {
        width: 60rpx;
        height: 60rpx;
      }
    }
    
    .music-icon {
      position: absolute;
      width: 80rpx;
      height: 80rpx;
      display: flex;
      align-items: center;
      justify-content: center;
      
      .iconfont {
        font-size: 40rpx;
        color: #ffffff;
        text-shadow: 0 0 5rpx rgba(0, 0, 0, 0.5);
      }
    }
  }
  
  @keyframes rotate {
    from { 
        transform: rotate(0deg); 
    }
    to { 
        transform: rotate(360deg); 
    }
  }
  
  /* 平台适配 */
  /* #ifdef MP-WEIXIN */
  .music-container { 
    z-index: 900; 
}
  /* #endif */
  
  /* #ifdef APP-PLUS */
  .music-container { 
    z-index: 999; 
}
  /* #endif */
  
  /* #ifdef H5 */
  .music-container { 
    position: fixed; 
    z-index: 999; 
}
  /* #endif */
  
  .default-style{
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60rpx;
    height: 60rpx;
    font-size: 34rpx;
    box-sizing: border-box;
    
    &.disable:after{
      content: '';
      position: absolute;
      left: 0;
      height: 50rpx;
      width: 2rpx;
      background: var(--color);
      left: 50%;
      transform: translateX(-50%) rotate(-45deg);
    }
  }
  </style>
      