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

namespace app\service\admin\upgrade;

use app\dict\sys\BackupDict;
use app\model\sys\SysBackupRecords;
use app\service\admin\sys\SystemService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use core\exception\CommonException;
use core\util\DbBackup;
use think\facade\Cache;
use think\facade\Db;

/**
 * 备份记录表服务层
 */
class BackupRecordsService extends BaseAdminService
{

    protected $upgrade_dir;

    protected $root_path;

    protected $cache_key = 'backup'; // 手动备份

    protected $cache_restore_key = 'restore'; // 恢复

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysBackupRecords();
        $this->root_path = dirname(root_path()) . DIRECTORY_SEPARATOR;
        $this->upgrade_dir = $this->root_path . 'upgrade' . DIRECTORY_SEPARATOR;
    }

    /**
     * 添加备份记录
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data[ 'status' ] = BackupDict::STATUS_READY;
        $data[ 'create_time' ] = time();
        $res = $this->model->create($data);
        return $res->id;
    }

    /**
     * 编辑备份记录
     * @param array $condition
     * @param array $data
     * @return true
     */
    public function edit($condition, array $data)
    {
        $this->model->where($condition)->update($data);
        return true;
    }

    /**
     * 修改备注
     * @param $params
     * @return true
     */
    public function modifyRemark($params)
    {
        return $this->edit([
            [ 'id', '=', $params[ 'id' ] ]
        ], [ 'remark' => $params[ 'remark' ] ]);
    }

    /**
     * 执行完成，更新备份记录的状态
     * @param $backup_key
     * @return void
     */
    public function complete($backup_key)
    {
        $this->model->where([
            [ 'backup_key', '=', $backup_key ],
        ])->update([
            'status' => BackupDict::STATUS_COMPLETE,
            'complete_time' => time()
        ]);
    }

    /**
     * 执行失败，更新备份记录的状态
     * @param $backup_key
     * @return void
     */
    public function failed($backup_key)
    {
        $info = $this->getInfo([
            [ 'backup_key', '=', $backup_key ]
        ], 'id,backup_key');
        if (!empty($info)) {
            $this->del($info[ 'id' ]);
        }
    }

    /**
     * 删除备份记录
     * @param $ids
     * @return true
     */
    public function del($ids)
    {
        $list = $this->model->field('id,backup_key')->where([ [ 'id', 'in', $ids ] ])->select()->toArray();
        if (empty($list)) {
            throw new AdminException('UPGRADE_RECORD_NOT_EXIST');
        }
        try {
            Db::startTrans();

            foreach ($list as $k => $v) {
                // 删除备份文件
                $upgrade_dir = project_path() . 'upgrade' . DIRECTORY_SEPARATOR . $v[ 'backup_key' ] . DIRECTORY_SEPARATOR;
                if (is_dir($upgrade_dir)) {
                    del_target_dir($upgrade_dir, true);
                }
            }

            $this->model->where([ [ 'id', 'in', $ids ] ])->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 恢复前检测文件是否存在
     * @param $id
     * @return void
     */
    public function checkDirExist($id)
    {
        $field = 'id, version, backup_key';
        $info = $this->model->where([
            [ 'id', '=', $id ],
            [ 'status', '=', BackupDict::STATUS_COMPLETE ]
        ])->field($field)->append([ 'backup_dir', 'backup_code_dir', 'backup_sql_dir' ])->findOrEmpty()->toArray();

        if (empty($info)) {
            throw new AdminException('UPGRADE_RECORD_NOT_EXIST');
        }

        // 检测源码目录是否存在
        if (!is_dir($info[ 'backup_code_dir' ])) {
            throw new AdminException('UPGRADE_BACKUP_CODE_NOT_FOUND');
        }

        // 检测数据库目录是否存在
        if (!is_dir($info[ 'backup_sql_dir' ])) {
            throw new AdminException('UPGRADE_BACKUP_SQL_NOT_FOUND');
        }
    }

    /**
     * 检测目录权限
     * @return void
     */
    public function checkPermission()
    {
        $niucloud_dir = $this->root_path . 'niucloud' . DIRECTORY_SEPARATOR;
        $upgrade_dir = $this->root_path . 'upgrade' . DIRECTORY_SEPARATOR;
        $admin_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR;
        $web_dir = $this->root_path . 'web' . DIRECTORY_SEPARATOR;
        $wap_dir = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR;

        if (!is_dir($admin_dir)) throw new CommonException('ADMIN_DIR_NOT_EXIST');
        if (!is_dir($web_dir)) throw new CommonException('WEB_DIR_NOT_EXIST');
        if (!is_dir($wap_dir)) throw new CommonException('UNIAPP_DIR_NOT_EXIST');

        $data = [
            // 目录检测
            'dir' => [
                // 要求可读权限
                'is_readable' => [],
                // 要求可写权限
                'is_write' => []
            ]
        ];

        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $niucloud_dir), 'status' => is_readable($niucloud_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $upgrade_dir), 'status' => is_readable($upgrade_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $admin_dir), 'status' => is_readable($admin_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $web_dir), 'status' => is_readable($web_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $wap_dir), 'status' => is_readable($wap_dir) ];

        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $niucloud_dir), 'status' => is_write($niucloud_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $upgrade_dir), 'status' => is_write($upgrade_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $admin_dir), 'status' => is_write($admin_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $web_dir), 'status' => is_write($web_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $wap_dir), 'status' => is_write($wap_dir) ];

        // 检测全部目录及文件是否可读可写，忽略指定目录

        // 忽略指定目录，admin
        $exclude_admin_dir = [ 'dist', 'node_modules', '.git' ];
        $check_res = checkDirPermissions(project_path() . 'admin', [], $exclude_admin_dir);

        // 忽略指定目录，uni-app
        $exclude_uniapp_dir = [ 'dist', 'node_modules', '.git' ];
        $check_res = array_merge2($check_res, checkDirPermissions(project_path() . 'uni-app', [], $exclude_uniapp_dir));

        // 忽略指定目录，web
        $exclude_web_dir = [ '.nuxt', '.output', 'dist', 'node_modules', '.git' ];
        $check_res = array_merge2($check_res, checkDirPermissions(project_path() . 'web', [], $exclude_web_dir));

        // 忽略指定目录，niucloud
        $exclude_niucloud_dir = [
            'public' . DIRECTORY_SEPARATOR . 'admin',
            'public' . DIRECTORY_SEPARATOR . 'wap',
            'public' . DIRECTORY_SEPARATOR . 'web',
            'public' . DIRECTORY_SEPARATOR . 'upload',
            'public' . DIRECTORY_SEPARATOR . 'file',
            'runtime',
            'vendor',
            '.git'
        ];
        $check_res = array_merge2($check_res, checkDirPermissions(project_path() . 'niucloud', [], $exclude_niucloud_dir));

        if (!empty($check_res[ 'unreadable' ])) {
            foreach ($check_res[ 'unreadable' ] as $item) {
                $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $item), 'status' => false ];
            }
        }
        if (!empty($check_res[ 'not_writable' ])) {
            foreach ($check_res[ 'not_writable' ] as $item) {
                $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $item), 'status' => false ];
            }
        }

        $check_res = array_merge(
            array_column($data[ 'dir' ][ 'is_readable' ], 'status'),
            array_column($data[ 'dir' ][ 'is_write' ], 'status')
        );

        // 是否通过校验
        $data[ 'is_pass' ] = !in_array(false, $check_res);

        return $data;
    }

    /**
     * 备份恢复
     * @param $data
     * @return array
     */
    public function restore($data)
    {

        $field = 'id, version, backup_key';
        $info = $this->model->where([
            [ 'id', '=', $data[ 'id' ] ],
            [ 'status', '=', BackupDict::STATUS_COMPLETE ]
        ])->field($field)->append([ 'backup_dir', 'backup_code_dir', 'backup_sql_dir' ])->findOrEmpty()->toArray();

        if (empty($info)) {
            throw new AdminException('UPGRADE_RECORD_NOT_EXIST');
        }

        // 恢复源码备份
        if (!is_dir($info[ 'backup_code_dir' ])) {
            throw new AdminException('UPGRADE_BACKUP_CODE_NOT_FOUND');
        }

        // 恢复数据库备份
        if (!is_dir($info[ 'backup_sql_dir' ])) {
            throw new AdminException('UPGRADE_BACKUP_SQL_NOT_FOUND');
        }

        $res = [ 'code' => 1, 'data' => [], 'msg' => '' ];
        $cache_data = Cache::get($this->cache_restore_key);
        if (!empty($cache_data) && !empty($cache_data[ 'key' ])) {
            $key = $cache_data[ 'key' ];
        } else {
            $key = uniqid();
        }

        try {
            if ($data[ 'task' ] == '') {

                $key = uniqid();

                $res[ 'data' ] = [
                    'content' => '开始恢复备份',
                    'task' => 'backupCode'
                ];

                $temp = [
                    'key' => $key,
                    'data' => [ $res[ 'data' ] ]
                ];

                Cache::set($this->cache_key, null);

                Cache::set($this->cache_restore_key, $temp);

                // 添加恢复日志
                $this->add([
                    'backup_key' => $key,
                    'content' => "自动备份",
                    'version' => $info[ 'version' ]
                ]);

            } elseif ($data[ 'task' ] == 'backupCode') {
                $res[ 'data' ] = [
                    'content' => '备份源码',
                    'task' => 'backupSql'
                ];

                $temp = Cache::get($this->cache_restore_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_restore_key, $temp);

                //备份源码
                $this->backupCode($key);

            } elseif ($data[ 'task' ] == 'backupSql') {
                $backup_result = $this->backupSql($key);
                if ($backup_result === true) {
                    //备份数据库
                    $res[ 'data' ] = [
                        'content' => '数据库备份完成',
                        'task' => 'restoreCode'
                    ];
                } else {
                    $res[ 'data' ] = [
                        'content' => '',
                        'task' => 'backupSql'
                    ];
                    if ($backup_result % 5 == 0) {
                        $res[ 'data' ][ 'content' ] = $backup_result == 0 ? '数据库开始备份' : '数据库备份中已备份' . $backup_result . '%';
                    }
                }

                $temp = Cache::get($this->cache_restore_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_restore_key, $temp);
            } elseif ($data[ 'task' ] == 'restoreCode') {

                $res[ 'data' ] = [
                    'content' => '恢复源码备份',
                    'task' => 'restoreSql'
                ];

                $temp = Cache::get($this->cache_restore_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_restore_key, $temp);

                // 恢复源码备份
                $root_path = dirname(root_path()) . DIRECTORY_SEPARATOR;
                dir_copy($info[ 'backup_code_dir' ], rtrim($root_path, DIRECTORY_SEPARATOR));

            } elseif ($data[ 'task' ] == 'restoreSql') {
                // 恢复数据库备份
                $db = new DbBackup($info[ 'backup_sql_dir' ]);
                $restore_result = $db->restoreDatabase();

                if ($restore_result === true) {
                    $res[ 'data' ] = [
                        'content' => '数据库恢复完成',
                        'task' => 'restoreData'
                    ];
                } else {
                    $res[ 'data' ] = [
                        'content' => '',
                        'task' => 'restoreSql'
                    ];
                    $restore_progress = $db->getRestoreProgress();
                    if ($restore_progress % 5 == 0) {
                        $res[ 'data' ][ 'content' ] = $restore_progress == 0 ? '数据库开始恢复' : '数据库恢复中已恢复' . $restore_progress . '%';
                    }
                }

                $temp = Cache::get($this->cache_restore_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_restore_key, $temp);
            } elseif ($data[ 'task' ] == 'restoreData') {

                $res[ 'data' ] = [
                    'content' => '恢复数据',
                    'task' => 'restoreComplete'
                ];

                $temp = Cache::get($this->cache_restore_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_restore_key, $temp);

                // todo 恢复数据

            } elseif ($data[ 'task' ] == 'restoreComplete') {

                $res[ 'data' ] = [
                    'content' => '备份恢复完成',
                    'task' => 'end'
                ];

                // 修改备份记录状态
                $this->edit([
                    [ 'backup_key', '=', Cache::get($this->cache_restore_key)[ 'key' ] ],
                ], [
                    'status' => BackupDict::STATUS_COMPLETE,
                    'complete_time' => time()
                ]);

                Cache::set($this->cache_restore_key, null);

                ( new SystemService() )->clearCache(); // 清除缓存
            }
            return $res;
        } catch (\Exception $e) {
            $res[ 'data' ] = [
                'content' => '备份恢复失败，稍后请手动恢复，备份文件路径：' . $info[ 'backup_dir' ] . '，失败原因：' . $e->getMessage() . $e->getFile() . $e->getLine(),
                'task' => 'fail'
            ];

            $fail_reason = [
                'Message' => '失败原因：' . $e->getMessage(),
                'File' => '文件：' . $e->getFile(),
                'Line' => '代码行号：' . $e->getLine(),
                'Trace' => $e->getTrace()
            ];

            // 修改备份记录状态
            $this->edit([
                [ 'backup_key', '=', Cache::get($this->cache_restore_key)[ 'key' ] ],
            ], [
                'fail_reason' => $fail_reason,
                'status' => BackupDict::STATUS_FAIL,
                'complete_time' => time()
            ]);
            Cache::set($this->cache_restore_key, null);

            return $res;
        }
    }

    /**
     * 手动备份
     * @param $data
     * @return array
     */
    public function manualBackup($data)
    {
        $res = [ 'code' => 1, 'data' => [], 'msg' => '' ];
        $key = uniqid();
        $cache_data = Cache::get($this->cache_key);
        if ($cache_data) {
            $key = $cache_data[ 'key' ];
        }
        try {
            if ($data[ 'task' ] == '') {

                $key = uniqid();

                $res[ 'data' ] = [
                    'content' => '开始备份',
                    'task' => 'backupCode'
                ];

                $temp = [
                    'key' => $key,
                    'data' => [ $res[ 'data' ] ]
                ];

                Cache::set($this->cache_key, null);

                Cache::set($this->cache_key, $temp);
                // 添加备份日志
                $this->add([
                    'backup_key' => $key,
                    'content' => "手动备份",
                    'version' => "v" . config('version.version'),
                ]);

            } elseif ($data[ 'task' ] == 'backupCode') {

                $res[ 'data' ] = [
                    'content' => '备份源码',
                    'task' => 'backupSql'
                ];
                $temp = Cache::get($this->cache_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_key, $temp);

                //备份源码
                $this->backupCode($key);

            } elseif ($data[ 'task' ] == 'backupSql') {
                $backup_result = $this->backupSql($key);
                if ($backup_result === true || $backup_result == 100) {
                    //备份数据库
                    $res[ 'data' ] = [
                        'content' => '数据库备份完成',
                        'task' => 'backComplete'
                    ];
                } else {
                    $res[ 'data' ] = [
                        'content' => $backup_result == 0 ? '数据库开始备份' : '数据库备份已备份' . $backup_result . '%',
                        'task' => 'backupSql'
                    ];
                }

                $temp = Cache::get($this->cache_restore_key);
                $temp[ 'data' ][] = $res[ 'data' ];
                Cache::set($this->cache_restore_key, $temp);
            } elseif ($data[ 'task' ] == 'backComplete') {

                $res[ 'data' ] = [
                    'content' => '备份完成',
                    'task' => 'end'
                ];

                // 修改备份记录状态
                $this->complete(Cache::get($this->cache_key)[ 'key' ]);

                Cache::set($this->cache_key, null);
            }
            return $res;
        } catch (\Exception $e) {

            $res[ 'data' ] = [
                'content' => '备份失败，稍后请重新手动备份，失败原因：' . $e->getMessage() . $e->getFile() . $e->getLine(),
                'task' => 'fail'
            ];

            // 修改备份记录状态
            $this->failed(Cache::get($this->cache_key)[ 'key' ]);

            Cache::set($this->cache_key, null);
            return $res;
        }
    }

    /**
     * 获取正在进行的恢复任务
     * @return mixed|null
     */
    public function getRestoreTask()
    {
        $task_data = Cache::get($this->cache_restore_key) ?? [];
        return $task_data;
    }

    /**
     * 获取正在进行的备份任务
     * @return mixed|null
     */
    public function getBackupTask()
    {
        $task_data = Cache::get($this->cache_key) ?? [];
        return $task_data;
    }

    /**
     * 获取备份记录信息
     * @param array $condition
     * @param $field
     * @return array
     */
    public function getInfo(array $condition, $field = 'id, version, backup_key, content, status, fail_reason, create_time, complete_time')
    {
        return $this->model->field($field)->where($condition)->findOrEmpty()->toArray();
    }

    /**
     * 备份记录列表
     * @param $condition
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($condition = [], $field = 'id, version, backup_key, content, status, fail_reason, create_time, complete_time')
    {
        $order = "id desc";
        return $this->model->where($condition)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 备份记录分页列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'id, version, backup_key, content, remark, complete_time';
        $order = "id desc";
        $search_model = $this->model->where([
            [ 'status', '=', BackupDict::STATUS_COMPLETE ],
            [ 'content|version', 'like', '%' . $where[ 'content' ] . '%' ]
        ])->append([ 'backup_dir' ])->field($field)->order($order);
        return $this->pageQuery($search_model);
    }

    /**
     * 备份代码
     * @param $backup_key
     * @return void
     */
    public function backupCode($backup_key)
    {
        $backup_dir = $this->upgrade_dir . $backup_key . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR;

        // 创建目录
        dir_mkdir($backup_dir);

        // 备份admin
        dir_copy($this->root_path . 'admin', $backup_dir . 'admin', exclude_dirs: [ '.vscode', 'node_modules', 'dist' ]);

        // 备份uni-app
        dir_copy($this->root_path . 'uni-app', $backup_dir . 'uni-app', exclude_dirs: [ 'node_modules', 'dist' ]);

        // 备份web
        dir_copy($this->root_path . 'web', $backup_dir . 'web', exclude_dirs: [ 'node_modules', '.nuxt', '.output', 'dist' ]);

        // 备份niucloud全部代码
        $niucloud_dir = $backup_dir . 'niucloud' . DIRECTORY_SEPARATOR;

        dir_copy($this->root_path . 'niucloud', $niucloud_dir, exclude_dirs: [ 'runtime', 'upload' ], exclude_files: ['.user.ini']);
        return true;
    }

    /**
     * 备份数据库
     * @param $backup_key
     * @return void
     */
    public function backupSql($backup_key)
    {
        $backup_dir = $this->upgrade_dir . $backup_key . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        // 创建目录
        dir_mkdir($backup_dir);

        $prefix = config('database.connections.' . config('database.default'))[ 'prefix' ];

        // 不需要备份的表
        $not_need_backup = [
            "{$prefix}sys_schedule_log",
            "{$prefix}sys_user_log",
            "{$prefix}jobs",
            "{$prefix}jobs_failed",
            "{$prefix}sys_upgrade_records",
            "{$prefix}sys_backup_records"
        ];

        $db = new DbBackup($backup_dir, 1024 * 1024 * 2, $not_need_backup, key: $backup_key);

        $result = $db->backupDatabaseSegment();
        if ($result === true) return true;

        return $db->getBackupProgress();
    }

}
