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

namespace addon\shop\app\dict\delivery;


/**
 *同城三方配送相关枚举类
 */
class DeliveryLocalDict
{
    const DELIVERY_TYPE_BUSINESS = 'business';//商家自配送
    const DELIVERY_TYPE_THIRD = 'third';//三方配送
    const DADA = 'dada';
    const DADA2 = 'dada2';


    /**
     * 获取三方配送提供商(用于订单)
     * @param string $type
     * @return array|array[]|string
     */
    public static function getType(string $type = '')
    {
        $data = [
            self::DADA => [
                'name' => '达达配送',
                'column' => ['app_secret', 'app_key', 'shop_id', 'shop_store_no'],
                'encrypt_params' => ['app_secret', 'app_key', 'shop_id', 'shop_store_no']
            ],
        ];

        if ($type == '') {
            return $data;
        }
        return $data[$type] ?? '';
    }
}
