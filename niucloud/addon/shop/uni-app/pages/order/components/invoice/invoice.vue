<template>
    <u-popup :show="invoicePop" @close="closeFn" mode="bottom">
        <!-- class="bg-[#fff] rounded-[10rpx] popup-common" @touchmove.prevent.stop -->
        <view class="popup-common">
            <view class="title">请填写发票信息</view>
            <scroll-view :scroll-y="true" class="h-[50vh]">
                <view class="px-[var(--popup-sidebar-m)] pb-[60rpx] pt-0 text-sm ">
                    <u-form labelPosition="left" :model="formData" labelWidth="200rpx"
                            :labelStyle="{'font-size': '28rpx'}" errorType='toast' :rules="rules" ref="formRef">
                        <view class="mt-[10rpx]">
                            <u-form-item label="需要发票" leftIconStyle="text-[28rpx]">
                                <view class="flex">
                                    <view
                                        class="h-[60rpx] box-border rounded px-[30rpx] leading-[56rpx] mr-[20rpx] border-[2rpx] border-[var(--temp-bg)] bg-[var(--temp-bg)] border-solid text-[24rpx]"
                                        :class="{'!bg-[var(--primary-color-light)] !text-[var(--primary-color)] !border-primary': !need}"
                                        @click="need = false">不需要</view>
                                    <view
                                        class="h-[60rpx] box-border rounded px-[30rpx] leading-[56rpx] border-[2rpx] border-[var(--temp-bg)] border-solid text-[24rpx] bg-[var(--temp-bg)]"
                                        :class="{'!bg-[var(--primary-color-light)] !text-[var(--primary-color)] !border-primary': need}"
                                        @click="need = true">需要</view>
                                </view>
                            </u-form-item>
                        </view>
                        <view v-show="need">
                            <view class="mt-[10rpx]">
                                <u-form-item label="抬头类型">
                                    <view class="flex">
                                        <view class="h-[60rpx] box-border rounded px-[30rpx] mr-[20rpx] leading-[56rpx] border-[2rpx] border-[var(--temp-bg)] border-solid text-[24rpx] bg-[var(--temp-bg)]"
                                            :class="{'!bg-[var(--primary-color-light)] !text-[var(--primary-color)] !border-primary': formData.header_type == 1}"
                                            @click="formData.header_type = 1">个人
                                        </view>
                                        <view
                                            class="h-[60rpx] box-border rounded px-[30rpx] leading-[56rpx] border-[2rpx] border-[var(--temp-bg)] border-solid text-[24rpx] bg-[var(--temp-bg)]"
                                            :class="{'!bg-[var(--primary-color-light)] !text-[var(--primary-color)] !border-primary': formData.header_type == 2}"
                                            @click="formData.header_type = 2">企业
                                        </view>
                                    </view>
                                </u-form-item>
                            </view>
                            <view class="mt-[10rpx]">
                                <u-form-item label="发票内容" prop="header_name">
                                    <view class="flex flex-wrap">
                                        <template v-for="(item,index) in config.invoice_content">
                                            <view
                                                class="box-border rounded px-[20rpx] py-[12rpx] leading-[1.4] border-[2rpx] border-[var(--temp-bg)] border-solid text-[24rpx] bg-[var(--temp-bg)] my-[10rpx]"
                                                :class="{'!bg-[var(--primary-color-light)] !text-[var(--primary-color)] !border-primary': formData.name == item, 'mr-[20rpx]': (config.invoice_content.length-1) != index}"
                                                @click="formData.name = item">{{ item }}</view>
                                        </template>
                                    </view>
                                </u-form-item>
                            </view>
                            <view class="mt-[10rpx]">
                                <u-form-item label="发票抬头" prop="header_name">
                                    <u-input fontSize="28rpx" v-model.trim="formData.header_name" border="none" maxlength="50"
                                             placeholder-class="!text-[var(--text-color-light9)] text-[28rpx]" clearable
                                             placeholder="请输入发票抬头" />
                                </u-form-item>
                            </view>
                            <view v-if="formData.header_type == 2">
                                <view class="mt-[10rpx]">
                                    <u-form-item label="纳税人识别号" prop="tax_number">
                                        <u-input fontSize="28rpx" v-model.trim="formData.tax_number" border="none"
                                                 clearable placeholder="请输入纳税人识别号" maxlength="20"
                                                 placeholder-class="!text-[var(--text-color-light9)] text-[28rpx]"
                                                 @change="inputChange" />
                                    </u-form-item>
                                </view>
                                <!-- <view class="py-[20rpx] h-[48rpx] flex items-center">
                                  <text class="text-[30rpx]">更多选填内容</text>
                                  <text class="text-xs text-gray-subtitle ml-[10rpx]">注册地址、电话、开户银行及账号</text>
                                  <view class="text-xs text-right flex-1" @click="optionalShow = !optionalShow">
                                    <text>{{ optionalShow ? '收起' : '展开' }}</text>
                                    <text class="text-[30rpx] nc-iconfont text-gray-subtitle ml-[5rpx]" :class="optionalShow ? 'nc-icon-shangV6xx-1' : 'nc-icon-xiaV6xx'"></text>
                                  </view>
                                </view> -->
                                <view class="mt-[10rpx]">
                                    <u-form-item label="注册地址">
                                        <u-input fontSize="28rpx" v-model="formData.address"
                                                 placeholder-class="!text-[var(--text-color-light9)] text-[28rpx]"
                                                 border="none" clearable maxlength="120"
                                                 placeholder="(选填)请输入企业注册地址" />
                                    </u-form-item>
                                </view>
                                <view class="mt-[10rpx]">
                                    <u-form-item label="注册电话">
                                        <u-input fontSize="28rpx" v-model="formData.telephone"
                                                 placeholder-class="!text-[var(--text-color-light9)] text-[28rpx]"
                                                 border="none" clearable maxlength="12"
                                                 placeholder="(选填)请输入企业注册电话" />
                                    </u-form-item>
                                </view>
                                <view class="mt-[10rpx]">
                                    <u-form-item label="开户银行">
                                        <u-input fontSize="28rpx" v-model="formData.bank_name"
                                                 placeholder-class="!text-[var(--text-color-light9)] text-[28rpx]"
                                                 border="none" clearable maxlength="50"
                                                 placeholder="(选填)请输入企业开户银行" />
                                    </u-form-item>
                                </view>
                                <view class="mt-[10rpx]">
                                    <u-form-item label="银行账号">
                                        <u-input fontSize="28rpx" v-model="formData.bank_card_number"
                                                 placeholder-class="!text-[var(--text-color-light9)] text-[28rpx]"
                                                 border="none" clearable maxlength="25"
                                                 placeholder="(选填)请输入企业开户银行账号" />
                                    </u-form-item>
                                </view>
                            </view>
                        </view>
                    </u-form>
                </view>
            </scroll-view>
            <view class="btn-wrap">
                <button class="primary-btn-bg btn" @click="confirm">确认</button>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import { getInvoiceConfig } from '@/addon/shop/api/config'

