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
use think\facade\Db;
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
                $recover_list = [];
                foreach ($order_discount as $v) {
                    $item_discount_type = $v['discount_type'];
                    $recover_list[$item_discount_type][] = $v['discount_type_id'];
                }
                foreach ($recover_list as $item_discount_type => $discount_type_ids) {
                    switch ($item_discount_type) {
                        case 'coupon'://优惠券
                            (new CoreCouponMemberService())->recover($discount_type_ids);
                            break;
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
            $inc_data = [];
            foreach ($order_goods_data as $v) {
                $stock = $v['num'];
                $inc_data['goods'][] = [
                    'stock' => Db::raw("stock+" . $stock),
                    'goods_id' => $v['goods_id']
                ];
                $inc_data['sku'][] = [
                    'stock' => Db::raw(" stock+" . $stock),
                    'sku_id' => $v['sku_id']
                ];
            }
            $core_goods_stock_service->batchUpdateStock($inc_data);
            //商品累计销量
            //累减销量
            $dec_data = [];
            $core_goods_sale_num_service = new CoreGoodsSaleNumService();
            foreach ($order_goods_data as $v) {
                // 商品销量累减 - 下单数（不剔除退款订单）
                if (empty($order_data['pay_time'])) {
                    //商品累计销量
                    $stock = $v['num'];
                    $dec_data['goods'][] = [
                        'sale_num' => Db::raw("sale_num-" . $stock),
                        'goods_id' => $v['goods_id']
                    ];
                    $dec_data['sku'][] = [
                        'sale_num' => Db::raw(" sale_num-" . $stock),
                        'sku_id' => $v['sku_id']
                    ];
                    //TODO::可以优化  后置
                    CoreGoodsStatService::decStat(['goods_id' => $v['goods_id'], 'time' => $order_data['create_time'], 'sale_num' => $v['num']]);
                }
            }
            $core_goods_sale_num_service->batchUpdateSaleNum($dec_data);


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
                event("NewcomerActiveJoin", ['member_id' => $order_data['member_id'], 'is_join' => 0, 'order_id' => $order_data['order_id']]);
            }
        } catch (\Exception $e) {
            Log::write('订单AfterShopOrderClose失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }
}
