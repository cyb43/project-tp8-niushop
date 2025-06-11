import { defineStore } from 'pinia'
import { t } from '@/locale'
import { redirect, getToken } from '@/utils/common'
import { addCart, editCart, deleteCart, getCartList } from '@/addon/shop/api/cart';

interface Cart {
    cartList: AnyObject
    totalNum: number
    totalMoney: number
    isRepeat: boolean
}

const useCartStore = defineStore('cart', {
    state: (): Cart => {
        return {
            cartList: {}, // 购物车列表
            totalNum: 0, // 购物车商品总数量
            totalMoney: 0, // 购物车商品总价格
            isRepeat: false
        }
    },
    actions: {
        // 查询购物车列表
        getList(callback: any = null) {
            if (!getToken()) {
                // 每次查询清空
                for (let k in this.cartList) {
                    delete this.cartList[k];
                }
                this.totalNum = 0;
                this.totalMoney = 0;
                return;
            }
            getCartList({}).then((res: any) => {
                let data = res.data;

                // 每次查询清空
                for (let k in this.cartList) {
                    delete this.cartList[k];
                }
                if (data) {
                    data.forEach((item: any) => {
                        if (item.goods.status == 1 && item.goods.delete_time == 0 && item.goodsSku) {
                            let cart: any = {
                                id: item.id,
                                goods_id: item.goods_id,
                                sku_id: item.sku_id,
                                stock: item.goodsSku.stock,
                                num: item.num,
                                sale_price: item.goodsSku.show_price
                            };

                            // if (item.goods.member_discount && getToken() && item.goodsSku.member_price != item.goodsSku.price) {
                            //     cart.sale_price = item.goodsSku.member_price ? item.goodsSku.member_price : item.goodsSku.price // 会员价
                            // }

                            // todo sale_price 改成 price

                            if (!this.cartList['goods_' + cart.goods_id]) {
                                this.cartList['goods_' + cart.goods_id] = {};
                            }
                            this.cartList['goods_' + cart.goods_id]['sku_' + cart.sku_id] = cart;
                        }
                    })
                }
                this.calculateNum();
                if (callback && typeof callback == 'function') callback();
            })
        },
        /**
         * 购物车数量增加
         * data：数据源
         * step：记步数量，默认为：1，每次加一个，设置为：0时，按照 num 修改
         */
        increase(data: any, step = 1, callback: any = null) {
            if (!data || (data && Object.keys(data).length == 0) || !data.goods_id || !data.sku_id) return;
            if (!getToken()) return;

            let num = data.num || 0; // 当前数量
            let updateNum = num + step; // 变更数量

            let api = data.id ? editCart : addCart;

            if (updateNum > parseInt(data.stock)) {
                uni.showToast({ title: '商品库存不足', icon: 'none' })
                return;
            }

            if (this.isRepeat) return;
            this.isRepeat = true;

            // 更新存储数据
            if (data.id) {
                this.cartList['goods_' + data.goods_id]['sku_' + data.sku_id].num = updateNum;
            } else {

                // 如果商品第一次添加，则初始化数据
                if (!this.cartList['goods_' + data.goods_id]) {
                    this.cartList['goods_' + data.goods_id] = {};
                }
                this.cartList['goods_' + data.goods_id]['sku_' + data.sku_id] = {
                    id: data.id,
                    goods_id: data.goods_id,
                    sku_id: data.sku_id,
                    stock: data.stock,
                    num: updateNum,
                    sale_price: data.sale_price
                };
            }

            this.calculateNum();

            api({
                id: data.id,
                goods_id: data.goods_id,
                sku_id: data.sku_id,
                num: updateNum
            }).then(res => {
                this.getList(callback)
                this.isRepeat = false;
            }).catch(res => {
                this.isRepeat = false;
            })

        },
        /**
         * 购物车数量减少
         * data：数据源
         * step：记步数量，默认为：1，每次减一个，设置为：0时，按照 num 修改
         */
        reduce(data: any, step = 1, callback: any = null) {
            if (!data || (data && Object.keys(data).length == 0) || !data.goods_id || !data.sku_id) return;
            if (!getToken()) return;

            let num = data.num || 0; // 当前数量
            let updateNum = num - step; // 变更数量

            let api = updateNum > 0 ? editCart : deleteCart;

            if (this.isRepeat) return;
            this.isRepeat = true;

            // 更新存储数据
            if (updateNum > 0) {
                this.cartList['goods_' + data.goods_id]['sku_' + data.sku_id].num = updateNum;
            } else {
                delete this.cartList['goods_' + data.goods_id]['sku_' + data.sku_id];
                if (Object.keys(this.cartList['goods_' + data.goods_id]).length == 0) delete this.cartList['goods_' + data.goods_id];
            }

            this.calculateNum();

            api({
                ids: data.id, // 删除接口用
                id: data.id,
                goods_id: data.goods_id,
                sku_id: data.sku_id,
                num: updateNum
            }).then(res => {
                this.getList(callback);
                this.isRepeat = false;
            }).catch(res => {
                this.isRepeat = false;
            })
        },
        // 购物车删除
        delete(ids: any, callback: any = null) {
            if (!ids) return;

            deleteCart({
                ids
            }).then(res => {
                this.getList();
                this.isRepeat = false;
                if (callback) callback();
            }).catch(res => {
                this.isRepeat = false;
            })
        },

        // 计算购物车商品的总数量、总价格
        calculateNum() {
            this.totalNum = 0;
            this.totalMoney = 0;

            if (Object.keys(this.cartList).length) {
                for (let goods in this.cartList) {
                    let totalNum = 0;
                    let totalMoney = 0;
                    for (let sku in this.cartList[goods]) {
                        if (typeof this.cartList[goods][sku] == 'object') {
                            totalNum += this.cartList[goods][sku].num;
                            totalMoney += this.cartList[goods][sku].num * this.cartList[goods][sku].sale_price;
                        }
                    }
                    this.cartList[goods].totalNum = totalNum;
                    this.cartList[goods].totalMoney = totalMoney.toFixed(2);

                    this.totalNum += totalNum;
                    this.totalMoney += totalMoney;

                }
            }
            this.totalMoney = this.totalMoney.toFixed(2);
        }
    }
})

export default useCartStore
