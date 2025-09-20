<template>
  <view class="firework-container" v-if="isVisible">
    <canvas
      class="firework-canvas"
      canvas-id="simpleFirework"
      id="simpleFirework"
    ></canvas>
  </view>
</template>

<script setup>
// #ifdef VUE3
import {
  ref,
  onMounted,
  onUnmounted,
  getCurrentInstance
} from 'vue';
// #endif
// #ifndef VUE3
import {
  ref,
  onMounted,
  onUnmounted,
  getCurrentInstance
} from '@vue/composition-api';
// #endif

// 定义props
const props = defineProps({
  duration: {
    type: Number,
    default: 30000 // 默认30秒
  },
  autoStart: {
    type: Boolean,
    default: false
  },
  noTrail: {
    type: Boolean,
    default: true // 默认不显示拖尾，避免黑色阴影
  }
});

const app = getCurrentInstance();
let ctx = null;
let animationId = null;
let fireworks = [];
let isRunning = false;
let canvasWidth = 375;
let canvasHeight = 667;
let autoStopTimer = null;
let autoLaunchTimer = null;

const isVisible = ref(false);

// 获取随机颜色
const getRandomColor = () => {
  const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
  return colors[Math.floor(Math.random() * colors.length)];
};

// 简化的烟花类
class SimpleFirework {
  constructor(startX, startY, targetX, targetY) {
    this.x = startX;
    this.y = startY;
    this.targetX = targetX;
    this.targetY = targetY;
    this.color = getRandomColor();
    this.particles = [];
    this.exploded = false;
    this.speed = 5;
    
    // 计算方向
    const dx = targetX - startX;
    const dy = targetY - startY;
    const distance = Math.sqrt(dx * dx + dy * dy);
    this.vx = (dx / distance) * this.speed;
    this.vy = (dy / distance) * this.speed;
  }
  
  update() {
    if (!this.exploded) {
      this.x += this.vx;
      this.y += this.vy;
      
      // 检查是否到达目标
      const distance = Math.sqrt((this.targetX - this.x) ** 2 + (this.targetY - this.y) ** 2);
      if (distance < 20 || this.y <= this.targetY) {
        this.explode();
        this.exploded = true;
      }
    } else {
      // 更新粒子
      this.particles = this.particles.filter(particle => {
        particle.x += particle.vx;
        particle.y += particle.vy;
        particle.vy += 0.1; // 重力
        particle.alpha -= 0.02;
        return particle.alpha > 0;
      });
    }
  }
  
  explode() {
    // 创建粒子
    for (let i = 0; i < 20; i++) {
      const angle = (Math.PI * 2 * i) / 20;
      const speed = Math.random() * 4 + 2;
      this.particles.push({
        x: this.x,
        y: this.y,
        vx: Math.cos(angle) * speed,
        vy: Math.sin(angle) * speed,
        alpha: 1,
        color: this.color
      });
    }
  }
  
  draw() {
    if (!ctx) return;
    
    try {
      if (!this.exploded) {
        // 绘制发射的烟花
        if (ctx.setFillStyle) {
          ctx.setFillStyle(this.color);
          ctx.beginPath();
          ctx.arc(this.x, this.y, 3, 0, Math.PI * 2);
          ctx.fill();
        }
      } else {
        // 绘制爆炸粒子
        this.particles.forEach(particle => {
          if (ctx.setGlobalAlpha && ctx.setFillStyle) {
            ctx.setGlobalAlpha(particle.alpha);
            ctx.setFillStyle(particle.color);
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, 2, 0, Math.PI * 2);
            ctx.fill();
          }
        });
        if (ctx.setGlobalAlpha) {
          ctx.setGlobalAlpha(1);
        }
      }
    } catch (error) {
      // 绘制错误，忽略
    }
  }
  
  isDead() {
    return this.exploded && this.particles.length === 0;
  }
}

// 初始化Canvas
const initCanvas = () => {
  try {
    // 尝试创建Canvas上下文
    ctx = uni.createCanvasContext('simpleFirework', app.proxy);

    if (ctx) {
      // 获取屏幕尺寸
      const systemInfo = uni.getSystemInfoSync();
      canvasWidth = systemInfo.windowWidth || 375;
      canvasHeight = systemInfo.windowHeight || 667;

      // 初始化透明背景
      ctx.setFillStyle('rgba(255, 255, 255, 0.01)');
      ctx.fillRect(0, 0, canvasWidth, canvasHeight);

      // 绘制一个测试点来验证Canvas是否工作
      ctx.setFillStyle('#ff0000');
      ctx.fillRect(canvasWidth / 2, canvasHeight / 2, 10, 10);

      ctx.draw(); // 立即绘制初始背景

      // 开始动画（延迟更长时间确保Canvas完全准备好）
      setTimeout(() => {
        startAnimation();
      }, 1000);
    } else {
      // 重试机制
      setTimeout(() => {
        initCanvas();
      }, 1000);
    }
  } catch (error) {
    // 重试机制
    setTimeout(() => {
      initCanvas();
    }, 1000);
  }
};

