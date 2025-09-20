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

namespace addon\shop\app\service\core\third_addon\order;

use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\order\CoreOrderEventService;
use addon\shop\app\service\core\order\CoreOrderPayService;
use core\base\BaseCoreService;
use think\facade\Log;

/**
 *  三方插件订单服务层
 */
class CoreCreateOrderService extends BaseCoreService
{

    /**
     * 拼团系统对接订单
     * @param array $data
     * @return array|void
     * @throws \Exception
     */
    public function pintuanCreateOrder(array $data)
    {
        $return_order_list = [];
        $source_id = $data['source_id'] ?? 0;
        $source = $data['source'] ?? '';
        $relate_order_list = $data['order_data'] ?? [];
        $total_orders = count($relate_order_list);

        // 合并初始日志：包含关键参数和总数
        Log::write("SHOP_PINTUAN - pintuanCreateOrder 开始处理拼团订单，订单信息" . json_encode($relate_order_list, 256));
        Log::write("SHOP_PINTUAN - pintuanCreateOrder 开始处理拼团订单，来源ID: {$source_id}，总订单数: {$total_orders}");

        if (empty($relate_order_list)) {
            Log::warning("SHOP_PINTUAN - pintuanCreateOrder 接收到的订单数据为空，来源ID: {$source_id}");
            return;
        }

        $processed_orders = 0;
        $failed_orders = []; // 记录失败订单信息

        foreach ($relate_order_list as $index => $relate_order_info) {
            $order_index = $index + 1;
            //兼容以后得数据 如果不是商城的订单全部跳过
            if ($relate_order_info['source'] != 'shop') {
                Log::warning("SHOP_PINTUAN - pintuanCreateOrder 接收到的订单数据 商品来源非商城 跳过，来源ID: {$source_id} 拼团数据来源:" . $relate_order_info['source']);
                continue;
            }
            try {
                // 提取SKU和商品信息
                $sku_info = $relate_order_info['sku'] ?? [];
                $goods_id = $sku_info['source_id'] ?? 0;
                $sku_id = $sku_info['source_sku_id'] ?? 0;
                $relate_order_id = $relate_order_info['id'] ?? 0;

                list($check_res, $reason) = $this->checkGoods( $goods_id, $sku_id, $relate_order_info['num']);
                if (!$check_res) {
                    $failed_orders[] = "第{$order_index}个订单（ID: {$relate_order_id}）" . $reason;
                    $return_order_list[$relate_order_id] = [
                        'relate_order_id' => 0,
                        'relate_order_item_id' => 0,
                        'is_success' => 0,
                        'reason' => $reason
                    ];
                    continue;
                }


                // 检查必要参数
                if (empty($goods_id) || empty($sku_id)) {
                    $failed_orders[] = "第{$order_index}个订单（ID: {$relate_order_id}）缺少商品或SKU信息";
                    continue;
                }

                // 获取商品类型
                $goods_model_info = (new Goods())->where([
                    'goods_id' => $goods_id,
                ])->field('goods_type')->findOrEmpty()->toArray();

                $goods_type = $goods_model_info['goods_type'] ?? '';
                $third_type = $goods_type == GoodsDict::VIRTUAL
                    ? OrderDeliveryDict::VIRTUAL
                    : OrderDeliveryDict::EXPRESS;
                $has_goods_types = [$goods_type];

                $third_cost_list = array_column($relate_order_info['third_cost_list'], null, 'key');
                // 构建订单数据
                $temp_order_data = [
                    'order_type' => OrderDict::TYPE,
                    'status' => OrderDict::WAIT_DELIVERY,
                    'body' => $relate_order_info['body'] ?? '',
                    'member_id' => $relate_order_info['member_id'] ?? 0,
                    'goods_money' => $relate_order_info['goods_money'] ?? 0,
                    'delivery_money' => $third_cost_list['delivery_money']['amount'] ?? 0,
                    'discount_money' => 0,
                    'order_from' => $relate_order_info['order_from'],
                    'order_money' => $relate_order_info['order_money'] ?? 0,
                    'has_goods_types' => $has_goods_types,
                    'delivery_type' => $third_type,
                    'taker_name' => $relate_order_info['taker_name'] ?? '',
                    'taker_mobile' => $relate_order_info['taker_mobile'] ?? '',
                    'buyer_ask_delivery_time' => '',
                    'taker_province' => $relate_order_info['taker_province'] ?? 0,
                    'taker_city' => $relate_order_info['taker_city'] ?? 0,
                    'taker_district' => $relate_order_info['taker_district'] ?? 0,
                    'taker_address' => $relate_order_info['taker_address'] ?? '',
                    'taker_full_address' => $relate_order_info['taker_full_address'] ?? '',
                    'taker_longitude' => $relate_order_info['taker_longitude'] ?? '',
                    'taker_latitude' => $relate_order_info['taker_latitude'] ?? '',
                    'take_store_id' => 0,
                    'member_remark' => '',
                    'order_no' => create_no(),
                    'is_enable_refund' => 1,
                    'invoice_id' => 0,
                    'out_trade_no' => $relate_order_info['out_trade_no'] ?? '',
                    'relate_id' => $source_id,
                    'relate_order_id' => $relate_order_id,
                    'relate_source' => $source,
                    'activity_type' => $source,
                    'create_time' => strtotime($relate_order_info['create_time']),
                    'pay_time' => strtotime($relate_order_info['pay_time']),
                    'pay_money' => $relate_order_info['pay_money'] ?? 0,
                ];
                $order_info = (new Order())->where([
                    'relate_id' => $source_id,
                    'relate_source' => $source,
                    'relate_order_id' => $relate_order_id,
                ])->findOrEmpty();
                if (!$order_info->isEmpty()) {
                    // 修改订单
                    $shop_order_id =$order_info['order_id'];
                    Log::write("SHOP_PINTUAN - pintuanCreateOrder 订单已存在 订单ID: {$shop_order_id}");
                }else{
                    // 创建订单
                    $shop_order_id = (new Order())->insertGetId($temp_order_data);
                    Log::write("SHOP_PINTUAN - pintuanCreateOrder 创建订单成功 订单ID: {$shop_order_id}");
                }


                if (!$shop_order_id) {
                    $failed_orders[] = "第{$order_index}个订单（ID: {$relate_order_id}）创建失败";
                    continue;
                }

                // 构建订单商品数据
                $goods_info = $sku_info['goods'] ?? [];
                $goods_single_money = ($relate_order_info['goods_money'] ?? 0) / ($relate_order_info['num'] ?? 1);
                $order_goods_data = [
                    'member_id' => $relate_order_info['member_id'] ?? 0,
                    'goods_id' => $goods_id,
                    'sku_id' => $sku_id,
                    'goods_name' => $goods_info['title'] ?? '',
                    'sku_name' => $sku_info['sku_name'] ?? '',
                    'goods_image' => $goods_info['cover'] ?? '',
                    'sku_image' => $sku_info['sku_image'] ?? '',
                    'price' => $goods_single_money,
                    'original_price' => $sku_info['price_config']['original'] ?? 0,
                    'num' => $relate_order_info['num'] ?? 0,
                    'goods_money' => ($relate_order_info['goods_money'] ?? 0),
                    'goods_type' => $goods_type,
                    'order_goods_money' => $goods_single_money,
                    'order_id' => $shop_order_id,
                    'discount_money' => 0,
                    'status' => OrderGoodsDict::NORMAL,
                    'delivery_status' => OrderDeliveryDict::WAIT_DELIVERY,
                    'extend' => '',
                    'is_gift' => 0,
                    'is_enable_refund' => 1,
                ];
                $order_goods_id = (new OrderGoods())->insertGetId($order_goods_data);
                Log::write("SHOP_PINTUAN - pintuanCreateOrder 创建订单商品成功 订单商品ID: {$order_goods_id}");

                $return_order_list[$relate_order_id] = [
                    'relate_order_id' => $shop_order_id,
                    'relate_order_item_id' => $order_goods_id,
                    'is_success' => 1
                ];
                $temp_order_data['order_id'] = $shop_order_id;
                Log::write("SHOP_PINTUAN - orderCreateAfter 触发事件");

                CoreOrderEventService::orderCreateAfter([
                    'order_id' => $shop_order_id,
                    'order_data' => $temp_order_data,
                    'order_goods_data' => [$order_goods_data],
                    'cart_ids' => [],
                    'basic' => [],
                    'main_type' => OrderLogDict::MEMBER,
                    'main_id' => $relate_order_info['member_id'] ?? 0,
                    'time' => time()
                ]);
                Log::write("SHOP_PINTUAN - orderPayAfter 触发事件");

                CoreOrderEventService::orderPayAfter([
                    'order_id' => $shop_order_id,
                    'order_data' => $temp_order_data,
                    'order_goods_data' => [$order_goods_data],
                    'cart_ids' => [],
                    'basic' => [],
                    'main_type' => OrderLogDict::MEMBER,
                    'main_id' => $relate_order_info['member_id'] ?? 0,
                    'time' => time(),
                    'send_notice'=>0
                ]);
                $processed_orders++;
            } catch (\Exception $e) {
                $failed_orders[] = "第{$order_index}个订单（ID: {$shop_order_id}）处理异常: {$e->getMessage()}";
                Log::error("SHOP_PINTUAN - pintuanCreateOrder 拼团订单处理失败，" . $e->getFile() . $e->getFile() . $e->getMessage() . $e->getTraceAsString());

            }
        }
        // 合并结果日志：包含统计和失败信息
        $result_log = ["总订单数: {$total_orders}", "成功处理: {$processed_orders}", "失败订单: " . count($failed_orders)];
        if (!empty($failed_orders)) {
            $result_log[] = "失败详情: " . implode('; ', $failed_orders);
        }
        Log::write("SHOP_PINTUAN - pintuanCreateOrder 拼团订单处理完成，" . implode('; ', $result_log));

        return $return_order_list;
    }

    private function checkGoods( $goods_id, $sku_id, $num = 1)
    {
        $goods_info = (new Goods())->where([
            'goods_id' => $goods_id,
        ])->findOrEmpty();
        if ($goods_info->isEmpty()) {
            return [false, get_lang('SHOP_GOODS_NOT_EXIST')];
        }
        $sku_info = (new GoodsSku())->where([
            'goods_id' => $goods_id,
            'sku_id' => $sku_id,
        ])->findOrEmpty();
        if ($sku_info->isEmpty()) {
            return [false, get_lang('SHOP_GOODS_NOT_EXIST')];
        }
        $stock = $sku_info['stock'] ?? 0;
        return [$stock >= $num, get_lang('SHOP_ORDER_GOODS_INSUFFICIENT')];
    }
}
