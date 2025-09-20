<?php
declare ( strict_types = 1 );

namespace addon\shop\app\listener\refund;

use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderRefundDict;
use addon\shop\app\dict\order\OrderRefundLogDict;
use addon\shop\app\model\order\Order;
use addon\shop\app\service\core\CoreStatService;
use addon\shop\app\service\core\goods\CoreGoodsStatService;
use addon\shop\app\service\core\marketing\CoreManjianService;
use addon\shop\app\service\core\order\CoreOrderCloseService;
use addon\shop\app\service\core\order\CoreOrderDeliveryService;
use addon\shop\app\service\core\refund\CoreRefundLogService;
use addon\shop\app\service\core\third_addon\order\CoreOrderNoticeService;

/**
 * 退款完成后操作
 */
class AfterShopOrderRefundFinish
{

    public function handle($data)
    {

        $refund_data = $data[ 'refund_data' ];

        $order = ( new Order() )->where([ [ 'order_id', '=', $refund_data[ 'order_id' ] ] ])->findOrEmpty();

        //满减送订单退款完成后退还赠品
        ( new CoreManjianService() )->refundGift($refund_data);

        if (!$order->isEmpty()) {
            if ($order[ 'status' ] == OrderDict::WAIT_DELIVERY) {
                //校验一下订单项是否全部发货
                ( new CoreOrderDeliveryService() )->checkFinish(
                    [ 'order_id' => $refund_data[ 'order_id' ]]
                );
            }
            //校验一下是否全部退款
            if ($order[ 'status' ] != OrderDict::CLOSE) {
                ( new CoreOrderCloseService() )->checkAllClose([ 'order_id' => $refund_data[ 'order_id' ] ]);
            }
        }
        //商城统计
        CoreStatService::addStat([ 'refund_money' => $refund_data[ 'money' ] ]);

        ( new CoreGoodsStatService() )->saveGoodsRefundNumAndMoneyByOrderId($refund_data); // 商品退款数量 金额统计数据

        //日志
        $main_type = $data[ 'main_type' ] ?? OrderRefundLogDict::SYSTEM;
        $main_id = $data[ 'main_id' ] ?? 0;
        //日志
        ( new CoreRefundLogService() )->add([
            'order_refund_no' => $refund_data[ 'order_refund_no' ],
            'status' => $refund_data[ 'status' ],
            'main_type' => $main_type,
            'main_id' => $main_id,
            'type' => OrderRefundDict::FINISH_ACTION,
            'content' => ''
        ]);
        //消息发送

        //通知三方应用更新订单状态
        if(!empty($order['relate_source'])){
            (new CoreOrderNoticeService())->sendRefundFinishNotice($refund_data,$order['relate_source'],$order['relate_order_id']);
        }
    }
}
