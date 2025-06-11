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
use addon\shop\app\model\coupon\CouponSendRecord;
use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use addon\shop\app\service\core\marketing\CoreCouponService;
use app\model\member\Member;
use core\base\BaseJob;
use think\facade\Db;
use think\facade\Log;

/**
 * 优惠券限时自动开启
 */
class CouponSend extends BaseJob
{
    /**
     * 消费
     * @return true
     */
    public function doJob($record_id)
    {
        Log::write('CouponSend 发送优惠券开始');
        //调整状态为进行中
        (new CouponSendRecord())->where([
            ['id' ,'=',$record_id]
        ])->update([
            'status' => CouponDict::SEND_STATUS_PROGRESS,
        ]);
        Db::startTrans();
        try {

            $success_num = 0;

            $records_info = (new CouponSendRecord())->where([
                ['id' ,'=',$record_id]
            ])->findOrEmpty()->toArray();
            $memberIds = $this->getMemberIds($records_info['range_type'], $records_info['range_param']);
            Log::write("CouponSend 发送优惠券id {$record_id}  会员id数组：".json_encode($memberIds,256));

            foreach ($memberIds as $member_id) {
                $res = (new CoreCouponMemberService())->sendCoupon($member_id, $records_info['coupon_id'], $records_info['send_num']);
                if ($res) {
                    $success_num += 1;
                }
            }
            (new CouponSendRecord())->where([['id' ,'=',$record_id]])->update([
                'status' => CouponDict::SEND_STATUS_FINISH,
                'success_num' => $success_num,
                'end_time' => time(),
                'update_time' => time()
            ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Log::error('CouponSend 发送优惠券失败'.$e->getMessage().$e->getLine());
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取发送用户数量
     * @param $range_type
     * @param $range_param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getMemberIds($range_type, $range_param)
    {
        switch ($range_type) {
            case CouponDict::SEND_RANGE_ALL:
                $member_ids = (new Member())->where([
                    ['member_id', '>', 0]
                ])->column('member_id');
                break;
            case CouponDict::SEND_RANGE_MEMBER:
                $member_ids =$range_param['member_ids'];
                break;
            case CouponDict::SEND_RANGE_MEMBER_LEVEL:
                $member_level = $range_param['member_level'];
                $member_ids = (new Member())->where([
                    ['member_level', 'in', implode(',',$member_level)],
                ])->column('member_id');
                break;
            case CouponDict::SEND_RANGE_MEMBER_LABEL:
                $member_label = $range_param['member_label'];
                $member_ids = (new Member())->where([
                ])->withSearch(['member_label'], ['member_label' => $member_label])->column('member_id');
                break;
            default:
                $member_ids = 0;
                break;
        }
        return array_values($member_ids ?? []) ?? [];
    }
}
