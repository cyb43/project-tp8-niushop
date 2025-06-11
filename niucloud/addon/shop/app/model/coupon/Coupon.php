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

namespace addon\shop\app\model\coupon;

use addon\shop\app\dict\coupon\CouponDict;
use core\base\BaseModel;
use think\db\Query;

/**
 * 优惠券模型
 */
class Coupon extends BaseModel
{


    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_coupon';

    protected $type = [
        'start_time' => 'timestamp',
        'end_time' => 'timestamp',
        'valid_start_time' => 'timestamp',
        'valid_end_time' => 'timestamp',
    ];
    /**
     * 优惠券商品项
     * @return \think\model\relation\HasMany
     */
    public function goods()
    {
        return $this->hasMany(CouponGoods::class, 'coupon_id', 'id');
    }

    /**
     * 关联商品项
     * @param $value
     * @param $data
     * @return CouponGoods[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
//    public function getGoodsDataAttr($value, $data)
//    {
//        $coupon_id = $data['id'] ?? 0;
//        $list = [];
//        if($coupon_id > 0) {
//            $coupon_id = $data['id'];
//            $list = (new CouponGoods())->where([['coupon_id', '=', $coupon_id]])->select();
//        }
//        return $list;
//    }


    public function getCouponPriceAttr($value, $data)
    {
        if (empty($data['price']))
        {
            return 0;
        }
        return rtrim(rtrim($data['price'], '0'), '.');

    }

    public function getCouponMinPriceAttr($value, $data)
    {
        if (empty($data['min_condition_money']))
        {
            return 0;
        }
        return rtrim(rtrim($data['min_condition_money'], '0'), '.');

    }

    /**
     * 搜索器:标题
     * @param $value
     * @param $data
     */
    public function searchTitleAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("title", 'like', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }

    /**
     * 搜索器:状态
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("status",'=',$value);
        }
    }


    /**
     * 类型
     * @param $value
     * @param $data
     * @return string
     */
    public function getTypeNameAttr($value, $data)
    {
        if (empty($data['type']))
            return '';
        return CouponDict::getType()[$data['type']] ?? '';
    }

    /**
     * 领取类型
     * @param $value
     * @param $data
     * @return string
     */
    public function getReceiveTypeNameAttr($value, $data)
    {
        if (empty($data['receive_type']))
            return '';
        return CouponDict::getReceiveType()[$data['receive_type']] ?? '';
    }

    /**
     * 优惠券状态
     * @param $value
     * @param $data
     * @return string
     */
    public function getStatusNameAttr($value, $data)
    {
        return CouponDict::getStatus()[$data['status']] ?? '';
    }

    /**
     * 发券按钮是否展示
     * @param $value
     * @param $data
     * @return string
     */
    public function getIsShowSendAttr($value, $data)
    {
        if($data['status'] != CouponDict::NORMAL){
            return 0;
        }
        return 1;
    }


    /**
     * 活动开始时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchStartTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[0]) ? 0 : strtotime($value[0]);
        $end_time = empty($value[1]) ? 0 : strtotime($value[1]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('start_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([['start_time', '>=', $start_time]]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([['start_time', '<=', $end_time]]);
        }
    }

    /**
     * 活动结束时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchEndTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[0]) ? 0 : strtotime($value[0]);
        $end_time = empty($value[1]) ? 0 : strtotime($value[1]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('end_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([['end_time', '>=', $start_time]]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([['end_time', '<=', $end_time]]);
        }
    }
}
