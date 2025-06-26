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

namespace app\adminapi\controller\sys;

use app\service\admin\sys\AreaService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 地区管理
 * Class Area
 * @description 地区管理
 * @package app\adminapi\controller\sys
 */
class Area extends BaseAdminController
{
    /**
     * 根据pid获取子项列表
     * @description 根据pid获取子项列表
     * @param int $pid
     * @return Response
     */
    public function listByPid(int $pid)
    {
        return success((new AreaService())->getListByPid($pid));
    }

    /**
     * 获取层级列表
     * @description 获取层级列表
     * @param int $level
     * @return Response
     */
    public function tree(int $level)
    {
        return success((new AreaService())->getAreaTree($level));
    }

    /**
     * 获取地址信息
     * @description 获取地址信息
     * @return Response
     */
    public function addressInfo()
    {
        $data = $this->request->params([
            ['address', ''],
        ]);
        return success((new AreaService())->getAddress($data['address']));
    }

    /**
     * 获取地址信息
     * @description 获取地址信息
     * @return Response
     */
    public function contraryAddress()
    {
        $data = $this->request->params([
            ['location', ''],
        ]);
        return success((new AreaService())->getAddressInfo($data['location']));
    }

    /**
     * 根据code获取地址信息
     * @description 根据code获取地址信息
     * @return void
     */
    public function areaByAreaCode(string $code) {
        return success((new AreaService())->getAreaByAreaCode($code));
    }
}
