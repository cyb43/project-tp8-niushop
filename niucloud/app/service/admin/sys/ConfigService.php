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

namespace app\service\admin\sys;

use app\service\core\channel\CoreH5Service;
use app\service\core\sys\CoreConfigService;
use app\service\core\sys\CoreSysConfigService;
use core\base\BaseAdminService;

/**
 * 配置服务层
 * Class ConfigService
 * @package app\service\core\sys
 */
class ConfigService extends BaseAdminService
{
    //系统配置文件
    public $core_config_service;

    public function __construct()
    {
        parent::__construct();
        $this->core_config_service = new CoreConfigService();
    }

    /**
     * 获取版权信息(网站整体，不按照站点设置)
     * @return array|mixed
     */
    public function getCopyright()
    {
        return ( new CoreSysConfigService() )->getCopyright();
    }

    /**
     * 设置版权信息(整体设置，不按照站点)
     * @param array $value
     * @return bool
     */
    public function setCopyright(array $value)
    {
        $data = [
            'icp' => $value[ 'icp' ],
            'gov_record' => $value[ 'gov_record' ],
            'gov_url' => $value[ 'gov_url' ],
            'market_supervision_url' => $value[ 'market_supervision_url' ],
            'logo' => $value[ 'logo' ],
            'company_name' => $value[ 'company_name' ],
            'copyright_link' => $value[ 'copyright_link' ],
            'copyright_desc' => $value[ 'copyright_desc' ]
        ];
        return $this->core_config_service->setConfig('COPYRIGHT', $data);
    }

    /**
     * 获取网站信息
     * @return array
     */
    public function getWebSite()
    {
        $info = ( new CoreConfigService() )->getConfig('WEB_SITE_INFO');
        if (empty($info)) {
            $info = [];
            $info[ 'value' ] = [
                'site_name' => config('install.admin_site_name'),
                'logo' => config('install.admin_logo'),
                'desc' => '',
                'latitude' => '',
                'longitude' => '',
                'province_id' => 0,
                'city_id' => 0,
                'district_id' => 0,
                'address' => '',
                'full_address' => '',
                'phone' => '',
                'business_hours' => '',
                'front_end_name' => '',
                'front_end_logo' => '',
                'front_end_icon' => '',
                'icon' => '',
            ];
        }
        return $info[ 'value' ];

    }

    /**
     * 设置网站信息
     * @return bool
     */
    public function setWebSite($data)
    {
        return $this->core_config_service->setConfig('WEB_SITE_INFO', $data);
    }

    /**
     * 获取前端域名
     * @return array|string[]
     */
    public function getSceneDomain()
    {
        return ( new CoreSysConfigService() )->getSceneDomain();
    }

    /**
     * 获取服务信息
     * @return array|mixed
     */
    public function getService()
    {
        $info = ( new CoreConfigService() )->getConfig('SERVICE_INFO')[ 'value' ] ?? [];
        return [
            'wechat_code' => $info[ 'wechat_code' ] ?? '',
            'enterprise_wechat' => $info[ 'enterprise_wechat' ] ?? '',
            'tel' => $info[ 'tel' ] ?? ''
        ];
    }

    /**
     * 设置服务信息
     * @param array $value
     * @return bool
     */
    public function setService(array $value)
    {
        $data = [
            "wechat_code" => $value[ 'wechat_code' ],
            "enterprise_wechat" => $value[ 'enterprise_wechat' ],
            "tel" => $value[ 'tel' ]
        ];

        return $this->core_config_service->setConfig('SERVICE_INFO', $data);
    }

    /**
     * 设置地图key
     * @param array $value
     * @return bool
     */
    public function setMap(array $value)
    {
        $data = [
            'key' => $value[ 'key' ],
            'is_open' => $value[ 'is_open' ], // 是否开启定位
            'valid_time' => $value[ 'valid_time' ] // 定位有效期/分钟，过期后将重新获取定位信息，0为不过期
        ];
        ( new CoreH5Service() )->mapKeyChange($value[ 'key' ]);
        return $this->core_config_service->setConfig('MAPKEY', $data);
    }

    /**
     * 获取地图key
     */
    public function getMap()
    {
        $info = ( new CoreConfigService() )->getConfig('MAPKEY');
        if (empty($info)) {
            $info = [];
            $info[ 'value' ] = [
                'key' => '',
                'is_open' => 1, // 是否开启定位
                'valid_time' => 5 // 定位有效期/分钟，过期后将重新获取定位信息，0为不过期
            ];
        }

        $info[ 'value' ][ 'is_open' ] = $info[ 'value' ][ 'is_open' ] ?? 1;
        $info[ 'value' ][ 'valid_time' ] = $info[ 'value' ][ 'valid_time' ] ?? 5;
        return $info[ 'value' ];
    }

    /**
     * 获取手机端首页列表
     * @param $data
     * @return array
     */
    public function getWapIndexList($data = [])
    {
        return ( new CoreSysConfigService() )->getWapIndexList($data);
    }

    /**
     * 获取开发者key
     * @return array
     */
    public function getDeveloperToken()
    {
        return ( new CoreConfigService() )->getConfigValue("DEVELOPER_TOKEN");
    }

    /**
     * 设置开发者key
     * @param array $data
     * @return \app\model\sys\SysConfig|array|bool|\think\Model
     */
    public function setDeveloperToken(array $data)
    {
        return ( new CoreConfigService() )->setConfig("DEVELOPER_TOKEN", $data);
    }

}
