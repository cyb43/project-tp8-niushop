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
 * 小票打印机模型
 * Class SysPrinter
 * @package app\model\sys
 */
class SysPrinter extends BaseModel
{


    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'printer_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'sys_printer';

    // 设置json类型字段
    protected $json = [ 'template_type', 'trigger', 'value' ];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 状态字段转化
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getBrandNameAttr($value, $data)
    {
        if (!empty($data[ 'brand' ])) {
            return PrinterDict::getBrandName($data[ 'brand' ]) ?? '';
        }
        return '';
    }

    /**
     * 搜索器:小票打印机
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchPrinterIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("printer_id", $value);
        }
    }

    /**
     * 搜索器:小票打印机设备品牌（易联云，365，飞鹅）
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchBrandAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("brand", $value);
        }
    }

    /**
     * 搜索器:小票打印机打印机名称
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchPrinterNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("printer_name", 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器:小票打印机状态（0，关闭，1：开启）
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where("status", $value);
        }
    }

}
