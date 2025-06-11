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
use addon\shop\app\dict\order\OrderDiscountDict;
use addon\shop\app\model\active\ActiveGoods;
use addon\shop\app\service\core\marketing\CoreNewcomerService;
use think\facade\Log;

/**
 * 新人专享
 * Class ShopNewcomerCalculate
 * @package addon\shop\app\listener\marketing
 */
class ShopNewcomerCalculate
{
    public function handle(array $params)
    {
        Log::write('参与新人专享活动，ShopNewcomerCalculate:' . json_encode($params));
        $sku_info = $params[ 'sku_info' ] ?? [];
        $order_obj = $params[ 'order_obj' ];

        $is_newcomer = ( new CoreNewcomerService() )->checkIfNewcomer($order_obj->member_id);

        if (!empty($order_obj->extend_data) && $sku_info && $order_obj->extend_data[ 'activity_type' ] == ActiveDict::NEWCOMER_DISCOUNT && $is_newcomer) {

            //查询新人专享活动
            $newcomer_goods_info = ( new ActiveGoods() )->field('active_id,active_goods_value')->where([
                [ 'goods_id', '=', $sku_info[ 'goods_id' ] ],
                [ 'sku_id', '=', $sku_info[ 'sku_id' ] ],
                [ 'active_class', '=', ActiveDict::NEWCOMER_DISCOUNT ]
            ])->with([
                'active' => function($query) {
                    $query->field('active_id,active_desc,active_status,active_value');
                }
            ])->findOrEmpty()->toArray();

            if (!empty($newcomer_goods_info) && $newcomer_goods_info[ 'active' ][ 'active_status' ] == ActiveDict::ACTIVE) {
                $newcomer_price = json_decode($newcomer_goods_info[ 'active_goods_value' ], true)[ 'newcomer_price' ];
                $sku_info[ 'newcomer_price' ] = $newcomer_price;
                $sku_info[ 'is_newcomer' ] = 1;
                $sku_info[ 'extend' ] = [ 'is_newcomer' => 1, 'newcomer_price' => $newcomer_price ];
                $order_obj->discount[ ActiveDict::NEWCOMER_DISCOUNT ] = $order_obj->discountFormat(
                    [ $sku_info[ 'sku_id' ] ],
                    OrderDiscountDict::DISCOUNT,
                    1,
                    number_format($sku_info[ 'price' ] - $newcomer_price, '2', '.'),
                    ActiveDict::NEWCOMER_DISCOUNT,
                    $newcomer_goods_info[ 'active_id' ],
                    '新人专享',
                    $newcomer_goods_info[ 'active' ][ 'active_desc' ] ?? ''
                );
                if ($sku_info[ 'num' ] == 1) {
                    $sku_info[ 'price' ] = $newcomer_price;
                    $sku_info[ 'goods_money' ] = $sku_info[ 'price' ] * $sku_info[ 'num' ];//只有一个商品时使用新人价计算
                } else {
                    // 超出数量按照正常价格计算（原价/会员价）
                    $sku_info[ 'goods_money' ] = $newcomer_price * 1 + $sku_info[ 'member_price' ] * ( intval($sku_info[ 'num' ]) - 1 );
                }

                return [
                    'sku_info' => $sku_info,
                    'relate_id' => $newcomer_goods_info[ 'active_id' ],
                    'activity_type' => ActiveDict::NEWCOMER_DISCOUNT
                ];
            }

        }
    }
}
