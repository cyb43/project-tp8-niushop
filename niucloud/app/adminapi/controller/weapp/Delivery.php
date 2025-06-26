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

namespace app\adminapi\controller\weapp;

use app\service\admin\weapp\WeappDeliveryService;
use core\base\BaseAdminController;

/**
 * 小程序发货信息管理服务
 * @description 小程序发货信息管理服务
 */
class Delivery extends BaseAdminController
{

    /**
     * 查询小程序是否已开通发货信息管理服务
     * @description 查询小程序是否已开通发货信息管理服务
     * @return bool
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getIsTradeManaged()
    {
        try {
            $wechat_template_service = new WeappDeliveryService();
            $result = $wechat_template_service->getIsTradeManaged();
            if ($result) {
                return success([ 'is_trade_managed' => true ]);
            }

        } catch (\Exception $e) {
        }
        return success([ 'is_trade_managed' => false ]);
    }

}
