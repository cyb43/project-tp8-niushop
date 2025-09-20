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

namespace app\model\diy;

use core\base\BaseModel;


/**
 * 自定义主题配色表
 * Class DiyTheme
 * @package app\model\diy
 */
class DiyTheme extends BaseModel
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
    protected $name = 'diy_theme';

    // 设置json类型字段
    protected $json = [ 'default_theme', 'theme', 'new_theme' ];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

}
