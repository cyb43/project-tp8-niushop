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

namespace app\adminapi\controller\member;

use app\dict\member\MemberCashOutDict;
use app\dict\pay\TransferDict;
use app\service\admin\member\MemberCashOutService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 提现
 * Class CashOut
 * @description 提现
 * @package app\adminapi\controller\member
 */
class CashOut extends BaseAdminController
{
    /**
     * 提现列表
     * @description 提现列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['member_id', ''],
            ['status', ''],
            ['transfer_type', ''],
            ['create_time', []],
            ['audit_time', []],
            ['transfer_time', []],
            ['cash_out_no', ''],
            ['keywords', '']
        ]);
        return success((new MemberCashOutService())->getPage($data));
    }

    /**
     * 提现详情
     * @description 提现详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success((new MemberCashOutService())->getInfo($id));
    }

    /**
     * 提现申请
     * @description 提现申请
     * @param $id
     * @param $action
     * @return Response
     */
    public function audit($id, $action)
    {
        $data = $this->request->params([
            ['refuse_reason', ''],
        ]);
        (new MemberCashOutService())->audit($id, $action, $data);
        return success();
    }

    /**
     * 转账方式
     * @description 转账方式
     * @return Response
     */
    public function getTransferType()
    {
        return success(TransferDict::getTransferType([], false));
    }

    /**
     * 转账方式
     * @description 转账方式
     * @param $id
     * @return Response
     */
    public function transfer($id)
    {
        $data = $this->request->params([
            ['transfer_voucher', ''],
            ['transfer_remark', ''],
            ['transfer_type', '']
        ]);
        (new MemberCashOutService())->transfer($id, $data);
        return success();
    }

    /**
     * 备注转账信息
     * @description 备注转账信息
     * @param $id
     * @return Response
     */
    public function remark($id)
    {
        $data = $this->request->params([
            ['remark', ''],
        ]);
        (new MemberCashOutService())->remark($id, $data);
        return success();
    }
    /**
     * 状态
     * @description 状态
     * @return Response
     */
    public function getStatusList()
    {
        return success(MemberCashOutDict::getStatus());
    }

    /**
     * 统计数据
     * @description 统计数据
     */
    public function stat()
    {
        return success((new MemberCashOutService())->stat());
    }

    /**
     * 校验数组是否
     * @description 校验数组是否
     * @return Response
     */
    public function checkTransferStatus($id){
        (new MemberCashOutService())->checkTransferStatus($id);
        return success();
    }

    /**
     * 取消
     * @description 取消
     * @param $id
     * @return Response
     */
    public function cancel($id){

        (new MemberCashOutService())->cancel($id);
        return success();
    }
}
