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

namespace addon\shop\app\listener\pay;


use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\service\core\refund\CoreRefundService;

/**
 * 退款异步回调事件
 */
class RefundSuccessListener
{
    public function handle(array $params)
    {
        if ($params[ 'trade_type' ] == OrderDict::TYPE || (!empty($params[ 'refund_trade_type' ]) && $params[ 'refund_trade_type' ] == OrderDict::TYPE)) {

            ( new CoreRefundService() )->transferSuccess([
                'refund_no' => $params[ 'refund_no' ],
                'order_refund_no' => $params[ 'trade_id' ],
            ]);
            return true;
        }
    }
}