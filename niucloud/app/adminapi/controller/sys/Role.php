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

namespace app\adminapi\controller\sys;

use app\dict\sys\RoleStatusDict;
use app\service\admin\sys\RoleService;
use core\base\BaseAdminController;
use think\db\exception\DbException;
use think\Response;

/**
 * 用户组管理
 * Class Role
 * @description 用户组管理
 * @package app\adminapi\controller\sys
 */
class Role extends BaseAdminController
{
    /**
     * 用户组列表
     * @description 用户组列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['role_name', ''],
        ]);
        $list = (new RoleService())->getPage($data);
        return success($list);

    }

    /**
     * 用户组详情
     * @description 用户组详情
     * @param $role_id
     * @return Response
     */
    public function info($role_id)
    {
        return success((new RoleService())->getInfo($role_id));
    }

    /**
     * 获取全部权限
     * @description 获取全部权限
     * @return Response
     */
    public function all()
    {
        return success((new RoleService())->getAll());
    }

    /**
     * 新增用户组
     * @description 新增用户组
     * @return Response
     */
    public function add()
    {
        $data = $this->request->params([
            ['role_name', ''],
            ['rules', []],
            ['status', RoleStatusDict::ON],
        ]);
        $this->validate($data, 'app\validate\sys\Role.add');
        (new RoleService())->add($data);
        return success('ADD_SUCCESS');
    }

    /**
     * 更新用户组
     * @description 更新用户组
     */
    public function edit($role_id)
    {
        $data = $this->request->params([
            ['role_name', ''],
            ['rules', []],
            ['status', RoleStatusDict::ON],
        ]);
        $this->validate($data, 'app\validate\sys\Role.edit');
        (new RoleService())->edit($role_id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 删除单个用户组
     * @description 删除单个用户组
     * @param $role_id
     * @return Response
     * @throws DbException
     */
    public function del($role_id)
    {
        (new RoleService())->del($role_id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 设置角色状态
     * @description 设置角色状态
     * @param $role_id
     * @return Response
     */
    public function modifyStatus($role_id)
    {
        $data = $this->request->params([
            ['status', RoleStatusDict::ON],
        ]);
        (new RoleService())->modifyStatus($role_id, $data['status']);
        return success('DELETE_SUCCESS');
    }

}
