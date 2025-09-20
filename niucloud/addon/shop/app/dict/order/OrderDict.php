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

namespace addon\shop\app\dict\order;

use app\dict\pay\PayDict;

/**
 * 订单相关枚举类
 */
class OrderDict
{
    //订单状态
    //待支付
    const WAIT_PAY = 1;

    //待发货
    const WAIT_DELIVERY = 2;

    //待收货
    const WAIT_TAKE = 3;

    //已完成
    const FINISH = 5;

    //已关闭
    const CLOSE = -1;


    /**
     * 当前订单支持的支付方式
     */
    const ALLOW_PAY = [
        PayDict::WECHATPAY,
        PayDict::ALIPAY,
        PayDict::OFFLINEPAY,
    ];

    const TYPE = 'shop';
    const NORMAL = 1;
    const REFUNDING = 2;


    // 退款相关状态
    // 没有退款
    const REFUND_FINISH = 3;
    // 部分退款
    const ORDER_CREATE_ACTION = 'order_create';
    // 退款完成
    const ORDER_PAY_ACTION = 'order_pay';
    // 退款失败
//    const REFUND_FAIL = -1;
    const ORDER_CLOSE_ACTION = 'order_close';//订单创建
    const ORDER_DELIVERY_PART_ACTION = 'order_part_delivery';//订单支付
    const ORDER_DELIVERY_ACTION = 'order_delivery';// 订单发货
    const ORDER_FINISH_ACTION = 'order_finish';//订单部分发货
    const ORDER_REMARK_ACTION = 'order_remark';//订单发货

    const ORDER_EDIT_PRICE_ACTION = 'order_edit';//订单改价
    const ORDER_CLOSE_ALLOW_REFUND_ACTION = 'order_close_allow_refund';//订单完成
    const SHOP_CLOSE = 'shop_close';//商家关闭
    const BUYER_CLOSE = 'buyer_close';//订单关闭允许售后
    const AUTO_CLOSE = 'auto_close';
    const REFUND_CLOSE = 'refund_close';//商家主动关闭

    /**
     * 订单类型以及名称
     * @return array
     */
    public static function getOrderType()
    {
        return [
            'type' => self::TYPE,
            'name' => get_lang('dict_shop_order.order_type_shop')
        ];
    }//买家主动关闭

    public static function getStatus($status = '', $delivery_type = '')
    {
        $data = [
            self::WAIT_PAY => [
                'name' => get_lang('dict_shop_order.status_wait_pay'),//待支付
                'status' => self::WAIT_PAY,
                'is_refund' => 0,
                'action' => [],
                'member_action' => [
                    [
                        'name' => '支付',
                        'class' => '',
                        'params' => ''
                    ],
                ],
            ],
            self::WAIT_DELIVERY => [
                'name' => $delivery_type == OrderDeliveryDict::STORE ? get_lang('dict_shop_order.status_wait_pickup') : get_lang('dict_shop_order.status_wait_shipping'),//待发货
                'status' => self::WAIT_DELIVERY,
                'is_refund' => 0,
                'action' => [],
                'member_action' => [
                    [
                        'name' => '支付',
                        'class' => '',
                        'params' => ''
                    ],
                ],
            ],
            self::WAIT_TAKE => [
                'name' => get_lang('dict_shop_order.status_wait_take'),//待收货
                'status' => self::WAIT_TAKE,
                'is_refund' => 0,
                'action' => [],
                'member_action' => [
                    [
                        'name' => '支付',
                        'class' => '',
                        'params' => ''
                    ],
                ],
            ],
            self::FINISH => [
                'name' => get_lang('dict_shop_order.status_finish'),//已完成
                'status' => self::FINISH,
                'is_refund' => 0,
                'action' => [],
                'member_action' => [
                ],
            ],
            self::CLOSE => [
                'name' => get_lang('dict_shop_order.status_close'),//已关闭
                'status' => self::CLOSE,
                'is_refund' => 0,
                'action' => [],
                'member_action' => [
                ],
            ]

        ];
        if ($status == '') {
            return $data;
        }
        return $data[$status] ?? '';
    }//自动关闭

    /**
     * 订单操作类型
     * @param string $type
     * @return array|mixed|string
     */
    public static function getActionType(string $type = '')
    {
        $data = [
            self::ORDER_CREATE_ACTION => get_lang('dict_shop_order_action.order_create_action'),
            self::ORDER_PAY_ACTION => get_lang('dict_shop_order_action.order_pay_action'),
            self::ORDER_CLOSE_ACTION => get_lang('dict_shop_order_action.order_close_action'),
            self::ORDER_DELIVERY_PART_ACTION => get_lang('dict_shop_order_action.order_part_delivery'),
            self::ORDER_DELIVERY_ACTION => get_lang('dict_shop_order_action.order_delivery_action'),
            self::ORDER_FINISH_ACTION => get_lang('dict_shop_order_action.order_finish_action'),
            self::ORDER_REMARK_ACTION => get_lang('dict_shop_order_action.order_remark_action'),
            self::ORDER_CLOSE_ALLOW_REFUND_ACTION => get_lang('dict_shop_order_action.order_close_allow_refund'),
            self::ORDER_EDIT_PRICE_ACTION => get_lang('dict_shop_order_action.order_edit_price_action'),
        ];
        if (!$type) {
            return $data;
        }
        return $data[$type] ?? '';
    }

    /**
     * 关闭方式
     * @param string $type
     * @return array|mixed|string
     */
    public static function getCloseType(string $type = '')
    {
        $data = [
            self::SHOP_CLOSE => get_lang('dict_shop_order_close_type.shop_close'),
            self::BUYER_CLOSE => get_lang('dict_shop_order_close_type.buyer_close'),
            self::AUTO_CLOSE => get_lang('dict_shop_order_close_type.auto_close'),
            self::REFUND_CLOSE => get_lang('dict_shop_order_close_type.refund_close'),
        ];
        if (!$type) {
            return $data;
        }
        return $data[$type] ?? '';
    }

    /**
     * @param $type
     * @return string
     */
    public static function getOrderActiveTypeName($type)
    {
        if (empty($type)) {
            $type = self::TYPE;
        }
        $data = [
            'pintuan' => '拼团',
            'seckill' => '秒杀',
            self::TYPE => '商城',
            'exchange' => '兑换',
            'giftcard' => '礼品卡',
            'newcomer_discount' => '新人专享',
            'discount' => '限时折扣',
            'manjiansong' => '满减送',
        ];
        return $data[$type] ?? '';
    }
}
