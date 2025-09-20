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

namespace app\service\admin\schedule;


use app\dict\schedule\ScheduleDict;
use app\service\core\schedule\CoreScheduleService;
use core\base\BaseAdminService;
use core\exception\AdminException;


/**
 * 计划任务服务层
 * Class CoreCronService
 * @package app\service\core\cron
 */
class ScheduleService extends BaseAdminService
{


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取自动任务列表
     * @param array $data
     * @return array
     */
    public function getPage(array $data = [])
    {
        return (new CoreScheduleService())->getPage($data);
    }

    /**
     * 获取信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id){
        return (new CoreScheduleService())->getInfo($id);
    }
    /**
     * 启用或关闭
     * @param int $id
     * @param $status
     * @return true
     */
    public function modifyStatus(int $id, $status)
    {
        return (new CoreScheduleService())->modifyStatus($id, $status);
    }

    /**
     * 添加
     * @param array $data
     * @return true
     */
    public function add(array $data)
    {
        if (!empty($data['time'])) {
            $this->checkTimeCycle($data['time']);
        }

        (new CoreScheduleService())->add($data);
        return true;

    }

    /**
     * 编辑
     * @param int $id
     * @param array $data
     * @return true
     */
    public function edit(int $id, array $data)
    {
        if (!empty($data['time'])) {
            $this->checkTimeCycle($data['time']);
        }
        (new CoreScheduleService())->edit($id, $data);
        return true;
    }

    /**
     * 校验任务时间配置
     * @param array $time
     * @throws AdminException
     */
    protected function checkTimeCycle(array $time)
    {
        $type = $time['type'] ?? null;

        // 配置要求字段
        $rules = [
            ScheduleDict::MIN   => ['min'],
            ScheduleDict::HOUR  => ['hour', 'min'],
            ScheduleDict::DAY   => ['day', 'hour', 'min'],
            ScheduleDict::WEEK  => ['hour', 'min'],
            ScheduleDict::MONTH => ['day', 'hour', 'min'],
        ];

        if (!isset($rules[$type])) {
            throw new AdminException('TASK_CYCLE_ERROR');
        }

        foreach ($rules[$type] as $field) {
            if (empty($time[$field])) {
                throw new AdminException('TASK_CYCLE_ERROR');
            }
        }
    }

    /**
     * 删除
     * @param int $id
     * @return true
     */
    public function del(int $id)
    {
        (new CoreScheduleService())->del($id);
        return true;
    }

    /**
     * 计划任务模板
     * @return array|null
     */
    public function getTemplateList(){
        return (new CoreScheduleService())->getTemplateList();
    }

    /**
     * 执行一次计划任务
     * @return true
     */
    public function doSchedule(int $id)
    {
        return (new CoreScheduleService())->doSchedule($id);
    }

    /**
     * 重置定时任务
     * @return true
     */
    public function resetSchedule()
    {
        return (new CoreScheduleService())->resetSchedule();
    }


}
