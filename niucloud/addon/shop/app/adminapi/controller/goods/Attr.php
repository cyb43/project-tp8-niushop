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

namespace addon\shop\app\adminapi\controller\goods;

use addon\shop\app\service\admin\goods\AttrService;
use core\base\BaseAdminController;


/**
 * 商品参数控制器
 * Class Attr
 * @package addon\shop\app\adminapi\controller\goods
 */
class Attr extends BaseAdminController
{
    /**
     * 获取商品参数分页列表
     * @description 查看商品参数列表-分页
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "attr_name", "" ],
            [ 'order', '' ],
            [ 'sort', '' ]
        ]);
        return success(( new AttrService() )->getPage($data));
    }

    /**
     * 获取商品参数分页列表
     * @description 查看商品参数列表-全部
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ 'attr_id_arr', [] ],
            [ "attr_name", "" ],
            [ 'order', '' ],
            [ 'sort', '' ]
        ]);
        return success(( new AttrService() )->getList($data));
    }

    /**
     * 商品参数详情
     * @description 查看商品参数详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new AttrService() )->getInfo($id));
    }

    /**
     * 添加商品参数
     * @description 添加商品参数
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "attr_name", "" ],
            [ "attr_value_format", "" ],
            [ "sort", 0 ]
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Attr.add');
        $id = ( new AttrService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品参数编辑
     * @description 编辑商品参数模板
     * @param $id  商品参数id
     * @return \think\Response
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            [ "attr_name", "" ],
            [ "sort", 0 ]
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Attr.edit');
        ( new AttrService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商品参数删除
     * @description 删除商品参数模板
     * @param int $id 商品参数id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new AttrService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 修改商品排序号
     * @description 修改商品参数排序
     * @return \think\Response
     */
    public function modifySort()
    {
        $data = $this->request->params([
            [ 'attr_id', '' ],
            [ 'sort', '' ],
        ]);
        ( new AttrService() )->modifySort($data);
        return success('SUCCESS');
    }

    /**
     * 修改商品参数名称
     * @description 修改商品参数名称
     * @return \think\Response
     */
    public function modifyAttrName()
    {
        $data = $this->request->params([
            [ 'attr_id', '' ],
            [ 'attr_name', '' ],
        ]);
        ( new AttrService() )->modifyAttrName($data);
        return success('SUCCESS');
    }

    /**
     * 修改商品参数值
     * @description 修改商品参数值
     * @return \think\Response
     */
    public function modifyAttrValueFormat()
    {
        $data = $this->request->params([
            [ 'attr_id', '' ],
            [ 'attr_value_format', '' ],
        ]);
        ( new AttrService() )->modifyAttrValueFormat($data);
        return success('SUCCESS');
    }

}
