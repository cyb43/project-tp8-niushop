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

use addon\shop\app\dict\coupon\CouponDict;
use addon\shop\app\model\coupon\Coupon;
use addon\shop\app\service\core\marketing\CoreCouponService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 优惠券限时自动结束
 */
class CouponEnd extends BaseJob
{
    /**
     * 消费
     * @return true
     */
    public function doJob()
    {
        //Log::write('优惠券限时自动结束');
        try {
            $ids = (new Coupon())->where([
                ['status', '=', CouponDict::NORMAL],
                ['start_time', '>', 0],
                ['end_time', '>', 0],
                ['end_time', '<=', time()]
            ])->column('id');
            if(!empty($ids)){
                //修改状态
                (new CoreCouponService())->couponExpire($ids);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
