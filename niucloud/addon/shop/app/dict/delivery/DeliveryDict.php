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

namespace addon\shop\app\dict\delivery;


/**
 *订单配送相关枚举类
 */
class DeliveryDict
{
    //订单状态
    //快递
    const EXPRESS = 'express';
    //同城
    const LOCAL_DELIVERY = 'local_delivery';
    //自提
    const STORE = 'store';
    /**
     * 周列表
     */
    const WEEK_LIST = [
        1 => [ 'name' => '周一', 'type' => 1 ],
        2 => [ 'name' => '周二', 'type' => 2 ],
        3 => [ 'name' => '周三', 'type' => 3 ],
        4 => [ 'name' => '周四', 'type' => 4 ],
        5 => [ 'name' => '周五', 'type' => 5 ],
        6 => [ 'name' => '周六', 'type' => 6 ],
        0 => [ 'name' => '周日', 'type' => 0 ],
    ];
    //时间间隔 单位 分钟
    const TIME_INTERVAL_30 = 30;
    const TIME_INTERVAL_60 = 60;
    const TIME_INTERVAL_90 = 90;
    const TIME_INTERVAL_120 = 120;

    /**
     * 获取配送方式(用于订单)
     * @param string $type
     * @return array|array[]|string
     */
    public static function getType(string $type = '')
    {
        $data = [
            self::EXPRESS => get_lang('dict_shop_delivery_type.express'),
            self::LOCAL_DELIVERY => get_lang('dict_shop_delivery_type.local_delivery'),
            self::STORE => get_lang('dict_shop_delivery_type.store'),
        ];

        if ($type == '') {
            return $data;
        }
        return $data[ $type ] ?? '';
    }

    /**
     * 获取周列表
     * @param string $type
     * @return array|array[]|string
     */
    public static function getWeekList($week = '')
    {
        if ($week !== '') {
            return self::WEEK_LIST[ $week ];
        }
        return self::WEEK_LIST;
    }

    public static function getTimeIntervalList($time_interval = '')
    {
        $list = [
            self::TIME_INTERVAL_30 => [
                'type' => self::TIME_INTERVAL_30,
                'name' => get_lang('dict_shop_delivery_store_time_interval.30'),
            ],
            self::TIME_INTERVAL_60 => [
                'type' => self::TIME_INTERVAL_60,
                'name' => get_lang('dict_shop_delivery_store_time_interval.60'),
            ],
            self::TIME_INTERVAL_90 => [
                'type' => self::TIME_INTERVAL_90,
                'name' => get_lang('dict_shop_delivery_store_time_interval.90'),
            ],
            self::TIME_INTERVAL_120 => [
                'type' => self::TIME_INTERVAL_120,
                'name' => get_lang('dict_shop_delivery_store_time_interval.120'),
            ],
        ];
        if ($time_interval == '') return $list;
        return $list[ $time_interval ];
    }
}
