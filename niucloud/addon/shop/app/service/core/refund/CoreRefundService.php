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

namespace addon\shop\app\service\core\refund;

use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\dict\order\OrderRefundDict;
use addon\shop\app\model\newcomer\NewcomerRecords;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\model\order\OrderRefund;
use app\dict\pay\RefundDict;
use core\base\BaseCoreService;
use core\exception\CommonException;

/**
 * 订单退款服务层
 */
class CoreRefundService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderRefund();
    }

    /**
     *  查询退款信息
     * @param $order_refund_no
     * @return array
     */
    public function getInfo($order_refund_no)
    {
        //查询订单
        $where = array(
            ['order_refund_no', '=', $order_refund_no]
        );
        return $this->model->where($where)->with(['order_main'])->findOrEmpty()->toArray();
    }

    /**
     * 退款转账完成
     * @param $data
     * @return void
     */
    public function transferSuccess($data)
    {
        $order_refund_no = $data['order_refund_no'];
        $refund_no = $data['refund_no'];
        $update_data = array(
            'transfer_time' => time(),
            'refund_no' => $refund_no
        );
        $this->model->where([
            [
                'order_refund_no', '=', $order_refund_no
            ]
        ])->update($update_data);
        $this->finish([
            'order_refund_no' => $order_refund_no,
            'main_type' => OrderLogDict::SYSTEM,
        ]);
        return true;
    }

    /**
     * 完成
     * @param $data
     * @return void
     */
    public function finish($data)
    {
        $order_refund_no = $data['order_refund_no'];
        //查询订单项信息
        $order_refund_info = $this->model->where([
            ['order_refund_no', '=', $order_refund_no],
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new CommonException('SHOP_ORDER_REFUND_IS_NOT_FOUND');//退款已失效
        if ($order_refund_info['status'] == OrderRefundDict::FINISH) throw new CommonException('SHOP_ORDER_REFUND_IS_INVALID_OR_FINISH');//退款已完成或已失效
        $update_data = array(
            'status' => OrderRefundDict::FINISH,
        );
        $order_refund_info->save($update_data);

        //对应的要将订单项设置为已完成
        $order_goods_where = array(
            ['order_refund_no', '=', $order_refund_no]
        );
        $order_goods_update_data = array(
            'status' => OrderGoodsDict::REFUND_FINISH,
            'is_enable_refund' => 0,//禁用退款
        );
        (new OrderGoods())->where($order_goods_where)->update($order_goods_update_data);
        //订单完成退款后事件
        $data['order_refund_no'] = $order_refund_no;
        $data['refund_data'] = array_merge($order_refund_info->toArray(), $update_data);
        event('AfterShopOrderRefundFinish', $data);

        //新人专享活动退还参与资格
        $newcomerRecords = (new NewcomerRecords())->field('sku_ids')->where([
            ['member_id', '=', $order_refund_info->member_id],
            ['order_id', '=', $order_refund_info->order_id],
            ['is_join', '=', 1]
        ])->findOrEmpty()->toArray();
        if (!empty($newcomerRecords)) {
            //判断是否还有未退款的
            $not_refund_count = (new OrderGoods())->where([
                ['order_id', '=', $order_refund_info->order_id],
                ['sku_id', 'in', $newcomerRecords['sku_ids']],
                ['status', '<>', OrderGoodsDict::REFUND_FINISH]
            ])->count();
            if ($not_refund_count == 0) {
                event("NewcomerActiveJoin", ['member_id' => $order_refund_info->member_id, 'is_join' => 0, 'order_id' => $order_refund_info->order_id]);
            }
        }
        return true;
    }

    /**
     * 待转账
     * @param $order_refund_data
     * @param $main_type
     * @param $main_id
     * @return true
     */
    public function toTransfer($order_refund_data, $main_type, $main_id)
    {
        if ($order_refund_data['money'] > 0) {
            $core_refund_service = new \app\service\core\pay\CoreRefundService();
            $order = (new Order())->where([
                ['order_id', '=', $order_refund_data['order_id']]
            ])->findOrEmpty();
            if ($order->isEmpty()) throw new CommonException('SHOP_ORDER_NOT_FOUND');
            $refund_no = $core_refund_service->create( $order['out_trade_no'], $order_refund_data['money'], get_lang('SHOP_ORDER_BUYER_APPLY_REFUND'), OrderDict::TYPE, $order_refund_data['order_refund_no']);
            $this->model->where(
                [
                    ['order_refund_no', '=', $order_refund_data['order_refund_no']]
                ]
            )->update(
                [
                    'refund_no' => $refund_no,
                    'status' => OrderRefundDict::STORE_REFUND_TRANSFERING
                ]
            );
            $core_refund_service->refund( $refund_no, '', RefundDict::BACK, $main_type, $main_id);
        }
        //状态改变
        return true;
    }

    /**
     * 退款校验
     * @param $order_goods_ids
     * @param $apply_refund_money
     * @return array
     */
    public function refundCheck($order_goods_ids, $apply_refund_money)
    {
        if ($apply_refund_money < 0) throw new CommonException('SHOP_ORDER_REFUND_MONEY_LESS_THAN_ZERO');//退款金额不能小于0
        //查询最大可退款总额
        $refund_money_array = $this->getOrderRefundMoney($order_goods_ids);
        $refund_money = $refund_money_array['refund_money'];
        $comparison = bccomp(bcsub($apply_refund_money, $refund_money), 0);//浮点数直接进行比较会出现精度问题
        if ($comparison > 0) throw new CommonException('SHOP_ORDER_REFUND_MONEY_GT_ORDER_MONEY');//退款金额不能大于可退款总额
        return $refund_money_array;
    }


    /**
     * 获取订单最大可退金额
     * @param $order_goods_ids
     * @return array
     */
    public function getOrderRefundMoney($order_goods_ids)
    {
        //查询订单项信息
        $refund_money = 0;
        $order_goods_model = new OrderGoods();
        $order_goods_list = $order_goods_model->where([['order_goods_id', 'in', $order_goods_ids]])->select();
        if ($order_goods_list->isEmpty()) throw new CommonException('SHOP_ORDER_IS_INVALID');//订单已失效
        $order_id = $order_goods_list[0]['order_id'];//订单id
        $order = (new Order())->where([['order_id', '=', $order_id]])->findOrEmpty();
        if ($order->isEmpty()) throw new CommonException('SHOP_ORDER_IS_INVALID');//订单已失效
        if (!in_array($order['status'], [OrderDict::WAIT_DELIVERY, OrderDict::WAIT_TAKE, OrderDict::FINISH])) throw new CommonException('SHOP_ORDER_REFUND_WAIT_PAY_OR_CLOSE');
        //查询是否是最后一笔退款且还没有退运费
        $order_goods_count = $order_goods_model->where([['order_id', '=', $order_id], ['is_gift', '=', 0]])->count();
        $refund_count = $this->model->where([['order_id', '=', $order_id]])->whereNotIn(
            'status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND]
        )->count();
        //是否包含运费
        $is_refund_delivery = 0;
        if (($refund_count + count($order_goods_ids)) >= $order_goods_count) {//最后一笔退款
            //判断是否已经退过运费
            $refund_delivery_count = $this->model->where([['order_id', '=', $order_id], ['is_refund_delivery', '=', 1]])->whereNotIn(
                'status', [OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND]
            )->count();
            if ($refund_delivery_count == 0) {//已经退过运费的,就不需要重复再退了
                $is_refund_delivery = 1;
            }
        }
        foreach ($order_goods_list as $key => $value) {
            if (!$value['is_enable_refund']) throw new CommonException('SHOP_ORDER_IS_NOT_ENABLE_REFUND');//订单不允许退款
            //查询有没有正没有关闭的退款
            if ($value['status'] != OrderGoodsDict::NORMAL) throw new CommonException('SHOP_ORDER_REFUND_IS_REFUND_FINISH');//订单已退款或存在未完成的退款
            //当前订单项最大可退金额
            $item_refund_money = $value['goods_money'] - $value['discount_money'] - $value['shop_active_refund_money'];//可退金额
            if ($is_refund_delivery == 1 && $key == 0) {
                $item_refund_money += $order['delivery_money'];
                $order_goods_list[$key]['is_refund_delivery'] = $is_refund_delivery;
            } else {
                $order_goods_list[$key]['is_refund_delivery'] = 0;
            }
            $order_goods_list[$key]['item_refund_money'] = $item_refund_money;
            $refund_money += $item_refund_money;
        }
        return [
            'refund_money' => $refund_money,
            'order_goods_list' => $order_goods_list
        ];
    }
}
