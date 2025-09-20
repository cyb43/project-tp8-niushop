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

namespace addon\shop\app\service\core\coupon;

use addon\shop\app\dict\coupon\CouponDict;
use addon\shop\app\dict\coupon\CouponMemberDict;
use addon\shop\app\model\coupon\Coupon;
use addon\shop\app\model\coupon\CouponMember;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\facade\Db;

/**
 * 会员优惠券服务层
 */
class CoreCouponMemberService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new CouponMember();
    }

    /**
     * 通过会员id查询会员有效可用的优惠券
     * @param $member_id
     * @return mixed
     */
    public function getUseCouponListByMemberId($member_id)
    {
        $where = array(
            ['member_id', '=', $member_id],
            ['status', '=', CouponMemberDict::WAIT_USE],
            ['expire_time', '>', time()]
        );
        $field = 'id, coupon_id, member_id, create_time, expire_time, use_time, type, status, price, min_condition_money, title';
        return $this->model->where($where)->field($field)->with([
            'goods'
        ])->select()->toArray();
    }

    /**
     * 查询会员优惠券
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUseCouponById($id)
    {
        $where = array(
            ['id', '=', $id],
            ['status', '=', CouponMemberDict::WAIT_USE]
        );
        $field = 'id, coupon_id, member_id, create_time, expire_time, use_time, type, status, price, min_condition_money, title';
        return $this->model->where($where)->field($field)->with([
            'goods'
        ])->findOrEmpty()->toArray();
    }

    /**
     * 恢复(只单个恢复)
     * @return void
     */
    public function recover($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $where = array(
            ['id', 'in', $ids],
            ['status', '=', CouponMemberDict::USED]
        );

        $coupon_member_list = $this->model->where($where)->select();
        if ($coupon_member_list->isEmpty()) {
            return true;
        }
        //恢复没必要返还错误
        //判断使用时间是否过期
        $update = [];
        foreach ($coupon_member_list as $item) {
            if (time() >= $item->expire_time) {
                $status = CouponMemberDict::EXPIRE;
            } else {
                $status = CouponMemberDict::WAIT_USE;
            }
            $update[] = [
                'id' => $item->id,
                'status' => $status,
                'trade_id' => 0,
                'use_time' => 0,
            ];
        }
        $this->model->saveAll($update);
        return true;
    }

    /**
     * 失效
     * @param $ids
     * @return void
     */
    public function invalid($ids)
    {
        $where = [
            ['id', 'in', $ids],
            ['status', '=', CouponMemberDict::WAIT_USE]
        ];
        $data = [
            'status' => CouponMemberDict::INVALID
        ];
        $this->model->where($where)->update($data);
        return true;
    }

    /**
     * 过期
     * @param $ids
     * @return void
     */
    public function expire($ids)
    {
        $where = [
            ['id', 'in', $ids],
            ['status', '=', CouponMemberDict::WAIT_USE]
        ];
        $data = [
            'status' => CouponMemberDict::EXPIRE
        ];
        $this->model->where($where)->update($data);
        return true;
    }

    /**
     * 使用
     * @param array $data
     * @return void
     */
    public function use(array $data)
    {
        $id = $data['id'];
        $where = array(
            ['id', '=', $id],
            ['status', '=', CouponMemberDict::WAIT_USE]
        );
        $coupon = $this->model->where($where)->findOrEmpty();
        if ($coupon->isEmpty()) throw new CommonException('SHOP_COUPON_IS_USED_OR_EXIST');//优惠券不存在或已使用
        $coupon->save(
            [
                'status' => CouponMemberDict::USED,
                'trade_id' => $data['trade_id'],
                'use_time' => time()
            ]
        );
        return true;
    }

    /**
     * 发放优惠券

     * @param $member_ids
     * @param $coupon_id
     * @param $num
     * @return false|int
     */
    public function sendCoupon($member_ids, $coupon_id, $num)
    {
        if (!is_array($member_ids)) {
            $member_ids = [$member_ids];
        }

        $give_count = 0;
        $coupon = (new Coupon())->where([['id', '=', $coupon_id], ['status', '=', CouponDict::NORMAL]])->findOrEmpty();
        if ($coupon->isEmpty()) {
            return $give_count;
        }
        // 剩余数量不足
//        if ($coupon[ 'remain_count' ] != '-1' && $coupon[ 'remain_count' ] < $num) {
//            throw new CommonException('COUPON_STOCK_INSUFFICIENT');
//        }
//
//        if (strtotime($coupon[ 'start_time' ]) > 0) {
//            $time = time();
//            if ($time < strtotime($coupon[ 'start_time' ])) {
//                throw new CommonException('COUPON_RECEIVE_NOT_TIME');//优惠券不在领取时间范围内
//            }
//            if ($time > strtotime($coupon[ 'end_time' ])) {
//                throw new CommonException('COUPON_RECEIVE_NOT_TIME');//优惠券不在领取时间范围内
//            }
//        }

        if ($coupon['valid_type'] == 1) {
            $expire_time = 86400 * $coupon['length'] + time();
        } else {
            $expire_time = strtotime($coupon['valid_end_time']);
        }

        $member_coupon_data = [];
        foreach ($member_ids as $member_id) {
            for ($i = 0; $i < $num; $i++) {
                $member_coupon_data[] = [
                    'coupon_id' => $coupon_id,
                    'member_id' => $member_id,
                    'create_time' => time(),
                    'expire_time' => $expire_time,
                    'receive_type' => 'send',
                    'type' => $coupon['type'],
                    'title' => $coupon['title'],
                    'price' => $coupon['price'],
                    'status' => CouponMemberDict::WAIT_USE,
                    'min_condition_money' => $coupon['min_condition_money']
                ];
            }
            $give_count += $num;
        }


        $this->model->insertAll($member_coupon_data);

        $coupon->give_count += $give_count;
//        if ($coupon[ 'remain_count' ] != -1) {
//            $coupon->remain_count -= $num;
//        }
        $coupon->save();

        return $give_count;
    }

    /**
     * 刷新用户优惠券过期时间
     * @param $coupon_id
     * @return void
     */
    public function refeshMemberCouponExpireTime($coupon_id)
    {
        $coupon_info = (new Coupon())->where([
            ['id', '=', $coupon_id]
        ])->findOrEmpty($coupon_id)->toArray();
        if ($coupon_info['valid_type'] == 1) {
            $time = 86400 * $coupon_info['length'];
            $this->model->where([
                ['coupon_id', '=', $coupon_id]
            ])->update([
                'expire_time' => Db::raw("create_time +{$time}")
            ]);
        } else {
            $this->model->where([
                ['coupon_id', '=', $coupon_id]
            ])->update([
                'expire_time' => strtotime($coupon_info['valid_end_time'])
            ]);
        }
    }

}
