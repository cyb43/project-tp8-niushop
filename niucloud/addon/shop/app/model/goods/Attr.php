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

/**
 * 商品参数模型
 * Class Attr
 * @package addon\shop\app\model\goods
 */
class Attr extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'attr_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_goods_attr';


    /**
     * 搜索器:商品参数商品参数id
     * @param $value
     * @param $data
     */
    public function searchAttrIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("attr_id", $value);
        }
    }


    /**
     * 搜索器:商品参数商品参数id
     * @param $value
     * @param $data
     */
    public function searchAttrIdArrAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("attr_id", 'in', $value);
        }
    }

    /**
     * 搜索器:商品参数参数名称
     * @param $value
     * @param $data
     */
    public function searchAttrNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("attr_name", 'like', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }

}
