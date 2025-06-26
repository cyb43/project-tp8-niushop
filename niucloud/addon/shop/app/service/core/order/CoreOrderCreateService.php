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

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\model\cart\Cart;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\service\core\goods\CoreGoodsActivePriceService;
use app\dict\member\MemberDict;
use app\model\member\MemberLevel;
use app\service\core\member\CoreMemberService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 *  订单服务层
 */
class CoreOrderCreateService extends BaseCoreService
{

    use CoreOrderCreateTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 订单创建
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        //参数赋值
        $this->setParam($data);

        $member_info = ( new CoreMemberService() )->getInfoByMemberId($data[ 'member_id' ], 'status');

        if (empty($member_info)) throw new CommonException('SHOP_ORDER_BUYER_NOT_FOUND');//无效的账号
        if ($member_info[ 'status' ] == MemberDict::OFF) throw new CommonException('SHOP_ORDER_BUYER_LOCKED');//账号被锁定

        $order_key = $this->param[ 'order_key' ] ?? '';
        //获取订单缓存缓存
        $this->getOrderCache($order_key);

        $this->goods_data = array_merge($this->goods_data, $this->gift_goods);
        $this->form_data = $this->param[ 'form_data' ] ?? []; // 万能表单数据
        //校验错误
        $this->checkError();
        //普通订单校验库存
        $this->checkStock($this->goods_data);
        $local_delivery_type = $data[ 'delivery' ]['local_delivery_type'] ?? '';
        $order_data = [
            //订单整体
            'order_type' => OrderDict::TYPE,
            'status' => OrderDict::WAIT_PAY,
            'body' => $this->basic[ 'body' ],
            'member_id' => $this->member_id,
            'goods_money' => $this->basic[ 'goods_money' ],
            'delivery_money' => $this->basic[ 'delivery_money' ] ?? 0,
            'discount_money' => $this->basic[ 'discount_money' ] ?? 0,
            'order_money' => $this->basic[ 'order_money' ],
            'has_goods_types' => $this->basic[ 'has_goods_types' ],//包含的商品形式
//            'invoice_id' => $order_array['invoice_id'] ?? 0,

            //收发货相关
            'delivery_type' => $this->delivery[ 'delivery_type' ] ?? '',
            'taker_name' => $data[ 'delivery' ][ 'taker_name' ] ?? $this->delivery[ 'take_address' ][ 'name' ] ?? '',
            'taker_mobile' => $data[ 'delivery' ][ 'taker_mobile' ] ?? $this->delivery[ 'take_address' ][ 'mobile' ] ?? '',
            'buyer_ask_delivery_time' =>$local_delivery_type == 'now' ? "" :  $data[ 'delivery' ][ 'buyer_ask_delivery_time' ] ?? '', // 购买者期望的时间
            'taker_province' => $this->delivery[ 'take_address' ][ 'province_id' ] ?? 0,
            'taker_city' => $this->delivery[ 'take_address' ][ 'city_id' ] ?? 0,
            'taker_district' => $this->delivery[ 'take_address' ][ 'district_id' ] ?? 0,
            'taker_address' => $this->delivery[ 'take_address' ][ 'address' ] ?? '',
            'taker_full_address' => $this->delivery[ 'take_address' ][ 'full_address' ] ?? '',
            'taker_longitude' => $this->delivery[ 'take_address' ][ 'lng' ] ?? '',
            'taker_latitude' => $this->delivery[ 'take_address' ][ 'lat' ] ?? '',
            'take_store_id' => $this->delivery[ 'take_store' ][ 'store_id' ] ?? 0,

            // 附属信息
            'member_remark' => $this->param[ 'member_remark' ] ?? '',//买家留言
            'relate_id' => $this->extend_data[ 'relate_id' ] ?? 0,//关联id
            'activity_type' => $this->extend_data[ 'activity_type' ] ?? '', // 活动类型

        ];//总

