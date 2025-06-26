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

use addon\shop\app\service\admin\delivery\DeliverService;
use addon\shop\app\service\admin\delivery\DeliveryService;
use core\base\BaseAdminController;


/**
 * 物流配置
 * Class Config
 * @package addon\shop\app\adminapi\controller\config
 */
class Delivery extends BaseAdminController
{

    /**
     * 配送信息设置
     * @description 设置物流配置
     * @return \think\Response
     */
    public function setDeliveryConfig()
    {
        $data = $this->request->params([
            [ "value", "" ],
        ]);

        ( new DeliveryService() )->setConfig($data[ 'value' ]);
        return success('SUCCESS');
    }

    /**
     * 配送页信息
     * @description 获取配送页信息
     * @return void
     */
    public function getDelivery()
    {
        return success(( new DeliveryService() )->getDeliveryList());
    }

    /**
     * 配送页信息
     * @description 获取配送页信息配置
     * @return void
     */
    public function getDeliveryList()
    {
        return success(( new DeliveryService() )->getDeliveryConfigList());
    }

    /**
     * 获取配送员列表
     * @description 查看配送员列表
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "deliver_name", "" ],
            [ "deliver_mobile", "" ]
        ]);
        return success(( new DeliverService() )->getPage($data));
    }

    /**
     * 配送员详情
     * @description 查看配送员详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new DeliverService() )->getInfo($id));
    }

    /**
     * 添加配送员
     * @description 添加配送员
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "deliver_name", "" ],
            [ "deliver_mobile", "" ],
        ]);
        $id = ( new DeliverService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 配送员编辑
     * @description 编辑配送员
     * @param $id  自提门店id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "deliver_name", "" ],
            [ "deliver_mobile", "" ],
        ]);
        ( new DeliverService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 配送员删除
     * @description 删除配送员
     * @param $id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new DeliverService() )->del($id);
        return success('DELETE_SUCCESS');
    }
}
