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

namespace addon\shop\app\listener\verify;

/**
 * 核销类型注册
 */
class VerifyTypeListener
{
    /**
     * @param array $params
     * @return array|void
     */
    public function handle($params = [])
    {
        return [
            'shopVirtualGoods' => [
                'name' => '商城虚拟商品'
            ],
            'shopPickUpOrder' => [
                'name' => '商城自提订单'
            ],
        ];
    }
}
