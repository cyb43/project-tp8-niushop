<template>
	<view v-if="diyComponent.viewFormDetail" class="form-item-frame">
		<view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
			<view class="detail-one-content">
				<text class="detail-one-content-label">{{ diyComponent.field.name }}</text>
				<view class="flex flex-wrap detail-one-content-value pt-[6rpx]">
					<view class="relative w-[180rpx] !h-[180rpx] mr-[16rpx] mb-[16rpx] "
						v-for="(item,index) in diyComponent.field.value" :key="index">
						<image class="w-[100%] h-[100%]" :src="img(item)" @click="handleImg(item,index)"
							mode="aspectFill" />
					</view>
				</view>
			</view>
		</view>
		<view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
			<view class="detail-two-content">
				<text class="detail-two-content-label">{{ diyComponent.field.name }}</text>
				<view class="flex flex-wrap w-[80%] justify-end">
					<view class="relative w-[180rpx] !h-[180rpx] mr-[16rpx] mb-[16rpx] "
						v-for="(item,index) in diyComponent.field.value" :key="index">
						<image class="w-[100%] h-[100%]" :src="img(item)" @click="handleImg(item,index)"
							mode="aspectFill" />
					</view>
				</view>
			</view>
		</view>
	</view>
	<view v-else :style="warpCss" class="form-item-frame">
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
			<view class="flex flex-wrap">
				<view class="relative w-[180rpx] !h-[180rpx] mr-[16rpx] mb-[16rpx] layout-one-content"
					v-for="(item,index) in diyComponent.field.value" :key="index">
					<image class="w-[100%] h-[100%]" :src="img(item)" mode="aspectFill" />
					<view
						class="absolute top-0 right-[0] bg-[#373737] flex justify-end h-[28rpx] w-[28rpx] rounded-bl-[40rpx]"
						@click="deleteImage(index)">
						<text
							class="nc-iconfont nc-icon-guanbiV6xx !text-[20rpx] mt-[2rpx] mr-[2rpx] text-[#fff]"></text>
					</view>
				</view>
			<!-- 	<view class="flex items-center flex-1"
					v-if="diyComponent.uploadMode.length > 1 && diyComponent.field.value.length <= 0">
					<view class="layout-one-content !p-[0] flex-1 !items-stretch !h-[100rpx]">
						<u-upload accept="image" @afterRead="afterRead" multiple :maxCount="9" capture="camera">
							<view class="flex items-center h-[100%] w-[100%] pl-[30rpx] box-border">
								<text class="nc-iconfont nc-icon-xiangjiV6xx"></text>
								<text class="text-[28rpx] ml-[10rpx]">拍照上传</text>
							</view>
						</u-upload>
					</view>
					<view class="layout-one-content !p-[0] ml-[20rpx] !items-stretch flex-1 !h-[100rpx]">
						<u-upload accept="image" @afterRead="afterRead" multiple :maxCount="9" capture="album">
							<view class="flex items-center h-[100%] w-[100%] pl-[30rpx] box-border">
								<text class="nc-iconfont nc-icon-tupiandaohangpc"></text>
								<text class="text-[28rpx] ml-[10rpx]">从相册中选择</text>
							</view>
						</u-upload>
					</view>
				</view> -->
				<view class="layout-one-content h-[180rpx] w-[180rpx] !px-[0]">
					<u-upload accept="image" v-if="diyComponent.uploadMode=='shoot_and_album'" @afterRead="afterRead"
						capture="album"  :maxCount="1">
						<view class="flex flex-col items-center justify-center w-[180rpx] h-[180rpx] ">
							<text class="nc-iconfont !text-[36rpx] nc-icon-jiahaoV6xx mb-[16rpx]"></text>
							<text class="text-[28rpx] ml-[10rpx] text-[24rpx]">添加视频</text>
						</view>
					</u-upload>
					<u-upload accept="image" v-else @afterRead="afterRead" :maxCount="1" :maxDuration="60" capture="camera">
						<view class="flex flex-col items-center justify-center w-[180rpx] h-[180rpx] ">
							<text class="nc-iconfont !text-[36rpx] nc-icon-jiahaoV6xx mb-[16rpx]"></text>
							<text class="text-[28rpx] ml-[10rpx] text-[24rpx]">拍摄上传</text>
						</view>
					</u-upload>
				</view>
			</view>
			<view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
				<view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)"
					class="layout-one-attribute-item">{{ item.title }}</view>
			</view>
		</view>
		<view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
			<text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden"
				class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
			<view class="layout-two-wrap" :class="{'no-border': !diyGlobal.borderControl}">
				<view class="layout-two-label"
					:class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}">
					<text class="required"
						v-if="diyComponent.field.required">{{ diyComponent.field.required ? '*' : '' }}</text>
					<text class="name"
						:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
				</view>
				<view class="layout-two-content flex-wrap">
					<view
						class="relative border-box w-[180rpx] !h-[180rpx] ml-[16rpx] mb-[16rpx] border-box border-[2rpx] border-solid border-[#e6e6e6] rounded-[10rpx] flex items-center"
						v-for="(item,index) in diyComponent.field.value" :key="index">
						<image class="w-[100%] h-[100%]" :src="img(item)" mode="aspectFill" />
						<view
							class="absolute top-0 right-[0] bg-[#373737] flex justify-end h-[28rpx] w-[28rpx] rounded-bl-[40rpx]"
							@click="deleteImage(index)">
							<text
								class="nc-iconfont nc-icon-guanbiV6xx !text-[20rpx] mt-[2rpx] mr-[2rpx] text-[#fff]"></text>
						</view>
					</view>
					<view
						class="items-start border-box border-[2rpx] ml-[16rpx] mb-[16rpx] border-solid border-[#e6e6e6] rounded-[10rpx]">
						<u-upload accept="video" v-if="diyComponent.uploadMode=='shoot_and_album'" @afterRead="afterRead" :maxCount="1"
							capture="album" >
							<view class="flex flex-col items-center justify-center w-[180rpx] h-[180rpx] ">
								<text class="nc-iconfont !text-[36rpx] nc-icon-jiahaoV6xx mb-[16rpx]"></text>
								<text class="text-[28rpx] ml-[10rpx] text-[24rpx]">添加视频</text>
							</view>
						</u-upload>
						<u-upload accept="video" v-else @afterRead="afterRead" :maxCount="1" :maxDuration="60" capture="camera">
							<view class="flex flex-col items-center justify-center w-[180rpx] h-[180rpx] ">
								<text class="nc-iconfont !text-[36rpx] nc-icon-jiahaoV6xx mb-[16rpx]"></text>
								<text class="text-[28rpx] ml-[10rpx] text-[24rpx]">拍摄上传</text>
							</view>
						</u-upload>
					</view>
				</view>
			</view>
			<view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
			<view v-if="diyComponent.field.remark.text" class="layout-two-remark"
				:style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">
				{{ diyComponent.field.remark.text }}</view>
			<view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
				<view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)"
					class="layout-two-attribute-item">{{ item.title }}</view>
			</view>
		</view>
		<view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
		<!-- <view class="relative">
			<view class="p-[10rpx] flex items-center ">
				<view class="w-[25%] flex items-center">
					<text class="text-overflow-ellipsis"
						:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
					<text class="text-[#ec0003]">{{ diyComponent.field.required ? '*' : '' }}</text>
				</view>
				<view class="w-[75%] flex justify-center items-center">
					<u-upload :fileList="imgListPreview" :disabled="isDisabled" @afterRead="afterRead"
						@delete="deletePic" multiple :maxCount="diyComponent.limit" />
				</view>
			</view>

		</view> -->
	</view>
