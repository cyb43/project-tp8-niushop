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

namespace addon\shop\app\service\admin\refund;

use addon\shop\app\dict\order\OrderRefundDict;
use addon\shop\app\dict\order\OrderRefundLogDict;
use addon\shop\app\model\order\OrderRefund;
use addon\shop\app\service\core\refund\CoreRefundActionService;
use addon\shop\app\service\core\refund\CoreRefundService;
use core\base\BaseAdminService;

/**
 *  退款操作服务层
 */
class RefundActionService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderRefund();
    }

    /**
     * 审核退款申请
     * @param $data ['is-agree]
     * @return void
     */
    public function auditApply($data)
    {
        $data[ 'main_type' ] = OrderRefundLogDict::STORE;
        $data[ 'main_id' ] = $this->uid;
        (new CoreRefundActionService())->auditApply($data);
        return true;
    }

    /**
     * 审核是否确认收货
     * @param $data
     * @return true
     */
    public function auditRefundGoods($data)
    {
        $data[ 'main_type' ] = OrderRefundLogDict::STORE;
        $data[ 'main_id' ] = $this->uid;
        (new CoreRefundActionService())->auditRefundGoods($data);
        return true;
    }

    /**
     * 商家主动退款
     * @param $data
     * @return true
     */
    public function shopActiveRefund($data)
    {
        $data[ 'main_type' ] = OrderRefundLogDict::STORE;
        $data[ 'main_id' ] = $this->uid;
        (new CoreRefundActionService())->shopActiveRefund($data);
        return true;
    }

    /**
     * 获取订单可退款金额
     * @param $data
     * @return array
     */
    public function getOrderRefundMoney($data)
    {
        $order_goods_ids = $data[ 'order_goods_ids' ];
        $refund_money_array = (new CoreRefundService())->getOrderRefundMoney($order_goods_ids);
        return [
            'refund_money' => round($refund_money_array[ 'refund_money' ] ?? 0, 2)
        ];
    }

    /**
     * 商家主动关闭售后
     * @param $order_refund_no
     * @return void
     */
    public function closeRefund($order_refund_no)
    {
        $data[ 'main_type' ] = OrderRefundLogDict::STORE;
        $data[ 'main_id' ] = $this->uid;
        $data[ 'order_refund_no' ] =$order_refund_no;
        (new CoreRefundActionService())->close($data,OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND);
    }

}