const invoicePop = ref(false)
const config = ref({
    is_invoice: 2,
    invoice_content: []
})
const need = ref(false)
const optionalShow = ref(false)
const formData: any = ref({
    header_type: 1,
    header_name: '',
    type: '',
    name: '',
    tax_number: '',
    telephone: '',
    address: '',
    bank_name: '',
    bank_card_number: ''
})

const invoiceOpen = computed(() => {
    return config.value.is_invoice == 1
})

getInvoiceConfig().then(({ data }) => {
    config.value = data
    data.invoice_content.length && (formData.value.name = data.invoice_content[0])
}).catch()

const inputChange = (e: any) => {
    nextTick(() => {
        formData.value.tax_number = e.replace(/[^0-9a-zA-Z]/g, '')
    })
}

const formRef: any = ref(null)

const rules = computed(() => {
    return {
        'header_name': {
            type: 'string',
            required: need.value,
            message: '请输入发票抬头',
            trigger: ['blur', 'change'],
        },
        'tax_number': [{
            type: 'string',
            required: need.value && formData.value.header_type == 2,
            message: '请输入纳税人识别号',
            trigger: ['blur', 'change'],
        }, {
            validator(rule: any, value: any, callback: any) {
                const limit = /^[0-9a-zA-Z]+$/;
                if (!limit.test(value) && formData.header_type == 2) {
                    callback(new Error('请输入正确的纳税人识别号'))
                } else {
                    callback()
                }
            }
        }]
    }
})

const open = () => {
    invoicePop.value = true;
}

const emits = defineEmits(['confirm'])

const confirm = () => {
    formRef.value.validate().then(() => {
        const invoice = need.value ? formData.value : {}
        emits('confirm', invoice)
        invoicePop.value = false;
    })
}
const closeFn = () => {
    invoicePop.value = false;
}

defineExpose({
    open,
    invoiceOpen
})
</script>

<style lang="scss" scoped>
</style>
