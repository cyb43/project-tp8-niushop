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

namespace addon\shop\app\adminapi\controller\delivery;

use addon\shop\app\service\admin\delivery\ElectronicSheetService;
use core\base\BaseAdminController;


/**
 * 电子面单控制器
 * Class ElectronicSheet
 * @package addon\shop\app\adminapi\controller\delivery
 */
class ElectronicSheet extends BaseAdminController
{
    /**
     * @description 查看电子面单分页列表
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "template_name", "" ],
            [ "express_company_id", "" ],
            [ "is_notice", "" ],
            [ "status", "" ],
        ]);
        $field = 'id,template_name,express_company_id,customer_name,pay_type,is_notice,status,is_default,create_time';
        return success(( new ElectronicSheetService() )->getPage($data, $field));
    }

    /**
     * @description 查看电子面单列表
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "template_name", "" ],
            [ "express_company_id", "" ],
            [ "is_notice", "" ],
            [ "status", "" ],
        ]);
        $field = 'id,template_name,express_company_id,customer_name,pay_type,is_notice,status,is_default,create_time';
        return success(( new ElectronicSheetService() )->getList($data, $field));
    }

    /**
     * @description 电子面单详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new ElectronicSheetService() )->getInfo($id));
    }

    /**
     * 添加电子面单
     * @description 添加电子面单
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "template_name", "" ],
            [ "express_company_id", 0 ],
            [ "customer_name", "" ],
            [ "customer_pwd", "" ],
            [ "send_site", "" ],
            [ "send_staff", "" ],
            [ "month_code", "" ],
            [ "pay_type", 0 ],
            [ "is_notice", 0 ],
            [ "status", 0 ],
            [ 'exp_type', 0 ],
            [ "print_style", "" ],
            [ "is_default", 0 ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\delivery\ElectronicSheet.add');
        $id = ( new ElectronicSheetService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 电子面单编辑
     * @description 编辑电子面单
     * @param $id  电子面单id
     * @return \think\Response
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            [ "template_name", "" ],
            [ "express_company_id", 0 ],
            [ "customer_name", "" ],
            [ "customer_pwd", "" ],
            [ "send_site", "" ],
            [ "send_staff", "" ],
            [ "month_code", "" ],
            [ "pay_type", 0 ],
            [ "is_notice", 0 ],
            [ "status", 0 ],
            [ 'exp_type', 0 ],
            [ "print_style", "" ],
            [ "is_default", 0 ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\delivery\ElectronicSheet.edit');
        ( new ElectronicSheetService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 电子面单删除
     * @description 删除电子面单
     * @param $id  电子面单id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new ElectronicSheetService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 电子面单设置默认模板
     * @description 电子面单设置默认模板
     * @param int $id
     * @return array|\think\Response
     */
    public function setDefault(int $id)
    {
        ( new ElectronicSheetService() )->setDefault($id);
        return success('EDIT_SUCCESS');
    }

    /**
     * 设置 电子面单配置
     * @description 设置电子面单配置
     * @return array|\think\Response
     */
    public function setConfig()
    {
        $data = $this->request->params([
            [ "interface_type", "" ],
            [ "kdniao_id", "" ],
            [ "kdniao_api_key", "" ],
            [ 'server_port1', '8000' ],
            [ 'server_port2', '18000' ],
            [ 'https_port', '8443' ]
        ]);
        ( new ElectronicSheetService() )->setConfig($data);
        return success();
    }

    /**
     * 获取 电子面单设置
     * @description 查看电子面单设置
     * @return array|\think\Response
     */
    public function getConfig()
    {
        return success(( new ElectronicSheetService() )->getConfig());
    }

    /**
     * @description 查看邮费支付方式类型
     * @return array|\think\Response
     */
    public function getPayType()
    {
        return success(( new ElectronicSheetService() )->getPayType());
    }

    /**
     * 打印电子面单
     * @description 打印电子面单
     * @return array|\think\Response
     */
    public function printElectronicSheet()
    {
        $data = $this->request->params([
            [ 'print_type', 'single' ], // 打印方式，single：单订单，multiple：多订单
            [ 'order_id', '' ], // 订单id
            [ 'electronic_sheet_id', 0 ], // 电子面单id
            [ 'order_goods_ids', '' ],
            [ 'list', [] ] // 多个包裹，格式：[{ delivery_id : 0, electronic_sheet_id : 0 }]
//            [ 'is_delivery', 0 ], // 是否发货 todo 后续扩展
        ]);
        $res = ( new ElectronicSheetService() )->printElectronicSheet($data);
        if (count($res) == 1) {
            if ($res[ 0 ][ 'success' ]) {
                return success($res);
            } else {
                return fail('[' . $res[ 0 ][ 'result_code' ] . '] ' . $res[ 0 ][ 'reason' ], [], $res[ 0 ][ 'result_code' ]);
            }
        } else {
            return success($res);
        }

    }

}
