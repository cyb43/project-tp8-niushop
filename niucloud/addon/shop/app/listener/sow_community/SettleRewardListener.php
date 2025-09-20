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

namespace addon\shop\app\listener\sow_community;

use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use core\exception\CommonException;
use think\facade\Log;

/**
 * 种草奖励发放优惠券
 * Class SettleRewardListener
 * @package addon\shop\app\listener\sow_community
 */
class SettleRewardListener
{
    public function handle(array $params)
    {
        $member_id = $params[ 'member_id' ];

        Log::write('种草赠送优惠券开始', json_encode($params));
        foreach ($params[ 'coupon' ] as $value) {
            try {
                ( new CoreCouponMemberService() )->sendCoupon($member_id, $value[ 'coupon_id' ], $value[ 'num' ]);
            } catch (CommonException $e) {
                Log::write('种草赠送优惠券“' . $value[ 'coupon_id' ] . '”发放失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            }
        }
    }
}
