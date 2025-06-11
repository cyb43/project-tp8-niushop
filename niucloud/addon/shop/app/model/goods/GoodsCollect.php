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

use core\base\BaseModel;
use think\model\relation\HasOne;


/**
 * 商品收藏记录
 * Class GoodsCollect
 * @package addon\shop\app\model\goods
 */
class GoodsCollect extends BaseModel
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
    protected $name = 'shop_goods_collect';

    /**
     * 商品信息
     * @return HasOne
     */
    public function goods()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id')->joinType('left')->withField('goods_id, goods_name,goods_cover,status')->append([ 'goods_cover_thumb_mid' ])->bind([ 'goods_name', 'goods_cover_thumb_mid','status' ]);
    }

    /**
     * 关联默认商品规格
     * @return \think\model\relation\HasOne
     */
    public function goodsSku()
    {
        return $this->hasOne(GoodsSku::class, 'goods_id', 'goods_id')->joinType('left')->withField('goods_id,sku_id,sku_name,price,member_price,sale_price')->bind([ 'sku_id', 'sku_name', 'price', 'member_price', 'sale_price' ]);
    }

}
