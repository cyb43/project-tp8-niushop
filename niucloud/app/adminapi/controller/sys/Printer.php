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

use app\service\admin\sys\PrinterService;
use app\service\admin\sys\PrinterTemplateService;
use core\base\BaseAdminController;
use think\Response;


/**
 * 小票打印机控制器
 * Class Printer
 * @description 小票打印机
 * @package app\adminapi\controller\sys
 */
class Printer extends BaseAdminController
{

    /**
     * 获取小票打印机分页列表
     * @description 获取小票打印机分页列表
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "printer_name", "" ],
        ]);
        return success(( new PrinterService() )->getPage($data));
    }

    /**
     * 获取小票打印机列表
     * @description 获取小票打印机列表
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "printer_name", "" ],
        ]);
        return success(( new PrinterService() )->getList($data));
    }

    /**
     * 小票打印机详情
     * @description 小票打印机详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new PrinterService() )->getInfo($id));
    }

    /**
     * 添加小票打印机
     * @description 添加小票打印机
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "brand", "" ],
            [ "printer_name", "" ],
            [ "printer_code", "" ],
            [ "printer_key", "" ],
            [ "open_id", "" ],
            [ "apikey", "" ],
            [ 'template_type', '' ],
            [ 'trigger', '' ],
            [ "value", "" ],
            [ "print_width", "" ],
            [ "status", 0 ],
        ]);
        $this->validate($data, 'app\validate\sys\Printer.add');
        $id = ( new PrinterService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 小票打印机编辑
     * @description 小票打印机编辑
     * @param int $id 小票打印机id
     * @return Response
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            [ "brand", "" ],
            [ "printer_name", "" ],
            [ "printer_code", "" ],
            [ "printer_key", "" ],
            [ "open_id", "" ],
            [ "apikey", "" ],
            [ 'template_type', '' ],
            [ 'trigger', '' ],
            [ "value", "" ],
            [ "print_width", "" ],
            [ "status", 0 ],
        ]);
        $this->validate($data, 'app\validate\sys\Printer.edit');
        ( new PrinterService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 修改小票打印机状态
     * @description 修改小票打印机状态
     * @return \think\Response
     */
    public function modifyStatus()
    {
        $data = $this->request->params([
            [ "printer_id", 0 ],
            [ "status", 0 ],
        ]);
        ( new PrinterService() )->modifyStatus($data);
        return success('SUCCESS');
    }

    /**
     * 小票打印机删除
     * @description 小票打印机删除
     * @param int $id 小票打印机id
     * @return Response
     */
    public function del(int $id)
    {
        ( new PrinterService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取小票打印模板分页列表
     * @description 获取小票打印模板分页列表
     * @return \think\Response
     */
    public function templatePageLists()
    {
        $data = $this->request->params([
            [ "template_type", "" ],
            [ "template_name", "" ],
        ]);
        return success(( new PrinterTemplateService() )->getPage($data));
    }

    /**
     * 获取小票打印模板列表
     * @description 获取小票打印模板列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function templateLists()
    {
        $data = $this->request->params([
            [ "template_type", "" ],
            [ "template_name", "" ],
        ]);
        return success(( new PrinterTemplateService() )->getList($data));
    }

    /**
     * 小票打印模板详情
     * @description 小票打印模板详情
     * @param int $id
     * @return \think\Response
     */
    public function templateInfo(int $id)
    {
        return success(( new PrinterTemplateService() )->getInfo($id));
    }

    /**
     * 添加小票打印模板
     * @description 添加小票打印模板
     * @return \think\Response
     */
    public function templateAdd()
    {
        $data = $this->request->params([
            [ "template_type", "" ],
            [ "template_name", "" ],
            [ "value", "" ],
        ]);
        $this->validate($data, 'app\validate\sys\PrinterTemplate.add');
        $id = ( new PrinterTemplateService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 小票打印模板编辑
     * @description 小票打印模板编辑
     * @param int $id 小票打印模板id
     * @return Response
     */
    public function templateEdit(int $id)
    {
        $data = $this->request->params([
            [ "template_type", "" ],
            [ "template_name", "" ],
            [ "value", "" ],
        ]);
        $this->validate($data, 'app\validate\sys\PrinterTemplate.edit');
        ( new PrinterTemplateService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 小票打印模板删除
     * @description 小票打印模板删除
     * @param int $id 小票打印模板id
     * @return Response
     */
    public function templateDel(int $id)
    {
        ( new PrinterTemplateService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取小票打印模板类型
     * @description 获取小票打印模板类型
     * @return \think\Response
     */
    public function getType()
    {
        return success(( new PrinterService() )->getType());
    }

    /**
     * 获取小票打印机设备品牌
     * @description 获取小票打印机设备品牌
     * @return \think\Response
     */
    public function getBrand()
    {
        $data = $this->request->params([
            [ "brand", "" ],
        ]);
        return success(( new PrinterService() )->getBrand($data[ 'brand' ]));
    }

    /**
     * 测试打印
     * @description 测试打印
     * @param int $id
     * @return \think\Response
     */
    public function testPrint(int $id)
    {
        ( new PrinterService() )->testPrint($id);
        return success('SUCCESS');
    }

    /**
     * 刷新打印机token
     * @description 刷新打印机token
     * @param int $id
     * @return \think\Response
     */
    public function refreshToken(int $id)
    {
        ( new PrinterService() )->refreshToken($id);
        return success('SUCCESS');
    }

    /**
     * 打印小票内容
     * @description 打印小票内容
     * @return \think\Response
     */
    public function printTicket()
    {
        $data = $this->request->params([
            [ "type", "" ], // 小票模板类型
            [ "trigger", "" ], // 触发时机
            [ 'business', [] ] // 业务参数，根据自身业务传值
        ]);

        $res = ( new PrinterService() )->printTicket($data);
        if ($res[ 'code' ] == 0) {
            return success('SUCCESS');
        } else {
            return fail($res[ 'message' ]);
        }
    }

}
