<template>
	<!-- 简单烟花特效 -->
	<simple-firework ref="simpleFireworkRef" :duration="fireworkDuration" :no-trail="props.noTrail"></simple-firework>
	<!-- 红包雨特效 -->
	<red-packet-rain
		ref="redPacketRainRef"
		:duration="fireworkDuration"
		:images="redPacketImages"
		:density="redPacketDensity"
		:speed="redPacketSpeed"
		:min-size="redPacketMinSize"
		:max-size="redPacketMaxSize"
		:min-amount="redPacketMinAmount"
		:max-amount="redPacketMaxAmount"
		:clickable="redPacketClickable"
		:use-images="redPacketUseImages"
	></red-packet-rain>
</template>
<script setup>
	// 导入特效组件
	import SimpleFirework from './simple-firework.vue'
	import RedPacketRain from './red-packet-rain.vue'
	// #ifdef VUE3
	import {
		ref,
		computed
	} from 'vue';
	// #endif
	// #ifndef VUE3
	import {
		ref,
		computed
	} from '@vue/composition-api';
	// #endif

	// 定义props
	const props = defineProps({
		duration: {
			type: Number,
			default: 30000 // 默认30秒
		},
		noTrail: {
			type: Boolean,
			default: true // 默认无拖尾，避免黑色阴影
		},
		// 红包雨相关配置
		redPacketImages: {
			type: Array,
			default: () => ['/static/images/hongbao.png']
		},
		redPacketDensity: {
			type: Number,
			default: 3 // 每次生成的红包数量
		},
		redPacketSpeed: {
			type: Number,
			default: 2 // 红包下落速度
		},
		// 红包大小配置
		redPacketMinSize: {
			type: Number,
			default: 60 // 红包最小大小
		},
		redPacketMaxSize: {
			type: Number,
			default: 100 // 红包最大大小
		},
		// 红包金额配置
		redPacketMinAmount: {
			type: Number,
			default: 0.01 // 最小金额
		},
		redPacketMaxAmount: {
			type: Number,
			default: 10.00 // 最大金额
		},
		// 是否可点击
		redPacketClickable: {
			type: Boolean,
			default: true // 默认可点击
		},
		// 是否使用图片模式
		redPacketUseImages: {
			type: Boolean,
			default: false // 默认使用绘制模式
		}
	});

	// 特效组件引用
	const simpleFireworkRef = ref(null)
	const redPacketRainRef = ref(null)

	// 计算烟花持续时间
	const fireworkDuration = computed(() => props.duration)

	const handleShowEffect = (params) => {
		const {
			type,
			duration
		} = params;

		// 支持的特效类型
		if (type == 'simple-firework') {
			handleShowSimpleFirework(duration)
		} else if (type == 'red-packet-rain' || type == 'hongbao') {
			handleShowRedPacketRain(duration)
		} else {
			console.warn('🎆 支持的特效类型: simple-firework, red-packet-rain，当前类型:', type)
		}
	}

	// 显示简单烟花特效
	const handleShowSimpleFirework = (customDuration) => {
		if (simpleFireworkRef.value) {
			simpleFireworkRef.value.startFireworks(customDuration);
		}
	}

	// 显示红包雨特效
	const handleShowRedPacketRain = (customDuration) => {
		if (redPacketRainRef.value) {
			redPacketRainRef.value.startRedPacketRain(customDuration);
		}
	}

	// 停止烟花特效
	const stopFireworks = () => {
		if (simpleFireworkRef.value && typeof simpleFireworkRef.value.stopFireworks === 'function') {
			simpleFireworkRef.value.stopFireworks();
		}
	}

	// 停止红包雨特效
	const stopRedPacketRain = () => {
		if (redPacketRainRef.value && typeof redPacketRainRef.value.stopRedPacketRain === 'function') {
			redPacketRainRef.value.stopRedPacketRain();
		}
	}

	// 停止所有特效
	const stopAllEffects = () => {
		stopFireworks();
		stopRedPacketRain();
	}

	// 暴露方法给父组件
	defineExpose({
		handleShowEffect,
		stopFireworks,
		stopRedPacketRain,
		stopAllEffects
	})


</script>
