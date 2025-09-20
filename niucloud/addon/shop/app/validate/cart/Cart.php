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

namespace addon\shop\app\validate\cart;

use core\base\BaseValidate;

/**
 * 购物车验证器
 * Class Cart
 * @package addon\shop\app\validate\cart
 */
class Cart extends BaseValidate
{

    protected $rule = [
        'id' => 'require|gt:0',
        'goods_id' => 'require|gt:0',
        'sku_id' => 'require|gt:0',
        'num' => 'require',
    ];

    protected $message = [
        'goods_id.require' => [ 'common_validate.require', [ 'goods_id' ] ],
        'sku_id.require' => [ 'common_validate.require', [ 'sku_id' ] ],
        'num.require' => [ 'common_validate.require', [ 'num' ] ],
    ];

    protected $scene = [
        "add" => [ 'member_id', 'goods_id', 'sku_id', 'num', 'status', 'invalid_remark' ],
        "edit" => [ 'member_id', 'sku_id', 'num', 'status', 'invalid_remark' ]
    ];

}
