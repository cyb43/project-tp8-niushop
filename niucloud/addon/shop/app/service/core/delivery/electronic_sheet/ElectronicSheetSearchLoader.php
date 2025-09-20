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

namespace addon\shop\app\service\core\delivery\electronic_sheet;

use addon\shop\app\service\core\delivery\CoreConfigService;
use core\exception\AdminException;
use core\loader\Loader;

/**
 * Class ElectronicSheetSearchLoader
 * @package addon\shop\app\service\core\delivery\electronic_sheet
 * @method  string|null electronicSheet(array $data) 电子面单
 */
class ElectronicSheetSearchLoader extends Loader
{

    public array $method = [
        'kdbird' => 'KdniaoSearch',
   //需线上测试,先隐藏     'kd100' => 'Kd100Search',
    ];

    public function __construct()
    {
        $config = ( new CoreConfigService() )->getDeliveryElectronSheeticConfig();
        if(empty($config['interface_type']) || !isset($this->method[$config['interface_type']]) ){
            throw new AdminException('NOT_CONFIGURED_DELIVERY_TYPE');
        }
        parent::__construct($this->method[$config['interface_type']], $config);
    }

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\addon\\shop\\app\\service\\core\\delivery\\electronic_sheet\\';

    protected $config_name = 'electronic_sheet';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefault()
    {
        return 'kdbird';
    }
}