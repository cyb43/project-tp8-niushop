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

use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderRefundDict;
use addon\shop\app\dict\order\OrderRefundLogDict;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\model\order\OrderRefund;
use addon\shop\app\model\shop_address\ShopAddress;
use core\base\BaseCoreService;
use core\exception\CommonException;

/**
 * 订单退款服务层
 */
class CoreRefundActionService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderRefund();
    }

    /**
     * 审核退款申请
     * @param $data ['is-agree]
     * @return void
     */
    public function auditApply($data)
    {
        $is_agree = $data[ 'is_agree' ];
        $order_refund_no = $data[ 'order_refund_no' ];
        //查询订单项信息
        $order_refund_info = $this->model->where([
            [ 'order_refund_no', '=', $order_refund_no ]
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new CommonException('SHOP_ORDER_REFUND_IS_INVALID');//退款已失效
        if ($order_refund_info[ 'status' ] != OrderRefundDict::APPLY) throw new CommonException('SHOP_ORDER_REFUND_IS_ONLY_WAIT_REFUND');//退款已失效(只有申请中的退款才可以审核)

        $order_goods_id = $order_refund_info[ 'order_goods_id' ];
        //查询订单项信息
        $order_goods_info = ( new OrderGoods() )->where([
            [ 'order_goods_id', '=', $order_goods_id ],
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new CommonException('SHOP_ORDER_IS_INVALID');//订单已失效

        $order_id = $order_goods_info[ 'order_id' ];//订单id
        $order = ( new Order() )->where([ [ 'order_id', '=', $order_id ] ])->findOrEmpty();
        if ($order->isEmpty()) throw new CommonException('SHOP_ORDER_IS_INVALID');//订单已失效

        //根据退款方式来判断下一步的状态
        $update_data = [];

        $is_refund_delivery = $order_refund_info[ 'is_refund_delivery' ];
        //当前订单项最大可退金额
        $max_refund_money = $order_goods_info[ 'goods_money' ] - $order_goods_info[ 'discount_money' ];//可退金额
        if ($is_refund_delivery == 1) {
            $max_refund_money += $order[ 'delivery_money' ];
        }

        if ($is_agree) {
            $money = $data[ 'money' ];
            //退款金额不能大于可退款总额
            $comparison = bccomp(bcsub($money, $max_refund_money), 0);//浮点数直接进行比较会出现精度问题
            if ($comparison > 0) throw new CommonException('SHOP_ORDER_REFUND_MONEY_GT_ORDER_MONEY');//退款金额不能大于可退款总额
            $update_data[ 'money' ] = $money;
            //只退款
            if ($order_refund_info[ 'refund_type' ] == OrderRefundDict::ONLY_REFUND) {

                $update_data[ 'status' ] = OrderRefundDict::STORE_AGREE_REFUND_WAIT_TRANSFER;
            } else {
                $refund_address_id = $data[ 'refund_address_id' ] ?? 0;
                $shop_address_field = 'contact_name,mobile,province_id,city_id,district_id,address,full_address,lat,lng';
                $shop_address = ( new ShopAddress() )->where([ [ 'id', '=', $refund_address_id ] ])->field($shop_address_field)->findOrEmpty();
                if ($shop_address->isEmpty()) throw new CommonException('SHOP_ORDER_REFUND_SELECT_ADDRESS');//订单已失效
                $update_data[ 'refund_address' ] = $shop_address->toArray();
//                $update_data['timeout'] = 0;
                $update_data[ 'status' ] = OrderRefundDict::STORE_AGREE_REFUND_GOODS_APPLY_WAIT_BUYER;
            }
        } else {
            $update_data[ 'status' ] = OrderRefundDict::STORE_REFUSE_REFUND_GOODS_APPLY_WAIT_BUYER;
            $update_data[ 'shop_reason' ] = $data[ 'shop_reason' ];
        }
        $order_refund_info->save($update_data);
        //审核完毕后续事件
        $data[ 'order_refund_no' ] = $order_refund_no;
        $data[ 'refund_data' ] = array_merge($order_refund_info->toArray(), $update_data);

        event('AfterShopOrderRefundAuditApply', $data);
        return true;
    }

    /**
     * 审核是否确认收货
     * @param $data
     * @return true
     */
    public function auditRefundGoods($data)
    {
        $is_agree = $data[ 'is_agree' ];
        $order_refund_no = $data[ 'order_refund_no' ];
        //查询订单项信息
        $order_refund_info = $this->model->where([
            [ 'order_refund_no', '=', $order_refund_no ]
        ])->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new CommonException('SHOP_ORDER_REFUND_IS_INVALID');//退款已失效
        if ($order_refund_info[ 'status' ] != OrderRefundDict::BUYER_REFUND_GOODS_WAIT_STORE) throw new CommonException('SHOP_ORDER_REFUND_IS_ONLY_WAIT_REFUND_GOODS');//退款已失效(只有待确认收货请求才可以修改退款)

        $order_goods_id = $order_refund_info[ 'order_goods_id' ];
        //查询订单项信息
        $order_goods_info = ( new OrderGoods() )->where([
            [ 'order_goods_id', '=', $order_goods_id ],
        ])->findOrEmpty();
        if ($order_goods_info->isEmpty()) throw new CommonException('SHOP_ORDER_IS_INVALID');//订单已失效
        //根据退款方式来判断下一步的状态
        $update_data = array();
        if ($is_agree) {
            $update_data[ 'status' ] = OrderRefundDict::STORE_AGREE_REFUND_WAIT_TRANSFER;
        } else {
            $update_data[ 'status' ] = OrderRefundDict::STORE_REFUSE_TAKE_REFUND_GOODS_WAIT_BUYER;
            $update_data[ 'shop_reason' ] = $data[ 'shop_reason' ];
        }
        $order_refund_info->save($update_data);
        $data[ 'order_refund_no' ] = $order_refund_no;
        $data[ 'refund_data' ] = array_merge($order_refund_info->toArray(), $update_data);
        //确认收货审核完毕后续事件
        event('AfterShopOrderRefundAuditRefundGoods', $data);
        return true;
    }

    /**
     * 退款关闭
     * @param $data
     * @return void
     */
    public function close($data,$refund_status=OrderRefundDict::CLOSE)
    {
        $order_refund_no = $data[ 'order_refund_no' ];
        //查询订单项信息
        $main_type = $data[ 'main_type' ] ?? '';
        $where = [
            [ 'order_refund_no', '=', $order_refund_no ],

        ];
        if ($main_type == OrderRefundLogDict::MEMBER) {
            $where[] = [ 'member_id', '=', $data[ 'main_id' ] ];
        }
        $order_refund_info = $this->model->where($where)->findOrEmpty();
        if ($order_refund_info->isEmpty()) throw new CommonException('SHOP_ORDER_REFUND_IS_INVALID');//退款已失效
        if (in_array($order_refund_info[ 'status' ], [ OrderRefundDict::STORE_AGREE_REFUND_WAIT_TRANSFER, OrderRefundDict::STORE_REFUND_TRANSFERING, OrderRefundDict::FINISH, OrderRefundDict::CLOSE, OrderRefundDict::SHOP_ACTIVE_CLOSE_REFUND ])) throw new CommonException('SHOP_ORDER_REFUND_IS_INVALID_OR_FINISH');//退款已失效(退款已完成或已关闭)
        $update_data = array(
            'status' => $refund_status,
            'close_time' => time()
        );
        $order_refund_info->save($update_data);

        //对应的要将订单项还原
        $order_goods_where = array(
            [ 'order_refund_no', '=', $order_refund_no ]
        );
        $order_goods_update_data = array(
            'status' => OrderGoodsDict::NORMAL
        );
        ( new OrderGoods() )->where($order_goods_where)->update($order_goods_update_data);
        //订单申请退款后事件
        $data[ 'order_refund_no' ] = $order_refund_no;
        $data[ 'refund_data' ] = array_merge($order_refund_info->toArray(), $update_data);
        event('AfterShopOrderRefundClose', $data);
        return true;
    }

    /**
     * 商家主动退款
     * @param $data
     * @return true
     */
    public function shopActiveRefund($data)
    {
        $order_goods_ids = $data[ 'order_goods_ids' ];
        $shop_active_refund_money = $data[ 'shop_active_refund_money' ];

        $check_data = ( new CoreRefundService() )->refundCheck($order_goods_ids, $shop_active_refund_money);
        $order_goods_list = $check_data[ 'order_goods_list' ];

        foreach ($order_goods_list as $order_goods_info) {
            $order_refund_no = create_no();
            $insert_data = [
                'order_id' => $order_goods_info[ 'order_id' ],
                'order_goods_id' => $order_goods_info[ 'order_goods_id' ],
                'order_refund_no' => $order_refund_no,
                'refund_type' => OrderRefundDict::ONLY_REFUND,
                'reason' => get_lang('dict_shop_order_refund_action.shop_active_refund'),
                'member_id' => $order_goods_info[ 'member_id' ],
                'apply_money' => $order_goods_info[ 'item_refund_money' ],
                'money' => $order_goods_info[ 'item_refund_money' ],
                'status' => OrderRefundDict::STORE_AGREE_REFUND_WAIT_TRANSFER,
                'remark' => $data[ 'shop_active_refund_remark' ],
                'voucher' => [],
                'source' => OrderRefundDict::ACTIVE_REFUND,
                'is_refund_delivery' => $order_goods_info[ 'is_refund_delivery' ],
            ];
            $order_refund_info = $this->model->create($insert_data);

            //将订单项的退款单号覆盖
            $order_goods_info->save([
                'order_refund_no' => $order_refund_no,
                'shop_active_refund' => 1,
                'shop_active_refund_money' => $order_goods_info[ 'item_refund_money' ],
                'status' => OrderGoodsDict::REFUNDING
            ]);

            $data[ 'refund_data' ] = $order_refund_info->toArray();
            $data[ 'order_goods_data' ] = $order_goods_info->toArray();
            event('AfterShopOrderRefundActiveCreate', $data);
        }
        return true;
    }


}
