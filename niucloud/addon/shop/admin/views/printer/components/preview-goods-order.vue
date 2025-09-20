<template>
    <div class="print-preview">

        <!-- 票头信息 -->
        <div class="my-[10px] text-center" v-show="data.head_info.ticket_name.status" :class="[data.head_info.ticket_name.fontSize + '-size',data.head_info.ticket_name.fontWeight + '-weight']">{{ data.head_info.ticket_name.value }}</div>
        <div class="bottom-border" v-show="data.head_info.ticket_name.status"></div>

        <div class="my-[10px] text-center" v-show="data.head_info.shop_name.status" :class="[data.head_info.shop_name.fontSize + '-size',data.head_info.shop_name.fontWeight + '-weight']">商城名称</div>
        <div class="bottom-border" v-show="data.head_info.shop_name.status"></div>

        <!-- 订单信息 -->
        <div v-show="data.order_info.order_item.value.length">
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('order_no') != -1">订单编号：20240713456280064905216</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('order_from') != -1">订单来源：微信小程序</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('order_status') != -1">订单状态：已完成</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('pay_type') != -1">支付方式：微信支付</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('delivery_type') != -1">配送方式：同城配送</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('create_time') != -1">下单时间：2023-12-12 14:20:20</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('pay_time') != -1">付款时间：2023-12-12 14:20:30</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('goods_money') != -1">商品总额：￥10.00</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('delivery_money') != -1">配送金额：￥1.00</div>
            <div class="text-[16px] my-[10px]" v-show="data.order_info.order_item.value.indexOf('discount_money') != -1">优惠金额：￥2.00</div>
        </div>
        <div class="my-[10px]" v-show="data.order_info.order_money.status" :class="[data.order_info.order_money.fontSize + '-size',data.order_info.order_money.fontWeight + '-weight']">实付金额：¥7.00</div>
        <div class="my-[10px]" v-show="data.order_info.shop_remark.status" :class="[data.order_info.shop_remark.fontSize + '-size',data.order_info.shop_remark.fontWeight + '-weight']">商家备注：新用户优惠</div>
        <div class="bottom-border" v-show="data.order_info.order_item.value.length || data.order_info.order_money.status||data.order_info.shop_remark.status"></div>

        <!-- 商品信息 -->
        <div class="flex my-[10px]">
            <div class="text-[16px]" style="flex: 2 1 0;">商品名称</div>
            <div class="text-[16px] flex-1 text-right">数量</div>
            <div class="text-[16px] flex-1 text-right" v-show="data.goods_info.goods_price.status">金额</div>
        </div>
        <div class="flex my-[10px]">
            <div class="text-[16px]" style="flex: 2 1 0;" :class="[data.goods_info.goods_name.fontSize + '-size',data.goods_info.goods_name.fontWeight + '-weight']">可口可乐碳酸饮料</div>
            <div class="text-[16px] flex-1 text-right">x2</div>
            <div class="text-[16px] flex-1 text-right" v-show="data.goods_info.goods_price.status" :class="[data.goods_info.goods_price.fontSize + '-size',data.goods_info.goods_price.fontWeight + '-weight']">¥54.50</div>
        </div>
        <div class="flex my-[10px]">
            <div class="text-[16px]" style="flex: 2 1 0;" :class="[data.goods_info.goods_name.fontSize + '-size',data.goods_info.goods_name.fontWeight + '-weight']">液晶电视</div>
            <div class="text-[16px] flex-1 text-right">x1</div>
            <div class="text-[16px] flex-1 text-right" v-show="data.goods_info.goods_price.status" :class="[data.goods_info.goods_price.fontSize + '-size',data.goods_info.goods_price.fontWeight + '-weight']">¥1999.00</div>
        </div>
        <div class="bottom-border"></div>

        <!-- 买家/收货信息 -->
        <div class="my-[10px]" v-show="data.buyer_info.member_basic_info.value.indexOf('nickname') != -1">
            <div>会员昵称：张三</div>
        </div>
        <div class="my-[10px]" v-show="data.buyer_info.member_basic_info.value.indexOf('account_balance') != -1">账户余额：￥2000.00</div>
        <div class="my-[10px]" v-show="data.buyer_info.member_basic_info.value.indexOf('account_point') != -1">账户积分：300</div>
        <div class="my-[10px]" v-show="data.buyer_info.buyer_mobile.status" :class="[data.buyer_info.buyer_mobile.fontSize + '-size',data.buyer_info.buyer_mobile.fontWeight + '-weight']">会员手机号：{{ data.buyer_info.buyer_mobile.value == 'yes' ? '152****5131' : '15266665131' }}</div>
        <div class="bottom-border" v-show="data.buyer_info.member_basic_info.value || data.buyer_info.buyer_mobile.status"></div>

        <div class="my-[10px]" v-show="data.buyer_info.taker_name.status" :class="[data.buyer_info.taker_name.fontSize + '-size',data.buyer_info.taker_name.fontWeight + '-weight']">收货人：小张</div>
        <div class="my-[10px]" v-show="data.buyer_info.taker_mobile.status" :class="[data.buyer_info.taker_mobile.fontSize + '-size',data.buyer_info.taker_mobile.fontWeight + '-weight']">联系方式：{{ data.buyer_info.taker_mobile.value == 'yes' ? '152****5131' : '15266665131' }}</div>
        <div class="my-[10px]" v-show="data.buyer_info.taker_full_address.status" :class="[data.buyer_info.taker_full_address.fontSize + '-size',data.buyer_info.taker_full_address.fontWeight + '-weight']">收货地址：山西省太原市小店区创业街27号时代广场大厦</div>
        <div class="my-[10px]" v-show="data.buyer_info.member_remark.status" :class="[data.buyer_info.member_remark.fontSize + '-size',data.buyer_info.member_remark.fontWeight + '-weight']">买家备注：微辣，多放孜然，谢谢！</div>
        <div class="bottom-border" v-show="data.buyer_info.taker_name.status || data.buyer_info.taker_mobile.status|| data.buyer_info.taker_full_address.status || data.buyer_info.member_remark.status"></div>

        <!-- 底部信息 -->
        <div class="my-[10px]" v-show="data.bottom_info.qrcode.status">
            <img :src="qrcode" class="w-[150px] h-[150px] mx-auto" />
        </div>
        <div class="my-[10px] text-center text-[16px]" v-show="data.bottom_info.ticket_print_time.status">打印时间：2024年7月13日 17:48:39</div>
        <div class="my-[10px] text-center text-[16px]" v-show="data.bottom_info.bottom_remark.status">{{ data.bottom_info.bottom_remark.value }}</div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import usePosterStore from '@/stores/modules/poster'
import QRCode from 'qrcode'

const posterStore = usePosterStore()

const prop = defineProps({
    value: {
        type: Object,
        default: {}
    }
})

const data = computed(() => {
    return prop.value
})

const qrcode = ref('')

QRCode.toDataURL('小票预览二维码', {
    errorCorrectionLevel: 'L',
    margin: 0,
    width: 200
}).then(url => {
    qrcode.value = url
})

defineExpose({})

</script>

<style lang="scss" scoped>
.print-preview {
    width: 400px;
    padding: 10px;
    border: 1px solid #ededed;
    border-radius: 10px;
    background: #f8f8f9;
    color: #333;
    margin: 0 auto;

    .bottom-border {
        border-bottom: 1px dashed #ccc;
    }

    .normal-size {
        font-size: 16px;
    }

    .big-size {
        font-size: 20px;
    }

    .bold-weight {
        font-weight: 700;
    }
}
</style>
