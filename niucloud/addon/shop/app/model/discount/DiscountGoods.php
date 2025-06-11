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

use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use app\dict\sys\FileDict;
use core\base\BaseModel;

/**
 * 限时折扣商品模型
 */
class DiscountGoods extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'discount_goods_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_discount_goods';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    /**
     * 限时折扣
     * @return \think\model\relation\HasOne
     */
    public function discount()
    {
        return $this->hasOne(Discount::class, 'discount_id', 'discount_id');
    }

    /**
     * 商品
     * @return \think\model\relation\HasOne
     */
    public function goods()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id');
    }

    /**
     * 商品规格
     * @return \think\model\relation\HasOne
     */
    public function goodsSku()
    {
        return $this->hasOne(GoodsSku::class, 'sku_id', 'sku_id');
    }

    /**
     * 获取封面缩略图（小）
     */
    public function getGoodsCoverThumbSmallAttr($value, $data)
    {
        if (isset($data[ 'goods_cover' ]) && $data[ 'goods_cover' ] != '') {
            return get_thumb_images($data[ 'goods_cover' ], FileDict::SMALL);
        }
        return [];
    }

    /**
     * 状态字段转化
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getGoodsTypeNameAttr($value, $data)
    {
        if (!empty($data[ 'goods_type' ])) {
            return GoodsDict::getType($data[ 'goods_type' ])[ 'name' ] ?? '';
        }
        return '';
    }

}
