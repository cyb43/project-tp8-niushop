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

use addon\shop\app\dict\order\OrderRefundDict;
use app\model\member\Member;
use app\model\pay\Refund;
use core\base\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\HasOne;

/**
 * 订单退款模型
 */
class OrderRefund extends BaseModel
{
    use SoftDelete;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'refund_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_order_refund';

    //类型
    protected $type = [
        'transfer_time' => 'timestamp',
    ];

    // 设置json类型字段
    protected $json = ['delivery', 'voucher', 'refund_address'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 定义软删除标记字段.
     * @var string
     */
    protected $deleteTime = 'delete_time';

    protected $defaultSoftDelete = 0;

    /**
     * 关联订单项
     * @return HasOne
     */
    public function orderGoods()
    {
        return $this->hasOne(OrderGoods::class, 'order_goods_id', 'order_goods_id');
    }

    /**
     * 订单主表
     * @return BaseModel|HasOne
     */
    public function orderMain()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id');
    }

    /**
     * 关联退款日志表
     * @return \think\model\relation\HasMany
     */
    public function refundLog()
    {
        return $this->hasMany(OrderRefundLog::class, 'order_refund_no', 'order_refund_no');
    }

    /**
     * 关联会员表
     */
    public function member()
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id');
    }

    /**
     * 关联退款表
     */
    public function payRefund()
    {
        return $this->hasOne(Refund::class, 'refund_no', 'refund_no');
    }

    /**
     * 获取退款状态
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        if ($data['status']) {
            return OrderRefundDict::getRefundStatus($data['status']);
        }
    }

    /**
     * 退款类型
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    public function getRefundTypeNameAttr($value, $data)
    {
        if ($data['refund_type']) {
            return OrderRefundDict::getRefundType($data['refund_type']);
        }
    }

    /**
     * 退款编号搜索
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchOrderRefundNoAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("order_refund_no", "like", "%" . $this->handelSpecialCharacter($value) . "%");
        }
    }

    /**
     * 退款状态搜索
     * @param $query
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
     * 创建时间搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr($query, $value, $data)
    {
        $start_time = empty($value[0]) ? 0 : strtotime($value[0]);
        $end_time = empty($value[1]) ? 0 : strtotime($value[1]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('create_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([['create_time', '>=', $start_time]]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([['create_time', '<=', $end_time]]);
        }
    }
}
