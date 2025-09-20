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

namespace addon\shop\app\service\core\pointexchange;

use addon\shop\app\model\exchange\Exchange;
use core\base\BaseCoreService;


/**
 * 商品销量服务层
 */
class CoreGoodsSaleNumService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Exchange();
    }

    /**
     * 增加
     * @param $data
     * @return void
     */
    public function inc($data)
    {
        $this->model->where([['id', '=', $data['id']]])->inc('total_exchange_num', $data['num'])->inc('total_member_num', 1)->inc('total_point_num', $data['point'])->inc('total_price_num', $data['goods_money'])->inc('total_order_num', 1)->update();
        return true;
    }
    /**
     * 增加
     * @param $data
     * @return void
     */
    public function batchUpdate($data)
    {
        $this->model->saveAll($data);
        return true;
    }

    /**
     * 减少
     * @param $data
     * @return void
     */
    public function dec($data)
    {
        $this->model->where([['id', '=', $data['id']]])->dec('total_exchange_num', $data['num'])->dec('total_member_num', 1)->dec('total_point_num', $data['point'])->dec('total_price_num', $data['goods_money'])->dec('total_order_num', 1)->update();
        return true;
    }

}
