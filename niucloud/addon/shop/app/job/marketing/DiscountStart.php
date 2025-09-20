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

use addon\shop\app\dict\active\DiscountDict;
use addon\shop\app\model\discount\Discount;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\service\admin\marketing\DiscountService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 限时折扣自动开启
 */
class DiscountStart extends BaseJob
{
    /**
     * 限时折扣
     * @return true
     */
    public function doJob()
    {
        Log::write('限时折扣自动开启');
        try {

            $ids = (new Discount())->where([
                ['status', '=', DiscountDict::NOT_ACTIVE],
                ['start_time', '<=', time()]
            ])->column('discount_id');
            (new Discount())->where([ ['discount_id', 'in', $ids], ['status', '=', DiscountDict::NOT_ACTIVE], ['start_time', '<=', time()] ])->update([ 'status' => DiscountDict::ACTIVE ]);
            (new DiscountGoods())->where([ ['discount_id', 'in', $ids]])->update([ 'status' => DiscountDict::ACTIVE ]);
            ( new DiscountService() )->discountStartAfter($ids);
            return true;
        } catch (\Exception $e) {
            Log::write('限时折扣自动开启error'.$e->getMessage().$e->getFile().$e->getLine());
            return false;
        }
    }

}
