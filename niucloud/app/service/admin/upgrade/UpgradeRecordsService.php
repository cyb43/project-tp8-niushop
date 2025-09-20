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

use app\dict\sys\UpgradeDict;
use app\model\sys\SysUpgradeRecords;
use core\base\BaseAdminService;

/**
 * 升级记录表服务层
 */
class UpgradeRecordsService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysUpgradeRecords();
    }

    /**
     * 添加升级记录
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data[ 'create_time' ] = time();
        $res = $this->model->create($data);

        // 关联备份记录
        $is_need_backup = $data['is_need_backup'] ?? true;
        if ($is_need_backup) {
            ( new BackupRecordsService() )->add([
                'version' => $data[ 'current_version' ],
                'backup_key' => $data[ 'upgrade_key' ],
                'content' => '自动备份'
            ]);
        }
        return $res->id;
    }

    /**
     * 编辑升级记录
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
     * 执行完成，更新升级记录状态，备份记录状态
     * @param $upgrade_key
     * @return void
     */
    public function complete($upgrade_key)
    {
        // 记录升级日志
        $this->edit([
            [ 'upgrade_key', '=', $upgrade_key ]
        ], [
            'status' => UpgradeDict::STATUS_COMPLETE,
            'complete_time' => time()
        ]);
        ( new BackupRecordsService() )->complete($upgrade_key);
    }

    /**
     * 执行失败，更新升级记录状态，备份记录状态
     * @param $upgrade_key
     * @param array $fail_reason
     * @return void
     */
    public function failed($upgrade_key, $fail_reason = [])
    {
        $this->model->where([ [ 'upgrade_key', '=', $upgrade_key ] ])->update(['status' => UpgradeDict::STATUS_FAIL, 'fail_reason' => $fail_reason ]);
        ( new BackupRecordsService() )->failed($upgrade_key);
    }

    /**
     * 获取升级记录信息
     * @param array $condition
     * @param $field
     * @return array
     */
    public function getInfo(array $condition, $field = 'id, upgrade_key, app_key, name, content, prev_version, current_version, status, fail_reason, create_time, complete_time')
    {
        return $this->model->field($field)->where($condition)->findOrEmpty()->toArray();
    }

    /**
     * 升级记录分页列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'id, upgrade_key, app_key, name, content, prev_version, current_version, complete_time, create_time, status, fail_reason';
        $order = "id desc";
        $search_model = $this->model->where([
//            [ 'status', '=', UpgradeDict::STATUS_COMPLETE ],
            [ 'name|content|current_version', 'like', '%' . $where[ 'name' ] . '%' ]
        ])->field($field)->order($order)->append(['status_name']);
        return $this->pageQuery($search_model);
    }

    /**
     * 刪除升级记录
     * @param $ids
     * @return void
     */
    public function del($ids) {
        $this->model->where([['id', 'in', $ids]])->delete();
    }
}
