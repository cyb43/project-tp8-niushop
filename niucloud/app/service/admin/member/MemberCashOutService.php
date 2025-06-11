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

namespace app\service\admin\member;

use app\dict\member\MemberCashOutDict;
use app\dict\pay\TransferDict;
use app\model\member\MemberCashOut;
use app\service\core\member\CoreMemberCashOutService;
use core\base\BaseAdminService;
use core\exception\CommonException;

/**
 * 会员提现服务层
 */
class MemberCashOutService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new MemberCashOut();
    }

    /**
     * 会员提现列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {

        $field = 'id,cash_out_no,member_cash_out.member_id,account_type,transfer_type,transfer_realname,transfer_mobile,transfer_bank,transfer_account,transfer_fail_reason,transfer_status,transfer_time,apply_money,rate,service_money,member_cash_out.money,audit_time,member_cash_out.status,member_cash_out.remark,member_cash_out.create_time,refuse_reason,transfer_no, transfer_payment_code';
        $member_where = [];
        if(!empty($where['keywords']))
        {
            $member_where = [['member.member_no|member.nickname|member.mobile|member.username', '=', $where['keywords']]];
        }
        $search_model = $this->model
            ->withSearch(['member_id','status', 'join_create_time' => 'create_time', 'audit_time', 'transfer_time', 'transfer_type', 'cash_out_no'],$where)->with(['transfer'])
            ->withJoin(["member" => ['member_id', 'member_no', 'username', 'mobile', 'nickname', 'headimg']])->where($member_where)->field($field)
            ->order('create_time desc')->append(['status_name', 'transfer_status_name', 'transfer_type_name', 'account_type_name']);
        return $this->pageQuery($search_model);
    }

    /**
     * 提现详情
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'id,cash_out_no,member_id,account_type,transfer_type,transfer_realname,transfer_mobile,transfer_bank,transfer_account,transfer_fail_reason,transfer_status,transfer_time,apply_money,rate,service_money,money,audit_time,status,remark,create_time,refuse_reason,transfer_no, transfer_payment_code';
        return $this->model->where([['id', '=', $id]])->with(['memberInfo', 'transfer'])->field($field)->append(['status_name', 'transfer_status_name', 'transfer_type_name', 'account_type_name'])->findOrEmpty()->toArray();
    }

    /**
     * @param int $id
     * @param string $action
     * @param $data
     * @return true|null
     */
    public function audit(int $id, string $action, $data){
        $core_member_cash_out_service = new CoreMemberCashOutService();
        return $core_member_cash_out_service->audit($id, $action, $data);
    }


    /**
     * 转账
     * @param int $id
     * @param array $data
     * @return true
     */
    public function transfer(int $id, array $data){
        $core_member_cash_out_service = new CoreMemberCashOutService();
        $cash_out = $core_member_cash_out_service->find($id);
        if ($cash_out->isEmpty()) throw new CommonException('RECHARGE_LOG_NOT_EXIST');
        if ($cash_out['status'] != MemberCashOutDict::WAIT_TRANSFER && $cash_out['transfer_type'] == TransferDict::WECHAT) throw new CommonException('CASH_OUT_WECHAT_ACCOUNT_NOT_ALLOW_ADMIN');
        return $core_member_cash_out_service->transfer($id, $data);
    }

    /**
     * 备注
     * @param int $id
     * @param array $data
     * @return true
     */
    public function remark(int $id, array $data){
        $core_member_cash_out_service = new CoreMemberCashOutService();
        return $core_member_cash_out_service->remark($id, $data);
    }
    /**
     * 统计数据
     * @return array
     */
    public function stat()
    {
        $stat = [];
        //已提现
        $stat['transfered'] = $this->model->where([['status', '=', MemberCashOutDict::TRANSFERED]])->sum("apply_money");
        //所有金额（包括提现中，已提现）
        $all_money = $this->model->where([['status', '>=', 0]])->sum("apply_money");

        $stat['cash_outing'] = $all_money - $stat['transfered'];
        return $stat;
    }

    /**
     * 检测实际的转账状态
     * @param int $id
     * @return true
     */
    public function checkTransferStatus(int $id){
        $core_member_cash_out_service = new CoreMemberCashOutService();
        return $core_member_cash_out_service->checkTransferStatus($id);
    }

    /**
     * 取消体现
     * @param int $id
     * @return mixed
     */
    public function cancel(int $id){
        $core_member_cash_out_service = new CoreMemberCashOutService();
        return $core_member_cash_out_service->cancel($id);
    }

}
