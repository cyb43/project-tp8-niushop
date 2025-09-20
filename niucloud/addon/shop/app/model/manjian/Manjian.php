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

namespace addon\shop\app\model\manjian;

use addon\shop\app\dict\active\ManjianDict;
use core\base\BaseModel;
use think\db\Query;

/**
 * 满减送活动模型
 */
class Manjian extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'manjian_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_manjian';

    protected $type = [
        'start_time' => 'timestamp',
        'end_time' => 'timestamp',
    ];

    // 设置json类型字段
    protected $json = [ 'rule_json', 'goods_ids', 'level_ids', 'label_ids' ];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 活动商品项
     * @return \think\model\relation\HasMany
     */
    public function activeGoods()
    {
        return $this->hasMany(ManjianGoods::class, 'manjian_id', 'manjian_id');
    }

    /**
     * 活动状态
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        return ManjianDict::getStatus()[ $data[ 'status' ] ] ?? '';
    }

    /**
     * 参与商品
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getGoodsTypeNameAttr($value, $data)
    {
        if (empty($data[ 'goods_type' ])) {
            return '';
        }
        return ManjianDict::getGoodsType()[ $data[ 'goods_type' ] ] ?? '';
    }

    /**
     * 搜索器:标题
     * @param $value
     * @param $data
     */
    public function searchManjianNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("manjian_name", 'like', '%' . $this->handelSpecialCharacter($value) . '%');
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
            $query->where("status", '=', $value);
        }
    }

    /**
     * 活动结束时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[ 0 ]) ? 0 : strtotime($value[ 0 ]);
        $end_time = empty($value[ 1 ]) ? 0 : strtotime($value[ 1 ]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('end_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([ [ 'end_time', '>=', $start_time ] ]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([ [ 'end_time', '<=', $end_time ] ]);
        }
    }

    /**
     * 搜索器:id
     * @param $value
     * @param $data
     */
    public function searchActiveIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where("manjian_id", 'in', $value);
        }
    }
}
