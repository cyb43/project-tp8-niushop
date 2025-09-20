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
namespace addon\shop\app\job\marketing;

use addon\shop\app\service\core\marketing\CoreNewcomerService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 新人活动修改后操作
 */
class NewcomerSaveAfter extends BaseJob
{
    /**
     * 消费
     * @return bool
     */
    public function doJob()
    {
        //Log::write('新人专享活动修改后更新会员活动有效期');
        try {
            //修改状态
            (new CoreNewcomerService())->afterSave();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
