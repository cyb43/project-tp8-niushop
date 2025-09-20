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

use addon\shop\app\dict\active\ManjianDict;
use addon\shop\app\model\manjian\Manjian;
use addon\shop\app\model\manjian\ManjianGoods;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 活动自动开启
 */
class ManjianStart extends BaseJob
{
    /**
     * 满减送开启
     * @return true
     */
    public function doJob()
    {
        //Log::write('满减送自动开启');
        try {

            $ids = (new Manjian())->where([
                ['status', '=', ManjianDict::NOT_ACTIVE],
                ['start_time', '<=', time()]
            ])->column('manjian_id');
            (new Manjian())->where([['manjian_id', 'in', $ids], ['status', '=', ManjianDict::NOT_ACTIVE], ['start_time', '<=', time()]])->update(['status' => ManjianDict::ACTIVE]);
            (new ManjianGoods())->where([['manjian_id', 'in', $ids]])->update(['status' => ManjianDict::ACTIVE]);
            return true;
        } catch (\Exception $e) {
            Log::write('满减送自动开启error' . $e->getMessage() . $e->getFile() . $e->getLine());
            return false;
        }
    }

}
