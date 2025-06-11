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


use addon\shop\app\dict\coupon\CouponDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDiscountDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use addon\shop\app\service\core\delivery\CoreDeliveryService;
use addon\shop\app\service\core\delivery\CoreExpressService;
use addon\shop\app\service\core\delivery\CoreLocalDeliveryService;
use addon\shop\app\service\core\delivery\CoreStoreService;
use addon\shop\app\service\core\goods\CoreGoodsLimitBuyService;
use addon\shop\app\service\core\marketing\CoreManjianService;
use app\service\core\diy_form\CoreDiyFormRecordsService;
use app\service\core\member\CoreMemberAddressService;
use core\exception\CommonException;
use Exception;
use think\facade\Cache;
use think\facade\Db;

/**
 *  订单服务层
 */
trait CoreOrderCreateTrait
{
    public $member_id;//会员id
    public $form_id; // 万能表单id
    public $param = [];//入参
    public $cart_ids = [];//购物车
    public $buyer = [];//买家信息
    public $basic = [
        'discount_money' => 0,//优惠金额
        'coupon_money' => 0,//优惠券金额
        'delivery_money' => 0,
        'goods_money' => 0,
        'order_money' => 0
    ];//基本数据处理(整体的数据)
    public $goods_data = [];//商品数据处理
    public $impulse_buy_list = [];//顺买商品数据处理
    public $extend_data = [];//活动数据
    public $form_data = [];// 万能表单数据
    public $config = [];//配置集合
    public $discount = [];//优惠整合
    public $gift = [];//赠品集合
    public $gift_goods = [];//赠品商品
    public $limit_buy = [];//限购

    public $delivery = [];//配送相关

    public $order_id;

    public $invoice = [];

    public $order_key;
    public $error = [];

