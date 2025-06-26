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

namespace addon\shop\app\service\api\order;

use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\delivery\Local;
use addon\shop\app\model\order\Order;
use addon\shop\app\service\core\delivery\CoreStoreService;
use addon\shop\app\service\core\order\CoreOrderCreateService;
use core\base\BaseApiService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 *  订单服务层
 */
class OrderCreateService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 订单创建
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $data['member_id'] = $this->member_id;
        $data['order_from'] = $this->channel;
        $data['main_type'] = OrderLogDict::MEMBER;
        $data['main_id'] = $this->member_id;
        return (new CoreOrderCreateService())->create($data);
    }

    /**
     * 计算
     * @param array $data
     * @return void|null
     */
    public function calculate(array $data)
    {
        $data['member_id'] = $this->member_id;
        $data['order_from'] = $this->channel;
        return (new CoreOrderCreateService())->calculate($data);
    }

    /**
     * 确认订单数据
     * @param array $data
     * @return void
     */
//    public function confirm(array $data)
//    {
//        $data['member_id'] = $this->member_id;
//        return (new CoreOrderCreateService())->confirm($data);
//    }

    /**
     * 获取优惠券列表
     * @param array $data
     * @return void|null
     */
    public function getCoupon(array $data)
    {
        $data['member_id'] = $this->member_id;
        return (new CoreOrderCreateService())->getCoupon($data);
    }

    /**
     * @return void
     * @todo  获取配送信息(待定)
     */
    public function getDelivery(array $data)
    {
        $data['member_id'] = $this->member_id;
    }

    /**
     * 查询自提点
     * @param array $data
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getStore(array $data)
    {
        return (new CoreStoreService())->getStoreList($data);
    }

    public function getLocalDeliveryConfig()
    {
        return  (new Local())->where([['local_id', '>', 0]])->findOrEmpty()->toArray();
    }
}
