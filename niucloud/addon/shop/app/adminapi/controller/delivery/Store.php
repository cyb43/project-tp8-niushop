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
use addon\shop\app\service\admin\delivery\StoreService;


/**
 * 自提门店控制器
 * Class Store
 * @package addon\shop\app\adminapi\controller\delivery
 */
class Store extends BaseAdminController
{
    /**
     * 获取展示用信息
     * @description 获取添加/编辑展示用信息
     * @param int $id
     * @return \think\Response
     */
    public function init()
    {
        return success(( new StoreService() )->getInitInfo());
    }

    /**
     * 获取自提门店列表
     * @description 查看自提门店列表详情
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "store_name", "" ],
            [ "create_time", [ "", "" ] ]
        ]);
        return success(( new StoreService() )->getPage($data));
    }

    /**
     * 自提门店详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new StoreService() )->getInfo($id));
    }


    /**
     * 添加自提门店
     * @description 添加自提门店
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "store_name", "" ],
            [ "store_desc", "" ],
            [ "store_logo", "" ],
            [ "store_mobile", 0 ],
            [ "province_name", "" ],
            [ "city_name", "" ],
            [ "district_name", "" ],
            [ "address", "" ],
            [ "full_address", "" ],
            [ "longitude", "" ],
            [ "latitude", "" ],
            [ "trade_time", "" ],
            [ "time_type", "" ],//类型
            [ "time_week", [] ],//周数组
            [ "time_interval", "" ],//间隔时间段 单位分钟
            [ "trade_time_json", [] ],//营业时间
        ]);
        $this->validate($data, 'addon\shop\app\validate\delivery\Store.add');
        $id = ( new StoreService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 自提门店编辑
     * @description 编辑自提门店
     * @param $id  自提门店id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "store_name", "" ],
            [ "store_desc", "" ],
            [ "store_logo", "" ],
            [ "store_mobile", 0 ],
            [ "address", "" ],
            [ "full_address", "" ],
            [ "longitude", "" ],
            [ "latitude", "" ],
            [ "trade_time", "" ],
            [ "time_week", [] ],//周数组
            [ "time_interval", "" ],//间隔时间段 单位分钟
            [ "trade_time_json", "" ],//营业时间
        ]);
        $address = $this->request->params([
            [ "province_name", "" ],
            [ "city_name", "" ],
            [ "district_name", "" ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\delivery\Store.edit');
        ( new StoreService() )->edit($id, $data, $address);
        return success('EDIT_SUCCESS');
    }

    /**
     * 自提门店删除
     * @description 删除自提门店
     * @param $id  自提门店id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new StoreService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * @description 查看自提门店列表-全部
     * @return \think\Response
     */
    public function getList()
    {
        $data = $this->request->params([
            [ "store_name", "" ],
            [ "create_time", [ "", "" ] ]
        ]);
        return success(( new StoreService() )->getList($data));
    }

}
