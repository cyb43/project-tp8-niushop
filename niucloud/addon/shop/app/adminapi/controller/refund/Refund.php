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

namespace addon\shop\app\adminapi\controller\refund;

use addon\shop\app\service\admin\refund\RefundActionService;
use addon\shop\app\service\admin\refund\RefundService;
use core\base\BaseAdminController;
use think\Response;

class Refund extends BaseAdminController
{
    /**
     * 列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ 'order_refund_no', '' ],
            [ 'status', '' ],
            [ 'create_time', [] ],
        ]);
        return success(( new RefundService() )->getPage($data));
    }

    /**
     * 详情
     * @param string $order_refund_no
     * @return Response
     */
    public function detail($id)
    {
        return success(( new RefundService() )->getDetail($id));
    }

    /**
     * 审核退款
     * @return Response
     */
    public function auditApply($order_refund_no)
    {
        $data = $this->request->params([
            [ 'shop_reason', '' ],
            [ 'is_agree', '' ],
            [ 'money', 0 ],
            [ 'refund_address_id', 0 ]
        ]);
        $data[ 'order_refund_no' ] = $order_refund_no;
        ( new RefundActionService() )->auditApply($data);
        return success();
    }

    /**
     * 确认收货
     * @return Response
     */
    public function auditRefundGoods($order_refund_no)
    {
        $data = $this->request->params([
            [ 'shop_reason', '' ],
            [ 'is_agree', '' ]
        ]);
        $data[ 'order_refund_no' ] = $order_refund_no;
        ( new RefundActionService() )->auditRefundGoods($data);
        return success();
    }

    /**
     * 商家主动退款
     * @return Response
     */
    public function shopActiveRefund()
    {
        $data = $this->request->params([
            [ 'order_goods_ids', [] ],
            [ 'shop_active_refund_money', 0 ],
            [ 'shop_active_refund_remark', '' ],
        ]);
        ( new RefundActionService() )->shopActiveRefund($data);
        return success();
    }

    /**
     * 获取订单项可退款金额
     * @return Response
     */
    public function getOrderRefundMoney()
    {
        $data = $this->request->params([
            [ 'order_goods_ids', [] ]
        ]);
        return success(data: ( new RefundActionService() )->getOrderRefundMoney($data));
    }

    /**
     * 关闭售后
     * @return void
     */
    public function closeRefund($order_refund_no)
    {
        (new RefundActionService())->closeRefund($order_refund_no);
        return success();
    }


}
