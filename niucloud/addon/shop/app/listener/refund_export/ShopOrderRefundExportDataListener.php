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

namespace addon\shop\app\listener\refund_export;

use addon\shop\app\model\order\OrderRefund;

/**
 * 订单退款售后导出数据源查询
 * Class ShopOrderRefundExportDataListener
 * @package addon\shop\app\listener\refund_export
 */
class ShopOrderRefundExportDataListener
{
    public function handle($param)
    {
        $data = [];
        if ($param['type'] == 'shop_order_refund') {
            $model = new OrderRefund();

            $field = 'refund_id, order_id, order_goods_id, order_refund_no, refund_type, reason, member_id, apply_money, money, status, create_time, transfer_time, remark, voucher, source, timeout, refund_no, delivery, shop_reason, refund_address';
            $order = 'create_time desc';

            $search_model = $model
                ->withSearch([ 'order_refund_no', 'status', 'create_time' ], $param['where'])
                ->field($field)
                ->with(
                    [
                        'order_goods' => function($query) {
                            $query->field('order_goods_id, order_id, member_id, goods_id, sku_id, goods_name, sku_name, goods_image, sku_image, price, num, goods_money,discount_money, is_enable_refund, goods_type')->append([ 'goods_image_thumb_small' ]);
                        },
                        'member' => function($query) {
                            $query->field('member_id, nickname');
                        }
                    ])->order($order)->append([ 'status_name', 'refund_type_name' ]);
            if ($param['page']['page'] > 0 && $param['page']['limit'] > 0) {
                $data = $search_model->page($param['page']['page'], $param['page']['limit'])->select()->toArray();
            } else {
                $data = $search_model->select()->toArray();
            }
            foreach ($data as $key => $val) {
                $data[$key]['nickname'] = $val['member']['nickname'];
            }
            foreach ($data as $key => $val) {
                $data[$key]['order_refund_no'] = $val['order_refund_no']."\t";
                $data[$key]['nickname'] = !empty($val['member']) ? $val['member']['nickname'] : '';
                $data[$key]['goods_info'] = $val['order_goods']['goods_name'].' '.$val['order_goods']['sku_name'];
                $data[$key]['refund_num'] = $val['order_goods']['num'];
                $data[$key]['create_time'] = !empty($val['create_time']) ? $val['create_time'] : '';
                $data[$key]['transfer_time'] = !empty($val['transfer_time']) ? $val['transfer_time'] : '';
                $data[$key]['refund_no'] = $val['refund_no']."\t";
                $data[$key]['contact_name'] = !empty($val['refund_address']) ? $val['refund_address']['contact_name'] : '';
                $data[$key]['refund_address'] = !empty($val['refund_address']) ? $val['refund_address']['full_address'] : '';
                $data[$key]['express_number'] = !empty($val['delivery']) ? $val['delivery']['express_number'] : '';
                $data[$key]['express_company'] = !empty($val['delivery']) ? $val['delivery']['express_company'] : '';
            }
        }
        return $data;
    }
}