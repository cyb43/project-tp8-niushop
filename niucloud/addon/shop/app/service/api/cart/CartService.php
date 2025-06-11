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

namespace addon\shop\app\service\api\cart;

use addon\shop\app\model\cart\Cart;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\service\api\marketing\ManjianService;
use addon\shop\app\service\core\goods\CoreGoodsActivePriceService;
use addon\shop\app\service\core\goods\CoreGoodsCartNumService;
use addon\shop\app\service\core\goods\CoreGoodsStatService;
use addon\shop\app\service\core\goods\CoreGoodsLimitBuyService;
use app\dict\member\MemberDict;
use app\service\core\member\CoreMemberService;
use core\base\BaseApiService;
use core\exception\ApiException;
use addon\shop\app\service\core\marketing\CoreManjianService;

/**
 * 购物车服务层
 * Class CartService
 */
class CartService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Cart();
    }

    /**
     * 添加购物车
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        $member_info = ( new CoreMemberService() )->getInfoByMemberId($this->member_id, 'status');

        if (empty($member_info)) throw new ApiException('SHOP_ORDER_BUYER_NOT_FOUND');//无效的账号
        if ($member_info[ 'status' ] == MemberDict::OFF) throw new ApiException('SHOP_ORDER_BUYER_LOCKED');//账号被锁定

        $data[ 'member_id' ] = $this->member_id;
        $info = $this->model->where([
            [ 'member_id', '=', $data[ 'member_id' ] ],
            [ 'goods_id', '=', $data[ 'goods_id' ] ],
            [ 'sku_id', '=', $data[ 'sku_id' ] ],
        ])->field('id,num')->findOrEmpty()->toArray();

//        $other_sku_num = $this->model->where([
//            [ 'member_id', '=', $this->member_id ],
//            [ 'goods_id', '=', $data[ 'goods_id' ] ],
//            [ 'sku_id', '<>', $data[ 'sku_id' ] ]
//        ])->sum('num');

        $goods_info = ( new Goods() )->where([
            [ 'goods_id', '=', $data[ 'goods_id' ] ],
        ])->field('goods_id,goods_name,stock,is_limit,limit_type,max_buy,min_buy')->findOrEmpty()->toArray();
        if (empty($goods_info)) throw new ApiException("SHOP_GOODS_NOT_EXIST");
        // 商品加入购物车统计
        CoreGoodsStatService::addStat([ 'goods_id' => $data[ 'goods_id' ], 'cart_num' => $data[ 'num' ] ]);
        ( new CoreGoodsCartNumService() )->inc([ 'goods_id' => $data[ 'goods_id' ], 'cart_num' => $data[ 'num' ] ]);

//        $core_goods_limit_buy_service = new CoreGoodsLimitBuyService();
        if (!empty($info)) {
            // 存在，更新数量
            $update = [
                'num' => $info[ 'num' ] + $data[ 'num' ]
            ];
            // 检测限购
//            $goods_info[ 'num' ] = $other_sku_num + $update[ 'num' ];
//            $error = $core_goods_limit_buy_service->checkGoodsLimitBuy($this->member_id, [ $goods_info ]);
//            if (!empty($error)) throw new ApiException($error);
            $this->model->where([ [ 'id', '=', $info[ 'id' ] ] ])->update($update);
            return $info[ 'id' ];
        } else {
            // 检测限购
//            $goods_info[ 'num' ] = $other_sku_num + $data[ 'num' ];
//            $error = $core_goods_limit_buy_service->checkGoodsLimitBuy($this->member_id, [ $goods_info ]);
//            if (!empty($error)) throw new ApiException($error);
            // 添加
            $res = $this->model->create($data);
            return $res->id;
        }
    }

    /**
     * 编辑购物车数量
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $member_info = ( new CoreMemberService() )->getInfoByMemberId($this->member_id, 'status');

        if (empty($member_info)) throw new ApiException('SHOP_ORDER_BUYER_NOT_FOUND');//无效的账号
        if ($member_info[ 'status' ] == MemberDict::OFF) throw new ApiException('SHOP_ORDER_BUYER_LOCKED');//账号被锁定

        $data[ 'member_id' ] = $this->member_id;
        $info = $this->model->where([
            [ 'id', '=', $data[ 'id' ] ],
            [ 'member_id', '=', $data[ 'member_id' ] ],
        ])->field('sku_id, goods_id, num')->findOrEmpty()->toArray();

        if (empty($info)) return false;

        $update = [
            'num' => $data[ 'num' ]
        ];

        if (!empty($data[ 'sku_id' ]) && $info[ 'sku_id' ] != $data[ 'sku_id' ]) {
            $update[ 'sku_id' ] = $data[ 'sku_id' ];

            $cart_info = $this->model->where([
                [ 'sku_id', '=', $data[ 'sku_id' ] ],
                [ 'member_id', '=', $this->member_id ],
            ])->field('id')->findOrEmpty()->toArray();

            if (!empty($cart_info)) {
                $this->model->where([ [ 'id', '=', $cart_info[ 'id' ] ] ])->delete();
            }
        }

        $goods_info = ( new Goods() )->where([
            [ 'goods_id', '=', $info[ 'goods_id' ] ],
        ])->field('goods_id,goods_name,stock,is_limit,limit_type,max_buy,min_buy')->findOrEmpty()->toArray();
        if (empty($goods_info)) throw new ApiException("SHOP_GOODS_NOT_EXIST");

//        $other_sku_num = $this->model->where([
//            [ 'member_id', '=', $this->member_id ],
//            [ 'goods_id', '=', $info[ 'goods_id' ] ],
//            [ 'sku_id', '<>', $info[ 'sku_id' ] ]
//        ])->sum('num');

        // 检测限购
//        if ($data[ 'num' ] > $info['num']) {
//            $goods_info[ 'num' ] = $other_sku_num + $update[ 'num' ];
//            $error = ( new CoreGoodsLimitBuyService() )->checkGoodsLimitBuy($this->member_id, [ $goods_info ]);
//            if (!empty($error)) throw new ApiException($error);
//        }

        $this->model->where([ [ 'id', '=', $data[ 'id' ] ] ])->update($update);

        // 商品加入购物车统计
        CoreGoodsStatService::addStat([ 'goods_id' => $info[ 'goods_id' ], 'cart_num' => ( $data[ 'num' ] - $info[ 'num' ] ) ]);
        ( new CoreGoodsCartNumService() )->inc([ 'goods_id' => $info[ 'goods_id' ], 'cart_num' => ( $data[ 'num' ] - $info[ 'num' ] ) ]);

        return true;
    }

    /**
     * 购物车删除，支持批量
     * @param $data
     * @return bool
     */
    public function del($data)
    {
        $this->model->where([ [ 'member_id', '=', $this->member_id ], [ 'id', 'in', $data[ 'ids' ] ] ])->delete();
        return true;
    }

    /**
     * 清空购物车
     * @return bool
     */
    public function clear()
    {
        $this->model->where([ [ 'member_id', '=', $this->member_id ] ])->delete();
        return true;
    }

    /**
     * 获取购物车列表
     * @param $data
     * @return array
     */
    public function getList($data)
    {
        $field = 'id, member_id, goods_id, sku_id, num, market_type, market_type_id, status, invalid_remark';
        $order = "create_time desc";
        $list = $this->model->where([ [ 'member_id', '=', $this->member_id ] ])->field($field)
            ->with([
                'goodsSku' => function($query) {
                    $query->withField('sku_id, sku_image, price, sale_price, stock,member_price');
                },
                'goods' => function($query) {
                    $query->withField('goods_id, status,delete_time,member_discount,is_discount');
                },
            ])->order($order)->select()->toArray();

        array_multisort(array_column($list, 'id'), SORT_ASC, $list);
        $goods_active_price_service = (new CoreGoodsActivePriceService());
        foreach ($list as $k => &$v) {
            if (!empty($v[ 'goodsSku' ])) {
                $v[ 'goodsSku' ][ 'member_discount' ] = $v[ 'goods' ][ 'member_discount' ] ?? '';
                //获取展示活动价格
                $show_price_data = $goods_active_price_service->getActivePrice($v[ 'goodsSku' ], $this->member_id);
                $v[ 'goodsSku' ][ 'show_price' ] = $show_price_data[ 'show_price' ];
                $v[ 'goodsSku' ][ 'show_type' ] = $show_price_data[ 'show_type' ];
            }
            if (!empty($v[ 'goods' ])) {
                // 限购查询当前会员已购数量
                $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($this->member_id, $v[ 'goods' ][ 'goods_id' ]);
                $v[ 'goods' ][ 'has_buy' ] = $has_buy;
            }
        }
        array_multisort(array_column($list, 'id'), SORT_DESC, $list);
        return $list;
    }

    /**
     * 获取购物车商品列表
     * @param $data
     * @return array
     */
    public function getGoodsList($data)
    {
        $field = 'id, member_id, goods_id, sku_id, num, market_type, market_type_id, status, invalid_remark';
        $order = "create_time desc";
        $list = $this->model->where([ [ 'member_id', '=', $this->member_id ] ])->field($field)
            ->with([
                'goods' => function($query) {
                    $query->withField('goods_id, goods_name, goods_type, sub_title, goods_cover, unit, stock, sale_num + virtual_sale_num as sale_num, is_limit,limit_type,max_buy,min_buy,status,delete_time,member_discount,label_ids,brand_id')->append([ 'goods_label_name', 'goods_brand' ]);
                },
                'goodsSku' => function($query) {
                    $query->withField('sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, is_default,member_price');
                },
                // 商品规格项/规格值列表
                'goodsSpec' => function($query) {
                    $query->field('spec_id, goods_id, spec_name, spec_values');
                },
            ])->order($order)->select()->toArray();


        array_multisort(array_column($list, 'id'), SORT_ASC, $list);
        $goods_active_price_service = (new CoreGoodsActivePriceService());
        $gift_goods = [];
        foreach ($list as $k => &$v) {
            if (!empty($v[ 'goodsSku' ])) {
                $v[ 'goodsSku' ][ 'member_discount' ] = $v[ 'goods' ][ 'member_discount' ] ?? '';
                //获取展示活动价格
                $show_price_data = $goods_active_price_service->getActivePrice($v[ 'goodsSku' ], $this->member_id);
                $v[ 'goodsSku' ][ 'show_price' ] = $show_price_data[ 'show_price' ];
                $v[ 'goodsSku' ][ 'show_type' ] = $show_price_data[ 'show_type' ];
            }
            if (!empty($v[ 'goods' ])) {
                // 限购查询当前会员已购数量
                $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($this->member_id, $v[ 'goods' ][ 'goods_id' ]);
                $v[ 'goods' ][ 'has_buy' ] = $has_buy;
            }

            $manjian_info = ( new ManjianService() )->getManjianInfo([ 'goods_id' => $v[ 'goods_id' ], 'sku_id' => $v[ 'sku_id' ], 'gift_goods' => $gift_goods ]);
            if (!empty($manjian_info[ 'gift_goods' ])) {
                $gift_goods = array_merge($gift_goods, $manjian_info[ 'gift_goods' ]);
            }
            $v[ 'manjian_info' ] = $manjian_info;
        }
        array_multisort(array_column($list, 'id'), SORT_DESC, $list);
        return $list;
    }

    /**
     * 购物车计算
     * @param $data
     * @return array
     */
    public function calculate($data)
    {
        //获取商品信息
        $data = $this->getSelectGoods($data);
        //满减
        $data = ( new CoreManjianService() )->manjianPromotion($data, $this->member_id);
        $promotion_money = $data[ 'promotion_money' ] ?? 0;
        $order_money = max($data['goods_money'] - $promotion_money, 0);
        $data[ 'order_money' ] = $order_money;

        $good_list = $data[ 'goods_list' ];
        $match_list = [];
        foreach ($good_list as $k => $v) {
            $match_list[ $k ][ 'goods_id' ] = $v[ 'goods_id' ];
            $match_list[ $k ][ 'sku_id' ] = $v[ 'sku_id' ];
            foreach ($v[ 'promotion' ] as $kk => $vv) {
                switch ($kk) {
                    case 'manjian':
                        if ($vv[ 'discount_array' ][ 'level' ] >= 0) {
                            $match_list[ $k ][ 'level' ] = $vv[ 'discount_array' ][ 'level' ];
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return [
            'goods_money' => $data[ 'goods_money' ],
            'promotion_money' => $promotion_money,
            'order_money' => $order_money,
            'match_list' => $match_list
        ];
    }

    /**
     * 获取购物车商品列表信息
     * @param $data
     * @return mixed
     */
    public function getSelectGoods($data)
    {
        $member_id = $this->member_id;

        $sku_ids = $data[ 'sku_ids' ] ?? [];
        $sku_id_list = array_column($sku_ids, 'sku_id');
        $sku_num_list = array_column($sku_ids, 'num', 'sku_id');
        //组装商品列表
        $field = 'goodsSku.sku_id, goodsSku.sku_name,  goodsSku.price, goodsSku.stock, goodsSku.goods_id, cart.id as cart_id,cart.num, goodsSku.member_price, goods.member_discount, goodsSku.sale_price';
        $condition = [
            [ 'goodsSku.sku_id', 'in', $sku_id_list ],
        ];
        $goods_list = ( new GoodsSku() )
            ->alias('goodsSku')
            ->field($field)
            ->where($condition)
            ->join('shop_cart cart', 'cart.sku_id = goodsSku.sku_id and cart.member_id = ' . $member_id)
            ->join('shop_goods goods', 'goods.goods_id = goodsSku.goods_id')
            ->select()
            ->toArray();

        $data[ 'goods_num' ] = 0;
        $data[ 'goods_money' ] = 0;
        $data[ 'goods_list' ] = [];
        $data[ 'coupon_money' ] = 0; //优惠券金额
        $data[ 'promotion_money' ] = 0; //优惠金额
        $goods_active_price_service = (new CoreGoodsActivePriceService());
        if (!empty($goods_list)) {
            foreach ($goods_list as $k => $v) {
                //获取展示活动价格
                $show_price_data = $goods_active_price_service->getActivePrice($v, $this->member_id);
                $v[ 'show_price' ] = $show_price_data[ 'show_price' ];
                $v[ 'show_type' ] = $show_price_data[ 'show_type' ];
                $item_num = $sku_num_list[ $v[ 'sku_id' ] ];
                $price = $v[ 'show_price' ];
                $v[ 'goods_money' ] = $price * $item_num;
                $v[ 'real_goods_money' ] = $v[ 'goods_money' ];
                $v[ 'promotion' ] = [];

                $data[ 'goods_num' ] += $item_num;
                $data[ 'goods_money' ] += $v[ 'goods_money' ];
                $data[ 'goods_list' ][] = $v;
            }
        }
        return $data;
    }

    /**
     * 获取购物车数量
     * @param $data
     * @return float
     */
    public function getSum($data)
    {
        $condition = [
            [ 'member_id', '=', $this->member_id ],
        ];
        if (!empty($data[ 'goods_id' ])) {
            $condition[] = [ 'goods_id', '=', $data[ 'goods_id' ] ];
        }
        return $this->model->where($condition)->sum('num');
    }

}
