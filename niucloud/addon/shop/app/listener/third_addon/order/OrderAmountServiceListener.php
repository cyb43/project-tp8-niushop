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

namespace addon\shop\app\listener\third_addon\order;

use addon\shop\app\service\core\third_addon\order\CoreOrderDeliveryService;
use core\exception\CommonException;
use think\facade\Log;

/**
 * 订单配送费用
 */
class OrderAmountServiceListener
{
    private $return = [];

    public function handle($data)
    {
        try {
            if ($data['source'] == 'shop') {
//                return [
//                    [
//                        'name' => '配送费',
//                        'key' => 'delivery_money',
//                        'amount' => 20
//                    ],
//                    [
//                        'name' => '服务费',
//                        'key' => 'service_money',
//                        'amount' => 10
//                    ]
//                ];
                $this->getDeliveryMoney($data);
                if (!empty($this->return)) {
                    return $this->return;
                }
            }
        } catch (CommonException $e) {
            throw new CommonException($e->getMessage());
        }
    }

    private function getDeliveryMoney($data)
    {
        //必要参数,不传地址将获取用户默认地址
        $data = [
            'member_id' => $data['member_id'],
            'province_id' => $data['address_info']['province_id'] ?? '',
            'city_id' => $data['address_info']['city_id'] ?? '',
            'district_id' => $data['address_info']['district_id'] ?? '',
            'source_sku_id' => $data['source_sku_id'],//商品skuID
            'num' => $data['num'], //购买数量
        ];
        $order_model = new CoreOrderDeliveryService();
        //计算运费
        $delivery_money = $order_model->calculate($data);
        if ($delivery_money > 0) {
            $this->return[] =  [
                'name' => '配送费',
                'key' => 'delivery_money',
                'amount' => $delivery_money
            ];
        }
    }
}