        $order_goods_data = [];//项
        $write_goods_data = array_merge($this->goods_data, $this->impulse_buy_list);
        foreach ($write_goods_data as $v) {
            $order_goods_data[] = [
                'member_id' => $data[ 'member_id' ],
                'goods_id' => $v[ 'goods_id' ],
                'sku_id' => $v[ 'sku_id' ],
                'goods_name' => $v[ 'goods' ][ 'goods_name' ],
                'sku_name' => $v[ 'sku_name' ],
                'goods_image' => $v[ 'goods' ][ 'goods_cover' ],
                'sku_image' => $v[ 'sku_image' ],
                'price' => $v[ 'price' ],
                'original_price' => $v[ 'original_price' ] ?? 0,
                'num' => $v[ 'num' ],
                'goods_money' => $v[ 'goods_money' ],
                'goods_type' => $v[ 'goods' ][ 'goods_type' ],
                'order_id' => &$this->order_id,
                'discount_money' => $v[ 'discount_money' ] ?? 0,
                'status' => OrderGoodsDict::NORMAL,
                'extend' => $v[ 'extend' ] ?? '',
                'is_gift' => $v[ 'is_gift' ] ?? 0,
            ];
        }
        $create_order_data = array(
            'order_data' => $order_data,
            'order_goods_data' => $order_goods_data,
        );
        return $this->createOrder($create_order_data);
    }

    /**
     * 整理
     * @param array $data
     * @return void
     */
    public function calculate(array $data)
    {
        //参数赋值
        $this->setParam($data);
        $this->order_key = $this->param[ 'order_key' ] ?? '';
        $is_need_recalculate = $data[ 'is_need_recalculate' ] ?? 0;
        if (empty($this->order_key) || $is_need_recalculate == 1) {
            $this->confirm($this->order_key);
        }
        //获取订单数据的缓存
        $this->getOrderCache($this->order_key . '_basic');
        //计算优惠和营销
        $this->calculateDiscount();
        $this->setOrderCache($this->order_key);
        //计算运费
        $this->calculateDelivery();
        //检测限购
        $this->checkGoodsLimitBuy();

        //金额格式化
        $discount_money = $this->moneyFormat($this->basic[ 'discount_money' ] ?? 0);//优惠金额
        $delivery_money = $this->moneyFormat($this->basic[ 'delivery_money' ] ?? 0);
        $goods_money = $this->moneyFormat($this->basic[ 'goods_money' ] ?? 0);
        $order_money = $this->moneyFormat($this->basic[ 'order_money' ] ?? 0);

        $order_money = $this->moneyFormat($this->moneyCalculate($order_money, $delivery_money, $goods_money, -$discount_money));
        $this->basic[ 'discount_money' ] = $discount_money;
        $this->basic[ 'delivery_money' ] = $delivery_money;
        $this->basic[ 'goods_money' ] = $goods_money;
        //todo 校验控制,不能小于0
        $order_money = $order_money < 0 ? 0 : $order_money;
        $this->basic[ 'order_money' ] = $order_money;
//        $this->basic['pay_money'] = $this->basic['pay_money'] ?? $order_money;
        //订单创建数据写入缓存,并将标识返回给前端
        $order_cache = get_object_vars($this);
        unset($order_cache[ 'param' ]);
        $this->setOrderCache($this->order_key, $order_cache);
        return $order_cache;
    }

    /**
     * 订单确认(基础信息查询并缓存)
     * @return array|mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function confirm($order_key = '')
    {
        //查看会员信息
        $member_id = $this->param[ 'member_id' ];
        $this->member_id = $member_id;
        $member_info = ( new CoreMemberService() )->getInfoByMemberId($member_id, 'nickname, headimg, balance, point, member_level');
        if (empty($member_info)) throw new CommonException('SHOP_ORDER_BUYER_NOT_FOUND');//无效的账号

        // 查询会员等级信息
        $member_info[ 'member_level' ] = ( new MemberLevel() )->where([ [ 'level_id', '=', $member_info[ 'member_level' ] ] ])->field('level_id,level_benefits')->findOrEmpty()->toArray();

        //会员账户信息
        $this->buyer = $member_info;

        $order_config = ( new CoreOrderConfigService() )->getConfig() ?? [];
        $this->form_id = $order_config[ 'form_id' ];

        //查询商品信息
        $this->getGoodsData();
        //配送相关信息
        $this->getDelivery();

        $order_cache = get_object_vars($this);
        unset($order_cache[ 'param' ]);
        unset($order_cache[ 'order_key' ]);
        if (empty($order_key)) {
            $order_key = $this->setOrderCache('', $order_cache);
        }
        //将基础订单数据单独存放一个缓存
        $order_basic_key = $order_key . '_basic';
        $this->setOrderCache($order_basic_key, $order_cache);

        $this->order_key = $order_key;
        return true;
    }

    /**
     * 商品相关数据
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getGoodsData()
    {
        $cart_ids = $this->param[ 'cart_ids' ] ?? [];
        $this->extend_data = $this->param[ 'extend_data' ] ?? []; //活动扩展数据：[ 'relate_id' => 1, 'activity_type' => 'giftcard' ]
        if (!empty($cart_ids)) {
            $this->cart_ids = $cart_ids;
            //查询购物车
            $cart = ( new Cart() )->where([ [ 'id', 'in', $cart_ids ] ])->field('goods_id, sku_id, num, market_type, market_type_id')->select();
            if ($cart->isEmpty()) throw new CommonException('SHOP_ORDER_CARTS_EXPIRE');//无效的数据
            if ($cart->count() != count($cart_ids)) throw new CommonException('SHOP_ORDER_CARTS_EXPIRE');//无效的商品
            $sku_data = $cart->toArray();
        } else {
            $sku_data = $this->param[ 'sku_data' ] ?? [];
            if (empty($sku_data)) throw new CommonException('SHOP_ORDER_CARTS_EXPIRE');//无效的数据
        }
        $sku_ids = array_column($sku_data, 'sku_id');
        $sku_condition = array(
            [ 'sku_id', 'in', $sku_ids ]
        );
        $sku_list = ( new  GoodsSku() )->where($sku_condition)->with([ 'goods' ])->field('sku_id, sku_name, sku_image, goods_id, price, stock, weight, volume,sku_id, sku_spec_format,member_price, sale_price')->select()->toArray();
        $sku_list = array_column($sku_list, null, 'sku_id');
        //商品数据  查询商品列表
        $order_data = [];
        $goods_list = [];
        $order_money = $goods_money = $delivery_money = 0;
        $total_num = 0;
        $body = '';
        //订单中包含的商品形式
        $has_goods_types = [];
        $activity_type = $this->extend_data[ 'activity_type' ] ?? '';
        foreach ($sku_data as $v) {
            $sku_id = $v[ 'sku_id' ];
            $num = $v[ 'num' ];
            $total_num += $num;
            $market_type = $v[ 'market_type' ] ?? '';
            $market_type_id = $v[ 'market_type_id' ] ?? 0;
            $sku_info = $sku_list[ $sku_id ] ?? [];
            if (empty($sku_info)) throw new CommonException('SHOP_ORDER_CARTS_EXPIRE');//无效的商品
            $sku_info[ 'member_discount' ] = $sku_info[ 'goods' ][ 'member_discount' ] ?? '';

            //商品原价
            $sku_info[ 'original_price' ] = $sku_info[ 'price' ];
            //获取活动价格
            $sku_info[ 'show_type' ] = 'original_price';
            $sku_info[ 'active_id' ] = '';
            if ($activity_type != ActiveDict::NEWCOMER_DISCOUNT){
                $goods_active_price_service = (new CoreGoodsActivePriceService());
                $show_price_data = $goods_active_price_service->getActivePrice($sku_info, $this->member_id);
                $sku_info[ 'show_type' ] = $show_price_data[ 'show_type' ];
                $sku_info[ 'price' ] = $show_price_data[ 'show_price' ];
                $sku_info[ 'active_id' ] = $show_price_data[ 'discount_id' ] ?? '';
            }else{
                $sku_info[ 'member_price' ] = $this->getMemberPrice($sku_info);
                $sku_info[ 'price' ] = $sku_info[ 'member_price' ];
                if ($sku_info[ 'member_price' ] < $sku_info[ 'price' ]){
                    $sku_info[ 'show_type' ] = 'member_price';
                }
            }

            //默认金额填充
            $sku_info[ 'discount_money' ] = 0;
            $item_goods_type = $sku_info[ 'goods' ][ 'goods_type' ];

            // 商品限购处理
            if (isset($this->limit_buy[ 'goods_' . $sku_info[ 'goods_id' ] ])) {
                $this->limit_buy[ 'goods_' . $sku_info[ 'goods_id' ] ][ 'num' ] += $num;
            } else {
                $this->limit_buy[ 'goods_' . $sku_info[ 'goods_id' ] ] = [
                    'goods_id' => $sku_info[ 'goods_id' ],
                    'goods_name' => $sku_info[ 'goods' ][ 'goods_name' ],
                    'stock' => $sku_info[ 'goods' ][ 'stock' ],
                    'num' => $num,
                    'is_limit' => $sku_info[ 'goods' ][ 'is_limit' ],
                    'limit_type' => $sku_info[ 'goods' ][ 'limit_type' ],
                    'max_buy' => $sku_info[ 'goods' ][ 'max_buy' ],
                    'min_buy' => $sku_info[ 'goods' ][ 'min_buy' ]
                ];
            }

            if (!in_array($item_goods_type, $has_goods_types)) $has_goods_types[] = $item_goods_type;

            $sku_info[ 'num' ] = $num;
            $sku_info[ 'market_type' ] = $market_type;//活动类型
            $sku_info[ 'market_type_id' ] = $market_type_id;//活动id

            // 计算商品小计
            $price = $sku_info[ 'price' ];
            $sku_info[ 'goods_money' ] = $price * $num;

            /****顺手买商品不参与任何其他活动 进行单独计算****/
            if (!isset($v[ 'impulse_buy_goods_id' ])) {
                // 活动操纵数据  market_data 活动信息
                $temp = [];
                // 过滤活动计算结果，去除空元素
                $temp_list = array_filter(event('ShopGoodsMarketCalculate', [
                    'sku_info' => $sku_info,
                    'sku_data' => $sku_data,
                    'order_obj' => $this
                ]));

                // 获取最后一个非空的活动计算结果
                if (!empty($temp_list)) {
                    $temp = end($temp_list);
                }

                if (!empty($temp)) {
                    // 更新 SKU 信息
                    $sku_info = $temp[ 'sku_info' ];
                    if (!empty($this->extend_data)) {
                        // 更新扩展数据中的关联 ID 和活动类型
                        $this->extend_data[ 'relate_id' ] = $temp[ 'relate_id' ] ?? 0;
                        $this->extend_data[ 'activity_type' ] = $temp[ 'activity_type' ] ?? '';
                    }
                }
            }

            $goods_money += $sku_info[ 'goods_money' ];
            $body = $body ? $body . ( $sku_info[ 'sku_name' ] . $sku_info[ 'goods' ][ 'goods_name' ] ) : ( $sku_info[ 'sku_name' ] . $sku_info[ 'goods' ][ 'goods_name' ] );
            $goods_list[ $sku_id ] = $sku_info;
        }

        /*****顺手买开始*****/
        $impulse_buy_goods = $this->param[ 'impulse_buy_goods' ] ?? [];
        $impulse_buy_list = [];
        if (!empty($impulse_buy_goods)) {
            $temp_list = array_filter(event('ShopImpulseBuyGoodsMarketCalculate', [
                'data' => $impulse_buy_goods,
                'member_id' => $this->member_id,
                'goods_money' => $goods_money,
                'order_obj' => $this
            ]))[ 0 ] ?? [];
            if (!empty($temp_list)) {
                $impulse_buy_list = $temp_list[ 'impulse_buy_data' ];
                $goods_money = $temp_list[ 'goods_money' ];
                $order_money = $temp_list[ 'order_money' ];
                $this->basic[ 'order_money' ] += $order_money;
            }
        }
        /*****顺手买结束*****/

        $this->basic[ 'has_goods_types' ] = $has_goods_types;
        $this->basic[ 'total_num' ] = $total_num;
        $this->goods_data = $goods_list;
        $this->impulse_buy_list = $impulse_buy_list;
        $this->basic[ 'goods_money' ] = $goods_money;
        $this->basic[ 'body' ] = $body;
        return $order_data;
    }

    /**
     * 获取商品的会员价
     * @param $sku_info
     * @return string
     */
    public function getMemberPrice($sku_info)
    {
        if (empty($sku_info[ 'goods' ][ 'member_discount' ])) {
            return $sku_info[ 'price' ];
        }

        // 没有会员等级，排除
        if (empty($this->buyer[ 'member_level' ])) {
            return $sku_info[ 'price' ];
        }

        $price = $sku_info[ 'price' ];

        if ($sku_info[ 'goods' ][ 'member_discount' ] == 'discount') {
            // 按照会员等级折扣计算

            // 默认按会员享受折扣计算
            if (isset($this->buyer[ 'member_level' ][ 'level_benefits' ])
                && isset($this->buyer[ 'member_level' ][ 'level_benefits' ][ 'discount' ])
                && $this->buyer[ 'member_level' ][ 'level_benefits' ][ 'discount' ][ 'is_use' ]) {

                $price = number_format($price * $this->buyer[ 'member_level' ][ 'level_benefits' ][ 'discount' ][ 'discount' ] / 10, 2, '.', '');
            }

        } elseif ($sku_info[ 'goods' ][ 'member_discount' ] == 'fixed_price') {
            // 指定会员价
            if (!empty($sku_info[ 'member_price' ])) {
                $sku_info[ 'member_price' ] = json_decode($sku_info[ 'member_price' ], true);
                if (!empty($sku_info[ 'member_price' ][ 'level_' . $this->buyer[ 'member_level' ][ 'level_id' ] ])) {
                    $member_level_price = $sku_info[ 'member_price' ][ 'level_' . $this->buyer[ 'member_level' ][ 'level_id' ] ];
                    $price = number_format($member_level_price, 2, '.', '');
                }
            }
        }

        return $price;
    }

}
