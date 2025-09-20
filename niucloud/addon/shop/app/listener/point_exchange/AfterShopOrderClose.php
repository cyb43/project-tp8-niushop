<?php
declare (strict_types=1);

namespace addon\shop\app\listener\point_exchange;


use addon\shop\app\service\core\pointexchange\CoreGoodsSaleNumService;
use addon\shop\app\service\core\pointexchange\CoreGoodsStockService;
use addon\shop\app\model\order\OrderGoods;
use app\service\core\member\CoreMemberAccountService;
use addon\shop\app\model\order\OrderRefund;
use core\exception\CommonException;
use Exception;
use think\facade\Db;
use think\facade\Log;

/**
 *  订单关闭后 积分商城业务服务层
 */
class AfterShopOrderClose
{

    public function handle($data)
    {
        Log::write('订单AfterShopOrderClose' . json_encode($data));
        try {
            $order_data = $data['order_data'];
            if (isset($data['order_data']) && !empty($data[ 'order_data' ][ 'activity_type' ])  && $data['order_data']['activity_type'] == 'exchange') {
                $order_goods_where = array(
                    ['order_id', '=', $order_data['order_id']]
                );
                $order_goods_data = (new OrderGoods())->where($order_goods_where)->select()->toArray();
                //减去商品主体库存
                $core_goods_stock_service = new CoreGoodsStockService();
                foreach ($order_goods_data as $v) {
                    // todo 可以优化 暂不处理
                    $core_goods_stock_service->inc([
                        'num' => $v['num'],
                        'goods_id' => $v['goods_id'],
                        'sku_id' => $v['sku_id'],
                    ]);
                }
                //销量 +  兑换数量 +累积分消费 返还
                $core_goods_sale_num_service = new CoreGoodsSaleNumService();
                $dec_data = [];
                foreach ($order_goods_data as $v) {
                    //商品累计销量
//                    $core_goods_sale_num_service->dec([
//                        'num' => $v['num'],
//                        'goods_id' => $v['goods_id'],
//                        'sku_id' => $v['sku_id'],
//                        'id' => $order_data['relate_id'],
//                        'point' => $order_data['point'],
//                        'goods_money' => $v['goods_money'],
//                    ]);

                    $dec_data[] = [
                        'total_exchange_num' => Db::raw('total_exchange_num-' . $v['num']),
                        'total_member_num' => Db::raw('total_member_num-1'),
                        'total_point_num' => Db::raw('total_point_num-' . $order_data['point']),
                        'total_price_num' => Db::raw('total_price_num-' . $v['goods_money']),
                        'total_order_num' => Db::raw('total_order_num-1'),
                        'goods_id' => $v['goods_id'],
                        'sku_id' => $v['sku_id'],
                        'id' => $order_data['relate_id'],
                    ];


                }
                $core_goods_sale_num_service->batchUpdate($dec_data);
                $point = 0;
                foreach ($order_goods_data as $v) {
                    if (empty($v['order_refund_no'])) {
                        $point = ($v['extend']['point'] ?? 0) * $v['num'];
                        $from_type = 'account_point_exchange_close';
                        $memo = '积分商城订单关闭积分返还';
                    } else {
                        $order_refund_info = (new OrderRefund)->where([
                            ['order_refund_no', '=', $v['order_refund_no']]
                        ])->findOrEmpty();
                        if ($order_refund_info->isEmpty()) throw new CommonException('SHOP_ORDER_REFUND_IS_INVALID');//退款已失效
//                        if ($order_refund_info['money'] == $v['order_goods_money']) {
                            $point = ($v['extend']['point'] ?? 0) * $v['num'];
                            $from_type = 'account_point_exchange_refund';
                            $memo = '积分商城订单售后成功积分返还';
//                        }
                    }
                    if ($point > 0) {
                        (new CoreMemberAccountService())->addLog($order_data['member_id'], 'point', $order_data['point'], $from_type, $memo ?? '');
                    }
                }
            }
        } catch ( Exception $e ) {
            Log::write('订单AfterShopOrderClose失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }
}
