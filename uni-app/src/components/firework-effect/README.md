# 🎆 简单烟花特效组件

一个轻量级的uni-app烟花特效组件，专注于提供最佳的用户体验。

## 📁 组件结构

```
firework-effect/
├── firework-effect.vue          # 主要的烟花特效组件
├── simple-firework.vue          # 简单烟花特效组件
└── README.md                    # 说明文档
```

## ✨ 特效特点

### 🎇 简单烟花 (simple-firework)
- 从底部发射，空中爆炸成彩色粒子
- 自动发射，可定义持续时间
- **不阻挡用户操作** - 设置了 `pointer-events: none`
- **自动停止** - 默认30秒，可自定义
- **无黑色阴影** - 使用透明背景
- **兼容性最好** - 使用传统Canvas API

### 🧧 红包雨 (red-packet-rain)
- **新增功能** ⭐
- 从屏幕顶部飘落的红包雨效果
- **点击获得金额** - 点击红包显示随机金额提示
- **不阻挡用户操作** - 设置了 `pointer-events: none`，不会挡住底部按钮
- **自定义时间** - 可设置持续时间
- **自定义密度** - 可调节红包数量
- **自定义速度** - 可调节下落速度
- **自定义大小** - 可设置红包大小范围
- **自定义金额** - 可设置金额范围
- **精美动画** - 金额提示有向上飘动的动画效果
- **自动绘制红包** - 无需图片资源，自动绘制精美红包

## 🎯 使用方法

### 1. 基本使用

```vue
<template>
  <view>
    <!-- 你的页面内容 -->
    <!-- 基础配置 -->
    <firework-effect
      ref="fireworkRef"
      :duration="20000"
      :no-trail="true"
      :red-packet-density="5"
      :red-packet-speed="3"
      :red-packet-min-size="80"
      :red-packet-max-size="120"
      :red-packet-min-amount="0.1"
      :red-packet-max-amount="50.0"
      :red-packet-clickable="true"
    ></firework-effect>
  </view>
</template>

<script setup>
import { ref } from 'vue';
import FireworkEffect from '@/components/firework-effect/firework-effect.vue';

const fireworkRef = ref(null);

// 显示简单烟花特效
const showFireworks = () => {
  fireworkRef.value?.handleShowEffect({
    type: 'simple-firework',
    duration: 15000 // 15秒后自动停止，可选参数
  });
};

// 显示红包雨特效（新功能）
const showRedPacketRain = () => {
  fireworkRef.value?.handleShowEffect({
    type: 'red-packet-rain',
    duration: 10000 // 10秒后自动停止
  });
};

// 使用默认30秒
const showDefaultFireworks = () => {
  fireworkRef.value?.handleShowEffect({ type: 'simple-firework' });
};
</script>
```

### 2. API 说明

#### FireworkEffect 组件

**Props:**
- `duration`: 特效持续时间（毫秒），默认30000（30秒）
- `noTrail`: 是否禁用拖尾效果（避免黑色阴影），默认true
- `redPacketImages`: 红包图片数组，如：`['/static/images/hongbao1.png']`
- `redPacketUseImages`: 是否使用图片模式，默认false（使用绘制模式）
- `redPacketDensity`: 红包密度（每次生成数量），默认3
- `redPacketSpeed`: 红包下落速度，默认2
- `redPacketMinSize`: 红包最小大小（px），默认60
- `redPacketMaxSize`: 红包最大大小（px），默认100
- `redPacketMinAmount`: 红包最小金额，默认0.01
- `redPacketMaxAmount`: 红包最大金额，默认10.00
- `redPacketClickable`: 是否可点击红包，默认true

**方法:**
- `handleShowEffect(params)`: 显示特效
  - `params.type`: 特效类型
    - `'simple-firework'`: 简单烟花特效
    - `'red-packet-rain'` 或 `'hongbao'`: 红包雨特效
  - `params.duration`: 自定义持续时间（可选）
### 🚫 解决黑色阴影问题

如果您看到黑色阴影，请确保：

1. **设置 `no-trail="true"`**（默认已启用）
2. **避免使用拖尾效果**，这会产生半透明覆盖

```vue
<!-- ✅ 推荐：无阴影版本 -->
<firework-effect :no-trail="true"></firework-effect>

<!-- ❌ 不推荐：可能有阴影 -->
<firework-effect :no-trail="false"></firework-effect>
```