</template>

<script setup lang="ts">
	// 表单 视频组件
	import { ref, computed, watch, onMounted } from 'vue';
	import useDiyStore from '@/app/stores/diy';
	import { img } from '@/utils/common';
	import { uploadVideo } from '@/app/api/system'
	import { t } from '@/locale'
	const props = defineProps(['component', 'index', 'global']);
	const diyStore = useDiyStore();
	const selectValue = ref([])
	const errorInfo: any = ref(null);
	const diyComponent = computed(() => {
		if (diyStore.mode == 'decorate') {
			return diyStore.value[props.index];
		} else {
			return props.component;
		}
	})
	// 上传方式
	const uploadType = computed(() => {
	    let type = '';
	    if (diyComponent.value && diyComponent.value.uploadMode) {
			if(diyComponent.value.uploadMode=='shoot_and_album'){
				type = 'album'
			}else if(diyComponent.value.uploadMode=='shoot_only'){
				type = 'camera'
			}
	    }
	    return type;
	});
	const diyGlobal = computed(() => {
		return props.global;
	})
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
					if (newValue && newValue.componentName == 'FormVideo') {
						refresh();
					}
				}
			)
		}
	});


	const isDisabled = computed(() => {
		return diyStore.mode === 'decorate';
	});

	const imgListPreview = computed(() => {
		return selectValue.value.map(item => {
			return { url: img(item) }
		})
	})
	const afterRead = (event) => {
	    // uni.setStorageSync('sku_form_refresh', true);
	    event.file.forEach(item => {
	        upload(item);
	    })
	}
	
	const upload = (data: any) => {
	    // if (diyComponent.value.field.value.length > Number(diyComponent.value.limit)) {
	    //     uni.showToast({ title: `最多允许上传${ diyComponent.value.limit }张图片`, icon: 'none' })
	    //     return false
	    // }
	
	    uploadVideo({
	        filePath: data.url,
	        name: 'file'
	    }).then(res => {
	        // if (diyComponent.value.field.value.length < Number(diyComponent.value.limit)) {
	            diyComponent.value.field.value.push(res.data.url)
	        // }
	    }).catch(() => {
	    })
	}
	
	const deleteImage = (event) => {
	    diyComponent.value.field.value.splice(event.index, 1)
	}
	// input属性
	const inputAttribute = () => {
	    let arr = [];
	    if (diyComponent.value.autofill) {
	        let obj = {
	            title: '已自动填充'
	        };
	        arr.push(obj);
	    }
	
	    arr.forEach((item, index, arr) => {
	        if (index != (arr.length - 1)) {
	            let obj = {
	                title: '|'
	            };
	            arr.push(obj);
	        }
	    })
	    return arr;
	}

	const refresh = () => {
	}

	// 表单组件验证
	const verify = () => {
	    const res = { code: true, message: '' }
	    // todo 验证组件，diyComponent.value.field.value
	    if (diyComponent.value.field.required && (!diyComponent.value.field.value || diyComponent.value.field.value && !diyComponent.value.field.value.length)) {
	        res.code = false
	        res.message = `请上传视频`;
	    }
	    errorInfo.value = res;
	    return res;
	}

	// 重置表单组件数据
	const reset = () => {
		// todo 清空组件数据，
		 diyComponent.value.field.value = [];
	}

	defineExpose({
		verify,
		reset
	})
</script>

<style lang="scss" scoped>
	@import '@/styles/diy_form.scss';
</style>