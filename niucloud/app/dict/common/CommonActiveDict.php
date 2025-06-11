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
    const IMPULSE_BUY = 'impulse_buy';
    const GIFTCARD = 'gift_card';

    const DISCOUNT = 'discount';// 限时折扣
    const EXCHANGE = 'exchange';// 积分商城
    const MANJIANSONG = 'manjiansong'; // 满减送
    const NEWCOMER_DISCOUNT = 'newcomer_discount'; // 新人专享

    public static function getActiveShort($active)
    {
        $data = [
            self::IMPULSE_BUY => [
                'name' => get_lang('common_active_short.impulse_buy'),
                'bg_color' => "#FFAE42"
            ],
            self::GIFTCARD => [
                'name' => get_lang('common_active_short.gift_card'),
                'bg_color' => '#FF0000'
            ],
            self::DISCOUNT => [
                'name' => get_lang('common_active_short.discount'),
                'bg_color' => '#38B0DE'
            ],
            self::EXCHANGE => [
                'name' => get_lang('common_active_short.exchange'),
                'bg_color' => '#00FFFF'
            ],
            self::MANJIANSONG => [
                'name' => get_lang('common_active_short.manjiansong'),
                'bg_color' => '#00FF00'
            ],
            self::NEWCOMER_DISCOUNT => [
                'name' => get_lang('common_active_short.newcomer_discount'),
                'bg_color' => '#70DB93'
            ],
        ];
        return !empty($active) ? $data[$active] : $data;
    }
}