### 📋 注意事项

1. 确保页面有足够的渲染性能
2. 在低端设备上可能需要减少粒子数量
3. 长时间运行建议定期清理资源
4. 某些小程序平台可能对 Canvas API 有限制
5. **只支持 `simple-firework` 类型**，不会阻挡用户操作

### 🎁 红包雨详细配置

#### 方式一：使用自定义图片

```vue
<template>
  <!-- 使用自定义红包图片 -->
  <firework-effect
    ref="fireworkRef"
    :red-packet-images="[
      '/static/images/hongbao1.png',
      '/static/images/hongbao2.png',
      '/static/images/hongbao3.png'
    ]"
    :red-packet-use-images="true"    <!-- 启用图片模式 -->
    :red-packet-min-size="100"       <!-- 红包最小100px -->
    :red-packet-max-size="150"       <!-- 红包最大150px -->
    :red-packet-min-amount="1.0"     <!-- 最小1元 -->
    :red-packet-max-amount="100.0"   <!-- 最大100元 -->
    :red-packet-density="6"          <!-- 每次生成6个红包 -->
    :red-packet-speed="1.5"          <!-- 较慢的下落速度 -->
    :red-packet-clickable="true"     <!-- 启用点击功能 -->
  ></firework-effect>
</template>
```

#### 方式二：使用代码绘制（默认）

```vue
<template>
  <!-- 使用代码绘制的红包 -->
  <firework-effect
    ref="fireworkRef"
    :red-packet-use-images="false"   <!-- 使用绘制模式（默认） -->
    :red-packet-min-size="80"        <!-- 红包最小80px -->
    :red-packet-max-size="120"       <!-- 红包最大120px -->
    :red-packet-min-amount="0.1"     <!-- 最小0.1元 -->
    :red-packet-max-amount="50.0"    <!-- 最大50元 -->
    :red-packet-density="4"          <!-- 每次生成4个红包 -->
    :red-packet-speed="2.5"          <!-- 下落速度 -->
    :red-packet-clickable="true"     <!-- 启用点击功能 -->
  ></firework-effect>
</template>
```

#### 方式三：使用网络图片

```vue
<template>
  <!-- 使用网络红包图片 -->
  <firework-effect
    ref="fireworkRef"
    :red-packet-images="[
      'https://example.com/hongbao1.png',
      'https://example.com/hongbao2.png'
    ]"
    :red-packet-use-images="true"
    :red-packet-min-size="100"
    :red-packet-max-size="150"
  ></firework-effect>
</template>
```

### 🖼️ 红包图片要求

#### 图片格式
- **PNG**（推荐，支持透明背景）
- **JPG/JPEG**
- **WebP**

#### 尺寸建议
- **推荐尺寸**: 200x160px（宽高比5:4）
- **最小尺寸**: 100x80px
- **最大尺寸**: 400x320px

#### 文件大小
- **建议**: 小于100KB
- **最大**: 不超过500KB

#### 图片准备步骤
1. 将红包图片放在 `uni-app/public/static/images/` 目录
2. 文件命名建议：`hongbao1.png`, `hongbao2.png` 等
3. 确保图片背景透明（PNG格式）
4. 图片内容应该是完整的红包设计

### 📏 红包大小控制

```vue
<!-- 小红包 -->
:red-packet-min-size="60"
:red-packet-max-size="80"

<!-- 中等红包 -->
:red-packet-min-size="80"
:red-packet-max-size="120"

<!-- 大红包 -->
:red-packet-min-size="120"
:red-packet-max-size="180"

<!-- 超大红包 -->
:red-packet-min-size="150"
:red-packet-max-size="200"
```

### 🎯 红包点击功能

- **点击检测**: 自动检测用户点击的红包
- **金额显示**: 显示随机金额（在设定范围内）
- **动画效果**: 金额提示向上飘动并淡出
- **防重复**: 已点击的红包变为半透明，不可重复点击
- **响应式**: 支持触摸和鼠标点击

### 🔧 兼容性

- 支持 Vue 2 和 Vue 3
- 支持 uni-app 各平台（H5、小程序、App）
- 使用传统 Canvas API，兼容性最佳
