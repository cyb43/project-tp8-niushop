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

namespace addon\shop\app\adminapi\controller\marketing;


use addon\shop\app\dict\active\ExchangeDict;
use addon\shop\app\service\admin\marketing\pointexchange\ExchangeService;
use core\base\BaseAdminController;


/**
 * 积分商城控制器
 * Class Exchange
 * @package addon\shop\app\adminapi\controller\marketing
 */
class Exchange extends BaseAdminController
{
    /**
     * @description 查看积分商城列表-分页
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "names", "" ],
            [ "status", "" ],
            [ "create_time", "" ],
        ]);
        return success(( new ExchangeService() )->getPage($data));
    }

    /**
     * 积分商品分页列表（用于弹框选择）
     * @description 积分商城列表-全部
     * @return \think\Response
     */
    public function select()
    {
        $data = $this->request->params([
            [ "names", "" ],
            [ "create_time", "" ],
            [ 'verify_goods_ids', [] ], // 检测id集合是否存在，移除不存在的id，纠正数据准确性
        ]);
        return success(( new ExchangeService() )->getSelectPage($data));
    }

    /**
     * 获取活动商品类型
     * @description 积分商城商品类型
     * @return \think\Response
     */
    public function type()
    {
        return success(ExchangeDict::getType());
    }

    /**
     * 获取积分商城状态
     * @description 积分商城状态字典
     */
    public function status()
    {
        return success(ExchangeDict::getStatus());
    }

    /**
     * 添加积分商城
     * @description 添加积分商城活动
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "type", '' ],
            [ "names", "" ],
            [ "title", '' ],
            [ "image", '' ],
            [ "product_detail", '{}' ],
            [ "point", '' ],
            [ "price", '' ],
            [ "limit_num", '' ],
            [ "content", '' ],
            [ "sort", '' ],
            [ "stock", '' ],
        ]);
        $id = ( new ExchangeService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 详情
     * @description 查看积分商城详情
     * @param int $id
     * @return \think\Response
     */
    public function detail(int $id)
    {
        return success(( new ExchangeService() )->getDetail($id));
    }

    /**
     * 积分商城编辑
     * @description 编辑积分商城活动
     * @param int $id
     * @return \think\Response
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            [ "type", '' ],
            [ "names", "" ],
            [ "title", '' ],
            [ "image", '' ],
            [ "product_detail", '{}' ],
            [ "point", '' ],
            [ "price", '' ],
            [ "limit_num", '' ],
            [ "content", '' ],
            [ "sort", '' ],
            [ "stock", '' ],
        ]);
        ( new ExchangeService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 修改上架状态
     * @description 修改积分商城上下架
     * @param int $id
     * @return \think\Response
     */
    public function editStatus(int $id)
    {
        $data = $this->request->params([
            [ 'status', '' ],
        ]);
        ( new ExchangeService() )->editStatus($data, $id);
        return success('SUCCESS');
    }

    /**
     * 删除活动
     * @description 删除积分商城活动
     * @param int $id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new ExchangeService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 批量删除活动
     * @description 批量删除积分商城活动
     * @return \think\Response
     */
    public function batchDelete()
    {
        $data = $this->request->params([
            [ 'ids', [] ],
        ]);
        ( new ExchangeService() )->batchDelete($data['ids']);
        return success('SUCCESS');
    }

    /**
     * 批量下架活动
     * @description 批量下架积分商城活动
     * @return \think\Response
     */
    public function batchDown()
    {
        $data = $this->request->params([
            [ 'ids', [] ],
        ]);
        ( new ExchangeService() )->batchDown($data['ids']);
        return success('SUCCESS');
    }

    /**
     * 批量上架架活动
     * @description 批量下架积分商城活动
     * @return \think\Response
     */
    public function batchUp()
    {
        $data = $this->request->params([
            [ 'ids', [] ],
        ]);
        ( new ExchangeService() )->batchUp($data['ids']);
        return success('SUCCESS');
    }

    /**
     * 修改排序
     * @description 修改积分商城活动排序
     * @param int $id
     * @return \think\Response
     */
    public function modifySort(int $id)
    {
        $data = $this->request->params([
            [ 'sort', '' ],
        ]);
        ( new ExchangeService() )->modifySort($data, $id);
        return success('SUCCESS');
    }

}
