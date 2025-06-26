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

namespace addon\shop\app\adminapi\controller\shop_address;

use core\base\BaseAdminController;
use addon\shop\app\service\admin\shop_address\ShopAddressService;


/**
 * 商家地址库控制器
 * Class ShopAddress
 * @package addon\shop\app\adminapi\controller\shop_address
 */
class ShopAddress extends BaseAdminController
{
    /**
     * 获取商家地址库列表
     * @description 查看商家地址库列表-分页
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "mobile", "" ],
            [ "full_address", "" ]
        ]);
        return success(( new ShopAddressService() )->getPage($data));
    }

    /**
     * 商家地址库详情
     * @description 查看商家地址库详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new ShopAddressService() )->getInfo($id));
    }

    /**
     * 添加商家地址库
     * @description 添加商家地址库
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "contact_name", "" ],
            [ "mobile", "" ],
            [ "province_id", 0 ],
            [ "city_id", 0 ],
            [ "district_id", 0 ],
            [ "address", "" ],
            [ "full_address", "" ],
            [ "lat", "" ],
            [ "lng", "" ],
            [ "is_delivery_address", 0 ],
            [ "is_refund_address", 0 ],
            [ "is_default_delivery", 0 ],
            [ "is_default_refund", 0 ]
        ]);
        $this->validate($data, 'addon\shop\app\validate\shop_address\ShopAddress.add');
        $id = ( new ShopAddressService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商家地址库编辑
     * @description 编辑商家地址库
     * @param $id  商家地址库id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "contact_name", "" ],
            [ "mobile", "" ],
            [ "province_id", 0 ],
            [ "city_id", 0 ],
            [ "district_id", 0 ],
            [ "address", "" ],
            [ "full_address", "" ],
            [ "lat", "" ],
            [ "lng", "" ],
            [ "is_delivery_address", 0 ],
            [ "is_refund_address", 0 ],
            [ "is_default_delivery", 0 ],
            [ "is_default_refund", 0 ]
        ]);
        $this->validate($data, 'addon\shop\app\validate\shop_address\ShopAddress.edit');
        ( new ShopAddressService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商家地址库删除
     * @description 删除商家地址库
     * @param $id  商家地址库id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new ShopAddressService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * @description 获取默认配送地址
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function defaultDelivery()
    {
        return success(data:( new ShopAddressService() )->getDefaultDeliveryAddress());
    }

    /**
     * 获取商家地址（不分页）
     * @description 获取商家地址库列表-不分页
     */
    public function getList()
    {
        return success(( new ShopAddressService() )->getList());
    }
}
