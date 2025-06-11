<?php

return [
    'SHOP_ORDER_NOT_FOUND' => '订单不存在',
    'SHOP_ORDER_IS_INVALID' => '订单已失效',
    'SHOP_ONLY_WAIT_TAKE_CAN_BE_TAKE' => '只有待收货的订单才可以收货',
    'SHOP_ONLY_WAIT_DELIVERY_CAN_BE_DELIVERY' => '只有待发货的订单才可以发货',
    'SHOP_ONLY_WAIT_PAY_CAN_BE_PAY' => '只有待支付的订单可以支付',
    'SHOP_GOODS_CATEGORY_NOT_EXIST' => '分类不存在',
    'SHOP_GOODS_CATEGORY_EXIST_CHILD' => '当前分类存在下级，不可修改为该上级',
    'SHOP_GOODS_CATEGORY_EXIST_GOODS' => '当前分类下存在商品，不可删除',
    'SHOP_GOODS_NOT_EXIST' => '商品不存在',
    'SHOP_GOODS_LABEL_NAME_REPEAT'=>'标签名称重复',
    'SHOP_GOODS_LABEL_IN_USE'=>'该标签正在使用中',
    'NOT_SUPPORT_DELIVERY_TYPE' => '不支持选择的配送方式',
    'NOT_SUPPORT_DELIVERY_ADDRESS' => '所选的收货地址不支持配送',
    'NOT_SELECT_ADDRESS' => '需要先选择收货地址',
    'NOT_SELECT_STORE' => '请选择自提点',
    'NOT_NEED_DELIVERY_START_PRICE' => '未达到起送费用',
    'EXIST_ORDER_NOT_DELETE_GOODS' => '存在未完成的订单，无法删除商品',
    'EXIST_ORDER_NOT_EDIT_GOODS' => '存在未完成的订单，无法编辑商品',
    'COUPON_STOCK_INSUFFICIENT' => '优惠券已领完',
    'COUPON_NOT_EXIST' => '优惠券不存在',
    'COUPON_INVALID' => '优惠券已失效',
    'COUPON_CAN_NOT_MANUAL_RECEIVE' => '该优惠券不可手动领取',
    'COUPON_RECEIVE_NOT_TIME' => '优惠券不在领取时间范围内',
    'COUPON_RECEIVE_EXCESS' => '已领取数量超过限制领取数量，不可领取',
    'COUPON_RECEIVE_TYPE_NOT_EXIST' => '优惠券领取方式有误',
    'COUPON_RECEIVE_SUCCESS' => '领取成功',
    'MEMBER_ALREADY_COLLECT' => '已收藏，不可重复收藏',
    'CANCEL_COLLECT_SUCCESS' => '取消收藏成功',

    'SHOP_COUPON_IS_USED_OR_EXIST' => '优惠券不存在或已使用',
    'SHOP_COUPON_VALID_END_TIME_NOT_ALLOW_LT_START_TIME' => '优惠券的有效期结束时间不能小于当前时间',
    'SHOP_COUPON_IN_USE_NOT_ALLOW_EDIT' => '优惠券正在参与营销活动，禁止修改',
    'SHOP_COUPON_IN_USE_NOT_ALLOW_DEL' => '优惠券已有用户领取，禁止删除',
    'SHOP_GOODS_NOT_HAS_DEFAULT_SPEC' => '商品缺少默认规格',
    'SHOP_GOODS_DELISTED' => '此商品已下架',
    'SHOP_GOODS_EVALUATE_SUCCESS' => '评价成功',
    'SHOP_GOODS_PARTICIPATE_IN_ACTIVE_DISABLED_EDIT' => '商品正在参与营销活动，禁止修改',


    'SHOP_THE_LINE_ITEM_SUBTOTAL_CAN_T_BE_LESS_THAN_0' => '订单项小计总额不能小于0',
    'SHOP_THE_SHIPPING_FEE_CANNOT_BE_LESS_THAN_0' => '运费不能小于0',
    'SHOP_ONLY_PENDING_ORDERS_CAN_BE_REPRICED' => '只有待支付的订单可以改价',
    'SHOP_THE_AVAILABLE_SHIPPING_RATES_AREN_T_CONFIGURED' => '未配置可用的运费模板',

    'SHOP_ONLY_PENDING_ORDERS_EDIT_TAKER' => '只有待支付或待发货的订单可以修改配送地址',
    'SHOP_VIRTUAL_ORDERS_EDIT_TAKER' => '虚拟商品订单不可以修改配送地址',
    'EXPRESS_FIELD_EMPTY' => '地址信息有误',
    'GOODS_NOT_DELIVERY_TYPE' => '当前商品不支持该配送方式',
    'DELIVERY_TYPE_NOT_OPEN' => '商家未开启该配送方式',

    'SHOP_THE_ITEM_IS_BEING_REFUNDED_OR_HAS_BEEN_REFUNDED' => '当前商品项存在退款',

    'NOT_GET_SET_TYPE' => '未获取到设置类型',
    'NOT_GET_SHOP_INFO' => '未获取到商品信息',

    /********************************************* 订单相关 start ****************************************************/
    'SHOP_ORDER_HAS_REFUNDING_NOT_ALLOW_FINISH' => '订单中存在退款,无法收货',
    'SHOP_ORDER_IS_PAY_FINISH' => '订单已支付',
    'SHOP_ORDER_IS_CLOSED' => '订单已关闭',
    'SHOP_ORDER_STATUS_NOT_SUPPORT_ACTION' => '当前的操作与订单状态不符',
    'SHOP_ORDER_COUPON_EXPIRE_OR_NOT_FOUND' => '优惠券已使用或已过期',
    'SHOP_ORDER_COUPON_EXPIRE' => '当前优惠券已过期',
    'SHOP_ORDER_COUPON_NOT_SUPPORT_GOODS' => '当前优惠券在本单不可用',
    'SHOP_ORDER_COUPON_NOT_SUPPORT_MIN_MONEY' => '未达到当前优惠券的最低使用条件',
    'SHOP_ORDER_PLEASE_SELECT_DELIVERY_TYPE' => '请选择正确的配送方式',
    'SHOP_ORDER_PLEASE_SELECT_DELIVERY_EMPTY_LNG_LAT' => '所选同城配送没有设置地图定位',
    'SHOP_ORDER_CARTS_EXPIRE' => '购物车数据已过期',
    'SHOP_ORDER_BUYER_NOT_FOUND' => '找不到买家',
    'SHOP_ORDER_BUYER_LOCKED' => '该会员已被锁定，请联系商家',
    'SHOP_ORDER_DELIVERY_NOT_ALLOW_REFUND_OR_DELIVERY_FINISH' => '存在退款或已发货的商品不能发货',
    'SHOP_ORDER_DELIVERY_ALLOW_ONE_GOODS_TYPE' => '一次发货只能存在一种商品类型的订单项',
    'SHOP_ORDER_DELIVERY_VIRTUAL_ALLOW_VIRTUAL_DELIVERY' => '虚拟商品只支持虚拟发货',
    'SHOP_ORDER_DELIVERY_TYPE_NOT_ORDER_DELIVERY_TYPE' => '发货方式需要与订单的配送方式相匹配',
    'SHOP_ORDER_COUPON_SUPPORT_GOODS' => '没有适用的商品',
    'SHOP_ORDER_COUPON_NOT_CONDITION' => '未达到最低可使用金额',
    'SHOP_ORDER_DELIVERY_SUCCESS' => '发货成功',
    'SHOP_ORDER_DELIVERY_EXPRESS_NUMBER_EXITS' => '物流单号不能重复',
    'NOT_CONFIGURED_LOCAL_DELIVERY' => '商家未配置同城配送',
    'NOT_CONFIGURED_DELIVERY_TYPE' => '商家尚未配置配送方式',

    'SHOP_ELECTRONIC_SHEET_API_EMPTY' => '未配置电子面单接口',
    'SHOP_ELECTRONIC_SHEET_TEMPLATE_FOUND' => '电子面单模板不存在',

    'SHOP_ADDRESS_DEFAULT_EMPTY' => '商家未设置默认地址',

    'SHOP_ORDER_GOODS_INSUFFICIENT' => '商品库存不足',
    'SHIPPING_TEMPLATE_IN_USE' => '运费模板有商品正在使用中不能删除',

    //虚拟订单
    'SHOP_ORDER_ITEM_HAS_BEEN_WRITTEN_OFF_OR_EXPIRED' => '商品已核销或已过期',
    'SHOP_ORDER_HAS_BEEN_CLOSED_OR_COMPLETED' => '订单已关闭或已完成!',
    'SHOP_GOODS_CURRENT_PRODUCT_DOES_NOT_SUPPORT_WRITE_OFF' => '当前商品不支持核销',
    'SHOP_ORDER_MAXIMUM_NUMBER_OF_WRITE_OFFS_HAS_BEEN_REACHED' => '已达到最大核销次数',
    'SHOP_ORDER_ITEM_HAS_EXPIRED' => '商品已过期',
    /*********************************************  订单相关 end ****************************************************/
    /********************************************* 订单退款 start ****************************************************/
    'SHOP_ORDER_REFUND_IS_INVALID' => '退款已失效',
    'SHOP_ORDER_REFUND_IS_INVALID_OR_FINISH' => '退款已完成或已关闭',
    'SHOP_ORDER_REFUND_WAIT_PAY_OR_CLOSE' => '退款未支付或已关闭',
    'SHOP_ORDER_REFUND_IS_REFUND_FINISH' => '订单已退款或存在未完成的退款',
    'SHOP_ORDER_REFUND_MONEY_GT_ORDER_MONEY' => '退款金额不能大于可退款总额',
    'SHOP_ORDER_REFUND_MONEY_LESS_THAN_ZERO' => '退款金额不能小于0',
    'SHOP_ORDER_REFUND_STATUS_NOT_SUPPORT_ACTION' => '当前的操作与退款状态不符',
    'SHOP_ORDER_IS_NOT_ENABLE_REFUND' => '订单不允许退款',
    'SHOP_ORDER_REFUND_SELECT_ADDRESS' => '请选择退货地址',
    'SHOP_ORDER_REFUND_IS_ONLY_WAIT_REFUND_GOODS' => '只有待确认退货请求才可以审核',
    'SHOP_ORDER_REFUND_IS_ONLY_WAIT_REFUND' => '只有待确认退款请求才可以审核',
    'SHOP_ORDER_BUYER_APPLY_REFUND' => '买家申请退款',
    'SHOP_ORDER_STORE_ACTIVE_REFUND' => '卖家主动退款',
    'SHOP_ORDER_REFUND_DELIVERY_NOT_ALLOW_REFUND_GOODS' => '待发货的商品项不允许退货退款',
    'SHOP_ORDER_DELETE_STATUS_ERROR' => '删除订单中有未完成订单：%s',
    /*********************************************  订单退款 end ****************************************************/


    'INVOICE_NOT_EXIST' => '发票不存在',
    'INVOICED' => '已开票',

    /*********************************************  活动start ****************************************************/
    'END_TIME_NOT_LESS_CURRENT_TIME' => '活动结束时间不能小于当前时间',
    'ACTIVE_GOODS_NOT_EMPTY' => '请选择参与活动商品',
    'ACTIVE_GOODS_SKU_NOT_EMPTY' => '商品规格不能为空',
    'ACTIVE_NOT_FOUND' => '活动未找到',
    'ACTIVE_NOT_EDIT' => '活动不可编辑',
    'ACTIVE_GOODS_NOT_REPEAR' => '同一商品在一个时间段内只能参加一个限时折扣活动',
    'ACTIVE_NOT_DELETE' => '进行中活动不能直接删除',
    'ACTIVE_GOODS_DISCOUNT_TYPE_NOT_EMPTY' => '折扣类型不能为空',
    'ACTIVE_GOODS_DISCOUNT_TYPE_ERROR' => '折扣类型错误',
    'ACTIVE_GOODS_DISCOUNT_PRICE_NOT_EMPTY' => '折扣价格discount_price不能为空',
    'ACTIVE_GOODS_SPECIFY_PRICE_NOT_EMPTY' => '促销价不能为空',
    'ACTIVE_GOODS_DISCOUNT_RATE_NOT_EMPTY' => '打折折扣不能为空',
    'ACTIVE_GOODS_REDUCE_MONEY_NOT_EMPTY' => '减钱金额不能为空',
    'ACTIVE_IS_ENABLED_NOT_EMPTY' => 'is_enabled必传',
    /*********************************************  活动end ****************************************************/

    /*********************************************  积分商城start ****************************************************/
    'EXCHANGE_GOODS_CONFIRM_TYPE' => '请确认兑换的类型',
    'EXCHANGE_GOODS_POINT_GREATER_THAN_ZERO' => '商品兑换积分要大于零',
    'EXCHANGE_GOODS_STOCK_GREATER_THAN_ZERO' => '商品兑换库存要大于零',
    'EXCHANGE_GOODS_NOT_EMPTY' => '请选择参与活动商品',
    'EXCHANGE_COUPON_NOT_EMPTY' => '请选择要参与的优惠券',
    'EXCHANGE_COUPON_NOT_EXIST' => '优惠券不存在',
    'EXCHANGE_BALANCE_GREATER_THAN_ZERO' => '商品兑换余额要大于零',
    'EXCHANGE_DETA_NOT_FOUND' => '积分信息活动未找到',
    'EXCHANGE_ACTIVITY_REMOVE' => '此积分商品已经下架',
    'EXCHANGE_GOODS_ACTIVITY_EXISTING' => '此商品已经参与积分兑换活动了',
    /*********************************************   积分商城end ****************************************************/

    /*********************************************  订单业务 *****************************************************/
    'SHOP_ORDER_EXCHANGE_EXCEEDING_LIMIT' => '此积分商品超出单次限购数量',
    'SHOP_ORDER_EXCHANGE_INSUFFICIENT_EXCHANGE_QUANTITY' => '此积分商品可兑换数量不足',
    'SHOP_ORDER_EXCHANGE_POINT_INSUFFICIENT' => '账户积分不足',

    /*********************************************  新人专享start ****************************************************/
    'ACTIVE_GOODS_NEWCOMER_PRICE_NOT_EMPTY' => '新人价不能为空',
    /*********************************************   新人专享end ****************************************************/

    /*********************************************  商品排行榜start ****************************************************/
    'SHOP_RANK_NOT_EXIST' => '商品榜单不存在',
    /*********************************************   商品排行榜end ****************************************************/

    /*********************************************  满减送start ****************************************************/
    'MANJIANSONG_GOODS_NOT_REPEAR' => '同一商品在一个时间段内只能参加一个满减送活动',
    'MANJIANSONG_ALL_GOODS_EXIT' => '当前时间段已存在类型为全部商品参与的满减送活动，不可再添加其它满减送活动',
    'MANJIANSONG_CLOSED' => '满减送活动已关闭',
    'MANJIANSONG_NOT_FOUND' => '满减送活动未找到',
    /*********************************************   满减送end ****************************************************/

    /*********************************************  限时折扣start ****************************************************/
    'DISCOUNT_END_TIME_NOT_LESS_CURRENT_TIME' => '活动结束时间不能小于当前时间',
    'DISCOUNT_GOODS_NOT_EMPTY' => '请选择参与活动商品',
    'DISCOUNT_GOODS_SKU_NOT_EMPTY' => '商品规格不能为空',
    'DISCOUNT_NOT_FOUND' => '活动未找到',
    'DISCOUNT_NOT_EDIT' => '活动不可编辑',
    'DISCOUNT_GOODS_NOT_REPEAR' => '同一商品在一个时间段内只能参加一个限时折扣活动',
    'DISCOUNT_NOT_DELETE' => '进行中活动不能直接删除',
    'DISCOUNT_GOODS_DISCOUNT_TYPE_NOT_EMPTY' => '折扣类型不能为空',
    'DISCOUNT_GOODS_DISCOUNT_TYPE_ERROR' => '折扣类型错误',
    'DISCOUNT_GOODS_DISCOUNT_PRICE_NOT_EMPTY' => '折扣价格discount_price不能为空',
    'DISCOUNT_GOODS_SPECIFY_PRICE_NOT_EMPTY' => '促销价不能为空',
    'DISCOUNT_GOODS_DISCOUNT_RATE_NOT_EMPTY' => '打折折扣不能为空',
    'DISCOUNT_GOODS_REDUCE_MONEY_NOT_EMPTY' => '减钱金额不能为空',
    'DISCOUNT_IS_ENABLED_NOT_EMPTY' => 'is_enabled必传',
    /*********************************************  限时折扣end ****************************************************/

];
