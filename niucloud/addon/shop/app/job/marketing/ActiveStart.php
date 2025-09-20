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

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\model\active\Active;
use addon\shop\app\service\core\marketing\CoreActiveService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 活动自动开启
 */
class ActiveStart extends BaseJob
{
    /**
     * 消费
     * @return true
     */
    public function doJob()
    {
        //Log::write('活动自动开启');
        try {

            $ids = (new Active())->where([
                ['active_status', '=', ActiveDict::NOT_ACTIVE],
                ['start_time', '<=', time()]
            ])->column('active_id');

            foreach($ids as $k => $v){
                (new CoreActiveService())->start($v);
            }

            return true;
        } catch (\Exception $e) {
            Log::write('活动自动开启error'.$e->getMessage().$e->getFile().$e->getLine());
            return false;
        }
    }

}
