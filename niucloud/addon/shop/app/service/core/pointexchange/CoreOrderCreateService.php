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

namespace addon\shop\app\service\core\pointexchange;

use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use app\model\member\MemberLevel;
use app\service\core\member\CoreMemberService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use addon\shop\app\model\exchange\Exchange;
use addon\shop\app\dict\order\OrderDiscountDict;
use addon\shop\app\dict\active\ExchangeDict;


/**
 *  积分兑换订单服务层
 */
class CoreOrderCreateService extends BaseCoreService
{
    use \addon\shop\app\service\core\order\CoreOrderCreateTrait;

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
        $order_key = $this->param[ 'order_key' ] ?? '';
        //获取订单缓存缓存
        $this->getOrderCache($order_key);
        //校验错误
        $this->checkError();
        //基础校验库存/活动信息
        $this->checkExchangeGoods($this->goods_data);
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
            //收发货相关
            'delivery_type' => $this->delivery[ 'delivery_type' ] ?? '',
            'taker_name' => $this->delivery[ 'take_address' ][ 'name' ] ?? '',
            'taker_mobile' => $this->delivery[ 'take_address' ][ 'mobile' ] ?? '',
            'taker_province' => $this->delivery[ 'take_address' ][ 'province_id' ] ?? 0,
            'taker_city' => $this->delivery[ 'take_address' ][ 'city_id' ] ?? 0,
            'taker_district' => $this->delivery[ 'take_address' ][ 'district_id' ] ?? 0,
            'taker_address' => $this->delivery[ 'take_address' ][ 'address' ] ?? '',
            'taker_full_address' => $this->delivery[ 'take_address' ][ 'full_address' ] ?? '',
            'taker_longitude' => $this->delivery[ 'take_address' ][ 'lng' ] ?? '',
            'taker_latitude' => $this->delivery[ 'take_address' ][ 'lat' ] ?? '',
            'take_store_id' => $this->delivery[ 'take_store' ][ 'store_id' ] ?? 0,

            'buyer_ask_delivery_time' =>$local_delivery_type == 'now' ? "" :  $data[ 'delivery' ][ 'buyer_ask_delivery_time' ] ?? '', // 购买者期望的时间

