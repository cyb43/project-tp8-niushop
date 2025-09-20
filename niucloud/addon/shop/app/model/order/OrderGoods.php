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

use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use app\dict\sys\FileDict;
use app\model\member\Member;
use core\base\BaseModel;
use Exception;
use think\model\concern\SoftDelete;
use think\model\relation\HasOne;

/**
 * 订单模型
 */
class OrderGoods extends BaseModel
{
    use SoftDelete;
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'order_goods_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_order_goods';

    //类型
    protected $type = [
        'verify_expire_time' => 'timestamp',
    ];


    // 设置json类型字段
    protected $json = ['extend'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 定义软删除标记字段.
     * @var string
     */
    protected $deleteTime = 'delete_time';

    protected $defaultSoftDelete = 0;

    /**
     * 包裹
     * @return HasOne
     */
    public function orderDelivery()
    {
        return $this->hasOne(OrderDelivery::class, 'id', 'delivery_id');
    }

    /**
     * 商品
     * @return HasOne
     */
    public function goods()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id');
    }
    /**
     * 商品
     * @return HasOne
     */
    public function sku()
    {
        return $this->hasOne(GoodsSku::class, 'sku_id', 'sku_id');
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
     * 会员
     * @return HasOne
     */
    public function member()
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id');
    }  /**
     * 会员
     * @return HasOne
     */
    public function deliveryInfo()
    {
        return $this->hasOne(OrderDelivery::class, 'id', 'delivery_id')->append(['express_company_name']);
    }

    /**
     * 缩略图生成-大图
     * @param $value
     * @param $data
     * @return array|mixed
     * @throws Exception
     */
    public function getGoodsImageThumbBigAttr($value, $data)
    {
        if (!empty($data['goods_image'])) {
            return get_thumb_images($data['goods_image'], FileDict::BIG);
        }
        return '';
    }

    /**
     * 缩略图生成-中图
     * @param $value
     * @param $data
     * @return array|mixed
     * @throws Exception
     */
    public function getGoodsImageThumbMidAttr($value, $data)
    {
        if (!empty($data['goods_image'])) {
            return get_thumb_images($data['goods_image'], FileDict::MID);
        }
        return '';
    }

    /**
     * 缩略图生成-小图
     * @param $value
     * @param $data
     * @return array|mixed
     * @throws Exception
     */
    public function getGoodsImageThumbSmallAttr($value, $data)
    {
        if (!empty($data['goods_image'])) {
            return get_thumb_images($data['goods_image'], FileDict::SMALL);
        }
        return '';
    }

    /**
     * 缩略图生成-中图
     * @param $value
     * @param $data
     * @return array|mixed
     * @throws Exception
     */
    public function getSkuImageThumbMidAttr($value, $data)
    {
        if (!empty($data['sku_image'])) {
            return get_thumb_images($data['sku_image'], FileDict::MID);
        }
        return '';
    }

    /**
     * 转化发货状态
     */
    public function getDeliveryStatusNameAttr($value, $data)
    {
        if (!empty($data['delivery_status'])) {
            return OrderDeliveryDict::getStatus($data['delivery_status']);
        }
        return '';
    }

    /**
     * 转化订单项状态
     */
    public function getStatusNameAttr($value, $data)
    {
        if (!empty($data['status'])) {
            return OrderGoodsDict::getRefundStatus($data['status']);
        }
        return '';
    }

    /**
     * 转化订单项商品类型
     */
    public function getGoodsTypeNameAttr($value, $data)
    {
        if (!empty($data['goods_type'])) {
            return GoodsDict::getType($data['goods_type'])['name'];
        }
        return '';
    }

    public function getImpulseBuyInfoAttr($value, $data)
    {
        if(!empty($data['extend']) && isset($data['extend']['is_impulse_buy']) && $data['extend']['is_impulse_buy'] == 1){
            $impulse_buy_price = $data['extend']['impulse_buy_price']/$data['extend']['impulse_buy_goods_num'];
            $is_impulse_buy = 1;
        }
        return [
            'is_impulse_buy'=>$is_impulse_buy ?? 0 ,
            'impulse_buy_price'=>$impulse_buy_price ?? 0 ,
        ];
    }

}
