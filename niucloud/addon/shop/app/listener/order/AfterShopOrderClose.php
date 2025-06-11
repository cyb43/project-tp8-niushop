<?php
declare (strict_types=1);

namespace addon\shop\app\listener\order;

use addon\shop\app\service\core\goods\CoreGoodsStatService;
use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\model\order\OrderDiscounts;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use addon\shop\app\service\core\goods\CoreGoodsSaleNumService;
use addon\shop\app\service\core\goods\CoreGoodsStockService;
use addon\shop\app\service\core\order\CoreInvoiceService;
use addon\shop\app\service\core\order\CoreOrderLogService;
use think\facade\Log;

/**
 * 订单关闭后操作
 */
class AfterShopOrderClose
{

    public function handle($data)
    {
        Log::write('订单AfterShopOrderClose' . json_encode($data));
        try {
            $order_data = $data['order_data'];

            //退还优惠项
            $order_discount_where = array(
                ['order_id', '=', $order_data['order_id']]
            );
            $order_discount = (new OrderDiscounts())->where($order_discount_where)->select();
            if (!$order_discount->isEmpty()) {
                foreach ($order_discount as $v) {
                    $item_discount_type = $v['discount_type'];
                    switch ($item_discount_type) {
                        case 'coupon'://优惠券
                            (new CoreCouponMemberService())->recover($v['discount_type_id']);
                            break;
//                    case 'point':
//
//                        break;
                    }
                }
            }
            $order_goods_where = array(
                ['order_id', '=', $order_data['order_id']],
                ['is_gift', '=', 0],
            );
            $order_goods_data = (new OrderGoods())->where($order_goods_where)->select()->toArray();
            //返还商品库存
            $core_goods_stock_service = new CoreGoodsStockService();
            foreach ($order_goods_data as $v) {
                $core_goods_stock_service->inc([
                    'num' => $v['num'],
                    'goods_id' => $v['goods_id'],
                    'sku_id' => $v['sku_id']
                ]);
            }

            //商品累计销量
            //累减销量
            $core_goods_sale_num_service = new CoreGoodsSaleNumService();
            foreach ($order_goods_data as $v) {
                //商品累计销量
                $core_goods_sale_num_service->dec([
                    'num' => $v['num'],
                    'goods_id' => $v['goods_id'],
                    'sku_id' => $v['sku_id']
                ]);

                // 商品销量累减 - 下单数
                CoreGoodsStatService::decStat([ 'goods_id' => $v[ 'goods_id' ], 'time' => $order_data['create_time'], 'sale_num' => $v[ 'num' ] ]);
            }

            //发票改变状态........
            (new CoreInvoiceService())->close($order_data['invoice_id']);
            //发布日志
            $main_type = $data['main_type'];
            $main_id = $data['main_id'] ?? 0;
            (new CoreOrderLogService())->add([
                'order_id' => $order_data['order_id'],
                'status' => OrderDict::CLOSE,
                'main_type' => $main_type,//todo  可以是传入的
                'main_id' => $main_id,
                'type' => OrderDict::ORDER_CLOSE_ACTION,
                'content' => ''
            ]);
            //todo 消息发送

            //新人专享活动退还参与资格
            if ($order_data['activity_type'] == ActiveDict::NEWCOMER_DISCOUNT) {
                event("NewcomerActiveJoin", ['member_id' => $order_data[ 'member_id' ], 'is_join' => 0, 'order_id' => $order_data[ 'order_id' ]]);
            }
        } catch ( \Exception $e ) {
            Log::write('订单AfterShopOrderClose失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }
}
