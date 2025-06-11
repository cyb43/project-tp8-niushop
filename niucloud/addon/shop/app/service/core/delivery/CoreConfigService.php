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

use app\model\sys\SysConfig;
use app\service\core\sys\CoreConfigService as ConfigService;
use core\base\BaseCoreService;
use think\Model;

/**
 * 配送相关配置接口
 */
class CoreConfigService extends BaseCoreService
{
    /**
     * 设置物流查询接口配置
     * @param $data
     * @return SysConfig|bool|Model
     */
    public function setDeliverySearchConfig($data)
    {
        return ( new ConfigService() )->setConfig('DELIVERY_INTERFACE', $data);
    }

    /**
     * 获取快递鸟、快递100配置
     * @return array
     */
    public function getDeliverySearchConfig()
    {
        $info = ( new ConfigService() )->getConfig('DELIVERY_INTERFACE');
        if (empty($info)) {
            $info = [];
            $info[ 'value' ] = [
                'interface_type' => 1,
                'kdniao_id' => '',
                'kdniao_app_key' => '',
                'kdniao_is_pay' => 1,
                'kd100_app_key' => '',
                'kd100_customer' => 0
            ];
        } else {
            $info[ 'value' ][ 'interface_type' ] = intval($info[ 'value' ][ 'interface_type' ]);
        }
        return $info[ 'value' ];
    }
}
