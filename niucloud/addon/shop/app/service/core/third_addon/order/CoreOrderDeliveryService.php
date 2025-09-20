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


use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDeliveryDict;

use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\service\core\order\CoreOrderCreateTrait;
use app\service\core\member\CoreMemberAddressService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 *  秒杀订单服务层
 */
class CoreOrderDeliveryService extends BaseCoreService
{

    use CoreOrderCreateTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 订单参数设置
     * @param $params
     * @return void
     */
    public function setParams($params)
    {
        $param = [
            'delivery' => [
                'delivery_type' => 'express',
            ],
            'take_address' => [
                'province_id' => $params['province_id'] ?? '',
                'city_id' => $params['city_id'] ?? '',
                'district_id' => $params['district_id'] ?? '',
            ],
            'sku_data' => [
                [
                    'sku_id' => $params['source_sku_id'],
                    'num' => $params['num'],
                ]
            ],
            'member_id'=>$params['member_id'] ?? ''
        ];

        if(empty($param['take_address']['province_id'])){
            // 获取默认地址
            $param['take_address'] = ( new CoreMemberAddressService() )->getDefaultAddressByMemberId($params['member_id']);
        }
        $this->member_id = $param['member_id'];
        $this->delivery = $param['take_address'];
        $this->param = $param;
        $this->extend_data = $this->param[ 'extend_data' ] ?? [];
    }


    /**
     * 计算运费
     * @param array $data
     * @return void
     */
    public function calculate(array $data)
    {
        //参数初始化
        $this->setParams($data);
        //查询商品信息
        $this->getGoodsData();
        if (in_array(GoodsDict::VIRTUAL, $this->basic['has_goods_types'])) {
            $this->delivery['delivery_type'] = OrderDeliveryDict::VIRTUAL;
        } else {
            $this->delivery['delivery_type'] = 'express';
        }
        $this->getDelivery();
        //计算运费
        $this->calculateDelivery();
        return $this->basic[ 'delivery_money' ];
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
            if (empty($sku_info) || empty($sku_info['goods'])) throw new CommonException(get_lang('SHOP_GOODS_DELISTED'));//无效的商品
            $sku_info['member_discount'] = $sku_info['goods']['member_discount'] ?? '';
            //商品原价
            $sku_info['original_price'] = $sku_info['price'];
            //获取活动价格
            $sku_info['show_type'] = 'original_price';
            $sku_info['discount_money'] = 0;
            $item_goods_type = $sku_info['goods']['goods_type'];

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
