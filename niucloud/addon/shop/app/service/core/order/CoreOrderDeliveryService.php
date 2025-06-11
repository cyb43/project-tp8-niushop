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

use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderDelivery;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\model\shop_address\ShopAddress;
use addon\shop\app\service\core\delivery\CoreElectronicSheetService;
use app\dict\pay\PayDict;
use app\model\member\Member;
use app\model\pay\Pay;
use app\service\core\weapp\CoreWeappDeliveryService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Log;

/**
 *  订单配送服务层
 */
class CoreOrderDeliveryService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 发货入口
     * @param $data
     * @return true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function delivery($data)
    {
        $order_id = $data[ 'order_id' ];
        $order_goods_ids = $data[ 'order_goods_ids' ];//订单项id
        //查询订单
        $where = array(
            [ 'order_id', '=', $order_id ],
        );
        $order = $this->model->where($where)->findOrEmpty();
        if ($order->isEmpty()) throw new CommonException('SHOP_ORDER_NOT_FOUND');//订单不存在

        if ($order[ 'status' ] != OrderDict::WAIT_DELIVERY) throw new CommonException('SHOP_ONLY_WAIT_DELIVERY_CAN_BE_DELIVERY');//只有待收货的订单才可以收货

        //赠品跟随下单商品一起发货
        $gift_order_goods_ids = ( new OrderGoods() )->where([
            [ 'order_id', '=', $order_id ],
            [ 'is_gift', '=', 1 ],
            [ 'status', '=', OrderGoodsDict::NORMAL ],
            [ 'delivery_status', '=', OrderDeliveryDict::WAIT_DELIVERY ]
        ])->column('order_goods_id');
        if (!empty($gift_order_goods_ids)) {
            $order_goods_ids = array_merge($order_goods_ids, $gift_order_goods_ids);
            $data[ 'order_goods_ids' ] = $order_goods_ids;
        }

        //配送
        $delivery_type = $data[ 'delivery_type' ];
        //不用的订单项针对的发货方式不同
        $order_goods_where = [
            [
                'order_id', '=', $order_id
            ],
            [
                'order_goods_id', 'in', $order_goods_ids
            ],
            [
                'status', '=', OrderGoodsDict::NORMAL
            ],
            [
                'delivery_status', '=', OrderDeliveryDict::WAIT_DELIVERY
            ]
        ];
        $order_goods_data = ( new OrderGoods() )->where($order_goods_where)->select();
        if ($order_goods_data->count() != count($order_goods_ids)) throw new CommonException('SHOP_ORDER_DELIVERY_NOT_ALLOW_REFUND_OR_DELIVERY_FINISH');//存在退款的商品不能发货
        $has_goods_type_array = [];
        foreach ($order_goods_data as $v) {

            if($v['is_gift'] == 1) continue;

            if (!in_array($v[ 'goods_type' ], $has_goods_type_array)) {
                $has_goods_type_array[] = $v[ 'goods_type' ];
            }
        }
        if (count($has_goods_type_array) != 1) throw new CommonException('SHOP_ORDER_DELIVERY_ALLOW_ONE_GOODS_TYPE');//一次发货只能发送一种商品类型的订单项
        $goods_type = $has_goods_type_array[ 0 ];

        $delivery_param = [
            'order_data' => $order,
            'order_goods_data' => $order_goods_data,
            'param' => $data
        ];
        switch ($goods_type) {
            case GoodsDict::VIRTUAL://只要有虚拟商品,就可以使用虚拟发货
                if ($delivery_type != OrderDeliveryDict::VIRTUAL) throw new CommonException('SHOP_ORDER_DELIVERY_VIRTUAL_ALLOW_VIRTUAL_DELIVERY');//虚拟商品只支持虚拟发货
                //虚拟发货
                $this->virtual($delivery_param);
                break;
            case GoodsDict::REAL:
                if (in_array($delivery_type, OrderDeliveryDict::getChildType($order[ 'delivery_type' ]))) throw new CommonException('SHOP_ORDER_DELIVERY_TYPE_NOT_ORDER_DELIVERY_TYPE');//不支持的配送方式
                switch ($order[ 'delivery_type' ]) {
                    case OrderDeliveryDict::EXPRESS://快递
                        $this->express($delivery_param);
                        break;
                    case OrderDeliveryDict::LOCAL_DELIVERY://配送
                        $this->localDelivery($delivery_param);
                        break;
                    case OrderDeliveryDict::STORE://自提
                        $this->store($delivery_param);
                        break;
                }
                break;

            //todo  可以扩展新的商品形式
        }
        //校验是否全部发放完毕
        $this->checkFinish($data);

        return true;
    }

    /**
     * 虚拟发货
     * @param $data
     * @return void
     */
    public function virtual($data)
    {
        $order_data = $data[ 'order_data' ];
        $order_goods_data = $data[ 'order_goods_data' ];
        $param = $data[ 'param' ];
        $delivery_type = $param[ 'delivery_type' ];
        $insert_data = [
            'order_id' => $order_data[ 'order_id' ],
            'delivery_type' => $order_data[ 'delivery_type' ],
            'sub_delivery_type' => $delivery_type,
            'remark' => $param[ 'remark' ] ?? '',
        ];
        $delivery_id = $this->package($insert_data);

        //todo 甚至可自动收货
        $order_goods_data->update([
            'delivery_status' => OrderDeliveryDict::DELIVERY_FINISH,
            'delivery_id' => $delivery_id
        ]);

        //todo  实际调用虚拟配送的操作(类似生成核销码.....)
        $goods_ids = array_column($order_goods_data->toArray(), 'goods_id');
        $temp_goods_list = ( new Goods() )->where([ [ 'goods_id', 'in', $goods_ids ] ])->column('*', 'goods_id');
        foreach ($order_goods_data as $v) {
            $temp_goods = $temp_goods_list[ $v[ 'goods_id' ] ];
            $virtual_receive_type = $temp_goods[ 'virtual_receive_type' ];
            //如果需要核销,则生成核销码
            if ($virtual_receive_type == 'verify') {//生成核销码
                //待核销(todo  可能设置待核销状态)
                if ($temp_goods[ 'virtual_verify_type' ] == 0) {
                    $expire_time = 0;
                } else if ($temp_goods[ 'virtual_verify_type' ] == 1) {
                    $expire_time = time() + 86400 * $temp_goods[ 'virtual_indate' ];
                } else {
                    $expire_time = $temp_goods[ 'virtual_indate' ];
                }
                //设置虚拟商品的核销有效期
                $order_goods_data->update([
                    'verify_expire_time' => $expire_time,
                    'is_verify' => 1,
                ]);
//                (new CoreVerifyService())->create('virtual_verify', ['goods_id' => $v['goods_id'], 'sku_id' => $v['sku_id'], 'order_goods_id' => $v['order_goods_id']]);
            } else if ($virtual_receive_type == 'auto') {//todo 自动收货  (订单发货后,校验订单项是否全部收货,如果已全部收货,订单就只接完成)
                ( new CoreOrderFinishService() )->orderGoodsTake([
                    'order_id' => $order_data[ 'order_id' ],
                    'order_goods_ids' => [ $v[ 'order_goods_id' ] ]
                ]);
            }
        }

        return true;
    }

    /**
     * 订单配送包裹(order_delivery)
     * @param $data
     * @return mixed
     */
    public function package($data)
    {
        $delivery = ( new OrderDelivery() )->create($data);
        return $delivery->id;
    }

    /**
     * 物流发货
     * @param $data
     * @return true
     */
    public function express($data)
    {
        $order_data = $data[ 'order_data' ];
        $order_goods_data = $data[ 'order_goods_data' ];
        $param = $data[ 'param' ];
        $delivery_type = $param[ 'delivery_type' ];
        $order_goods_ids = $param[ 'order_goods_ids' ]; // 订单项id
        $delivery_way = $param[ 'delivery_way' ]; // 发货方式，manual_write：手动填写，electronic_sheet：电子面单
        $electronic_sheet_id = $param[ 'electronic_sheet_id' ]; // 电子面单模板

        $insert_data = array(
            'order_id' => $order_data[ 'order_id' ],
            'delivery_type' => $order_data[ 'delivery_type' ],
            'sub_delivery_type' => $delivery_type,
            'express_company_id' => $param[ 'express_company_id' ],
            'express_number' => $param[ 'express_number' ],
            'remark' => $param[ 'remark' ],
        );

        // 手动填写
        if ($delivery_way == 'manual_write') {
            if (!empty($param[ 'express_number' ])) {
                $express_number_count = ( new OrderDelivery() )->where([
                    [ 'order_id', '=', $order_data[ 'order_id' ] ],
                    [ 'express_number', '=', $param[ 'express_number' ] ]
                ])->count();
                if ($express_number_count > 0) throw new CommonException('SHOP_ORDER_DELIVERY_EXPRESS_NUMBER_EXITS');//物流单号不能重复
            }
        } elseif ($delivery_way == 'electronic_sheet') {
            if ($delivery_type == OrderDeliveryDict::EXPRESS) {
                // 电子面单
                $electronic_sheet_result = ( new CoreElectronicSheetService() )->printElectronicSheetByDelivery([
                    'order_id' => $order_data[ 'order_id' ],
                    'electronic_sheet_id' => $electronic_sheet_id,
                    'order_goods_data' => $order_goods_data->toArray()
                ]);
                if ($electronic_sheet_result[ 'success' ]) {
                    $insert_data[ 'express_number' ] = $electronic_sheet_result[ 'order_info' ][ 'LogisticCode' ]; // 获取电子面单返回的快递单号
                } else {
                    throw new CommonException($electronic_sheet_result[ 'reason' ]);
                }
            }
        }

        $delivery_id = $this->package($insert_data);
        $order_goods_data->update([
            'delivery_status' => OrderDeliveryDict::DELIVERY_FINISH,
            'delivery_id' => $delivery_id
        ]);
        return true;
    }

    /**
     * 同城配送
     * @param $data
     * @return true
     */
    public function localDelivery($data)
    {
        $order_data = $data[ 'order_data' ];
        $order_goods_data = $data[ 'order_goods_data' ];
        $param = $data[ 'param' ];
        $delivery_type = $param[ 'delivery_type' ];
        $insert_data = array(
            'order_id' => $order_data[ 'order_id' ],
            'delivery_type' => $order_data[ 'delivery_type' ],
            'sub_delivery_type' => $delivery_type,
            'local_deliver_id' => $param[ 'local_deliver_id' ],
            'remark' => $param[ 'remark' ],
        );
        $delivery_id = $this->package($insert_data);
        $order_goods_data->update([
            'delivery_status' => OrderDeliveryDict::DELIVERY_FINISH,
            'delivery_id' => $delivery_id
        ]);
        return true;
    }

    /**
     * 门店自提
     * @param $data
     * @return true
     */
    public function store($data)
    {
        $order_data = $data[ 'order_data' ];
        $order_goods_data = $data[ 'order_goods_data' ];
        $param = $data[ 'param' ];
        $delivery_type = $param[ 'delivery_type' ];
        $insert_data = array(
            'order_id' => $order_data[ 'order_id' ],
            'delivery_type' => $order_data[ 'delivery_type' ],
            'sub_delivery_type' => $delivery_type,
            'remark' => $param[ 'remark' ],
        );
        $delivery_id = $this->package($insert_data);
        $order_goods_data->update([
            'delivery_status' => OrderDeliveryDict::DELIVERY_FINISH,
            'delivery_id' => $delivery_id
        ]);
        return true;
    }

    /**
     * 校验订单下的订单项是否全部发货
     * @param $data
     * @return void
     */
    public function checkFinish($data)
    {
        $order_id = $data[ 'order_id' ];
        //校验订单下的有效的订单项是否全部发货完毕
        $where = array(
            [
                'delivery_status', '=', OrderDeliveryDict::WAIT_DELIVERY
            ],
            [
                'order_id', '=', $order_id
            ],
            [
                'status', '<>', OrderGoodsDict::REFUND_FINISH,//不包含退款完毕的
            ]
        );
        $order_goods_count = ( new OrderGoods() )->where($where)->count();
        $where_delivery = array(
            [
                'delivery_status', 'in', [ OrderDeliveryDict::DELIVERY_FINISH, OrderDeliveryDict::TAKED ]
            ],
            [
                'order_id', '=', $order_id
            ],
            [
                'status', '<>', OrderGoodsDict::REFUND_FINISH,//不包含退款完毕的
            ]
        );
        $order_goods_delivery_count = ( new OrderGoods() )->where($where_delivery)->count();
        //完成
        if ($order_goods_count == 0 && $order_goods_delivery_count > 0) {
            $this->finish($data);
        }
        return true;
    }

    /**
     * 发货完成
     * @param $data
     * @return void
     */
    public function finish($data)
    {
        $order_id = $data[ 'order_id' ];
        //查询订单
        $where = array(
            [ 'order_id', '=', $order_id ],
            [ 'status', '=', OrderDict::WAIT_DELIVERY ]
        );
        $order = $this->model->where($where)->findOrEmpty();
        if ($order->isEmpty()) throw new CommonException('SHOP_ORDER_NOT_FOUND');//订单不存在

        $order_data = array(
            'delivery_time' => time(),
            'status' => OrderDict::WAIT_TAKE,
            'timeout' => 0
        );
        $order->save($order_data);
        //订单发货后操作
        $data[ 'order_data' ] = $order->toArray();
        event('AfterShopOrderDelivery', $data);
        //订单发货操作
        CoreOrderEventService::orderDelivery($data);
        //订单发货后操作
//        CoreOrderEventService::orderDeliveryAfter($data);

        //如果有且只有一个虚拟商品并且已经收货，则订单完成
        $order_goods_list = ( new OrderGoods() )->where([
            [ 'order_id', '=', $order_id ],
        ])->select()->toArray();
        if (count($order_goods_list) == 1) {
            $first_order_goods = $order_goods_list[ 0 ];
            if ($first_order_goods[ 'goods_type' ] == GoodsDict::VIRTUAL && $first_order_goods[ 'delivery_status' ] == OrderDeliveryDict::TAKED) {
                ( new CoreOrderFinishService() )->finish([ 'order_id' => $order_id ]);
            }
        }

        return true;
    }

    /**
     * 微信小程序 发货信息录入接口
     * @param $params
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function orderShippingUploadShippingInfo($params)
    {
        try {

            $order_id = $params[ 'order_id' ];

            //查询订单
            $where = array(
                [ 'order_id', '=', $order_id ],
            );
            $order_data = $this->model->where($where)->findOrEmpty();

            //订单不存在
            if ($order_data->isEmpty()) {
                return '';
            }

            //不用的订单项针对的发货方式不同
            $order_goods_where = [
                [ 'order_id', '=', $order_id ],
                [ 'status', '=', OrderGoodsDict::NORMAL ]
            ];

            $order_goods_data = ( new OrderGoods() )->where($order_goods_where)->select();

            if ($order_goods_data->count() == 0) {
                return '';
            }

            $pay_model = new Pay();
            $where = array(
                [ 'out_trade_no', '=', $order_data[ 'out_trade_no' ] ]
            );
            $pay_info = $pay_model->where($where)->field('id,type')->findOrEmpty()->toArray();

            if (empty($pay_info)) {
                return '';
            }

            // 订单未使用微信支付，无须处理
            if ($pay_info[ 'type' ] != PayDict::WECHATPAY) {
                return '订单未使用微信支付';
            }

            $weapp_delivery_service = new CoreWeappDeliveryService();

            // 检测微信小程序是否已开通发货信息管理服务
            $is_trade_managed = $weapp_delivery_service->isTradeManaged();

            if (empty($is_trade_managed[ 'is_trade_managed' ])) {
                return '发货信息录入接口，报错：' . $is_trade_managed[ "errmsg" ];
            }

            // 设置消息跳转路径设置接口
            $result_jump_path = $weapp_delivery_service->setMsgJumpPath('shop_order');
            if ($result_jump_path[ 'errcode' ] != 0) {
                return '设置消息跳转路径设置接口，报错：' . $result_jump_path[ "errmsg" ];
            }

            $logistics_type = 1; // 物流模式，发货方式枚举值：1、实体物流配送采用快递公司进行实体物流配送形式 2、同城配送 3、虚拟商品，虚拟商品，例如话费充值，点卡等，无实体配送形式 4、用户自提

            if ($order_data[ 'delivery_type' ] == OrderDeliveryDict::EXPRESS) {

                // 1、实体物流配送采用快递公司进行实体物流配送形式
                $logistics_type = 1;

            } else if ($order_data[ 'delivery_type' ] == OrderDeliveryDict::STORE) {

                // 门店自提
                $logistics_type = 4;

            } else if ($order_data[ 'delivery_type' ] == OrderDeliveryDict::LOCAL_DELIVERY) {

                // 同城配送
                $logistics_type = 2;

            } else if ($order_data[ 'delivery_type' ] == OrderDeliveryDict::VIRTUAL) {

                // 虚拟商品，例如话费充值，点卡等，无实体配送形式
                $logistics_type = 3;

            }

            $order_goods_list = $order_goods_data;

            $shipping_list = [];
            $first_shipping_info = [];

            // 获取物流公司
            $delivery_list = [];

            $delivery_mode = 1; // 发货模式，发货模式枚举值：1、UNIFIED_DELIVERY（统一发货）2、SPLIT_DELIVERY（分拆发货） 示例值: UNIFIED_DELIVERY
            if ($logistics_type == 1) {
                $delivery_mode = count($order_goods_list) == 1 ? 1 : 2;
                $result_delivery_list = $weapp_delivery_service->getDeliveryList();
                if ($result_delivery_list[ 'errcode' ] == 0 && !empty($result_delivery_list[ 'delivery_list' ])) {
                    $delivery_list = $result_delivery_list[ 'delivery_list' ];
                }

            }

            // 查询商家默认发货地址
            $shop_address_field = 'contact_name,mobile,province_id,city_id,district_id,address,full_address,lat,lng';
            $shop_address = ( new ShopAddress() )->where([ [ 'is_default_delivery', '=', 1 ] ])->field($shop_address_field)->findOrEmpty()->toArray();

            foreach ($order_goods_list as $k => $v) {

                // 一笔支付单最多分拆成 10 个包裹
                if ($k == 9) {
                    break;
                }

                $express_company = ''; // 物流公司编码，快递公司ID，参见「查询物流公司编码列表」，物流快递发货时必填， 示例值: DHL 字符字节限制: [1, 128]
                $tracking_no = ''; // 物流单号，物流快递发货时必填，示例值: 323244567777 字符字节限制: [1, 128]

                if ($logistics_type == 1) {

                    $field = 'id, order_id, delivery_type, sub_delivery_type, express_company_id,express_number';
                    $info = ( new OrderDelivery() )->where([ [ 'id', '=', $v[ 'delivery_id' ] ] ])->with([
                        'company' => function($query) {
                            $query->field('company_id, company_name, express_no');
                        }
                    ])->field($field)->findOrEmpty()->toArray();

                    if (!empty($info) && $info[ 'express_company_id' ] && !empty($delivery_list)) {
                        $tracking_no = $info[ 'express_number' ];
                        $index = array_search($info[ 'company' ][ 'express_no' ], array_column($delivery_list, 'delivery_id'));
                        if ($index !== false && isset($delivery_list[ $index ])) {
                            $express_company = $delivery_list[ $index ][ 'delivery_id' ];
                        }
                        if (empty($express_company)) {
                            return '物流公司不支持，请更换其他物流公司';
                        }
                    } else {
                        $logistics_type = 2; // 无需物流的情况，无实体配送形式
                        $delivery_mode = 1; // 统一发货
                    }

                }

                $item = [
                    'tracking_no' => $tracking_no, // 物流单号，物流快递发货时必填，示例值: 323244567777 字符字节限制: [1, 128]
                    'express_company' => $express_company, // 物流公司编码，快递公司ID，参见「查询物流公司编码列表」，物流快递发货时必填， 示例值: DHL 字符字节限制: [1, 128]
                    'item_desc' => str_sub($v[ 'goods_name' ] . $v[ 'sku_name' ], 90) . '*' . $v[ 'num' ], // 商品信息，例如：微信红包抱枕*1个，限120个字以内
                    'contact' => [
                        'consignor_contact' => '',
                        'receiver_contact' => ''
                    ]
                ];

                // 寄件人联系方式，寄件人联系方式，采用掩码传输，最后4位数字不能打掩码 示例值: `189****1234, 021-****1234, ****1234, 0**2-***1234, 0**2-******23-10, ****123-8008` 值限制: 0 ≤ value ≤ 1024
                if (!empty($shop_address[ 'mobile' ])) {
                    $item[ 'contact' ][ 'consignor_contact' ] = substr($shop_address[ 'mobile' ], 0, 3) . '****' . substr($shop_address[ 'mobile' ], 7);
                }

                // 收件人联系方式，收件人联系方式为，采用掩码传输，最后4位数字不能打掩码 示例值: `189****1234, 021-****1234, ****1234, 0**2-***1234, 0**2-******23-10, ****123-8008` 值限制: 0 ≤ value ≤ 1024
                if (!empty($order_data[ 'taker_mobile' ])) {
                    $item[ 'contact' ][ 'receiver_contact' ] = substr($order_data[ 'taker_mobile' ], 0, 3) . '****' . substr($order_data[ 'taker_mobile' ], 7);
                }

                $shipping_list[] = $item;
                if ($k == 0) {
                    $first_shipping_info = $item;
                }
            }

            // 统一发货，只能有一个物流信息，拼装商品信息
            if ($delivery_mode == 1) {
                if (count($shipping_list) > 1) {
                    foreach ($shipping_list as $k => $v) {
                        if ($k > 0) {
                            $first_shipping_info[ 'item_desc' ] .= ',' . $v[ 'item_desc' ];
                        }
                    }
                }
                $first_shipping_info[ 'item_desc' ] = str_sub($first_shipping_info[ 'item_desc' ], 90);
            }

            // 分拆发货模式时必填，用于标识分拆发货模式下是否已全部发货完成，只有全部发货完成的情况下才会向用户推送发货完成通知。示例值: true/false
            $is_all_delivered = false;
            if ($delivery_mode == 2) {
                $is_all_delivered = true;
            }

            $member_info = ( new Member() )->where([ [ 'member_id', '=', $order_data[ 'member_id' ] ] ])->field('weapp_openid')->findOrEmpty()->toArray();

            $data = [
                'out_trade_no' => $order_data[ 'out_trade_no' ],
                'logistics_type' => $logistics_type, // 物流模式，发货方式枚举值：1、实体物流配送采用快递公司进行实体物流配送形式 2、同城配送 3、虚拟商品，虚拟商品，例如话费充值，点卡等，无实体配送形式 4、用户自提
                'delivery_mode' => $delivery_mode, // 发货模式，发货模式枚举值：1、UNIFIED_DELIVERY（统一发货）2、SPLIT_DELIVERY（分拆发货） 示例值: UNIFIED_DELIVERY
                // 同城配送没有物流信息，只能传一个订单
                'shipping_list' => $delivery_mode == 1 ? [ $first_shipping_info ] : $shipping_list, // 物流信息列表，发货物流单列表，支持统一发货（单个物流单）和分拆发货（多个物流单）两种模式，多重性: [1, 10]
                'weapp_openid' => $member_info[ 'weapp_openid' ], // 用户标识，用户在小程序appid下的唯一标识。 下单前需获取到用户的Openid 示例值: oUpF8uMuAJO_M2pxb1Q9zNjWeS6o 字符字节限制: [1, 128]
                'is_all_delivered' => $is_all_delivered
            ];

            $weapp_delivery_service->uploadShippingInfo($data);
        } catch (\Exception $e) {
            Log::write('商城订单发货失败' . $e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

}
