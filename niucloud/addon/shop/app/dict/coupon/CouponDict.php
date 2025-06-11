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

namespace addon\shop\app\dict\coupon;

class CouponDict
{

    const ALL = 1;
    const CATEGORY = 2;
    const GOODS = 3;

    const USER = 1;
    const GRANT = 2;

    /**
     * 优惠券类型
     * @param $status
     * @return array|mixed|string
     */
    public static function getType($type = '')
    {
        $list = [
            self::ALL => get_lang('dict_shop_coupon.all'),
            self::CATEGORY => get_lang('dict_shop_coupon.category'),
            self::GOODS => get_lang('dict_shop_coupon.goods'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }

    /**
     * 领取优惠券类型
     * @param $status
     * @return array|mixed|string
     */
    public static function getReceiveType($type = '')
    {
        $list = [
            self::USER => get_lang('dict_shop_coupon.user'),
            self::GRANT => get_lang('dict_shop_coupon.grant'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }


    //未开始
    const WAIT_START = 0;
    //进行中
    const NORMAL = 1;
    //已过期
    const EXPIRE = 2;
    //已失效
    const INVALID = 3;

    const SEND_RANGE_ALL = 'all';
    const SEND_RANGE_MEMBER = 'member';
    const SEND_RANGE_MEMBER_LEVEL = 'member_level';
    const SEND_RANGE_MEMBER_LABEL = 'member_label';

    const SEND_STATUS_WAIT= 'wait';
    const SEND_STATUS_PROGRESS= 'progress';
    const SEND_STATUS_FINISH= 'finish';

    /**
     * 状态
     * @param $status
     * @return array|mixed|string
     */
    public static function getStatus($status = '')
    {
        $list = [
            self::WAIT_START => get_lang('dict_shop_coupon.wait_start'),
            self::NORMAL => get_lang('dict_shop_coupon.normal'),
            self::EXPIRE => get_lang('dict_shop_coupon.expire'),
            self::INVALID => get_lang('dict_shop_coupon.invalid'),
        ];
        if ($status == '') return $list;
        return $list[ $status ] ?? '';
    }

    /**
     * 发券范围
     * @param $status
     * @return array|mixed|string
     */
    public static function getSendCouponRangeType($range = '')
    {
        $list = [
            self::SEND_RANGE_ALL => get_lang('dict_shop_send_coupon_range.all'),
            self::SEND_RANGE_MEMBER => get_lang('dict_shop_send_coupon_range.member'),
            self::SEND_RANGE_MEMBER_LEVEL => get_lang('dict_shop_send_coupon_range.member_level'),
            self::SEND_RANGE_MEMBER_LABEL => get_lang('dict_shop_send_coupon_range.member_label'),
        ];
        if ($range == '') return $list;
        return $list[ $range ] ?? '';
    }

    /**
     * 发券范围描述
     * @param $status
     * @return array|mixed|string
     */
    public static function getSendCouponRangeTypeDesc($range = '')
    {
        $list = [
            self::SEND_RANGE_ALL => get_lang('dict_shop_send_coupon_range_type_desc.all'),
            self::SEND_RANGE_MEMBER => get_lang('dict_shop_send_coupon_range_type_desc.member'),
            self::SEND_RANGE_MEMBER_LEVEL => get_lang('dict_shop_send_coupon_range_type_desc.member_level'),
            self::SEND_RANGE_MEMBER_LABEL => get_lang('dict_shop_send_coupon_range_type_desc.member_label'),
        ];
        if ($range == '') return $list;
        return $list[ $range ] ?? '';
    }

    /**
     * 发券状态
     * @param $status
     * @return array|mixed|string
     */
    public static function getSendCouponStatus($range = '')
    {
        $list = [
            self::SEND_STATUS_WAIT => get_lang('dict_shop_send_coupon_status.wait'),
            self::SEND_STATUS_PROGRESS => get_lang('dict_shop_send_coupon_status.progress'),
            self::SEND_STATUS_FINISH => get_lang('dict_shop_send_coupon_status.finish'),
        ];
        if ($range == '') return $list;
        return $list[ $range ] ?? '';
    }


}
