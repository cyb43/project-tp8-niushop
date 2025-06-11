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

/**
 * 订单退款售后导出数据类型查询
 * Class MemberExportTypeListener
 * @package app\listener\member
 */
class ShopOrderRefundExportTypeListener
{
    public function handle()
    {
        return [
            'shop_order_refund' => [
                'name' => '退款售后',
                'column' => [
                    'order_refund_no' => [ 'name' => '退款单号'],
                    'nickname' => [ 'name' => '会员昵称'],
                    'refund_type_name' => [ 'name' => '退款方式'],
                    'apply_money' => [ 'name' => '申请退款金额'],
                    'goods_info' => [ 'name' => '退货商品信息'],
                    'refund_num' => [ 'name' => '退货数量'],
                    'money' => [ 'name' => '实际退款金额'],
                    'status_name' => [ 'name' => '退款状态'],
                    'create_time' => [ 'name' => '申请时间'],
                    'transfer_time' => [ 'name' => '转账时间'],
                    'refund_no' => [ 'name' => '退款交易号'],
                    'reason' => [ 'name' => '退款原因'],
                    'shop_reason' => [ 'name' => '拒绝原因'],
                    'contact_name' => [ 'name' => '退货联系人'],
                    'refund_address' => [ 'name' => '退货地址'],
                    'express_number' => [ 'name' => '退货物流单号'],
                    'express_company' => [ 'name' => '退货物流公司'],
                ]
            ]
        ];
    }
}