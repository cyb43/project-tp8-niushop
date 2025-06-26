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

namespace addon\shop\app\model\delivery;

use core\base\BaseModel;


/**
 * 同城配送
 * Class Local
 * @package addon\shop\app\model\delivery
 */
class Local extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'local_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_delivery_local_delivery';

    protected $type = [
        'center' => 'json',
        'area' => 'json',
        'delivery_type' => 'json',
        'delivery_time' => 'json'
    ];
}
