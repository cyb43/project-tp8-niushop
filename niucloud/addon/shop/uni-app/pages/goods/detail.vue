<template>
    <view :style="themeColor()">
        <!-- 自定义模板渲染 -->
        <view class="diy-template-wrap bg-index" :style="diy.pageStyle()">
            <diy-group ref="diyGroupRef" :data="diy.data" />
        </view>
        <ns-goods-sku ref="goodsSkuRef" v-if="Object.keys(useGoodsDetailStore().goodsDetail).length" :goods-detail="nsGoodsSkuData" @change="specSelectFn"></ns-goods-sku>
        <share-poster ref="sharePosterRef" v-if="Object.keys(useGoodsDetailStore().goodsDetail).length" posterType="shop_goods" :posterId="useGoodsDetailStore().goodsDetail.goods?.poster_id" :posterParam="posterParam" :copyUrlParam="copyUrlParam" @close="posterClose" />
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, nextTick, watch, computed } from 'vue'
import diyGroup from '@/addon/components/diy/group/index.vue'
import { useDiyGoodsDetail } from '@/addon/shop/hooks/useDiyGoodsDetail'
import { useShare } from '@/hooks/useShare'
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'
import nsGoodsSku from '@/addon/shop/components/ns-goods-sku/ns-goods-sku.vue'
import sharePoster from '@/components/share-poster/share-poster.vue'
import useMemberStore from '@/stores/member'

const { setShare } = useShare()

const goodsSkuRef: any = ref(null);
const sharePosterRef: any = ref(null);
const posterParam = ref({});
const copyUrlParam = ref('');
const nsGoodsSkuData = ref()
const diy = useDiyGoodsDetail({
    name: 'DIY_SHOP_GOODS_DETAIL'
})
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)
nsGoodsSkuData.value = JSON.parse(JSON.stringify(useGoodsDetailStore().goodsDetail))

watch(
    () => useGoodsDetailStore().goodsDetail.isOpenSkuBuy,
    (newValue, oldValue) => {
        if (newValue && goodsSkuRef.value) {
			nsGoodsSkuData.value = JSON.parse(JSON.stringify(useGoodsDetailStore().goodsDetail))
            goodsSkuRef.value.open(useGoodsDetailStore().goodsDetail.skuBuyType)
        }
    }
)

watch(
    () => useGoodsDetailStore().goodsDetail.isOpenSharePoster,
    (newValue, oldValue) => {
        if (newValue && sharePosterRef.value) {
            sharePosterRef.value.openShare()
        }
    }
)
const posterClose = () => {
    useGoodsDetailStore().setGoodsDetail({ isOpenSharePoster: false })
}

const specSelectFn = (id: any) => {
    useGoodsDetailStore().goodsDetail.skuList.forEach((item: any, index: any) => {
        if (item.sku_id == id) {
            useGoodsDetailStore()?.setGoodsDetail(item);
        }
    })
}

const diyGroupRef = ref(null)

const wxPrivacyPopupRef: any = ref(null)

// 监听页面加载
diy.onLoad();

// 监听页面显示
diy.onShow((data: any) => {
    let share = data.share;
    setShare({
        wechat: {
            ...share
        },
        weapp: {
            ...share
        }
    });
    diyGroupRef.value?.refresh();

    copyUrlFn()
    nextTick(() => {
        setTimeout(() => {
            // 分享海报
            if (sharePosterRef.value) {
                posterParam.value.sku_id = useGoodsDetailStore().goodsDetail.sku_id;
                if (diy.data.global.goodsParameter.type) posterParam.value.active = diy.data.global.goodsParameter.type;
                if (userInfo.value && userInfo.value.member_id) posterParam.value.member_id = userInfo.value.member_id;
                sharePosterRef.value.loadPoster();
            }
        }, 500);
    })

    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
});

// 分享海报链接
const copyUrlFn = () => {
    copyUrlParam.value = '?sku_id=' + useGoodsDetailStore().goodsDetail.sku_id;
    if (useGoodsDetailStore().goodsDetail.type) {
        copyUrlParam.value += '&type=' + useGoodsDetailStore().goodsDetail.type;
    }
    if (userInfo.value && userInfo.value.member_id) copyUrlParam.value += '&mid=' + userInfo.value.member_id;
}

// 监听页面隐藏
diy.onHide();

// 监听页面卸载
diy.onUnload();

// 监听滚动事件
diy.onPageScroll()
</script>
<style lang="scss" scoped>
@import '@/styles/diy.scss';
</style>
