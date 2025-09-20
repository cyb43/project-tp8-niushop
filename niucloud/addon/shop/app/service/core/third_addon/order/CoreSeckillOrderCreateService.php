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

namespace addon\shop\app\service\core\third_addon\order;

use addon\seckill\app\dict\SeckillOrderDict;
use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\dict\order\OrderLogDict;
use addon\shop\app\model\cart\Cart;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\api\marketing\NewcomerService;
use addon\shop\app\service\core\goods\CoreGoodsActivePriceService;
use addon\shop\app\service\core\marketing\CoreNewcomerService;
use addon\shop\app\service\core\order\CoreOrderConfigService;
use addon\shop\app\service\core\order\CoreOrderCreateTrait;
use addon\shop\app\service\core\order\CoreOrderEventService;
use addon\shop\app\service\core\order\CoreOrderPayService;
use app\dict\member\MemberDict;
use app\dict\pay\PayDict;
use app\model\member\MemberLevel;
use app\service\core\member\CoreMemberService;
use app\service\core\pay\CorePayService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 *  秒杀订单服务层
 */
class CoreSeckillOrderCreateService extends BaseCoreService
{

    use CoreOrderCreateTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }


    /**
     * 秒杀订单参数设置
     * @param $params
     * @return void
     */
    public function setSeckillParam($params)
    {
        $param = [
            'order_from' => $params['order_from'] ?? '',
            'member_id' => $params['member_id'],
            'extend_data' => [
                'relate_id' => $params['seckill_id'],
                'relate_order_id' => $params['id'],
                'relate_source' => 'seckill'
            ],
            'delivery' => [
                'delivery_type' => 'express',
            ],
            'take_address' => [
                'taker_name' => $params['taker_name'],
                'taker_mobile' => $params['taker_mobile'],
                'taker_province' => $params['taker_province'],
                'taker_city' => $params['taker_city'],
                'taker_district' => $params['taker_district'],
                'taker_address' => $params['taker_address'],
                'taker_full_address' => $params['taker_full_address'],
                'taker_longitude' => $params['taker_longitude'] ?? '',
                'taker_latitude' => $params['taker_latitude'] ?? '',
            ],
            'sku_data' => [
                [
                    'sku_id' => $params['source_sku_id'],
                    'num' => $params['num'],
                    'seckill_price' => $params['goods_price']
                ]
            ],
            'pay' => $params['pay'],
            'delivery_money'=>$params['delivery_money'] ?? 0,
        ];
        $this->param = $param;
        $this->extend_data = $this->param[ 'extend_data' ] ?? [];
    }


    /**
     * 秒杀订单创建
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        //参数赋值
        $this->setSeckillParam($data);
        //检查会员
        $this->checkMember($data);
        //订单计算
        $this->calculate($data);
        //普通订单校验库存
        $this->checkStock($this->goods_data);

        $order_data = [
            //订单整体
            'order_no' => create_no(),
            'ip' => request()->ip(),
            'order_from' => $data['order_from'],
            'order_type' => OrderDict::TYPE,
            'status' => OrderDict::WAIT_PAY,
            'body' => $this->basic['body'],
            'member_id' => $data['member_id'],
            'goods_money' => $this->basic['goods_money'],
            'delivery_money' => $this->basic['delivery_money'] ?? 0,
            'discount_money' => $this->basic['discount_money'] ?? 0,
            'order_money' => $this->basic['order_money'],
            'pay_money' => $this->basic['order_money'],
            'has_goods_types' => $this->basic['has_goods_types'],//包含的商品形式
            'delivery_type' => $this->delivery['delivery_type'] ?? '',
            'taker_name' => $this->delivery['take_address']['taker_name'] ?? '',
            'taker_mobile' => $this->delivery['take_address']['taker_mobile'] ?? '',
            'taker_province' => $this->delivery['take_address']['taker_province'] ?? 0,
            'taker_city' => $this->delivery['take_address']['taker_city'] ?? 0,
            'taker_district' => $this->delivery['take_address']['taker_district'] ?? 0,
            'taker_address' => $this->delivery['take_address']['taker_address'] ?? '',
            'taker_full_address' => $this->delivery['take_address']['taker_full_address'] ?? '',
            'taker_longitude' => $this->delivery['take_address']['taker_longitude'] ?? '',
            'taker_latitude' => $this->delivery['take_address']['taker_latitude'] ?? '',
            'take_store_id' => $this->delivery['take_store']['store_id'] ?? 0,
            'member_remark' => $this->param['member_remark'] ?? '',//买家留言
            'relate_id' => $this->extend_data['relate_id'] ?? 0,//关联id
            'activity_type' => $this->extend_data['relate_source'] ?? '', // 活动类型
            'relate_order_id' => $this->extend_data['relate_order_id'] ?? 0,
            'relate_source' => $this->extend_data['relate_source'] ?? '',
            'out_trade_no' => isset($data['pay']) ? $data['pay']['out_trade_no'] : '',
        ];

        $order_goods_data = [];//项
        $write_goods_data = array_merge($this->goods_data, $this->impulse_buy_list);
        foreach ($write_goods_data as $v) {
            $order_goods_data[] = [
                'member_id' => $data['member_id'],
                'goods_id' => $v['goods_id'],
                'sku_id' => $v['sku_id'],
                'goods_name' => $v['goods']['goods_name'],
                'sku_name' => $v['sku_name'],
                'goods_image' => $v['goods']['goods_cover'],
                'sku_image' => $v['sku_image'],
                'price' => $v['price'],
                'original_price' => $v['original_price'] ?? 0,
                'num' => $v['num'],
                'goods_money' => $v['goods_money'],
                'goods_type' => $v['goods']['goods_type'],
                'order_id' => &$this->order_id,
                'discount_money' => $v['discount_money'] ?? 0,
                'status' => OrderGoodsDict::NORMAL,
                'extend' => $v['extend'] ?? '',
                'is_gift' => $v['is_gift'] ?? 0,
                'order_goods_money' => $v['goods_money']
            ];
        }

        $result = $this->model->transaction(function () use ($data, $order_data, $order_goods_data) {
            //生成订单
            $order = (new Order())->create($order_data);
            $this->order_id = $order['order_id'];
            //添加订单项目表
            $order_goods_model = new OrderGoods();
            $order_goods_model->insertAll($order_goods_data);
            $order_data['order_id'] = $this->order_id;
            $main_type = OrderLogDict::MEMBER;
            $main_id = $order_data['member_id'];
            //订单创建后事件
            CoreOrderEventService::orderCreateAfter([ 'order_id' => $this->order_id, 'order_data' => $order_data, 'order_goods_data' => $order_goods_data, 'cart_ids' => $this->cart_ids, 'basic' => get_object_vars($this), 'main_type' => $main_type, 'main_id' => $main_id, 'time' => time() ]);
            //支付

            $this->pay($data['pay'] ?? [], $order_data);

            $order_goods_ids = $order_goods_model->where(['order_id' => $this->order_id])->column("order_goods_id");
            return [
                'trade_type' => $order_data['order_type'],
                'order_id' => $this->order_id,
                'order_goods_id' => $order_goods_ids,
                'seckill_order_id' => $order_data['relate_order_id']
            ];
        });

        return $result;
    }


    public function pay($pay_info, $order_data)
    {
        $data = array(
            'out_trade_no' => $pay_info['out_trade_no'] ?? '',
            'trade_type' => 'shop',
            'trade_id' => $order_data['order_id'],
            'main_id' => $pay_info['main_id'] ?? '',
        );
        //秒杀推送的订单不添加交易记录
        // $pay_model = new \app\model\pay\Pay();
        // $pay_model->create($data);
        if ($order_data['pay_money'] > 0) {
            event('PaySuccess', ['out_trade_no' => $data['out_trade_no'], 'trade_type' => $data['trade_type'], 'trade_id' => $data['trade_id'], 'main_id' => $data['main_id']]);
        } else {
            (new CoreOrderPayService())->pay($data);
        }
    }

    public function checkMember($data): void
    {
        $this->delivery['take_address'] = $data['take_address'] ?? [];
        $member_info = (new CoreMemberService())->getInfoByMemberId( $data['member_id'], 'status');
        if (empty($member_info)) throw new CommonException(get_lang('SHOP_ORDER_BUYER_NOT_FOUND'));//无效的账号
        if ($member_info['status'] == MemberDict::OFF) throw new CommonException(get_lang('SHOP_ORDER_BUYER_LOCKED'));//账号被锁定
    }

    /**
     * 整理
     * @param array $data
     * @return void
     */
    public function calculate(array $data)
    {
        //参数赋值
        $this->delivery['take_address'] = $data['take_address'] ?? [];
        $this->order_key = $this->param['order_key'] ?? '';
        $is_need_recalculate = $data['is_need_recalculate'] ?? 0;
        if (empty($this->order_key) || $is_need_recalculate == 1) {
            $this->confirm($this->order_key);
        }
        if (in_array(GoodsDict::VIRTUAL, $this->basic['has_goods_types'])) {
            $this->delivery['delivery_type'] = OrderDeliveryDict::VIRTUAL;
        } else {
            $this->delivery['delivery_type'] = 'express';
        }

        //金额格式化
        $discount_money = $this->moneyFormat($this->basic['discount_money'] ?? 0);//优惠金额
        $delivery_money = $this->moneyFormat($this->param['delivery_money'] ?? 0);
        $goods_money = $this->moneyFormat($this->basic['goods_money'] ?? 0);
        $order_money = $this->moneyFormat($this->basic['order_money'] ?? 0);
        $order_money = $this->moneyFormat($this->moneyCalculate($order_money, $delivery_money, $goods_money, -$discount_money));
        $this->basic['discount_money'] = $discount_money;
        $this->basic['delivery_money'] = $delivery_money;
        $this->basic['goods_money'] = $goods_money;
        $this->basic['order_money'] = $order_money;
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
        $member_id = $this->param['member_id'];
        $this->member_id = $member_id;
        $member_info = (new CoreMemberService())->getInfoByMemberId($member_id, 'nickname, headimg, balance, point, member_level,member_label');
        if (empty($member_info)) throw new CommonException(get_lang('SHOP_ORDER_BUYER_NOT_FOUND'));//无效的账号
        // 查询会员等级信息
        $member_info['member_level_id'] = $member_info['member_level'];
        $member_info['member_level'] = (new MemberLevel())->where([['level_id', '=', $member_info['member_level']]])->field('level_id,level_benefits')->findOrEmpty()->toArray();
        //会员账户信息
        $this->buyer = $member_info;
        $order_config = (new CoreOrderConfigService())->getConfig() ?? [];
        $this->form_id = $order_config['form_id'];
        //查询商品信息
        $this->getGoodsData();
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
        $this->extend_data = $this->param['extend_data'] ?? [];
        $sku_data = $this->param['sku_data'] ?? [];
        if (empty($sku_data)) throw new CommonException(get_lang('SHOP_GOODS_DELISTED'));//无效的数据
        $sku_ids = array_column($sku_data, 'sku_id');
        $sku_condition = array(
            ['sku_id', 'in', $sku_ids]
        );
        $sku_list = (new  GoodsSku())->where($sku_condition)->with(['goods'])->field('sku_id, sku_name, sku_image, goods_id, price, stock, weight, volume,sku_id, sku_spec_format,member_price, sale_price')->select()->toArray();
        $sku_list = array_column($sku_list, null, 'sku_id');
        //商品数据  查询商品列表
        $order_data = [];
        $goods_list = [];
        $goods_money = 0;
        $total_num = 0;
        $body = '';
        //订单中包含的商品形式
        $has_goods_types = [];
        foreach ($sku_data as $v) {
            $sku_id = $v['sku_id'];
            $num = $v['num'];
            $total_num += $num;
            $market_type = $v['market_type'] ?? '';
            $market_type_id = $v['market_type_id'] ?? 0;
            $sku_info = $sku_list[$sku_id] ?? [];
            if (empty($sku_info)) throw new CommonException(get_lang('SHOP_GOODS_DELISTED'));//无效的商品
            $sku_info['member_discount'] = $sku_info['goods']['member_discount'] ?? '';
            //商品原价
            $sku_info['original_price'] = $sku_info['price'];
            //获取活动价格
            $sku_info['show_type'] = 'original_price';
            $sku_info['price'] = $v['seckill_price'];
            $sku_info['active_id'] = $v['seckill_id'] ?? '';
            $sku_info['discount_money'] = 0;
            $item_goods_type = $sku_info['goods']['goods_type'];

            // 商品限购处理
            if (isset($this->limit_buy['goods_' . $sku_info['goods_id']])) {
                $this->limit_buy['goods_' . $sku_info['goods_id']]['num'] += $num;
            } else {
                $this->limit_buy['goods_' . $sku_info['goods_id']] = [
                    'goods_id' => $sku_info['goods_id'],
                    'goods_name' => $sku_info['goods']['goods_name'],
                    'stock' => $sku_info['goods']['stock'],
                    'num' => $num,
                    'is_limit' => $sku_info['goods']['is_limit'],
                    'limit_type' => $sku_info['goods']['limit_type'],
                    'max_buy' => $sku_info['goods']['max_buy'],
                    'min_buy' => $sku_info['goods']['min_buy']
                ];
            }

            if (!in_array($item_goods_type, $has_goods_types)) $has_goods_types[] = $item_goods_type;

            $sku_info['num'] = $num;
            $sku_info['market_type'] = $market_type;//活动类型
            $sku_info['market_type_id'] = $market_type_id;//活动id

            // 计算商品小计
            $price = $sku_info['price'];
            $sku_info['goods_money'] = $price * $num;
            $goods_money += $sku_info['goods_money'];
            $body = $body ? $body . ($sku_info['sku_name'] . $sku_info['goods']['goods_name']) : ($sku_info['sku_name'] . $sku_info['goods']['goods_name']);
            $goods_list[$sku_id] = $sku_info;
        }

        $this->basic['has_goods_types'] = $has_goods_types;
        $this->basic['total_num'] = $total_num;
        $this->goods_data = $goods_list;
        $this->basic['goods_money'] = $goods_money;
        $this->basic['body'] = $body;
        return $order_data;
    }


}
