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

namespace addon\shop\app\service\admin\order;

use addon\shop\app\dict\delivery\DeliveryDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\delivery\Local;
use addon\shop\app\model\delivery\Store;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderDelivery;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\admin\delivery\DeliveryService;
use addon\shop\app\service\core\delivery\CoreLocalDeliveryService;
use addon\shop\app\service\core\delivery\third_delivery\ThirdDeliveryLoader;
use addon\shop\app\service\core\order\CoreOrderEventService;
use addon\shop\app\service\core\order\CoreOrderPayService;
use app\dict\common\ChannelDict;
use app\dict\pay\PayDict;
use app\model\diy_form\DiyFormRecordsFields;
use app\model\member\Member;
use app\model\pay\Pay;
use app\model\verify\Verify;
use app\service\core\pay\CorePayService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Location\Polygon;
use think\db\exception\DbException;
use think\db\Query;
use think\facade\Db;

/**
 * 订单
 */
class OrderService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 分页列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where)
    {
        $field = 'buyer_ask_delivery_time,point,activity_type,order_id,order_no,order_type,order_from,out_trade_no,status,member_id,ip,goods_money,delivery_money,order_money,create_time,pay_time,delivery_type,taker_name,taker_mobile,taker_full_address,take_store_id,is_enable_refund,member_remark,shop_remark,close_type,close_remark,pay_money';
        $order = 'create_time desc';

        $pay_where = [];
        if ($where[ 'pay_type' ]) {
            if ($where[ 'pay_type' ] == PayDict::FRIENDSPAY) {
                $pay_where = [
                    [ 'pay.main_id', '<>', Db::raw("pay.from_main_id") ],
                    [ 'pay.from_main_id', '>', 0 ],
                    [ 'pay.status', '=', PayDict::STATUS_FINISH ]
                ];
            } else {
                $pay_where[] = [ 'pay.type', '=', $where[ 'pay_type' ] ];
            }
        }
        $member_where = [];
        if ($where[ 'keyword' ] != '') {
            $member_where = [
                [ 'member.member_no|member.nickname|member.username|member.mobile', 'like', "%" . $where[ 'keyword' ] . "%" ],
            ];
        }
        $search_model = $this->model
            ->where([ [ 'order.order_id', '>', 0 ] ])
            ->withSearch([ 'search_type', 'order_from', 'join_status', 'create_time', 'join_pay_time', 'activity_type' ], $where)
            ->field($field)
            ->withJoin([
                'pay' => function (Query $query) use ($pay_where) {
                    $query->where($pay_where);
                },
                'member' => function (Query $query) use ($member_where) {
                    $query->where($member_where);
                },

            ], 'left')
            ->with([
                'order_goods' => function ($query) {
                    $query->with([ 'deliveryInfo' => function ($query) {
                        $query->field('*');
                    } ])
                        ->field('extend,order_goods_id, order_id, member_id, goods_id, sku_id, goods_name, sku_name, goods_image, sku_image, price, num, goods_money, is_enable_refund, goods_type, delivery_status, status,discount_money,delivery_id,is_gift')
                        ->append([ 'delivery_status_name', 'status_name', 'goods_image_thumb_small', 'impulse_buy_info', 'delivery_info' ]);
                }
            ])->order($order)->append([ 'order_from_name', 'order_type_name', 'status_name', 'delivery_type_name' ]);
        $order_status_list = OrderDict::getStatus();
        $order_close_list = OrderDict::getCloseType();
        $list = $this->pageQuery($search_model, function ($item, $key) use ($order_status_list, $order_close_list) {
            $item[ 'close_type_name' ] = $order_close_list[ $item[ 'close_type' ] ] ?? "";
            $item[ 'order_status_data' ] = $order_status_list[ $item[ 'status' ] ] ?? [];
            $item_pay = $item[ 'pay' ];
            if (!empty($item_pay)) {
                $item_pay->append([ 'type_name' ]);
                $item_pay[ 'pay_type_name' ] = PayDict::getPayType()[ PayDict::FRIENDSPAY ][ 'name' ] ?? '';
            }
        });
        return $list;
    }

    /**
     * 详情
     * @param int $order_id
     * @return array
     */
    public function getDetail(int $order_id)
    {
        $field = 'activity_type,point,order_id,order_no,order_type,order_from,out_trade_no,status,member_id,ip,goods_money,delivery_money,order_money,invoice_id,create_time,pay_time,delivery_time,take_time,finish_time,close_time,delivery_type,taker_name,taker_mobile,buyer_ask_delivery_time,taker_province,taker_city,taker_district,taker_address,taker_full_address,taker_longitude,taker_latitude,take_store_id,is_enable_refund,member_remark,shop_remark,close_remark,discount_money,form_record_id';
        $info = $this->model->where([ [ 'order_id', '=', $order_id ] ])->field($field)
            ->with(
                [
                    'order_goods' => function ($query) {
                        $query->field('extend,order_goods_id, order_id, member_id, goods_id, sku_id, goods_name, sku_name, goods_image, sku_image, price, num, goods_money, is_enable_refund, goods_type, delivery_status, status,discount_money,delivery_id,is_gift,form_record_id')->append([ 'delivery_status_name', 'status_name', 'impulse_buy_info' ]);
                    },
                    'member' => function ($query) {
                        $query->field('member_id, nickname, mobile, headimg');
                    },
                    'order_log' => function ($query) {
                        $query->field('order_id, content, main_type, create_time, main_id, type')->order("create_time desc, id desc")->append([ 'main_type_name', 'type_name', 'main_name' ]);
                    },
                    'order_discount' => function ($query) {
                        $query->field('order_id,discount_type,money,content');
                    }
                ])->append([ 'order_from_name', 'order_type_name', 'status_name', 'delivery_type_name' ])->findOrEmpty()->toArray();
        $info[ 'verify_code' ] = ''; // 核销码
        $info[ 'verifier_member' ] = ''; // 核销员
        $order_status_list = OrderDict::getStatus();
        if (!empty($info)) $info[ 'order_status_data' ] = $order_status_list[ $info[ 'status' ] ] ?? [];
        if ($info[ 'delivery_type' ] == DeliveryDict::STORE) {
            $info[ 'store' ] = ( new Store() )->where([ [ 'store_id', '=', $info[ 'take_store_id' ] ] ])
                ->field('store_id, store_name, full_address, store_mobile, trade_time')
                ->findOrEmpty()->toArray();
            $verify_info = ( new Verify() )->where([
                [ 'type', '=', 'shopPickUpOrder' ],
                [ 'relate_tag', '=', $order_id ]
            ])->field('id, code, verifier_member_id')->with([ 'member' => function ($query) {
                $query->field('member_id, nickname');
            } ])->findOrEmpty()->toArray();
            if (!empty($verify_info)) {
                $info[ 'verify_code' ] = $verify_info[ 'code' ];
                $info[ 'verifier_member' ] = $verify_info[ 'member' ];
            }
        }

        if ($info[ 'delivery_type' ] == DeliveryDict::EXPRESS || $info[ 'delivery_type' ] == DeliveryDict::LOCAL_DELIVERY) {
            $info[ 'order_delivery' ] = ( new OrderDelivery() )
                ->where([ [ 'order_id', '=', $info[ 'order_id' ] ] ])
                ->field('id, order_id, name, delivery_type, sub_delivery_type,express_company_id, express_number, create_time,third_delivery')
                ->append([ 'third_delivery_name' ])
                ->select()->toArray();
            if ($info[ 'delivery_type' ] == DeliveryDict::LOCAL_DELIVERY) {
                $third_party_config = ( new CoreLocalDeliveryService() )->getConfig();
                foreach ($info[ 'order_delivery' ] as $k => $item) {
                    if (!empty($item[ 'third_delivery' ])) {
                        $third_delivery = $item[ 'third_delivery' ];
                        $loader = new ThirdDeliveryLoader($third_delivery, $third_party_config[ $third_delivery ]);
                        //三方配送下单
                        $info[ 'order_delivery' ][ $k ][ 'third_delivery_info' ] = $loader->queryOrderInfo([
                            'order_no' => $info[ 'order_no' ]
                        ]);
                    }
                }
            }
        }


        if ($info[ 'out_trade_no' ]) {
            $info[ 'pay' ] = ( new Pay() )->where([ [ 'out_trade_no', '=', $info[ 'out_trade_no' ] ] ])
                ->field('main_id, out_trade_no, type, pay_time, status')->append([ 'type_name' ])->findOrEmpty()->toArray();

            if (!empty($info[ 'pay' ])) {
                if ($info[ 'member_id' ] != $info[ 'pay' ][ 'main_id' ]) {
                    $member_info = ( new Member() )->where([ [ 'member_id', '=', $info[ 'pay' ][ 'main_id' ] ] ])->findOrEmpty()->toArray();
                    if (!empty($member_info)) {
                        $info[ 'pay' ][ 'pay_member' ] = $member_info[ 'nickname' ];
                    }
                }
                $info[ 'pay' ][ 'pay_type_name' ] = PayDict::getPayType()[ PayDict::FRIENDSPAY ][ 'name' ] ?? '';
            }
        }

        $coupon_money = 0;
        $manjian_discount_money = 0;
        if ($info[ 'order_discount' ]) {
            foreach ($info[ 'order_discount' ] as $item) {
                if ($item[ 'discount_type' ] == 'coupon') {
                    $coupon_money += $item[ 'money' ];
                }
                if ($item[ 'discount_type' ] == 'manjian') {
                    $manjian_discount_money += $item[ 'money' ];
                }
            }
        }
        $info[ 'coupon_money' ] = number_format($coupon_money, 2, '.', '');
        $info[ 'manjian_discount_money' ] = number_format($manjian_discount_money, 2, '.', '');

        $diy_form_records_fields_model = new DiyFormRecordsFields();
        if (!empty($info[ 'form_record_id' ])) {
            $field_count = $diy_form_records_fields_model->where([ [ 'record_id', '=', $info[ 'form_record_id' ] ] ])->count();
            if ($field_count > 0) {
                $info[ 'form_record_show' ] = true;
            } else {
                $info[ 'form_record_show' ] = false;
            }
        }
        if (!empty($info[ 'order_goods' ])) {
            foreach ($info[ 'order_goods' ] as &$item) {
                $field_count = $diy_form_records_fields_model->where([ [ 'record_id', '=', $item[ 'form_record_id' ] ] ])->count();
                if ($field_count > 0) {
                    $item[ 'form_record_show' ] = true;
                } else {
                    $item[ 'form_record_show' ] = false;
                }
                $impulse_buy_tips = '';
                if (isset($item[ 'extend' ][ 'is_impulse_buy' ]) == 1) {
                    $impulse_buy_num = $item[ 'extend' ][ 'impulse_buy_goods_num' ] ?? 0;//5
                    $impulse_buy_price_total = $item[ 'extend' ][ 'impulse_buy_price' ] ?? 0;
                    if ($impulse_buy_num > 0) {
                        $impulse_buy_price = $impulse_buy_price_total / $impulse_buy_num;
                        if ($impulse_buy_num == 1) {
                            $impulse_buy_tips = '第1' . '件' . $impulse_buy_price . '元';
                        } else {
                            $impulse_buy_tips = '第1-' . $impulse_buy_num . '件' . $impulse_buy_price . '元';
                            if ($item[ 'num' ] > $impulse_buy_num) {
                                if ($impulse_buy_num + 1 == $item[ 'num' ]) {
                                    $impulse_buy_tips .= ' 第' . ( $impulse_buy_num + 1 ) . '件' . $item[ 'price' ] . '元';
                                } else {
                                    $impulse_buy_tips .= ' 第' . ( $impulse_buy_num + 1 ) . '-' . $item[ 'num' ] . '件' . $item[ 'price' ] . '元';
                                }
                            }
                        }
                    } else {//全原价
                        $impulse_buy_tips = '第1-' . $item[ 'num' ] . '件' . $item[ 'price' ] . '元';
                    }
                }
                $item[ 'impulse_buy_info' ][ 'show_tips' ] = $impulse_buy_tips;
            }
        }
        return $info;
    }

    /**
     * 商家留言
     * @param $data
     * @return bool
     */
    public function shopRemark($data)
    {
        $this->model->where([ [ 'order_id', '=', $data[ 'order_id' ] ] ])->update([ 'shop_remark' => $data[ 'shop_remark' ] ]);
        return true;
    }

    /**
     * 订单数量统计
     * @throws DbException
     */
    public function getOrderCount()
    {
        $data = [
            "wait_pay_order" => 0, //待付款
            "wait_delivery_order" => 0, //待发货
            "wait_take_order" => 0, //待收货
            "refund_order" => 0, //退款中（订单项）
        ];

        $data[ 'wait_pay_order' ] = $this->model->where([ [ 'status', '=', OrderDict::WAIT_PAY ] ])->count();
        $data[ 'wait_delivery_order' ] = $this->model->where([ [ 'status', '=', OrderDict::WAIT_DELIVERY ] ])->count();
        $data[ 'wait_take_order' ] = $this->model->where([ [ 'status', '=', OrderDict::WAIT_TAKE ] ])->count();
        $data[ 'refund_order' ] = ( new OrderGoods() )->where([ [ 'status', '=', OrderGoodsDict::REFUNDING ] ])->count();

        return $data;
    }

    /**
     * 订单改价
     * @param $data
     * @return void
     */
    public function editPrice($data)
    {
        $order_id = $data[ 'order_id' ];
        $order = $this->model->where([ [ 'order_id', '=', $order_id ] ])->findOrEmpty();
        if ($order->isEmpty()) throw new AdminException('SHOP_ORDER_NOT_FOUND');
        if ($order[ 'status' ] != OrderDict::WAIT_PAY) throw new AdminException('SHOP_ONLY_PENDING_ORDERS_CAN_BE_REPRICED');

        //关闭相关的支付  todo  封装订单专用的关闭支付相关
        try {
            ( new CorePayService() )->closeByTrade(OrderDict::TYPE, $order_id);
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }

        $delivery_money = $data[ 'delivery_money' ];
        if ($delivery_money < 0) throw new AdminException('SHOP_THE_SHIPPING_FEE_CANNOT_BE_LESS_THAN_0');
        $order_goods_data = $data[ 'order_goods_data' ];//['order_goods_id' => ['money' => 10]]
        $order_goods_model = new OrderGoods();
        $order_goods_list = $order_goods_model->where([ [ 'order_id', '=', $order_id ] ])->select();
        $goods_money = 0;
        Db::startTrans();
        try {
            foreach ($order_goods_list as $key => $item) {
                $item_order_goods_id = $item[ 'order_goods_id' ];
                $temp_goods_data = $order_goods_data[ $item_order_goods_id ] ?? [];

                if (!empty($temp_goods_data)) {
                    $item_money = $temp_goods_data[ 'money' ] ?? 0;
                    if ($item_money != 0) {
                        $item_order_goods_money = $item[ 'order_goods_money' ];//订单项总额
                        $item_new_order_goods_money = round($item_order_goods_money + $item_money, 2);//
                        if ($item_new_order_goods_money < 0) {
                            throw new AdminException('SHOP_THE_LINE_ITEM_SUBTOTAL_CAN_T_BE_LESS_THAN_0');
                        }
                        $item_new_goods_money = $item_new_order_goods_money + $item[ 'discount_money' ];
                        $item_new_price = floor($item_new_goods_money / $item[ 'num' ] * 100) / 100;

                        $order_goods_list[ $key ][ 'old_goods_money' ] = $item[ 'goods_money' ];

                        $item->save([
                            'price' => $item_new_price,
                            'goods_money' => $item_new_goods_money,
                            'order_goods_money' => $item_new_order_goods_money
                        ]);
                        $goods_money += $item_new_goods_money;
                        continue;
                    }
                }
                $goods_money += $item[ 'goods_money' ];
            }
            $order_money = round($goods_money + $delivery_money - $order[ 'discount_money' ], 2);
            if ($order_money < 0) {
                $order_money = 0;
            }

            $order[ 'old_delivery_money' ] = $order[ 'delivery_money' ];

            $order->save([
                'goods_money' => $goods_money,
                'delivery_money' => $delivery_money,
                'order_money' => $order_money
            ]);

            $order_param = [
                'order_data' => $order->toArray(),
                'order_goods_data' => $order_goods_list->toArray(),
                'main_id' => $this->uid,
                'main_type' => OrderLogDict::SYSTEM
            ];
            //订单改价操作
            CoreOrderEventService::orderEditPrice($order_param);
            //订单改价后操作
            CoreOrderEventService::orderEditPriceAfter($order_param);
            if ($order_money == 0) {
                ( new CoreOrderPayService() )->pay([ 'trade_id' => $order_id, 'main_type' => OrderLogDict::SYSTEM, 'main_id' => $this->uid ]);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 修改收货地址
     * @param $data
     */
    public function editDelivery($data)
    {
        $data = $this->getEditDeliveryData($data);
        if ($data[ 'error_code' ] < 0) {
            throw new AdminException($data[ 'error_msg' ] ?? '');
        }
        $delivery_data = $data[ 'delivery_data' ];
        $this->model->where([ [ 'order_id', '=', $data[ 'order_id' ] ] ])->update($delivery_data);
        return true;
    }

    /**
     * 订单修改收货地址信息
     * @param $data
     */
    public function getEditDeliveryData($data)
    {
        $delivery_type = $data[ 'delivery_type' ];
        $order_id = $data[ 'order_id' ];
        $order = $this->model->where([ [ 'order_id', '=', $order_id ] ])->findOrEmpty()->toArray();
        $delivery_list = ( new DeliveryService() )->getDeliveryList();
        if (empty($order)) throw new AdminException('SHOP_ORDER_NOT_FOUND');
        if (!in_array($order[ 'status' ], [ OrderDict::WAIT_PAY, OrderDict::WAIT_DELIVERY ])) throw new AdminException('SHOP_ONLY_PENDING_ORDERS_EDIT_TAKER');
        if ($order[ 'delivery_type' ] == OrderDeliveryDict::VIRTUAL) throw new AdminException('SHOP_VIRTUAL_ORDERS_EDIT_TAKER');
        $order[ 'delivery_data' ] = $data;

        $order[ 'order_goods' ] = ( new OrderGoods() )->where([ [ 'goods_type', '=', 'real' ], [ 'order_id', '=', $order_id ] ])->with([ 'goods' ])->select()->toArray();
        $order[ 'error_code' ] = 1;
        $order[ 'error_msg' ] = '';
        if (empty($delivery_type) || !isset($delivery_list[ $delivery_type ])) {
            $order[ 'error_code' ] = -1;
            $order[ 'error_msg' ] = get_lang('NOT_SUPPORT_DELIVERY_TYPE');
            return $order;
        } else if (!empty($delivery_type) && $delivery_list[ $delivery_type ][ 'status' ] > 1) {
            $order[ 'error_code' ] = -1;
            $order[ 'error_msg' ] = get_lang('DELIVERY_TYPE_NOT_OPEN');
            return $order;
        }

        $goods_name = '';
        foreach ($order[ 'order_goods' ] as $k => &$v) {
            if (!in_array($delivery_type, $v[ 'goods' ][ 'delivery_type' ])) {
                $v[ 'error_code' ] = -1;
                $v[ 'error_msg' ] = get_lang('GOODS_NOT_DELIVERY_TYPE');
                $order[ 'error_code' ] = -1;
                $goods_name .= $v[ 'goods_name' ] . ',';
            }
        }

        if ($order[ 'error_code' ] < 0) {
            $goods_name = trim($goods_name, ',');
            if ($goods_name) $order[ 'error_msg' ] = $goods_name . '-' . get_lang('GOODS_NOT_DELIVERY_TYPE');
            return $order;
        }

        switch ($delivery_type) {
            case OrderDeliveryDict::EXPRESS:
                if (empty($data[ 'taker_name' ])
                    || empty($data[ 'taker_mobile' ])
                    || empty($data[ 'taker_province' ])
                    || empty($data[ 'taker_city' ])
                    || empty($data[ 'taker_district' ])
                    || empty($data[ 'taker_address' ])
                    || empty($data[ 'taker_full_address' ])
                ) {
                    $order[ 'error_code' ] = -1;
                    $order[ 'error_msg' ] = get_lang('EXPRESS_FIELD_EMPTY');
                }
                break;
            case OrderDeliveryDict::LOCAL_DELIVERY:
                if (empty($data[ 'taker_name' ])
                    || empty($data[ 'taker_mobile' ])
                    || empty($data[ 'taker_province' ])
                    || empty($data[ 'taker_city' ])
                    || empty($data[ 'taker_district' ])
                    || empty($data[ 'taker_address' ])
                    || empty($data[ 'taker_full_address' ])
                    || empty($data[ 'taker_longitude' ])
                    || empty($data[ 'taker_latitude' ])
                ) {
                    $order[ 'error_code' ] = -1;
                    $order[ 'error_msg' ] = get_lang('EXPRESS_FIELD_EMPTY');
                } else {
                    $order = $this->checkLocationInArea($order, $data);
                }
                break;
            case OrderDeliveryDict::STORE:
                if (empty($data[ 'take_store_id' ])) {
                    $order[ 'error_code' ] = -1;
                    $order[ 'error_msg' ] = get_lang('EXPRESS_FIELD_EMPTY');
                }
                break;
        }

        return $order;
    }

    /**
     * 检查收货地址是否在配送区域
     * @param $order
     * @param $data
     * @return mixed
     */
    public function checkLocationInArea($order, $data)
    {
        $local = ( new Local() )->where([ [ 'local_id', '>', 0 ] ])->field('fee_type,base_dist,base_price,grad_dist,grad_price,weight_start,weight_unit,weight_price,delivery_type,area,center')->findOrEmpty();
        if ($local->isEmpty()) {
            $order[ 'error_code' ] = -1;
            $order[ 'error_msg' ] = get_lang('NOT_CONFIGURED_LOCAL_DELIVERY');
        }
        // 收货地址
        $address_point = new Coordinate($data[ 'taker_latitude' ], $data[ 'taker_longitude' ]);

        // 判断所在区域
        $located_in_area = null;
        foreach ($local[ 'area' ] as $area) {
            if ($area[ 'area_type' ] == 'radius') {
                $center = new Coordinate($area[ 'area_json' ][ 'center' ][ 'lat' ], $area[ 'area_json' ][ 'center' ][ 'lng' ]);
                $distance = ( new Vincenty() )->getDistance($address_point, $center);
                if ($distance <= $area[ 'area_json' ][ 'radius' ]) {
                    $located_in_area = $area;
                    break;
                }
            } else {
                $geofence = new Polygon();
                $geofence->addPoints(array_map(function ($latlng) {
                    return new Coordinate($latlng[ 'lat' ], $latlng[ 'lng' ]);
                }, $area[ 'area_json' ][ 'paths' ]));
                if ($geofence->contains($address_point)) {
                    $located_in_area = $area;
                    break;
                }
            }
        }
        if (!$located_in_area) {
            $order[ 'error_code' ] = -1;
            $order[ 'error_msg' ] = get_lang('NOT_SUPPORT_DELIVERY_ADDRESS');
        }
        return $order;
    }

    /**
     * 获取订单来源
     * @return array
     */
    public function getOrderFrom()
    {
        $order_from_list = ChannelDict::getType();
        $from_event_list = array_filter(event('OrderFromList')) ?? [];
        foreach ($from_event_list as $item) {
            $order_from_list = array_merge($order_from_list, $item);
        }
        return $order_from_list;
    }

    /**
     * 待确定删除逻辑之后再细化
     * @param $order_ids
     * @return bool|void
     */
    public function delete($order_ids)
    {
        if (!empty($order_ids)) {
            $status_list = $this->model->whereIn('order_id', $order_ids)->column('status,out_trade_no');
            $status_arr = array_column($status_list, null, 'status');
            $status_arr = array_unique($status_arr);
            if (count($status_arr) > 1) {
                $error_order_str = '';
                foreach ($status_list as $item) {
                    if ($item[ 'status' ] == OrderDict::CLOSE) {
                        continue;
                    }
                    $error_order_str .= $item[ 'out_trade_no' ] . ',';
                }
                $error_order_str = rtrim($error_order_str, ',');
                $error_str = sprintf(get_lang('SHOP_ORDER_DELETE_STATUS_ERROR'), $error_order_str);
                throw new AdminException($error_str);
            }
            return $this->model::destroy(function ($query) use ($order_ids) {
                $query->where([ [ 'order_id', 'in', $order_ids ] ]);
            });
        }
    }
}
