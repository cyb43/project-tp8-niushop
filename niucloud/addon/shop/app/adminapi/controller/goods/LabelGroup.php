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

use addon\shop\app\service\admin\goods\LabelGroupService;
use core\base\BaseAdminController;


/**
 * 商品标签分组控制器
 * Class LabelGroup
 * @package addon\shop\app\adminapi\controller\goods
 */
class LabelGroup extends BaseAdminController
{
    /**
     * 获取商品标签分组分页列表
     * @description 获取商品标签分组列表-分页
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "group_name", "" ],
            [ 'order', '' ],
            [ 'sort', '' ]
        ]);
        return success(( new LabelGroupService() )->getPage($data));
    }

    /**
     * 获取商品标签分组列表
     * @description 获取商品标签分组列表-全部
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "group_name", "" ]
        ]);
        return success(( new LabelGroupService() )->getList($data));
    }

    /**
     * 商品标签分组详情
     * @description 查看商品标签分组详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new LabelGroupService() )->getInfo($id));
    }

    /**
     * 添加商品标签分组
     * @description 添加商品标签分组
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "group_name", "" ],
            [ "sort", 0 ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\LabelGroup.add');
        $id = ( new LabelGroupService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品标签分组编辑
     * @description 编辑商品标签分组
     * @param $id  商品标签分组id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "group_name", "" ],
            [ "sort", 0 ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\LabelGroup.edit');
        ( new LabelGroupService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商品标签分组删除
     * @description 删除商品标签分组
     * @param $id  商品标签分组id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new LabelGroupService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 修改排序
     * @description 修改商品标签分组排序
     * @return \think\Response
     */
    public function modifySort()
    {
        $data = $this->request->params([
            [ 'group_id', '' ],
            [ 'sort', '' ],
        ]);
        ( new LabelGroupService() )->modifySort($data);
        return success('SUCCESS');
    }

}
