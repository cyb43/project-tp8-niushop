<?php
declare ( strict_types = 1 );

namespace addon\shop\app\listener\order;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\order\InvoiceDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\job\order\OrderPayRemind;
use addon\shop\app\model\order\Order;
use addon\shop\app\service\core\cart\CoreCartService;
use addon\shop\app\service\core\CoreStatService;
use addon\shop\app\service\core\goods\CoreGoodsSaleNumService;
use addon\shop\app\service\core\goods\CoreGoodsStatService;
use addon\shop\app\service\core\goods\CoreGoodsStockService;
use addon\shop\app\service\core\order\CoreInvoiceService;
use addon\shop\app\service\core\order\CoreOrderConfigService;
use addon\shop\app\service\core\order\CoreOrderLogService;
use think\facade\Db;
use think\facade\Log;

class AfterShopOrderCreate
{

    public function handle($data)
    {
        Log::write('订单AfterShopOrderCreate' . json_encode($data));
        try {
            $basic = $data[ 'basic' ];
            $order_data = $data[ 'order_data' ];
            $order_goods_data = $data[ 'order_goods_data' ] ?? [];
//        Db::startTrans();
//        try{
            //减去商品库存
            //循环商品项扣除库存

            $core_goods_stock_service = new CoreGoodsStockService();
            $dec_data = [];
            foreach ($order_goods_data as $v) {
                $dec_data['goods'][] =[
                    'stock' => Db::raw('stock-' . $v[ 'num' ]),
                    'goods_id' => $v[ 'goods_id' ],
                ];
                $dec_data['sku'][] =[
                    'stock' => Db::raw('stock-' . $v[ 'num' ]),
                    'sku_id' => $v[ 'sku_id' ],
                ];
            }
            $core_goods_stock_service->batchUpdateStock($dec_data);
//            Db::commit();
//        } catch (\Exception $e) {
//            Db::rollback();
//            throw new CommonException($e->getMessage());
//        }

            //购物车删除
            $cart_ids = $data[ 'cart_ids' ] ?? [];
            if ($cart_ids) {
                ( new CoreCartService() )->deleteByCartIds($cart_ids);
            }

            //累增销量
            $core_goods_sale_num_service = new CoreGoodsSaleNumService();
            $inc_data = [];
            foreach ($order_goods_data as $v) {
                //商品累计销量
                $inc_data['goods'][] =[
                    'sale_num' => Db::raw('sale_num+' . $v[ 'num' ]),
                    'goods_id' => $v[ 'goods_id' ],
                ];
                $inc_data['sku'][] =[
                    'sale_num' => Db::raw('sale_num+' . $v[ 'num' ]),
                    'sku_id' => $v[ 'sku_id' ],
                ];
                // todo 可以优化 逻辑较多 后置
                // 商品销量统计 - 下单数
                CoreGoodsStatService::addStat([ 'goods_id' => $v[ 'goods_id' ], 'sale_num' => $v[ 'num' ] ]);
            }
            $core_goods_sale_num_service->batchUpdateSaleNum($inc_data);
            //写入发票
            $invoice = $basic[ 'invoice' ] ?? [];
            if (!empty($invoice)) {
                $invoice_id = ( new CoreInvoiceService() )->add([
                    'type' => $invoice[ 'type' ] ?? '',
                    'name' => $invoice[ 'name' ],
                    'header_type' => $invoice[ 'header_type' ],
                    'header_name' => $invoice[ 'header_name' ],
                    'tax_number' => $invoice[ 'tax_number' ],
                    'mobile' => $invoice[ 'mobile' ],
                    'email' => $invoice[ 'email' ],
                    'telephone' => $invoice[ 'telephone' ],
                    'address' => $invoice[ 'address' ],
                    'bank_name' => $invoice[ 'bank_name' ],
                    'bank_card_number' => $invoice[ 'bank_card_number' ],
                    'money' => $order_data[ 'order_money' ],
                    'status' => InvoiceDict::WAIT_OPEN,
                    'member_id' => $order_data[ 'member_id' ],
                    'trade_type' => $order_data[ 'order_type' ],
                    'trade_id' => $order_data[ 'order_id' ],
                ]);
                //修改订单发票id
                ( new Order() )->where([
                    [ 'order_id', '=', $order_data[ 'order_id' ] ]
                ])->update([ 'invoice_id' => $invoice_id ]);
            }

            //发布日志
            $main_type = $data[ 'main_type' ] ?? OrderLogDict::MEMBER;
            $main_id = $data[ 'main_id' ] ?? $order_data[ 'member_id' ];
            ( new CoreOrderLogService() )->add([
                'order_id' => $order_data[ 'order_id' ],
                'status' => OrderDict::WAIT_PAY,
                'main_type' => $main_type,//todo  可以是传入的
                'main_id' => $main_id,
                'type' => OrderDict::ORDER_CREATE_ACTION,
                'content' => ''
            ]);

            //消息发送

            //创建定时关闭任务
            $core_order_config_service = new CoreOrderConfigService();
            $order_config = $core_order_config_service->orderClose();
            if ($order_config[ 'is_close' ] == 1) {
                if ($order_config[ 'close_length' ] > 0) {
                    ( new Order() )->where([ [ 'order_id', '=', $order_data[ 'order_id' ] ] ])->update([
                        'timeout' => $data[ 'time' ] + $order_config[ 'close_length' ] * 60
                    ]);
//                OrderClose::dispatch(['order_id' => $order_data['order_id'] ], secs: $order_config['close_length'] * 60);
                }
            }
            //增加统计数据
            CoreStatService::addStat([ 'order_num' => 1 ]);

            //新人专享活动扣除参与资格
            $goods_ids = [];//参与商品id集合
            $sku_ids = [];//参与商品规格id集合
            foreach ($order_goods_data as $v) {
                //判断商品是否享受新人专享活动
                if (isset($v[ 'extend' ]) && !empty($v[ 'extend' ]) && isset($v[ 'extend' ][ 'is_newcomer' ]) && $v[ 'extend' ][ 'is_newcomer' ]) {
                    $goods_ids[] = $v[ 'goods_id' ];
                    $sku_ids[] = $v[ 'sku_id' ];
                }
            }
            if ($order_data[ 'activity_type' ] == ActiveDict::NEWCOMER_DISCOUNT) {
                event("NewcomerActiveJoin", [ 'member_id' => $order_data[ 'member_id' ], 'is_join' => 1, 'order_id' => $order_data[ 'order_id' ], 'goods_ids' => $goods_ids, 'sku_ids' => $sku_ids ]);
            }
        } catch (\Exception $e) {
            Log::write('订单AfterShopOrderCreate失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
        // 订单催付通知
        OrderPayRemind::dispatch([ 'order_id' => $order_data[ 'order_id' ] ], secs: 1800);
    }
}
