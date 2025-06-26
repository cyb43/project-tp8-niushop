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

namespace app\adminapi\controller\pay;

use app\dict\pay\RefundDict;
use app\service\admin\pay\RefundService;
use core\base\BaseAdminController;

/**
 * 退款管理
 * Class PayRefund
 * @description 退款管理
 * @package app\adminapi\controller\pay
 */
class PayRefund extends BaseAdminController
{
    /**
     * 退款状态
     * @description 退款状态
     * @return \think\Response
     */
    public function getStatus()
    {
        return success(RefundDict::getStatus());
    }
    /**
     * 退款列表
     * @description 退款列表
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            ['refund_no', ''],
            ['create_time', []],
            ['status', '']
        ]);
        return success(data: (new RefundService())->getPage($data));
    }

    /**
     * 退款详情
     * @description 退款详情
     * @param $refund_no
     * @return \think\Response
     */
    public function detail($refund_no)
    {
        return success(data: (new RefundService())->getDetail($refund_no));
    }

    /**
     * 获取退款方式
     * @description 获取退款方式
     */
    public function getRefundType()
    {
        return success(data:(new RefundDict())->getType());
    }

    /**
     * 转账
     * @description 转账
     * @return \think\Response
     */
    public function transfer()
    {
        $data = $this->request->params([
            ['refund_no', ''],
            ['refund_type', []],
            ['voucher', '']
        ]);
        return success(data:(new RefundService())->refund($data));
    }
}