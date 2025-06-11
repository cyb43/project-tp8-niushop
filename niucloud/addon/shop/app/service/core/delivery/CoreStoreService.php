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

namespace addon\shop\app\service\core\delivery;

use addon\shop\app\dict\delivery\DeliveryDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\model\delivery\Store;
use core\base\BaseCoreService;
use Location\Coordinate;
use Location\Distance\Vincenty;

/**
 * 门店自提服务层
 * Class CoreExpressService
 * @package addon\shop\app\service\admin\delivery
 */
class CoreStoreService extends BaseCoreService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Store();
    }

    /**
     * 配送费用计算
     * @param $order
     * @return void
     */
    public static function calculate(&$order)
    {
        $store = $order->delivery[ 'take_store' ] ?? [];

        if (empty($store)) {
            $order->error[] = get_lang('NOT_SELECT_STORE');
            return;
        }

        foreach ($order->goods_data as $k => &$v) {
            $goods_type = $v[ 'goods' ][ 'goods_type' ];
            if ($goods_type == GoodsDict::REAL) {
                if (!in_array(OrderDeliveryDict::STORE, $v[ 'goods' ][ 'delivery_type' ])) {
                    $v[ 'not_support_delivery' ] = 1;
                    $order->error[] = get_lang('NOT_SUPPORT_DELIVERY_TYPE');
                }
            }
        }
    }

    /**
     * 查询自提点
     * @param int $id
     * @return array
     */
    public function getInfoById(int $store_id)
    {
        $condition = array(
            [ 'store_id', '=', $store_id ]
        );
        return $this->model->where($condition)->findOrEmpty()->toArray();
    }

    /**
     * 查询自提点
     * @param $latlng
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStoreList($latlng = [])
    {
        $list = $this->model->where([ [ 'store_id', '>', 0 ] ])->field('store_id,time_week,time_interval,trade_time_json,store_name,store_logo,store_mobile,full_address,longitude,latitude,trade_time')->select()->toArray();
        if (!empty($list) && !empty($latlng) && !empty($latlng[ 'lat' ]) && !empty($latlng[ 'lng' ])) {
            $location = new Coordinate($latlng[ 'lat' ], $latlng[ 'lng' ]);
            $list = array_map(function($item) use ($location) {
                $item[ 'distance' ] = ( new Vincenty() )->getDistance($location, new Coordinate((float) $item[ 'latitude' ], (float) $item[ 'longitude' ]));
                return $item;
            }, $list);
            array_multisort(array_column($list, 'distance'), SORT_ASC, $list);
        }
        $day_start_time = strtotime(date('Y-m-d'));
        foreach ($list as &$item) {
            $item[ 'store_time_list' ] = $this->formatTime($item[ 'time_week' ], $item[ 'trade_time_json' ], $day_start_time, $item[ 'time_interval' ]);
        }
        return $list;
    }

    /**
     * @param $weekList
     * @param $hour_arr
     * @param $start_time
     * @param $interval
     * @return array
     */
    private function formatTime($weekList, $hour_arr, $start_time, $interval)
    {
        if (empty($weekList)) {
            return [];
        }
        $array = [];
        $day_week = (int) date('w', time());
        foreach ($weekList as $week) {
            $name = DeliveryDict::getWeekList($week)[ 'name' ];
            if ($day_week == $week) {
                $day_str = '今天';
                $time = $start_time;
            }
            if ($day_week < $week) {
                $day = $week - $day_week;
                $time = strtotime("+{$day} day", $start_time);
                if ($day_week + 1 == $week) {
                    $day_str = '明天';
                } else {
                    $day_str = date("Y-m-d", $time);
                }
            }

            if ($day_week > $week) {
                $day = $day_week - $week;
                $time = strtotime('+1 week', strtotime("-{$day} day", $start_time));
                $day_str = date("Y-m-d", $time);
            }
            $array[ $time ] = [
                'name' => $day_str . "({$name})",
                'time_list' => $this->getTimeArr($hour_arr, $time, (int) $interval)
            ];
        }
        ksort($array);
        return array_values($array);
    }

    /**
     * @param $hour_arr
     * @param $start_time
     * @param int $interval
     * @return array
     */
    private function getTimeArr($hour_arr, $start_time, int $interval)
    {
        $array = [];
        foreach ($hour_arr as $item) {
            $start_second = (int) $item[ 'start_time' ];
            $end_second = (int) $item[ 'end_time' ];
            $num = ( $end_second - $start_second ) / 60 / $interval;
            for ($i = 1; $i <= $num; $i++) {
                $start_hour = $start_time + $start_second + ( $i - 1 ) * $interval * 60;
                $end_hour = $start_time + $start_second + $i * $interval * 60;
                if ($end_hour <= time()) {
                    continue;
                }
                $array[] = [
//                    'is_show'=>$end_hour>time() ?1:0,
                    'show_hour' => date('H:i', $start_hour) . '-' . date('H:i', $end_hour),
                    'time_str' => date('Y-m-d H:i', $start_hour) . '-' . date('H:i', $end_hour),
                ];
            }
        }
        return $array;
    }
}
