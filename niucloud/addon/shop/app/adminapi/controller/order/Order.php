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

namespace addon\shop\app\adminapi\controller\order;

use addon\shop\app\dict\order\OrderBatchDeliveryDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\service\admin\order\OrderBatchDeliveryService;
use addon\shop\app\service\admin\order\OrderFinishService;
use addon\shop\app\service\admin\order\OrderService;
use addon\shop\app\service\admin\order\OrderCloseService;
use addon\shop\app\service\admin\order\OrderDeliveryService;
use app\dict\common\ChannelDict;
use app\dict\pay\PayDict;
use app\service\core\notice\NoticeService;
use core\base\BaseAdminController;
use think\Response;

class Order extends BaseAdminController
{
    /**
     * 订单列表
     * @description 查看订单列表-分页
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['search_type', ''],
            ['search_name', ''],
            ['status', ''],
            ['pay_type', ''],
            ['order_from', ''],
            ['create_time', []],
            ['pay_time', []],
            ['activity_type', ''],
            ['keyword', ''],
        ]);
        return success((new OrderService())->getPage($data));
    }

    /**
     * 订单详情
     * @description 查看订单详情
     * @param int $order_id
     * @return Response
     */
    public function detail(int $id)
    {
        return success((new OrderService())->getDetail($id));
    }

    /**
     * 获取订单状态
     * @description 获取订单状态字典
     * @return Response
     */
    public function getOrderStatus()
    {
        return success(OrderDict::getStatus());
    }

    /**
     * 获取订单类型
     * @description 获取订单类型字典
     * @return Response
     */
    public function getOrderType()
    {
        return success(OrderDict::getOrderType());
    }

    /**
     * 订单关闭
     * @description 关闭订单
     * @param $id
     * @return Response
     */
    public function orderClose($id)
    {
        (new OrderCloseService())->close($id);
        return success('SUCCESS');
    }

    /**
     * 订单完成
     * @description 完成订单
     * @param $id
     * @return Response
     */
    public function orderFinish($id)
    {
        (new OrderFinishService())->finish($id);
        return success();
    }

    /**
     * 订单发货
     * @description 订单发货
     * @param $id
     * @return Response
     */
    public function orderDelivery()
    {
        $data = $this->request->params([
            ['order_id', 0],
            ['delivery_ids', []],//修改
            ['order_goods_ids', []],
            ['delivery_type', ''],
            ['delivery_way', ''], // 发货方式，manual_write：手动填写，electronic_sheet：电子面单
            ['express_company_id', ''],
            ['express_number', ''],
            ['electronic_sheet_id', 0], // 电子面单
            ['local_deliver_id', 0],//配送员
            ['remark', ''],//配送员
        ]);
        return success("DELIVERY_SUCCESS", (new OrderDeliveryService())->delivery($data));
    }

    /**
     *
     * 获取订单配送方式
     * @description 获取订单配送方式
     */
    public function getDeliveryType()
    {
        $data = $this->request->params([
            ['delivery_type', ''],
        ]);
        return success(OrderDeliveryDict::getChildType($data['delivery_type']));
    }

    /**
     * 商家留言
     * @description 设置商家留言
     * @return Response
     */
    public function setShopRemark()
    {
        $data = $this->request->params([
            ['order_id', ''],
            ['shop_remark', ''],
        ]);
        (new OrderService())->shopRemark($data);
        return success("SUCCESS");
    }

    /**
     * 订单包裹
     * @description 查看订单包裹
     * @return Response
     */
    public function getOrderPackage()
    {
        $data = $this->request->params([
            ['id', ''],
            ['mobile', ''],
        ]);
        return success(data: (new OrderDeliveryService())->getDeliveryPackage($data));
    }

    /**
     * 订单包裹列表
     * @description 查看订单包裹列表-分页
     * @return Response
     */
    public function getDeliveryPackageList()
    {
        $data = $this->request->params([
            ['order_id', ''],
        ]);
        return success((new OrderDeliveryService())->getDeliveryPackageList($data));
    }

