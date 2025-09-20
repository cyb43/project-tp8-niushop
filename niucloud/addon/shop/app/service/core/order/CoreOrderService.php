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

namespace addon\shop\app\service\core\order;

use addon\shop\app\model\order\Order;
use addon\shop\app\service\core\delivery\CoreConfigService;
use addon\shop\app\service\core\delivery\delivery_search\DeliverySearchLoader;
use core\base\BaseCoreService;
use think\facade\Cache;

/**
 * 订单服务层
 */
class CoreOrderService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 查询订单
     * @param int $order_id
     * @return array
     */
    public function getInfo(int $order_id)
    {
        //查询订单
        $where = array(
            [ 'order_id', '=', $order_id ]
        );
        return $this->model->where($where)->findOrEmpty()->toArray();
    }

    /**
     * 查询物流信息
     * @param $params
     * @return mixed
     */
    public function deliverySearch($params)
    {
        $class = new DeliverySearchLoader();
        $data = [
            'company_id' => !empty($params[ 'company' ]) ? $params[ 'company' ][ 'company_id' ] : '',
            'express_no' => !empty($params[ 'company' ]) ? $params[ 'company' ][ 'express_no' ] : '',
            'logistic_no' => $params[ 'express_number' ],
            'mobile' => $params[ 'mobile' ],
        ];

        //获取缓存
        $traces = Cache::get('order_delivery:'.$data['express_no']);
        if(empty($traces)){
            $traces = $class->search($data);
            if (!empty($traces[ 'list' ])) {
                //结果缓存2小时
                $traces[ 'list' ] = array_reverse($traces[ 'list' ]);
                Cache::tag('order_delivery')->set('order_delivery:'.$data['express_no'], $traces, 7200);
            }
        }
        $params[ 'traces' ] = $traces;
        return $params;
    }
}