// 动画循环
const animate = () => {
  if (!isRunning || !ctx) {
    return;
  }

  try {
    // 根据props决定清除方式
    if (props.noTrail) {
      // 完全清除，无拖尾效果，无阴影
      ctx.clearRect && ctx.clearRect(0, 0, canvasWidth, canvasHeight);
      // 如果不支持clearRect，使用极淡的白色
      if (!ctx.clearRect) {
        ctx.setFillStyle('rgba(255, 255, 255, 0.02)');
        ctx.fillRect(0, 0, canvasWidth, canvasHeight);
      }
    } else {
      // 半透明清除，产生拖尾效果
      ctx.setFillStyle('rgba(255, 255, 255, 0.08)');
      ctx.fillRect(0, 0, canvasWidth, canvasHeight);
    }

    // 更新和绘制烟花
    fireworks = fireworks.filter(firework => {
      firework.update();
      firework.draw();
      return !firework.isDead();
    });

    // 绘制到画布
    ctx.draw();

    // 继续动画
    setTimeout(animate, 16); // 约60fps
  } catch (error) {
    if (isRunning) {
      setTimeout(animate, 50);
    }
  }
};

// 开始动画
const startAnimation = () => {
  isRunning = true;
  animate();
};

// 停止动画
const stopAnimation = () => {
  isRunning = false;
  fireworks = [];
};

// 创建烟花
const createFirework = (targetX, targetY) => {
  if (!ctx) {
    return;
  }

  const startX = Math.random() * canvasWidth;
  const startY = canvasHeight;
  const finalTargetX = targetX || Math.random() * canvasWidth;
  const finalTargetY = targetY || Math.random() * canvasHeight * 0.5;

  fireworks.push(new SimpleFirework(startX, startY, finalTargetX, finalTargetY));
};

// 自动发射
const startAutoLaunch = () => {
  if (autoLaunchTimer) {
    clearInterval(autoLaunchTimer);
  }

  autoLaunchTimer = setInterval(() => {
    if (isRunning && autoLaunchTimer) { // 确保在停止发射后不再生成新烟花
      createFirework();
    }
  }, 800); // 每0.8秒发射一个烟花
};

const stopAutoLaunch = () => {
  if (autoLaunchTimer) {
    clearInterval(autoLaunchTimer);
    autoLaunchTimer = null;
  }
};

// 移除点击和触摸处理，避免阻挡用户操作

// 启动烟花
const startFireworks = (customDuration) => {
  // 显示组件
  isVisible.value = true;

  if (!isRunning) {
    startAnimation();
  }

  // 立即创建一个测试烟花
  createFirework();

  // 开始自动发射
  startAutoLaunch();

  // 设置自动停止定时器
  const duration = customDuration || props.duration;

  if (autoStopTimer) {
    clearTimeout(autoStopTimer);
  }

  autoStopTimer = setTimeout(() => {
    stopFireworks();
  }, duration);
};

// 停止烟花
const stopFireworks = () => {
  // 停止自动发射新烟花，但继续动画让现有烟花完成
  stopAutoLaunch();

  // 清除自动停止定时器
  if (autoStopTimer) {
    clearTimeout(autoStopTimer);
    autoStopTimer = null;
  }

  // 等待现有烟花全部完成后再停止动画和隐藏组件
  const waitForFireworksToFinish = () => {
    if (fireworks.length === 0) {
      // 所有烟花都完成了，停止动画
      stopAnimation();

      // 延迟隐藏组件
      setTimeout(() => {
        isVisible.value = false;
      }, 1000);
    } else {
      // 还有烟花在进行，继续等待
      setTimeout(waitForFireworksToFinish, 500);
    }
  };

  waitForFireworksToFinish();
};

onMounted(() => {
  setTimeout(() => {
    initCanvas();
  }, 1000);
});

onUnmounted(() => {
  stopFireworks();
});

defineExpose({
  startFireworks,
  stopFireworks,
  createFirework
});
</script>

<style lang="scss" scoped>
.firework-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none; /* 不阻挡用户操作 */
  z-index: 999; /* 降低层级，避免遮挡重要UI */

  .firework-canvas {
    width: 100%;
    height: 100%;
    pointer-events: none; /* Canvas也不接收事件，完全不阻挡操作 */
  }
}
</style>
