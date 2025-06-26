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
use addon\shop\app\service\admin\goods\ServiceService;


/**
 * 商品服务控制器
 * Class Serve
 * @package addon\shop\app\adminapi\controller\goods
 */
class Service extends BaseAdminController
{
    /**
     * 获取商品服务分页列表
     * @description 查看商品服务列表-分页
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "service_name", "" ]
        ]);
        return success(( new ServiceService() )->getPage($data));
    }

    /**
     * 获取商品服务列表
     * @description 查看商品服务列表-全部
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "service_name", "" ]
        ]);
        return success(( new ServiceService() )->getList($data));
    }

    /**
     * 商品服务详情
     * @description 查看商品服务详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new ServiceService() )->getInfo($id));
    }

    /**
     * 添加商品服务
     * @description 添加商品服务
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "service_name", "" ],
            [ "image", "" ],
            [ "desc", "" ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Service.add');
        $id = ( new ServiceService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品服务编辑
     * @description 编辑服务编辑
     * @param $id  商品服务id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "service_name", "" ],
            [ "image", "" ],
            [ "desc", "" ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Service.edit');
        ( new ServiceService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商品服务删除
     * @description 删除服务编辑
     * @param $id  商品服务id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new ServiceService() )->del($id);
        return success('DELETE_SUCCESS');
    }

}
