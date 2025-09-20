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

namespace addon\shop\app\listener\third_addon\order;

use addon\shop\app\dict\order\OrderRefundDict;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderRefund;
use addon\shop\app\service\core\third_addon\order\CoreCreateOrderService;
use think\facade\Log;

/**
 * 创建订单
 */
class OrderServicePathListener
{
    /**
     * 订单详情
     * @var string
     */
    private $admin_detail_path = "/admin/shop/order/detail?order_id=%d";
    private $detail_path = "/addon/shop/pages/order/detail?order_id=%d";

    /**
     * 退款地址
     * @var string[]
     */
    private $refund_apply_path = "/addon/shop/pages/refund/apply?order_goods_id=%s&order_id=%s";

    private $refund_detail_path = "/addon/shop/pages/refund/detail?order_refund_no=%s";

    public function handle($data)
    {
        if ($data['source'] == 'shop') {
            $order_id = $data['source_id'] ?? 0;
            $order_goods_id = $data['source_order_item_id'] ?? 0;
            if (empty($order_id) || $order_id == 0) { //未提供订单ID 获取全都地址
                return null;
            }
            $refund_info = (new OrderRefund())->whereNotIn('status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND])->where('order_id', $order_id)->findOrEmpty();
            if ($refund_info->isEmpty()) {
                $refund_path = sprintf($this->refund_apply_path, $order_goods_id, $order_id);
            } else {
                $refund_path = sprintf($this->refund_detail_path, $refund_info['order_refund_no']);
            }
            $order_info = (new Order())->findOrEmpty($order_id)->toArray();
            if ($order_info['is_enable_refund'] == 0){//特殊处理  商城的订单已完成就不允许售后了（配置项），拼团是允许的则  商城不允许退款之后就直接返回空地址，这样拼团就不展示售后按钮了
                $refund_path = '';
            }

            $detail_path = sprintf($this->detail_path, $order_id);
            $admin_detail_path = sprintf($this->admin_detail_path, $order_id);
            return [
                'refund' => ['name' => '商城-退款相关地址', 'url' => $refund_path],
                'detail' => ['name' => '查看关联订单', 'url' => $detail_path],
                'admin_detail' => ['name' => '查看关联订单', 'url' => $admin_detail_path],
            ];
        }
    }
}