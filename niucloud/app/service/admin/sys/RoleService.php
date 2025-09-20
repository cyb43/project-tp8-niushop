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

namespace app\service\admin\sys;

use app\dict\sys\RoleStatusDict;
use app\model\sys\SysRole;
use app\model\sys\SysUser;
use core\base\BaseAdminService;
use core\exception\AdminException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;

/**
 * admin授权服务层
 * Class BaseService
 * @package app\service
 */
class RoleService extends BaseAdminService
{
    public static $cache_tag_name = 'role_cache';

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysRole();
    }

    /**
     * 管理端获取角色列表
     * @param array $data
     * @return array
     */
    public function getPage(array $data)
    {
        $where = [];
        if (isset($data[ 'role_name' ]) && $data[ 'role_name' ] !== '') {
            $where[] = [ 'role_name', 'like', "%" . $this->model->handelSpecialCharacter($data[ 'role_name' ]) . "%" ];
        }
        $field = 'role_id,role_name,status,create_time';
        $search_model = $this->model->where($where)->field($field)->order('create_time desc')->append([ 'status_name' ]);
        return $this->pageQuery($search_model);
    }

    /**
     * 获取权限信息
     * @param int $role_id
     * @return array
     */
    public function getInfo(int $role_id)
    {
        return $this->model->append([ 'status_name' ])->findOrEmpty($role_id)->toArray();
    }

    /**
     * 获取站点下的所有权限
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAll()
    {
        $where = array(
            [ 'status', '=', 1 ]
        );
        return $this->model->where($where)->field('role_id,role_name,status,create_time')->select()->toArray();
    }

    /**
     * 新增权限
     * @param array $data
     * @return true
     */
    public function add(array $data)
    {
        $data[ 'create_time' ] = time();
        $this->model->save($data);
        Cache::tag(self::$cache_tag_name)->clear();
        return true;
    }

    /**
     * 更新权限
     * @param int $role_id
     * @param array $data
     * @return true
     */
    public function edit(int $role_id, array $data)
    {
        $where = array(
            [ 'role_id', '=', $role_id ],
        );
        $data[ 'update_time' ] = time();
        $this->model->update($data, $where);
        Cache::tag(self::$cache_tag_name)->clear();
        return true;

    }

    /**
     * 获取模型对象
     * @param int $role_id
     * @return mixed
     */
    public function find(int $role_id)
    {
        $where = array(
            [ 'role_id', '=', $role_id ],
        );
        $role = $this->model->where($where)->findOrEmpty();
        if ($role->isEmpty())
            throw new AdminException('USER_ROLE_NOT_EXIST');
        return $role;
    }

    /**
     * 删除权限
     * @param int $role_id
     * @return mixed
     * @throws DbException
     */
    public function del(int $role_id)
    {
        $role = $this->find($role_id);
        if (SysUser::where([ [ 'role_ids', 'like', [ '%"' . $role_id . '"%' ] ] ])->count() > 0)
            throw new AdminException('USER_ROLE_NOT_ALLOW_DELETE');
        $res = $role->delete();
        Cache::tag(self::$cache_tag_name)->clear();
        return $res;

    }

    /**
     * 获取角色id为健名,角色名为键值的数据
     * @return mixed|string
     */
    public function getColumn()
    {
        $cache_name = 'role_column';
        return cache_remember(
            $cache_name,
            function () {
                return $this->model->column('role_name', 'role_id');
            },
            [ RoleService::$cache_tag_name, self::$cache_tag_name ]
        );
    }

    /**
     * 通过权限组id获取菜单id
     * @param array $role_ids
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMenuKeysByRoleIds(array $role_ids)
    {
        sort($role_ids);
        $cache_name = 'user_role_menu_keys_' . md5(implode('_', $role_ids));
        return cache_remember(
            $cache_name,
            function () use ($role_ids) {
                $rules = $this->model->where([ [ 'role_id', 'IN', $role_ids ], [ 'status', '=', RoleStatusDict::ON ] ])->field('rules')->select()->toArray();
                if (!empty($rules)) {
                    $temp = [];
                    foreach ($rules as $v) {
                        $temp = array_merge($temp, $v[ 'rules' ]);
                    }
                    $temp = array_unique($temp);

                    if (empty($temp)) return [];
                    return $temp;
                }
                return [];
            },
            [ RoleService::$cache_tag_name, self::$cache_tag_name ]
        );

    }


    /**
     * 获取应用keys
     * @param array $role_ids
     * @return mixed|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAddonKeysByRoleIds(array $role_ids)
    {
        sort($role_ids);
        $cache_name = 'user_role_addon_keys_' . md5(implode('_', $role_ids));
        return cache_remember(
            $cache_name,
            function () use ($role_ids) {
                $rules = $this->model->where([ [ 'role_id', 'IN', $role_ids ], [ 'status', '=', RoleStatusDict::ON ] ])->field('addon_keys')->select()->toArray();
                if (!empty($rules)) {
                    $temp = [];
                    foreach ($rules as $v) {
                        $temp = array_merge($temp, $v[ 'addon_keys' ]);
                    }
                    $temp = array_unique($temp);

                    if (empty($temp)) return [];
                    return $temp;
                }
                return [];
            },
            [ RoleService::$cache_tag_name, self::$cache_tag_name ]
        );

    }

    /**
     * 用户权限信息
     * @return mixed
     */
    public function getUserRoles($role_ids)
    {
        if (empty($role_ids)){
            return [];
        }
        $cache_name = 'user_roles_' . md5(implode('_', $role_ids));;
        return cache_remember(
            $cache_name,
            function () use ($role_ids) {
                $where = array(
                    [ 'role_id', 'in', $role_ids ]
                );
                return $this->model->where($where)->select()->toArray();
            },
            [ RoleService::$cache_tag_name, self::$cache_tag_name ]
        );
    }

    /**
     * 角色状态修改
     */
    public function modifyStatus(int $id, int $status)
    {
        $this->model->where([ [ 'role_id', '=', $id ] ])->update([ 'status' => $status ]);
        return true;
    }
}
