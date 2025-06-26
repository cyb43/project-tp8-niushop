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

namespace addon\shop\app\model\order;

use addon\shop\app\dict\delivery\DeliveryDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\model\delivery\Store;
use app\dict\common\ChannelDict;
use app\model\member\Member;
use app\model\pay\Pay;
use core\base\BaseModel;
use think\db\Query;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 订单模型
 */
class Order extends BaseModel
{
    use SoftDelete;
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'order_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_order';

    //类型
    protected $type = [
        'pay_time' => 'timestamp',
        'close_time' => 'timestamp',

        'delivery_time' => 'timestamp',
        'take_time' => 'timestamp',
        'finish_time' => 'timestamp',
    ];

    // 设置json类型字段
    protected $json = [ 'has_goods_types' ];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 定义软删除标记字段.
     * @var string
     */
    protected $deleteTime = 'delete_time';

    protected $defaultSoftDelete = 0;

    /**
     * 订单项
     * @return HasMany
     */
    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id', 'order_id');
    }

    /**
     * 订单会员
     * @return HasOne
     */
    public function member()
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id');
    }

    /**
     * 支付
     * @return HasOne
     */
    public function pay()
    {
        return $this->hasOne(Pay::class, 'out_trade_no', 'out_trade_no');
    }

    /**
     * 日志
     */
    public function orderLog()
    {
        return $this->hasMany(OrderLog::class, 'order_id', 'order_id');
    }

    /**
     * 自提点
     * @return HasOne
     */
    public function store()
    {
        return $this->hasOne(Store::class, 'store_id', 'take_store_id');
    }

    /**
     * 包裹
     * @return hasMany
     */
    public function orderDelivery()
    {
        return $this->hasMany(OrderDelivery::class, 'order_id', 'order_id');
    }

    /**
     * 优惠表
     * @return HasOne
     */
    public function shopOrderDiscount()
    {
        return $this->hasOne(OrderDiscounts::class, 'order_id', 'order_id');
    }

    /**
     * 优惠表
     * @return hasMany
     */
    public function orderDiscount()
    {
        return $this->hasMany(OrderDiscounts::class, 'order_id', 'order_id');
    }

    /**
     * 发票
     * @return HasOne
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

    /**
     * 来源渠道
     * @param $value
     * @param $data
     * @return mixed|string|void
     */
    public function getOrderFromNameAttr($value, $data)
    {
        if (empty($data[ 'order_from' ]))
            return '';
        $order_from_list = ChannelDict::getType();
        $from_event_list = array_filter(event('OrderFromList')) ?? [];
        foreach ($from_event_list as $item) {
            $order_from_list = array_merge($order_from_list, $item);
        }
        return $order_from_list[ $data[ 'order_from' ] ] ?? '';
    }

    /**
     * 订单类型
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getOrderTypeNameAttr($value, $data)
    {
        if (empty($data[ 'order_type' ]))
            return '';
//        return OrderDict::getType()[$data['order_type']] ?? '';
    }

    /**
     * 订单状态
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        if (empty($data[ 'status' ]))
            return '';
        return OrderDict::getStatus($data[ 'status' ],$data['delivery_type'])?? '';
    }

    /**
     * 配送方式
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getDeliveryTypeNameAttr($value, $data)
    {
        if (empty($data[ 'delivery_type' ]))
            return '';
        return OrderDeliveryDict::getType()[ $data[ 'delivery_type' ] ] ?? '';
    }

    /**
     * 搜索器:订单编号
     * @param $value
     * @param $data
     */
    public function searchOrderNoAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("order_no", "like", "%$value%");
        }
    }

    /**
     * 搜索器:订单状态
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("status", $value);
        }
    }


    /**
     * 搜索器:营销类型
     * @param $value
     * @param $data
     */
    public function searchActivityTypeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("activity_type", $value);
        }
    }


    /**
     * 连表搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchJoinStatusAttr($query, $value, $data)
    {
        if ($data[ 'status' ]) {
            $query->where("order.status", $data[ 'status' ]);
        }
    }

    /**
     * 支付时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchPayTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[ 0 ]) ? 0 : strtotime($value[ 0 ]);
        $end_time = empty($value[ 1 ]) ? 0 : strtotime($value[ 1 ]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('pay_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([ [ 'pay_time', '>=', $start_time ] ]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([ [ 'pay_time', '<=', $end_time ] ]);
        }
    }

    /**
     * 支付时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchJoinPayTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($data[ 'pay_time' ][ 0 ]) ? 0 : strtotime($data[ 'pay_time' ][ 0 ]);
        $end_time = empty($data[ 'pay_time' ][ 1 ]) ? 0 : strtotime($data[ 'pay_time' ][ 1 ]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('order.pay_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([ [ 'order.pay_time', '>=', $start_time ] ]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([ [ 'order.pay_time', '<=', $end_time ] ]);
        }
    }

    /**
     * 创建时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[ 0 ]) ? 0 : strtotime($value[ 0 ]);
        $end_time = empty($value[ 1 ]) ? 0 : strtotime($value[ 1 ]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('order.create_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([ [ 'order.create_time', '>=', $start_time ] ]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([ [ 'order.create_time', '<=', $end_time ] ]);
        }
    }

    /**
     * 搜索器:订单状态
     * @param $value
     * @param $data
     */
    public function searchSearchTypeAttr($query, $value, $data)
    {
        if ($value && isset($data[ 'search_name' ]) && $data[ 'search_name' ] != '') {
            $search_name = $this->handelSpecialCharacter($data[ 'search_name' ]);
            if ($value == 'order_no') $query->where("order_no", "like", "%$search_name%");
            if ($value == 'out_trade_no') $query->where("order.out_trade_no", "like", "%$search_name%");
            if ($value == 'goods_name') $query->where("order.body", "like", "%$search_name%");
        }
    }

    /**
     * 搜索器:订单状态
     * @param $value
     * @param $data
     */
    public function searchOrderFromAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("order_from", $value);
        }
    }

    /**
     * 搜索器:收货人|收货人手机号
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->whereLike("taker_name|taker_mobile", "%" . $this->handelSpecialCharacter($value) . "%");
        }
    }
}
