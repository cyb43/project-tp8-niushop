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

use app\dict\sys\DateDict;
use core\base\BaseAdminController;
use think\Response;

/**
 * 公共数据字典
 * Class Common
 * @description 公共数据字典
 * @package app\adminapi\controller\sys
 */
class Common extends BaseAdminController
{
    /**
     * 月份
     * @description 月份
     * @return Response
     */
    public function getMonth()
    {
        return success((new DateDict())->getMonth());
    }

    /**
     * 星期
     * @description 星期
     * @return Response
     */
    public function getWeek()
    {
        return success((new DateDict())->getWeek());
    }

}
