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

namespace addon\shop\app\service\core\goods;

use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\Stat;
use addon\shop\app\model\order\OrderGoods;
use core\base\BaseCoreService;
use think\facade\Db;

/**
 * 商品数据统计服务层
 */
class CoreGoodsStatService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
    }

    private const STAT_FIELD = [
        'sale_num',  //销售数量
        'pay_num',  //支付商品数
        'pay_money',  //支付总金额
        'refund_num',  //退款数量
        'refund_money',  //退款金额
        'access_num',  //访客数
        'collect_num',  //收藏数量
        'cart_num',  //加入购物车数量
        'evaluate_num',  //评论数量
        'goods_visit_member_count',  //商品访客数
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
            'date_time' => strtotime(date('Y-m-d', time())),
            'goods_id' => $data[ 'goods_id' ],
        ];
        if ($stat_data['goods_id'] <= 0) return false;

        $stat = ( new Stat() )->where($stat_data)->findOrEmpty();
        if ($stat->isEmpty()) {
            $stat->allowField(array_merge(array_keys($stat_data), self::STAT_FIELD))->save(array_merge($stat_data, $data));
        } else {
            unset($data[ 'goods_id' ]);
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
            'date_time' => strtotime(date('Y-m-d', $time)),
            'goods_id' => $data[ 'goods_id' ],
        ];

        $stat = ( new Stat() )->where($stat_data)->findOrEmpty();
        if (!$stat->isEmpty()) {
            unset($data[ 'goods_id' ]);
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
     * @param int $goods_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getStatData(int $goods_id, string $start_date, string $end_date)
    {
        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        $field = implode(',', array_merge(self::STAT_FIELD, [ 'date' ]));
        $stat_data = ( new Stat() )->where([ [ 'goods_id', '=', $goods_id ], [ 'date_time', '>=', $start_date ], [ 'date_time', '<=', $end_date ] ])->field($field)->select()->toArray();
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
     * 根据订单统计商品的支付数量及支付金额
     * @param $order_data
     * @return bool
     */
    public function saveGoodsPayNumAndMoneyByOrderId($order_data)
    {
        $order_goods_model = new OrderGoods();
        $order_goods_data = $order_goods_model->where([ 'order_id' => $order_data[ 'order_id' ] ])->select()->toArray();
        if (!empty($order_goods_data)) {
            foreach ($order_goods_data as $goods) {
                CoreGoodsStatService::addStat([ 'goods_id' => $goods[ 'goods_id' ], 'pay_num' => $goods[ 'num' ], 'pay_money' => $goods[ 'order_goods_money' ] ]);

                ( new Goods() )->where([ [ 'goods_id', '=', $goods[ 'goods_id' ] ] ])->inc('pay_num', $goods[ 'num' ])->update(); // 更新支付件数
                ( new Goods() )->where([ [ 'goods_id', '=', $goods[ 'goods_id' ] ] ])->inc('pay_money', $goods[ 'order_goods_money' ])->update(); // 更新支付金额
            }
        }
        return true;
    }

    /**
     * 根据退款订单统计商品的退款件数、金额
     * @param $refund_data
     * @return bool
     */
    public function saveGoodsRefundNumAndMoneyByOrderId($refund_data)
    {
        $order_goods_model = new OrderGoods();
        $order_goods_info = $order_goods_model->where([ 'order_goods_id' => $refund_data[ 'order_goods_id' ] ])->findOrEmpty();
        if (!$order_goods_info->isEmpty()) {
            CoreGoodsStatService::addStat([ 'goods_id' => $order_goods_info[ 'goods_id' ], 'refund_num' => $order_goods_info[ 'num' ], 'refund_money' => $refund_data[ 'money' ] ]);

            ( new Goods() )->where([ [ 'goods_id', '=', $order_goods_info[ 'goods_id' ] ] ])->inc('refund_num', $order_goods_info[ 'num' ])->update(); // 更新退款件数
            ( new Goods() )->where([ [ 'goods_id', '=', $order_goods_info[ 'goods_id' ] ] ])->inc('refund_money', $refund_data[ 'money' ])->update(); // 更新退款金额
        }

        return true;
    }

}
