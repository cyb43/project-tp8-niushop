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

use addon\shop\app\service\admin\delivery\ShippingTemplateService;
use core\base\BaseAdminController;


/**
 * 运费模板控制器
 * Class ShippingTemplate
 * @package addon\shop\app\adminapi\controller\delivery
 */
class ShippingTemplate extends BaseAdminController
{
    /**
     * 运费模板分页列表
     * @return void
     */
    public function pages()
    {
        $data = $this->request->params([
            [ 'template_name', '' ],
        ]);
        return success(( new ShippingTemplateService() )->getPage($data));
    }

    /**
     * 运费模板列表
     * @description 查看运费模板列表
     * @return void
     */
    public function lists()
    {
        $data = $this->request->params([
            [ 'template_name', '' ],
        ]);
        return success(( new ShippingTemplateService() )->getList($data));
    }

    /**
     * 运费模板详情
     * @description 查看运费模板详情
     * @param int $template_id
     * @return void
     */
    public function info(int $template_id)
    {
        return success(( new ShippingTemplateService() )->getInfo($template_id));
    }

    /**
     * 添加运费模板
     * @description 添加运费模板
     * @return void
     */
    public function add()
    {
        $data = $this->request->params([
            [ 'template_name', '' ],
            [ 'fee_type', '' ],
            [ 'area', [] ],
            [ 'no_delivery', 0 ],
            [ 'is_free_shipping', 0 ]
        ]);
        return success('ADD_SUCCESS', ( new ShippingTemplateService() )->add($data));
    }

    /**
     * 编辑运费模板
     * @description 编辑运费模板
     * @return void
     */
    public function edit(int $template_id)
    {
        $data = $this->request->params([
            [ 'template_name', '' ],
            [ 'fee_type', '' ],
            [ 'area', [] ],
            [ 'no_delivery', 0 ],
            [ 'is_free_shipping', 0 ]
        ]);
        return success('EDIT_SUCCESS', ( new ShippingTemplateService() )->edit($template_id, $data));
    }

    /**
     * 删除模板
     * @description 删除运费模板
     * @param int $template_id
     * @return \think\Response
     */
    public function del(int $template_id)
    {
        return success('DELETE_SUCCESS', ( new ShippingTemplateService() )->delete($template_id));
    }
}
