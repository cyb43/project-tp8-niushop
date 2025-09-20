import { defineStore } from 'pinia'
import { t } from '@/locale'
import { redirect, getToken } from '@/utils/common'

interface GoodsDetail {
    goodsDetail: AnyObject
}

const useGoodsDetailStore = defineStore('goodsDetail', {
    state: (): GoodsDetail => {
        return {
            goodsDetail: {}
        }
    },
    actions: {
        setGoodsDetail(data: any, isAllUpdate:Boolean = false) { // 商品详情
            if(isAllUpdate){
                this.goodsDetail = data;
            }else{
                Object.assign(this.goodsDetail, data);
            }
        },
        removeGoodsDetail(){ // 商品详情
            this.goodsDetail = {}
        }
    }
})

export default useGoodsDetailStore
