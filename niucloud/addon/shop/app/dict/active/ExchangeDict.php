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

namespace addon\shop\app\dict\active;

class ExchangeDict
{
    //活动商品类型
    const GOODS = 'goods';//商品
    const COUPON = 'coupon';//优惠券
    const BALANCE = 'balance';//余额

    //活动状态
    const UP = 1;  ///上架
    const DOWN = 0;  //下架

    //活动类别
    const DISCOUNT = 'exchange';//积分商城


    /**
     * 活动商品类型
     * @param $type
     * @return array|mixed|string
     */
    public static function getType($type = '')
    {
        $list = [
            self::GOODS => get_lang('dict_shop_point_exchange_type.goods'),
            self::COUPON => get_lang('dict_shop_point_exchange_type.coupon'),
            self::BALANCE => get_lang('dict_shop_point_exchange_type.balance'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }


    /**
     * 活动状态
     */
    public static function getStatus($status = '')
    {
        $list = [
            self::UP => '已上架',
            self::DOWN => "已下架",
        ];
        if ($status == '') return $list;
        return $list[ $status ] ?? '';
    }


    /**
     * 活动类别
     * @param $type
     * @return array|mixed|string
     */
    public static function getClass($type = '')
    {
        $list = [
            self::DISCOUNT => get_lang('dict_shop_active_class.exchange'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }


}
