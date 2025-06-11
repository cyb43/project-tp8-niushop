<?php

use app\dict\sys\SmsDict;

return [
    'shop_order_pay' => [
        'is_need_closure_content' => 1,//是否需要闭包处理content
        'content' => function ($data) {
            $sms_type = $data['sms_type'];
            if ($sms_type == SmsDict::NIUSMS) {
                return "您的订单{order_no}已支付成功";
            }
            return "您购买的“{body}”已支付成功。查看详情{url}";
        }
    ],
    'shop_order_delivery' => [
        'is_need_closure_content' => 1,//是否需要闭包处理content
        'content' => function ($data) {
            $sms_type = $data['sms_type'];
            if ($sms_type == SmsDict::NIUSMS) {
                return "您的订单{order_no}已于{delivery_time}发货";
            }
            return "您购买的“{body}”已于{delivery_time}发货。查看详情{url}";
        }
    ],
    'shop_refund_agree' => [
        'is_need_closure_content' => 0,
        'content' => '商家已同意您订单编号为{order_no}的退款申请，请关注具体的退款情况',
    ],
    'shop_refund_refuse' => [
        'is_need_closure_content' => 0,
        'content' => '很抱歉，您订单编号为{order_no}的退款申请未被同意，请您联系商家并修改申请',
    ],
];
