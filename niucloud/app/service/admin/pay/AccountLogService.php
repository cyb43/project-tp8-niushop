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

namespace app\service\admin\pay;

use app\model\pay\AccountLog;
use core\base\BaseAdminService;
use think\db\exception\DbException;

/**
 * 账单服务层
 * Class AccountLogService
 * @package app\service\admin\pay
 */
class AccountLogService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new AccountLog();
    }

    /**
     * 获取账单列表
     * @param array $where
     * @return array
     * @throws DbException
     */
    public function getPage(array $where = [])
    {
        $field = 'id, type, money, trade_no, create_time';
        $search_model = $this->model->withSearch([ 'create_time', 'type', 'trade_no' ], $where)->field($field)->append([ 'type_name', 'money', 'pay_info' ])->order('create_time desc');
        return $this->pageQuery($search_model);
    }

    /**
     * 获取账单详情
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'id, type, money, trade_no, create_time';
        return $this->model->where([ [ 'id', '=', $id ] ])->field($field)->append([ 'type_name', 'pay_info' ])->findOrEmpty()->toArray();
    }

    /**
     * 统计数据
     * @return array
     */
    public function stat()
    {
        return [
            'pay' => $this->model->where([ [ 'type', '=', 'pay' ] ])->sum("money") * 1,
            'refund' => $this->model->where([ [ 'type', '=', 'refund' ] ])->sum("money") * -1,
            'transfer' => $this->model->where([ [ 'type', '=', 'transfer' ] ])->sum("money") * -1,
        ];
    }

}
