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
 * 活动自动关闭
 */
class ManjianEnd extends BaseJob
{
    /**
     * 满减送关闭
     * @return true
     */
    public function doJob()
    {
        Log::write('满减送自动关闭');
        try {

            $ids = (new Manjian())->where([
                ['status', '=', ManjianDict::ACTIVE],
            ])->whereBetweenTime('end_time', 1, time())->column('manjian_id');//过滤end_time=0的情况，0表示活动永久有效
            (new Manjian())->where([['manjian_id', 'in', $ids], ['status', '=', ManjianDict::ACTIVE], ['end_time', '<=', time()]])->update(['status' => ManjianDict::END]);
            (new ManjianGoods())->where([['manjian_id', 'in', $ids]])->update(['status' => ManjianDict::END]);
            return true;
        } catch (\Exception $e) {
            Log::write('满减送自动关闭error' . $e->getMessage() . $e->getFile() . $e->getLine());
            return false;
        }
    }

}
