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

namespace app\service\core\channel;

use app\dict\sys\ConfigKeyDict;
use app\model\sys\SysAttachment;
use app\service\core\addon\WapTrait;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;

/**
 * 素材管理服务层
 * Class CoreAttachmentService
 * @package app\service\core\sys
 */
class CoreAppService extends BaseCoreService
{
    use WapTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysAttachment();
    }

    /**
     * 获取app端配置
     * @return array|mixed
     */
    public function getConfig()
    {
        $info = (new CoreConfigService())->getConfig(ConfigKeyDict::APP)['value'] ?? [];
        return $info;
    }

    public function setConfig($data)
    {
        $info = (new CoreConfigService())->setConfig(ConfigKeyDict::APP, $data);
        $this->appConfigChange($data);
        return $info;
    }

    /**
     * 地图key改变后变更 manifest.json
     * @param string $map_key
     * @return void
     */
    public function appConfigChange($data) {
//        $compile_path = project_path(). 'uni-app' . DIRECTORY_SEPARATOR;
//        $this->mergeManifestJson($compile_path, [
//            "h5" => [
//                "sdkConfigs" => [
//                    "maps" => [
//                        "qqmap" => [
//                            "key" => $map_key
//                        ]
//                    ]
//                ]
//            ]
//        ]);
    }
}
