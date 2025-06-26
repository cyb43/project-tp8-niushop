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

use app\dict\sys\ExportDict;
use core\base\BaseModel;
use think\db\Query;

/**
 * 导出报表模型
 * Class SysExport
 * @package app\model\sys
 */
class SysExport extends BaseModel
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
    protected $name = 'sys_export';

    /**
     * 状态字段转化
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getExportStatusNameAttr($value, $data)
    {
        if (empty($data['export_status']))
            return '';
        return ExportDict::getStatus()[$data['export_status']] ?? '';
    }

    /**
     * 关键字字段转化
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getExportKeyNameAttr($value, $data)
    {
        if (empty($data['export_key']))
            return '';
        return ExportDict::getExportType()[$data['export_key']]['name'] ?? '';
    }

    /**
     * 主题关键字搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchExportKeyAttr(Query $query, $value, $data)
    {
        if ($value) {
            $query->where('export_key', $value);
        }
    }

    /**
     * 导出状态搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchExportStatusAttr(Query $query, $value, $data)
    {
        if ($value) {
            $query->where('export_status', $value);
        }
    }

    /**
     * 导出时间搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr($query, $value, $data)
    {
        $start_time = empty($value[0]) ? 0 : strtotime($value[0]);
        $end_time = empty($value[1]) ? 0 : strtotime($value[1]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('create_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([['create_time', '>=', $start_time]]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([['create_time', '<=', $end_time]]);
        }
    }

}
