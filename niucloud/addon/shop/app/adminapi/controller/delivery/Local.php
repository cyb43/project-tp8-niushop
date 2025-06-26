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

use addon\shop\app\service\admin\delivery\LocalService;
use core\base\BaseAdminController;


/**
 * 同城配送
 * @description 同城配送
 * Class Store
 * @package addon\shop\app\adminapi\controller\delivery
 */
class Local extends BaseAdminController
{
    /**
     * @description 查看设置
     * @return \think\Response
     */
    public function getLocal()
    {
        return success(data: (new LocalService())->getLocal());
    }

    /**
     * @description 设置配置
     * @return \think\Response
     */
    public function setLocal()
    {
        $data = $this->request->params([
            ['center', []],
            ['fee_type', ''],
            ['base_dist', 0],
            ['base_price', 0],
            ['grad_dist', 0],
            ['grad_price', 0],
            ['weight_start', 0],
            ['weight_unit', 0],
            ['weight_price', 0],
            ['delivery_type', ''],
            ['area', []],

            ['time_is_open', 0],
            ['time_type', 0],
            ['time_week', ''],
            ['time_interval', 30],
            ['advance_day', 0],
            ['most_day', 7],
            ['start_time', 0],
            ['end_time', 0],
            ['delivery_time', ''],

            ['third_party_config', []]
        ]);
        return success(data: (new LocalService())->setLocal($data));
    }

    /**
     * @description 查看三方配送初始化信息
     * @return \think\Response
     */
    public function getThirdPartyInit()
    {
        return success((new LocalService())->getThirdTypeConfig());
    }

    public function thirdDeliveryCallback($type, $order_no)
    {
        $params = $this->request->all();
        $params['type'] = $type;
        $params['order_no'] = $order_no;
        (new LocalService())->thirdDeliveryCallback($params);
    }
}
