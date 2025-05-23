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

namespace app\job\schedule;

use app\model\sys\SysScheduleLog;
use core\base\BaseJob;
use think\facade\Log;

/**
 * 队列异步调用定时任务
 */
class AutoClearScheduleLog extends BaseJob
{
    /****执行成功的数据记录留存时间***/
    const SUCCESS_SAVE_DAY = 3;
    /***执行失败的数据记录留存时间***/
    const ERROR_SAVE_DAY = 7;

    public function doJob()
    {
        Log::write('AutoClearScheduleLog 定时清除schedule_log数据' . date('Y-m-d h:i:s'));
        try {
            ( new SysScheduleLog() )->where([
                [ 'execute_time', '<', time() - self::SUCCESS_SAVE_DAY * 86400 ],
                [ 'status', '=', 'success' ],
            ])->delete();
            ( new SysScheduleLog() )->where([
                [ 'execute_time', '<', time() - self::ERROR_SAVE_DAY * 86400 ],
                [ 'status', '=', 'error' ],
            ])->delete();
            return true;
        } catch (\Exception $e) {
            Log::write('AutoClearScheduleLog 定时清除schedule_log数据失败' . date('Y-m-d h:i:s') . $e->getMessage() . $e->getFile() . $e->getLine());
            return false;
        }

    }
}
