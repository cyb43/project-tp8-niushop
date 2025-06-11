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

namespace addon\shop\app\service\api\order;

use addon\shop\app\service\core\order\CoreOrderConfigService;
use core\base\BaseApiService;


/**
 * 订单设置服务层
 * Class ConfigService
 * @package adaddon\shop\app\service\admin\order
 */
class ConfigService extends BaseApiService
{

    public $order_config_service;

    public function __construct()
    {
        parent::__construct();
        $this->order_config_service = new CoreOrderConfigService();
    }

    /**
     * 获取交易设置
     * @return array
     */
    public function getConfig()
    {
        return $this->order_config_service->getConfig();
    }

}