    /**
     * 获取支付方式
     * @description 获取订单支付方式字典
     * @return Response
     */
    public function getPayType()
    {
        return success(PayDict::getPayType());
    }

    /**
     * 获取订单来源
     * @description 获取订单来源字典
     */
    public function getOrderFrom()
    {
        return success((new OrderService())->getOrderFrom());
    }

    /**
     * 订单改价
     * @description 修改订单价格
     * @return void
     */
    public function editPrice()
    {
        $data = $this->request->params([
            ['order_id', 0],
            ['delivery_money', 0],
            ['order_goods_data', []],
        ]);
        return success(data: (new OrderService())->editPrice($data));
    }

    /**
     * 订单配送信息修改
     * @description 修改订单配送信息
     */
    public function editDelivery()
    {
        $data = $this->request->params([
            ['order_id', 0],
            ['delivery_type', ''],
            ['take_store_id', 0],
            ['taker_name', ''],
            ['taker_mobile', ''],
            ['taker_province', 0],
            ['taker_city', 0],
            ['taker_district', 0],
            ['taker_address', ''],
            ['taker_full_address', ''],
            ['taker_longitude', ''],
            ['taker_latitude', ''],
            ['taker_store_id', 0],
        ]);
        return success("SUCCESS", (new OrderService())->editDelivery($data));
    }

    /**
     * 订单修改配送信息数据获取
     * @description 修改订单配送信息
     */
    public function editDeliveryData()
    {
        $data = $this->request->params([
            ['order_id', 0],
            ['delivery_type', ''],
            ['take_store_id', 0],
            ['taker_name', ''],
            ['taker_mobile', ''],
            ['taker_province', 0],
            ['taker_city', 0],
            ['taker_district', 0],
            ['taker_address', ''],
            ['taker_full_address', ''],
            ['taker_longitude', ''],
            ['taker_latitude', ''],
            ['taker_store_id', 0],
        ]);
        return success(data: (new OrderService())->getEditDeliveryData($data));
    }

    /**
     *获取订单批量操作记录
     * @description 查看订单批量操作
     * @return Response
     */
    public function getOrderBatchDeliveryPage()
    {
        $data = $this->request->params([
            ['status', ''],
            ['type', ''],
            ['main_id', ''],
            ['create_time', []],
        ]);
        return success((new OrderBatchDeliveryService())->getPage($data));
    }

    /**
     * 记录详情
     * @description 查看批量操作订单配送记录信息
     * @param $id
     * @return Response
     * @throws \think\db\exception\DbException
     */
    public function getOrderBatchDeliveryInfo($id)
    {
        return success((new OrderBatchDeliveryService())->getInfo($id));
    }

    /**
     * 发布导入批量操作
     * @description 批量订单发货
     * @return Response
     */
    public function addBatchOrderDelivery()
    {
        $data = $this->request->params([
            ['data', []],//['path' => '', 'type' => 'order/order_goods']
        ]);
        return success(data: (new OrderBatchDeliveryService())->addBatchOrderDelivery($data));
    }

    /**
     * 获取操作类型
     * @description 获取批量发送类型字典
     * @return Response
     */
    public function getBatchType()
    {
        return success(data: OrderBatchDeliveryDict::getType());
    }

    /**
     * 获取批量状态
     * @description 获取订单批量发送状态字典
     * @return Response
     */
    public function getBatchStatus()
    {
        return success(data: OrderBatchDeliveryDict::getStatus());
    }

    /**
     * @description 删除订单
     * @return Response
     */
    public function delete()
    {
        $params = $this->request->params([
            ['order_ids', []]
        ]);
        $order_ids = $params['order_ids'];
        $res = (new OrderService)->delete($order_ids);
        return success("DELETE_SUCCESS");

    }

}
