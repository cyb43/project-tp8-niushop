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

namespace addon\shop\app\service\api\refund;

use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderRefundDict;
use addon\shop\app\dict\order\OrderRefundLogDict;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\model\order\OrderRefund;
use addon\shop\app\service\core\refund\CoreRefundActionService;
use core\base\BaseApiService;
use core\exception\ApiException;

/**
 *  订单服务层
 */
class RefundActionService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderRefund();
    }

    /**
     * 退款申请
     * @param $data
     * @return true
     */
    public function apply($data)
    {
        $order_goods_id = $data['order_goods_id'];
        //查询订单项信息
        $order_goods_model = new OrderGoods();
        $order_goods_info = $order_goods_model->where([
            ['order_goods_id', '=', $order_goods_id],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效
        if(!$order_goods_info['is_enable_refund']) throw new ApiException('SHOP_ORDER_IS_NOT_ENABLE_REFUND');//订单不允许退款
        //查询有没有正没有关闭的退款
        if ($order_goods_info['status'] != OrderGoodsDict::NORMAL) throw new ApiException('SHOP_ORDER_REFUND_IS_REFUND_FINISH');//订单已退款或存在未完成的退款
        $order_id = $order_goods_info['order_id'];//订单id
        $order = (new Order())->where([['order_id', '=', $order_id]])->findOrEmpty();
        if ($order->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效
        $order_refund_no = create_no();
        $refund_type = $data['refund_type'];
        //只有已发货,才能退货退款
        if($refund_type == OrderRefundDict::RETURN_AND_REFUND_GOODS && $order_goods_info['delivery_status'] == OrderDeliveryDict::WAIT_DELIVERY)  throw new ApiException('SHOP_ORDER_REFUND_DELIVERY_NOT_ALLOW_REFUND_GOODS');
        $apply_money = $data['apply_money'];

        //查询是否是最后一笔退款且还没有退运费
        $order_goods_count = $order_goods_model->where([['order_id', '=', $order_id], ['is_gift', '=', 0]])->count();
        $refund_count = $this->model->where([['order_id', '=', $order_id]])->whereNotIn(
            'status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND]
        )->count();
        //是否包含运费
        $is_refund_delivery = 0;
        if(($refund_count + 1) >= $order_goods_count){//最后一笔退款
            //判断是否已经退过运费
            $refund_delivery_count = $this->model->where([['order_id', '=', $order_id], ['is_refund_delivery', '=', 1]])->whereNotIn(
                'status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND]
            )->count();
            if($refund_delivery_count == 0){//已经退过运费的,就不需要重复再退了
                $is_refund_delivery = 1;
            }
        }
        //当前订单项最大可退金额
        $max_refund_money = $order_goods_info['goods_money'] - $order_goods_info['discount_money'];//可退金额
        if($is_refund_delivery == 1){
            $max_refund_money += $order['delivery_money'];
        }
        //退款金额不能大于可退款总额
        $comparison = bccomp(bcsub($apply_money, $max_refund_money), 0);//浮点数直接进行比较会出现精度问题
        if ($comparison > 0) throw new ApiException('SHOP_ORDER_REFUND_MONEY_GT_ORDER_MONEY');//退款金额不能大于可退款总额
        $reason = $data['reason'];
        $insert_data = [
            'order_id' => $order_goods_info['order_id'],
            'order_goods_id' => $order_goods_id,
            'order_refund_no' => $order_refund_no,
            'refund_type' => $refund_type,
            'reason' => $reason,
            'member_id' => $this->member_id,
            'apply_money' => $apply_money,
            'status' => OrderRefundDict::BUYER_APPLY_WAIT_STORE,
            'remark' => $data['remark'],
            'voucher' => $data['voucher'],
            'source' => OrderRefundDict::APPLY,
            'is_refund_delivery' => $is_refund_delivery ?? 0,
        ];
        $this->model->create($insert_data);

        //将订单项的退款单号覆盖
        $order_goods_info->save([
            'order_refund_no' => $order_refund_no,
            'status' => OrderGoodsDict::REFUNDING
        ]);
        //订单申请退款后事件
        event('AfterShopOrderRefundApply', ['order_refund_no' => $order_refund_no, 'refund_data' => $insert_data]);
        return true;
    }

    /**
     * 售后
     * @param $data
     * @return true
     */
    public function edit($data)
    {
        $order_refund_no = $data['order_refund_no'];
        //查询订单项信息
        $order_refund_info = $this->model->where([
            ['order_refund_no', '=', $order_refund_no],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//退款已失效
        if (!in_array($order_refund_info['status'], [OrderRefundDict::BUYER_APPLY_WAIT_STORE, OrderRefundDict::STORE_REFUSE_REFUND_GOODS_APPLY_WAIT_BUYER])) throw new ApiException('SHOP_ORDER_IS_INVALID');//退款已失效(只有被拒绝的请求才可以修改退款)
        $refund_type = $data['refund_type'];
        $apply_money = $data['apply_money'];

        $order_goods_id = $order_refund_info['order_goods_id'];
        //查询订单项信息
        $order_goods_info = (new OrderGoods())->where([
            ['order_goods_id', '=', $order_goods_id],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new ApiException('SHOP_ORDER_REFUND_IS_INVALID');//订单已失效

        $order_id = $order_goods_info['order_id'];//订单id
        $order = (new Order())->where([['order_id', '=', $order_id]])->findOrEmpty();
        if ($order->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效

        //是否包含运费
        $is_refund_delivery = $order_refund_info['is_refund_delivery'];
        //当前订单项最大可退金额
        $max_refund_money = $order_goods_info['goods_money'] - $order_goods_info['discount_money'];//可退金额
        if($is_refund_delivery == 1){
            $max_refund_money += $order['delivery_money'];
        }

        //退款金额不能大于可退款总额
        $comparison = bccomp(bcsub($apply_money, $max_refund_money), 0);//浮点数直接进行比较会出现精度问题
        if ($comparison > 0) throw new ApiException('SHOP_ORDER_REFUND_MONEY_GT_ORDER_MONEY');//退款金额不能大于可退款总额
        $reason = $data['reason'];
        $update_data = [
            'refund_type' => $refund_type,
            'reason' => $reason,
            'apply_money' => $apply_money,
            'status' => OrderRefundDict::BUYER_APPLY_WAIT_STORE,
            'remark' => $data['remark'],
            'voucher' => $data['voucher'],
            'source' => OrderRefundDict::APPLY,
            'shop_reason'=>'' // 再次申请退款，清空拒绝理由
        ];
        $order_refund_info->save($update_data);
        //订单申请退款后事件
        event('AfterShopOrderRefundEdit', [ 'order_refund_no' => $order_refund_no, 'refund_data' => array_merge($order_refund_info->toArray(), $update_data)]);
        return true;
    }

    /**
     * 退款关闭
     * @param $data
     * @return true
     */
    public function close($data)
    {
        $data[ 'main_type' ] = OrderRefundLogDict::MEMBER;
        $data[ 'main_id' ] = $this->member_id;
        (new CoreRefundActionService())->close($data);
        return true;
    }

    /**
     * 退款发货
     * @param $data
     * @return true
     */
    public function delivery($data)
    {
        $order_refund_no = $data['order_refund_no'];
        //查询订单项信息
        $order_refund_info = $this->model->where([
            ['order_refund_no', '=', $order_refund_no],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new ApiException('SHOP_ORDER_REFUND_IS_INVALID');//退款已失效
        if ($order_refund_info['status'] != OrderRefundDict::STORE_AGREE_REFUND_GOODS_APPLY_WAIT_BUYER) throw new ApiException('SHOP_ORDER_REFUND_STATUS_NOT_SUPPORT_ACTION');//退款已失效(只有被拒绝的请求才可以修改退货)

        $order_goods_id = $order_refund_info['order_goods_id'];
        //查询订单项信息
        $order_goods_info = (new OrderGoods())->where([
            ['order_goods_id', '=', $order_goods_id],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new ApiException('SHOP_ORDER_REFUND_IS_INVALID');//订单已失效
        //todo 配送信息增加
        $delivery_param = $data['delivery'];
        $delivery = array(
            'express_number' => $delivery_param['express_number'],
            'express_company' => $delivery_param['express_company'],
            'remark' => $delivery_param['remark'],
        );
        $update_data = [
            'delivery' => $delivery,
            'status' => OrderRefundDict::BUYER_REFUND_GOODS_WAIT_STORE,
        ];
        $order_refund_info->save($update_data);
        //订单申请退款后事件
        event('AfterShopOrderRefundDelivery', ['main_type' => OrderRefundLogDict::MEMBER, 'main_id' => $this->member_id, 'order_refund_no' => $order_refund_no, 'refund_data' => array_merge($order_refund_info->toArray(), $update_data)]);
        return true;
    }

    /**
     * 修改发货信息
     * @param $data
     * @return true
     */
    public function editDelivery($data)
    {
        $order_refund_no = $data['order_refund_no'];
        //查询订单项信息
        $order_refund_info = $this->model->where([
            ['order_refund_no', '=', $order_refund_no],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new ApiException('SHOP_ORDER_REFUND_IS_INVALID');//退款已失效
        if (!in_array($order_refund_info['status'], [OrderRefundDict::STORE_REFUSE_TAKE_REFUND_GOODS_WAIT_BUYER, OrderRefundDict::BUYER_REFUND_GOODS_WAIT_STORE])) throw new ApiException('SHOP_ORDER_REFUND_STATUS_NOT_SUPPORT_ACTION');//退款已失效(只有被拒绝的请求才可以修改退款)
        $order_goods_id = $order_refund_info['order_goods_id'];
        //查询订单项信息
        $order_goods_info = (new OrderGoods())->where([
            ['order_goods_id', '=', $order_goods_id],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效
        //todo 配送信息增加
        $delivery_param = $data['delivery'];
        $delivery = array(
            'express_number' => $delivery_param['express_number'],
            'express_company' => $delivery_param['express_company'],
            'remark' => $delivery_param['remark'],
        );
        $update_data = [
            'delivery' => $delivery,
            'status' => OrderRefundDict::BUYER_REFUND_GOODS_WAIT_STORE,
        ];
        $order_refund_info->save($update_data);
        //订单申请退款后事件
        event('AfterShopOrderRefundEditDelivery', ['main_type' => OrderRefundLogDict::MEMBER, 'main_id' => $this->member_id, 'order_refund_no' => $order_refund_no, 'refund_data' => array_merge($order_refund_info->toArray(), $update_data)]);
        return true;
    }

    /**
     *
     * @return array
     */
    public function getRefundReason()
    {
        return OrderRefundDict::getRefundReason();
    }

    /**
     * 获取订单项可退款属性
     * @param $data
     * @return void
     */
    public function getRefundData($data){
        $order_goods_id = $data['order_goods_id'];
        //查询订单项信息
        $order_goods_model = new OrderGoods();
        $order_goods_info = $order_goods_model->where([
            ['order_goods_id', '=', $order_goods_id],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效
//        if(!$order_goods_info['is_enable_refund']) throw new ApiException('SHOP_ORDER_IS_NOT_ENABLE_REFUND');//订单不允许退款
        //查询有没有正没有关闭的退款
//        if ($order_goods_info['status'] != OrderGoodsDict::NORMAL) throw new ApiException('SHOP_ORDER_REFUND_IS_REFUND_FINISH');//订单已退款或存在未完成的退款
        $order_id = $order_goods_info['order_id'];//订单id
        $order = (new Order())->where([['order_id', '=', $order_id]])->findOrEmpty();
        if ($order->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效

        //查询是否是最后一笔退款且还没有退运费
        $order_goods_count = $order_goods_model->where([['order_id', '=', $order_id], ['is_gift', '=', 0]])->count();
        $refund_count = $this->model->where([['order_id', '=', $order_id]])->whereNotIn(
            'status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND]
        )->count();
        $is_refund_delivery = 0;
        if(($refund_count + 1) >= $order_goods_count){//最后一笔退款
            //判断是否已经退过运费
            $refund_delivery_count = $this->model->where([['order_id', '=', $order_id], ['is_refund_delivery', '=', 1]])->whereNotIn(
                'status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND]
            )->count();
            if($refund_delivery_count == 0){//已经退过运费的,就不需要重复再退了
//                $refund_delivery_money = $order['delivery_money'];
                $is_refund_delivery = 1;
            }
        }
        $refund_data = [
            'refund_money' => $order_goods_info['order_goods_money'],
            'is_refund_delivery' => $is_refund_delivery,
            'refund_order_goods_money' => $order_goods_info['order_goods_money']
        ];
        if($is_refund_delivery){
            $refund_data['refund_money'] += $order['delivery_money'];
            $refund_data['refund_delivery_money'] = $order['delivery_money'];
        }
        return $refund_data;
    }

    /**
     * 通过退款单号获取退款信息
     * @param array $data
     * @return void
     */
    public function getRefundDataByOrderRefundNo($data)
    {
        $order_refund_no = $data['order_refund_no'];

        $order_refund_info = $this->model->where([
            ['order_refund_no', '=', $order_refund_no],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//退款已失效
        $order_goods_id = $order_refund_info['order_goods_id'];//订单id
        $order_id = $order_refund_info['order_id'];//订单id
        //查询订单项信息
        $order_goods_model = new OrderGoods();
        $order_goods_info = $order_goods_model->where([
            ['order_goods_id', '=', $order_goods_id],
            ['member_id', '=', $this->member_id]
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效

        $order = (new Order())->where([['order_id', '=', $order_id]])->findOrEmpty();
        if ($order->isEmpty()) throw new ApiException('SHOP_ORDER_IS_INVALID');//订单已失效

        //判断是否已经退过运费
        $is_refund_delivery = $order_refund_info['is_refund_delivery'];
        $refund_data = [
            'refund_money' => $order_goods_info['order_goods_money'],
            'is_refund_delivery' => $is_refund_delivery,
            'refund_order_goods_money' => $order_goods_info['order_goods_money']
        ];
        if($is_refund_delivery){
            $refund_data['refund_money'] += $order['delivery_money'];
            $refund_data['refund_delivery_money'] = $order['delivery_money'];
        }
        return $refund_data;
    }

}
