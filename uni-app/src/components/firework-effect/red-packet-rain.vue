<template>
  <view class="red-packet-container" v-if="isVisible">
    <canvas
      class="red-packet-canvas"
      canvas-id="redPacketRain"
      id="redPacketRain"
    ></canvas>



    <!-- 红包元素（图片模式或绘制模式） -->
    <view
      v-for="packet in redPackets"
      :key="packet.id"
      class="red-packet-item"
      :style="{
        left: packet.x + 'px',
        top: packet.y + 'px',
        width: packet.width + 'px',
        height: packet.height + 'px',
        transform: `rotate(${packet.rotation}rad)`,
        opacity: packet.opacity
      }"
      @click="handlePacketClick(packet, $event)"
    >
      <!-- 图片模式 -->
      <image
        v-if="props.useImages && packet.image"
        :src="packet.image"
        class="red-packet-image"
        mode="aspectFit"
        @load="onRedPacketImageLoad"
        @error="onRedPacketImageError"
      />
      <!-- 绘制模式 -->
      <view
        v-else
        class="red-packet-drawn"
        :style="{
          width: '100%',
          height: '100%'
        }"
      >
        <view class="red-packet-body"></view>
        <view class="red-packet-text">福</view>
      </view>
    </view>

    <!-- 预加载图片（隐藏） -->
    <image
      v-for="(imagePath, index) in props.images"
      :key="'preload-' + index"
      :src="imagePath"
      class="preload-image"
      @load="onImageLoad"
      @error="onImageError"
    />

    <!-- 金额提示弹窗 -->
    <view
      v-for="tip in amountTips"
      :key="tip.id"
      class="amount-tip"
      :style="{
        left: tip.x + 'px',
        top: tip.y + 'px',
        opacity: tip.opacity
      }"
    >
      <view class="tip-content">
        <text class="amount">+¥{{ tip.amount }}</text>
        <text class="label">{{ tip.label }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, getCurrentInstance, onUnmounted } from 'vue';

// 定义props
const props = defineProps({
  duration: {
    type: Number,
    default: 30000 // 默认30秒
  },
  images: {
    type: Array,
    default: () => [
      // 可以是网络图片或本地图片路径
      // 'https://example.com/hongbao1.png',
      // '/static/images/hongbao.png',
      // 如果为空数组，则使用绘制的红包
    ]
  },
  // 是否使用图片模式
  useImages: {
    type: Boolean,
    default: false // 默认使用绘制模式
  },
  density: {
    type: Number,
    default: 3 // 每次生成的红包数量
  },
  speed: {
    type: Number,
    default: 2 // 下落速度
  },
  // 红包大小配置
  minSize: {
    type: Number,
    default: 60 // 最小红包大小
  },
  maxSize: {
    type: Number,
    default: 100 // 最大红包大小
  },
  // 金额配置
  minAmount: {
    type: Number,
    default: 0.01 // 最小金额
  },
  maxAmount: {
    type: Number,
    default: 10.00 // 最大金额
  },
  // 是否启用点击功能
  clickable: {
    type: Boolean,
    default: true
  }
});

const app = getCurrentInstance();
let ctx = null;
let animationId = null;
let isRunning = false;
let canvasWidth = 375;
let canvasHeight = 667;
let autoStopTimer = null;
let createTimer = null;
let loadedImages = new Map(); // 存储加载的图片
let tipIdCounter = 0; // 提示ID计数器

const isVisible = ref(false);
const amountTips = ref([]); // 金额提示数组
const redPackets = ref([]); // 红包数组（响应式）

// 红包类
class RedPacket {
  constructor(x, y, image) {
    this.x = x;
    this.y = y;
    // 使用props中的大小范围
    this.width = props.minSize + Math.random() * (props.maxSize - props.minSize);
    this.height = this.width * 0.8; // 保持比例
    this.speed = props.speed + Math.random() * 2; // 随机速度
    this.rotation = Math.random() * 0.2 - 0.1; // 轻微旋转
    this.rotationSpeed = (Math.random() - 0.5) * 0.02;
    this.image = image;
    this.opacity = 0.9 + Math.random() * 0.1;
    // 随机金额
    this.amount = (props.minAmount + Math.random() * (props.maxAmount - props.minAmount)).toFixed(2);
    this.clicked = false; // 是否已被点击
    this.id = Date.now() + Math.random(); // 唯一ID
  }
  
