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

namespace app\adminapi\controller\sys;

use app\service\admin\sys\ConfigService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 配置管理
 * Class Config
 * @description 配置管理
 * @package app\adminapi\controller\sys
 */
class Config extends BaseAdminController
{
    /**
     * 获取网站设置
     * @description 获取网站设置
     * @return Response
     */
    public function getWebsite()
    {
        return success(( new ConfigService() )->getWebSite());
    }

    /**
     * 网站设置
     * @description 网站设置
     * @return Response
     */
    public function setWebsite()
    {
        $data = $this->request->params([
            [ "site_name", "" ],
            [ "logo", "" ],
            [ "keywords", "" ],
            [ "desc", "" ],
            [ "latitude", "" ],
            [ "longitude", "" ],
            [ "province_id", 0 ],
            [ "city_id", 0 ],
            [ "district_id", 0 ],
            [ "address", "" ],
            [ "full_address", "" ],
            [ "phone", "" ],
            [ "business_hours", "" ],
            [ "front_end_name", "" ],
            [ "front_end_logo", "" ],
            [ "front_end_icon", "" ],
            [ "icon", "" ],
            [ "meta_title", "" ],
            [ "meta_desc", "" ],
            [ "meta_keyword", "" ],
        ]);
        ( new ConfigService() )->setWebSite($data);

        $service_data = $this->request->params([
            [ "wechat_code", "" ],
            [ "enterprise_wechat", "" ],
            [ "tel", "" ],
        ]);
        ( new ConfigService() )->setService($service_data);

        return success();
    }

    /**
     * 获取版权信息
     * @description 获取版权信息
     * @return Response
     */
    public function getCopyright()
    {
        return success(( new ConfigService() )->getCopyright());
    }

    /**
     * 设置版权信息
     * @description 设置版权信息
     * @return Response
     */
    public function setCopyright()
    {
        $data = $this->request->params([
            [ 'icp', '' ],
            [ 'gov_record', '' ],
            [ 'gov_url', '' ],
            [ 'market_supervision_url', '' ],
            [ 'logo', '' ],
            [ 'company_name', '' ],
            [ 'copyright_link', '' ],
            [ 'copyright_desc', '' ],
        ]);
        ( new ConfigService() )->setCopyright($data);
        return success();
    }

    /**
     * 场景域名
     * @description 场景域名
     * @return Response
     */
    public function getSceneDomain()
    {
        return success(( new ConfigService() )->getSceneDomain());
    }

    /**
     * 获取服务信息
     * @description 获取服务信息
     * @return Response
     */
    public function getServiceInfo()
    {
        return success(( new ConfigService() )->getService());
    }

    /**
     * 设置地图信息
     * @description 设置地图信息
     * @return Response
     */
    public function setMap()
    {
        $data = $this->request->params([
            [ 'key', '' ],
            [ 'is_open', 0 ], // 是否开启定位
            [ 'valid_time', 0 ] // 定位有效期/分钟，过期后将重新获取定位信息，0为不过期
        ]);
        ( new ConfigService() )->setMap($data);
        return success();
    }

    /**
     * 获取地图设置
     * @description 获取地图设置
     * @return Response
     */
    public function getMap()
    {
        return success(( new ConfigService() )->getMap());
    }

    /**
     * 获取手机端首页列表
     */
    public function getWapIndexList()
    {
        $data = $this->request->params([
            [ 'title', '' ],
            [ 'key', '' ] // 多个查询，逗号隔开
        ]);
        return success(( new ConfigService() )->getWapIndexList($data));
    }

    /**
     * 获取开发者key
     * @description 获取开发者key
     * @return Response
     */
    public function getDeveloperToken()
    {
        return success(data: ( new ConfigService() )->getDeveloperToken());
    }

    /**
     * 设置开发者key
     * @description 设置开发者key
     * @return Response
     */
    public function setDeveloperToken()
    {
        $data = $this->request->params([
            [ 'token', '' ],
        ]);
        ( new ConfigService() )->setDeveloperToken($data);
        return success();
    }

    /**
     * 获取install.php配置
     * @return Response
     */
    public function getInstallConfig()
    {
        return success(config('install'));
    }

}
