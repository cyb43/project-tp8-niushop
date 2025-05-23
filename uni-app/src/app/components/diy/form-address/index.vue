<template>
	<view v-if="diyComponent.viewFormDetail" class="form-item-frame">
	    <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
	        <view class="detail-one-content">
	            <text class="detail-one-content-label">{{ diyComponent.field.name }}</text>
	            <text class="detail-one-content-value">{{ diyComponent.field.value }}</text>
	        </view>
	    </view>
	    <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
	        <view class="detail-two-content">
	            <view>{{ diyComponent.field.name }}</view>
	            <view class="detail-two-content-value w-[80%]">{{ diyComponent.field.value }}</view>
	        </view>
	    </view>
	</view>
	<view v-else :style="warpCss" class="form-item-frame">
		<view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
		    <view class="layout-one-label">
		        <text class="text-overflow-ellipsis" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
		        <text class="required">{{ diyComponent.field.required ? '*' : '' }}</text>
		        <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="is-hidden">{{ t('diyForm.hidden') }}</text>
		    </view>
		    <view v-if="diyComponent.field.remark.text" class="layout-one-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
		    <view class="layout-one-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
			<view class="flex layout-one-content justify-between items-center">
				<input type="text" class="flex-1" :placeholder="inputPlaceholder"
		           placeholderClass="layout-one-input-placeholder"
		           :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
		           :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
		           v-model="diyComponent.field.value" :disabled="isDisabled" @click="selectArea" />
				   <view class="text-[var(--primary-color)]" v-if="diyComponent.field.value" @click="reset">
				   	清除
				   </view>
			</view>
		    
			<textarea v-if="diyComponent.addressFormat=='province/city/district/address'" type="textarea" class="layout-one-content mt-2 w-full" placeholderClass="layout-one-input-placeholder" :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }" placeholder="详细地址(如小区门牌号)" :disabled="isDisabled"></textarea>	   
		   <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
		        <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-one-attribute-item">{{ item.title }}</view>
		    </view>
		</view>
		<view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
		    <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
		    <view class="layout-two-wrap" :class="{'no-border': !diyGlobal.borderControl}">
		        <view class="layout-two-label" :class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}">
		            <text class="required" v-if="diyComponent.field.required">{{ diyComponent.field.required ? '*' : '' }}</text>
		            <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
		        </view>
		        <input type="text" class="layout-two-content no-flex" :placeholder="inputPlaceholder"
		               placeholderClass="layout-two-input-placeholder" @click="selectArea"
		               :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
		               :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
		               v-model="diyComponent.field.value" :disabled="isDisabled" />
		    </view>
			<textarea v-if="diyComponent.addressFormat=='province/city/district/address'" type="textarea" class="layout-one-content p-2 mt-2 w-full" placeholderClass="layout-one-input-placeholder" :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }" placeholder="详细地址(如小区门牌号)" :disabled="isDisabled"></textarea>
		    <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
		    <view v-if="diyComponent.field.remark.text" class="layout-two-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
		    <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
		        <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
		    </view>
		</view>
		<view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
	</view>
	<area-select ref="areaRef" @complete="areaSelectComplete" :area-id="formData.district_id" />
</template>

<script setup lang="ts">
	// 表单 地址组件
	import { ref, computed, watch,onMounted } from 'vue';
	import { img } from '@/utils/common';
	import useDiyStore from '@/app/stores/diy';
	import { t } from '@/locale'
	const props = defineProps(['component', 'index','global']);
	const diyStore = useDiyStore();
	const areaRef = ref()
	const errorInfo: any = ref(null);
	const isSelectAddress = ref(false)
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
	const formData: any = ref({
	    id: 0,
	    name: '',
	    mobile: '',
	    province_id: 0,
	    city_id: 0,
	    district_id: 0,
	    lat: '',
	    lng: '',
	    address: '',
	    address_name: '',
	    full_address: '',
	    is_default: 0,
	    area: ''
	})
	const inputPlaceholder = computed(() => {
	    let str = '请选择';
	    if(diyComponent.value.addressFormat=="province/city/district/address"){
			str += '省/市/区/街道'
		}else if(diyComponent.value.addressFormat=="province/city/district/street"){
			str += '省/市/区/街道(镇)'
		}else if(diyComponent.value.addressFormat=="province/city/district"){
			str += '省/市/区(县)'
		}else if(diyComponent.value.addressFormat=="province/city"){
			str += '省/市'
		}else{
			str += '省份'
		}
	    return str;
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
	const areaSelectComplete = (event: any) => {
		if (isSelectAddress.value && (formData.value.province_id == event.province.id || formData.value.city_id != event.city.id || formData.value.district_id != event.district.id)) {
			formData.value.lat = '';
			formData.value.lng = '';
		}
		formData.value.province_id = event.province.id || 0
		formData.value.city_id = event.city.id || 0
		formData.value.district_id = event.district.id || 0
		formData.value.area = `${ event.province.name || '' }${ event.city.name || '' }${ event.district.name || '' }`
		diyComponent.value.field.value = formData.value.area
		isSelectAddress.value = false;
	}
    onMounted(() => {
        refresh();
        // 装修模式下刷新
        if (diyStore.mode == 'decorate') {
            watch(
                () => diyComponent.value,
                (newValue, oldValue) => {
                    if (newValue && newValue.componentName == 'FormAddress') {
                        refresh();
                    }
                }
            )
        }else{
		}
    });

    const refresh = ()=> {
    }
	const selectArea = () => {
	    isSelectAddress.value = true
	    areaRef.value.open()
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
	    if (diyComponent.value.field.privacyProtection) {
	        let obj = {
	            title: '已开启隐私保护',
	            type: 'privacy'
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

	// 表单组件验证
	const verify = () => {
	    const res = { code: true, message: '' }
	    if (diyComponent.value.field.required && diyComponent.value.field.value == '' && diyStore.mode != 'decorate') {
	        res.code = false
	        res.message ='111';
	    }
	    errorInfo.value = res;
	    return res;
	}

	// 重置表单组件数据
	const reset = () => {
		// todo 清空组件数据，diyComponent.value.field.value = '';
		diyComponent.value.field.value = '';
	}
	const isDisabled = computed(() => {
	    return diyStore.mode == 'decorate';
	});

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