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
use think\facade\Log;

/**
 * 订单配送费用
 */
class OrderDeliveryServiceListener
{

    public function handle($data)
    {
        if ($data['source'] == 'shop') {
            Log::write('订单配送费用参数：'.json_encode($data));
            if(empty($data['source_sku_id']) || empty($data['num'])){
                return [
                    'code' => 0,
                    'msg'  => '商品参数不完整'
                ];
            }
            if(empty($data['member_id'])){
                return [
                    'code' => 0,
                    'msg'  => '会员ID为空'
                ];
            }
            //必要参数,不传地址将获取用户默认地址
            $data = [
                'member_id'=>$data['member_id'],
                'province_id'=>$data['province_id'] ?? '',
                'city_id' => $data['city_id'] ?? '',
                'district_id'=>$data['district_id'] ?? '',
                'source_sku_id'=>$data['source_sku_id'],//商品skuID
                'num'=>$data['num'] //购买数量
            ];
            $order_model = new CoreOrderDeliveryService();
            //计算运费
            $delivery_money = $order_model->calculate($data);
            return [
                'code' => 1,
                'delivery_money'=>$delivery_money
            ];
        }
        return [];
    }
}