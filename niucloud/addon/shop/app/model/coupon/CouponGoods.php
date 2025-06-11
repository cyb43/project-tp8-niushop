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

use addon\shop\app\model\goods\Goods;
use core\base\BaseModel;

/**
 * 优惠券商品关联模型
 */
class CouponGoods extends BaseModel
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
    protected $name = 'shop_coupon_goods';

    public function goods()
    {
        return $this->hasOne(Goods::class , 'goods_id', 'goods_id');
    }

}
