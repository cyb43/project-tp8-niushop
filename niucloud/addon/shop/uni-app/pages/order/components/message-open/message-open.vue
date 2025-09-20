<template>
    <u-popup :show="show" @close="show = false" mode="bottom" :round="10">
        <view @touchmove.prevent.stop class="popup-common flex flex-col">
            <view class="title">买家留言</view>
            <view class="px-[var(--popup-sidebar-m)] py-[var(--pad-top-m)]" style="height: 180px;">
                <u--textarea 
                    class="message-input" 
                    v-model="message" 
                    placeholder="请输入备注信息..."
                    :maxlength="200"
				  :height="180"
					 
                ></u--textarea>
            </view>
            
            <view class="btn-wrap mt-[40rpx]">
                <button class="primary-btn-bg btn" @click="submit">提交</button>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps({
	defaultMessage: {
		type: String,
		default: ''
	}
})

const emits = defineEmits(['submit'])

const show = ref(false)
const message = ref('')

watch(show, (newVal) => {
	if (newVal) {
		message.value = props.defaultMessage
	}
})

// 监听消息长度变化
watch(message, (newVal) => {
	if (newVal && newVal.length > 200) {
		message.value = newVal.substring(0, 200)
	}
})

const open = () => {
	show.value = true
}

const submit = () => {
	emits('submit', message.value)
	show.value = false
}

defineExpose({
	open
})
</script>

<!-- <style lang="scss" scoped>
.message-input {
	width: 100%;
	min-height: 180px;
	padding: 12px;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	font-size: 28rpx;
	color: #333;
	background-color: #fafafa;
	resize: none;
	box-sizing: border-box;
}

.message-input::placeholder {
	color: #999;
	font-size: 28rpx;
}
</style> -->