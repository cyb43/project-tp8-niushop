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

namespace app\job\upgrade;

use app\dict\sys\BackupDict;
use app\dict\sys\UpgradeDict;
use app\model\sys\SysUpgradeRecords;
use app\service\admin\upgrade\BackupRecordsService;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 队列异步调用定时任务
 */
class AutoClearUpgradeRecords extends BaseJob
{
    const DAY = 7; // 清除7天前的数据

    public function doJob()
    {
        Log::write('AutoClearUpgradeRecords 定时清除升级记录、备份记录数据' . date('Y-m-d h:i:s'));
        try {
            // 清除7天前的升级记录数据
            ( new SysUpgradeRecords() )->where([
                [ 'create_time', '<', time() - self::DAY * 86400 ],
                [ 'status', 'in', [ UpgradeDict::STATUS_READY, UpgradeDict::STATUS_FAIL ] ]
            ])->delete();

            // 清除7天前的备份记录数据
            $backup_records_service = new BackupRecordsService();
            $backup_records = $backup_records_service->getList([
                [ 'create_time', '<', time() - self::DAY * 86400 ],
                [ 'status', 'in', [ BackupDict::STATUS_READY, BackupDict::STATUS_FAIL ] ]
            ], 'id');

            $backup_records_service->del(array_column($backup_records, 'id'));

            return true;
        } catch (\Exception $e) {
            Log::write('AutoClearScheduleLog 定时清除升级记录、备份记录数据失败' . date('Y-m-d h:i:s') . $e->getMessage() . $e->getFile() . $e->getLine());
            return false;
        }

    }
}
