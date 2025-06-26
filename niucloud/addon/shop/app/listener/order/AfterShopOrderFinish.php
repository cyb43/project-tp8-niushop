<?php
declare ( strict_types = 1 );

namespace addon\shop\app\listener\order;

use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\order\CoreOrderConfigService;
use addon\shop\app\service\core\order\CoreOrderFinishService;
use addon\shop\app\service\core\order\CoreOrderLogService;
use app\service\core\member\CoreMemberService;
use app\service\core\printer\CorePrinterService;
use think\facade\Log;

class AfterShopOrderFinish
{

    public function handle($data)
    {
        Log::write('订单AfterShopOrderFinish' . json_encode($data));
        try {
            $order_data = $data[ 'order_data' ];
            //日志
            $main_type = $data[ 'main_type' ] ?? OrderLogDict::SYSTEM;
            $main_id = $data[ 'main_id' ] ?? 0;
            ( new CoreOrderLogService() )->add([
                'order_id' => $order_data[ 'order_id' ],
                'status' => OrderDict::FINISH,
                'main_type' => $main_type,
                'main_id' => $main_id,
                'type' => OrderDict::ORDER_FINISH_ACTION,
                'content' => ''
            ]);
            //消息发送

            //定时关闭订单允许退款开关
            $core_order_config_service = new CoreOrderConfigService();
            $order_config = $core_order_config_service->orderRefund(); //确认收货后售后，1.开启，2.关闭
            if ($order_config[ 'no_allow_refund' ] == 2) {//等于2，不支持确认收货后售后，此时要关闭退款，否则不关闭
                ( new OrderGoods() )->where([ [ 'order_id', '=', $order_data[ 'order_id' ] ] ])->update([
                    'is_enable_refund' => 0
                ]);
            } else {
                if ($order_config[ 'refund_length' ] > 0) {
                    ( new Order() )->where([ [ 'order_id', '=', $order_data[ 'order_id' ] ] ])->update([
                        'is_enable_refund' => 1,
                        'timeout' => $order_data[ 'finish_time' ] + $order_config[ 'refund_length' ] * 86400  //确认收货后售后，单位为天
                    ]);
//                OrderCloseAllowRefund::dispatch(['order_id' => $order_data['order_id'] ], secs: $order_config['refund_length'] * 86400);
                }
            }

            // 订单完成发放积分成长值
            CoreMemberService::sendGrowth($order_data[ 'member_id' ], 'shop_buy_goods', $order_data);
            CoreMemberService::sendGrowth($order_data[ 'member_id' ], 'shop_buy_order');
            CoreMemberService::sendPoint($order_data[ 'member_id' ], 'shop_buy_goods', array_merge($order_data, [ 'from_type' => 'consume_reward', 'related_id' => $order_data[ 'order_id' ] ]));

            // 微信小程序 发送 确认收货提醒消息
            ( new CoreOrderFinishService() )->orderShippingNotifyConfirmReceive($order_data);

            // 小票打印，订单收货之后
            return ( new CorePrinterService() )->printTicket([
                "type" => "shopGoodsOrder",
                "trigger" => "take_delivery",
                'business' => [
                    'order_id' => $order_data[ 'order_id' ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::write('订单AfterShopOrderFinish失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }
}
