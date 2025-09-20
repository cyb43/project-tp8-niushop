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

namespace addon\shop\app\service\core\delivery\delivery_search;

use addon\shop\app\service\core\delivery\CoreConfigService;
use core\exception\AdminException;
use core\loader\Loader;

/**
 * @see DeliverySearchLoader
 * @package think\facade
 * @mixin BaseDeliverySearch
 * @method  string|null search(array $data) 物流跟踪查询
 */
class DeliverySearchLoader extends Loader
{
    public array $method = [
        1 => 'KdniaoDeliverySearch',
        2 => 'Kd100DeliverySearch',
    ];

    public function __construct()
    {
        $config = ( new CoreConfigService() )->getDeliverySearchConfig();
        if(empty($config['interface_type']) || !isset($this->method[$config['interface_type']]) ){
            throw new AdminException('NOT_CONFIGURED_DELIVERY_TYPE');
        }
        parent::__construct($this->method[$config['interface_type']], $config);
    }
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\addon\\shop\\app\\service\\core\\delivery\\delivery_search\\';

    protected $config_name = 'delivery_search';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefault()
    {
        return 'kdbird';
    }
}