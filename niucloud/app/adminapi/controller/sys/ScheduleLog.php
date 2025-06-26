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

use app\service\admin\schedule\ScheduleLogService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 计划任务执行记录
 * @description 计划任务执行记录
 */
class ScheduleLog extends BaseAdminController
{
    /**
     * 任务执行记录列表
     * @description 任务执行记录列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['schedule_id', ''],
            ['key', ''],
            ['status', 'all'],
            ['execute_time', []]
        ]);
        return success(data: (new ScheduleLogService())->getPage($data));

    }

    /**
     * 删除计划任务执行记录
     * @description 删除计划任务执行记录
     * @return Response
     */
    public function del()
    {
        $data = $this->request->params([
            [ 'ids', '' ],
        ]);
        (new ScheduleLogService())->del($data[ 'ids' ]);
        return success('DELETE_SUCCESS');
    }

    /**
     * 清空计划任务执行记录
     * @description 清空计划任务执行记录
     * @return Response
     */
    public function clear()
    {
        $data = $this->request->params([
            [ 'schedule_id', '' ],
        ]);
        (new ScheduleLogService())->clear($data);
        return success('SUCCESS');
    }

}
