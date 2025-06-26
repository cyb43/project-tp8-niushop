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

namespace app\service\core\diy_form;

use app\model\diy_form\DiyForm;
use app\model\diy_form\DiyFormFields;
use app\model\diy_form\DiyFormRecords;
use app\model\diy_form\DiyFormRecordsFields;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\facade\Db;

/**
 * 万能表单使用记录
 * Class CoreDiyFormRecordsService
 * @package app\service\core\diy
 */
class CoreDiyFormRecordsService extends BaseCoreService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new DiyFormRecords();
    }

    /**
     * 获取使用记录分页列表
     * @param array $data
     * @return array
     */
    public function getPage(array $data = [])
    {
        $where[ 'create_time' ] = $data[ 'create_time' ];
        $member_where = [];
        if (!empty($data[ 'keyword' ])) {
            $member_where[] = [ 'member_no|nickname|username|mobile', 'like', '%' . $data[ 'keyword' ] . '%' ];
        }
        $field = 'record_id, form_id, member_id, relate_id, create_time';
        $order = "create_time desc";
        $search_model = $this->model->where([
            [ 'form_id', '=', $data [ 'form_id' ] ]
        ])->field($field)->withSearch([ 'create_time' ], $where)
            ->withJoin([
                'member' => function($query) use ($member_where) {
                    $query->where($member_where);
                }
            ])->with([
                // 关联表单字段列表 todo 靠后完善
//                'formFieldList' => function($query) {
//                    $query->field('id, form_id, form_field_id, record_id, member_id, field_key, field_type, field_name, field_value, update_num, update_time');
//                },
                // 关联填写字段列表
                'recordsFieldList' => function($query) {
                    $query->field('id, form_id, form_field_id, record_id, member_id, field_key, field_type, field_name, field_value, update_num, update_time')->append([ 'handle_field_value', 'render_value', 'detailComponent' ]);
                }
            ])
            ->order($order);

        return $this->pageQuery($search_model, function($item) {
            $list_key = array_column($item[ 'recordsFieldList' ]->toArray(), 'field_key');
            $recordsFieldList = array_combine($list_key, $item[ 'recordsFieldList' ]->toArray());
            $item[ 'recordsFieldList' ] = $recordsFieldList;
        });
    }

    /**
     * 获取使用记录详情
     * @param array $data
     * @return array
     */
    public function getInfo(array $data = [])
    {
        $field = 'record_id,value';
        $info = $this->model->field($field)->where([ [ 'record_id', '=', $data[ 'record_id' ] ] ])
            ->with([
                // 关联填写字段列表
                'recordsFieldList' => function($query) {
                    $query->field('id, form_id, form_field_id, record_id, member_id, field_key, field_type, field_name, field_value, update_num, update_time')->append([ 'handle_field_value', 'render_value', 'detailComponent' ]);
                }
            ])->findOrEmpty()->toArray();
        if (!empty($info)) {
            $list_key = array_column($info[ 'recordsFieldList' ], 'field_key');
            $recordsFieldList = array_combine($list_key, $info[ 'recordsFieldList' ]);
            $info[ 'recordsFieldList' ] = $recordsFieldList;
        }
        return $info;
    }

    /**
     * 获取使用记录详情
     * @param array $data
     * @return bool
     */
    public function del($data)
    {
        return $this->model->where([ [ 'record_id', '=', $data[ 'record_id' ] ] ])->delete();
    }

    /**
     * 提交填表记录
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try {

            $form_info = ( new DiyForm() )->where([
                [ 'form_id', '=', $data[ 'form_id' ] ]
            ])->field('form_id,status')->findOrEmpty()->toArray();

            // 无效表单id
            if (empty($form_info)) {
                return [];
            }

            // 过滤未启用的表单
            if (!empty($form_info) && $form_info[ 'status' ] == 0) {
                return [];
            }

            Db::startTrans();

            $data[ 'create_time' ] = time();
            //  todo $data[ 'value' ] 考虑过滤存储数据，靠后完善，修改表单可能会用到

            $diy_form_records_fields_model = new DiyFormRecordsFields();

            $form_field_list = ( new DiyFormFields() )->field('field_key,field_required,field_unique')->where([
                [ 'form_id', '=', $data[ 'form_id' ] ]
            ])->select()->toArray();
            if ($form_field_list) {
                $list_key = array_column($form_field_list, 'field_key');
                $form_field_list = array_combine($list_key, $form_field_list);
            }

            $diy_form_records_fields = [];
            if (!empty($data[ 'value' ])) {
//                $value = json_decode($data[ 'value' ], true);
                foreach ($data[ 'value' ] as $component) {

                    // 过滤非表单组件和表单提交按钮组件
                    if ($component[ 'componentType' ] != 'diy_form' || $component[ 'componentName' ] == 'FormSubmit' || $component[ 'isHidden' ]) {
                        continue;
                    }

                    $field_value = $component[ 'field' ][ 'value' ];
                    $check_field_value = $field_value;
                    if (is_array($field_value)) {
                        $check_field_value = $diy_form_records_fields_model->getRenderValueAttr('', [ 'field_value' => json_encode($field_value, JSON_UNESCAPED_UNICODE) , 'field_type' => $component[ 'componentName' ]]);
                    }

                    $form_field_info = $form_field_list[ $component[ 'id' ] ] ?? [];

                    if (!empty($form_field_info)) {
                        if ($form_field_info[ 'field_required' ] == 1 && empty($field_value)) {
                            throw new CommonException(($component[ 'field' ][ 'name' ] ?? $component[ 'componentTitle' ]) . '不能为空');
                        } else if (empty($check_field_value)) {
                            // 过滤空数据
                            continue;
                        }

                        // 检测字段是否重复
                        $field_values = ( new DiyFormRecordsFields() )->where([
                            [ 'form_id', '=', $data[ 'form_id' ] ],
                            [ 'field_key', '=', $component[ 'id' ] ],
                            [ 'field_type', '=', $component[ 'componentName' ] ]
                        ])->column('field_value');
                        if ($form_field_info[ 'field_unique' ] == 1 && in_array($field_value, $field_values)) {
                            throw new CommonException(($component[ 'field' ][ 'name' ] ?? $component[ 'componentTitle' ]) . '不能重复');
                        }
                    } else if (empty($check_field_value)) {
                        // 过滤空数据
                        continue;
                    }

                    if (is_array($field_value)) {
                        $field_value = json_encode($field_value, JSON_UNESCAPED_UNICODE);
                    }

                    $diy_form_records_fields[] = [
                        'form_id' => $data[ 'form_id' ], // 所属万能表单id
//                    'form_field_id'=>'', // todo 暂无，靠后完善
//                        'record_id' => $res->record_id, // 关联表单填写记录id
                        'member_id' => $data[ 'member_id' ], // 填写会员id
                        'field_key' => $component[ 'id' ], // 字段唯一标识
                        'field_type' => $component[ 'componentName' ], // 字段类型
                        'field_name' => $component[ 'field' ][ 'name' ] ?? '', // 字段名称
//                        'field_remark' => $component[ 'field' ][ 'remark' ][ 'text' ] ?? '', // 字段说明
                        'field_value' => $field_value, // 字段值
                        'field_required' => $component[ 'field' ][ 'required' ], // 字段是否必填 0:否 1:是
                        'field_hidden' => $component[ 'isHidden' ], // 字段是否隐藏 0:否 1:是
                        'field_unique' => $component[ 'field' ][ 'unique' ], // 字段内容防重复 0:否 1:是
                        'privacy_protection' => $component[ 'field' ][ 'privacyProtection' ], // 隐私保护 0:关闭 1:开启
                        'create_time' => time(),
                        'update_time' => time()
                    ];
                }

            }
            if (!empty($diy_form_records_fields)) {
                $res = $this->model->create($data);
                foreach ($diy_form_records_fields as &$item){
                    $item['record_id'] = $res->record_id;
                }
                $diy_form_records_fields_model->insertAll($diy_form_records_fields);

                $diy_form = new DiyForm();
                // 累计填写数量
                $diy_form->where([ [ 'form_id', '=', $data[ 'form_id' ] ] ])->inc('write_num', 1)->update();

                $diy_form_fields_model = new DiyFormFields();
                foreach ($diy_form_records_fields as $field) {
                    // 字段累计填写数量
                    $diy_form_fields_model->where([ [ 'form_id', '=', $data[ 'form_id' ] ], [ 'field_key', '=', $field[ 'field_key' ] ] ])->inc('write_num', 1)->update();
                }
            }

            Db::commit();
            return $res->record_id ?? 0;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 编辑填表记录
     * @param $data
     * @return mixed
     */
    public function edit($data)
    {
        try {

            $form_info = ( new DiyForm() )->where([
                [ 'form_id', '=', $data[ 'form_id' ] ],
            ])->field('form_id,status')->findOrEmpty()->toArray();

            // 无效表单id
            if (empty($form_info)) {
                throw new CommonException('DIY_FORM_NOT_EXIST');
            }

            // 过滤未启用的表单
            if (!empty($form_info) && $form_info[ 'status' ] == 0) {
                throw new CommonException('DIY_FORM_NOT_OPEN');
            }

            Db::startTrans();

            //  todo $data[ 'value' ] 考虑过滤存储数据，靠后完善，修改表单可能会用到
            $res = $this->model->where([['record_id', '=', $data['record_id']]])->update($data);

            $diy_form_records_fields_model = new DiyFormRecordsFields();

            $form_field_list = ( new DiyFormFields() )->field('field_key,field_required,field_unique')->where([
                [ 'form_id', '=', $data[ 'form_id' ] ],
            ])->select()->toArray();
            if ($form_field_list) {
                $list_key = array_column($form_field_list, 'field_key');
                $form_field_list = array_combine($list_key, $form_field_list);
            }

            $diy_form_records_fields = [];
            if (!empty($data[ 'value' ])) {
//                $value = json_decode($data[ 'value' ], true);
                foreach ($data[ 'value' ] as $component) {

                    // 过滤非表单组件和表单提交按钮组件
                    $is_hidden = $component[ 'isHidden' ] ?? 0;
                    if ($component[ 'componentType' ] != 'diy_form' || $component[ 'componentName' ] == 'FormSubmit' || $is_hidden) {
                        continue;
                    }

                    $field_value = $component[ 'field' ][ 'value' ];
                    $check_field_value = $field_value;
                    if (is_array($field_value)) {
                        $check_field_value = $diy_form_records_fields_model->getRenderValueAttr('', [ 'field_value' => json_encode($field_value, JSON_UNESCAPED_UNICODE) , 'field_type' => $component[ 'componentName' ]]);
                    }

                    $form_field_info = $form_field_list[ $component[ 'id' ] ] ?? [];

                    if (!empty($form_field_info)) {
                        if ($form_field_info[ 'field_required' ] == 1 && empty($field_value)) {
                            throw new CommonException(($component[ 'field' ][ 'name' ] ?? $component[ 'componentTitle' ]) . '不能为空');
                        } else if (empty($check_field_value)) {
                            // 过滤空数据
                            continue;
                        }

                        // 检测字段是否重复
                        $field_values = ( new DiyFormRecordsFields() )->where([
                            [ 'form_id', '=', $data[ 'form_id' ] ],
                            [ 'field_key', '=', $component[ 'id' ] ],
                            [ 'field_type', '=', $component[ 'componentName' ] ],
                            [ 'record_id', '<>', $data[ 'record_id' ] ],
                        ])->column('field_value');
                        if ($form_field_info[ 'field_unique' ] == 1 && in_array($field_value, $field_values)) {
                            throw new CommonException(($component[ 'field' ][ 'name' ] ?? $component[ 'componentTitle' ]) . '不能重复');
                        }
                    } else if (empty($check_field_value)) {
                        // 过滤空数据
                        continue;
                    }

                    if (is_array($field_value)) {
                        $field_value = json_encode($field_value, JSON_UNESCAPED_UNICODE);
                    }

                    $diy_form_records_fields[] = [
                        'form_id' => $data[ 'form_id' ], // 所属万能表单id
//                    'form_field_id'=>'', // todo 暂无，靠后完善
                        'record_id' => $data[ 'record_id' ], // 关联表单填写记录id
                        'member_id' => $data[ 'member_id' ], // 填写会员id
                        'field_key' => $component[ 'id' ], // 字段唯一标识
                        'field_type' => $component[ 'componentName' ], // 字段类型
                        'field_name' => $component[ 'field' ][ 'name' ] ?? '', // 字段名称
//                        'field_remark' => $component[ 'field' ][ 'remark' ][ 'text' ] ?? '', // 字段说明
                        'field_value' => $field_value, // 字段值
                        'field_required' => $component[ 'field' ][ 'required' ] ?? 0, // 字段是否必填 0:否 1:是
                        'field_hidden' => $component[ 'isHidden' ] ?? 0, // 字段是否隐藏 0:否 1:是
                        'field_unique' => $component[ 'field' ][ 'unique' ] ?? 0, // 字段内容防重复 0:否 1:是
                        'privacy_protection' => $component[ 'field' ][ 'privacyProtection' ] ?? 0, // 隐私保护 0:关闭 1:开启
                        'create_time' => time(),
                        'update_time' => time()
                    ];
                }

            }

            if (!empty($diy_form_records_fields)) {
                $diy_form_fields_model = new DiyFormFields();
                $diy_form_records_fields_list = $diy_form_records_fields_model->where([ [ 'form_id', '=', $data[ 'form_id' ] ], [ 'record_id', '=', $data['record_id' ] ], [ 'member_id', '=', $data['member_id' ] ] ])->select()->toArray();
                if (!empty($diy_form_records_fields_list)) {
                    foreach ($diy_form_records_fields_list as $field) {
                        // 字段累计填写数量
                        $diy_form_fields_model->where([ [ 'form_id', '=', $data[ 'form_id' ] ], [ 'field_key', '=', $field[ 'field_key' ] ] ])->dec('write_num', 1)->update();
                    }
                    $diy_form_records_fields_model->where([ [ 'form_id', '=', $data[ 'form_id' ] ], [ 'record_id', '=', $data['record_id' ] ], [ 'member_id', '=', $data['member_id' ] ] ])->delete();
                }
                $diy_form_records_fields_model->insertAll($diy_form_records_fields);

                foreach ($diy_form_records_fields as $field) {
                    // 字段累计填写数量
                    $diy_form_fields_model->where([ [ 'form_id', '=', $data[ 'form_id' ] ], [ 'field_key', '=', $field[ 'field_key' ] ] ])->inc('write_num', 1)->update();
                }
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }
}
