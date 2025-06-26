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



use app\model\sys\SysUserLog;
use core\base\BaseAdminService;
use Exception;

/**
 * 操作日志
 * Class UserLogService
 * @package app\service\admin\user
 */
class UserLogService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
       $this->model = new SysUserLog();
    }

    /**
     * 获取用户日志
     * @param array $where
     * @return array
     */
    public function getPage(array $where)
    {
        $field = 'id, ip, uid, username, operation, url, params, type, create_time';
        $order = 'create_time desc';
        $search_model = $this->model->withSearch(['username', 'create_time', 'uid', 'ip', 'type', 'url'], $where)->field($field)->order($order);
        return $this->pageQuery($search_model);
    }

    /**
     * 日志详情
     * @param int $id
     * @return array
     */
    public function getInfo(int $id){
        $where = array(
            ['id', '=', $id],
        );
        $field = 'id, ip, uid, username, operation, url, params, type, create_time';
        return $this->model->where($where)->field($field)->findOrEmpty()->toArray();
    }

    /**
     * 添加用户（添加用户，不添加站点）
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function add(array $data){
        $res = $this->model->create($data);
        return $res->id;
    }

    public function destroy()
    {
        (new SysUserLog())->whereRaw("1=1")->delete();
    }
}