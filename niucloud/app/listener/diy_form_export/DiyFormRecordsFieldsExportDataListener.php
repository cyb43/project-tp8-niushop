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

namespace app\listener\diy_form_export;

use app\model\diy_form\DiyFormRecordsFields;
use app\service\admin\diy_form\DiyFormService;

/**
 * 表单填表人统计导出数据源查询
 * Class DiyFormRecordsMemberExportDataListener
 * @package app\listener\diy_form_export
 */
class DiyFormRecordsFieldsExportDataListener
{

    public function handle($param)
    {
        $data = [];
        if ($param['type'] == 'diy_form_records_fields') {
            $where = $param[ 'where' ];
            $field_list = ( new DiyFormService() )->getFieldsList($where, 'field_id, field_key, field_type, field_name');
            $simple_field_list = array_filter($field_list, function($v) { return !in_array($v[ 'field_type' ], [ 'FormRadio', 'FormCheckbox', 'FormDateScope', 'FormTimeScope', 'FormImage' ]); });
            $json_field_list = array_filter($field_list, function($v) { return in_array($v[ 'field_type' ], [ 'FormRadio', 'FormCheckbox', 'FormDateScope', 'FormTimeScope' ]); });

            $records_field_model = new DiyFormRecordsFields();
            foreach ($simple_field_list as $k => $v) {
                $value_list = $records_field_model->field('form_id, field_key, field_type, field_name, field_value, count(*) as write_count')->where([
                    [ 'field_key', '=', $v[ 'field_key' ] ],
                    [ 'field_type', '=', $v[ 'field_type' ] ]
                ])->withSearch([ 'form_id' ], $where)->group('field_value')->append([ 'render_value' ])->select()->toArray();
                $total_count = $records_field_model->where([
                    [ 'field_key', '=', $v[ 'field_key' ] ],
                    [ 'field_type', '=', $v[ 'field_type' ] ]
                ])->withSearch([ 'form_id' ], $where)->count();
                if ($total_count > 0) {
                    $total_percent = 100;
                    foreach ($value_list as $k1 => &$v1) {
                        if ($k1 == count($value_list) - 1) {
                            $item_percent = $total_percent;
                        } else {
                            $item_percent = round($v1[ 'write_count' ] / $total_count * 100, 2);
                        }
                        $v1[ 'write_percent' ] = floatval($item_percent);
                        $total_percent = bcsub($total_percent, $item_percent, 2);
                    }
                }
                $data = array_merge($data, $value_list);
            }
            foreach ($json_field_list as $k => $v) {
                $field_list = $records_field_model->field('form_id, field_key, field_type, field_name, field_value')->where([
                    [ 'field_key', '=', $v[ 'field_key' ] ],
                    [ 'field_type', '=', $v[ 'field_type' ] ]
                ])->withSearch([ 'form_id' ], $where)->append([ 'render_value' ])->select()->toArray();

                $total_count = 0;
                $value_list = [];
                foreach ($field_list as $k1 => $v1) {
                    if ($v1[ 'field_type' ] != 'FormCheckbox') {
                        $key = $v1[ 'field_key' ] . '_' . $v1[ 'render_value' ];
                        if (isset($value_list[ $key ])) {
                            $value_list[ $key ][ 'write_count' ] = $value_list[ $key ][ 'write_count' ] + 1;
                            $total_count++;
                        } else {
                            // 如果不存在，则初始化为1
                            $value_list[ $key ] = $v1;
                            $value_list[ $key ][ 'write_count' ] = 1;
                            $total_count++;
                        }
                    } else {
                        $value_arr = explode(',', $v1[ 'render_value' ]);
                        foreach ($value_arr as $k2 => $v2) {
                            $key = $v1[ 'field_key' ] . '_' . $v2;
                            if (isset($value_list[ $key ])) {
                                $value_list[ $key ][ 'write_count' ] = $value_list[ $key ][ 'write_count' ] + 1;
                                $total_count++;
                            } else {
                                $value_list[ $key ] = $v1;
                                $value_list[ $key ][ 'render_value' ] = $v2;
                                $value_list[ $key ][ 'write_count' ] = 1;
                                $total_count++;
                            }
                        }
                    }
                }
                if ($total_count > 0) {
                    $value_list = array_values($value_list);
                    $total_percent = 100;
                    foreach ($value_list as $k1 => &$v1) {
                        if ($k1 == count($value_list) - 1) {
                            $item_percent = $total_percent;
                        } else {
                            $item_percent = round($v1[ 'write_count' ] / $total_count * 100, 2);
                        }
                        $v1[ 'write_percent' ] = floatval($item_percent);
                        $total_percent = bcsub($total_percent, $item_percent, 2);
                    }
                }
                $data = array_merge($data, $value_list);
            }
            foreach ($data as $k => &$v) {
                $v[ 'render_value' ] = $v[ 'render_value' ] . "\t";
            }
            $data = array_values($data);
        }
        return $data;
    }
}