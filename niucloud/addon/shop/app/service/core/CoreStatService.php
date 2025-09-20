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

namespace addon\shop\app\service\core;

use addon\shop\app\model\ShopStat;
use core\base\BaseCoreService;
use think\facade\Db;

/**
 * 统计
 * Class CoreStatService
 */
class CoreStatService extends BaseCoreService
{
    private const STAT_FIELD = [
        'order_num',  //订单数
        'sale_money',  //销售额
        'refund_money',  //退款金额
        'access_sum'  //访客数
    ];

    /**
     * 添加统计数据
     * @param $data
     * @return true
     */
    public static function addStat($data = [])
    {
        // 添加天统计
        $stat_data = [
            'date' => date('Y-m-d', time()),
            'date_time' => strtotime(date('Y-m-d', time()))
        ];
        $stat = ( new ShopStat() )->where($stat_data)->findOrEmpty();
        if ($stat->isEmpty()) {
            $stat->allowField(array_merge(array_keys($stat_data), self::STAT_FIELD))->save(array_merge($stat_data, $data));
        } else {
            foreach ($data as $key => $value) {
                $stat->$key = Db::raw("{$key} + {$value}");
            }
            $stat->allowField(self::STAT_FIELD)->save();
        }
        return true;
    }

    /**
     * 累减统计数据
     * @param $data
     * @return true
     */
    public static function decStat($data = [])
    {
        $time = strtotime($data['time']);
        // 更新天统计
        $stat_data = [
            'date' => date('Y-m-d', $time),
            'date_time' => strtotime(date('Y-m-d', $time))
        ];

        $stat = ( new ShopStat() )->where($stat_data)->findOrEmpty();
        if (!$stat->isEmpty()) {
            unset($data[ 'time' ]);
            foreach ($data as $key => $value) {
                $stat->$key = Db::raw("CASE WHEN {$key} - {$value} >= 0 THEN {$key} - {$value} ELSE 0 END");
            }
            $stat->allowField(self::STAT_FIELD)->save();
        }
        return true;
    }

    /**
     * 获取时间区间内天统计数据
     * @param string $start_date
     * @param string $end_date
     * @return void
     */
    public function getStatData(string $start_date, string $end_date)
    {
        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        $field = implode(',', array_merge(self::STAT_FIELD, [ 'date' ]));
        $stat_data = ( new ShopStat() )->where([ [ 'date_time', '>=', $start_date ], [ 'date_time', '<=', $end_date ] ])->field($field)->select()->toArray();
        $stat_data = !empty($stat_data) ? array_column($stat_data, null, 'date') : [];

        $data = [];
        $day = ceil(( $end_date - $start_date ) / 86400);
        foreach (self::STAT_FIELD as $field) {
            $value = [];
            $time = [];
            for ($i = 0; $i < $day; $i++) {
                $date = date('Y-m-d', $start_date + $i * 86400);
                $time[] = $date;

                $value[] = isset($stat_data[ $date ]) && isset($stat_data[ $date ][ $field ]) ? $stat_data[ $date ][ $field ] : 0;
            }
            $data[ $field ] = $value;
            $data[ 'time' ] = $time;
        }

        return $data;
    }

    /**
     * 获取时间区间内小时统计数据
     * @param string $date
     * @return array
     */
    public function getHourStatData( string $date)
    {
        $field = implode(',', array_merge(self::STAT_FIELD, [ 'hour' ]));
        $stat_data = ( new ShopStat() )->where([ [ 'date_time', '=', strtotime($date) ] ])->field($field)->select()->toArray();
        $stat_data = !empty($stat_data) ? array_column($stat_data, null, 'hour') : [];

        $data = [];
        foreach (self::STAT_FIELD as $field) {
            $value = [];
            for ($i = 0; $i < 24; $i++) {
                $value[ $i ] = isset($stat_data[ $i ]) && isset($stat_data[ $i ][ $field ]) ? $stat_data[ $i ][ $field ] : 0;
            }
            $data[ $field ] = $value;
        }
        $data[ 'time' ] = array_map(function($value) {
            return $value . '时';
        }, range(0, 23, 1));

        return $data;
    }

    /**
     * 查询统计总和
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getStat( string $start_date = '', string $end_date = '')
    {
        $condition = [
            [ 'id', '>', 0 ]
        ];

        if (!empty($start_date) && !empty($end_date)) {
            $condition[] = [ 'date_time', '>=', strtotime($start_date) ];
            $condition[] = [ 'date_time', '<=', strtotime($end_date) ];
        } else if (!empty($start_date)) {
            $condition[] = [ 'date_time', '=', strtotime($start_date) ];
        }

        $field = array_map(function($field) {
            return "IFNULL(sum({$field}), 0) as {$field}";
        }, self::STAT_FIELD);

        if ($start_date == '' && $end_date == '') {
            $condition[] = [ 'date_time', '>', 0 ];
        }

        $stat_data = ( new ShopStat() )->where($condition)->field(implode(',', $field))->findOrEmpty()->toArray();

        return $stat_data;
    }
}