    public function createOrder(array $data)
    {
        $order_data = $data[ 'order_data' ];
        $order_goods_data = $data[ 'order_goods_data' ];
        $order_no = create_no();
        $order_data[ 'order_no' ] = $order_no;
        $order_data[ 'order_from' ] = $this->param[ 'order_from' ];//来源渠道
        $order_data[ 'ip' ] = request()->ip();
        $main_type = $data[ 'main_type' ] ?? OrderLogDict::MEMBER;
        $main_id = $data[ 'main_id' ] ?? $order_data[ 'member_id' ];

        // 校验整理发票
        $this->invoice();
        Db::startTrans();
        try {
            $order = ( new Order() )->create($order_data);
            $this->order_id = $order[ 'order_id' ];
            // 添加订单项目表
            $order_goods_model = new OrderGoods();
            $order_goods_data = array_map(function($value) {
                $value[ 'order_goods_money' ] = $this->calculateOrderGoodsMoney($value);
                return $value;
            }, $order_goods_data);
            $order_goods_model->insertAll($order_goods_data);

            // 优惠项
            $this->useDiscount();

            // 添加万能表单
            $this->addFormData($this->order_id);

            $order_data[ 'order_id' ] = $this->order_id;

            // 订单创建后事件
            CoreOrderEventService::orderCreate([ 'order_id' => $this->order_id, 'order_data' => $order_data, 'order_goods_data' => $order_goods_data, 'cart_ids' => $this->cart_ids, 'basic' => get_object_vars($this), 'main_type' => $main_type, 'main_id' => $main_id, 'time' => time() ]);

            Db::commit();

            // 删除订单缓存
            $this->delOrderCache($this->order_key);

            // 订单创建后事件
            CoreOrderEventService::orderCreateAfter([ 'order_id' => $this->order_id, 'order_data' => $order_data, 'order_goods_data' => $order_goods_data, 'cart_ids' => $this->cart_ids, 'basic' => get_object_vars($this), 'main_type' => $main_type, 'main_id' => $main_id, 'time' => time() ]);
//            event('AfterShopOrderCreate', ['order_id' => $this->order_id, 'order_data' => $order_data, 'order_goods_data' => $order_goods_data, 'cart_ids' => $this->cart_ids, 'basic' => get_object_vars($this), 'main_type' => $main_type, 'main_id' => $main_id, 'time' => time()]);

            // 订单金额为0的话,要直接支付
            if ($order_data[ 'order_money' ] == 0) {
                ( new CoreOrderPayService() )->pay([ 'trade_id' => $this->order_id, 'main_type' => $main_type, 'main_id' => $main_id ]);
            }

            return [
                'trade_type' => $order_data[ 'order_type' ],
                'order_id' => $this->order_id
            ];

        } catch (Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 校验库存
     * @param $order_goods_data
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkStock($order_goods_data)
    {
        $sku_list = ( new GoodsSku() )->field('sku_id,goods_id,sku_name,stock')->where([ [ 'sku_id', 'in', array_column($order_goods_data, 'sku_id') ] ])
            ->with([
                'goods' => function($query) {
                    $query->field('goods_id,goods_name');
                }
            ])->select()->toArray();

        // todo 赠品库存校验
        $sku_num = [];
        foreach ($order_goods_data as $v) {
            if (isset($sku_num[ 'num_' . $v[ 'sku_id' ] ])) {
                $sku_num[ 'num_' . $v[ 'sku_id' ] ] += $v[ 'num' ];
            } else {
                $sku_num[ 'num_' . $v[ 'sku_id' ] ] = $v[ 'num' ];
            }
        }

        foreach ($sku_list as $v) {
            $goods_name = count($sku_list) > 1 ? '“' . $v[ 'goods' ][ 'goods_name' ] . $v[ 'sku_name' ] . '”' : '';
            if ($v[ 'stock' ] < $sku_num[ 'num_' . $v[ 'sku_id' ] ]) throw new CommonException('商品' . $goods_name . '库存不足');
        }
    }

    /**
     * 发票整理
     * @return void
     */
    public function invoice()
    {
        if ($this->config('invoice')) {
            if (!empty($this->param[ 'invoice' ])) {
//                $invoive_type  = $this->param['invoice']['type'] ?? '';
                $this->invoice[ 'type' ] = $this->param[ 'invoice' ][ 'type' ] ?? '';
                $this->invoice[ 'name' ] = $this->param[ 'invoice' ][ 'name' ] ?? '';
                $this->invoice[ 'header_type' ] = $this->param[ 'invoice' ][ 'header_type' ] ?? '';
                $this->invoice[ 'header_name' ] = $this->param[ 'invoice' ][ 'header_name' ] ?? '';
                $this->invoice[ 'tax_number' ] = $this->param[ 'invoice' ][ 'tax_number' ] ?? '';
                $this->invoice[ 'mobile' ] = $this->param[ 'invoice' ][ 'mobile' ] ?? '';
                $this->invoice[ 'email' ] = $this->param[ 'invoice' ][ 'email' ] ?? '';
                $this->invoice[ 'telephone' ] = $this->param[ 'invoice' ][ 'telephone' ] ?? '';
                $this->invoice[ 'address' ] = $this->param[ 'invoice' ][ 'address' ] ?? '';
                $this->invoice[ 'bank_name' ] = $this->param[ 'invoice' ][ 'bank_name' ] ?? '';
                $this->invoice[ 'bank_card_number' ] = $this->param[ 'invoice' ][ 'bank_card_number' ] ?? '';
//                $this->invoice['money'] = $this->param['invoice']['money'] ?? '';
            }
        }

    }

    /**
     * 配置设置或查询
     * @param $key
     * @return array|mixed
     */
    public function config($key)
    {
        //查询购物配置
        $config = $this->config[ $key ] ?? [];
        if (empty($this->config[ $key ])) {
            switch ($key) {
                case 'order'://交易配置
                    $config = ( new CoreOrderConfigService() )->orderClose() ?? [];
                    break;
                case 'point'://交易配置
                    break;
                case 'delivery_type':
                    $config = ( new CoreDeliveryService() )->getDeliveryConfig();
                    break;
                case 'invoice':
                    $config = ( new CoreOrderConfigService() )->invoice() ?? [];
                    break;
            }
            $this->config[ $key ] = $config;
        }
        return $config;
    }

    /**
     * 计算订单项金额
     * @param $goods
     * @return mixed
     */
    public function calculateOrderGoodsMoney($goods)
    {
        $goods_money = $goods[ 'goods_money' ];
        $discount_money = $goods[ 'discount_money' ] ?? 0;
        return $goods_money - $discount_money;
    }

    /**
     * 优惠写入
     * @return void
     */
    public function useDiscount()
    {
        $insert_discount_data = [];
        $discount_service = new CoreOrderDiscountService();
        if ($this->discount) {
            foreach ($this->discount as $k => $v) {
                $result = array_values(array_filter(event('ShopOrderDiscountCreate', [ 'data' => $v, 'order_object' => $this ])));
                if (empty($result)) {
                    switch ($k) {
                        case 'coupon':
                            //使用优惠券
                            ( new CoreCouponMemberService() )->use([
//                            'member_id' => $this->member_id,
                                'id' => $v[ 'discount_type_id' ],
                                'trade_id' => $this->order_id
                            ]);
                            break;
                    }
                }
                if (isset($v[ 'discount_type' ])) {
                    $insert_discount_data[] = [
                        'type' => $v[ 'type' ],
                        'num' => $v[ 'num' ],
                        'money' => $v[ 'money' ],
                        'discount_type' => $v[ 'discount_type' ],
                        'discount_type_id' => $v[ 'discount_type_id' ],
                        'content' => $v[ 'content' ],
                        'order_id' => $this->order_id,
                    ];
                } else {
                    foreach ($v as $vv) {
                        $insert_discount_data[] = [
                            'type' => $vv[ 'type' ],
                            'num' => $vv[ 'num' ],
                            'money' => $vv[ 'money' ],
                            'discount_type' => $vv[ 'discount_type' ],
                            'discount_type_id' => $vv[ 'discount_type_id' ],
                            'content' => $vv[ 'content' ],
                            'order_id' => $this->order_id,
                        ];
                    }
                }

            }

            $discount_service->addAll($insert_discount_data);
        }
        return true;
    }


    /**
     * 获取参数
     * @param $key
     * @param $default
     * @return mixed|string
     */
    public function param($key, $default = '')
    {
        return $this->param[ $key ] ?? $default;
    }

    /**
     * 订单优惠
     * @return void
     */
    public function getDiscount()
    {
        //查询积分优惠
//        $this->getPoint();
        //查询可用优惠券
//        $this->getCoupon();
    }

    /**
     * 获取有效的优惠券
     * @param $data
     */
    public function getCoupon($data)
    {
        //参数赋值
        $this->setParam($data);
        $order_key = $this->param[ 'order_key' ] ?? '';
        //获取订单数据的缓存
        $this->getOrderCache($order_key);

        $coupon_list = ( new CoreCouponMemberService() )->getUseCouponListByMemberId($this->member_id);
        //如果有优惠券
        if (!empty($coupon_list)) {
            foreach ($coupon_list as &$v) {
                $type = (int) $v[ 'type' ];
                $goods_data = $v[ 'goods' ];
                $min_condition_money = $v[ 'min_condition_money' ];
                switch ($type) {
                    case CouponDict::ALL:
                        $v[ 'is_normal' ] = true;
                        //匹配的商品
                        $match_goods_list = $this->goods_data;
                        $match_goods_money = $this->basic[ 'goods_money' ];
                        break;
                    case CouponDict::CATEGORY:
                        $category_ids = array_column($goods_data, 'category_id');
                        //匹配的商品
                        $match_goods_list = [];
                        $match_goods_money = 0;
                        foreach ($this->goods_data as $goods_v) {
                            $item_goods_category = $goods_v[ 'goods' ][ 'goods_category' ];//商品分类数组
                            if (!empty(array_intersect($category_ids, $item_goods_category))) {
                                $match_goods_list[] = $goods_v;
                                $match_goods_money += $goods_v[ 'goods_money' ];
                            }
                        }
                        break;
                    case CouponDict::GOODS:
                        $goods_ids = array_column($goods_data, 'goods_id');
                        //匹配的商品
                        $match_goods_list = [];
                        $match_goods_money = 0;
                        foreach ($this->goods_data as $goods_v) {
                            if (in_array($goods_v[ 'goods_id' ], $goods_ids)) {
                                $match_goods_list[] = $goods_v;
                                $match_goods_money += $goods_v[ 'goods_money' ];
                            }
                        }
                        break;
                }

                if (empty($match_goods_list)) {
                    $v[ 'is_normal' ] = false;
                    $v[ 'error' ] = get_lang('SHOP_ORDER_COUPON_SUPPORT_GOODS');//没有支持可用的商品
                } else {
                    if ($match_goods_money < $min_condition_money) {
                        $v[ 'is_normal' ] = false;
                        $v[ 'error' ] = get_lang('SHOP_ORDER_COUPON_NOT_CONDITION');//没有达到商品最低使用条件
                    } else {
                        $v[ 'is_normal' ] = true;
                    }
                }
            }
        }
        return $coupon_list;
    }

    /**
     * 给传参
     * @param $param
     * @return true
     */
    public function setParam($param)
    {
        $this->param = $param;
        return true;
    }


    /**
     * 计算优惠
     * @return void
     */
    public function calculateDiscount()
    {
        //查询积分优惠
//        $this->getPoint();
        //满减送优惠计算
        $this->calculateManjian();
        //查询可用优惠券
        $this->calculateCoupon();
    }

    /**
     * 优惠券计算
     * @return void
     */
    public function calculateCoupon()
    {
        $coupon_id = $this->param[ 'discount' ][ 'coupon_id' ] ?? 0;//使用优惠券id

        if ($coupon_id > 0) {
            $coupon_data = ( new CoreCouponMemberService() )->getUseCouponById($coupon_id);
            if (empty($coupon_data)) throw new CommonException('SHOP_ORDER_COUPON_EXPIRE_OR_NOT_FOUND');//优惠券已使用或不存在

            $time = time();
            if ($time > strtotime($coupon_data[ 'expire_time' ])) {
                throw new CommonException('SHOP_ORDER_COUPON_EXPIRE');//优惠券已使用或不存在
            }
            $type = (int) $coupon_data[ 'type' ];
            $goods_data = $coupon_data[ 'goods' ];
            $min_condition_money = $coupon_data[ 'min_condition_money' ];
            $match_order_goods_money = 0;
            switch ($type) {
                case CouponDict::ALL:
                    //匹配的商品
                    $match_goods_list = $this->goods_data;
                    $match_goods_money = $this->basic[ 'goods_money' ];
                    $match_order_goods_money = $this->basic[ 'goods_money' ] - $this->basic[ 'discount_money' ];
                    break;
                case CouponDict::CATEGORY:
                    $category_ids = array_column($goods_data, 'category_id');
                    //匹配的商品
                    $match_goods_list = [];
                    $match_goods_money = 0;
                    foreach ($this->goods_data as $goods_v) {
                        $item_goods_category = $goods_v[ 'goods' ][ 'goods_category' ];//商品分类数组
                        if (!empty(array_intersect($category_ids, $item_goods_category))) {
                            $match_goods_list[] = $goods_v;
                            $match_goods_money += $goods_v[ 'goods_money' ];
                            $match_order_goods_money += $this->calculateOrderGoodsMoney($goods_v);
                        }
                    }
                    break;
                case CouponDict::GOODS:
                    $goods_ids = array_column($goods_data, 'goods_id');
                    //匹配的商品
                    $match_goods_list = [];
                    $match_goods_money = 0;
                    foreach ($this->goods_data as $goods_v) {
                        if (in_array($goods_v[ 'goods_id' ], $goods_ids)) {
                            $match_goods_list[] = $goods_v;
                            $match_goods_money += $goods_v[ 'goods_money' ];
                            $match_order_goods_money += $this->calculateOrderGoodsMoney($goods_v);
                        }
                    }

                    break;
            }
            if (empty($match_goods_list)) {
                $this->setError(get_lang('SHOP_ORDER_COUPON_NOT_SUPPORT_GOODS'));//没有支持可用的商品
            } else {
                if ($match_goods_money < $min_condition_money) {
                    $this->setError(get_lang('SHOP_ORDER_COUPON_NOT_SUPPORT_MIN_MONEY'));//没有达到商品最低使用条件
                } else {
                    $coupon_money = $coupon_data[ 'price' ];
                    if ($coupon_money > $match_order_goods_money) {
                        $coupon_money = $match_order_goods_money;
                    }
                    $surplus_money = $coupon_money;
                    $match_goods_list = array_values($match_goods_list);
                    $match_goods_list = array_filter($match_goods_list, function($item) {
                        return ( $item[ 'goods_money' ] - $item[ 'discount_money' ] ) != 0;
                    });
                    $match_count = count($match_goods_list);
                    //根据商品金额计算个订单项享受的优惠
                    foreach ($match_goods_list as $k => $v) {
                        $item_order_goods_money = $this->calculateOrderGoodsMoney($v);
                        $item_sku_id = $v[ 'sku_id' ];
                        if ($k == ( $match_count - 1 )) {
                            $item_coupon_money = $surplus_money;
                        } else {
                            if ($match_order_goods_money == 0 || $coupon_money == 0) {
                                $item_coupon_money = 0;
                            } else {
                                $item_coupon_money = $this->moneyFormat($item_order_goods_money / $match_order_goods_money * $coupon_money);
                                if ($item_coupon_money == 0) {
                                    $item_coupon_money = $item_order_goods_money;
                                }
                                if ($item_coupon_money > $surplus_money) {
                                    $item_coupon_money = $surplus_money;
                                }
                            }
                        }

                        $this->goods_data[ $item_sku_id ][ 'discount_money' ] += $item_coupon_money;
//                        $this->goods_data[$item_sku_id]['order_goods_money'] = $this->calculateOrderGoodsMoney($this->goods_data[$item_sku_id]);

                        $surplus_money = bcsub($surplus_money, $item_coupon_money, 2);
                    }
                    //优惠累增
                    $this->basic[ 'discount_money' ] += $coupon_money;
                    $this->basic[ 'coupon_money' ] += $coupon_money;
//                    $discount_money = $this->basic['discount']['discount_money'];
                    $this->discount[ 'coupon' ] = $this->discountFormat(
                        array_column($match_goods_list, 'sku_id'),
                        OrderDiscountDict::DISCOUNT,
                        1,
                        $coupon_money,
                        'coupon',
                        $coupon_id,
                        '',
                        $coupon_data[ 'title' ]
                    );
                }
            }

        }

    }

    /**
     * 优惠项格式
     * @param $match_goods_ids
     * @param $type
     * @param $num
     * @param $money
     * @param $discount_type
     * @param $discount_type_id
     * @param $content
     * @return array
     */
    public function discountFormat($match_goods_ids, $type, $num, $money, $discount_type, $discount_type_id, $content, $title = '')
    {
        return [
            'match_goods_ids' => $match_goods_ids,
            'type' => $type,
            'num' => $num,
            'money' => $money,
            'discount_type' => $discount_type,
            'discount_type_id' => $discount_type_id,
            'content' => $content,
            'title' => $title
        ];
    }

    /**
     * 获取积分
     * @return true
     */
    public function getPoint()
    {

        $is_point = $this->param[ 'discount' ][ 'is_point' ] ?? 0;//是否使用积分
        if ($is_point) {
            //todo  现在认为积分只能抵扣商品总额(单个商品不设置支持积分)
            $goods_money = $this->basic[ 'goods_money' ];
            $member_point = $this->buyer[ 'point' ];//会员积分
            $point_config = $this->config('config');
            if ($point_config[ 'status' ]) {//启用
                $point_money = $member_point * $point_config[ 'rate' ];
                $point_money = $point_money > $goods_money ? $goods_money : $point_money;
                $point = round($point_money / $point_config[ 'rate' ]);//todo 积分是否可以为小数

                $this->discount[ 'point' ][ 'point' ] = $point;
                $this->discount[ 'point' ][ 'point_money' ] = $point_money;
            }
        }

        return true;
    }

    public function usePoint($data)
    {
        $member_account = $data[ 'member_account' ];
    }

    /**
     * 计算积分
     * @return void
     */
    public function calculatePoint()
    {
    }

    /**
     * 使用优惠券
     * @param $data
     * @return void
     */
    public function useCoupon($data)
    {

    }

    /**
     * 查询订单支持的配送方式
     * @return void
     */
    public function getDelivery()
    {
        //先判断商品项中是否存在实物商品
        $has_goods_types = $this->basic[ 'has_goods_types' ];
        //存在实物商品查询物流信息
        if (in_array(GoodsDict::REAL, $has_goods_types)) {
            //todo 查询启用的配送方式
            $delivery_type_list = $this->config('delivery_type');
            foreach ($delivery_type_list as $k => $v) {
                if ($v[ 'status' ] != 1) {
                    unset($delivery_type_list[ $k ]);
                }
            }
//            $delivery_type_list = array_column($delivery_type_list, null, 'key');
            $this->delivery[ 'delivery_type_list' ] = $delivery_type_list;
        }
        return true;
    }

    /**
     * 获取和计算配送信息和费用
     * @return void
     */
    public function calculateDelivery()
    {
        //配送参数
        $delivery = $this->param[ 'delivery' ] ?? [];
        $delivery_type = $delivery[ 'delivery_type' ] ?? '';//配送方式
        $has_goods_types = $this->basic[ 'has_goods_types' ];
        //存在实物商品查询物流信息
        if (in_array(GoodsDict::REAL, $has_goods_types)) {
            $delivery_type_list = $this->delivery[ 'delivery_type_list' ];
            if (empty($delivery_type_list)) $this->setError('NOT_CONFIGURED_DELIVERY_TYPE');//商家尚未配置配送方式
            if ($delivery_type) {
                //校验是否合法
                if (empty($delivery_type_list[ $delivery_type ])) throw new CommonException('SHOP_ORDER_PLEASE_SELECT_DELIVERY_TYPE');//配送方式非法
            } else {
                //没有选择配送方式的话,默认选中第一个配送方式
                $default_delivery = reset($delivery_type_list);
                $delivery_type = $default_delivery ? $default_delivery[ 'key' ] : '';
            }
        } else {
            //如果订单中只有虚拟商品
            if (in_array(GoodsDict::VIRTUAL, $has_goods_types)) {
                $delivery_type = OrderDeliveryDict::VIRTUAL;
            }
        }
        if (!$delivery_type) {
            $this->setError('SHOP_ORDER_PLEASE_SELECT_DELIVERY_TYPE');//没有选中配送方式....
        } else {
            $this->delivery[ 'delivery_type' ] = $delivery_type;

            //选中收货地址
            $this->selectTakeAddress();

            switch ($delivery_type) {
                case OrderDeliveryDict::EXPRESS://快递
                    CoreExpressService::calculate($this);
                    break;
                case OrderDeliveryDict::LOCAL_DELIVERY://配送
                    CoreLocalDeliveryService::calculate($this);
                    break;
                case OrderDeliveryDict::STORE://自提
                    CoreStoreService::calculate($this);
                    break;
            }

            if (!empty($this->extend_data) && isset($this->extend_data[ 'delivery_money' ])) {
                $this->basic[ 'delivery_money' ] = $this->extend_data[ 'delivery_money' ];
            } else {
                $this->basic[ 'delivery_money' ] = round($this->basic[ 'delivery_money' ] ?? 0, 2);
            }

        }

        return true;
    }

    /**
     * 选中收货地址
     * @return void
     */
    public function selectTakeAddress()
    {
        //定义收货地址
        $has_goods_types = $this->basic[ 'has_goods_types' ];
        if (in_array(GoodsDict::REAL, $has_goods_types)) {
            if ($this->delivery[ 'delivery_type' ] == OrderDeliveryDict::STORE) {
                if (!empty($this->param[ 'delivery' ][ 'take_store_id' ])) {
                    $this->delivery[ 'take_store' ] = ( new CoreStoreService() )->getInfoById($this->param[ 'delivery' ][ 'take_store_id' ]);
                }
            } else {
                //查询默认收货地址
                if (!empty($this->param[ 'delivery' ][ 'take_address_id' ])) {
                    $this->delivery[ 'take_address' ] = ( new CoreMemberAddressService() )->getMemberAddressById($this->param[ 'delivery' ][ 'take_address_id' ], $this->member_id);
                } else {
                    $take_address = ( new CoreMemberAddressService() )->getDefaultAddressByMemberId($this->member_id);
                    if ($this->delivery[ 'delivery_type' ] == OrderDeliveryDict::LOCAL_DELIVERY && empty($take_address[ 'lng' ])) {
                        $take_address = ( new CoreMemberAddressService() )->getLngLatAddressByMemberId($this->member_id);
                    }
                    $this->delivery[ 'take_address' ] = $take_address;
                }

                if ($this->delivery[ 'delivery_type' ] == OrderDeliveryDict::LOCAL_DELIVERY && !empty($this->delivery[ 'take_address' ]) && empty($this->delivery[ 'take_address' ][ 'lng' ])) {
                    $this->setError('SHOP_ORDER_PLEASE_SELECT_DELIVERY_EMPTY_LNG_LAT');//选中的配送方式没有设置经纬度....
                }
            }

        }
        return true;
    }

    /**
     * 检测限购
     * @return bool
     */
    public function checkGoodsLimitBuy()
    {
        $error = ( new CoreGoodsLimitBuyService() )->checkGoodsLimitBuy($this->member_id, $this->limit_buy);
        if (!empty($error)) {
            $this->setError($error);
        }
        return true;
    }

    /**
     * 满减送优惠计算
     * @return void
     */
    public function calculateManjian()
    {
        ( new CoreManjianService() )->calculate($this);
    }

    /**
     * 商品计算运费
     * @param $goods
     * @return void
     */
    public function getDeliveryMoney($goods, $delivery_type)
    {

    }

    /**
     * 存在错误则抛出
     * @return void
     */
    public function checkError()
    {
        $error = $this->getError();
        if ($error) throw new CommonException($error[ 0 ]);
    }

    /**
     * 获取错误
     * @return array|mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 定义错误
     * @param $error
     * @return void
     */
    public function setError($error)
    {
        $this->error[] = $error;
    }

    /**
     * 获取整合后的数据
     * @return void
     */
    public function getData()
    {

    }

    /**
     * 设置订单缓存
     * @return string
     * @throws Exception
     */
    public function setOrderCache($order_key = '', $order_cache = [])
    {
        if (empty($order_key)) {
            $order_key = create_no('', $this->member_id);
        }
        Cache::tag('order_cache')->set($order_key, $order_cache, 300000);
        return $order_key;
    }

    /**
     * 获取订单缓存
     * @param $order_key
     * @return void
     */
    public function getOrderCache($order_key)
    {

        $order_cache = Cache::get($order_key, []);
        if (empty($order_cache))
            throw new CommonException('SHOP_ORDER_CARTS_EXPIRE');//购物车数据已过期
        foreach ($order_cache as $k => $v) {
            $this->$k = $v;
        }

        return true;
    }

    /**
     * 清除订单缓存
     * @param $order_key
     * @return true
     */
    public function delOrderCache($order_key = '')
    {
        Cache::delete($order_key);
        return true;
    }

    /**
     * 校验抵扣项是否可用
     * @return void
     */
    public function checkDiscount()
    {

    }

    /**
     * 比例(向下取整)
     * @param $rate
     * @return float|int
     */
    public function rateFormat($rate)
    {
        return floor(strval(( $rate ) * 100)) / 100;
    }

    /**
     * 金额计算
     * @return void
     */
    public function moneyCalculate()
    {
        $args = func_get_args();
        $money = 0;
        foreach ($args as $v) {
            $money += $v * 100;
        }
        return $money / 100;
    }

    /**
     * 金额格式化
     * @param $money
     * @return float|int
     */
    public function moneyFormat($money)
    {

        return floor(strval(( $money ) * 100)) / 100;
    }

    /**
     * 添加万能表单数据
     * @param $order_id
     */
    public function addFormData($order_id)
    {
        if (!empty($this->form_data)) {
            $diy_form_service = new CoreDiyFormRecordsService();
            $order_form = $this->form_data[ 'order' ] ?? [];
            if (!empty($order_form)) {
                $order_form[ 'member_id' ] = $this->member_id;
                $order_form[ 'relate_id' ] = $order_id;
                $form_record_id = $diy_form_service->add($order_form);
                ( new Order() )->where([
                    [ 'order_id', '=', $order_id ]
                ])->update([ 'form_record_id' => $form_record_id ]);
            }

            $goods_form = $this->form_data[ 'goods' ] ?? [];
            if (!empty($goods_form)) {
                $order_goods_model = new OrderGoods();
                $order_goods_list = $data = $order_goods_model->where([ [ 'order_id', '=', $order_id ] ])
                    ->field('order_goods_id,sku_id')->select()->toArray();
                foreach ($goods_form as $k => $v) {
                    foreach ($order_goods_list as $ck => $cv) {
                        if ($cv[ 'sku_id' ] == $k) {
                            $v[ 'member_id' ] = $this->member_id;
                            $v[ 'relate_id' ] = $cv[ 'order_goods_id' ];
                            $form_record_id = $diy_form_service->add($v);
                            $order_goods_model->where([
                                [ 'order_id', '=', $order_id ],
                                [ 'order_goods_id', '=', $cv[ 'order_goods_id' ] ]
                            ])->update([ 'form_record_id' => $form_record_id ]);
                            break;
                        }
                    }
                }
            }
        }
    }
}
