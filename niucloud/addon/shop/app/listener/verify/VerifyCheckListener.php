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
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\model\order\OrderGoods;

/**
 * 检测核销码对应信息
 */
class VerifyCheckListener
{
    /**
     * @param array $params
     * @return array|void
     */
    public function handle($params = [])
    {
        $params['un_use_msg'] = [];
        $params['is_can_use'] = 1;
        $data = $params['data'];
        //虚拟商品
        if ($params['type'] == 'shopVirtualGoods') {
            $order_goods_id = $data['order_goods_id'] ?? 0;
            $order_goods_info = (new OrderGoods())->where([['order_goods_id', '=', $order_goods_id]])->with([
                'goods' => function ($q) {
                    $q->field('goods_id,status');
                }
            ])->findOrEmpty()->toArray();
            $un_use_msg = '';
            if (in_array($order_goods_info['delivery_status'], [OrderDeliveryDict::TAKED, OrderDeliveryDict::EXPIRE])) {
                $params['un_use_msg'][] = get_lang('SHOP_ORDER_ITEM_HAS_BEEN_WRITTEN_OFF_OR_EXPIRED');
                $params['is_can_use'] = 0;
                $un_use_msg = get_lang('SHOP_ORDER_ITEM_HAS_BEEN_WRITTEN_OFF_OR_EXPIRED');
            } else if (in_array($order_goods_info['status'], [OrderGoodsDict::REFUNDING, OrderGoodsDict::REFUND_FINISH])) {
                $params['un_use_msg'][] = get_lang('SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED');
                $params['is_can_use'] = 0;
                $un_use_msg = get_lang('SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED');
            } else if ($order_goods_info['goods']['status'] != 1) {
                $params['un_use_msg'][] = '商品已下架';
                $params['is_can_use'] = 0;
                $un_use_msg = '商品已下架';
            }
            $params['value']['list'][0]['un_use_msg'] = $un_use_msg;
            return $params;
        } else if ($params['type'] == 'shopPickUpOrder') {//自提订单
            $order_id = $data['order_id'] ?? 0;
            //todo 订单存在退款
            $order_goods_list = (new OrderGoods())->where([['order_id', '=', $order_id]])->with([
                'goods' => function ($q) {
                    $q->field('goods_id,status,goods_name');
                }
            ])->select()->toArray();
            $verify_list = $params['value']['list'];
            foreach ($verify_list as &$value) {
                $value['un_use_msg'] = '';
                foreach ($order_goods_list as $order_goods_info) {
                    if ($value['order_goods_id'] == $order_goods_info['order_goods_id']) {
                        if (in_array($order_goods_info['status'], [OrderGoodsDict::REFUNDING, OrderGoodsDict::REFUND_FINISH])) {
                            $params['un_use_msg'][] = get_lang('SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED');
                            $params['is_can_use'] = 0;
                            $value['un_use_msg'] = get_lang('SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED');
                            continue;
                        }
                        if ($order_goods_info['goods']['status'] != 1) {
                            $params['un_use_msg'][] = $order_goods_info['goods']['goods_name'] . '已下架';
                            $params['is_can_use'] = 0;
                            $value['un_use_msg'] = '商品已下架';
                        }
                    }
                }
            }
            $params['value']['list'] = $verify_list;
            return $params;
        }

    }
}
