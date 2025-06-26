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

use app\dict\sys\ExportDict;
use app\service\admin\sys\ExportService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 报表导出
 * Class Export
 * @description 报表导出
 * @package app\adminapi\controller\sys
 */
class Export extends BaseAdminController
{

    /**
     * 报表导出列表
     * @description 报表导出列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['export_key', ''],
            ['export_status', ''],
            ['create_time', []],
        ]);
        $res = (new ExportService())->getPage($data);
        return success($res);
    }

    /**
     * 报表导出
     * @description 报表导出
     * @param string $type
     * @return Response
     */
    public function export(string $type){
        $where = $this->request->param();
        return success(data: (new ExportService())->exportData($type, $where));
    }

    /**
     * 检查导出数据源是否为空
     * @description 检查导出数据源是否为空
     * @param string $type
     * @return Response
     */
    public function check(string $type){
        $where = $this->request->param();
        $check = (new ExportService())->checkExportData($type, $where);
        return success($check ? 'SUCCESS' : 'EXPORT_NO_DATA', $check);
    }

    /**
     * 报表删除
     * @description 报表删除
     * @param $id
     * @return Response
     */
    public function del($id)
    {
        $res = (new ExportService())->deleteExport($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取导出状态列表
     * @description 获取导出状态列表
     * @param string $type
     */
    public function getExportStatus()
    {
        return success((new ExportDict())->getStatus());
    }

    /**
     * 获取导出数据类型列表
     * @description 获取导出数据类型列表
     * @return Response
     */
    public function getExportDataType(){
        return success((new ExportService())->getExportDataType());
    }
}
