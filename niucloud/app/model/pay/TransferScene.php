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

namespace app\model\pay;

use core\base\BaseModel;

/**
 * 微信转账场景模型
 * Class Order
 * @package app\model\order
 */
class TransferScene extends BaseModel
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
    protected $name = 'pay_transfer_scene';

    //类型
    protected $type = [
    ];

    // 设置json类型字段
    protected $json = [ 'infos'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;


}
