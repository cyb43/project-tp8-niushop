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

namespace addon\shop\app\service\core\delivery\third_delivery;

use core\loader\Loader;


/**
 *
 */
class ThirdDeliveryLoader extends Loader
{


    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\addon\\shop\\app\\service\\core\\delivery\\third_delivery\\';

    protected $config_name = 'third_delivery';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefault()
    {
        return config('third_delivery.default');
    }


}