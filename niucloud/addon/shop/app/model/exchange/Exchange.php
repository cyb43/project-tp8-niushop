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

namespace addon\shop\app\model\exchange;


use addon\shop\app\dict\active\ExchangeDict;
use addon\shop\app\model\goods\Brand;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\Label;
use app\dict\sys\FileDict;
use core\base\BaseModel;
use think\db\Query;

/**
 * 营销活动模型
 */
class Exchange extends BaseModel
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
    protected $name = 'shop_point_exchange';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    // 设置json类型字段
    protected $json = ['product_detail'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;


    /**
     * 搜索器商品名称
     * @param $value
     * @param $data
     */
    public function searchNamesAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("names", 'like', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }

    /**
     * 搜索器ids
     * @param $value
     * @param $data
     */
    public function searchIdsAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("id", 'in', $value);
        }
    }

    /**
     * 搜索器商品sku 查询订单使用
     * @param $value
     * @param $data
     */
    public function searchProductDetailAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("product_detail", 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器商品sku 查询sku
     * @param $value
     * @param $data
     */
    public function searchSkuIdAttr($query, $value, $data)
    {

        if ($value) {
            $query->where('product_detail', 'like', '%' . '"sku_id":' . $value . ',' . '%');
        }
    }

    /**
     * 搜索器商品sku 查询sku
     * @param $value
     * @param $data
     */
    public function searchGoodsIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('product_detail', 'like', '%' . '"goods_id":' . $value . ',' . '%');
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
     * 搜索器:ID in 查询
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("id", 'in', $value);
        }
    }

    /**
     * 活动商品类型
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getTypeNameAttr($value, $data)
    {
        if (empty($data['type'])) {
            return '';
        }
        return ExchangeDict::getType()[$data['type']] ?? '';
    }

    /**
     * 活动状态
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        if (empty($data['status']) && $data['status'] != 0) {
            return '';
        }
        return ExchangeDict::getStatus()[$data['status']] ?? '';
    }

    /**
     * 获取封面缩略图（小）
     */
    public function getGoodsCoverThumbSmallAttr($value, $data)
    {
        $data['goods_cover'] = explode(',', $data['image'])[0];
        if (isset($data['goods_cover']) && $data['goods_cover'] != '') {
            return get_thumb_images($data['goods_cover'], FileDict::SMALL);
        }
        return [];
    }

    /**
     * 获取封面缩略图（中）
     */
    public function getGoodsCoverThumbMidAttr($value, $data)
    {
        $data['goods_cover'] = explode(',', $data['image'])[0];
        if (isset($data['goods_cover']) && $data['goods_cover'] != '') {
            return get_thumb_images($data['goods_cover'], FileDict::MID);
        }
        return [];
    }

    /**
     * 获取封面缩略图（大）
     */
    public function getGoodsCoverThumbBigAttr($value, $data)
    {
        $data['goods_cover'] = explode(',', $data['image'])[0];
        if (isset($data['goods_cover']) && $data['goods_cover'] != '') {
            return get_thumb_images($data['goods_cover'], FileDict::BIG);
        }
        return [];
    }

    /**
     * 获取商品图片缩略图（小）
     */
    public function getGoodsImageThumbSmallAttr($value, $data)
    {
        if (isset($data['image']) && $data['image'] != '') {
            $goods_image = explode(',', $data['image']);
            $img_arr = [];
            foreach ($goods_image as $k => $v) {
                $img = get_thumb_images($v, FileDict::SMALL);
                if (!empty($img)) {
                    $img_arr[] = $img;
                }
            }
            return $img_arr;
        }
        return [];
    }

    /**
     * 获取商品图片缩略图（中）
     */
    public function getGoodsImageThumbMidAttr($value, $data)
    {
        if (isset($data['image']) && $data['image'] != '') {
            $goods_image = explode(',', $data['image']);
            $img_arr = [];
            foreach ($goods_image as $k => $v) {
                $img = get_thumb_images($v, FileDict::MID);
                if (!empty($img)) {
                    $img_arr[] = $img;
                }
            }
            return $img_arr;
        }
        return [];
    }

    /**
     * 获取商品图片缩略图（大）
     */
    public function getGoodsImageThumbBigAttr($value, $data)
    {
        if (isset($data['image']) && $data['image'] != '') {
            $goods_image = explode(',', $data['image']);
            $img_arr = [];
            foreach ($goods_image as $k => $v) {
                $img = get_thumb_images($v, FileDict::BIG);
                if (!empty($img)) {
                    $img_arr[] = $img;
                }
            }
            return $img_arr;
        }
        return [];
    }

    public function getGoodsBrandAttr($value, $data)
    {
        $product_detail = !is_array($data['product_detail']) ? json_decode($data['product_detail'], true) : $data['product_detail'];
        $goods_id = $product_detail[0]['goods_id'];
        $brand_id = (new Goods())->where([
            ['goods_id', '=', $goods_id],
        ])->value('brand_id') ?? 0;
        $brand = (new Brand())->where([['brand_id', '=', $brand_id]])->field('brand_name,brand_id,color_json,logo')->findOrEmpty();
        return $brand->isEmpty() ? null : $brand->toArray();
    }

    public function getGoodsLabelAttr($value, $data)
    {
        $product_detail = !is_array($data['product_detail']) ? json_decode($data['product_detail'], true) : $data['product_detail'];
        $goods_id = $product_detail[0]['goods_id'];
        $label_ids = (new Goods())->where([
            ['goods_id', '=', $goods_id],
        ])->value('label_ids') ?? [];
        return (new Label())->where([ ['label_id', 'in', $label_ids]])->field('label_id,label_name,color_json,style_type,icon')->select()->toArray();

    }

    /**
     * 搜索器:id
     * @param $value
     * @param $data
     */
    public function searchActiveIdAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->where("id", 'in', $value);
        }
    }

}