  update() {
    this.y += this.speed;
    this.rotation += this.rotationSpeed;
    
    // 轻微的左右摆动
    this.x += Math.sin(this.y * 0.01) * 0.5;
  }
  
  draw() {
    if (!ctx) return;

    ctx.save();
    ctx.globalAlpha = this.opacity;
    ctx.translate(this.x + this.width / 2, this.y + this.height / 2);
    ctx.rotate(this.rotation);

    // 根据模式选择绘制方式
    if (props.useImages && this.image) {
      console.log('🧧 使用图片模式绘制红包:', this.image);
      this.drawImage();
    } else {
      console.log('🧧 使用代码绘制红包');
      this.drawRedPacket();
    }

    ctx.restore();
  }

  // 绘制图片红包
  drawImage() {
    const halfWidth = this.width / 2;
    const halfHeight = this.height / 2;

    try {
      if (this.image && typeof this.image === 'string') {
        console.log('🧧 绘制图片红包:', this.image);

        // 在uni-app中，使用ctx.drawImage绘制图片
        // 参数：图片路径, x, y, width, height
        ctx.drawImage(this.image, -halfWidth, -halfHeight, this.width, this.height);

        console.log('🧧 图片绘制完成');
      } else {
        console.log('🧧 图片无效，使用绘制模式');
        this.drawRedPacket();
      }
    } catch (error) {
      console.warn('🧧 图片绘制失败，降级到绘制模式:', error);
      this.drawRedPacket();
    }
  }

  drawRedPacket() {
    const halfWidth = this.width / 2;
    const halfHeight = this.height / 2;

    // 绘制红包主体（圆角矩形）
    ctx.setFillStyle('#ff4444');
    ctx.fillRect(-halfWidth, -halfHeight, this.width, this.height);

    // 绘制金色装饰边框
    ctx.setStrokeStyle('#ffd700');
    ctx.setLineWidth(2);
    ctx.strokeRect(-halfWidth, -halfHeight, this.width, this.height);

    // 绘制中间的"福"字或装饰
    ctx.setFillStyle('#ffd700');
    try {
      ctx.setFontSize(this.width * 0.4);
      ctx.setTextAlign('center');
      ctx.fillText('福', 0, this.height * 0.1);
    } catch (error) {
      // 如果文本绘制失败，绘制一个圆形装饰
      ctx.beginPath();
      ctx.arc(0, 0, this.width * 0.15, 0, 2 * Math.PI);
      ctx.fill();
    }

    // 绘制顶部的蝴蝶结
    ctx.setFillStyle('#ffaa00');
    ctx.fillRect(-halfWidth * 0.6, -halfHeight, this.width * 0.6, this.height * 0.2);
  }
  
  isOffScreen() {
    return this.y > canvasHeight + this.height;
  }

  // 检测点击
  isClicked(touchX, touchY) {
    if (this.clicked) return false;

    const centerX = this.x + this.width / 2;
    const centerY = this.y + this.height / 2;
    const distance = Math.sqrt(
      Math.pow(touchX - centerX, 2) + Math.pow(touchY - centerY, 2)
    );

    return distance <= Math.max(this.width, this.height) / 2;
  }

  // 标记为已点击
  markAsClicked() {
    this.clicked = true;
    // 点击后变为半透明
    this.opacity = 0.3;
  }
}

// 图片加载成功事件
const onImageLoad = (event) => {
  console.log('🧧 预加载图片成功:', event.target.src);
};

// 图片加载失败事件
const onImageError = (event) => {
  console.error('🧧 预加载图片失败:', event.target.src);
};

// 红包图片加载成功事件
const onRedPacketImageLoad = () => {
  // 图片加载成功，无需处理
};

// 红包图片加载失败事件
const onRedPacketImageError = () => {
  // 图片加载失败，将使用绘制模式
};

