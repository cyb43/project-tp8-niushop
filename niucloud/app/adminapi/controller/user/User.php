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

namespace app\adminapi\controller\user;

use app\dict\sys\UserDict;
use app\service\admin\user\UserService;
use core\base\BaseAdminController;
use Exception;
use think\Response;

/**
 * 站点用户接口
 * @description 站点用户
 * Class User
 */
class User extends BaseAdminController
{
    /**
     * 用户列表
     * @description 用户列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['username', ''],
            ['realname', ''],
            ['role', ''],
            ['create_time', []],
        ]);
        $list = (new UserService())->getPage($data);
        return success($list);

    }

    /**
     * 用户详情
     * @param $uid
     * @return Response
     */
    public function info($uid)
    {
        if(!is_numeric($uid))
        {
            $uid = 0;
        }
        return success((new UserService())->getInfo($uid));
    }

    /**
     * 获取全部用户
     * @description 获取全部用户
     * @return Response
     */
    public function getUserAll()
    {
        $data = $this->request->params([
            ['username', ''],
            ['realname', ''],
            ['create_time', []],
        ]);
        $list = (new UserService())->getUserAll($data);
        return success($list);
    }

    /**
     * 新增用户
     * @description 新增用户
     * @return Response
     * @throws Exception
     */
    public function add()
    {
        $data = $this->request->params([
            ['username', ''],
            ['password', ''],
            ['real_name', ''],
            ['head_img', ''],
            ['status', UserDict::ON],
            ['role_ids', []]
        ]);
        $this->validate($data, 'app\validate\sys\User.add');
        $uid = (new UserService())->addUser($data);
        return success('ADD_SUCCESS', ['uid' => $uid]);
    }

    /**
     * 更新用户
     * @description 更新用户
     */
    public function edit($uid)
    {
        $data = $this->request->params([
            ['real_name', ''],
            ['head_img', ''],
            ['status', UserDict::ON],
            ['role_ids', []],
            ['password', '']
        ]);
        (new UserService())->editUser($uid, $data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 更新字段
     * @description 更新字段
     * @param $uid
     * @param $field
     * @return Response
     */
    public function modify($uid, $field)
    {
        $data = $this->request->params([
            ['value', ''],
            ['field', $field]
        ]);
        $data[$field] = $data['value'];
//        $this->validate($data, 'app\validate\sys\User.modify');
        (new UserService())->modify($uid, $field, $data['value']);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 删除单个用户
     * @description 删除单个用户
     * @param $uid
     * @return Response
     */
    public function del($uid)
    {
        (new UserService())->del($uid);
        return success('DELETE_SUCCESS');
    }

    /**
     * 锁定用户
     * @description 锁定用户
     */
    public function lock($uid)
    {
        (new UserService())->lock($uid);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 解锁用户
     * @description 解锁用户
     */
    public function unlock($uid)
    {
        (new UserService())->unlock($uid);
        return success('MODIFY_SUCCESS');
    }

}
