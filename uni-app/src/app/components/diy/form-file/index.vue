<template>
	<view :style="warpCss" class="form-item-frame">
		<view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
			<view class="layout-one-label">
			    <text class="text-overflow-ellipsis" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
			    <text class="required">{{ diyComponent.field.required ? '*' : '' }}</text>
			    <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="is-hidden">{{ t('diyForm.hidden') }}</text>
			</view>
			<view v-if="diyComponent.field.remark.text" class="layout-one-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
			<view class="layout-one-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
			<view class="flex flex-wrap">
			    <view class="relative w-[180rpx] !h-[180rpx] mr-[16rpx] mb-[16rpx] layout-one-content" v-for="(item,index) in diyComponent.field.value" :key="index">
			        <image class="w-[100%] h-[100%]" :src="img(item)" mode="aspectFill" />
			        <view class="absolute top-0 right-[0] bg-[#373737] flex justify-end h-[28rpx] w-[28rpx] rounded-bl-[40rpx]" @click="deleteImage(index)">
			            <text class="nc-iconfont nc-icon-guanbiV6xx !text-[20rpx] mt-[2rpx] mr-[2rpx] text-[#fff]"></text>
			        </view>
			    </view>
			    <view class="flex items-center flex-1">
			        <view class="layout-one-content !p-[0] flex-1 !items-stretch !h-[100rpx]">
			           <view class="flex items-center h-[100%] w-[100%] pl-[30rpx] box-border" @click="selectWeChatFile">
			           		    <text class="nc-iconfont nc-icon-weixinV6mm"></text>
			           		    <text class="text-[28rpx] ml-[10rpx]">选择微信聊天文件</text>
			           	</view>
			        </view>
			        <view class="layout-one-content !p-[0] ml-[20rpx] !items-stretch flex-1 !h-[100rpx]">
			            <u-upload accept="file " @afterRead="afterRead" multiple :maxCount="9">
			                <view class="flex items-center h-[100%] w-[100%] pl-[30rpx] box-border">
			                    <text class="nc-iconfont nc-icon-tupiandaohangpc"></text>
			                    <text class="text-[28rpx] ml-[10rpx]">选择本地文件</text>
			                </view>
			            </u-upload>
			        </view>
			    </view>
			</view>
		</view>
		<view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
	</view>
</template>

<script setup lang="ts">
	// 表单 文件组件
	import { ref, computed, watch,onMounted } from 'vue';
    import { img } from '@/utils/common';
	import useDiyStore from '@/app/stores/diy';
	import { t } from '@/locale'
    import { uploadImage } from '@/app/api/system'
	const errorInfo: any = ref(null);
	const props = defineProps(['component', 'index','global']);
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
	
	const warpCss = computed(() => {
		var style = '';
        style += 'position:relative;';
        if(diyComponent.value.componentStartBgColor) {
            if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${diyComponent.value.componentGradientAngle},${diyComponent.value.componentStartBgColor},${diyComponent.value.componentEndBgColor});`;
            else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
        }

        if(diyComponent.value.componentBgUrl) {
            style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
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
                    if (newValue && newValue.componentName == 'FormFile') {
                        refresh();
                    }
                }
            )
        }
    });

	// const imgListPreview = computed(() => {
	// 	return selectValue.value.map(item => {
	// 		return {url: img(item)}
	// 	})
	// })

	// 选择微信聊天文件
	const selectWeChatFile = () => {
	    uni.chooseMessageFile({
	        count: 9, // 最多选9个
	        type: 'all', // 可选 'image', 'video', 'file', 'all'
	        success: (res) => {
	            console.log('选择的微信聊天文件:', res.tempFiles);
	            res.tempFiles.forEach(file => {
	                uploadImage({
	                    filePath: file.path,
	                    name: 'file'
	                }).then(uploadRes => {
	                    if (diyComponent.value.field.value.length < Number(diyComponent.value.limit)) {
	                        diyComponent.value.field.value.push(uploadRes.data.url);
	                    }
	                }).catch(() => {
	                    console.error('上传失败');
	                });
	            });
	        },
	        fail: (err) => {
	            console.error('选择文件失败', err);
	        }
	    });
	};
	
	// 处理文件上传（本地文件 & 微信文件）
	const afterRead = (event) => {
	    event.file.forEach(item => {
	        uploadImage({
	            filePath: item.url,
	            name: 'file'
	        }).then(res => {
	            if (diyComponent.value.field.value.length < Number(diyComponent.value.limit)) {
	                diyComponent.value.field.value.push(res.data.url);
	            }
	        }).catch(() => {
	            console.error('上传失败');
	        });
	    });
	};


	const deleteImage = (event) => {
	    diyComponent.value.field.value.splice(event.index, 1)
	}

    const refresh = ()=> {
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
