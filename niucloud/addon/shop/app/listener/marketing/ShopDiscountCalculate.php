<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace addon\shop\app\listener\marketing;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\active\DiscountDict;
use addon\shop\app\dict\order\OrderDiscountDict;
use addon\shop\app\model\active\ActiveGoods;
use addon\shop\app\model\discount\DiscountGoods;
use think\facade\Log;

/**
 * 限时折扣
 * Class CouponReceiveListener
 * @package addon\shop\app\listener
 */
class ShopDiscountCalculate
{
    public function handle(array $params)
    {
        Log::write('参与限时折扣活动，ShopDiscountCalculate:' . json_encode($params));
        $sku_info = $params[ 'sku_info' ] ?? [];
        $order_obj = $params[ 'order_obj' ];

        if (!empty($order_obj->extend_data) && $sku_info && $sku_info[ 'goods' ][ 'is_discount' ] && $order_obj->extend_data[ 'activity_type' ] == ActiveDict::DISCOUNT) {

            $discount_goods_info = ( new DiscountGoods() )->where([ [ 'discount_goods.goods_id', '=', $sku_info[ 'goods_id' ] ], [ 'discount_goods.sku_id', '=', $sku_info[ 'sku_id' ] ] ])
                ->withJoin([ 'discount' => function($query) {
                    $query->where([
                        [ 'discount.status', '=', DiscountDict::ACTIVE ],
                    ]);
            } ])->findOrEmpty()->toArray();
            if ($discount_goods_info && $discount_goods_info[ 'discount' ][ 'status' ] == DiscountDict::ACTIVE) {

                if ($discount_goods_info[ 'is_enabled' ]){
                    $order_obj->discount[ ActiveDict::DISCOUNT ] = $order_obj->discountFormat(
                        [ $sku_info[ 'sku_id' ] ],
                        OrderDiscountDict::DISCOUNT,
                        $sku_info[ 'num' ],
                        number_format($sku_info[ 'price' ] - $sku_info[ 'sale_price' ], '2', '.'),
                        ActiveDict::DISCOUNT,
                        $discount_goods_info[ 'discount_id' ],
                        '限时折扣',
                        $discount_goods_info[ 'discount' ][ 'remark' ] ?? ''
                    );
                    $sku_info[ 'price' ] = $sku_info[ 'sale_price' ];
                    $sku_info[ 'goods_money' ] = $sku_info[ 'price' ] * $sku_info[ 'num' ];//小计
                    return [
                        'sku_info' => $sku_info,
                        'relate_id' => $discount_goods_info[ 'discount_id' ],
                        'activity_type' => ActiveDict::DISCOUNT
                    ];
                }

            }

        }
    }
}