            //附属信息
            'member_remark' => $this->param[ 'member_remark' ] ?? '',//买家留言
            'point' => $this->basic[ 'point_sum' ] ?? 0,//买家留言
            'relate_id' => $this->discount[ 'discount' ][ 'discount_type_id' ] ?? 0,//关联id
            'activity_type' => ExchangeDict::DISCOUNT, // 活动类型
        ];
        $order_goods_data = [];//项
        foreach ($this->goods_data as $v) {
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
                'extend' => json_encode($v[ 'exchange_info' ])
            ];
        }
        $create_order_data = array(
            'order_data' => $order_data,
            'order_goods_data' => $order_goods_data,
        );
        return $this->createOrder($create_order_data);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function calculate(array $data)
    {

        //参数赋值
        $this->setParam($data);
        $this->order_key = $this->param[ 'order_key' ] ?? '';
        if (empty($this->order_key)) {
            $this->confirm();
        }
        //获取订单数据的缓存
        $this->getOrderCache($this->order_key . '_basic');
        //  todo  计算优惠和营销
        //$this->calculateDiscount();
        $this->setOrderCache($this->order_key);
        //计算运费
        $this->calculateDelivery();
        //金额格式化
        $discount_money = $this->moneyFormat($this->basic[ 'discount_money' ] ?? 0);//优惠金额
        $delivery_money = $this->moneyFormat($this->basic[ 'delivery_money' ] ?? 0);
        $goods_money = $this->moneyFormat($this->basic[ 'goods_money' ] ?? 0);

        $order_money = $this->moneyFormat($this->moneyCalculate($delivery_money, $goods_money, -$discount_money));
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
     * @param array $data
     * @return array|mixed
     */
    public function confirm()
    {
        //查看会员信息
        $member_id = $this->param[ 'member_id' ];
        $this->member_id = $member_id;
        $member_info = ( new CoreMemberService() )->getInfoByMemberId($member_id, 'nickname, point, member_level');
        if (empty($member_info)) throw new CommonException('SHOP_ORDER_BUYER_NOT_FOUND');//无效的账号
        // 查询会员等级信息
        $member_info[ 'member_level' ] = ( new MemberLevel() )->where([ [ 'level_id', '=', $member_info[ 'member_level' ] ] ])->field('level_id,level_benefits')->findOrEmpty()->toArray();
        //会员账户信息
        $this->buyer = $member_info;
        //查询商品信息
        $this->getGoodsData();
        //配送相关信息
        $this->getDelivery();
        $order_cache = get_object_vars($this);
        unset($order_cache[ 'param' ]);
        unset($order_cache[ 'order_key' ]);
        $order_key = $this->setOrderCache('', $order_cache);
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
        $sku_data = $this->param[ 'sku_data' ] ?? [];
        $sku_ids = array_column($sku_data, 'sku_id');
        $sku_id = $sku_ids[ 0 ];
        $sku_where = [];
        $field = 'names,total_exchange_num,stock,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $exchange_goods_info = ( new  Exchange() )->where(array_merge($sku_where, [ [ 'product_detail', 'like', '%' . '"sku_id":' . $sku_ids[ 0 ] . ',' . '%' ] ]))->append([ 'type_name', 'status_name' ])->field($field)->findOrEmpty()->toArray();
        if (empty($exchange_goods_info)) throw new CommonException('EXCHANGE_DATA_NOT_FOUND');//无效的商品
        $sku_key = array_search($sku_id, array_column($exchange_goods_info[ 'product_detail' ], 'sku_id'));
        //todo 限制兑换 业务
        $sku_info = $exchange_goods_info[ 'product_detail' ][ $sku_key ];
        $exchange_goods_info = array_merge2($exchange_goods_info, $sku_info);
        $sku_list = ( new  GoodsSku() )->where(array_merge($sku_where, [ [ 'sku_id', 'in', $sku_ids ] ]))->with([ 'goods' ])->field('sku_id, sku_name, sku_image, goods_id, price, stock, weight, volume,sku_id, sku_spec_format,member_price, sale_price')->select()->toArray();
        $sku_list = array_column($sku_list, null, 'sku_id');
        $order_data = [];
        $goods_list = [];
        $order_money = $goods_money = $delivery_money = 0;
        $total_num = 0;
        $point_sum = 0;
        $body = '';
        //订单中包含的商品形式
        $has_goods_types = [];
        foreach ($sku_data as $v) {
            $sku_id = $v[ 'sku_id' ];
            $num = $v[ 'num' ];
            $total_num += $num;
            $sku_info = $sku_list[ $sku_id ] ?? [];
            if (empty($sku_info)) throw new CommonException('SHOP_ORDER_CARTS_EXPIRE');//无效的商品
            //商品原价
            $sku_info[ 'original_price' ] = $sku_info[ 'price' ];
            $sku_info[ 'member_price' ] = $exchange_goods_info[ 'price' ];
            $sku_info[ 'price' ] = $exchange_goods_info[ 'price' ];
            //默认金额填充
            $sku_info[ 'discount_money' ] = 0;
            $item_goods_type = $sku_info[ 'goods' ][ 'goods_type' ];
            if (!in_array($item_goods_type, $has_goods_types)) $has_goods_types[] = $item_goods_type;
            $sku_info[ 'num' ] = $num;
            $sku_info[ 'market_type' ] = '积分兑换';//活动类型
            $sku_info[ 'market_type_id' ] = $exchange_goods_info[ 'id' ];//活动id
            //优化业务录入 todo 目前不考虑 数量
            $this->discount[ 'discount' ] = $this->discountFormat(
                [ $sku_info[ 'sku_id' ] ],
                OrderDiscountDict::DISCOUNT,
                $exchange_goods_info[ 'point' ] * $num,
                ( $sku_info[ 'original_price' ] - $sku_info[ 'price' ] ) * $num,
                ExchangeDict::DISCOUNT,
                $exchange_goods_info[ 'id' ],
                '积分兑换',
                $exchange_goods_info[ 'names' ] ?? ''
            );
            $price = $sku_info[ 'price' ];
            $sku_info[ 'goods_money' ] = $price * $num;//小计
            $sku_info[ 'point_sum' ] = $exchange_goods_info[ 'point' ] * $num;//小计
            $goods_money += $sku_info[ 'goods_money' ];
            $point_sum += $sku_info[ 'point_sum' ];
            $body = $body ? $body . ( $sku_info[ 'sku_name' ] . $sku_info[ 'goods' ][ 'goods_name' ] ) : ( $sku_info[ 'sku_name' ] . $sku_info[ 'goods' ][ 'goods_name' ] );
            $body = $exchange_goods_info[ 'names' ] . " " . $body;
            $sku_info[ 'goods_name' ] = $exchange_goods_info[ 'names' ];
            $sku_info[ 'sku_image' ] = $exchange_goods_info[ 'image' ];
            $sku_info[ 'exchange_id' ] = $exchange_goods_info[ 'id' ];
            $sku_info[ 'exchange_info' ] = $exchange_goods_info[ 'product_detail' ][ $sku_key ];
            $goods_list[ $sku_id ] = $sku_info;
        }
        $this->basic[ 'has_goods_types' ] = $has_goods_types;
        $this->basic[ 'total_num' ] = $total_num;
        $this->basic[ 'point_sum' ] = $point_sum ?? 0;
        $this->goods_data = $goods_list;
        $this->basic[ 'goods_money' ] = $goods_money;
        $this->basic[ 'body' ] = $body;
        return $order_data;
    }

    /**
     * 自身业务检测
     * @param $sku_info
     * @return string
     */
    public function checkExchangeGoods($order_goods_data)
    {
        //不走后续的扣款判断改变提示语
        if ($this->basic[ 'point_sum' ] > $this->buyer[ 'point' ]) {
            throw new CommonException('SHOP_ORDER_EXCHANGE_POINT_INSUFFICIENT');
        }
        $order_goods_data_column = array_column($order_goods_data, 'num', 'sku_id');
        $sku_list = ( new GoodsSku() )->where([ [ 'sku_id', 'in', array_column($order_goods_data, 'sku_id') ] ])->select();
        foreach ($sku_list as $v) {
            $sku_where = [];
            $field = 'names,total_exchange_num,stock,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
            $exchange_goods_info = ( new  Exchange() )->where(array_merge($sku_where, [ [ 'product_detail', 'like', '%' . '"sku_id":' . $v[ 'sku_id' ] . ',' . '%' ] ]))->append([ 'type_name', 'status_name' ])->field($field)->findOrEmpty()->toArray();
            if (empty($exchange_goods_info)) throw new CommonException('EXCHANGE_DATA_NOT_FOUND');//无效的商品
            if ($exchange_goods_info[ 'status' ] != 1) throw new CommonException('EXCHANGE_ACTIVITY_REMOVE');//下架判断
            $sku_key = array_search($v[ 'sku_id' ], array_column($exchange_goods_info[ 'product_detail' ], 'sku_id'));
            //todo 限制兑换 业务
            $sku_info = $exchange_goods_info[ 'product_detail' ][ $sku_key ];
            if ($sku_info[ 'limit_num' ] < $order_goods_data_column[ $v[ 'sku_id' ] ]) throw new CommonException('SHOP_ORDER_EXCHANGE_EXCEEDING_LIMIT');// 限购判断
            if ($v[ 'stock' ] < $order_goods_data_column[ $v[ 'sku_id' ] ]) throw new CommonException('SHOP_ORDER_GOODS_INSUFFICIENT');
            if ($sku_info[ 'stock' ] < $order_goods_data_column[ $v[ 'sku_id' ] ]) throw new CommonException('SHOP_ORDER_EXCHANGE_INSUFFICIENT_EXCHANGE_QUANTITY');//兑换数判断
        }
    }

}
