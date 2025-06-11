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

namespace app\job\sys;

use app\model\sys\SysUserLog;
use core\base\BaseJob;


class ClearUserLog extends BaseJob
{
    public function doJob()
    {
        (new SysUserLog())->where('create_time','<',time()-7*24*60*60)->delete();
        return true;
    }
}
