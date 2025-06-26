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

namespace app\service\api\diy_form;

use app\model\diy_form\DiyForm;
use app\model\diy_form\DiyFormRecords;
use app\model\diy_form\DiyFormWriteConfig;
use app\model\member\Member;
use app\model\member\MemberLabel;
use app\model\member\MemberLevel;
use app\service\core\diy_form\CoreDiyFormRecordsService;
use app\service\core\member\CoreMemberConfigService;
use core\base\BaseApiService;
use core\exception\ApiException;

/**
 * 万能表单服务层
 * Class DiyFormService
 * @package app\service\api\diy
 */
class DiyFormService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new DiyForm();
    }

    /**
     * 获取万能表单信息
     * @param int $form_id
     * @param string $field
     * @return mixed
     */
    public function getInfo(int $form_id, $field = 'form_id, page_title, title, type, status, value, share, remark')
    {
        $write_config = ( new DiyFormWriteConfig() )->where([
            [ 'form_id', '=', $form_id ]
        ])->findOrEmpty()->toArray();
        $error = [];
        $info = $this->model->field($field)->where([ [ 'form_id', '=', $form_id ], [ 'status', '=', 1 ] ])->findOrEmpty()->toArray();
        if (!empty($info)) {
            if (!empty($info[ 'value' ][ 'value' ])) {
                foreach ($info[ 'value' ][ 'value' ] as $k => $v) {
                    if ($v[ 'isHidden' ]) {
                        unset($info[ 'value' ][ 'value' ][ $k ]); // 过滤隐藏的组件
                    }
                }
                $info[ 'value' ][ 'value' ] = array_values($info[ 'value' ][ 'value' ]);
            }
            if (!empty($write_config) && !empty($this->member_id)) {
                if ($error_msg = $this->checkMemberCanJoinOrNot($this->member_id, $write_config)) {
                    $error[] = $error_msg;
                }
                if ($error_msg = $this->checkFormWriteTime($write_config)) {
                    $error[] = $error_msg;
                }
                if ($error_msg = $this->checkFormWriteLimitNum($form_id, $write_config)) {
                    $error[] = $error_msg;
                }
                if ($error_msg = $this->checkMemberWriteLimitNum($this->member_id, $form_id, $write_config)) {
                    $error[] = $error_msg;
                }
            }
        } else {
            $error[] = [
                'title' => '当前表单无法查看',
                'type' => '表单状态',
                'desc' => '该表单已关闭'
            ];
        }
        $info[ 'error' ] = $error;
        return $info;
    }

    /**
     * 判断当前会员是否可以填写表单
     * @param int $member_id
     * @param array $config
     * @return array
     */
    public function checkMemberCanJoinOrNot($member_id, $config)
    {
        $member_info = ( new Member() )->where([ [ 'member_id', '=', $member_id ] ])->field('member_level,member_label')->findOrEmpty()->toArray();
        if (empty($member_info)) throw new ApiException('MEMBER_NOT_EXIST');
        $error = [];
        switch ($config[ 'join_member_type' ]) {
            case 'all_member':
                break;
            case 'selected_member_level':
                if (!in_array($member_info[ 'member_level' ], $config[ 'level_ids' ])) {
                    $level_names = ( new MemberLevel() )->where([ [ 'level_id', 'in', $config[ 'level_ids' ] ] ])->column('level_name');
                    $error = [
                        'title' => '当前表单无法查看',
                        'type' => '允许填写用户',
                        'desc' => '该表单已设置仅限“' . implode('、', $level_names) . '等级”的用户填写'
                    ];
                }
                break;
            case 'selected_member_label':
                if (empty($member_info[ 'member_label' ])) $member_info[ 'member_label' ] = [];
                if (empty(array_intersect($member_info[ 'member_label' ], $config[ 'label_ids' ]))) {
                    $label_names = ( new MemberLabel() )->where([ [ 'label_id', 'in', $config[ 'label_ids' ] ] ])->column('label_name');
                    $error = [
                        'title' => '当前表单无法查看',
                        'type' => '允许填写用户',
                        'desc' => '该表单已设置仅限“' . implode('、', $label_names) . '标签”的用户填写'
                    ];
                }
                break;
        }
        return $error;
    }

    /**
     * 检查会员填写表单次数限制
     * @return array
     */
    public function checkMemberWriteLimitNum($member_id, $form_id, $config)
    {
        $form_records_model = new DiyFormRecords();
        $error = [];
        switch ($config[ 'member_write_type' ]) {
            case 'no_limit':
                break;
            case 'diy':
                $member_write_rule = $config[ 'member_write_rule' ];
                switch ($member_write_rule[ 'time_unit' ]) {
                    case 'day':
                        $time_text = '天';
                        break;
                    case 'week':
                        $time_text = '周';
                        break;
                    case 'month':
                        $time_text = '月';
                        break;
                    case 'year':
                        $time_text = '年';
                        break;
                }
                $count = $form_records_model->where([
                    [ 'form_id', '=', $form_id ],
                    [ 'member_id', '=', $member_id ]
                ])->whereTime('create_time', '-' . $member_write_rule[ 'time_value' ] . ' ' . $member_write_rule[ 'time_unit' ])->count();
                if ($count >= $member_write_rule[ 'num' ]) {
                    $error = [
                        'title' => '您的填写次数已达上限',
                        'type' => '允许填写次数(每人)',
                        'desc' => '该表单已设置“每人每' . $member_write_rule[ 'time_value' ] . $time_text . '可填写' . $member_write_rule[ 'num' ] . '次”'
                    ];
                }
                break;
        }
        return $error;
    }

    /**
     * 检查表单填写次数限制
     * @return array
     */
    public function checkFormWriteLimitNum($form_id, $config)
    {
        $form_records_model = new DiyFormRecords();
        $error = [];
        switch ($config[ 'form_write_type' ]) {
            case 'no_limit':
                break;
            case 'diy':
                $form_write_rule = $config[ 'form_write_rule' ];
                switch ($form_write_rule[ 'time_unit' ]) {
                    case 'day':
                        $time_text = '天';
                        break;
                    case 'week':
                        $time_text = '周';
                        break;
                    case 'month':
                        $time_text = '月';
                        break;
                    case 'year':
                        $time_text = '年';
                        break;
                }
                $count = $form_records_model->where([
                    [ 'form_id', '=', $form_id ]
                ])->whereTime('create_time', '-' . $form_write_rule[ 'time_value' ] . ' ' . $form_write_rule[ 'time_unit' ])->count();
                if ($count >= $form_write_rule[ 'num' ]) {
                    $error = [
                        'title' => '表单总填写次数已达上限',
                        'type' => '允许填写次数(总)',
                        'desc' => '该表单已设置“每' . $form_write_rule[ 'time_value' ] . $time_text . '可填写' . $form_write_rule[ 'num' ] . '次”'
                    ];
                }
                break;
        }
        return $error;
    }

    /**
     * 检查表单填写时间限制
     * @param array $config
     * @return array
     */
    public function checkFormWriteTime($config)
    {
        $error = [];
        switch ($config[ 'time_limit_type' ]) {
            case 'no_limit':
                break;
            case 'specify_time':
                $specify_time = $config[ 'time_limit_rule' ][ 'specify_time' ] ?? [];
                if (!empty($specify_time)) {
                    if (time() < $specify_time[ 0 ] || time() > $specify_time[ 1 ]) {
                        $error = [
                            'title' => '当前时间无法查看',
                            'type' => '允许查看时间',
                            'desc' => '该表单已设置“' . date('Y-m-d H:i:s', $specify_time[ 0 ]) . '-' . date('Y-m-d H:i:s', $specify_time[ 1 ]) . '”可查看'
                        ];
                    }
                }
                break;
            case 'open_day_time':
                $open_day_time = $config[ 'time_limit_rule' ][ 'open_day_time' ] ?? [];
                if (!empty($open_day_time)) {
                    $start_time = strtotime(date('Y-m-d', time())) + $open_day_time[ 0 ];
                    $end_time = strtotime(date('Y-m-d', time())) + $open_day_time[ 1 ];
                    if (time() < $start_time || time() > $end_time) {
                        $error = [
                            'title' => '当前时间无法查看',
                            'type' => '允许查看时间',
                            'desc' => '该表单已设置“每天' . date('H:i', $start_time) . '-' . date('H:i', $end_time) . '”可查看'
                        ];
                    }
                }
                break;
        }
        return $error;
    }

    /**
     * 提交填表记录
     * @param array $data
     * @return array
     */
    public function addRecord(array $data = [])
    {
        $data[ 'member_id' ] = $this->member_id;

        $info = $this->model->field('status')->where([ [ 'form_id', '=', $data[ 'form_id' ] ] ])->findOrEmpty()->toArray();
        if (empty($info)) throw new ApiException('DIY_FORM_NOT_EXIST');
        if ($info[ 'status' ] == 0) throw new ApiException('DIY_FORM_NOT_OPEN');

        $write_config = ( new DiyFormWriteConfig() )->where([
            [ 'form_id', '=', $data[ 'form_id' ] ]
        ])->findOrEmpty()->toArray();
        if (!empty($write_config)) {
            if ($error_msg = $this->checkMemberCanJoinOrNot($this->member_id, $write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
            if ($error_msg = $this->checkFormWriteTime($write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
            if ($error_msg = $this->checkFormWriteLimitNum($data[ 'form_id' ], $write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
            if ($error_msg = $this->checkMemberWriteLimitNum($this->member_id, $data[ 'form_id' ], $write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
        }
        return ( new CoreDiyFormRecordsService() )->add($data);
    }

    /**
     * 编辑填表记录
     * @param array $data
     * @return array
     */
    public function editRecord(array $data = [])
    {
        $diy_form_records_model = new DiyFormRecords();
        $records_info = $diy_form_records_model->field('form_id')->where([['record_id', '=', $data['record_id']], ['member_id', '=', $this->member_id]])->findOrEmpty()->toArray();
        if (empty($records_info)) throw new ApiException('DIY_FORM_RECORDS_NOT_EXIST');

        $data[ 'form_id' ] = $records_info[ 'form_id' ];
        $data[ 'member_id' ] = $this->member_id;

        $info = $this->model->field('status')->where([ [ 'form_id', '=', $records_info[ 'form_id' ] ] ])->findOrEmpty()->toArray();
        if (empty($info)) throw new ApiException('DIY_FORM_NOT_EXIST');
        if ($info[ 'status' ] == 0) throw new ApiException('DIY_FORM_NOT_OPEN');

        $core_diy_form_records_service = new CoreDiyFormRecordsService();
        $write_config = ( new DiyFormWriteConfig() )->where([
            [ 'form_id', '=', $records_info[ 'form_id' ] ]
        ])->findOrEmpty()->toArray();
        if (!empty($write_config)) {
            if ($error_msg = $this->checkMemberCanJoinOrNot($this->member_id, $write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
            if ($error_msg = $this->checkFormWriteTime($write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
            if ($error_msg = $this->checkFormWriteLimitNum($records_info[ 'form_id' ], $write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
            if ($error_msg = $this->checkMemberWriteLimitNum($this->member_id, $records_info[ 'form_id' ], $write_config)) {
                throw new ApiException($error_msg[ 'desc' ]);
            }
        }
        return $core_diy_form_records_service->edit($data);
    }

    /**
     * 获取表单填写结果信息
     * @param array $params
     * @return mixed
     */
    public function getResult($params = [])
    {
        $diy_form_records_model = new DiyFormRecords();
        $field = 'record_id,form_id,create_time';
        return $diy_form_records_model->field($field)->where([ [ 'record_id', '=', $params[ 'record_id' ] ], [ 'member_id', '=', $this->member_id ] ])->with([ 'submitConfig' ])->findOrEmpty()->toArray();
    }

    /**
     * 获取表单填写记录，循环diy-group，每个表单组件实现各自的渲染处理
     * @param array $params
     * @return mixed
     */
    public function getFormRecordInfo($params = [])
    {
        $diy_form_records_model = new DiyFormRecords();
        $field = 'record_id,form_id,create_time';
        $info = $diy_form_records_model->field($field)->where([ [ 'record_id', '=', $params[ 'record_id' ] ], [ 'member_id', '=', $this->member_id ] ])
            ->with([
                // 关联填写字段列表
                'recordsFieldList' => function($query) {
                    $query->field('id, form_id, form_field_id, record_id, field_key, field_type, field_name, field_value, field_required, field_unique, privacy_protection, update_num, update_time')->append([ 'handle_field_value', 'render_value' ]);
                }
            ])->findOrEmpty()->toArray();

        return $info;
    }

    /**
     * 获取个人资料表单填写记录
     * @param array $params
     * @return mixed
     */
    public function getMemberInfoFormRecordInfo()
    {
        $info = [];
        $config = (new CoreMemberConfigService())->getMemberConfig();
        if (!empty($config['form_id'])) {
            $diy_form_records_model = new DiyFormRecords();
            $field = 'record_id,form_id,create_time';
            $records_info = $diy_form_records_model->field($field)->where([['form_id', '=', $config['form_id']], ['member_id', '=', $this->member_id]])->with([
                // 关联填写字段列表
                'recordsFieldList' => function($query) {
                    $query->field('id, form_id, form_field_id, record_id, field_key, field_type, field_name, field_value, field_required, field_unique, privacy_protection, update_num, update_time')->append([ 'handle_field_value', 'render_value' ]);
                }
            ])->order('create_time desc')->findOrEmpty()->toArray();

            $form_info = $this->model->where([['form_id', '=', $config['form_id']], ['status', '=', 1]])->field('form_id,type')
                ->with(['formField' => function($query) {
                    $query->field('form_id,field_id as form_field_id,field_key,field_type,field_name,field_default as field_value,field_required,field_unique,privacy_protection');
                }])->append([ 'type_name' ])->findOrEmpty()->toArray();

            if (!empty($form_info)) {
                if (!empty($records_info)) {
                    $records_info['recordsFieldList'] = array_column($records_info['recordsFieldList'], null, 'field_key');
                    foreach ($form_info['formField'] as $k => &$v) {
                        if (!empty($records_info['recordsFieldList'][$v['field_key']])) {
                            $v['field_value'] = $records_info['recordsFieldList'][$v['field_key']]['field_value'];
                        }
                    }
                }
                $info = $form_info;
            }
        }
        return $info;
    }

}
