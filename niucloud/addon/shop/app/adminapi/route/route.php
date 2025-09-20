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

use app\adminapi\middleware\AdminCheckRole;
use app\adminapi\middleware\AdminCheckToken;
use app\adminapi\middleware\AdminLog;
use think\facade\Route;

// 第三方配送
Route::any('delivery/third/callback/:type/:order_no', 'addon\shop\app\adminapi\controller\delivery\Local@thirdDeliveryCallback');

/**
 * 商城系统
 */
Route::group('shop', function () {

    /************************************************** 配送相关接口 *****************************************************/
    //物流公司 分页列表
    Route::get('delivery/company', 'addon\shop\app\adminapi\controller\delivery\Company@pages');

    //物流公司 列表
    Route::get('delivery/company/list', 'addon\shop\app\adminapi\controller\delivery\Company@lists');

    //物流公司 详情
    Route::get('delivery/company/:id', 'addon\shop\app\adminapi\controller\delivery\Company@info');

    //物流公司 添加
    Route::post('delivery/company', 'addon\shop\app\adminapi\controller\delivery\Company@add');

    //物流公司 编辑
    Route::put('delivery/company/:id', 'addon\shop\app\adminapi\controller\delivery\Company@edit');

    //物流公司 删除
    Route::delete('delivery/company/:id', 'addon\shop\app\adminapi\controller\delivery\Company@del');

    //物流查询接口 设置
    Route::post('delivery/search', 'addon\shop\app\adminapi\controller\delivery\DeliverySearch@setConfig');

    //物流跟踪接口 查询
    Route::get('delivery/search', 'addon\shop\app\adminapi\controller\delivery\DeliverySearch@getConfig');

    //运费模版 分页列表
    Route::get('shipping/template', 'addon\shop\app\adminapi\controller\delivery\ShippingTemplate@pages');

    //运费模版 列表
    Route::get('shipping/template/list', 'addon\shop\app\adminapi\controller\delivery\ShippingTemplate@lists');

    //运费模版 详情
    Route::get('shipping/template/:template_id', 'addon\shop\app\adminapi\controller\delivery\ShippingTemplate@info');

    //运费模版 添加
    Route::post('shipping/template', 'addon\shop\app\adminapi\controller\delivery\ShippingTemplate@add');

    //运费模版 编辑
    Route::put('shipping/template/:template_id', 'addon\shop\app\adminapi\controller\delivery\ShippingTemplate@edit');

    //运费模版 删除
    Route::delete('shipping/template/:template_id', 'addon\shop\app\adminapi\controller\delivery\ShippingTemplate@del');

    //自提门店列表（分页）
    Route::get('delivery/store', 'addon\shop\app\adminapi\controller\delivery\Store@lists');

    Route::get('delivery/store/init', 'addon\shop\app\adminapi\controller\delivery\Store@init');

    //自提门店列表（不分页）
    Route::get('delivery/store/list', 'addon\shop\app\adminapi\controller\delivery\Store@getList');

    //自提门店详情
    Route::get('delivery/store/:id', 'addon\shop\app\adminapi\controller\delivery\Store@info');

    //添加自提门店
    Route::post('delivery/store', 'addon\shop\app\adminapi\controller\delivery\Store@add');

    //编辑自提门店
    Route::put('delivery/store/:id', 'addon\shop\app\adminapi\controller\delivery\Store@edit');

    //删除自提门店
    Route::delete('delivery/store/:id', 'addon\shop\app\adminapi\controller\delivery\Store@del');

    //物流配置
    Route::get('delivery/deliveryList', 'addon\shop\app\adminapi\controller\delivery\Delivery@getDeliveryList');
    Route::put('delivery/setConfig', 'addon\shop\app\adminapi\controller\delivery\Delivery@setDeliveryConfig');

    //配送员列表
    Route::get('delivery/staff', 'addon\shop\app\adminapi\controller\delivery\Delivery@lists');

    //配送员详情
    Route::get('delivery/staff/:id', 'addon\shop\app\adminapi\controller\delivery\Delivery@info');

    //添加配送员
    Route::post('delivery/staff', 'addon\shop\app\adminapi\controller\delivery\Delivery@add');

    //编辑配送员
    Route::put('delivery/staff/:id', 'addon\shop\app\adminapi\controller\delivery\Delivery@edit');

    //删除配送员
    Route::delete('delivery/staff/:id', 'addon\shop\app\adminapi\controller\delivery\Delivery@del');

    // 获取同城配送设置
    Route::get('local', 'addon\shop\app\adminapi\controller\delivery\Local@getLocal');

    // 设置同城配送
    Route::put('local', 'addon\shop\app\adminapi\controller\delivery\Local@setLocal');
    Route::get('third/init', 'addon\shop\app\adminapi\controller\delivery\Local@getThirdPartyInit');

    /************************************************** 接口管理 *******************************************************/

    // 电子面单 分页列表
    Route::get('electronic_sheet', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@pages');

    // 电子面单 列表
    Route::get('electronic_sheet/list', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@lists');

    // 电子面单 详情
    Route::get('electronic_sheet/:id', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@info');

    // 电子面单 添加
    Route::post('electronic_sheet', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@add');

    // 电子面单 编辑
    Route::put('electronic_sheet/:id', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@edit');

    // 电子面单 删除
    Route::delete('electronic_sheet/:id', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@del');

    // 电子面单 设为默认模板
    Route::put('electronic_sheet/setDefault/:id', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@setDefault');

    // 电子面单 获取设置
    Route::get('electronic_sheet/config', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@getConfig');

    // 电子面单 设置
    Route::post('electronic_sheet/config', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@setConfig');

    // 电子面单 获取邮费支付方式类型
    Route::get('electronic_sheet/paytype', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@getPayType');

    // 电子面单 打印
    Route::post('electronic_sheet/print', 'addon\shop\app\adminapi\controller\delivery\ElectronicSheet@printElectronicSheet');

    //商品分页列表
    Route::get('goods', 'addon\shop\app\adminapi\controller\goods\Goods@pages');

    //商品详情
    Route::get('goods/:id', 'addon\shop\app\adminapi\controller\goods\Goods@info');

    //添加实物商品
    Route::post('goods', 'addon\shop\app\adminapi\controller\goods\Goods@add');

    //编辑实物商品
    Route::put('goods/:id', 'addon\shop\app\adminapi\controller\goods\Goods@edit');

    // 商品添加/编辑数据
    Route::get('goods/init', 'addon\shop\app\adminapi\controller\goods\Goods@init');

    //添加虚拟商品
    Route::post('goods/virtual', 'addon\shop\app\adminapi\controller\goods\VirtualGoods@add');

    //编辑虚拟商品
    Route::put('goods/virtual/:id', 'addon\shop\app\adminapi\controller\goods\VirtualGoods@edit');

    // 商品添加/编辑数据
    Route::get('goods/virtual/init', 'addon\shop\app\adminapi\controller\goods\VirtualGoods@init');

    //删除商品
    Route::put('goods/delete', 'addon\shop\app\adminapi\controller\goods\Goods@del');

    // 回收站商品分页列表
    Route::get('goods/recycle', 'addon\shop\app\adminapi\controller\goods\Goods@recyclePages');

    //商品恢复
    Route::put('goods/recycle', 'addon\shop\app\adminapi\controller\goods\Goods@recycle');

    // 修改商品排序号
    Route::put('goods/sort', 'addon\shop\app\adminapi\controller\goods\Goods@editSort');

    // 修改商品上下架状态
    Route::put('goods/status', 'addon\shop\app\adminapi\controller\goods\Goods@editStatus');

    // 修改商品上下架状态（单商品）
    Route::put('goods/single/status', 'addon\shop\app\adminapi\controller\goods\Goods@editSingleStatus');

    // 复制商品
    Route::put('goods/copy/:goods_id', 'addon\shop\app\adminapi\controller\goods\Goods@copy');

    // 获取商品选择分页列表
    Route::get('goods/select', 'addon\shop\app\adminapi\controller\goods\Goods@select');

    // 获取商品选择分页列表带sku
    Route::get('goods/selectgoodssku', 'addon\shop\app\adminapi\controller\goods\Goods@selectGoodsSku');

    // 获取商品SKU规格列表
    Route::get('goods/sku', 'addon\shop\app\adminapi\controller\goods\Goods@sku');

    // 编辑商品规格列表库存
    Route::put('goods/sku/stock', 'addon\shop\app\adminapi\controller\goods\Goods@editGoodsListStock');

    // 编辑商品规格列表价格
    Route::put('goods/sku/price', 'addon\shop\app\adminapi\controller\goods\Goods@editGoodsListPrice');

    // 编辑商品规格列表会员价格
    Route::put('goods/sku/member_price', 'addon\shop\app\adminapi\controller\goods\Goods@editGoodsListMemberPrice');

    // 获取商品SKU规格列表
    Route::get('goods/active/count', 'addon\shop\app\adminapi\controller\goods\Goods@getActiveGoodsCount');

    // 获取商品类型
    Route::get('goods/type', 'addon\shop\app\adminapi\controller\goods\Goods@type');

    //商品标签分页列表
    Route::get('goods/label', 'addon\shop\app\adminapi\controller\goods\Label@pages');

    //商品标签列表
    Route::get('goods/label/list', 'addon\shop\app\adminapi\controller\goods\Label@lists');

    //商品标签详情
    Route::get('goods/label/:id', 'addon\shop\app\adminapi\controller\goods\Label@info');

    //添加商品标签
    Route::post('goods/label', 'addon\shop\app\adminapi\controller\goods\Label@add');

    //编辑商品标签
    Route::put('goods/label/:id', 'addon\shop\app\adminapi\controller\goods\Label@edit');

    //复制商品标签
    Route::post('goods/label/copy/:id', 'addon\shop\app\adminapi\controller\goods\Label@copy');

    //删除商品标签
    Route::delete('goods/label/:id', 'addon\shop\app\adminapi\controller\goods\Label@del');

    // 修改商品标签排序号
    Route::put('goods/label/sort', 'addon\shop\app\adminapi\controller\goods\Label@modifySort');

    // 修改商品标签排序号
    Route::put('goods/label/status', 'addon\shop\app\adminapi\controller\goods\Label@modifyStatus');

    //商品标签分组分页列表
    Route::get('goods/label/group', 'addon\shop\app\adminapi\controller\goods\LabelGroup@pages');

    //商品标签分组列表
    Route::get('goods/label/group/list', 'addon\shop\app\adminapi\controller\goods\LabelGroup@lists');

    //商品标签分组详情
    Route::get('goods/label/group/:id', 'addon\shop\app\adminapi\controller\goods\LabelGroup@info');

    //添加商品标签分组
    Route::post('goods/label/group', 'addon\shop\app\adminapi\controller\goods\LabelGroup@add');

    //编辑商品标签分组
    Route::put('goods/label/group/:id', 'addon\shop\app\adminapi\controller\goods\LabelGroup@edit');

    //删除商品标签分组
    Route::delete('goods/label/group/:id', 'addon\shop\app\adminapi\controller\goods\LabelGroup@del');

    // 修改商品标签分组排序号
    Route::put('goods/label/group/sort', 'addon\shop\app\adminapi\controller\goods\LabelGroup@modifySort');

    //商品品牌分页列表
    Route::get('goods/brand', 'addon\shop\app\adminapi\controller\goods\Brand@pages');

    //商品品牌列表
    Route::get('goods/brand/list', 'addon\shop\app\adminapi\controller\goods\Brand@lists');

    //商品品牌详情
    Route::get('goods/brand/:id', 'addon\shop\app\adminapi\controller\goods\Brand@info');

    //添加商品品牌
    Route::post('goods/brand', 'addon\shop\app\adminapi\controller\goods\Brand@add');

    //编辑商品品牌
    Route::put('goods/brand/:id', 'addon\shop\app\adminapi\controller\goods\Brand@edit');

    //删除商品品牌
    Route::delete('goods/brand/:id', 'addon\shop\app\adminapi\controller\goods\Brand@del');

    // 修改商品品牌排序号
    Route::put('goods/brand/sort', 'addon\shop\app\adminapi\controller\goods\Brand@modifySort');

    //商品服务分页列表
    Route::get('goods/service', 'addon\shop\app\adminapi\controller\goods\Service@pages');

    //商品服务列表
    Route::get('goods/service/list', 'addon\shop\app\adminapi\controller\goods\Service@lists');

    //商品服务详情
    Route::get('goods/service/:id', 'addon\shop\app\adminapi\controller\goods\Service@info');

    //添加商品服务
    Route::post('goods/service', 'addon\shop\app\adminapi\controller\goods\Service@add');

    //编辑商品服务
    Route::put('goods/service/:id', 'addon\shop\app\adminapi\controller\goods\Service@edit');

    //删除商品服务
    Route::delete('goods/service/:id', 'addon\shop\app\adminapi\controller\goods\Service@del');

    //商品分类列表树结构
    Route::get('goods/tree', 'addon\shop\app\adminapi\controller\goods\Category@tree');

    Route::get('goods/category', 'addon\shop\app\adminapi\controller\goods\Category@lists');

    //商品分类详情
    Route::get('goods/category/:id', 'addon\shop\app\adminapi\controller\goods\Category@info');

    //添加商品分类
    Route::post('goods/category', 'addon\shop\app\adminapi\controller\goods\Category@add');

    //编辑商品分类
    Route::put('goods/category/:id', 'addon\shop\app\adminapi\controller\goods\Category@edit');

    //删除商品分类
    Route::delete('goods/category/:id', 'addon\shop\app\adminapi\controller\goods\Category@del');

    //编辑商品分类
    Route::post('goods/category/update', 'addon\shop\app\adminapi\controller\goods\Category@editCategory');

    // 获取商品分类配置
    Route::post('goods/category/config', 'addon\shop\app\adminapi\controller\goods\Category@setGoodsCategoryConfig');

    // 获取商品分类配置
    Route::get('goods/category/config', 'addon\shop\app\adminapi\controller\goods\Category@getGoodsCategoryConfig');

    // 获取商品分类树结构供弹框调用
    Route::get('goods/category/components', 'addon\shop\app\adminapi\controller\goods\Category@components');

    // 商品参数分页列表
    Route::get('goods/attr', 'addon\shop\app\adminapi\controller\goods\Attr@pages');

    // 商品参数列表
    Route::get('goods/attr/list', 'addon\shop\app\adminapi\controller\goods\Attr@lists');

    // 商品参数详情
    Route::get('goods/attr/:id', 'addon\shop\app\adminapi\controller\goods\Attr@info');

    // 添加商品参数
    Route::post('goods/attr', 'addon\shop\app\adminapi\controller\goods\Attr@add');

    // 编辑商品参数
    Route::put('goods/attr/:id', 'addon\shop\app\adminapi\controller\goods\Attr@edit');

    // 删除商品参数
    Route::delete('goods/attr/:id', 'addon\shop\app\adminapi\controller\goods\Attr@del');

    // 修改商品参数排序号
    Route::put('goods/attr/sort', 'addon\shop\app\adminapi\controller\goods\Attr@modifySort');

    // 修改商品参数名称
    Route::put('goods/attr/attr_name', 'addon\shop\app\adminapi\controller\goods\Attr@modifyAttrName');

    // 修改商品参数值
    Route::put('goods/attr/attr_value', 'addon\shop\app\adminapi\controller\goods\Attr@modifyAttrValueFormat');

    // 获取商品下单选择分页列表
    Route::get('goods/buy/goods/select', 'addon\shop\app\adminapi\controller\goods\Goods@buyGoodsSelect');

    // 获取商品下单已选分页列表
    Route::get('goods/buy/goods/selected', 'addon\shop\app\adminapi\controller\goods\Goods@buyGoodsSelected');

    // 获取商品下单SKU规格列表
    Route::get('goods/buy/sku/select', 'addon\shop\app\adminapi\controller\goods\Goods@buySkuSelect');

    // 批量设置商品
    Route::put('goods/batchSet', 'addon\shop\app\adminapi\controller\goods\Goods@batchSet');

    //获取商品排行榜统计类型
    Route::get('goods/batchSet/dict', 'addon\shop\app\adminapi\controller\goods\Goods@getBatchSetDict');

    /************************************************** 订单相关接口 *****************************************************/
    //交易配置
    Route::post('order/config', 'addon\shop\app\adminapi\controller\order\Config@setConfig');
    Route::get('order/config', 'addon\shop\app\adminapi\controller\order\Config@getConfig');

    //订单列表
    Route::get('order/list', 'addon\shop\app\adminapi\controller\order\Order@lists');

    //订单详情
    Route::get('order/detail/:id', 'addon\shop\app\adminapi\controller\order\Order@detail');

    //订单删除
    Route::post('order/delete', 'addon\shop\app\adminapi\controller\order\Order@delete');

    //获取 订单类型
    Route::get('order/type', 'addon\shop\app\adminapi\controller\order\Order@getOrderType');

    //获取 订单状态
    Route::get('order/status', 'addon\shop\app\adminapi\controller\order\Order@getOrderStatus');

    //订单关闭
    Route::put('order/close/:id', 'addon\shop\app\adminapi\controller\order\Order@orderClose');

    //订单改价
    Route::put('order/edit_price', 'addon\shop\app\adminapi\controller\order\Order@editPrice');

    //订单配送修改
    Route::put('order/edit_delivery', 'addon\shop\app\adminapi\controller\order\Order@editDelivery');

    //订单配送修改信息
    Route::get('order/edit_delivery', 'addon\shop\app\adminapi\controller\order\Order@editDeliveryData');

    //订单发货
    Route::put('order/delivery', 'addon\shop\app\adminapi\controller\order\Order@orderDelivery');

    //订单项发货
    Route::put('order/goods/delivery/:id', 'addon\shop\app\adminapi\controller\order\Order@orderDelivery');

    //获取订单配送方式
    Route::get('order/delivery_type', 'addon\shop\app\adminapi\controller\order\Order@getDeliveryType');

    //商家留言
    Route::put('order/shop_remark', 'addon\shop\app\adminapi\controller\order\Order@setShopRemark');

    //订单完成
    Route::put('order/finish/:id', 'addon\shop\app\adminapi\controller\order\Order@orderFinish');

    //获取 物流包裹信息（跟踪信息）
    Route::get('order/delivery/package', 'addon\shop\app\adminapi\controller\order\Order@getOrderPackage');

    //获取 物流包裹列表
    Route::get('order/delivery/package/list', 'addon\shop\app\adminapi\controller\order\Order@getDeliveryPackageList');

    //获取 支付类型
    Route::get('order/pay/type', 'addon\shop\app\adminapi\controller\order\Order@getPayType');

    //获取 订单来源
    Route::get('order/from', 'addon\shop\app\adminapi\controller\order\Order@getOrderFrom');

    //订单售后 列表
    Route::get('order/refund', 'addon\shop\app\adminapi\controller\refund\Refund@lists');

    //订单售后 详情
    Route::get('order/refund/:id', 'addon\shop\app\adminapi\controller\refund\Refund@detail');

    //订单售后审核
    Route::put('order/refund/audit/:order_refund_no', 'addon\shop\app\adminapi\controller\refund\Refund@auditApply');

    //订单售后审核
    Route::put('order/refund/delivery/:order_refund_no', 'addon\shop\app\adminapi\controller\refund\Refund@auditRefundGoods');

    //订单售后 可退款金额
    Route::get('order/refund/refund_money', 'addon\shop\app\adminapi\controller\refund\Refund@getOrderRefundMoney');

    //订单售后 商家主动退款
    Route::post('order/refund/active', 'addon\shop\app\adminapi\controller\refund\Refund@shopActiveRefund');
    //关闭售后
    Route::put('order/refund/close/:order_refund_no', 'addon\shop\app\adminapi\controller\refund\Refund@closeRefund');

    /************************************************** 订单发货批量操作相关接口 *****************************************************/

    //订单批量操作 列表
    Route::get('order_batch_delivery', 'addon\shop\app\adminapi\controller\order\Order@getOrderBatchDeliveryPage');

    //订单批量操作 详情
    Route::get('order_batch_delivery/:id', 'addon\shop\app\adminapi\controller\order\Order@getOrderBatchDeliveryInfo');

    //批量发货
    Route::put('order_batch_delivery/add_batch_order_delivery', 'addon\shop\app\adminapi\controller\order\Order@addBatchOrderDelivery');

    //订单批量操作类型
    Route::get('order_batch_delivery/get_type', 'addon\shop\app\adminapi\controller\order\Order@getBatchType');

    //订单批量操作状态
    Route::get('order_batch_delivery/get_status', 'addon\shop\app\adminapi\controller\order\Order@getBatchStatus');

    //营销中心
    Route::get('marketing', 'addon\shop\app\adminapi\controller\marketing\Index@index');

    /************************************************** 优惠券相关接口 *****************************************************/
    //优惠券列表
    Route::get('goods/coupon', 'addon\shop\app\adminapi\controller\marketing\Coupon@lists');

    //优惠券初始化信息
    Route::get('goods/coupon/init', 'addon\shop\app\adminapi\controller\marketing\Coupon@init');

    //添加优惠券
    Route::post('goods/coupon', 'addon\shop\app\adminapi\controller\marketing\Coupon@add');

    //优惠券领取记录
    Route::get('goods/coupon/records', 'addon\shop\app\adminapi\controller\marketing\Coupon@getMemberCoupon');

    //优惠券详情
    Route::get('goods/coupon/detail/:id', 'addon\shop\app\adminapi\controller\marketing\Coupon@info');

    //编辑优惠券
    Route::put('goods/coupon/edit/:id', 'addon\shop\app\adminapi\controller\marketing\Coupon@edit');

    //删除优惠券基于有批量删除
    Route::post('goods/coupon/delete', 'addon\shop\app\adminapi\controller\marketing\Coupon@del');

    //优惠券设置状态
    Route::put('goods/coupon/setstatus/:status', 'addon\shop\app\adminapi\controller\marketing\Coupon@setCouponStatus');

    //优惠券失效
    Route::put('goods/coupon/invalid', 'addon\shop\app\adminapi\controller\marketing\Coupon@couponInvalid');

    //删除优惠券
    Route::delete('goods/coupon/:id', 'addon\shop\app\adminapi\controller\marketing\Coupon@del');

    //查询优惠券选择分页列表
    Route::get('goods/coupon/select', 'addon\shop\app\adminapi\controller\marketing\Coupon@select');

    //查询选中的优惠券
    Route::get('goods/coupon/selected', 'addon\shop\app\adminapi\controller\marketing\Coupon@getSelectedLists');

    //优惠券状态列表
    Route::get('goods/coupon/status', 'addon\shop\app\adminapi\controller\marketing\Coupon@getCouponStatus');

    //发送优惠券范围列表
    Route::get('goods/coupon/send/init', 'addon\shop\app\adminapi\controller\marketing\Coupon@getSendRangeInit');
    Route::get('goods/coupon/send/pages/:coupon_id', 'addon\shop\app\adminapi\controller\marketing\Coupon@getSendPages');
    Route::post('goods/coupon/send/:coupon_id', 'addon\shop\app\adminapi\controller\marketing\Coupon@sendCoupon');

    //商家地址库列表
    Route::get('shop_address', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@lists');

    //商家地址库详情
    Route::get('shop_address/:id', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@info');

    //添加商家地址库
    Route::post('shop_address', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@add');

    //编辑商家地址库
    Route::put('shop_address/:id', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@edit');

    //删除商家地址库
    Route::delete('shop_address/:id', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@del');

    // 默认发货地址
    Route::get('shop_address/default/delivery', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@defaultDelivery');

    //获取商家收货地址库
    Route::get('order/refund/address', 'addon\shop\app\adminapi\controller\shop_address\ShopAddress@getList');

    //商品评价 列表
    Route::get('goods/evaluate', 'addon\shop\app\adminapi\controller\goods\Evaluate@lists');

    //商品评价 添加
    Route::post('goods/evaluate', 'addon\shop\app\adminapi\controller\goods\Evaluate@add');

    //商品评价 删除
    Route::delete('goods/evaluate/:id', 'addon\shop\app\adminapi\controller\goods\Evaluate@del');

    //商品评价 回复
    Route::put('goods/evaluate/reply/:id', 'addon\shop\app\adminapi\controller\goods\Evaluate@evaluateReply');

    //商品评价 通过
    Route::put('goods/evaluate/adopt/:id', 'addon\shop\app\adminapi\controller\goods\Evaluate@adopt');

    //商品评价 拒绝
    Route::put('goods/evaluate/refuse/:id', 'addon\shop\app\adminapi\controller\goods\Evaluate@refuse');

    //商品评价 置顶
    Route::put('goods/evaluate/topping/:id', 'addon\shop\app\adminapi\controller\goods\Evaluate@topping');

    //获取商品评价审核状态
    Route::get('goods/evaluate/status', 'addon\shop\app\adminapi\controller\goods\Evaluate@status');

    //批量通过
    Route::post('goods/evaluate/batch/adopt', 'addon\shop\app\adminapi\controller\goods\Evaluate@batchAdopt');

    //批量拒绝
    Route::post('goods/evaluate/batch/refuse', 'addon\shop\app\adminapi\controller\goods\Evaluate@batchRefuse');

    //批量删除
    Route::post('goods/evaluate/batch/del', 'addon\shop\app\adminapi\controller\goods\Evaluate@batchDel');

    //商品评价 取消置顶
    Route::put('goods/evaluate/cancel_topping/:id', 'addon\shop\app\adminapi\controller\goods\Evaluate@cancelTopping');
    //校验商品编码
    Route::post('goods/verify/skuno', 'addon\shop\app\adminapi\controller\goods\Goods@verifySkuNo');

    //商品搜索配置
    Route::get('goods/config/search', 'addon\shop\app\adminapi\controller\goods\Config@getSearchConfig');
    Route::post('goods/config/search', 'addon\shop\app\adminapi\controller\goods\Config@setSearchConfig');

    //商品编码配置
    Route::get('goods/config/unique', 'addon\shop\app\adminapi\controller\goods\Config@getUniqueConfig');
    Route::post('goods/config/unique', 'addon\shop\app\adminapi\controller\goods\Config@setUniqueConfig');

    //商品排序配置
    Route::get('goods/config/sort', 'addon\shop\app\adminapi\controller\goods\Config@getSortConfig');
    Route::post('goods/config/sort', 'addon\shop\app\adminapi\controller\goods\Config@setSortConfig');


    Route::get('stat/total', 'addon\shop\app\adminapi\controller\Stat@total');
    Route::get('stat/today', 'addon\shop\app\adminapi\controller\Stat@today');
    Route::get('stat/yesterday', 'addon\shop\app\adminapi\controller\Stat@yesterday');
    Route::get('stat', 'addon\shop\app\adminapi\controller\Stat@stat');
    Route::get('stat/order', 'addon\shop\app\adminapi\controller\Stat@order');
    Route::get('stat/goods', 'addon\shop\app\adminapi\controller\Stat@goods');

    // 发票列表
    Route::get('invoice', 'addon\shop\app\adminapi\controller\order\Invoice@lists');

    // 发票信息
    Route::get('invoice/:id', 'addon\shop\app\adminapi\controller\order\Invoice@info');

    // 开票
    Route::put('invoice/:id', 'addon\shop\app\adminapi\controller\order\Invoice@invoicing');

    /************************************************** 限时折扣 *****************************************************/
    //限时折扣列表
    Route::get('active/discount', 'addon\shop\app\adminapi\controller\marketing\Discount@lists');

    //添加
    Route::post('active/discount', 'addon\shop\app\adminapi\controller\marketing\Discount@add');

    //编辑
    Route::put('active/discount/:discount_id', 'addon\shop\app\adminapi\controller\marketing\Discount@edit');

    //限时折扣商品校验
    Route::post('active/discount/goods/check', 'addon\shop\app\adminapi\controller\marketing\Discount@checkGoods');

    //删除
    Route::delete('active/discount/:discount_id', 'addon\shop\app\adminapi\controller\marketing\Discount@del');

    //获取限时折扣状态列表
    Route::get('active/discount/status', 'addon\shop\app\adminapi\controller\marketing\Discount@discountStatus');

    //关闭
    Route::put('active/discount/close/:discount_id', 'addon\shop\app\adminapi\controller\marketing\Discount@close');

    //批量删除
    Route::post('active/discount/batchDelete', 'addon\shop\app\adminapi\controller\marketing\Discount@batchDelete');

    //批量关闭
    Route::post('active/discount/batchClose', 'addon\shop\app\adminapi\controller\marketing\Discount@batchClose');

    //详情-基础信息
    Route::get('active/discount/info/:discount_id', 'addon\shop\app\adminapi\controller\marketing\Discount@info');

    //详情
    Route::get('active/discount/:discount_id', 'addon\shop\app\adminapi\controller\marketing\Discount@detail');

    //状态
    Route::get('active/status', 'addon\shop\app\adminapi\controller\marketing\Discount@status');

    //参与订单
    Route::get('active/discount/order/:active_id', 'addon\shop\app\adminapi\controller\marketing\Discount@order');

    //参与会员
    Route::get('active/discount/member/:active_id', 'addon\shop\app\adminapi\controller\marketing\Discount@member');

    //参与商品
    Route::get('active/discount/goods/:active_id', 'addon\shop\app\adminapi\controller\marketing\Discount@goods');

    //获取配置
    Route::get('active/discount/config', 'addon\shop\app\adminapi\controller\marketing\Discount@banner');

    //设置配置
    Route::put('active/discount/config', 'addon\shop\app\adminapi\controller\marketing\Discount@setBanner');


    /************************************************** 积分商城 *****************************************************/
    //积分商城列表
    Route::get('active/exchange', 'addon\shop\app\adminapi\controller\marketing\Exchange@lists');

    //积分商城分页列表（用于弹框选择）
    Route::get('active/exchange/select', 'addon\shop\app\adminapi\controller\marketing\Exchange@select');

    //商品类型
    Route::get('active/exchange/type', 'addon\shop\app\adminapi\controller\marketing\Exchange@type');

    //商品类型
    Route::get('active/exchange/status', 'addon\shop\app\adminapi\controller\marketing\Exchange@status');

    //添加积分商城
    Route::post('active/exchange', 'addon\shop\app\adminapi\controller\marketing\Exchange@add');

    //积分商城详情
    Route::get('active/exchange/:id', 'addon\shop\app\adminapi\controller\marketing\Exchange@detail');

    //编辑积分商城
    Route::put('active/exchange/:id', 'addon\shop\app\adminapi\controller\marketing\Exchange@edit');

    //修改积分商城上下架状态
    Route::put('active/exchange/status/:id', 'addon\shop\app\adminapi\controller\marketing\Exchange@editStatus');

    //删除
    Route::delete('active/exchange/:id', 'addon\shop\app\adminapi\controller\marketing\Exchange@del');

    //批量删除
    Route::post('active/exchange/batchDelete', 'addon\shop\app\adminapi\controller\marketing\Exchange@batchDelete');

    //批量下架
    Route::post('active/exchange/batchDown', 'addon\shop\app\adminapi\controller\marketing\Exchange@batchDown');

    //批量上架
    Route::post('active/exchange/batchUp', 'addon\shop\app\adminapi\controller\marketing\Exchange@batchUp');

    //修改排序号
    Route::put('active/exchange/sort/:id', 'addon\shop\app\adminapi\controller\marketing\Exchange@modifySort');

    /************************************************** 新人专享 *****************************************************/
    //新人专享配置
    Route::get('active/newcomer/config', 'addon\shop\app\adminapi\controller\marketing\Newcomer@getConfig');

    //新人专享设置
    Route::put('active/newcomer/config', 'addon\shop\app\adminapi\controller\marketing\Newcomer@setConfig');

    //新人专享商品选择列表
    Route::get('active/newcomer/goods/select', 'addon\shop\app\adminapi\controller\marketing\Newcomer@select');

    //新人专享商品选择已选商品列表
    Route::get('active/newcomer/goods/selectgoodssku', 'addon\shop\app\adminapi\controller\marketing\Newcomer@selectGoodsSku');

    /************************************************** 商品排行榜 *****************************************************/

    // 排行榜配置
    Route::post('good/rank/config', 'addon\shop\app\adminapi\controller\goods\Rank@setRankConfig');

    Route::get('good/rank/config', 'addon\shop\app\adminapi\controller\goods\Rank@getRankConfig');

    // 排行榜分页列表
    Route::get('good/rank', 'addon\shop\app\adminapi\controller\goods\Rank@pages');

    Route::post('good/rank', 'addon\shop\app\adminapi\controller\goods\Rank@add');

    Route::put('good/rank/:id', 'addon\shop\app\adminapi\controller\goods\Rank@edit');

    Route::get('good/rank/:id', 'addon\shop\app\adminapi\controller\goods\Rank@info');

    Route::get('good/rank/dict', 'addon\shop\app\adminapi\controller\goods\Rank@getOptionData');

    Route::delete('good/rank/:id', 'addon\shop\app\adminapi\controller\goods\Rank@del');

    //排行榜修改排序
    Route::put('good/rank/sort', 'addon\shop\app\adminapi\controller\goods\Rank@editSort');

    //排行榜批量删除
    Route::put('good/rank/batchDelete', 'addon\shop\app\adminapi\controller\goods\Rank@batchDelete');

    //获取排行榜分页列表
    Route::get('good/rank/select', 'addon\shop\app\adminapi\controller\goods\Rank@select');

    // 修改排行榜状态
    Route::put('goods/rank/status', 'addon\shop\app\adminapi\controller\goods\Rank@modifyStatus');

    /************************************************** 商品统计 *****************************************************/

    //获取商品统计基本信息
    Route::get('goods/statistics/basic', 'addon\shop\app\adminapi\controller\goods\Statistics@getBasic');

    //获取商品统计图表信息
    Route::get('goods/statistics/trend', 'addon\shop\app\adminapi\controller\goods\Statistics@getTrend');

    //获取商品排行榜信息
    Route::get('goods/statistics/rank', 'addon\shop\app\adminapi\controller\goods\Statistics@getRank');

    //获取统计类型
    Route::get('goods/statistics/type', 'addon\shop\app\adminapi\controller\goods\Statistics@getType');

    //同步商品统计信息
    Route::post('goods/statistics/sync', 'addon\shop\app\adminapi\controller\goods\Statistics@syncStatGoods');

    /************************************************** 满减送 *****************************************************/
    //满减送列表
    Route::get('manjian', 'addon\shop\app\adminapi\controller\marketing\Manjian@lists');

    //关闭满减送
    Route::put('manjian/close/:id', 'addon\shop\app\adminapi\controller\marketing\Manjian@closeManjian');

    //删除满减送
    Route::delete('manjian/:id', 'addon\shop\app\adminapi\controller\marketing\Manjian@del');

    //满减送详情
    Route::get('manjian/:id', 'addon\shop\app\adminapi\controller\marketing\Manjian@info');

    //满减送参与会员
    Route::get('manjian/member/:id', 'addon\shop\app\adminapi\controller\marketing\Manjian@member');

    //添加满减送
    Route::post('manjian', 'addon\shop\app\adminapi\controller\marketing\Manjian@add');

    //编辑满减送
    Route::put('manjian/:id', 'addon\shop\app\adminapi\controller\marketing\Manjian@edit');

    //获取编辑数据
    Route::get('manjian/init', 'addon\shop\app\adminapi\controller\marketing\Manjian@init');

    //状态
    Route::get('manjian/status', 'addon\shop\app\adminapi\controller\marketing\Manjian@status');

    //满减送商品校验
    Route::post('manjian/goods/check', 'addon\shop\app\adminapi\controller\marketing\Manjian@checkGoods');

    //满减送批量关闭
    Route::put('manjian/goods/batchClose', 'addon\shop\app\adminapi\controller\marketing\Manjian@batchClose');

    //满减送批量删除
    Route::put('manjian/goods/batchDelete', 'addon\shop\app\adminapi\controller\marketing\Manjian@batchDelete');


})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);
