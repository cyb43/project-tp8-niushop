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

use core\loader\Storage;

/**
 * Class BaseDelivery
 * @package
 */
abstract class BaseThirdDelivery extends Storage
{
    /**
     * 初始化
     * @param array $config
     * @return void
     */
    protected function initialize(array $config = []){

    }
    abstract function createOrder($data);
    abstract function calculate($order_data);
    abstract function createOrderAfterCalculate($delivery_third_order_no);
    abstract function queryOrderInfo($data);
    abstract function closeOrder($data);
    abstract function callback($data);
}