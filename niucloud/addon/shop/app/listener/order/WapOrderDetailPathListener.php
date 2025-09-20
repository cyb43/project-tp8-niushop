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

namespace addon\shop\app\listener\order;

use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\service\core\order\CoreOrderService;

/**
 * 通过支付信息获取手机端订单详情路径
 * Class WapOrderDetailPathListener
 * @package addon\shop\app\listener\order
 */
class WapOrderDetailPathListener
{

    public function handle($params)
    {
        $trade_type = $params[ 'trade_type' ] ?? '';
        if (in_array($trade_type, [ OrderDict::TYPE ])) {
            $order_info = ( new CoreOrderService() )->getInfo($params[ 'trade_id' ]);
            if (empty($order_info)) return 'addon/shop/pages/order/list';
            return 'addon/shop/pages/order/detail?order_id=' . $order_info[ 'order_id' ];
        }
    }
}
