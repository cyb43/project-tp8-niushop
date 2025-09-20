<?php

return [
    'dict_shop_delivery' => [
        'num' => '按件',
        'weight' => '按重量',
        'volume' => '按体积'
    ],
    'dict_shop_goods' => [
        'real' => '实物商品',
        'real_desc' => '物流发货',
        'virtual' => '虚拟商品',
        'virtual_desc' => '无需物流'
    ],
    'dict_shop_app_manage' => [
        'coupon' => '优惠券',
        'marketing' => '营销活动',
    ],
    'dict_shop_order' => [
        'status_wait_pay' => '待支付',
        'status_wait_shipping' => '待发货',
        'status_wait_pickup' => '待提货',
        'status_wait_take' => '待收货',
        'status_finish' => '已完成',
        'status_close' => '已关闭'
    ],
    'dict_shop_delivery_type' => [
        'express' => '物流配送',
        'local_delivery' => '同城配送',
        'store' => '门店自提',
        'virtual' => '虚拟发货',
        'none_express' => '无需物流'
    ],
    'dict_shop_delivery_store_time_interval' => [
        '30' => '30分钟',
        '60' => '1小时',
        '90' => '90分钟',
        '120' => '2小时',
    ],

    'dict_shop_coupon' => [
        'user' => '手动领取',
        'grant' => '后台或活动发放',
        'all' => '通用券',
        'category' => '品类券',
        'goods' => '商品券',
        'wait_start' => '未开始',
        'normal' => '进行中',
        'expire' => '已过期',
        'invalid' => '已失效',
    ],
    'dict_shop_member_coupon' => [
        'wait_use' => '待使用',
        'used' => '已使用',
        'expire' => '已过期',
        'invalid' => '已失效',
        'send' => '后台发放',
        'receive' => '用户手动领取'
    ],
    'dict_shop_send_coupon_range' => [//后台发送优惠券范围类型
        'all' => '全部会员',
        'member' => '选择会员',
        'member_level' => '按会员等级',
        'member_label' => '按会员标签',
    ],
    'dict_shop_send_coupon_range_type_desc' => [//后台发送优惠券范围类型描述
        'all' => '全部会员',
        'member' => '指定会员',
        'member_level' => '指定会员等级',
        'member_label' => '指定会员标签',
    ],
    'dict_shop_send_coupon_status' => [//后台发送优惠券状态
        'wait' => '待发放',
        'progress' => '发放中',
        'finish' => '发放完成',
    ],
    'dict_wap_index' => [
        'shop' => '商城',
        'shop_desc' => '商品购买，配送'
    ],
    'dict_shop_order_log' => [
        'store' => '商家',
        'member' => '会员',
        'system' => '系统'
    ],
    'dict_shop_goods_evaluate' => [
        'audit_no' => '无需审核',
        'audit' => '待审核',
        'audit_adopt' => '审核通过',
        'audit_refuse' => '审核拒绝'
    ],
    'dict_shop_order_action' => [
        'order_create_action' => '订单创建',
        'order_pay_action' => '订单支付',
        'order_close_action' => '订单关闭',
        'order_delivery_action' => '订单发货',
        'order_finish_action' => '订单完成',
        'order_remark_action' => '订单商家备注',
        'order_close_allow_refund' => '订单自动关闭售后',
        'order_edit_price_action' => '订单改价',
    ],
    'dict_shop_invoice' => [
        'header_type_company' => '企业',
        'header_type_person' => '个人',
        'common' => '普票',
        'sprcial' => '专票',
        'wait_open' => '未生效',
        'open' => '已生效',
        'wait_invoice' => '待开票',
        'invoiced' => '已开票',
    ],
    'dict_shop_order_refund_status' => [
        'buyer_apply_wait_store' => '买家申请售后',
        'store_agree_refund_goods_apply_wait_buyer' => '商家同意售后',
        'store_refuse_refund_goods_apply_wait_buyer' => '商家拒绝售后',
        'buyer_refund_goods_wait_store' => '买家已退货',
        'store_refuse_take_refund_goods_wait_buyer' => '商家拒绝收货',
        'store_agree_refund_wait_transfer' => '同意售后申请',
        'store_refund_transfering' => '转账中',
        'finish' => '退款成功',
        'close' => '退款关闭',
        'shop_active_close_refund' => '商家关闭售后'
    ],
    'dict_shop_order_close_type' => [
        'shop_close' => '后台关闭',
        'buyer_close' => '用户关闭',
        'auto_close' => '未支付自动关闭',
        'refund_close' => '退款完成关闭',
    ],
    'dict_diy' => [
        'shop_component_type_basic' => '商城组件',
        'shop_goods_detail_component_type_basic' => '商品详情组件',
        'page_shop_index' => '商城首页',
        'page_shop_member_index' => '商城个人中心',
        'page_shop_point_index' => '积分商城',
        'page_shop_detail' => '商品详情',

        'shop_title' => '商城',
        'shop_link' => '商城系统',
        'shop_link_basic' => '商城链接',
        'shop_link_index' => '商城首页',
        'shop_link_goods_category' => '商品分类',
        'shop_link_goods_list' => '商品列表',
        'shop_link_goods_rank' => '商品排行榜',
        'shop_link_goods_search' => '商品搜索',
        'shop_link_goods_cart' => '购物车',
        'page_link_member_index' => '商城个人中心',
        'shop_link_coupon_list' => '优惠券列表',
        'shop_link_my_coupon' => '我的优惠券',
        'shop_link_my_goods_collect' => '商品收藏',
        'shop_link_my_goods_browse' => '我的足迹',
        'shop_link_order_list' => '订单列表',
        'shop_link_order_refund_list' => '退款列表',
        'shop_link_point_index' => '积分商城',
        'shop_link_point_list' => '积分商品列表',
        'shop_link_point_order_list' => '积分兑换记录',
        'shop_link_discount_list' => '限时折扣列表',
        'shop_link_newcomer_list' => '新人专享',
        'shop_link_goods_select' => '选择商品',
        'shop_link_goods_category_select' => '商品分类',
        'shop_link_coupon_select' => '优惠券',
        'shop_link_point_exchange_select' => '积分商品',
        'shop_link_goods_rank_select' => '商品榜单'
    ],
    'dict_diy_poster' => [
        'shop_goods_component_type_basic' => '商品组件',
    ],
    'dict_diy_form' => [
        'shop_component_type_basic' => '商城表单组件',
        'type_goods_detail' => '商品表单',
        'type_order_payment' => '待付款表单',
    ],
    'dict_shop_delivery_status' => [
        'wait_delivery' => '待发货',
        'delivery' => '配送中',
        'delivery_finish' => '已发货',
        'taked' => '已收货'
    ],
    'dict_shop_order_refund_action' => [
        'apply' => '买家申请退款',
        'edit_apply' => '买家修改退款',
        'delivery' => '买家退货',
        'edit_delivery' => '买家修改退货',
        'agree_audit_apply' => '卖家同意退款',
        'refuse_audit_apply_action' => '卖家拒绝退款申请',
        'agree_audit_refund_goods' => '卖家同意退货',
        'refuse_audit_refund_goods' => '卖家拒绝退货',
        'active_refund' => '卖家主动退款',
        'shop_active_refund' => '商家主动退款',
        'finish' => '退款完成',
        'close' => '关闭退款'
    ],

    'dict_shop_order_refund_reason' => [
        'delivery_timeout' => '未按约定时间发货',
        'buy_more_or_dislike' => '拍错/多拍/不喜欢',
        'negotiation_completed' => '协商一致退款',
        'other' => '其他',
    ],


    'dict_shop_order_refund_type' => [
        'only_refund' => '仅退款',
        'return_and_refund_goods' => '退款退货'
    ],
    'dict_shop_order_refund' => [
        'normal' => '正常',
        'refunding' => '退款中',
        'refund_finish' => '退款完成'
    ],

    'dict_shop_active_status' => [
        'not_active' => '未开始',
        'active' => '进行中',
        'end' => '已结束',
        'close' => '已关闭'
    ],
    'dict_shop_active_class' => [
        'discount' => '限时折扣',
        'exchange' => '积分商城',
        'manjiansong' => '满减送',
        'newcomer_discount' => '新人专享',
        'giftcard' => '礼品卡',
        'pintuan' => '拼团'
    ],
    'dict_shop_active_type' => [
        'shop' => '店铺活动',
        'goods' => '商品活动',
        'member' => '会员活动',
    ],
    'dict_shop_active_goods_type' => [
        'single' => '单品',
        'independent' => '独立商品',
        'shop' => '店铺整体商品',
    ],

    'dict_shop_point_exchange_type' => [
        'goods' => '商品',
        'coupon' => '优惠券',
        'balance' => '余额',
    ],

    'dict_shop_manjian_condition_type' => [
        'over_n_yuan' => '满N元',
        'over_n_piece' => '满N件'
    ],

    'dict_shop_manjian_goods_type' => [
        'all_goods' => '全部商品参与',
        'selected_goods' => '指定商品参与',
        'selected_goods_not' => '指定商品不参与'
    ],

    'dict_shop_manjian_join_member_type' => [
        'all_member' => '所有会员参与',
        'selected_member_level' => '指定会员等级',
        'selected_member_label' => '指定会员标签'
    ],

    'dict_shop_manjian_rule_type' => [
        'ladder' => '阶梯优惠',
        'cycle' => '循环优惠',
    ],

    'dict_shop_manjian_status' => [
        'not_active' => '未开始',
        'active' => '进行中',
        'end' => '已结束',
        'close' => '已关闭',
    ],

    'dict_member' => [
        'account_point_exchange_close' => '兑换订单关闭',
        'account_point_exchange_order' => '兑换订单消费',
        'account_point_exchange_refund' => '兑换订单售后',
        'account_point_consume_reward' => '下单奖励',
        'account_point_manjian_gift_give' => '满减送活动赠品发放',
        'account_point_manjian_gift_back' => '满减送活动赠品退还',
        'account_balance_manjian_gift_give' => '满减送活动赠品发放',
        'account_balance_manjian_gift_back' => '满减送活动赠品退还',
        'recharge_point_give' => '会员充值积分发放',
        'account_point_sow_community_settle_reward' => '种草奖励发放',
        'account_balance_sow_community_settle_reward' => '种草奖励发放',
    ],

    'dict_shop_delivery_electronic_sheet' => [
        'cash_payment' => '现付',
        'freight_collect' => '到付',
        'monthly_statement' => '月结'
    ],

    'dict_shop_batch_delivery_status' => [
        'processing' => '处理中',
        'finish' => '已完成',
        'fail' => '任务失败'
    ],
    'dict_shop_batch_delivery_type' => [
        'delivery' => '批量导入发货',
    ],

    // 商品排行榜单
    'dict_shop_goods_rank_rank_type' => [
        'day' => '天',
        'week' => '周',
        'month' => '月',
        'quarter' => '季度'
    ],
    'dict_shop_goods_rank_goods_source' => [
        'goods' => '指定商品',
        'category' => '指定分类',
        'brand' => '指定品牌',
        'label' => '指定标签',
        'all' => '全部',
    ],

    'dict_shop_goods_rank_rule_type' => [
        'sale' => '按销量',
        'collect' => '按收藏数',
        'evaluate' => '按评价数',
        'access' => '按浏览量'
    ],

    //商品统计
    'dict_shop_goods_statistics_type' => [
        'access_num' => '访问次数',
        'cart_num' => '加入购物车数量',
        'sale_num' => '商品销量',
        'pay_num' => '支付件数',
        'collect_num' => '收藏数量',
        'pay_money' => '支付总金额',
        'goods_visit_member_count' => '访客数',
    ],

    //批量设置
    'dict_shop_goods_batch_set' => [
        'label' => '商品标签',
        'service' => '商品服务',
        'virtual_sale_num' => '虚拟销量',
        'category' => '商品分类',
        'brand' => '商品品牌',
        'poster' => '商品海报',
        'gift' => '是否赠品',
        'delivery' => '配送设置',
        'stock' => '商品库存',
        'member_discount' => '会员折扣',
        'diy_detail' => '商品模版',
        'diy_form' => '万能表单',
    ],
    'dict_shop_goods_sort_config' => [
        'asc' => '正序排序',
        'desc' => '倒序排序',
        'price' => '价格',
        'sort' => '排序号',
        'sale_num' => '销量',
        'create_time' => '创建时间',
    ],
];
