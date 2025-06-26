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

namespace addon\shop\app\api\controller\order;

use addon\shop\app\service\api\order\OrderCreateService;
use core\base\BaseApiController;
use think\Response;

class OrderCreate extends BaseApiController
{

    /**
     * 查询订单数据
     * @return Response
     */
//    public function confirm(){
//        $data = $this->request->params([
//            ['cart_ids', []],
//            ['sku_data', []],
//            ['delivery_type', ''],//配送方式
//
//        ]);
//        return success('SUCCESS', (new OrderCreateService())->confirm($data));
//    }

    /**
     * 计算
     * @return Response
     */
    public function calculate()
    {
        $data = $this->request->params([
            [ 'order_key', [] ],
            [ 'cart_ids', [] ],
            [ 'sku_data', [] ],
            [ 'delivery', [] ],
            [ 'is_need_recalculate', 0 ],
//            ['member_remark', ''],//买家留言
            [ 'discount', [] ],//优惠
//            ['invoice', []],//发票
            [ 'extend_data', [] ],// 扩展数据
            [ 'impulse_buy_goods', [] ] // 顺买商品信息
        ]);
        return success('SUCCESS', ( new OrderCreateService() )->calculate($data));
    }

    /**
     * 订单创建
     * @return Response
     */
    public function create()
    {
        $data = $this->request->params([
            [ 'order_key', [] ],
            [ 'member_remark', '' ],//买家留言
            [ 'delivery', [] ],//配送参数
//            ['discount', []],//优惠
            [ 'invoice', [] ],//发票
            [ 'form_data', [] ] // 万能表单数据
        ]);
        return success('SUCCESS', ( new OrderCreateService() )->create($data));
    }

    /**
     * 查询优惠券
     * @return Response
     */
    public function getCoupon()
    {
        $data = $this->request->params([
            [ 'order_key', [] ],
        ]);
        return success('SUCCESS', ( new OrderCreateService() )->getCoupon($data));
    }

    /**
     * 获取自提点
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStore()
    {
        $data = $this->request->params([
            [ 'latlng', [] ],
        ]);
        return success('SUCCESS', ( new OrderCreateService() )->getStore($data[ 'latlng' ]));
    }

    /**
     * 获取同城配送配置项
     * @return Response
     */
    public function getLocalDeliveryConfig()
    {
        return success(((new OrderCreateService())->getLocalDeliveryConfig()));
    }
}
