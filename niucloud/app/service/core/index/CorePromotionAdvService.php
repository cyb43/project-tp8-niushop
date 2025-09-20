<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud-admin.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\service\core\index;

use app\service\core\addon\CoreAddonBaseService;
use app\service\core\niucloud\CoreModuleService;
use think\Response;

/**
 * 推广广告控制器
 * Class PromotionAdv
 * @package app\adminapi\controller\sys
 */
class CorePromotionAdvService extends CoreAddonBaseService
{
    /**
     * 广告列表
     * @return Response
     */
    public function getIndexAdvList()
    {
        return (new CoreModuleService())->getIndexAdvList()['data'] ?? [];
    }


}
