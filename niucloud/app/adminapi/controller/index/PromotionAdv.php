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

namespace app\adminapi\controller\index;

use app\service\core\index\CorePromotionAdvService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 推广广告控制器
 * Class PromotionAdv
 * @package app\adminapi\controller\sys
 */
class PromotionAdv extends BaseAdminController
{
    /**
     * 广告列表
     * @return Response
     */
    public function getIndexAdvList()
    {
        $res = (new CorePromotionAdvService())->getIndexAdvList();
        return success('SUCCESS',$res);
    }


}
