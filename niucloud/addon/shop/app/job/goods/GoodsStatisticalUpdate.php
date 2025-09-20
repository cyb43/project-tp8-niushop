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

use addon\shop\app\service\admin\goods\StatisticsService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 商品统计更新
 */
class GoodsStatisticalUpdate extends BaseJob
{
    /**
     * 商品统计更新
     * @return bool
     */
    public function doJob()
    {
        //Log::write(' 商品统计更新');
        try {
            (new StatisticsService())->syncStatGoods();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
