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

namespace addon\shop\app\adminapi\controller\order;

use addon\shop\app\service\admin\order\InvoiceService;
use core\base\BaseAdminController;
use think\Response;

class Invoice extends BaseAdminController
{
    /**
     * 发票列表
     * @description  查看发票列表-分页
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['create_time', []],
            ['invoice_time', []],
            ['header_name', ''],
            ['header_type', 'all'],
            ['is_invoice', 'all'],
        ]);
        return success((new InvoiceService())->getPage($data));
    }

    /**
     * 发票信息
     * @description 查看订单发票信息
     * @param int $order_id
     * @return Response
     */
    public function info(int $id)
    {
        return success((new InvoiceService())->getInfo($id));
    }

    /**
     * @description 开具发表
     * @param int $id
     * @return Response
     */
    public function invoicing(int $id) {
        $data = $this->request->params([
            ['remark', ''],
            ['invoice_number', ''],
            ['invoice_voucher', ''],
        ]);
        return success('SUCCESS', data:(new InvoiceService())->invoicing($id, $data));
    }

}
