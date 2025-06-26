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

namespace addon\shop\app\service\admin\delivery;

use addon\shop\app\dict\delivery\DeliveryLocalDict;
use addon\shop\app\model\delivery\Local;
use addon\shop\app\service\core\delivery\CoreLocalDeliveryService;
use app\dict\common\CommonDict;
use core\base\BaseAdminService;

/**
 * 同城配送服务层
 * Class LocalService
 * @package addon\shop\app\service\admin\delivery
 */
class LocalService extends BaseAdminService
{
    public $core_service;

    public function __construct()
    {
        parent::__construct();
        $this->core_service = new CoreLocalDeliveryService();

    }

    /**
     * 设置同城配送
     * @param array $data
     * @return mixed
     */
    public function setLocal(array $data)
    {
        (new Local())->where([['local_id', '>', 0]])->delete();

        $create_res = (new Local())->create([
            'center' => $data['center'],
            'fee_type' => $data['fee_type'],
            'base_dist' => $data['base_dist'],
            'base_price' => $data['base_price'],
            'grad_dist' => $data['grad_dist'],
            'grad_price' => $data['grad_price'],
            'weight_start' => $data['weight_start'],
            'weight_unit' => $data['weight_unit'],
            'weight_price' => $data['weight_price'],
            'delivery_type' => $data['delivery_type'],
            'area' => $data['area'],

            'time_is_open' => $data['time_is_open'],
            'time_type' => $data['time_type'],
            'time_week' => $data['time_week'],
            'time_interval' => $data['time_interval'],
            'advance_day' => $data['advance_day'],
            'most_day' => $data['most_day'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'delivery_time' => $data['delivery_time'],
        ]);
        if (in_array(DeliveryLocalDict::DELIVERY_TYPE_THIRD, $data['delivery_type'])) {
            $this->setThirdPartyConfig($data['third_party_config']);
        }
        return $create_res->local_id;
    }

    /**
     * 获取同城配送设置
     * @return array
     */
    public function getLocal()
    {
        $info = (new Local())->where([['local_id', '>', 0]])->findOrEmpty()->toArray();
        $config = $this->getThirdPartyConfig();
        $type_config = DeliveryLocalDict::getType();
        foreach ($type_config as $type => $value) {
            foreach ($value['encrypt_params'] as $encrypt_param) {
                $config[$type][$encrypt_param] = !empty( $config[$type][$encrypt_param] ) ? CommonDict::ENCRYPT_STR : '';
            }
        }
        $info['third_party_config'] = $config;
        return $info;
    }

    public function getThirdTypeConfig()
    {
        $config = DeliveryLocalDict::getType();
        $return = [];
        foreach ($config as $type => $item) {
            $return[] = [
                'type' => $type,
                'name' => $item['name']
            ];
        }
        return $return;
    }

    public function getThirdPartyConfig()
    {
        return $this->core_service->getConfig();
    }

    public function setThirdPartyConfig($data)
    {
        if (empty($data)) {
            return true;
        }
        $config = $this->getThirdPartyConfig();
        $config['default'] = $data['default'] ?? $config['default'];
        unset($data['default']);
        foreach ($data as $type => $item) {
            foreach ($item as $column => $v) {
                if (empty($v) || $v == CommonDict::ENCRYPT_STR) {
                    $config[$type][$column] = $v ?? $config[$type][$column];
                } else {
                    $config[$type][$column] = $v;
                }
            }
        }
        return $this->core_service->setConfig($config);
    }

    public function thirdDeliveryCallback($params)
    {
        $this->core_service->thirdDeliveryCallback($params);
    }
}
