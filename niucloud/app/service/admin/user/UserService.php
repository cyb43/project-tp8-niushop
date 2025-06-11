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

namespace app\service\admin\user;


use app\dict\sys\UserDict;
use app\model\sys\SysRole;
use app\model\sys\SysUser;
use app\service\admin\auth\LoginService;
use app\service\admin\sys\RoleService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use Exception;
use think\db\exception\DbException;
use think\facade\Cache;
use think\Model;

/**
 * 用户服务层
 * Class BaseService
 * @package app\service
 */
class UserService extends BaseAdminService
{
    public static $cache_tag_name = 'user_cache';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 用户列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where)
    {
        $field = 'uid,username,head_img,real_name,last_ip,last_time,login_count,status, role_ids, is_admin';
        $search = [
            'username' => $where[ 'username' ],
            'realname' => $where[ 'realname' ],
            'create_time' => $where[ 'create_time' ]
        ];
        if (!empty($where[ 'role' ])) {
            $search[ 'role_ids' ] = $where[ 'role' ];
        }
        $search_model = ( new SysUser() )->withSearch([ 'username', 'realname', 'create_time', 'role_ids' ], $search)->field($field)->order('uid desc')->append([ 'status_name' ]);
        return $this->pageQuery($search_model, function ($item, $key) {
            $role_ids = $item[ 'role_ids' ] ?? [];
            $item->role_data = $this->getRoleByUserRoleIds($role_ids);
        });
    }


    /**
     * 用户详情
     * @param int $uid
     * @return array
     */
    public function getInfo(int $uid)
    {
        $where = array(
            [ 'uid', '=', $uid ],
        );
        $field = 'uid, username, head_img, real_name, last_ip, last_time, create_time, login_count, status, delete_time, update_time, role_ids, is_admin';
        $user = ( new SysUser() )->where($where)->field($field)->findOrEmpty();
        if ($user->isEmpty())
            return [];

        if (!empty($user?->userrole)) {
            $user->userrole->appendData([ 'role_array' => $this->getRoleByUserRoleIds($user->role_ids ?? []) ]);
        }

        return $user->append([ 'status_name' ])->toArray();
    }

    /**
     * 添加用户（添加用户，不添加站点）
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function add(array $data)
    {
        $user_data = [
            'username' => $data[ 'username' ],
            'head_img' => $data[ 'head_img' ],
            'status' => $data[ 'status' ],
            'real_name' => $data[ 'real_name' ],
            'password' => create_password($data[ 'password' ]),

            'is_admin' => $data[ 'is_admin' ],
            'role_ids' => $data[ 'role_ids' ],
        ];
        $user = ( new SysUser() )->create($user_data);
        return $user?->uid;
    }

    /**
     * 添加对应站点用户(添加站点，同时添加站点用户,用于添加站点以及站点添加站点用户)
     * @param $data
     * @return bool
     */
    public function addUser($data)
    {
        $role_ids = $data[ 'role_ids' ] ?? [];
        $is_admin = $data[ 'is_admin' ] ?? 0;

        $data[ 'is_admin' ] = $is_admin;
        if (!$is_admin) {
            $data[ 'role_ids' ] = $role_ids;
        }
        //添加用户
        $uid = $this->add($data);
        return $uid;
    }

    /**
     * 更新对应站点用户
     * @param $uid
     * @param $data
     * @return true
     */
    public function editUser($uid, $data)
    {
        $role_ids = $data[ 'role_ids' ] ?? [];
        $is_admin = $data[ 'is_admin' ] ?? 0;
        $data[ 'is_admin' ] = $is_admin;
        if (!$is_admin) {
            $data[ 'role_ids' ] = $role_ids;
        }
        $this->edit($uid, $data);
        return true;
    }


    /**
     * 修改字段
     * @param int $uid
     * @param string $field
     * @param $data
     * @return bool|true
     */
    public function modify(int $uid, string $field, $data)
    {
        $field_name = match ( $field ) {
            'password' => 'password',
            'real_name' => 'real_name',
            'head_img' => 'head_img',
        };
        return $this->edit($uid, [ $field_name => $data ]);
    }

    /**
     * 锁定
     * @param int $uid
     * @return bool|true
     */
    public function lock(int $uid)
    {
        return $this->edit($uid, [ 'status' => UserDict::OFF ]);
    }

    /**
     * 解锁
     * @param int $uid
     * @return bool|true
     */
    public function unlock(int $uid)
    {
        return $this->edit($uid, [ 'status' => UserDict::ON ]);
    }

    /**
     * 检测用户名是否重复
     * @param $username
     * @return bool
     * @throws DbException
     */
    public function checkUsername($username)
    {
        $count = ( new SysUser() )->where([ [ 'username', '=', $username ] ])->count();
        if ($count > 0) {
            return true;
        } else return false;
    }

    /**
     * 用户模型对象
     * @param int $uid
     * @return SysUser|array|mixed|Model
     */
    public function find(int $uid)
    {

        $user = ( new SysUser() )->findOrEmpty($uid);
        if ($user->isEmpty())
            throw new AdminException('USER_NOT_EXIST');
        return $user;
    }

    /**
     * 编辑用户
     * @param int $uid
     * @param array $data
     * @return true
     */
    public function edit(int $uid, array $data)
    {
        $user = $this->find($uid);
        $user_data = [
        ];
        $is_off_status = false;
        if (isset($data[ 'status' ])) {
            $user_data[ 'status' ] = $data[ 'status' ];
            if ($data[ 'status' ] == UserDict::OFF)
                $is_off_status = true;
        }
        if (isset($data[ 'head_img' ])) {
            $user_data[ 'head_img' ] = $data[ 'head_img' ];
        }
        if (isset($data[ 'real_name' ])) {
            $user_data[ 'real_name' ] = $data[ 'real_name' ];
        }

        $password = $data[ 'password' ] ?? '';
        $is_change_password = false;
        if (!empty($password) && !check_password($password, $user->password)) {
            $user_data[ 'password' ] = create_password($password);
            $is_change_password = true;
        }

        if (isset($data[ 'role_ids' ])) {
            $user_data[ 'role_ids' ] = $data[ 'role_ids' ];
        }

        if (empty($user_data))
            return true;
        //更新用户信息
        $user->save($user_data);
        //更新权限  禁用用户  修改密码 都会清理token
        if ($is_off_status || $is_change_password) {
            LoginService::clearToken($uid);
        }
        //清除用户缓存
        $cache_name = 'user_role_' . $uid;
        Cache::delete($cache_name);
        return true;
    }

    /**
     * 删除
     * @param int $uid
     * @return true
     */
    public function del(int $uid)
    {
        $where = [
            [ 'uid', '=', $uid ]
        ];
        $user = ( new SysUser() )->where($where)->findOrEmpty();
        if ($user->isEmpty()) throw new AdminException('USER_NOT_EXIST');
        if ($user->is_admin) throw new AdminException("SUPER_ADMIN_NOT_ALLOW_DEL");
        $user->delete();
        LoginService::clearToken($uid);
        Cache::delete('user_role_' . $uid);
        return true;
    }

    /**
     * 通过账号获取管理员信息
     * @param string $username
     * @return SysUser|array|mixed|Model
     */
    public function getUserInfoByUsername(string $username)
    {
        return ( new SysUser() )->where([ [ 'username', '=', $username ] ])->findOrEmpty();
    }

    /**
     * 获取全部用户列表（用于平台整体用户管理）
     * @param array $where
     * @return array
     */
    public function getUserAll(array $where)
    {
        $field = 'uid, username, head_img';
        return ( new SysUser() )->withSearch([ 'username', 'realname', 'create_time' ], $where)
            ->field($field)
            ->order('uid desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取用户缓存
     * @param int $uid
     * @return mixed|string
     */
    public function getUserCache(int $uid)
    {
        $cache_name = 'user_role_' . $uid;
        return cache_remember(
            $cache_name,
            function () use ($uid) {
                $where = array(
                    [ 'uid', '=', $uid ],
                );
                $field = 'uid, username, head_img, real_name, last_ip, last_time, create_time, login_count, status, delete_time, update_time, role_ids, is_admin';
                $user = ( new SysUser() )->where($where)->field($field)->append([ 'status_name' ])->findOrEmpty();
                return $user->toArray();
            },
            [ self::$cache_tag_name, RoleService::$cache_tag_name ]
        );

    }

    /**
     * 通过角色id组获取角色
     * @param array $role_ids
     * @return mixed
     */
    public function getRoleByUserRoleIds(array $role_ids)
    {
        sort($role_ids);
        $cache_name = 'role_by_ids_' . md5(implode(',', $role_ids));
        return cache_remember(
            $cache_name,
            function () use ($role_ids) {
                $where = array(
                    [ 'role_id', 'in', $role_ids ],
                );
                return SysRole::where($where)->column('role_name');
            },
            [ RoleService::$cache_tag_name ]
        );
    }
}
