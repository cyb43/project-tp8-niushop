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

namespace addon\shop\app\listener\verify;


use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\order\CoreOrderFinishService;
use core\exception\CommonException;

/**
 * 核销功能实现
 */
class VerifyListener
{
    /**
     * @param array $params
     * @throws \think\db\exception\DbException
     */
    public function handle($params = [])
    {
        if ($params[ 'type' ] == 'shopVirtualGoods') {
            //执行
            $param = $params[ 'data' ];//订单信息
            $order_goods_id = $param[ 'order_goods_id' ];
            $order_goods_info = ( new OrderGoods() )->where([[ 'order_goods_id', '=', $order_goods_id ] ])->findOrEmpty();
            if ($order_goods_info->isEmpty()) throw new CommonException('SHOP_ORDER_NOT_FOUND');

            if (in_array($order_goods_info[ 'delivery_status' ], [ OrderDeliveryDict::TAKED, OrderDeliveryDict::EXPIRE ])) throw new CommonException('SHOP_ORDER_ITEM_HAS_BEEN_WRITTEN_OFF_OR_EXPIRED');
            if (in_array($order_goods_info[ 'status' ], [ OrderGoodsDict::REFUNDING, OrderGoodsDict::REFUND_FINISH ])) throw new CommonException('SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED');
            if (strtotime($order_goods_info[ 'verify_expire_time' ]) > 0 && strtotime($order_goods_info[ 'verify_expire_time' ]) < time()) throw new CommonException('SHOP_ORDER_ITEM_HAS_EXPIRED');
            if ($order_goods_info[ 'verify_count' ] >= $order_goods_info[ 'num' ]) throw new CommonException('SHOP_ORDER_MAXIMUM_NUMBER_OF_WRITE_OFFS_HAS_BEEN_REACHED');

            //todo  判断订单项状态(已收货 已核销  核销已过期)
            $order_info = ( new Order() )->where([ [ 'order_id', '=', $order_goods_info[ 'order_id' ] ] ])->findOrEmpty();
            if ($order_info->isEmpty()) throw new CommonException('SHOP_ORDER_NOT_FOUND');
            if (in_array($order_info[ 'status' ], [ OrderDict::FINISH, OrderDict::CLOSE ])) throw new CommonException('SHOP_ORDER_HAS_BEEN_CLOSED_OR_COMPLETED');
            //核销次数累加1,达到最大核销次数后订单自动完成
            $order_goods_info->save([
                'verify_count' => $order_goods_info[ 'verify_count' ] + 1,
            ]);
            //核销完成,顺便订单完成
            if ($order_goods_info[ 'verify_count' ] >= $order_goods_info[ 'num' ]) {
                ( new CoreOrderFinishService() )->finish([
                    'main_type' => OrderLogDict::SYSTEM,
                    'main_id' => 0,
                    'order_id' => $order_goods_info[ 'order_id' ],
                ]);
            }
            //存库
        } else if ($params[ 'type' ] == 'shopPickUpOrder') {
            $param = $params[ 'data' ];//订单信息
            $order_id = $param[ 'order_id' ];
            $order_info = ( new Order() )->where([ [ 'order_id', '=', $order_id ] ])->findOrEmpty();
            if ($order_info->isEmpty()) throw new CommonException('SHOP_ORDER_NOT_FOUND');
            if (in_array($order_info[ 'status' ], [ OrderDict::FINISH, OrderDict::CLOSE ])) throw new CommonException('SHOP_ORDER_HAS_BEEN_CLOSED_OR_COMPLETED');
            $order_goods_list = ( new OrderGoods() )->where([
                [ 'order_id', '=', $order_id ],
            ])->select()->toArray();
            foreach ($order_goods_list as $order_goods_info) {
                if (in_array($order_goods_info[ 'delivery_status' ], [ OrderDeliveryDict::TAKED, OrderDeliveryDict::EXPIRE ])) throw new CommonException('SHOP_ORDER_ITEM_HAS_BEEN_WRITTEN_OFF_OR_EXPIRED');
                if (in_array($order_goods_info[ 'status' ], [ OrderGoodsDict::REFUNDING, OrderGoodsDict::REFUND_FINISH ])) throw new CommonException('SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED');
            }
            //暂时自提订单核销为统一核销 故校验完数据后直接结束该自提订单
            ( new CoreOrderFinishService() )->finish([
                'main_type' => OrderLogDict::SYSTEM,
                'main_id' => 0,
                'order_id' => $order_id,
            ]);
        }
        return;
    }
}