// 加载图片（简化版，依赖image标签预加载）
const loadImages = async () => {
  if (!props.useImages || props.images.length === 0) {
    loadedImages.set('default', 'default');
    return Promise.resolve();
  }

  // 直接将图片路径添加到loadedImages中
  // 实际的预加载由template中的image标签完成
  for (const imagePath of props.images) {
    loadedImages.set(imagePath, imagePath);
  }

  return Promise.resolve();
};

// 初始化Canvas
const initCanvas = () => {
  try {
    ctx = uni.createCanvasContext('redPacketRain', app.proxy);

    if (ctx) {
      // 获取屏幕尺寸
      const systemInfo = uni.getSystemInfoSync();
      canvasWidth = systemInfo.windowWidth || 375;
      canvasHeight = systemInfo.windowHeight || 667;

      // 初始化透明背景
      ctx.setFillStyle('rgba(0, 0, 0, 0)');
      ctx.fillRect(0, 0, canvasWidth, canvasHeight);
      ctx.draw();

      return true;
    } else {
      return false;
    }
  } catch (error) {
    return false;
  }
};

// 创建红包
const createRedPackets = () => {
  if (!isRunning) {
    return;
  }

  for (let i = 0; i < props.density; i++) {
    const x = Math.random() * (canvasWidth - 100);
    const y = -100 - Math.random() * 200; // 从屏幕上方开始

    let image = null;

    if (props.useImages && loadedImages.size > 0) {
      // 随机选择一张图片
      const allKeys = Array.from(loadedImages.keys());
      const imageKeys = allKeys.filter(key => key !== 'default');

      if (imageKeys.length > 0) {
        const randomImage = imageKeys[Math.floor(Math.random() * imageKeys.length)];
        image = loadedImages.get(randomImage);
      } else {
        // 如果没有过滤出图片，直接使用第一个
        if (allKeys.length > 0) {
          const firstKey = allKeys[0];
          image = loadedImages.get(firstKey);
        }
      }
    }

    const packet = new RedPacket(x, y, image);
    redPackets.value.push(packet);
  }

};

