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

namespace addon\shop\app\model\goods;

use app\dict\sys\FileDict;
use core\base\BaseModel;

/**
 * 商品规格模型
 * Class GoodsSku
 * @package addon\shop\app\model\goods_sku
 */
class GoodsSku extends BaseModel
{


    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'sku_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_goods_sku';

    /**
     * 获取商品缩略图（小）
     */
    public function getSkuImageThumbSmallAttr($value, $data)
    {
        if (isset($data[ 'sku_image' ]) && $data[ 'sku_image' ] != '') {
            return get_thumb_images($data[ 'sku_image' ], FileDict::SMALL);
        }
        return [];
    }

    /**
     * 获取商品缩略图（中）
     */
    public function getSkuImageThumbMidAttr($value, $data)
    {
        if (isset($data[ 'sku_image' ]) && $data[ 'sku_image' ] != '') {
            return get_thumb_images($data[ 'sku_image' ], FileDict::MID);
        }
        return [];
    }

    /**
     * 获取商品缩略图（大）
     */
    public function getSkuImageThumbBigAttr($value, $data)
    {
        if (isset($data[ 'sku_image' ]) && $data[ 'sku_image' ] != '') {
            return get_thumb_images($data[ 'sku_image' ], FileDict::BIG);
        }
        return [];
    }

    /**
     * 搜索器:商品规格商品sku_id
     * @param $value
     * @param $data
     */
    public function searchSkuIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("sku_id", $value);
        }
    }

    /**
     * 搜索器:商品规格商品sku名称
     * @param $value
     * @param $data
     */
    public function searchSkuNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("sku_name", "like", "%" . $value . "%");
        }
    }

    /**
     * 搜索器:商品规格商品sku编码
     * @param $value
     * @param $data
     */
    public function searchSkuNoAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("sku_no", "like", "%" . $value . "%");
        }
    }

    /**
     * 搜索器:商品规格商品id
     * @param $value
     * @param $data
     */
    public function searchGoodsIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("goods_id", 'in', $value);
        }
    }

    /**
     * 搜索器:商品规格sku规格格式
     * @param $value
     * @param $data
     */
    public function searchSkuSpecFormatAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("sku_spec_format", $value);
        }
    }

    /**
     * 搜索器:商品规格sku单价
     * @param $value
     * @param $data
     */
    public function searchPriceAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("price", $value);
        }
    }

    /**
     * 搜索器:商品规格划线价
     * @param $value
     * @param $data
     */
    public function searchMarketPriceAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("market_price", $value);
        }
    }

    /**
     * 搜索器:商品规格实际卖价（有活动显示活动价，默认原价）
     * @param $value
     * @param $data
     */
    public function searchSalePriceAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("sale_price", $value);
        }
    }

    /**
     * 搜索器:商品规格sku成本价
     * @param $value
     * @param $data
     */
    public function searchCostPriceAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("cost_price", $value);
        }
    }

    /**
     * 搜索器:商品规格商品sku库存
     * @param $value
     * @param $data
     */
    public function searchStockAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("stock", $value);
        }
    }

    /**
     * 搜索器:商品规格重量（单位kg）
     * @param $value
     * @param $data
     */
    public function searchWeightAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("weight", $value);
        }
    }

    /**
     * 搜索器:商品规格体积（单位立方米）
     * @param $value
     * @param $data
     */
    public function searchVolumeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("volume", $value);
        }
    }

    /**
     * 搜索器:商品规格销量
     * @param $value
     * @param $data
     */
    public function searchSaleNumAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("sale_num", $value);
        }
    }

    /**
     * 搜索器:商品规格sku主图
     * @param $value
     * @param $data
     */
    public function searchSkuImageAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("sku_image", $value);
        }
    }

    /**
     * 搜索器:商品规格是否默认
     * @param $value
     * @param $data
     */
    public function searchIsDefaultAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("is_default", $value);
        }
    }

    /**
     * 关联商品主表
     * @return \think\model\relation\HasOne
     */
    public function goods()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id')
            ->joinType('left')
            ->withField('goods_id, is_discount, goods_name, goods_type, sub_title, goods_cover, goods_image,goods_desc,brand_id,label_ids,service_ids, unit, stock, sale_num + virtual_sale_num as sale_num, is_limit, limit_type, max_buy, min_buy, status, is_free_shipping, fee_type, delivery_type, delivery_money, delivery_template_id, goods_category,attr_ids,attr_format,member_discount,is_discount,form_id')
            ->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid', 'delivery_type_list', 'goods_image_thumb_small', 'goods_image_thumb_mid', 'goods_image_thumb_big' ]);
    }

    /**
     * 关联商品规格列表
     * @return \think\model\relation\HasMany
     */
    public function skuList()
    {
        return $this->hasMany(GoodsSku::class, 'goods_id', 'goods_id');
    }

    /**
     * 关联商品规格列表
     * @return \think\model\relation\HasMany
     */
    public function goodsSpec()
    {
        return $this->hasMany(GoodsSpec::class, 'goods_id', 'goods_id');
    }

    /**
     * 关联商品主表
     * @return \think\model\relation\HasOne
     */
    public function goodsInfo()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id')
            ->joinType('left');
    }

    /**
     * 关联商品主表（自定义字段）
     * @return \think\model\relation\HasOne
     */
    public function goodsSingle()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id')->joinType('left');
    }

}
