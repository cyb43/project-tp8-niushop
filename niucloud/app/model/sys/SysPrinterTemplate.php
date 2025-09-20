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

namespace app\model\sys;

use app\dict\sys\PrinterDict;
use core\base\BaseModel;

/**
 * 小票打印模板模型
 * Class SysPrinterTemplate
 * @package app\model\sys
 */
class SysPrinterTemplate extends BaseModel
{


    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'template_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'sys_printer_template';

    // 设置json类型字段
    protected $json = [ 'value' ];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 状态字段转化
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getTemplateTypeNameAttr($value, $data)
    {
        if (!empty($data[ 'template_type' ])) {
            return PrinterDict::getType($data[ 'template_type' ])[ 'title' ] ?? '';
        }
        return '';
    }

    /**
     * 搜索器:小票打印模板
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTemplateIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("template_id", $value);
        }
    }

    /**
     * 搜索器:小票打印模板模板类型
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTemplateTypeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("template_type", $value);
        }
    }

    /**
     * 搜索器:小票打印模板模板名称
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTemplateNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("template_name", 'like', '%' . $value . '%');
        }
    }

}
