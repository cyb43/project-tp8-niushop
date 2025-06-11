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

namespace addon\shop\app\model\discount;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\active\DiscountDict;
use core\base\BaseModel;
use think\db\Query;

/**
 * 限时折扣模型
 */
class Discount extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'discount_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_discount';

    protected $type = [
        'start_time' => 'timestamp',
        'end_time' => 'timestamp',
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];


    /**
     * 活动商品项
     * @return \think\model\relation\HasMany
     */
    public function activeGoods()
    {
        return $this->hasMany(DiscountGoods::class, 'discount_id', 'discount_id');
    }

    /**
     * 状态
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        if (empty($data['status']))
        {
            return '';
        }
        return DiscountDict::getStatus()[$data['status']] ?? '';
    }

    /**
     * 搜索器:标题
     * @param $value
     * @param $data
     */
    public function searchNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("name", 'like', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }
    /**
     * 搜索器:状态
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("status", '=', $value);
        }
    }
}
