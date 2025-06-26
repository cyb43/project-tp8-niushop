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

namespace app\adminapi\controller\addon;

use app\service\admin\upgrade\BackupRecordsService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 备份管理
 * Class Backup
 * @description 备份管理
 * @package app\adminapi\controller\addon
 */
class Backup extends BaseAdminController
{

    /**
     * 获取升级记录分页列表
     * @description 获取升级记录分页列表
     * @return Response
     */
    public function getRecords()
    {
        $data = $this->request->params([
            [ "content", "" ],
        ]);
        return success(( new BackupRecordsService() )->getPage($data));
    }

    /**
     * 修改备注
     * @description 修改备注
     * @return Response
     */
    public function modifyRemark()
    {
        $data = $this->request->params([
            [ "id", "" ],
            [ 'remark', '' ]
        ]);
        ( new BackupRecordsService() )->modifyRemark($data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 恢复前检测文件是否存在
     * @description 恢复前检测文件是否存在
     * @return Response
     */
    public function checkDirExist()
    {
        $data = $this->request->params([
            [ "id", 0 ],
        ]);
        return success(( new BackupRecordsService() )->checkDirExist($data[ 'id' ]));
    }

    /**
     * 检测目录权限
     * @description 检测目录权限
     * @return Response
     */
    public function checkPermission()
    {
        return success(( new BackupRecordsService() )->checkPermission());
    }

    /**
     * 恢复备份
     * @description 恢复备份
     * @return Response
     */
    public function restoreBackup()
    {
        $data = $this->request->params([
            [ 'id', 0 ],
            [ 'task', '' ]
        ]);
        $res = ( new BackupRecordsService() )->restore($data);
        return $res;
    }

    /**
     * 删除升级记录
     * @description 删除升级记录
     * @return Response
     */
    public function deleteRecords()
    {
        $data = $this->request->params([
            [ "ids", [] ],
        ]);
        ( new BackupRecordsService() )->del($data[ 'ids' ]);
        return success('DELETE_SUCCESS');
    }

    /**
     * 手动备份
     * @description 手动备份
     * @return Response
     */
    public function manualBackup()
    {
        $data = $this->request->params([
            [ 'task', '' ]
        ]);
        $res = ( new BackupRecordsService() )->manualBackup($data);
        return $res;
    }

    /**
     * 获取正在进行的恢复任务
     * @description 获取正在进行的恢复任务
     * @return Response
     */
    public function getRestoreTask()
    {
        return success(( new BackupRecordsService() )->getRestoreTask());
    }

    /**
     * 获取正在进行的备份任务
     * @description 获取正在进行的备份任务
     * @return Response
     */
    public function getBackupTask()
    {
        return success(( new BackupRecordsService() )->getBackupTask());
    }
}
