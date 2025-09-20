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

namespace addon\shop\app\service\core\delivery;

use addon\shop\app\dict\delivery\DeliveryLocalDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\model\delivery\Local;
use addon\shop\app\service\core\delivery\third_delivery\ThirdDeliveryLoader;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Location\Polygon;

/**
 * 同城配送服务层
 * Class CoreExpressService
 * @package addon\shop\app\service\admin\delivery
 */
class CoreLocalDeliveryService extends BaseCoreService
{
    //系统配置文件
    public $core_config_service;

    public function __construct()
    {
        parent::__construct();
        $this->core_config_service = new CoreConfigService();
    }

    /**
     * 配置设置
     * @param array $params
     * @return array
     */

    public function setConfig( $data)
    {
        $this->core_config_service->setConfig( DeliveryLocalDict::SHOP_THIRD_PARTY_DELIVERY_CONFIG, $data);
        return true;
    }

    public function getConfig()
    {
        $data = $this->core_config_service->getConfigValue( DeliveryLocalDict::SHOP_THIRD_PARTY_DELIVERY_CONFIG);
        $config = DeliveryLocalDict::getType();
        foreach ($config as $type => $item) {
            if (!isset($data[$type])) {
                $temp = [];
                foreach ($item['column'] as $column) {
                    $temp[$column] = '';
                }
                $data[$type] = $temp;
            }
        }
        return $data;
    }

    /**
     * 配送费用计算
     * @param $order
     * @return void
     */
    public static function calculate(&$order)
    {
        $address = $order->delivery['take_address'] ?? [];

        if (empty($address)) {
            $order->error[] = get_lang('NOT_SELECT_ADDRESS');
            return;
        }

        $goods_money = 0; // 商品价格
        $goods_weight = 0; // 商品重量

        foreach ($order->goods_data as $k => &$v) {
            $goods_type = $v['goods']['goods_type'];
            if ($goods_type == GoodsDict::REAL) {
                if (in_array(OrderDeliveryDict::LOCAL_DELIVERY, $v['goods']['delivery_type'])) {
                    $goods_money += $v['goods_money'];
                    $goods_weight += $v['weight'] * $v['num'];
                } else {
                    $v['not_support_delivery'] = 1;
                    $order->error[] = get_lang('NOT_SUPPORT_DELIVERY_TYPE');
                }
            }
        }
        (new self())->feeCalculate($order, $goods_money, $goods_weight);
    }

    /**
     * 运费计算
     * @param $order
     * @param float $goods_money
     * @param float $goods_weight
     * @return void
     */
    public function feeCalculate(&$order, float $goods_money, float $goods_weight)
    {
        $address = $order->delivery['take_address'] ?? [];
        $local = (new Local())->where([['local_id', '>', 0]])->field('fee_type,base_dist,base_price,grad_dist,grad_price,weight_start,weight_unit,weight_price,delivery_type,area,center')->findOrEmpty();
        if ($local->isEmpty()) {
            $order->error[] = get_lang('NOT_CONFIGURED_LOCAL_DELIVERY');
            return;
        }
        if (empty($address['lat']) || empty($address['lng'])) {
            $order->error[] = get_lang('NOT_SUPPORT_DELIVERY_ADDRESS');
            return;
        }
        // 收货地址
        $address_point = new Coordinate($address['lat'], $address['lng']);
        // 取货地址
        $local_address_point = new Coordinate($local['center']['lat'], $local['center']['lng']);
        $local = $local->toArray();
//        $local['delivery_type'][0] = 'third'; //TODO调试使用   后续删除
        // 判断所在区域
        $located_in_area = null;
        foreach ($local['area'] as $area) {
            if ($area['area_type'] == 'radius') {
                $center = new Coordinate($area['area_json']['center']['lat'], $area['area_json']['center']['lng']);
                $distance = (new Vincenty())->getDistance($address_point, $center);
                if ($distance <= $area['area_json']['radius']) {
                    $located_in_area = $area;
                    break;
                }
            } else {
                $geofence = new Polygon();
                $geofence->addPoints(array_map(function ($latlng) {
                    return new Coordinate($latlng['lat'], $latlng['lng']);
                }, $area['area_json']['paths']));
                if ($geofence->contains($address_point)) {
                    $located_in_area = $area;
                    break;
                }
            }
        }
        if (!$located_in_area) {
            $order->error[] = get_lang('NOT_SUPPORT_DELIVERY_ADDRESS');
            return;
        }

        if (bccomp($goods_money, $located_in_area['start_price'], 2) == -1) {
            $order->error[] = '差' . $located_in_area['start_price'] - $goods_money . '元起送';
            return;
        }
        if ($local['delivery_type'][0] == DeliveryLocalDict::DELIVERY_TYPE_THIRD) {
            //TODO::计算三方配送费用
            $config = $this->getConfig();
            $third_type = $config['default'];
            $loader = new ThirdDeliveryLoader($third_type, $config[$third_type]);
            $res = $loader->calculate($order);
            // 按配送区域收费
            $order->basic['delivery_money'] = $res['delivery_price'];
            $order->basic['delivery_third'] = [
                'name' => DeliveryLocalDict::getType($third_type)['name'],
                'type' => $third_type,
                'third_order_no' => $res['third_order_no'],
            ];
        } else {

            if ($local['fee_type'] == 'distance') {
                // 按距离收费
                // 计算收货地址与取货地址的距离
                $distance = round((new Vincenty())->getDistance($address_point, $local_address_point) / 1000, 2);
                $order->basic['delivery_money'] = $local['base_price'];
                if ($distance > $local['base_dist']) {
                    $order->basic['delivery_money'] += round(ceil(($distance - $local['base_dist']) / $local['grad_dist']) * $local['grad_price'], 2);
                }
            } else {
                // 按配送区域收费
                $order->basic['delivery_money'] = $located_in_area['delivery_price'];
            }

            // 计算续重费用
            if ($goods_weight > 0 && $goods_weight > $local['weight_start'] && $local['weight_unit'] > 0 && $local['weight_price'] > 0) {
                $order->basic['delivery_money'] += round(ceil(($goods_weight - $local['weight_start']) / $local['weight_unit']) * $local['weight_price'], 2);
            }
        }
    }

    public function thirdDeliveryCallback(array $data)
    {
        $config = $this->getConfig();
        $loader = new ThirdDeliveryLoader($data['type'], $config[$data['type']]);
        $loader->callback($data);
    }
}