// 动画循环
const animate = () => {
  if (!isRunning) return;

  try {
    // 更新红包位置（HTML元素会自动重新渲染）
    redPackets.value = redPackets.value.filter(packet => {
      packet.update();
      return !packet.isOffScreen();
    });

    // 继续动画
    if (isRunning) {
      setTimeout(animate, 16); // 约60fps
    }
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
  if (animationId) {
    clearTimeout(animationId);
    animationId = null;
  }
};

// 开始创建红包的定时器
const startCreating = () => {
  if (createTimer) {
    clearInterval(createTimer);
  }
  
  // 立即创建一批红包
  createRedPackets();
  
  // 每隔一段时间创建新红包
  createTimer = setInterval(() => {
    if (isRunning && createTimer) { // 确保在停止创建后不再生成新红包
      createRedPackets();
    }
  }, 1000); // 每秒创建新红包
};

// 停止创建红包
const stopCreating = () => {
  if (createTimer) {
    clearInterval(createTimer);
    createTimer = null;
  }
};

// 启动红包雨
const startRedPacketRain = async (customDuration) => {
  // 显示组件
  isVisible.value = true;

  // 加载图片
  await loadImages();

  // 初始化Canvas（可选，仅用于背景）
  initCanvas();

  // 开始动画和创建红包
  startAnimation();
  startCreating();

  // 设置自动停止定时器
  const duration = customDuration || props.duration;

  if (autoStopTimer) {
    clearTimeout(autoStopTimer);
  }

  autoStopTimer = setTimeout(() => {
    stopRedPacketRain();
  }, duration);
};

// 停止红包雨
const stopRedPacketRain = () => {
  // 停止创建新红包，但继续动画让现有红包落完
  stopCreating();

  // 清除自动停止定时器
  if (autoStopTimer) {
    clearTimeout(autoStopTimer);
    autoStopTimer = null;
  }

  // 等待现有红包全部落下后再停止动画和隐藏组件
  const waitForPacketsToFall = () => {
    if (redPackets.value.length === 0) {
      // 所有红包都落下了，停止动画
      stopAnimation();
      amountTips.value = [];

      // 延迟隐藏组件
      setTimeout(() => {
        isVisible.value = false;
      }, 1000);
    } else {
      // 还有红包在飘落，继续等待
      setTimeout(waitForPacketsToFall, 500);
    }
  };

  waitForPacketsToFall();
};



// 处理红包图片点击
const handlePacketClick = (packet, event) => {
  if (!props.clickable || packet.clicked) return;

  console.log('🧧 点击红包图片，金额:', packet.amount);

  // 标记红包为已点击
  packet.markAsClicked();

  // 获取点击位置
  const touchX = event.currentTarget.offsetLeft + packet.width / 2;
  const touchY = event.currentTarget.offsetTop + packet.height / 2;

  // 显示金额提示
  showAmountTip(touchX, touchY, packet.amount);
};

// 显示金额提示
const showAmountTip = (x, y, amount) => {
  const tipId = ++tipIdCounter;
  const tip = {
    id: tipId,
    x: x - 50, // 居中显示
    y: y - 30,
    amount: amount,
    label: '恭喜获得',
    opacity: 1
  };

  amountTips.value.push(tip);

  // 动画效果：向上移动并淡出
  let animationStep = 0;
  const animate = () => {
    animationStep++;
    const progress = animationStep / 60; // 60帧动画

    if (progress >= 1) {
      // 动画结束，移除提示
      const index = amountTips.value.findIndex(t => t.id === tipId);
      if (index !== -1) {
        amountTips.value.splice(index, 1);
      }
      return;
    }

    // 更新提示位置和透明度
    const tipIndex = amountTips.value.findIndex(t => t.id === tipId);
    if (tipIndex !== -1) {
      amountTips.value[tipIndex].y = y - 30 - progress * 50; // 向上移动50px
      amountTips.value[tipIndex].opacity = 1 - progress; // 淡出
    }

    setTimeout(animate, 16); // 约60fps
  };

  animate();
};

// 组件卸载时清理资源
onUnmounted(() => {
  console.log('🧧 红包雨组件卸载，清理资源');
  stopRedPacketRain();
});

// 暴露方法
defineExpose({
  startRedPacketRain,
  stopRedPacketRain
});
</script>

<style lang="scss" scoped>
.red-packet-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 998; /* 比烟花稍低的层级 */
  pointer-events: none; /* 容器本身不阻挡点击事件 */

  .red-packet-canvas {
    width: 100%;
    height: 100%;
    pointer-events: none; /* Canvas 背景不阻挡点击事件 */
  }



  .test-red-packet {
    border: 2px solid red;
  }

  .red-packet-image {
    position: absolute;
    pointer-events: auto; /* 红包图片可以被点击 */
    z-index: 999;
    transition: opacity 0.3s ease;

    &:active {
      transform: scale(0.95);
    }
  }

  .red-packet-item {
    position: absolute;
    pointer-events: auto; /* 红包元素可以被点击 */
    z-index: 999;
    transition: opacity 0.3s ease;
  }

  .red-packet-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  .red-packet-drawn {
    position: relative;
    background: linear-gradient(135deg, #ff4444, #cc0000);
    border: 2px solid #ffd700;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);

    &::before {
      content: '';
      position: absolute;
      top: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 12px;
      background: linear-gradient(135deg, #ffaa00, #ff8800);
      border-radius: 6px;
      border: 1px solid #ffd700;
    }
  }

  .red-packet-text {
    color: #ffd700;
    font-size: 24px;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
  }

  .preload-image {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
    z-index: -1;
  }

  .amount-tip {
    position: fixed;
    pointer-events: none; /* 金额提示不阻挡点击事件 */
    z-index: 1000; /* 确保金额提示在最上层显示 */
    transition: all 0.3s ease-out;

    .tip-content {
      background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
      text-align: center;
      min-width: 100px;

      .amount {
        display: block;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 2px;
      }

      .label {
        display: block;
        font-size: 12px;
        opacity: 0.9;
      }
    }
  }
}
</style>
