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

namespace app\dict\common;


/**
 * 渠道枚举类
 * Class ChannelDict
 * @package app\dict\common
 */
class CommonActiveDict
{
    const IMPULSE_BUY = 'impulse_buy';//顺手买     顺
    const GIFTCARD = 'gift_card';//礼品卡  礼

    const DISCOUNT = 'discount';// 限时折扣     折
    const EXCHANGE = 'exchange';// 积分商城     积
    const MANJIANSONG = 'manjiansong'; // 满减送   满减
    const NEWCOMER_DISCOUNT = 'newcomer_discount'; // 新人专享  新
    const PINTUAN = 'pintuan'; // 新人专享  新
    const SECKILL = 'seckill'; // 秒杀  秒

    public static function getActiveShort($active = '')
    {
        $data = [
            self::IMPULSE_BUY => [
                'name' => get_lang('common_active_short.impulse_buy_short'),
                'active_name' => get_lang('common_active_short.impulse_buy_name'),
                'bg_color' => "#FF7700"
            ],
            self::GIFTCARD => [
                'name' => get_lang('common_active_short.gift_card_short'),
                'active_name' => get_lang('common_active_short.gift_card_name'),
                'bg_color' => '#F00000'
            ],
            self::DISCOUNT => [
                'name' => get_lang('common_active_short.discount_short'),
                'active_name' => get_lang('common_active_short.discount_name'),
                'bg_color' => '#FFA322'
            ],
            self::EXCHANGE => [
                'name' => get_lang('common_active_short.exchange_short'),
                'active_name' => get_lang('common_active_short.exchange_name'),
                'bg_color' => '#00C441'
            ],
            self::MANJIANSONG => [
                'name' => get_lang('common_active_short.manjiansong_short'),
                'active_name' => get_lang('common_active_short.manjiansong_name'),
                'bg_color' => '#249DE9'
            ],
            self::NEWCOMER_DISCOUNT => [
                'name' => get_lang('common_active_short.newcomer_discount_short'),
                'active_name' => get_lang('common_active_short.newcomer_discount_name'),
                'bg_color' => '#BB27FF'
            ],
            self::SECKILL => [
                'name' => get_lang('common_active_short.seckill_short'),
                'active_name' => get_lang('common_active_short.seckill_name'),
                'bg_color' => '#F606CA'
            ],
            self::PINTUAN => [
                'name' => get_lang('common_active_short.pintuan_short'),
                'active_name' => get_lang('common_active_short.pintuan_name'),
                'bg_color' => '#FF1C77'
            ],
        ];
        return !empty($active) ? $data[$active] : $data;
    }
}