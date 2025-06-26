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

use core\base\BaseAdminController;
use addon\shop\app\service\admin\goods\LabelService;


/**
 * 商品标签控制器
 * Class Label
 * @package addon\shop\app\adminapi\controller\goods
 */
class Label extends BaseAdminController
{
    /**
     * 获取商品标签分页列表
     * @description 获取商品标签列表-分页
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "label_name", "" ],
            [ 'group_id', 0 ],
            [ 'order', '' ],
            [ 'sort', '' ]
        ]);
        return success(( new LabelService() )->getPage($data));
    }

    /**
     * 获取商品标签列表
     * @description 获取商品标签列表-全部
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "label_name", "" ]
        ]);
        return success(( new LabelService() )->getList($data));
    }

    /**
     * 商品标签详情
     * @description 查看商品标签详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new LabelService() )->getInfo($id));
    }

    /**
     * 添加商品标签
     * @description 添加商品标签
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "label_name", "" ],
            [ 'group_id', 0 ],
            [ 'style_type', '' ],
            [ 'color_json', '' ],
            [ 'icon', '' ],
            [ 'status', '' ],
            [ "memo", "" ],
            [ "sort", 0 ],
        ]);

        $this->validate($data, 'addon\shop\app\validate\goods\Label.add');
        $id = ( new LabelService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品标签编辑
     * @description 编辑商品标签
     * @param $id  `商品标签id`
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "label_name", "" ],
            [ 'group_id', 0 ],
            [ 'style_type', '' ],
            [ 'color_json', '' ],
            [ 'icon', '' ],
            [ 'status', '' ],
            [ "memo", "" ],
            [ "sort", 0 ],

        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Label.edit');
        ( new LabelService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商品标签删除
     * @description 删除商品标签
     * @param $id  商品标签id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new LabelService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 修改排序
     * @description 修改商品标签排序
     * @return \think\Response
     */
    public function modifySort()
    {
        $data = $this->request->params([
            [ 'label_id', '' ],
            [ 'sort', '' ],
        ]);
        ( new LabelService() )->modifySort($data);
        return success('SUCCESS');
    }

    /**
     * 修改状态
     * @description 添加商品标签状态
     * @return \think\Response
     */
    public function modifyStatus()
    {
        $data = $this->request->params([
            [ 'label_id', '' ],
            [ 'status', '' ],
        ]);
        ( new LabelService() )->modifyStatus($data);
        return success('SUCCESS');
    }

    /**
     * 复制分类
     * @description 复制商品标签
     * @param int $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function copy(int $id)
    {
        (new LabelService())->copy($id);
        return success('SUCCESS');
    }

}
