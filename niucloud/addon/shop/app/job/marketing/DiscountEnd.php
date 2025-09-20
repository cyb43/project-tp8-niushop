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
use addon\shop\app\dict\active\DiscountDict;
use addon\shop\app\model\active\Active;
use addon\shop\app\model\discount\Discount;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\service\admin\marketing\DiscountService;
use addon\shop\app\service\core\marketing\CoreActiveService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 限时折扣自动关闭
 */
class DiscountEnd extends BaseJob
{
    /**
     * 限时折扣
     * @return true
     */
    public function doJob()
    {
        //Log::write('DiscountEnd 限时折扣自动关闭');
        try {

            $ids = (new Discount())->where([
                ['status', '=', DiscountDict::ACTIVE],
            ])->whereBetweenTime('end_time', 1, time())->column('discount_id');//过滤end_time=0的情况，0表示活动永久有效

            (new Discount())->where([['discount_id', 'in', $ids], ['status', '=', DiscountDict::ACTIVE], ['end_time', '<=', time()]])->update(['status' => DiscountDict::END]);
            (new DiscountGoods())->where([['discount_id', 'in', $ids]])->update(['status' => DiscountDict::END]);
            (new DiscountService())->discountEndAfter($ids);
            return true;
        } catch (\Exception $e) {
            Log::error('DiscountEnd 限时折扣自动关闭error' . $e->getMessage() . $e->getFile() . $e->getLine());
            return false;
        }
    }

}
