<?php

return [
    'bind' => [

    ],
    'listen' => [
        // 手机端初始化加载事件
        'initWap' => [
            'addon\shop\app\listener\config\initWapListener'
        ],

        // 添加/编辑商品之后的事件
        'AfterGoodsEdit' => [
            'addon\shop\app\listener\point_exchange\AfterGoodsEdit'
        ],
        //订单创建
        'ShopOrderCreate' => [ 'addon\shop\app\listener\order\ShopOrderCreate' ],
        //订单创建时 优惠抵扣减免业务
        'ShopOrderDiscountCreate' => [
            'addon\shop\app\listener\point_exchange\ShopOrderDiscountCreate'   //积分商城兑换
        ],
        //订单创建后
        'AfterShopOrderCreate' => [
            'addon\shop\app\listener\order\AfterShopOrderCreate',
            'addon\shop\app\listener\point_exchange\AfterShopOrderCreate',   //积分商城业务
        ],
        //订单支付后
        'AfterShopOrderPay' => [ 'addon\shop\app\listener\order\AfterShopOrderPay' ],
        //订单发货后
        'AfterShopOrderDelivery' => [ 'addon\shop\app\listener\order\AfterShopOrderDelivery' ],
        //订单收货后
        'AfterShopOrderFinish' => [ 'addon\shop\app\listener\order\AfterShopOrderFinish' ],
        //订单编辑价格后
        'AfterShopOrderEditPrice' => [ 'addon\shop\app\listener\order\AfterShopOrderEditPrice' ],
        //订单关闭后
        'AfterShopOrderClose' => [
            'addon\shop\app\listener\order\AfterShopOrderClose',
            'addon\shop\app\listener\point_exchange\AfterShopOrderClose',   //积分商城业务
        ],
        //活动信息
        'ShopGoodsMarketCalculate' => [
            'addon\shop\app\listener\marketing\ShopNewcomerCalculate'   //新人专享
        ],
        /***************************************************** 退款 start *****************************************************/
        'AfterShopOrderRefundApply' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundApply' ],
        'AfterShopOrderRefundAuditApply' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundAuditApply' ],
        'AfterShopOrderRefundAuditRefundGoods' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundAuditRefundGoods' ],
        'AfterShopOrderRefundClose' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundClose' ],
        'AfterShopOrderRefundDelivery' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundDelivery' ],
        'AfterShopOrderRefundEdit' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundEdit' ],
        'AfterShopOrderRefundEditDelivery' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundEditDelivery' ],
        'AfterShopOrderRefundFinish' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundFinish' ],
        'AfterShopOrderRefundActiveCreate' => [ 'addon\shop\app\listener\refund\AfterShopOrderRefundActiveCreate' ],
        /***************************************************** 退款 end *****************************************************/

        'ShopPromotion' => [ 'addon\shop\app\listener\app\ShopPromotionListener' ],
        'WapIndex' => [ 'addon\shop\app\listener\WapIndexListener' ],
        'BottomNavigation' => [ 'addon\shop\app\listener\BottomNavigationListener' ],

        //支付
        'PayCreate' => [ 'addon\shop\app\listener\pay\PayCreateListener' ],
        'PaySuccess' => [ 'addon\shop\app\listener\pay\PaySuccessListener' ],
        'PayTradeInfo' => [ 'addon\shop\app\listener\order\ShopOrderTradeInfoListener' ],   //订单交易信息
        'RefundSuccess' => [ 'addon\shop\app\listener\pay\RefundSuccessListener' ],

        'NoticeData' => [
            'addon\shop\app\listener\notice_template\OrderPay',
            'addon\shop\app\listener\notice_template\OrderPayRemind',
            'addon\shop\app\listener\notice_template\OrderDelivery',
            'addon\shop\app\listener\notice_template\RefundAgree',
            'addon\shop\app\listener\notice_template\RefundRefuse',
        ],
        //优惠券
        'CouponReceiveType' => [ 'addon\shop\app\listener\coupon\CouponReceiveListener' ],
        'CouponCheck' => [ 'addon\shop\app\listener\coupon\CouponCheckListener' ],

        //获取海报数据
        'GetPosterType' => [ 'addon\shop\app\listener\poster\ShopPosterType' ],
        'GetPosterData' => [ 'addon\shop\app\listener\poster\ShopPoster' ],

        //导出数据类型
        'ExportDataType' => [
            //订单列表导出
            'addon\shop\app\listener\order_export\ShopOrderExportTypeListener',
            //订单项导出
            'addon\shop\app\listener\order_export\ShopOrderGoodsExportTypeListener',
            //退款售后导出
            'addon\shop\app\listener\refund_export\ShopOrderRefundExportTypeListener',
            //发票列表导出
            'addon\shop\app\listener\order_export\ShopInvoiceExportTypeListener',
        ],
        //导出数据源
        'ExportData' => [
            //订单列表导出
            'addon\shop\app\listener\order_export\ShopOrderExportDataListener',
            //订单项导出
            'addon\shop\app\listener\order_export\ShopOrderGoodsExportDataListener',
            //退款售后导出
            'addon\shop\app\listener\refund_export\ShopOrderRefundExportDataListener',
            //发票列表导出
            'addon\shop\app\listener\order_export\ShopInvoiceExportDataListener',
        ],
        //商城统计执行
        'StatExecute' => [ 'addon\shop\app\listener\stat\StatExecuteListener' ],
        //商城统计字段
        'StatField' => [ 'addon\shop\app\listener\stat\StatFieldListener' ],
        //核销
        'VerifyType' => [ 'addon\shop\app\listener\verify\VerifyTypeListener' ],
        'VerifyCreate' => [ 'addon\shop\app\listener\verify\VerifyCreateListener' ],
        'Verify' => [ 'addon\shop\app\listener\verify\VerifyListener' ],
        'VerifyInfo' => [ 'addon\shop\app\listener\verify\VerifyInfoListener' ],
        'VerifyCheck' => ['addon\shop\app\listener\verify\VerifyCheckListener'],

        'GetGoodsJoinInfo' => [
            'addon\shop\app\listener\marketing\GetGoodsJoinInfo'
        ],
        'ActiveSaveAfter' => [
            'addon\shop\app\listener\marketing\ShopActiveSaveAfter'
        ],
        //通过支付信息获取手机端订单详情路径
        'WapOrderDetailPath' => [
            'addon\shop\app\listener\order\WapOrderDetailPathListener',
        ],

        'PrinterContent' => [
            'addon\shop\app\listener\printer\PrinterContentListener'
        ],

        //新人专享
        'NewcomerActiveJoin' => [ 'addon\shop\app\listener\marketing\NewcomerActiveJoinListener' ],
        //会员登录后事件
        'MemberLoginAfter' => [ 'addon\shop\app\listener\MemberLoginAfterListener' ],

        // 会员充值 赠送内容
        'RechargeGiftContent' => [ 'addon\shop\app\listener\recharge\GiftContentListener' ],
        // 会员充值成功后的事件
        'RechargeAfterListener' => [ 'addon\shop\app\listener\recharge\RechargeAfterListener' ],

        //获取宝贝数据
        'TreasureType' => [ 'addon\shop\app\listener\treasure\TreasureTypeListener' ],
        'TreasureData' => [ 'addon\shop\app\listener\treasure\TreasureDataListener' ],

        //主题色
        'ThemeColor' => [ 'addon\shop\app\listener\diy\ThemeColorListener' ],
        //万能表单删除前
        'BeforeFormDelete' => [ 'addon\shop\app\listener\diy\BeforeFormDeleteListener' ],
        //查询营销列表
        'ShowMarketing' => [
            'addon\shop\app\listener\system\ShowMarketingListener'
        ],
        //获取商品展示价格
        'GoodsShowPrice' => [ 'addon\shop\app\listener\goods\GoodsShowPriceListener' ]

    ],
    'subscribe' => [
    ],
];
