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

class ActiveDict
{
    //活动类型(店铺活动，会员活动，商品活动)
    const MEMBER = 'member';//会员活动
    const GOODS = 'goods';//商品活动
    const SHOP = 'shop';//店铺活动

    //商品活动类型（单品，独立商品，店铺整体商品）
    const GOODS_SINGLE = 'single';//单品
    const GOODS_INDEPENDENT = 'independent';//独立商品
    const GOODS_SHOP = 'shop';//店铺整体商品

    //活动状态
    const NOT_ACTIVE = 'not_active';//活动未开始
    const ACTIVE = 'active';//活动进行中
    const END = 'end';//活动已结束
    const CLOSE = 'close';//活动已关闭

    //活动类别（active_class）
    const DISCOUNT = 'discount';// 限时折扣
    const EXCHANGE = 'exchange';// 积分商城
    const MANJIANSONG = 'manjiansong'; // 满减送
    const NEWCOMER_DISCOUNT = 'newcomer_discount'; // 新人专享
    const GIFTCARD = 'giftcard'; // 礼品卡
    const PINTUAN = 'pintuan'; // 拼团
    //const SECKILL = 'seckill'; // 秒杀


    /**
     * 状态
     * @param $status
     * @return array|mixed|string
     */
    public static function getStatus($status = '')
    {
        $list = [
            self::NOT_ACTIVE => get_lang('dict_shop_active_status.not_active'),
            self::ACTIVE => get_lang('dict_shop_active_status.active'),
            self::END => get_lang('dict_shop_active_status.end'),
            self::CLOSE => get_lang('dict_shop_active_status.close'),
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
            self::DISCOUNT => get_lang('dict_shop_active_class.discount'),
            self::EXCHANGE => get_lang('dict_shop_active_class.exchange'),
            self::MANJIANSONG => get_lang('dict_shop_active_class.manjiansong'),
            self::NEWCOMER_DISCOUNT => get_lang('dict_shop_active_class.newcomer_discount'),
            self::GIFTCARD => get_lang('dict_shop_active_class.newcomer_discount'),
            self::PINTUAN => get_lang('dict_shop_active_class.newcomer_discount'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }

    /**
     * 活动类型
     * @param $type
     * @return array|mixed|string
     */
    public static function getType($type = '')
    {
        $list = [
            self::MEMBER => get_lang('dict_shop_active_type.member'),
            self::GOODS => get_lang('dict_shop_active_type.goods'),
            self::SHOP => get_lang('dict_shop_active_type.shop'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }

    /**
     * 活动商品类型
     * @param $type
     * @return array|mixed|string
     */
    public static function getGoodsType($type = '')
    {
        $list = [
            self::GOODS_SINGLE => get_lang('dict_shop_active_goods_type.single'),
            self::GOODS_INDEPENDENT => get_lang('dict_shop_active_goods_type.independent'),
            self::GOODS_SHOP => get_lang('dict_shop_active_goods_type.shop'),
        ];
        if ($type == '') return $list;
        return $list[ $type ] ?? '';
    }

}
