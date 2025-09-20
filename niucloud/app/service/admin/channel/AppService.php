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

namespace app\service\admin\channel;

use app\dict\sys\ConfigKeyDict;
use app\service\core\channel\CoreAppService;
use app\service\core\channel\CoreH5Service;
use app\service\core\sys\CoreConfigService;
use core\base\BaseAdminService;

/**
 * 配置服务层
 * Class ConfigService
 * @package app\service\core\sys
 */
class AppService extends BaseAdminService
{
    /**
     * 设置app信息
     * @param array $value
     * @return bool
     */
    public function setConfig(array $value)
    {
        return (new CoreAppService())->setConfig($value);
    }

    /**
     * 获取app配置
     * @return mixed
     */
    public function getConfig(){
        return (new CoreAppService())->getConfig();
    }
}
