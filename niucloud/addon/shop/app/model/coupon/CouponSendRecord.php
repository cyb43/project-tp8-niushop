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

/**
 * 优惠券会员领取记录模型
 */
class CouponSendRecord extends BaseModel
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
    protected $name = 'shop_coupon_send_records';


    protected $type = [
        'end_time' => 'timestamp',
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];
    protected $json = ['range_param'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 关联优惠券
     * @return \think\model\relation\HasOne
     */
    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }

    /**
     * 获取范围名称
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    public function getRangeTypeNameAttr($value, $data)
    {
        if (empty($data[ 'range_type' ]))
            return '';
        return CouponDict::getSendCouponRangeTypeDesc($data[ 'range_type' ]) ?? '';
    }

    /**
     * 获取状态
     * @param $value
     * @param $data
     * @return array|mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        if (empty($data['status']))
            return '';
        return CouponDict::getSendCouponStatus($data['status']) ?? '';
    }

    /**
     * 获取参数值
     * @param $value
     * @param $data
     * @return array|string
     */
    public function getRangeParamValueAttr($value, $data)
    {
        if (empty($data['range_type']))
            return '';
        if ($data['range_type'] == CouponDict::SEND_RANGE_MEMBER_LEVEL){
            return $data['range_param']['level_name'] ??"";
        }
        if ($data['range_type'] == CouponDict::SEND_RANGE_MEMBER_LABEL){
            return $data['range_param']['label_name'] ??"";
        }
        if ($data['range_type'] == CouponDict::SEND_RANGE_MEMBER){
            return $data['range_param']['member_ids'] ?? [];
        }
        return '全体会员';
    }

    /**
     * 创建时间搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr($query, $value, $data)
    {
        $start_time = empty($value[ 0 ]) ? 0 : strtotime($value[ 0 ]);
        $end_time = empty($value[ 1 ]) ? 0 : strtotime($value[ 1 ]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('create_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([ [ 'create_time', '>=', $start_time ] ]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([ [ 'create_time', '<=', $end_time ] ]);
        }
    }
}
