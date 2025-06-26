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

namespace app\adminapi\controller\diy;

use app\service\admin\diy_form\DiyFormConfig;
use app\service\admin\diy_form\DiyFormRecordsService;
use app\service\admin\diy_form\DiyFormService;
use core\base\BaseAdminController;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;


/**
 * 万能表单控制器
 * Class DiyForm
 * @description 万能表单
 * @package app\adminapi\controller\diy
 */
class DiyForm extends BaseAdminController
{
    /**
     * 获取万能表单分页列表
     * @description 获取万能表单分页列表
     * @return Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ "type", "" ],
            [ 'addon', '' ]
        ]);
        return success(( new DiyFormService() )->getPage($data));
    }

    /**
     * 获取万能表单分页列表（用于弹框选择）
     * @description 获取万能表单分页列表（用于弹框选择）
     * @return Response
     */
    public function select()
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ "type", "" ],
            [ 'addon', '' ],
            [ 'verify_form_ids', '' ] // 检测id集合是否存在，移除不存在的id，纠正数据准确性
        ]);
        return success(( new DiyFormService() )->getSelectPage($data));
    }

    /**
     * 获取万能表单列表
     * @description 获取万能表单列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ 'status', 0 ],
            [ "type", "" ],
            [ 'addon', '' ]
        ]);
        return success(( new DiyFormService() )->getList($data));
    }

    /**
     * 万能表单详情
     * @description 万能表单详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success(( new DiyFormService() )->getInfo($id));
    }

    /**
     * 添加万能表单
     * @description 添加万能表单
     * @return Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ "page_title", "" ],
            [ "type", "" ],
            [ "value", [] ],
            [ 'template', '' ],
        ]);
        $this->validate($data, 'app\validate\diy\DiyForm.add');
        $id = ( new DiyFormService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 万能表单编辑
     * @description 万能表单编辑
     * @param $id
     * @return Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ "page_title", "" ],
            [ "value", [] ],
            [ 'template', '' ],
        ]);
        $data[ 'form_id' ] = $id;
        $this->validate($data, 'app\validate\diy\DiyForm.edit');
        ( new DiyFormService() )->edit($id, $data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 万能表单删除
     * @description 万能表单删除
     * @return Response
     */
    public function del()
    {
        $params = $this->request->params([
            [ 'form_ids', [] ]
        ]);
        ( new DiyFormService() )->del($params[ 'form_ids' ]);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取万能表单初始化数据
     * @description 获取万能表单初始化数据
     * @return Response
     * @throws DbException
     */
    public function getInit()
    {
        $params = $this->request->params([
            [ 'form_id', "" ],
            [ "type", "" ],
            [ "title", "" ],
        ]);
        $diy_service = new DiyFormService();
        return success($diy_service->getInit($params));
    }

    /**
     * 获取万能表单模板
     * @description 获取万能表单模板
     * @return Response
     */
    public function getTemplate()
    {
        $params = $this->request->params([
            [ 'type', '' ],
            [ 'template_key', '' ],
        ]);
        $diy_service = new DiyFormService();
        return success($diy_service->getTemplate($params));
    }

    /**
     * 修改页面分享内容
     * @description 修改页面分享内容
     * @return Response
     */
    public function modifyShare()
    {
        $data = $this->request->params([
            [ "form_id", "" ],
            [ "share", "" ],
        ]);
        ( new DiyFormService() )->modifyShare($data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 获取模板页面（存在的应用插件列表）
     * @description 获取模板页面（存在的应用插件列表）
     * @return Response
     */
    public function getApps()
    {
        return success(( new DiyFormService() )->getApps());
    }

    /**
     * 复制万能表单
     * @description 复制万能表单
     * @return Response
     */
    public function copy()
    {
        $params = $this->request->params([
            [ 'form_id', '' ],
        ]);
        $form_id = ( new DiyFormService() )->copy($params);
        return success('ADD_SUCCESS', [ 'form_id' => $form_id ]);
    }

    /**
     * 获取模板页面（存在的应用插件列表）
     * @description 获取模板页面（存在的应用插件列表）
     * @return Response
     */
    public function getFormType()
    {
        return success(( new DiyFormService() )->getFormType());
    }

    /**
     * 修改状态
     * @description 修改状态
     * @return \think\Response
     */
    public function modifyStatus()
    {
        $data = $this->request->params([
            [ 'form_id', '' ],
            [ 'status', 1 ],
        ]);
        ( new DiyFormService() )->modifyStatus($data);
        return success('SUCCESS');
    }

    /**
     * 获取使用记录
     * @description 获取使用记录
     * @return Response
     */
    public function getRecordPages()
    {
        $data = $this->request->params([
            [ "form_id", 0 ],
            [ "keyword", "" ],
            [ "create_time", "" ],
        ]);
        return success(( new DiyFormService() )->getRecordPages($data));
    }

    /**
     * 获取使用记录详情
     * @description 获取使用记录详情
     * @param int $record_id
     * @return Response
     */
    public function getRecordInfo(int $record_id)
    {
        return success(( new DiyFormService() )->getRecordInfo($record_id));
    }

    /**
     * 使用记录删除
     * @description 使用记录删除
     * @return Response
     */
    public function delRecord()
    {
        $data = $this->request->params([
            [ "form_id", 0 ],
            [ 'record_id', 0 ],
        ]);
        ( new DiyFormService() )->delRecord($data);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取万能表单字段记录
     * @description 获取万能表单字段记录
     * @return Response
     */
    public function getFieldsList()
    {
        $data = $this->request->params([
            [ "form_id", 0 ],
            [ 'order', '' ],
            [ 'sort', '' ]
        ]);
        return success(( new DiyFormService() )->getFieldsList($data));
    }

    /**
     * 获取表单填写配置
     * @description 获取表单填写配置
     * @param $form_id int 所属万能表单id
     * @return Response
     */
    public function getWriteConfig($form_id)
    {
        return success(( new DiyFormConfig() )->getWriteConfig($form_id));
    }

    /**
     * 编辑表单填写配置
     * @description 编辑表单填写配置
     * @return Response
     */
    public function editWriteConfig()
    {
        $data = $this->request->params([
            [ 'id', 0 ],
            [ 'form_id', 0 ], // 所属万能表单id
            [ 'write_way', '' ], // 填写方式，no_limit：不限制，scan：仅限微信扫一扫，url：仅限链接进入
            [ 'join_member_type', '' ], // 参与会员，all_member：所有会员参与，selected_member_level：指定会员等级，selected_member_label：指定会员标签
            [ 'level_ids', [] ], // 会员等级id集合
            [ 'label_ids', [] ], // 会员标签id集合
            [ 'member_write_type', '' ], // 每人可填写次数，no_limit：不限制，diy：自定义
            [ 'member_write_rule', [] ], // 每人可填写次数自定义规则
            [ 'form_write_type', '' ], // 表单可填写数量，no_limit：不限制，diy：自定义
            [ 'form_write_rule', [] ], // 表单可填写总数自定义规则
            [ 'time_limit_type', '' ], // 填写时间限制类型，no_limit：不限制， specify_time：指定开始结束时间，open_day_time：设置每日开启时间
            [ 'time_limit_rule', [] ], // 填写时间限制规则
            [ 'is_allow_update_content', 0 ], // 是否允许修改自己填写的内容，0：否，1：是
            [ 'write_instruction', '' ], // 表单填写须知
        ]);
        ( new DiyFormConfig() )->editWriteConfig($data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 获取表单提交成功 也配置
     * @description 获取表单提交成功页配置
     * @param $form_id int 所属万能表单id
     * @return Response
     */
    public function getSubmitConfig($form_id)
    {
        return success(( new DiyFormConfig() )->getSubmitConfig($form_id));
    }

    /**
     * 编辑表单提交成功页配置
     * @description 编辑表单提交成功页配置
     * @return Response
     */
    public function editSubmitConfig()
    {
        $data = $this->request->params([
            [ 'id', 0 ],
            [ 'form_id', 0 ], // 所属万能表单id
            [ 'submit_after_action', '' ], // 填表人提交后操作，text：文字信息，voucher：核销凭证
            [ 'tips_type', '' ], // 提示内容类型，default：默认提示，diy：自定义提示
            [ 'tips_text', '' ], // 自定义提示内容
            [ 'time_limit_type', [] ], // 核销凭证有效期限制类型，no_limit：不限制，specify_time：指定固定开始结束时间，submission_time：按提交时间设置有效期
            [ 'time_limit_rule', '' ], // 核销凭证时间限制规则，json格式 todo 结构待定
            [ 'voucher_content_rule', [] ], // 核销凭证内容，json格式 todo 结构待定
            [ 'success_after_action', '' ], // 填写成功后续操作
        ]);
        ( new DiyFormConfig() )->editSubmitConfig($data);
        return success('EDIT_SUCCESS');
    }

    // todo 查询表单详情

    /**
     * 获取万能表单填表人统计列表
     * @description 获取万能表单填表人统计列表
     * @return Response
     */
    public function memberStatPages()
    {
        $data = $this->request->params([
            [ "form_id", 0 ],
            [ "keyword", '' ],
        ]);
        return success(( new DiyFormRecordsService() )->getPage($data));
    }

    /**
     * 获取万能表单字段统计列表
     * @description 获取万能表单字段统计列表
     * @return Response
     */
    public function fieldStatList()
    {
        $data = $this->request->params([
            [ "form_id", 0 ],
        ]);
        return success(( new DiyFormRecordsService() )->getFieldStatList($data));
    }

}
