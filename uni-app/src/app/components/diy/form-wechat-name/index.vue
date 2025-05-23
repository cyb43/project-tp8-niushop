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
		    <input type="nickname" class="layout-one-content" placeholder="获取填表人的微信名"
		           placeholderClass="layout-one-input-placeholder"
		           :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
		           :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
		           v-model="diyComponent.field.value" :disabled="isDisabled" @click="openInfo" />
		   <!-- <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
		        <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-one-attribute-item">{{ item.title }}</view>
		    </view> -->
		</view>
		<view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
		    <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
		    <view class="layout-two-wrap" :class="{'no-border': !diyGlobal.borderControl}">
		        <view class="layout-two-label" :class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}">
		            <text class="required" v-if="diyComponent.field.required">{{ diyComponent.field.required ? '*' : '' }}</text>
		            <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
		        </view>
		        <input type="nickname" class="layout-two-content no-flex"  placeholder="获取填表人的微信名"
		               placeholderClass="layout-two-input-placeholder" @click="openInfo"
		               :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
		               :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
		               v-model="diyComponent.field.value" :disabled="isDisabled" />
		    </view>
		    <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
		    <view v-if="diyComponent.field.remark.text" class="layout-two-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
		   <!-- <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
		        <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
		    </view> -->
		</view>
		<view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
	</view>
	<!-- #ifdef MP-WEIXIN -->
	    <!-- <information-filling ref="infoFill"></information-filling> -->
	<!-- #endif -->
</template>

<script setup lang="ts">
	// 表单 微信名组件
	import { ref, computed, watch,onMounted } from 'vue';
	import useDiyStore from '@/app/stores/diy';
	import { img, isWeixinBrowser } from '@/utils/common'
	import { useLogin } from '@/hooks/useLogin'
	import { t } from '@/locale'
	import useMemberStore from '@/stores/member'
	const memberStore = useMemberStore()
	const login = useLogin()
	const props = defineProps(['component', 'index','global']);
	const diyStore = useDiyStore();
	const infoFill: any = ref(false)
	const errorInfo: any = ref(null);
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
	const openInfo = ()=>{
		// #ifdef H5
		wechatGetInfo();
		// #endif
		
		// #ifdef MP
		weappGetInfo()
		// #endif
	}
	const wechatGetInfo = async () => {
	    // if (isWeixinBrowser()) {
	        // const userInfo = await login.getAuthCode({ scopes: 'snsapi_userinfo' });
	        if (memberStore) {
				console.log(memberStore)
	            diyComponent.value.field.value = memberStore.info.nickname;
	        }
	    // }
	};

	
	
	const weappGetInfo = async () => {
		// #ifdef MP-WEIXIN
		wx.getUserInfo({
		    success: function(res) {
				
			},
		    fail: function(res) {}
		});
		// #endif
	};


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
                    if (newValue && newValue.componentName == 'FormWechatName') {
                        refresh();
                    }
                }
            )
        }else{
		}
    });

    const refresh = ()=> {
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
