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

use core\base\BaseAdminController;
use addon\shop\app\service\admin\delivery\CompanyService;


/**
 * 物流公司控制器
 * Class Company
 * @description 物流公司
 * @package addon\shop\app\adminapi\controller\delivery
 */
class Company extends BaseAdminController
{
    /**
     * 获取物流公司列表
     * @description 查看物流公司列表-分页
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "company_name", "" ],
            [ 'electronic_sheet_switch', '' ]
        ]);
        return success(( new CompanyService() )->getPage($data));
    }

    /**
     * 获取物流公司列表
     * @description 查看物流公司列表-全部
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "company_name", "" ],
            [ 'electronic_sheet_switch', '' ]
        ]);
        return success(( new CompanyService() )->getList($data));
    }

    /**
     * 物流公司详情
     * @description 查看详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new CompanyService() )->getInfo($id));
    }

    /**
     * 添加物流公司
     * @description 添加
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "company_name", "" ],
            [ "logo", "" ],
            [ "url", "" ],
            [ "express_no", "" ], // 物流公司编号(用于物流跟踪)
            [ 'express_no_electronic_sheet', '' ], // 物流公司编号(用于电子面单)
            [ 'electronic_sheet_switch', 0 ], // 是否支持电子面单（0：不支持，1：支持）
            [ 'print_style', '' ], // 电子面单打印模板样式，template_size为空表示默认，json字符串，格式：[{"template_name":"二联150 100*150","template_size":""},{"template_name":"二联180 100*180","template_size":"180"}]
            [ 'exp_type', '' ] // 物流公司业务类型，json字符串，格式：[{"value":1,"text":"特快专递"},{"value":8,"text":"代收到付"},{"value":9,"text":"快递包裹"}]
        ]);
        $this->validate($data, 'addon\shop\app\validate\delivery\Company.add');
        $id = ( new CompanyService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 物流公司编辑
     * @description 编辑
     * @param int $id 物流公司id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "company_name", "" ],
            [ "logo", "" ],
            [ "url", "" ],
            [ "express_no", "" ], // 物流公司编号(用于物流跟踪)
            [ 'express_no_electronic_sheet', '' ], // 物流公司编号(用于电子面单)
            [ 'electronic_sheet_switch', 0 ], // 是否支持电子面单（0：不支持，1：支持）
            [ 'print_style', '' ], // 电子面单打印模板样式，template_size为空表示默认，json字符串，格式：[{"template_name":"二联150 100*150","template_size":""},{"template_name":"二联180 100*180","template_size":"180"}]
            [ 'exp_type', '' ] // 物流公司业务类型，json字符串，格式：[{"value":1,"text":"特快专递"},{"value":8,"text":"代收到付"},{"value":9,"text":"快递包裹"}]
        ]);
        $this->validate($data, 'addon\shop\app\validate\delivery\Company.edit');
        ( new CompanyService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 物流公司删除
     * @description 删除
     * @param int $id 物流公司id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new CompanyService() )->del($id);
        return success('DELETE_SUCCESS');
    }

}
