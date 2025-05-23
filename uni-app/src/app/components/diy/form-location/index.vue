<template>
	<view :style="warpCss" class="form-item-frame">
		<view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
			<view class="layout-one-label">
				<text class="text-overflow-ellipsis"
					:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
				<text class="required">{{ diyComponent.field.required ? '*' : '' }}</text>
				<text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden"
					class="is-hidden">{{ t('diyForm.hidden') }}</text>
			</view>
			<view v-if="diyComponent.field.remark.text" class="layout-one-remark"
				:style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">
				{{ diyComponent.field.remark.text }}</view>
			<view class="layout-one-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
			<view v-if="diyComponent.mode== 'authorized_wechat_location'">
				<view @click.stop="locationVal.reposition()" v-if="systemStore.diyAddressInfo" class="layout-one-content">
					<!-- <view class="flex items-baseline font-500">
						<text class="text-[24rpx] mr-[2rpx]">{{ systemStore.diyAddressInfo.city }}</text>
						<text class="iconfont iconxiaV6xx !text-[24rpx]"></text>
					</view> -->
					<text class="iconfont iconzuobiaofill !text-[28rpx]"></text>
					<view
						v-if="systemStore.diyAddressInfo.community">{{ systemStore.diyAddressInfo.community }}</view>
				</view>
				<view @click.stop="locationVal.reposition()" class="layout-one-content"
					v-else>
					<text class="iconfont iconzuobiaofill !text-[28rpx]"></text>
					<text class="ml-1 text-[#999]">点击获取位置信息</text>	
				</view>
			</view>
			<view v-else>
				<view @click.stop="locationVal.reposition()" v-if="systemStore.diyAddressInfo" class="layout-one-content">
					<view class="flex items-baseline font-500">
						<text class="text-[24rpx] mr-[2rpx]">{{ systemStore.diyAddressInfo.city }}</text>
						<text class="iconfont iconxiaV6xx !text-[24rpx]"></text>
					</view>
					<view class="layout-one-content"
						v-if="systemStore.diyAddressInfo.community">{{ systemStore.diyAddressInfo.community }}</view>
				</view>
				<view @click.stop="locationVal.reposition()" class="layout-one-content"
					v-else>
					<text class="iconfont iconzuobiaofill !text-[28rpx]"></text>
					<text class="ml-1 text-[#999]">选择位置</text>	
				</view>
				<view class="text-[var(--primary-color)] mt-1">
					当前定位
				</view>
			</view>
			
			
			<!-- <input type="text" class="layout-one-content" :placeholder=""
				placeholderClass="layout-one-input-placeholder"
				:placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
				:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
				v-model="diyComponent.field.value" :disabled="isDisabled" /> -->
			<!-- <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
				<view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)"
					class="layout-one-attribute-item">{{ item.title }}</view>
			</view> -->
		</view>
		<!-- <view class="relative">
			<view class="p-[10rpx]  flex items-center ">
				<view class="w-[27%] mr-[10rpx] flex items-center">
					<text class="text-overflow-ellipsis"
						:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name}}</text>
					<text class="text-[#ec0003]">{{ diyComponent.field.required ? '*' : '' }}</text>
				</view>
				
			</view>
		</view> -->
	</view>
</template>

<script setup lang="ts">
	// 表单 定位组件
	import { ref, computed, watch, onMounted } from 'vue';
	import { img } from '@/utils/common';
	import useDiyStore from '@/app/stores/diy';
	import { useLocation } from '@/hooks/useLocation'
	import useSystemStore from '@/stores/system';
	const errorInfo: any = ref(null);
	const systemStore = useSystemStore();
	const systemInfo = uni.getSystemInfoSync();
	const props = defineProps(['component', 'index', 'global']);
	const diyStore = useDiyStore();

	const diyComponent = computed(() => {
		if (diyStore.mode == 'decorate') {
			return diyStore.value[props.index];
		} else {
			return props.component;
		}
	})
	const diyGlobal = computed(() => {
	    return props.global;
	})
	

	/************** 定位-start ****************/
	let isOpenLocation = false;
	if (diyComponent.value && diyStore.mode != 'decorate') {
		isOpenLocation = true;
	}

	const locationVal = useLocation(isOpenLocation);
	locationVal.onLoad();
	locationVal.init();
	/************** 定位-end ****************/

	const warpCss = computed(() => {
		var style = '';
		style += 'position:relative;';
		if (diyComponent.value.componentStartBgColor) {
			if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${diyComponent.value.componentGradientAngle},${diyComponent.value.componentStartBgColor},${diyComponent.value.componentEndBgColor});`;
			else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
		}

		if (diyComponent.value.componentBgUrl) {
			style += `background-image:url('${img(diyComponent.value.componentBgUrl)}');`;
			style += 'background-size: cover;background-repeat: no-repeat;';
		}

		if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
		if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
		if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
		if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
		return style;
	})

	onMounted(() => {
		refresh();
		// 装修模式下刷新
		if (diyStore.mode == 'decorate') {
			watch(
				() => diyComponent.value,
				(newValue, oldValue) => {
					if (newValue && newValue.componentName == 'FormLocation') {
						refresh();
					}
				}
			)
		} else {
		}
	});

	const refresh = () => {
	}

	// 表单组件验证
	const verify = () => {
		const res = { code: true, message: '' }
		// todo 验证组件，diyComponent.value.field.value
		return res;
	}

	// 重置表单组件数据
	const reset = () => {
		// todo 清空组件数据，diyComponent.value.field.value = '';
	}

	defineExpose({
		verify,
		reset
	})
</script>

<style lang="scss" scoped>
	@import '@/styles/diy_form.scss';

	.text-overflow-ellipsis {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
</style>