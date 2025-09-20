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
namespace addon\shop\app\job\goods;

use addon\shop\app\service\admin\goods\EvaluateService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 商品评价审核状态更新为无需审核调用
 */
class GoodsEvaluateStat extends BaseJob
{
    /**
     * 消费
     * @return bool
     */
    public function doJob($evaluate_ids)
    {
        //Log::write('商品评价审核批量修改， 批量更新评价统计数据' . var_export($evaluate_ids, true));
        try {
            //修改状态
            (new EvaluateService())->updateGoodsEvaluateNumBach($evaluate_ids);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
