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

use core\base\BaseCoreService;

/**
 *  三方插件订单通知服务层
 */
class CoreOrderNoticeService extends BaseCoreService
{

    /**
     * 发送给插件 - 商城订单退款已发起通知
     * @param $refund_data
     * @param $relate_source
     * @param $relate_order_id
     * @return void
     */
    public function sendRefundApplyNotice($refund_data, $relate_source, $relate_order_id)
    {
        event('ThirdOrderRefundApplyNotice', [
            'source' => 'shop',
            'source_order_id' => $refund_data['order_id'],
            'relate_source' => $relate_source,
            'relate_order_id' => $relate_order_id,
            'refund_type' => $refund_data[ 'refund_type' ],
            'refund_reason' => $refund_data[ 'reason' ],
            'remark' => $refund_data[ 'remark' ],
            'refund_voucher' => $refund_data[ 'voucher' ],
            'order_refund_no'=>$refund_data['order_refund_no'],
        ]);
    }

    /**
     * 发送给插件 - 商城订单退款关闭通知
     * @param $refund_data
     * @param $relate_source
     * @param $relate_order_id
     * @return void
     */
    public function sendRefundCloseNotice($refund_data, $relate_source, $relate_order_id)
    {
        event('ThirdOrderRefundCloseNotice', [
            'source' => 'shop',
            'source_order_id' => $refund_data['order_id'],
            'relate_source' => $relate_source,
            'relate_order_id' => $relate_order_id,
            'reason' => $refund_data['reason'],
            'refund_no' => $refund_data['refund_no'],
        ]);
    }
    /**
     * 发送给插件 - 商城订单退款完成通知
     * @param $refund_data
     * @param $relate_source
     * @param $relate_order_id
     * @return void
     */
    public function sendRefundFinishNotice($refund_data, $relate_source, $relate_order_id)
    {
        event('ThirdOrderRefundFinishNotice', [
            'source' => 'shop',
            'source_order_id' => $refund_data['order_id'],
            'relate_source' => $relate_source,
            'relate_order_id' => $relate_order_id,
            'reason' => $refund_data['reason'],
            'refund_type' => $refund_data['refund_type'],
            'apply_time' => $refund_data['create_time'],
            'apply_money' => $refund_data['apply_money'],
            'money' => $refund_data['money'],
            'refund_no' => $refund_data['refund_no'],
            'order_refund_no'=>$refund_data['order_refund_no'],
        ]);
    }


}