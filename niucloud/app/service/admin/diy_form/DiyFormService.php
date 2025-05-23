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

namespace app\service\admin\diy_form;

use app\dict\diy_form\ComponentDict as DiyFormComponentDict;
use app\dict\diy\PagesDict;
use app\dict\diy_form\TemplateDict;
use app\dict\diy_form\TypeDict;
use app\model\diy_form\DiyForm;
use app\model\diy_form\DiyFormFields;
use app\model\diy_form\DiyFormRecords;
use app\model\diy_form\DiyFormRecordsFields;
use app\model\diy_form\DiyFormSubmitConfig;
use app\model\diy_form\DiyFormWriteConfig;
use app\service\admin\diy\DiyService;
use app\service\admin\sys\SystemService;
use app\service\core\diy_form\CoreDiyFormConfigService;
use app\service\core\diy_form\CoreDiyFormRecordsService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use core\exception\CommonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\Log;

/**
 * 万能表单服务层
 * Class DiyFormService
 * @package app\service\admin\diy
 */
class DiyFormService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new DiyForm();
    }

    /**
     * 获取万能表单分页列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'form_id, page_title, title, type, status, addon, share, write_num, remark, update_time';
        $order = "form_id desc";

        $search_model = $this->model->where([ [ 'form_id', '>', 0 ] ])->withSearch([ "title", "type", 'addon' ], $where)->field($field)->order($order)->append([ 'type_name', 'addon_name' ]);
        return $this->pageQuery($search_model);
    }

    /**
     * 获取万能表单分页列表（用于弹框选择）
     * @param array $where
     * @return array
     */
    public function getSelectPage(array $where = [])
    {
        $verify_form_ids = [];
        // 检测id集合是否存在，移除不存在的id，纠正数据准确性
        if (!empty($where[ 'verify_form_ids' ])) {
            $verify_form_ids = $this->model->where([
                [ 'form_id', 'in', $where[ 'verify_form_ids' ] ]
            ])->field('form_id')->select()->toArray();

            if (!empty($verify_form_ids)) {
                $verify_form_ids = array_column($verify_form_ids, 'form_id');
            }
        }

        $field = 'form_id, page_title, title, type, status, addon, share, write_num, remark, update_time';
        $order = "form_id desc";

        $search_model = $this->model->where([
            [ 'status', '=', 1 ],
        ])->withSearch([ "title", "type", 'addon' ], $where)->field($field)->order($order)->append([ 'type_name', 'addon_name' ]);
        $list = $this->pageQuery($search_model);
        $list[ 'verify_form_ids' ] = $verify_form_ids;
        return $list;
    }

    /**
     * 获取万能表单列表
     * @param array $where
     * @param string $field
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getList(array $where = [], $field = 'form_id, page_title, title, type, status, addon, share, write_num, remark, update_time')
    {
        $order = "form_id desc";
        return $this->model->where([ [ 'form_id', '>', 0 ] ])->withSearch([ "title", "type", 'addon', 'status' ], $where)->field($field)->order($order)->append([ 'type_name', 'addon_name' ])->select()->toArray();
    }

    /**
     * 获取万能表单信息
     * @param int $form_id
     * @param string $field
     * @return mixed
     */
    public function getInfo(int $form_id, $field = 'form_id, page_title, title, type, status, value, addon, share, write_num,template')
    {
        return $this->model->field($field)->where([ [ 'form_id', '=', $form_id ] ])->findOrEmpty()->toArray();
    }

    /**
     * 获取万能表单数量
     * @param array $where
     * @return mixed
     */
    public function getCount(array $where = [])
    {
        return $this->model->where([ [ 'form_id', '>', 0 ] ])->withSearch([ 'type' ], $where)->count();
    }

    /**
     * 添加万能表单
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        Db::startTrans();
        try {
            $data[ 'status' ] = 1; // 默认为开启状态
            $data[ 'create_time' ] = time();
            $data[ 'update_time' ] = time();
            $data[ 'addon' ] = TypeDict::getType([ 'type' => $data[ 'type' ] ])[ 'addon' ] ?? '';
            $res = $this->model->create($data);

            $diy_form_fields = [];
            if (!empty($data[ 'value' ])) {

                $value = json_decode($data[ 'value' ], true);
                foreach ($value[ 'value' ] as $component) {
                    // 过滤非表单组件和表单提交按钮组件
                    if ($component[ 'componentType' ] != 'diy_form' || $component[ 'componentName' ] == 'FormSubmit') {
                        continue;
                    }

                    if (isset($component[ 'field' ][ 'default' ]) && is_array($component[ 'field' ][ 'default' ])) {
                        $component[ 'field' ][ 'default' ] = json_encode($component[ 'field' ][ 'default' ]);
                    }

                    $diy_form_fields[] = [
                        'form_id' => $res->form_id, // 所属万能表单id
                        'field_key' => $component[ 'id' ], // 字段唯一标识
                        'field_type' => $component[ 'componentName' ], // 字段类型
                        'field_name' => $component[ 'field' ][ 'name' ] ?? '', // 字段名称
                        'field_remark' => $component[ 'field' ][ 'remark' ][ 'text' ] ?? '', // 字段说明
                        'field_default' => $component[ 'field' ][ 'default' ] ?? '', // 字段默认值
                        'field_required' => $component[ 'field' ][ 'required' ], // 字段是否必填 0:否 1:是
                        'field_hidden' => $component[ 'isHidden' ], // 字段是否隐藏 0:否 1:是
                        'field_unique' => $component[ 'field' ][ 'unique' ], // 字段内容防重复 0:否 1:是
                        'privacy_protection' => $component[ 'field' ][ 'privacyProtection' ], // 隐私保护 0:关闭 1:开启
                        'create_time' => time(),
                        'update_time' => time()
                    ];
                }
            }
            $form_fields_model = new DiyFormFields();
            $form_fields_model->insertAll($diy_form_fields);

            // 初始化表单填写配置
            ( new CoreDiyFormConfigService() )->addWriteConfig([ 'form_id' => $res->form_id ]);

            // 初始化表单提交成功页配置
            ( new CoreDiyFormConfigService() )->addSubmitConfig([ 'form_id' => $res->form_id ]);
            Db::commit();
            return $res->id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 编辑万能表单
     * @param int $form_id
     * @param array $data
     * @return bool
     */
    public function edit(int $form_id, array $data)
    {

        Db::startTrans();
        try {
            $data[ 'update_time' ] = time();

            // 更新万能表单
            $this->model->where([ [ 'form_id', '=', $form_id ] ])->update($data);

            // 更新万能表单字段信息
            $form_fields_model = new DiyFormFields();

            if (!empty($data[ 'value' ])) {
                $value = json_decode($data[ 'value' ], true);

                if (!empty($value[ 'value' ])) {
                    $form_fields_add = $form_fields_update = $form_fields_delete_ids = [];

                    $form_component = []; // 存储表单组件集合
                    $form_fields_list = $form_fields_model->where([ [ 'form_id', '=', $form_id ] ])->column('field_id', 'field_key');

                    foreach ($value[ 'value' ] as $component) {
                        // 过滤非表单组件和表单提交按钮组件
                        if ($component[ 'componentType' ] != 'diy_form' || $component[ 'componentName' ] == 'FormSubmit') {
                            continue;
                        }

                        if (isset($component[ 'field' ][ 'default' ]) && is_array($component[ 'field' ][ 'default' ])) {
                            $component[ 'field' ][ 'default' ] = json_encode($component[ 'field' ][ 'default' ]);
                        }

                        $form_component[] = $component;
                        if (isset($form_fields_list[ $component[ 'id' ] ])) {
                            $form_fields_update = [
                                'field_id' => $form_fields_list[ $component[ 'id' ] ],
                                'field_name' => $component[ 'field' ][ 'name' ] ?? '', // 字段名称
                                'field_remark' => $component[ 'field' ][ 'remark' ][ 'text' ] ?? '', // 字段说明
                                'field_default' => $component[ 'field' ][ 'default' ] ?? '', // 字段默认值
                                'field_required' => $component[ 'field' ][ 'required' ], // 字段是否必填 0:否 1:是
                                'field_hidden' => $component[ 'isHidden' ], // 字段是否隐藏 0:否 1:是
                                'field_unique' => $component[ 'field' ][ 'unique' ], // 字段内容防重复 0:否 1:是
                                'privacy_protection' => $component[ 'field' ][ 'privacyProtection' ], // 隐私保护 0:关闭 1:开启
                                'update_time' => time()
                            ];
                            // 更新万能表单字段
                            $form_fields_model->where([
                                [ 'field_id', '=', $form_fields_list[ $component[ 'id' ] ] ]
                            ])->update($form_fields_update);
                        } else {
                            $form_fields_add[] = [
                                'form_id' => $form_id,
                                'field_key' => $component[ 'id' ], // 字段唯一标识
                                'field_type' => $component[ 'componentName' ], // 字段类型
                                'field_name' => $component[ 'field' ][ 'name' ] ?? '', // 字段名称
                                'field_remark' => $component[ 'field' ][ 'remark' ][ 'text' ] ?? '', // 字段说明
                                'field_default' => $component[ 'field' ][ 'default' ] ?? '', // 字段默认值
                                'field_required' => $component[ 'field' ][ 'required' ], // 字段是否必填 0:否 1:是
                                'field_hidden' => $component[ 'isHidden' ], // 字段是否隐藏 0:否 1:是
                                'field_unique' => $component[ 'field' ][ 'unique' ], // 字段内容防重复 0:否 1:是
                                'privacy_protection' => $component[ 'field' ][ 'privacyProtection' ], // 隐私保护 0:关闭 1:开启
                                'create_time' => time(),
                                'update_time' => time()
                            ];
                        }
                    }

                    $field_key_list = array_column($form_component, 'id');
                    $form_fields_delete_ids = array_diff(array_keys($form_fields_list), $field_key_list);

                    // 添加万能表单字段
                    if (!empty($form_fields_add)) {
                        $form_fields_model->insertAll($form_fields_add);
                    }

                    // 删除万能表单字段
                    if (!empty($form_fields_delete_ids)) {
                        $form_fields_model->where([ [ 'form_id', '=', $form_id ], [ 'field_key', 'in', $form_fields_delete_ids ] ])->delete();
                    }

                } else {
                    // 未找到表单组件，则全部清空
                    $form_fields_model->where([ [ 'form_id', '=', $form_id ] ])->delete();
                }
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 删除万能表单
     * @param array $form_ids
     * @return bool
     */
    public function del(array $form_ids)
    {
        $where = [
            [ 'form_id', 'in', $form_ids ]
        ];

        $status_count = $this->model->where($where)->where([ [ 'status', '=', 1 ] ])->count();

        if ($status_count > 0) throw new AdminException('ON_STATUS_PROHIBIT_DELETE');

        foreach ($form_ids as $form_id) {
            $result = event('BeforeFormDelete', [ 'form_id' => $form_id ])[ 0 ] ?? [];
            if (!empty($result) && !$result[ 'allow_operate' ]) {
                $form_info = $this->model->field('page_title')->where([ [ 'form_id', '=', $form_id ] ])->findOrEmpty()->toArray();
                throw new AdminException($form_info[ 'page_title' ] . '已被使用，禁止删除');
            }
        }

        $form_fields_model = new DiyFormFields();
        $form_submit_config_model = new DiyFormSubmitConfig();
        $form_write_config_model = new DiyFormWriteConfig();

        Db::startTrans();
        try {
            //删除万能表单表
            $this->model->where($where)->delete();

            //删除万能表单字段表
            $form_fields_model->where($where)->delete();

            //删除万能表单提交页配置
            $form_submit_config_model->where($where)->delete();

            //删除万能表单填写配置
            $form_write_config_model->where($where)->delete();

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 页面加载初始化
     * @param array $params
     * @return array
     * @throws DbException
     */
    public function getInit(array $params = [])
    {
        $time = time();
        $data = [];
        if (!empty($params[ 'form_id' ])) {
            $data = $this->getInfo($params[ 'form_id' ]);
        }

        if (!empty($data)) {
            $current_type = TypeDict::getType([ 'type' => $data[ 'type' ] ]);
            $type_name = $current_type[ 'title' ];
            $data[ 'type_name' ] = $type_name;
        } else {
            if (!isset($params[ 'type' ]) || empty($params[ 'type' ])) throw new AdminException('DIY_FORM_TYPE_NOT_EXIST');
            $type = $params[ 'type' ];

            // 新页面赋值
            $page_title = $params[ 'title' ] ? : '表单页面' . $time; // 页面标题（用于前台展示）

            $current_type = TypeDict::getType([ 'type' => $params[ 'type' ] ]);
            $type_name = $current_type[ 'title' ];
            $title = $type_name;
            $value = '';

            $data = [
                'page_title' => $page_title, // 页面名称（用于后台展示）
                'title' => $title, // 页面标题（用于前台展示）
                'type' => $type,
                'type_name' => $type_name,
                'value' => $value,
            ];

        }

        $data[ 'component' ] = $this->getComponentList($data[ 'type' ]);
        $data[ 'domain_url' ] = ( new SystemService() )->getUrl();
        return $data;
    }

    /**
     * 修改分享内容
     * @param $data
     * @return bool
     */
    public function modifyShare($data)
    {
        $this->model->where([ [ 'form_id', '=', $data[ 'form_id' ] ] ])->update([ 'share' => $data[ 'share' ] ]);
        return true;
    }

    /**
     * 获取组件列表（表单组件+自定义装修的组件）
     * @param string $type 支持表单类型
     * @return array
     */
    public function getComponentList(string $type = '')
    {
        $componentType = function(&$component_list, $type) {
            if (!empty($component_list)) {
                foreach ($component_list as $k => &$value) {
                    if (!empty($value[ 'list' ])) {
                        foreach ($value[ 'list' ] as $ck => &$v) {
                            $v[ 'componentType' ] = $type;
                        }
                    }
                }
            }
        };

        $form_component_list = DiyFormComponentDict::getComponent();
        foreach ($form_component_list as $k => $v) {
            // 查询组件支持的表单类型
            $sort_arr = [];
            foreach ($v[ 'list' ] as $ck => $cv) {
                $support = $cv[ 'support' ] ?? [];
                if (!( count($support) == 0 || in_array($type, $support) )) {
                    unset($form_component_list[ $k ][ 'list' ][ $ck ]);
                    continue;
                }

                $sort_arr [] = $cv[ 'sort' ];
                unset($form_component_list[ $k ][ 'list' ][ $ck ][ 'sort' ], $form_component_list[ $k ][ 'list' ][ $ck ][ 'support' ]);
            }
            array_multisort($sort_arr, SORT_ASC, $form_component_list[ $k ][ 'list' ]); //排序，根据 sort 排序
        }
        $componentType($form_component_list, 'diy_form');

        $data = $form_component_list;

        if ($type == 'DIY_FORM') {
            $diy_service = new DiyService();
            $diy_component_list = $diy_service->getComponentList();
            $componentType($diy_component_list, 'diy');

            $data = array_merge($form_component_list, $diy_component_list);
        }
        return $data;
    }

    /**
     * 获取万能表单模板数据
     * @param $type
     * @param $name
     * @return array
     */
    public function getPageData($type, $name)
    {
        $pages = PagesDict::getPages([ 'type' => $type ]);
        return $pages[ $name ] ?? [];
    }

    /**
     * 复制万能表单
     * @param array $param
     * @return mixed
     */
    public function copy($param)
    {
        $info = $this->model
            ->withoutfield('create_time,update_time')
            ->with([
                'writeConfig' => function($query) {
                    $query->withoutfield('id,create_time,update_time');
                },
                'submitConfig' => function($query) {
                    $query->withoutfield('id,create_time,update_time');
                },
                'formField' => function($query) {
                    $query->withoutfield('field_id,create_time,update_time');
                } ])
            ->where([ [ 'form_id', '=', $param[ 'form_id' ] ] ])->findOrEmpty()->toArray();
        if (empty($info)) throw new AdminException('DIY_FORM_NOT_EXIST');
        unset($info[ 'form_id' ]);
        $info[ 'page_title' ] = $info[ 'page_title' ] . '_副本';
        $info[ 'status' ] = 0;
        $info[ 'share' ] = '';
        $info[ 'write_num' ] = 0;
        $info[ 'create_time' ] = time();
        $info[ 'update_time' ] = time();

        Db::startTrans();
        try {
            $res = $this->model->create($info);
            $form_id = $res->form_id;
            if (!empty($info[ 'formField' ])) {
                $form_field_list = array_map(function($item) use ($form_id) {
                    $item[ 'form_id' ] = $form_id;
                    $item[ 'write_num' ] = 0;
                    $item[ 'create_time' ] = time();
                    $item[ 'update_time' ] = time();
                    return $item;
                }, $info[ 'formField' ]);

                ( new DiyFormFields() )->saveALl($form_field_list);
                unset($info[ 'formField' ]);
            }
            if (!empty($info[ 'writeConfig' ])) {
                $info[ 'writeConfig' ][ 'form_id' ] = $form_id;
                ( new CoreDiyFormConfigService() )->addWriteConfig($info[ 'writeConfig' ]);
            }
            if (!empty($info[ 'submitConfig' ])) {
                $info[ 'submitConfig' ][ 'form_id' ] = $form_id;
                ( new CoreDiyFormConfigService() )->addSubmitConfig($info[ 'submitConfig' ]);
            }
            Db::commit();
            return $form_id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 获取页面模板
     * @param array $params
     * @return array
     */
    public function getTemplate($params = [])
    {
        $page_template = TemplateDict::getTemplate($params);
        return $page_template;
    }

    /**
     * 获取万能表单类型
     * @return array|null
     */
    public function getFormType()
    {
        $type_list = TypeDict::getType();
        return $type_list;
    }

    /**
     * 修改状态
     * @param $data
     * @return Bool
     */
    public function modifyStatus($data)
    {
        $result = event('BeforeFormDelete', [ 'form_id' => $data[ 'form_id' ] ])[ 0 ] ?? [];
        if (!empty($result) && !$result[ 'allow_operate' ] && $data[ 'status' ] == 0) {
            $form_info = $this->model->field('page_title')->where([ [ 'form_id', '=', $data[ 'form_id' ] ] ])->findOrEmpty()->toArray();
            throw new AdminException($form_info[ 'page_title' ] . '已被使用，不可禁用');
        }
        return $this->model->where([
            [ 'form_id', '=', $data[ 'form_id' ] ]
        ])->update([ 'status' => $data[ 'status' ] ]);
    }

    /**
     * 获取使用记录
     * @param array $data
     * @return array|null
     */
    public function getRecordPages($data)
    {
        return ( new CoreDiyFormRecordsService() )->getPage($data);
    }

    /**
     * 获取使用记录
     * @param int $record_id
     * @return array|null
     */
    public function getRecordInfo(int $record_id)
    {
        $data[ 'record_id' ] = $record_id;
        return ( new CoreDiyFormRecordsService() )->getInfo($data);
    }

    /**
     * 删除填写记录
     * @param $params
     * @return bool
     */
    public function delRecord($params)
    {
        Db::startTrans();
        try {

            // 减少填写数量
            $this->model->where([ [ 'form_id', '=', $params[ 'form_id' ] ] ])->dec('write_num', 1)->update();

            $form_records_model = new DiyFormRecords();
            $form_records_model->where([
                [ 'record_id', '=', $params[ 'record_id' ] ]
            ])->delete();

            $form_records_fields_model = new DiyFormRecordsFields();
            // 删除万能表单填写字段
            $form_records_fields_model->where([
                [ 'record_id', '=', $params[ 'record_id' ] ]
            ])->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }

    }

    /**
     * 获取万能表单字段列表
     * @param array $where
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function getFieldsList($where = [], $field = 'field_id, form_id, field_key, field_type, field_name, field_remark, write_num, field_required, field_hidden, field_unique, privacy_protection, create_time, update_time')
    {
        $order = "update_time desc";
        if (!empty($where[ 'order' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        }
        return ( new DiyFormFields() )->where([ [ 'field_id', '>', 0 ] ])->withSearch([ 'form_id' ], $where)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 检测表单名称唯一性
     * @param array $data
     * @return bool
     */
    public function checkPageTitleUnique($data)
    {
        $where = [
            [ 'page_title', "=", $data[ 'page_title' ] ]
        ];
        if (!empty($data[ 'form_id' ])) {
            $where[] = [ 'form_id', "<>", $data[ 'form_id' ] ];
        }
        return $this->model->where($where)->count() > 0;
    }

}
