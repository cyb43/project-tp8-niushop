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

namespace app\adminapi\controller\member;

use app\service\admin\member\AddressService;
use core\base\BaseAdminController;


/**
 * 会员收货地址控制器
 * @description 会员收货地址
 */
class Address extends BaseAdminController
{
   /**
    * 获取会员收货地址列表
    * @description 获取会员收货地址列表
    * @return \think\Response
    */
    public function lists(){
        $data = $this->request->params([
            ["member_id",""],
        ]);
        return success((new AddressService())->getList($data));
    }

    /**
     * 会员收货地址详情
     * @description 会员收货地址详情
     * @param $id  会员收货地址id
     * @return \think\Response
     */
    public function info($id){
        $data = $this->request->params([
            ["member_id",""],
        ]);
        return success((new AddressService())->getInfo($id, $data));
    }

    /**
     * 添加会员收货地址
     * @description 添加会员收货地址
     * @return \think\Response
     */
    public function add(){
        $data = $this->request->params([
             ["member_id",""],
             ["name",""],
             ["mobile",""],
             ["province_id",0],
             ["city_id",0],
             ["district_id",0],
             ["address",""],
             ["address_name", ""],
             ["full_address",""],
             ["lng",""],
             ["lat",""],
             ["is_default",0],
        ]);
        $id = (new AddressService())->add($data);
        return success('ADD_SUCCESS', ['id' => $id]);
    }

    /**
     * 会员收货地址编辑
     * @description 会员收货地址编辑
     * @param $id  会员收货地址id
     * @return \think\Response
     */
    public function edit($id){
        $data = $this->request->params([
             ["member_id",""],
             ["name",""],
             ["mobile",""],
             ["province_id",0],
             ["city_id",0],
             ["district_id",0],
             ["address",""],
             ["address_name", ""],
             ["full_address",""],
             ["lng",""],
             ["lat",""],
             ["is_default",0],
        ]);
        (new AddressService())->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

}
