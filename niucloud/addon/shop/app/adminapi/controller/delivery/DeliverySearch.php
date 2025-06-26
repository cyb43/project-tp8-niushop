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
use addon\shop\app\service\admin\delivery\DeliverySearchConfigService;


/**
 * 物流查询接口配置
 * Class DeliverySearch
 * @package addon\shop\app\adminapi\controller\delivery
 */
class DeliverySearch extends BaseAdminController
{
    /**
     * 设置配置
     * @description 设置物流查询配置
     * @return \think\Response
     */
    public function setConfig()
    {
        $data = $this->request->params([
            [ "interface_type", 1 ],
            [ "kdniao_id", "" ],
            [ "kdniao_app_key", "" ],
            [ "kdniao_is_pay", 1 ],
            [ "kd100_app_key", "" ],
            [ "kd100_customer", "" ],
        ]);
        ( new DeliverySearchConfigService() )->setConfig($data);
        return success('SUCCESS');
    }

    /**
     * 获取配置
     * @description 查看物流查询配置
     * @return \think\Response
     */
    public function getConfig()
    {
        return success(( new DeliverySearchConfigService() )->getConfig());
    }
}