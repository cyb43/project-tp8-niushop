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

namespace addon\shop\app\service\core\marketing;

use addon\shop\app\dict\active\ManjianDict;
use addon\shop\app\dict\coupon\CouponDict;
use addon\shop\app\dict\coupon\CouponMemberDict;
use addon\shop\app\dict\order\OrderDiscountDict;
use addon\shop\app\dict\order\OrderGoodsDict;
use addon\shop\app\model\coupon\Coupon;
use addon\shop\app\model\coupon\CouponMember;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\manjian\Manjian;
use addon\shop\app\model\manjian\ManjianGiveRecords;
use addon\shop\app\model\manjian\ManjianGoods;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use addon\shop\app\service\core\goods\CoreGoodsStockService;
use app\dict\member\MemberAccountTypeDict;
use app\model\member\Member;
use app\service\core\member\CoreMemberAccountService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\facade\Db;
use think\facade\Log;

/**
 * 满减服务层
 * Class CoreManjianService
 * @package addon\shop\app\service\api\marketing
 */
class CoreManjianService extends BaseCoreService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Manjian();
    }

    /**
     * 满减活动优惠计算
     * @param $order
     * @return void
     */
    public function calculate(&$order)
    {
        $manjian_discount_money = 0;

        //查询满减活动
        $manjian_list = $this->model->field('manjian_id,manjian_name,condition_type,goods_type,join_member_type,rule_type,rule_json,level_ids,label_ids')->where([
            [ 'status', '=', ManjianDict::ACTIVE ],
            [ 'start_time', '<=', time() ],
            [ 'end_time', '>', time() ]
        ])->select()->toArray();
        $member_info = ( new Member() )->field('member_level,member_label')->where([
            [ 'member_id', '=', $order->member_id ]
        ])->findOrEmpty()->toArray();

        if (!empty($manjian_list) && !empty($member_info)) {
            $gift_goods_all = [];//合并所有赠品计算剩余库存
            foreach ($manjian_list as &$value) {
                //判断会员是否有参与资格
                $can_join = 0;
                $join_member_type = $value[ 'join_member_type' ];
                $level_ids = $value[ 'level_ids' ];
                $label_ids = $value[ 'label_ids' ];
                if ($join_member_type == ManjianDict::ALL_MEMBER) {//所有会员参与
                    $can_join = 1;
                } elseif ($join_member_type == ManjianDict::SELECTED_MEMBER_LEVEL) {//指定会员等级
                    if (in_array($member_info[ 'member_level' ], $level_ids)) {
                        $can_join = 1;
                    }
                } elseif ($join_member_type == ManjianDict::SELECTED_MEMBER_LABEL) {//指定会员标签
                    if (!empty($member_info[ 'member_label' ]) && !empty(array_intersect($member_info[ 'member_label' ], $label_ids))) {
                        $can_join = 1;
                    }
                }
                if ($can_join == 0) continue;//不参与

                $condition_type = $value[ 'condition_type' ];
                $condition_match_value = 0;
                $condition_match_money = 0;
                $match_sku_ids = [];
                $match_order_goods_money = 0;
                foreach ($order->goods_data as $v) {
                    $manjian_goods_count = ( new ManjianGoods() )->where([
                        [ 'goods_id', '=', $v[ 'goods_id' ] ],
                        [ 'sku_id', '=', $v[ 'sku_id' ] ],
                        [ 'manjian_id', '=', $value[ 'manjian_id' ] ],
                        [ 'status', '=', ManjianDict::ACTIVE ]
                    ])->count();
                    if ($value[ 'goods_type' ] == ManjianDict::ALL_GOODS) {//全部商品参与
                        if ($condition_type == ManjianDict::OVER_N_YUAN) {//满N元
                            $condition_match_value += $v[ 'price' ] * $v[ 'num' ];
                        } else {//满N件
                            $condition_match_value += $v[ 'num' ];
                        }
                        $condition_match_money += $v[ 'price' ] * $v[ 'num' ];
                        $match_sku_ids[] = $v[ 'sku_id' ];
                        $match_order_goods_money += ( $v[ 'goods_money' ] - $v[ 'discount_money' ] );
                    } elseif ($value[ 'goods_type' ] == ManjianDict::SELECTED_GOODS) {//指定商品参与
                        if ($manjian_goods_count > 0) {
                            if ($condition_type == ManjianDict::OVER_N_YUAN) {//满N元
                                $condition_match_value += $v[ 'price' ] * $v[ 'num' ];
                            } else {//满N件
                                $condition_match_value += $v[ 'num' ];
                            }
                            $condition_match_money += $v[ 'price' ] * $v[ 'num' ];
                            $match_sku_ids[] = $v[ 'sku_id' ];
                            $match_order_goods_money += ( $v[ 'goods_money' ] - $v[ 'discount_money' ] );
                        }
                    } elseif ($value[ 'goods_type' ] == ManjianDict::SELECTED_GOODS_NOT) {//指定商品不参与
                        if ($manjian_goods_count == 0) {
                            if ($condition_type == ManjianDict::OVER_N_YUAN) {//满N元
                                $condition_match_value += $v[ 'price' ] * $v[ 'num' ];
                            } else {//满N件
                                $condition_match_value += $v[ 'num' ];
                            }
                            $condition_match_money += $v[ 'price' ] * $v[ 'num' ];
                            $match_sku_ids[] = $v[ 'sku_id' ];
                            $match_order_goods_money += ( $v[ 'goods_money' ] - $v[ 'discount_money' ] );
                        }
                    }
                }

                $discount_money = 0;//优惠金额
                $is_free_shipping = 0;//是否免邮
                $gift = [];//赠品
                $level = -1;//优惠层级

                $rule_type = $value[ 'rule_type' ];
                $rule_json = $value[ 'rule_json' ];
                foreach ($rule_json as $key => $rule) {
                    if ($condition_match_value >= $rule[ 'limit' ]) {
                        if ($rule_type == ManjianDict::LADDER) {//阶梯优惠
                            //满减优惠券
                            $discount_type = $rule[ 'discount_type' ];
                            if ($discount_type == 1) {//减
                                $discount_money = $rule[ 'discount_money' ] ?? 0;
                            } else {//折
                                $discount_money = $order->moneyFormat($condition_match_money * ( 1 - ( $rule[ 'discount_money' ] ?? 0 ) / 10 ));
                            }

                            if (!$rule[ 'is_discount' ]) {
                                $rule_json[ $key ][ 'discount_money' ] = 0;
                                $discount_money = 0;
                            }
                            //积分
                            $gift[ 'point' ] = $rule[ 'point' ] ?? 0;

                            if (!$rule[ 'is_give_point' ]) {
                                $rule_json[ $key ][ 'point' ] = 0;
                                $gift[ 'point' ] = 0;
                            }
                            //余额
                            $gift[ 'balance' ] = $rule[ 'balance' ] ?? 0;

                            if (!$rule[ 'is_give_balance' ]) {
                                $rule_json[ $key ][ 'balance' ] = 0;
                                $gift[ 'balance' ] = 0;
                            }
                            //优惠券
                            $gift_coupon = $rule[ 'coupon' ] ?? [];
                            if ($gift_coupon) {
                                foreach ($gift_coupon as $coupon_key => &$coupon) {
                                    $coupon_info = ( new Coupon() )->field('price,min_condition_money')->where([
                                        [ 'id', '=', $coupon[ 'coupon_id' ] ],
                                        [ 'status', '=', CouponDict::NORMAL ],
                                    ])->findOrEmpty()->toArray();
                                    if (!empty($coupon_info)) {
                                        if ($coupon_info[ 'min_condition_money' ] == '0.00') {
                                            $coupon_name = $coupon_info[ 'price' ] . "元无门槛券";
                                        } else {
                                            $coupon_name = "满" . $coupon_info[ 'min_condition_money' ] . "元减" . $coupon_info[ 'price' ] . "元券";
                                        }
                                        $coupon[ 'coupon_name' ] = $coupon_name;
                                    } else {
                                        unset($gift_coupon[ $coupon_key ]);
                                    }
                                }
                                $gift_coupon = array_values($gift_coupon);
                                $rule_json[ $key ][ 'coupon' ] = $gift_coupon;
                            }
                            $gift[ 'coupon' ] = $gift_coupon;

                            if (!$rule[ 'is_give_coupon' ]) {
                                $rule_json[ $key ][ 'coupon' ] = [];
                                $gift[ 'coupon' ] = [];
                            }
                            //赠品
                            $gift_goods = $rule[ 'goods' ] ?? [];
                            if ($gift_goods) {
                                foreach ($gift_goods as $goods_key => &$goods) {
                                    $sku_info = ( new GoodsSku() )->field('goods_id,sku_name,sku_image,price,stock')->where([
                                        [ 'goods_id', '=', $goods[ 'goods_id' ] ],
                                        [ 'sku_id', '=', $goods[ 'sku_id' ] ],
                                        [ 'stock', '>', 0 ],
                                    ])->with([ 'goods' ])->findOrEmpty()->toArray();
                                    if (!empty($sku_info) && !empty($sku_info[ 'goods' ]) && $sku_info[ 'goods' ][ 'status' ] == 1) {
                                        $goods[ 'goods_name' ] = $sku_info[ 'goods' ][ 'goods_name' ];
                                        $goods[ 'sku_name' ] = $sku_info[ 'sku_name' ];
                                        $goods[ 'sku_image' ] = $sku_info[ 'sku_image' ];
                                        $goods[ 'price' ] = $sku_info[ 'price' ];

                                        if (empty($gift_goods_all)) {
                                            // 库存小于赠送数量时，取剩余库存
                                            $num = $sku_info[ 'stock' ] >= $goods[ 'num' ] ? $goods[ 'num' ] : $sku_info[ 'stock' ];
                                            $goods[ 'num' ] = $num;
                                        } else {
                                            // 计算剩余库存
                                            $leave_num = $sku_info[ 'stock' ];
                                            foreach ($gift_goods_all as $gift_goods_item) {
                                                if ($gift_goods_item[ 'goods_id' ] == $goods[ 'goods_id' ] && $gift_goods_item[ 'sku_id' ] == $goods[ 'sku_id' ]) {
                                                    if ($leave_num - $gift_goods_item[ 'num' ] >= 0) {
                                                        $leave_num = $leave_num - $gift_goods_item[ 'num' ];
                                                    } else {
                                                        $leave_num = 0;
                                                    }
                                                }
                                            }
                                            $goods[ 'num' ] = $leave_num >= $goods[ 'num' ] ? $goods[ 'num' ] : $leave_num;
                                        }

                                        if ($goods[ 'num' ] == 0) {
                                            unset($gift_goods[ $goods_key ]);
                                        }

                                    } else {
                                        unset($gift_goods[ $goods_key ]);
                                    }
                                }
                                $gift_goods = array_values($gift_goods);
                                $gift_goods_all = array_merge($gift_goods_all, $gift_goods);
                                $rule_json[ $key ][ 'goods' ] = $gift_goods;
                            }
                            $gift[ 'goods' ] = $gift_goods;

                            if (!$rule[ 'is_give_goods' ]) {
                                $rule_json[ $key ][ 'goods' ] = [];
                                $gift[ 'goods' ] = [];
                            }
                        } else {//循环优惠
                            $cycle_num = intdiv($condition_match_value, $rule[ 'limit' ]);
                            $rule_json[ $key ][ 'limit' ] = $rule[ 'limit' ] * $cycle_num;
                            if ($rule[ 'is_discount' ]) {
                                if ($rule[ 'limit' ] == 0) {
                                    $discount_money = 0;
                                } else {
                                    $discount_money = $order->moneyFormat($cycle_num * ( $rule[ 'discount_money' ] ?? 0 ));
                                }
                                $rule_json[ $key ][ 'discount_money' ] = $discount_money;
                            }
                            if ($rule[ 'is_give_point' ]) {
                                $rule_json[ $key ][ 'point' ] = ( $rule[ 'point' ] ?? 0 ) * $cycle_num;
                                $gift[ 'point' ] = ( $rule[ 'point' ] ?? 0 ) * $cycle_num;
                            }
                            if ($rule[ 'is_give_balance' ]) {
                                $rule_json[ $key ][ 'balance' ] = ( $rule[ 'balance' ] ?? 0 ) * $cycle_num;
                                $gift[ 'balance' ] = ( $rule[ 'balance' ] ?? 0 ) * $cycle_num;
                            }
                            if ($rule[ 'is_give_coupon' ]) {
                                $gift_coupon = $rule[ 'coupon' ] ?? [];
                                if ($gift_coupon) {
                                    foreach ($gift_coupon as $coupon_key => &$coupon) {
                                        $coupon[ 'num' ] = intval($coupon[ 'num' ]) * $cycle_num;
                                        $coupon_info = ( new Coupon() )->field('price,min_condition_money')->where([
                                            [ 'id', '=', $coupon[ 'coupon_id' ] ],
                                            [ 'status', '=', CouponDict::NORMAL ],
                                        ])->findOrEmpty()->toArray();
                                        if (!empty($coupon_info)) {
                                            if ($coupon_info[ 'min_condition_money' ] == '0.00') {
                                                $coupon_name = $coupon_info[ 'price' ] . "元无门槛券";
                                            } else {
                                                $coupon_name = "满" . $coupon_info[ 'min_condition_money' ] . "元减" . $coupon_info[ 'price' ] . "元券";
                                            }
                                            $coupon[ 'coupon_name' ] = $coupon_name;
                                        } else {
                                            unset($gift_coupon[ $coupon_key ]);
                                        }
                                    }
                                    $gift_coupon = array_values($gift_coupon);
                                    $rule_json[ $key ][ 'coupon' ] = $gift_coupon;
                                }
                                $gift[ 'coupon' ] = $gift_coupon;
                            }
                            if ($rule[ 'is_give_goods' ]) {
                                $gift_goods = $rule[ 'goods' ] ?? [];
                                if ($gift_goods) {
                                    foreach ($gift_goods as $goods_key => &$goods) {
                                        $goods[ 'num' ] = intval($goods[ 'num' ]) * $cycle_num;
                                        $sku_info = ( new GoodsSku() )->field('goods_id,sku_name,sku_image,price,stock')->where([
                                            [ 'goods_id', '=', $goods[ 'goods_id' ] ],
                                            [ 'sku_id', '=', $goods[ 'sku_id' ] ],
                                            [ 'stock', '>', 0 ],
                                        ])->with([ 'goods' ])->findOrEmpty()->toArray();
                                        if (!empty($sku_info) && !empty($sku_info[ 'goods' ]) && $sku_info[ 'goods' ][ 'status' ] == 1) {
                                            $goods[ 'goods_name' ] = $sku_info[ 'goods' ][ 'goods_name' ];
                                            $goods[ 'sku_name' ] = $sku_info[ 'sku_name' ];
                                            $goods[ 'sku_image' ] = $sku_info[ 'sku_image' ];
                                            $goods[ 'price' ] = $sku_info[ 'price' ];

                                            if (empty($gift_goods_all)) {
                                                // 库存小于赠送数量时，取剩余库存
                                                $num = $sku_info[ 'stock' ] >= $goods[ 'num' ] ? $goods[ 'num' ] : $sku_info[ 'stock' ];
                                                $goods[ 'num' ] = $num;
                                            } else {
                                                // 计算剩余库存
                                                $leave_num = $sku_info[ 'stock' ];
                                                foreach ($gift_goods_all as $gift_goods_item) {
                                                    if ($gift_goods_item[ 'goods_id' ] == $goods[ 'goods_id' ] && $gift_goods_item[ 'sku_id' ] == $goods[ 'sku_id' ]) {
                                                        if ($leave_num - $gift_goods_item[ 'num' ] >= 0) {
                                                            $leave_num = $leave_num - $gift_goods_item[ 'num' ];
                                                        } else {
                                                            $leave_num = 0;
                                                        }
                                                    }
                                                }
                                                $goods[ 'num' ] = $leave_num >= $goods[ 'num' ] ? $goods[ 'num' ] : $leave_num;
                                            }

                                            if ($goods[ 'num' ] == 0) {
                                                unset($gift_goods[ $goods_key ]);
                                            }

                                        } else {
                                            unset($gift_goods[ $goods_key ]);
                                        }
                                    }
                                    $gift_goods = array_values($gift_goods);
                                    $gift_goods_all = array_merge($gift_goods_all, $gift_goods);
                                    $rule_json[ $key ][ 'goods' ] = $gift_goods;
                                }
                                $gift[ 'goods' ] = $gift_goods;
                            }
                        }
                        $is_free_shipping = $rule[ 'is_free_shipping' ] ?? 0;
                        $level = $key;
                    }
                }

                if ($level >= 0) {
                    foreach ($match_sku_ids as $sku_id) {
                        $value[ 'rule' ] = $rule_json[ $level ];
                        if ($value[ 'rule' ][ 'is_discount' ] || $value[ 'rule' ][ 'is_free_shipping' ] || $value[ 'rule' ][ 'is_give_point' ] || $value[ 'rule' ][ 'is_give_balance' ] || !empty($value[ 'rule' ][ 'coupon' ]) || !empty($value[ 'rule' ][ 'goods' ])) {
                            $order->goods_data[ $sku_id ][ 'manjian_info' ] = $value;
                        }
                    }
                }

                //填充满减商品优惠信息
                $manjian_one_discount_money = 0;
                if ($level >= 0) {
                    $this->manjianGoodsCalculate($order, $is_free_shipping, $discount_money, $match_sku_ids, $match_order_goods_money, $manjian_one_discount_money);
                }
                if ($manjian_one_discount_money > 0) {
                    $manjian_discount_money = bcadd($manjian_discount_money, $manjian_one_discount_money, 2);
                    $order->discount[ 'manjian' ][ $value[ 'manjian_id' ] ] = $order->discountFormat(
                        $match_sku_ids,
                        OrderDiscountDict::DISCOUNT,
                        count($match_sku_ids),
                        $manjian_one_discount_money,
                        'manjian',
                        $value[ 'manjian_id' ],
                        '',
                        $value[ 'manjian_name' ]
                    );
                }
                //获取满减活动商品赠品内容
                if (!empty($gift)) {
                    $this->getGiftContent($order, $gift, $value[ 'manjian_id' ], $level, $match_sku_ids);
                }
            }

            if ($manjian_discount_money > 0) {
                $order->basic[ 'discount_money' ] = bcadd($order->basic[ 'discount_money' ], $manjian_discount_money, 2);
                $order->basic[ 'manjian_discount_money' ] = $order->moneyFormat($manjian_discount_money);
            }
        }
    }

    /**
     * 满减活动商品计算
     * @param $order
     * @param $is_free_shipping
     * @param $discount_money
     * @param $match_sku_ids
     * @param $match_order_goods_money
     * @param $manjian_one_discount_money
     * @return void
     */
    public function manjianGoodsCalculate(&$order, $is_free_shipping, $discount_money, $match_sku_ids, $match_order_goods_money, &$manjian_one_discount_money)
    {
        if ($discount_money > $match_order_goods_money) {//优惠金额大于订单商品总金额，则优惠金额等于订单商品总金额
            $discount_money = $match_order_goods_money;
        }
        $surplus_money = $discount_money;
        $match_sku_ids = array_filter($match_sku_ids, function ($item) use ($order) {
            return ( $order->goods_data[ $item ][ 'goods_money' ] - $order->goods_data[ $item ][ 'discount_money' ] ) !== 0;
        });
        $match_count = count($match_sku_ids);
        foreach ($match_sku_ids as $sku_key => $sku_id) {
            $item_order_goods_money = $order->goods_data[ $sku_id ][ 'goods_money' ] - $order->goods_data[ $sku_id ][ 'discount_money' ];
            if ($is_free_shipping) {
                $order->goods_data[ $sku_id ][ 'goods' ][ 'is_free_shipping' ] = 1;
                $order->goods_data[ $sku_id ][ 'goods' ][ 'delivery_money' ] = 0;
            }

            //订单项满减优惠计算
            if ($sku_key == ( $match_count - 1 )) {
                $item_discount_money = $surplus_money;
            } else {
                if ($match_order_goods_money == 0 || $discount_money == 0) {
                    $item_discount_money = 0;
                } else {
                    $item_discount_money = $order->moneyFormat($item_order_goods_money / $match_order_goods_money * $discount_money);
                    if ($item_discount_money == 0) {
                        $item_discount_money = $item_order_goods_money;
                    }
                    if ($item_discount_money > $surplus_money) {
                        $item_discount_money = $surplus_money;
                    }
                }
            }
            $order->goods_data[ $sku_id ][ 'discount_money' ] = bcadd($order->goods_data[ $sku_id ][ 'discount_money' ], $item_discount_money, 2);
            $manjian_one_discount_money = bcadd($manjian_one_discount_money, $item_discount_money, 2);
            $surplus_money = bcsub($surplus_money, $item_discount_money, 2);
        }
    }

    /**
     * 满减活动商品赠品内容
     * @param $order
     * @param $gift
     * @param $manjian_id
     * @param $level
     * @param $sku_ids
     * @return void
     */
    public function getGiftContent(&$order, $gift, $manjian_id, $level, $sku_ids)
    {
        foreach ($gift as $k => $v) {
            switch ($k) {
                case 'balance':
                case 'point':
                    if ($v > 0) {
                        $gift[ $k ] = $v;
                    } else {
                        unset($gift[ $k ]);
                    }
                    break;
                case 'coupon':
                    if (!empty($v) && is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            $coupon_count = ( new Coupon() )->where([
                                [ 'id', '=', $vv[ 'coupon_id' ] ],
                                [ 'status', '=', CouponDict::NORMAL ],
                            ])->count();
                            if ($coupon_count == 0) {
                                unset($v[ $kk ]);
                            }
                        }
                        $v = array_values($v);
                        if (!empty($v) && is_array($v)) {
                            $gift[ $k ] = $v;
                        } else {
                            unset($gift[ $k ]);
                        }
                    }
                    break;
                case 'goods':
                    if (!empty($v) && is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            $sku_info = ( new GoodsSku() )->field('sku_id, sku_name, sku_image, goods_id, price, stock, weight, volume,sku_id, sku_spec_format,member_price, sale_price')->where([
                                [ 'goods_id', '=', $vv[ 'goods_id' ] ],
                                [ 'sku_id', '=', $vv[ 'sku_id' ] ],
                                [ 'stock', '>', 0 ],
                            ])->with([ 'goods' ])->findOrEmpty()->toArray();
                            if (!empty($sku_info) && !empty($sku_info[ 'goods' ]) && $sku_info[ 'goods' ][ 'status' ] == 1) {
                                if (isset($order->gift_goods[ $vv[ 'sku_id' ] ])) {
                                    $order->gift_goods[ $vv[ 'sku_id' ] ][ 'num' ] += $vv[ 'num' ];
                                } else {
                                    $sku_info[ 'original_price' ] = $sku_info[ 'price' ];
                                    $sku_info[ 'price' ] = 0;
                                    $sku_info[ 'num' ] = $vv[ 'num' ];
                                    $sku_info[ 'goods_money' ] = 0;
                                    $sku_info[ 'discount_money' ] = 0;
                                    $sku_info[ 'is_gift' ] = 1;
                                    $order->gift_goods[ $vv[ 'sku_id' ] ] = $sku_info;
                                }
                            } else {
                                unset($v[ $kk ]);
                            }
                        }
                        $v = array_values($v);
                        if (!empty($v) && is_array($v)) {
                            $gift[ $k ] = $v;
                        } else {
                            unset($gift[ $k ]);
                        }
                    }
                    break;
            }
        }
        if (!empty($gift)) {
            $order->gift[ 'manjian' ][ $manjian_id ][ 'gift' ] = $gift;
            $order->gift[ 'manjian' ][ $manjian_id ][ 'level' ] = $level;
            $order->gift[ 'manjian' ][ $manjian_id ][ 'sku_ids' ] = $sku_ids;
        }
    }

    /**
     * 添加满减活动赠送记录
     * @param $data
     * @return true
     */
    public function addGiveRecords($data)
    {
        $discount = $data[ 'basic' ][ 'gift' ];
        $order_data = $data[ 'order_data' ];
        if (!empty($discount) && !empty($discount[ 'manjian' ])) {
            $manjian_give_records_model = new ManjianGiveRecords();
            $core_manjian_stat_service = new CoreManjianStatService();
            foreach ($discount[ 'manjian' ] as $manjian_id => $v) {
                if (!empty($v[ 'gift' ])) {
                    $gift = $v[ 'gift' ];
                    $level = $v[ 'level' ];
                    $sku_ids = $v[ 'sku_ids' ];
                    $coupon_json = [];
                    if (!empty($gift[ 'coupon' ])) {
                        foreach ($gift[ 'coupon' ] as $vv) {
                            $coupon_json[] = [
                                'coupon_id' => $vv[ 'coupon_id' ],
                                'num' => $vv[ 'num' ]
                            ];
                        }
                    }
                    $goods_json = [];
                    if (!empty($gift[ 'goods' ])) {
                        foreach ($gift[ 'goods' ] as $vv) {
                            $goods_json[] = [
                                'goods_id' => $vv[ 'goods_id' ],
                                'sku_id' => $vv[ 'sku_id' ],
                                'num' => $vv[ 'num' ]
                            ];
                        }
                    }
                    $give_data[] = [
                        'member_id' => $order_data[ 'member_id' ],
                        'order_id' => $order_data[ 'order_id' ],
                        'manjian_id' => $manjian_id,
                        'level' => $level,
                        'point' => $gift[ 'point' ] ?? 0,
                        'balance' => $gift[ 'balance' ] ?? 0,
                        'coupon_json' => $coupon_json,
                        'goods_json' => $goods_json,
                        'sku_ids' => $sku_ids
                    ];
                }
                //会员满减活动参与次数
                $join_count = $manjian_give_records_model->where([
                    [ 'member_id', '=', $order_data[ 'member_id' ] ],
                    [ 'manjian_id', '=', $manjian_id ],
                ])->count();
                //满减活动数据统计
                $stat_data = [
                    'order_num' => 1,
                    'member_num' => $join_count == 0 ? 1 : 0,
                ];
                $core_manjian_stat_service->stat($manjian_id, $stat_data);
            }
            if (!empty($give_data)) {
                $manjian_give_records_model->saveAll($give_data);
            }
        }
        return true;
    }

    /**
     * 满减活动赠品发放
     * @param $data
     * @return true
     */
    public function giftGrant($data)
    {
        $give_records = ( new ManjianGiveRecords() )->where([
            [ 'order_id', '=', $data[ 'order_id' ] ],
            [ 'member_id', '=', $data[ 'member_id' ] ],
        ])->field('member_id,manjian_id,point,balance,coupon_json,goods_json')->select()->toArray();
        Log::write('满减送活动赠品发放:' . json_encode($give_records));
        if (!empty($give_records)) {
            $core_member_account = new CoreMemberAccountService();
            $core_coupon_member_service = new CoreCouponMemberService();
            $core_manjian_stat_service = new CoreManjianStatService();
            foreach ($give_records as $v) {
                //会员积分发放
                if ($v[ 'point' ] > 0) {
                    $core_member_account->addLog( $v[ 'member_id' ], MemberAccountTypeDict:: POINT, $v[ 'point' ], 'manjian_gift_give', '满减送活动赠送', $v[ 'manjian_id' ]);
                }
                //会员余额发放
                if ($v[ 'balance' ] > 0) {
                    $core_member_account->addLog($v[ 'member_id' ], MemberAccountTypeDict:: BALANCE, $v[ 'balance' ], 'manjian_gift_give', '满减送活动赠送', $v[ 'manjian_id' ]);
                }
                if (!empty($v[ 'coupon_json' ])) {
                    $coupon_num = 0;
                    foreach ($v[ 'coupon_json' ] as $coupon) {
                        try {
                            $core_coupon_member_service->sendCoupon( $v[ 'member_id' ], $coupon[ 'coupon_id' ], $coupon[ 'num' ]);
                            $coupon_num += $coupon[ 'num' ];
                        } catch (CommonException $e) {
                            Log::write('满减赠送优惠券“' . $coupon[ 'coupon_id' ] . '”发放失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
                        }
                    }
                }
                if (!empty($v[ 'goods_json' ])) {
                    $nums = array_column($v[ 'goods_json' ], 'num');
                    $goods_num = array_sum($nums);
                }

                //满减活动数据统计
                $stat_data = [
                    'order_money' => $data[ 'order_money' ],
                    'point' => $v[ 'point' ],
                    'balance' => $v[ 'balance' ],
                    'coupon_num' => $coupon_num ?? 0,
                    'goods_num' => $goods_num ?? 0
                ];
                $core_manjian_stat_service->stat($v[ 'manjian_id' ], $stat_data);
            }
        }
        return true;
    }

    /**
     * 满减活动退款校验
     * @return array
     */
    public function refundCheck($data)
    {
        $order_goods_model = new OrderGoods();
        //查询订单是否全部退款
        $order_goods_list = $order_goods_model->where([
            [ 'order_id', '=', $data[ 'order_id' ] ],
            [ 'member_id', '=', $data[ 'member_id' ] ],
            [ 'status', '<>', OrderGoodsDict::REFUND_FINISH ],
            [ 'is_gift', '=', 0 ]
        ])->column('order_goods_id,goods_id,sku_id,num,goods_money,extend', 'order_goods_id');

        $give_records = ( new ManjianGiveRecords() )->where([
            [ 'order_id', '=', $data[ 'order_id' ] ],
            [ 'member_id', '=', $data[ 'member_id' ] ],
        ])->field('manjian_id,level,point,balance,coupon_json,goods_json')->select()->toArray();

        $refund_gift_list = [];
        foreach ($give_records as $value) {
            $manjian_info = $this->model->where([
                [ 'manjian_id', '=', $value[ 'manjian_id' ] ]
            ])->findOrEmpty()->toArray();
            if (empty($manjian_info)) continue;

            $condition_type = $manjian_info[ 'condition_type' ];
            $condition_match_value = 0;

            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $v) {
                    $manjian_goods_count = ( new ManjianGoods() )->where([
                        [ 'goods_id', '=', $v[ 'goods_id' ] ],
                        [ 'sku_id', '=', $v[ 'sku_id' ] ],
                        [ 'manjian_id', '=', $value[ 'manjian_id' ] ],
                        [ 'status', '=', ManjianDict::ACTIVE ]
                    ])->count();
                    if ($manjian_info[ 'goods_type' ] == ManjianDict::ALL_GOODS) {//全部商品参与
                        if ($condition_type == ManjianDict::OVER_N_YUAN) {//满N元
                            $condition_match_value += $v[ 'goods_money' ];
                        } else {//满N件
                            $condition_match_value += $v[ 'num' ];
                        }

                    } elseif ($manjian_info[ 'goods_type' ] == ManjianDict::SELECTED_GOODS) {//指定商品参与
                        if ($manjian_goods_count > 0) {
                            if ($condition_type == ManjianDict::OVER_N_YUAN) {//满N元
                                $condition_match_value += $v[ 'goods_money' ];
                            } else {//满N件
                                $condition_match_value += $v[ 'num' ];
                            }

                        }
                    } elseif ($manjian_info[ 'goods_type' ] == ManjianDict::SELECTED_GOODS_NOT) {//指定商品不参与
                        if ($manjian_goods_count == 0) {
                            if ($condition_type == ManjianDict::OVER_N_YUAN) {//满N元
                                $condition_match_value += $v[ 'goods_money' ];
                            } else {//满N件
                                $condition_match_value += $v[ 'num' ];
                            }
                        }
                    }
                }
            }

            $gift = [];//赠品
            $level = -1;//优惠层级
            $rule_type = $manjian_info[ 'rule_type' ];
            $rule_json = $manjian_info[ 'rule_json' ];
            foreach ($rule_json as $key => $rule) {
                if ($condition_match_value >= $rule[ 'limit' ]) {
                    if ($rule_type == ManjianDict::LADDER) {//阶梯优惠
                        //积分
                        $gift[ 'point' ] = $rule[ 'point' ] ?? 0;

                        if (!$rule[ 'is_give_point' ]) {
                            $gift[ 'point' ] = 0;
                        }
                        //余额
                        $gift[ 'balance' ] = $rule[ 'balance' ] ?? 0;

                        if (!$rule[ 'is_give_balance' ]) {
                            $gift[ 'balance' ] = 0;
                        }
                        //优惠券
                        $gift[ 'coupon' ] = $rule[ 'coupon' ] ?? [];

                        if (!$rule[ 'is_give_coupon' ]) {
                            $gift[ 'coupon' ] = [];
                        }
                        //赠品
                        $gift[ 'goods' ] = $rule[ 'goods' ] ?? [];

                        if (!$rule[ 'is_give_goods' ]) {
                            $gift[ 'goods' ] = [];
                        }
                    } else {//循环优惠
                        $cycle_num = intdiv($condition_match_value, $rule[ 'limit' ]);
                        if ($rule[ 'is_give_point' ]) $gift[ 'point' ] = ( $rule[ 'point' ] ?? 0 ) * $cycle_num;
                        if ($rule[ 'is_give_balance' ]) $gift[ 'balance' ] = ( $rule[ 'balance' ] ?? 0 ) * $cycle_num;
                        if ($rule[ 'is_give_coupon' ]) {
                            $gift_coupon = $rule[ 'coupon' ] ?? [];
                            if ($gift_coupon) {
                                foreach ($gift_coupon as &$coupon) {
                                    $coupon[ 'num' ] = intval($coupon[ 'num' ]) * $cycle_num;
                                }
                            }
                            $gift[ 'coupon' ] = $gift_coupon;
                        }
                        if ($rule[ 'is_give_goods' ]) {
                            $gift_goods = $rule[ 'goods' ] ?? [];
                            if ($gift_goods) {
                                foreach ($gift_goods as &$goods) {
                                    $goods[ 'num' ] = intval($goods[ 'num' ]) * $cycle_num;
                                }
                            }
                            $gift[ 'goods' ] = $gift_goods;
                        }
                    }
                    $level = $key;
                }
            }

            if ($level < $value[ 'level' ] || empty($order_goods_list)) {//优惠层级降级或订单全部退款,退还全部赠品
                if ($value[ 'point' ] > 0) {
                    $refund_gift_list[ $value[ 'manjian_id' ] ][ 'point' ] = $value[ 'point' ];
                }
                if ($value[ 'balance' ] > 0) {
                    $refund_gift_list[ $value[ 'manjian_id' ] ][ 'balance' ] = $value[ 'balance' ];
                }
                if (!empty($value[ 'coupon_json' ])) {
                    $refund_gift_list[ $value[ 'manjian_id' ] ][ 'coupon' ] = $value[ 'coupon_json' ];
                }
                if (!empty($value[ 'goods_json' ])) {
                    $refund_gift_list[ $value[ 'manjian_id' ] ][ 'goods' ] = $value[ 'goods_json' ];
                }
            } else {
                if ($rule_type == ManjianDict::CYCLE) {//循环优惠
                    if ($value[ 'point' ] > 0) {
                        if (!empty($gift) && !empty($gift[ 'point' ])) {
                            if ($value[ 'point' ] > $gift[ 'point' ]) {
                                $value[ 'point' ] -= $gift[ 'point' ];
                                $refund_gift_list[ $value[ 'manjian_id' ] ][ 'point' ] = $value[ 'point' ];
                            }
                        }
                    }
                    if ($value[ 'balance' ] > 0) {
                        if (!empty($gift) && !empty($gift[ 'balance' ])) {
                            if ($value[ 'balance' ] > $gift[ 'balance' ]) {
                                $value[ 'balance' ] -= $gift[ 'balance' ];
                                $refund_gift_list[ $value[ 'manjian_id' ] ][ 'balance' ] = $value[ 'balance' ];
                            }
                        }
                    }
                    if (!empty($value[ 'coupon_json' ])) {
                        foreach ($value[ 'coupon_json' ] as $coupon) {
                            if (!empty($gift) && !empty($gift[ 'coupon' ])) {
                                if ($coupon[ 'num' ] > $gift[ 'coupon' ][ 'num' ]) {
                                    $coupon[ 'num' ] -= $gift[ 'coupon' ][ 'num' ];
                                    $refund_gift_list[ $value[ 'manjian_id' ] ][ 'coupon' ][] = $coupon;
                                }
                            }
                        }
                    }
                    if (!empty($value[ 'goods_json' ])) {
                        foreach ($value[ 'goods_json' ] as $goods) {
                            if (!empty($gift) && !empty($gift[ 'goods' ])) {
                                if ($goods[ 'num' ] > $gift[ 'goods' ][ 'num' ]) {
                                    $goods[ 'num' ] -= $gift[ 'goods' ][ 'num' ];
                                    $refund_gift_list[ $value[ 'manjian_id' ] ][ 'goods' ][] = $goods;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $refund_gift_list;
    }

    /**
     * 订单退款后退还赠品
     * @return true
     */
    public function refundGift($data)
    {
        $refund_gift_list = $this->refundCheck($data);
        if (!empty($refund_gift_list)) {
            $core_member_account = new CoreMemberAccountService();
            $core_coupon_member_service = new CoreCouponMemberService();
            $coupon_member_model = new CouponMember();
            $member_model = new Member();
            $coupon_model = new Coupon();
            $order_goods_model = new OrderGoods();
            $core_goods_stock_service = new CoreGoodsStockService();
            $manjian_give_records_service = new ManjianGiveRecords();
            $core_manjian_stat_service = new CoreManjianStatService();
            foreach ($refund_gift_list as $manjian_id => $v) {
                $manjian_give_where = [
                    [ 'member_id', '=', $data[ 'member_id' ] ],
                    [ 'order_id', '=', $data[ 'order_id' ] ],
                    [ 'manjian_id', '=', $manjian_id ],
                ];
                $give_records = $manjian_give_records_service->field('coupon_json,goods_json')->where($manjian_give_where)->findOrEmpty()->toArray();
                $member_info = $member_model->field('point,balance')->where([
                    [ 'member_id', '=', $data[ 'member_id' ] ],
                ])->findOrEmpty()->toArray();
                if (isset($v[ 'point' ]) && $v[ 'point' ] > 0) {
                    //会员积分扣除
                    if ($member_info[ 'point' ] >= $v[ 'point' ]) {//剩余积分足够才扣除
                        $core_member_account->addLog( $data[ 'member_id' ], MemberAccountTypeDict:: POINT, -$v[ 'point' ], 'manjian_gift_back', '满减送活动订单退款退还赠送积分', $manjian_id);
                        $manjian_give_records_service->where($manjian_give_where)->update([ 'point' => Db::raw('point - ' . $v[ 'point' ]) ]);
                    }
                }
                if (isset($v[ 'balance' ]) && $v[ 'balance' ] > 0) {
                    //会员余额扣除
                    if ($member_info[ 'balance' ] >= $v[ 'balance' ]) {//剩余余额足够才扣除
                        $core_member_account->addLog($data[ 'member_id' ], MemberAccountTypeDict:: BALANCE, -$v[ 'balance' ], 'manjian_gift_back', '满减送活动订单退款退还赠送余额', $manjian_id);
                        $manjian_give_records_service->where($manjian_give_where)->update([ 'balance' => Db::raw('balance - ' . $v[ 'balance' ]) ]);
                    }
                }
                if (!empty($v[ 'coupon' ]) && !empty($give_records)) {
                    //会员优惠券失效
                    $coupon_json = [];
                    foreach ($v[ 'coupon' ] as $coupon) {
                        $coupon_info = $coupon_model->where([ [ 'id', '=', $coupon[ 'coupon_id' ] ] ])->findOrEmpty();
                        if (!$coupon_info->isEmpty()) {
                            $ids = $coupon_member_model->where([
                                [ 'member_id', '=', $data[ 'member_id' ] ],
                                [ 'coupon_id', '=', $coupon[ 'coupon_id' ] ],
                                [ 'status', '=', CouponMemberDict::WAIT_USE ]
                            ])->limit($coupon[ 'num' ])->order('create_time desc')->column('id');
                            if (!empty($ids)) {
                                $core_coupon_member_service->invalid($ids);
                                $coupon_info->give_count -= $coupon[ 'num' ];
                                $coupon_info->save();
                            }
                            if (!empty($give_records[ 'coupon_json' ])) {
                                $coupon_array = array_column($give_records[ 'coupon_json' ], 'num', 'coupon_id');
                                if ($coupon_array[ $coupon[ 'coupon_id' ] ] > $coupon[ 'num' ]) {
                                    $coupon_json[][ 'coupon_id' ] = $coupon[ 'coupon_id' ];
                                    $coupon_json[][ 'num' ] = $coupon_array[ $coupon[ 'coupon_id' ] ] - $coupon[ 'num' ];
                                }
                            }
                        }
                    }
                    $manjian_give_records_service->where($manjian_give_where)->update([ 'coupon_json' => $coupon_json ]);
                    $nums = array_column($v[ 'coupon' ], 'num');
                    $coupon_num = array_sum($nums);
                }
                if (!empty($v[ 'goods' ]) && !empty($give_records)) {
                    //赠品退款或扣除赠送数量
                    $goods_json = [];
                    foreach ($v[ 'goods' ] as $goods) {
                        $order_goods_where = [
                            [ 'member_id', '=', $data[ 'member_id' ] ],
                            [ 'order_id', '=', $data[ 'order_id' ] ],
                            [ 'goods_id', '=', $goods[ 'goods_id' ] ],
                            [ 'sku_id', '=', $goods[ 'sku_id' ] ],
                            [ 'is_gift', '=', 1 ]
                        ];
                        $order_goods_info = $order_goods_model->field('num')->where($order_goods_where)->findOrEmpty()->toArray();
                        if (!empty($order_goods_info)) {
                            if ($goods[ 'num' ] >= $order_goods_info[ 'num' ]) {//赠送数量全部扣除，赠品订单项退款完成
                                $order_goods_model->where($order_goods_where)->update([
                                    'status' => OrderGoodsDict::REFUND_FINISH,
                                    'is_enable_refund' => 0,//禁用退款
                                ]);
                            } else {//没有全部扣除，只减去需要扣除的赠送数量，不改变退款状态
                                $order_goods_model->where($order_goods_where)->update([ 'num' => Db::raw('num - ' . $goods[ 'num' ]) ]);
                            }
                            //增加赠品库存
                            $core_goods_stock_service->inc([
                                'num' => $goods[ 'num' ],
                                'goods_id' => $goods[ 'goods_id' ],
                                'sku_id' => $goods[ 'sku_id' ]
                            ]);
                        }
                        if (!empty($give_records[ 'goods_json' ])) {
                            $goods_array = array_column($give_records[ 'goods_json' ], 'num', 'sku_id');
                            if ($goods_array[ $goods[ 'sku_id' ] ] > $goods[ 'num' ]) {
                                $goods_json[][ 'goods_id' ] = $goods[ 'goods_id' ];
                                $goods_json[][ 'sku_id' ] = $goods[ 'sku_id' ];
                                $goods_json[][ 'num' ] = $goods_array[ $goods[ 'sku_id' ] ] - $goods[ 'num' ];
                            }
                        }
                    }
                    $manjian_give_records_service->where($manjian_give_where)->update([ 'goods_json' => $goods_json ]);
                    $nums = array_column($v[ 'goods' ], 'num');
                    $goods_num = array_sum($nums);
                }
                //满减活动数据统计
                $stat_data = [
                    'order_money' => -$data[ 'money' ],
                    'point' => -( $v[ 'point' ] ?? 0 ),
                    'balance' => -( $v[ 'balance' ] ?? 0 ),
                    'coupon_num' => -( $coupon_num ?? 0 ),
                    'goods_num' => -( $goods_num ?? 0 )
                ];
                $core_manjian_stat_service->stat( $manjian_id, $stat_data);
            }
        }
        return true;
    }

    /**
     * 获取满减送订单项赠送信息
     * @return void
     */
    public function getOrderGoodsGiveInfo(&$order_goods_info, $order_goods_list, $member_id)
    {
        $manjian_records = ( new ManjianGiveRecords() )->field('manjian_id,level,point,balance,coupon_json,goods_json,sku_ids')->where([
            [ 'member_id', '=', $member_id ],
            [ 'order_id', '=', $order_goods_info[ 'order_id' ] ],
        ])->select()->toArray();
        if (!empty($manjian_records)) {
            foreach ($manjian_records as $kk => $vv) {
                $manjian_info = ( new Manjian() )->field('manjian_id,manjian_name,condition_type,goods_type,join_member_type,rule_type,rule_json')->where([
                    [ 'manjian_id', '=', $vv[ 'manjian_id' ] ]
                ])->findOrEmpty()->toArray();

                $condition_match_value = 0;
                foreach ($order_goods_list as $k => $v) {
                    if ($v[ 'is_gift' ] == 0) {
                        if (in_array($v[ 'sku_id' ], $vv[ 'sku_ids' ])) {
                            if (!empty($manjian_info)) {
                                if ($manjian_info[ 'condition_type' ] == ManjianDict::OVER_N_YUAN) {
                                    $condition_match_value += $v[ 'goods_money' ];
                                } else {
                                    $condition_match_value += $v[ 'num' ];
                                }
                            }
                        }
                    }
                }
                if (in_array($order_goods_info[ 'sku_id' ], $vv[ 'sku_ids' ])) {
                    if (!empty($manjian_info) && !empty($manjian_info[ 'rule_json' ])) {
                        $rule_json = $manjian_info[ 'rule_json' ][ $vv[ 'level' ] ];
                        $cycle_num = 0;
                        if ($manjian_info[ 'rule_type' ] == ManjianDict::CYCLE) {
                            $cycle_num = intdiv($condition_match_value, $rule_json[ 'limit' ]);
                            $rule_json[ 'limit' ] = $rule_json[ 'limit' ] * $cycle_num;
                            if (!empty($rule_json[ 'discount_money' ])) {
                                $rule_json[ 'discount_money' ] = bcmul($cycle_num, $rule_json[ 'discount_money' ], 2);
                            }
                            if (!empty($rule_json[ 'point' ])) {
                                $rule_json[ 'point' ] = intval($rule_json[ 'point' ]) * $cycle_num;
                            }
                            if (!empty($rule_json[ 'balance' ])) {
                                $rule_json[ 'balance' ] = bcmul($cycle_num, $rule_json[ 'balance' ], 2);
                            }
                        }

                        if (!empty($rule_json[ 'coupon' ])) {
                            foreach ($rule_json[ 'coupon' ] as &$coupon) {
                                if ($manjian_info[ 'rule_type' ] == ManjianDict::CYCLE && $cycle_num > 0) {
                                    $coupon[ 'num' ] = intval($coupon[ 'num' ]) * $cycle_num;
                                }
                                $coupon_info = ( new Coupon() )->field('price,min_condition_money')->where([
                                    [ 'id', '=', $coupon[ 'coupon_id' ] ]
                                ])->findOrEmpty()->toArray();
                                if (!empty($coupon_info)) {
                                    if ($coupon_info[ 'min_condition_money' ] == '0.00') {
                                        $coupon_name = $coupon_info[ 'price' ] . "元无门槛券";
                                    } else {
                                        $coupon_name = "满" . $coupon_info[ 'min_condition_money' ] . "元减" . $coupon_info[ 'price' ] . "元券";
                                    }
                                    $coupon[ 'coupon_name' ] = $coupon_name;
                                }
                            }
                        }
                        if (!empty($rule_json[ 'goods' ])) {
                            foreach ($rule_json[ 'goods' ] as &$goods) {
                                if ($manjian_info[ 'rule_type' ] == ManjianDict::CYCLE && $cycle_num > 0) {
                                    $goods[ 'num' ] = intval($goods[ 'num' ]) * $cycle_num;
                                }
                                $sku_info = ( new GoodsSku() )->field('goods_id,sku_name,sku_image,price')->where([
                                    [ 'goods_id', '=', $goods[ 'goods_id' ] ],
                                    [ 'sku_id', '=', $goods[ 'sku_id' ] ]
                                ])->with([ 'goods' ])->findOrEmpty()->toArray();
                                if (!empty($sku_info) && !empty($sku_info[ 'goods' ])) {
                                    $goods[ 'goods_name' ] = $sku_info[ 'goods' ][ 'goods_name' ];
                                    $goods[ 'sku_name' ] = $sku_info[ 'sku_name' ];
                                    $goods[ 'sku_image' ] = $sku_info[ 'sku_image' ];
                                    $goods[ 'price' ] = $sku_info[ 'price' ];
                                }
                            }
                        }
                        $manjian_info[ 'rule' ] = $rule_json;
                        if ($manjian_info[ 'rule' ][ 'is_discount' ] || $manjian_info[ 'rule' ][ 'is_free_shipping' ] || $manjian_info[ 'rule' ][ 'is_give_point' ] || $manjian_info[ 'rule' ][ 'is_give_balance' ] || $manjian_info[ 'rule' ][ 'is_give_coupon' ] || $manjian_info[ 'rule' ][ 'is_give_goods' ]) {
                            $order_goods_info[ 'manjian_info' ] = $manjian_info;
                        }
                    }
                }
            }
        }
    }

    /**
     * 满减优惠
     * @param $data
     * @return mixed
     */
    public function manjianPromotion($data, $member_id)
    {
        $promotion_money = $data[ 'promotion_money' ] ?? 0;
        //先查询全部商品的满减套餐  进行中
        $manjian_model = new Manjian();
        $all_info = $manjian_model
            ->field('manjian_id,manjian_name,goods_type,goods_ids,rule_json,rule_type,join_member_type,level_ids,label_ids,condition_type')
            ->where([ [ 'goods_type', '=', ManjianDict::ALL_GOODS ], [ 'status', '=', ManjianDict::ACTIVE ] ])
            ->findOrEmpty()->toArray();
        $goods_list = $data[ 'goods_list' ];
        //存在全场满减(不考虑部分满减情况)
        if (!empty($all_info)) {
            //验证当前用户是否可参与活动
            $can_join = $this->canJoinManjian($all_info, $member_id);
            if ($can_join) {
                //获取折扣价格
                $discount_array = $this->getManjianDiscountMoney($all_info, $data);
                $all_info[ 'discount_array' ] = $discount_array;

                $discount_money = $discount_array[ 'discount_money' ];
                $promotion_money = bcadd($promotion_money, $discount_money, 2);
                if (!empty($discount_array[ 'rule' ])) {
                    $goods_list = array_map(function ($item) use ($all_info) {
                        $item[ 'promotion' ][ 'manjian' ] = $all_info;
                        return $item;
                    }, $goods_list);
                }
            }
        } else {
            $not_select_info = $manjian_model
                ->field('manjian_id,manjian_name,goods_type,goods_ids,rule_json,rule_type,join_member_type,level_ids,label_ids,condition_type')
                ->with([ 'activeGoods' ])
                ->where([ [ 'goods_type', '=', ManjianDict::SELECTED_GOODS_NOT ], [ 'status', '=', ManjianDict::ACTIVE ] ])
                ->findOrEmpty()->toArray();
            if (!empty($not_select_info)) {
                //验证当前用户是否可参与活动
                $can_join = $this->canJoinManjian($not_select_info, $member_id);
                if ($can_join) {
                    $not_select_goods_sku_id = [];
                    $item_goods_data = [
                        'goods_money' => 0,
                        'goods_num' => 0
                    ];
                    $item_goods_list = [];
                    $sku_ids = [];
                    $discount_money = 0;
                    if (isset($not_select_info[ 'activeGoods' ]) && !empty($not_select_info[ 'activeGoods' ])) {
                        $not_select_goods_sku_id = array_column($not_select_info[ 'activeGoods' ], 'sku_id');
                    }

                    foreach ($goods_list as $goods_k => $goods_item) {
                        if (!in_array($goods_item[ 'sku_id' ], $not_select_goods_sku_id)) {
                            $item_goods_data[ 'goods_money' ] += $goods_item[ 'goods_money' ];
                            $item_goods_data[ 'goods_num' ] += $goods_item[ 'num' ];
                            $item_goods_list[] = $goods_item;
                            array_push($sku_ids, $goods_item[ 'sku_id' ]);
                        }
                    }
                    $discount_array = $this->getManjianDiscountMoney($not_select_info, $item_goods_data);

                    $not_select_info[ 'discount_array' ] = $discount_array;
                    $discount_money = bcadd($discount_money, $discount_array[ 'discount_money' ], 2);

                    if (!empty($discount_array[ 'rule' ])) {
                        $goods_list = array_map(function ($item) use ($sku_ids, $not_select_info) {
                            if (in_array($item[ 'sku_id' ], $sku_ids)) {
                                $item[ 'promotion' ][ 'manjian' ] = $not_select_info;
                            }
                            return $item;
                        }, $goods_list);
                    }
                    $promotion_money = bcadd($promotion_money, $discount_money, 2);
                }
            } else {
                $goods_ids = array_unique(array_column($data[ 'goods_list' ], 'goods_id'));
                $manjian_goods_list = ( new ManjianGoods() )
                    ->where([ [ 'status', '=', ManjianDict::ACTIVE ], [ 'goods_id', 'in', $goods_ids ] ])
                    ->column('manjian_id');
                if (!empty($manjian_goods_list)) {
                    $discount_money = 0;
                    $manjian_ids = array_unique($manjian_goods_list); //去重
                    sort($manjian_ids);
                    $manjian_list = $manjian_model
                        ->field('manjian_id,manjian_name,goods_type,goods_ids,rule_json,rule_type,join_member_type,level_ids,label_ids,condition_type')
                        ->with([ 'activeGoods' ])
                        ->where([ [ 'goods_type', '=', ManjianDict::SELECTED_GOODS ], [ 'status', '=', ManjianDict::ACTIVE ], [ 'manjian_id', 'in', $manjian_ids ] ])
                        ->select()->toArray();
                    foreach ($manjian_list as $k => $v) {
                        //验证当前用户是否可参与活动
                        $can_join = $this->canJoinManjian($v, $member_id);
                        if (!$can_join) {
                            continue;
                        }
                        $manjian_goods_sku_ids = [];
                        if (isset($v[ 'activeGoods' ]) && !empty($v[ 'activeGoods' ])) {
                            $manjian_goods_sku_ids = array_column($v[ 'activeGoods' ], 'sku_id');
                        }
                        $item_goods_data = [
                            'goods_money' => 0,
                            'goods_num' => 0
                        ];
                        $item_goods_list = [];
                        $sku_ids = [];
                        foreach ($goods_list as $goods_k => $goods_item) {
                            if (in_array($goods_item[ 'sku_id' ], $manjian_goods_sku_ids)) {
                                $item_goods_data[ 'goods_money' ] += $goods_item[ 'goods_money' ];
                                $item_goods_data[ 'goods_num' ] += $goods_item[ 'num' ];
                                $item_goods_list[] = $goods_item;
                                array_push($sku_ids, $goods_item[ 'sku_id' ]);
                                $goods_list[ $goods_k ] = $goods_item;
                            }
                        }
                        $discount_array = $this->getManjianDiscountMoney($v, $item_goods_data);

                        $discount_money = bcadd($discount_money, $discount_array[ 'discount_money' ], 2);

                        if (!empty($discount_array[ 'rule' ])) {
                            $goods_list = array_map(function ($item) use ($sku_ids, $v, $discount_array) {
                                if (in_array($item[ 'sku_id' ], $sku_ids)) {
                                    $item[ 'promotion' ][ 'manjian' ] = $v;
                                    $item[ 'promotion' ][ 'manjian' ][ 'discount_array' ] = $discount_array;
                                }
                                return $item;
                            }, $goods_list);
                        }
                    }

                    $promotion_money = bcadd($promotion_money, $discount_money, 2);
                }
            }
        }
        $data[ 'goods_list' ] = $goods_list;
        $data[ 'promotion_money' ] = $promotion_money;
        return $data;
    }

    /**
     * 满减优惠金额
     * @param $manjian_info
     * @param $data
     * @return array
     */
    public function getManjianDiscountMoney($manjian_info, $data)
    {
        $goods_money = $data[ 'goods_money' ];
        if ($manjian_info[ 'condition_type' ] == ManjianDict::OVER_N_YUAN) {
            $value = $data[ 'goods_money' ];
        } else {
            $value = $data[ 'goods_num' ];
        }
        //阶梯计算优惠
        $rule_item = $manjian_info[ 'rule_json' ];
        $discount_money = 0;
        $money = 0;
        $rule = []; // 符合条件的优惠规则
        $level = -1; // 优惠层级
        if ($manjian_info[ 'rule_type' ] == ManjianDict::LADDER) {
            foreach ($rule_item as $k => $v) {
                if ($value >= $v[ 'limit' ]) {
                    $rule = $v;
                    if (isset($v[ 'is_discount' ]) && $v[ 'is_discount' ] == 1) {
                        if ($v[ 'discount_type' ] == 1) {//减
                            $discount_money = $v[ 'discount_money' ] ?? 0;
                        } else {//折
                            $discount_money = bcsub($goods_money, bcmul(bcdiv(( $v[ 'discount_money' ] ?? 0 ), 10, 2), $goods_money, 2), 2);
                        }
                    }
                    $money = $v[ 'limit' ] ?? 0;
                    $level = $k;
                }
            }
        } elseif ($manjian_info[ 'rule_type' ] == ManjianDict::CYCLE) {
            foreach ($rule_item as $k => $v) {
                if ($value >= $v[ 'limit' ]) {
                    $rule = $v;
                    $cycle_num = intdiv($value, $v[ 'limit' ]);
                    if (isset($v[ 'is_discount' ]) && $v[ 'is_discount' ] == 1) {
                        if ($v[ 'limit' ] == 0) {
                            $discount_money = 0;
                        } else {
                            $discount_money = bcmul($cycle_num, ( $v[ 'discount_money' ] ?? 0 ), 2);
                        }
                    }
                    $level = $k;
                }
            }
        }
        return [ 'discount_money' => $discount_money, 'money' => $money, 'rule' => $rule, 'level' => $level ];
    }

    /**
     * 是否可参加活动
     * @param $manjian_info
     * @param $member_id
     * @return bool
     */
    public function canJoinManjian($manjian_info, $member_id)
    {
        //判断会员是否有参与资格
        $can_join = FALSE;
        $join_member_type = $manjian_info[ 'join_member_type' ];
        $level_ids = $manjian_info[ 'level_ids' ];
        $label_ids = $manjian_info[ 'label_ids' ];
        $member_info = ( new Member() )->field('member_level,member_label')->where([
            [ 'member_id', '=', $member_id ]
        ])->findOrEmpty()->toArray();
        if ($join_member_type == ManjianDict::ALL_MEMBER) {//所有会员参与
            $can_join = TRUE;
        } elseif ($join_member_type == ManjianDict::SELECTED_MEMBER_LEVEL) {//指定会员等级
            if (in_array($member_info[ 'member_level' ], $level_ids)) {
                $can_join = TRUE;
            }
        } elseif ($join_member_type == ManjianDict::SELECTED_MEMBER_LABEL) {//指定会员标签
            if (!empty($member_info[ 'member_label' ]) && !empty(array_intersect($member_info[ 'member_label' ], $label_ids))) {
                $can_join = TRUE;
            }
        }
        return $can_join;
    }

    /**
     * 获取满减信息
     * @return array
     */
    public function getManjianInfo($data)
    {
        $member_id = $data[ 'member_id' ];
        $goods_id = $data[ 'goods_id' ];
        $sku_id = $data[ 'sku_id' ];
        $gift_goods = $data[ 'gift_goods' ] ?? [];
        if (empty($sku_id) && !empty($goods_id)) {
            // 查询默认规格项
            $default_sku_info = ( new GoodsSku() )->where([ [ 'goods_id', '=', $goods_id ], [ 'is_default', '=', 1 ] ], 'sku_id')
                ->field('sku_id')->findOrEmpty()->toArray();
            if (!empty($default_sku_info)) {
                $sku_id = $default_sku_info[ 'sku_id' ];
            }
        }

        $manjian_info = [];
        $field = 'manjian_id,manjian_name,condition_type,rule_type,rule_json,join_member_type,level_ids,label_ids,start_time,end_time,remark';
        $common_where = [
            [ 'status', '=', ManjianDict::ACTIVE ],
            [ 'start_time', '<=', time() ],
            [ 'end_time', '>', time() ]
        ];
        $manjian_info_all_goods = $this->model->field($field)->where($common_where)->where([ [ 'goods_type', '=', ManjianDict::ALL_GOODS ] ])->findOrEmpty()->toArray();
        if (!empty($manjian_info_all_goods)) {//全部商品参与
            $manjian_info = $manjian_info_all_goods;
            $can_join = $this->canJoinManjian($manjian_info, $member_id);
            if ($can_join) {
                $rule_content = $this->getRuleContent($manjian_info, $gift_goods);
                $manjian_info = $rule_content[ 'is_join' ] ? $rule_content : [];
            }
        } else {
            $manjian_info_selected_goods_not = $this->model->field($field)->where($common_where)->where([ [ 'goods_type', '=', ManjianDict::SELECTED_GOODS_NOT ] ])->findOrEmpty()->toArray();
            $manjian_goods_info = ( new ManjianGoods() )->field('manjian_id,goods_type')->where([
                [ 'goods_id', '=', $goods_id ],
                [ 'sku_id', '=', $sku_id ],
                [ 'status', '=', ManjianDict::ACTIVE ]
            ])->findOrEmpty()->toArray();
            if (!empty($manjian_info_selected_goods_not) && empty($manjian_goods_info)) {//指定商品不参与
                $manjian_info = $manjian_info_selected_goods_not;
                $can_join = $this->canJoinManjian($manjian_info, $member_id);
                if ($can_join) {
                    $rule_content = $this->getRuleContent($manjian_info, $gift_goods);
                    $manjian_info = $rule_content[ 'is_join' ] ? $rule_content : [];
                }
            } else {//指定商品参与
                $manjian_info_selected_goods = $this->model->field($field)->where($common_where)->where([ [ 'goods_type', '=', ManjianDict::SELECTED_GOODS ] ])->select()->toArray();
                if (!empty($manjian_info_selected_goods) && !empty($manjian_goods_info) && $manjian_goods_info[ 'goods_type' ] == ManjianDict::SELECTED_GOODS) {
                    $manjian_info_selected_goods = array_column($manjian_info_selected_goods, null, 'manjian_id');
                    if (in_array($manjian_goods_info[ 'manjian_id' ], array_keys($manjian_info_selected_goods))) {
                        $manjian_info = $manjian_info_selected_goods[ $manjian_goods_info[ 'manjian_id' ] ];
                        $can_join = $this->canJoinManjian($manjian_info, $member_id);
                        if ($can_join) {
                            $rule_content = $this->getRuleContent($manjian_info, $gift_goods);
                            $manjian_info = $rule_content[ 'is_join' ] ? $rule_content : [];
                        }
                    }
                }
            }
        }
        return $manjian_info;
    }

    /**
     * 获取满减规则内容
     * @param $manjian_info
     * @return array
     */
    public function getRuleContent($manjian_info, $gift_goods)
    {
        if (!empty($manjian_info)) {
            $is_join = false;
            $manjian_info[ 'gift_goods' ] = $gift_goods;
            foreach ($manjian_info[ 'rule_json' ] as $key => $item) {
                if ($item[ 'is_discount' ]) {
                    $is_join = true;
                }
                if ($item[ 'is_free_shipping' ]) {
                    $is_join = true;
                }
                if ($item[ 'is_give_point' ]) {
                    $is_join = true;
                }
                if ($item[ 'is_give_balance' ]) {
                    $is_join = true;
                }
                if ($item[ 'is_give_coupon' ]) {
                    foreach ($item[ 'coupon' ] as $coupon_key => &$coupon) {
                        $coupon_info = ( new Coupon() )->field('price,min_condition_money')->where([
                            [ 'id', '=', $coupon[ 'coupon_id' ] ],
                            [ 'status', '=', CouponDict::NORMAL ],
                        ])->findOrEmpty()->toArray();
                        if (!empty($coupon_info)) {
                            if ($coupon_info[ 'min_condition_money' ] == '0.00') {
                                $coupon_name = $coupon_info[ 'price' ] . "元无门槛券";
                            } else {
                                $coupon_name = "满" . $coupon_info[ 'min_condition_money' ] . "元减" . $coupon_info[ 'price' ] . "元券";
                            }
                            $coupon[ 'coupon_name' ] = $coupon_name;
                        } else {
                            unset($item[ 'coupon' ][ $coupon_key ]);
                        }
                    }
                    $item[ 'coupon' ] = array_values($item[ 'coupon' ]);
                    if (!empty($item[ 'coupon' ])) {
                        $is_join = true;
                    }
                }
                if ($item[ 'is_give_goods' ]) {
                    foreach ($item[ 'goods' ] as $goods_key => &$goods) {
                        $sku_info = ( new GoodsSku() )->field('goods_id,sku_name,sku_image,price,stock')->where([
                            [ 'goods_id', '=', $goods[ 'goods_id' ] ],
                            [ 'sku_id', '=', $goods[ 'sku_id' ] ],
                            [ 'stock', '>', 0 ],
                        ])->with([ 'goods' ])->findOrEmpty()->toArray();
                        if (!empty($sku_info) && !empty($sku_info[ 'goods' ]) && $sku_info[ 'goods' ][ 'status' ] == 1) {
                            if (empty($gift_goods)) {
                                // 库存小于赠送数量时，取剩余库存
                                $num = $sku_info[ 'stock' ] >= $goods[ 'num' ] ? $goods[ 'num' ] : $sku_info[ 'stock' ];
                                $goods[ 'num' ] = $num;
                            } else {
                                // 计算剩余库存
                                $leave_num = $sku_info[ 'stock' ];
                                foreach ($gift_goods as $gift_goods_item) {
                                    if ($gift_goods_item[ 'goods_id' ] == $goods[ 'goods_id' ] && $gift_goods_item[ 'sku_id' ] == $goods[ 'sku_id' ]) {
                                        if ($leave_num - $gift_goods_item[ 'num' ] >= 0) {
                                            $leave_num = $leave_num - $gift_goods_item[ 'num' ];
                                        } else {
                                            $leave_num = 0;
                                        }
                                    }
                                }
                                $goods[ 'num' ] = $leave_num >= $goods[ 'num' ] ? $goods[ 'num' ] : $leave_num;
                            }
                            $goods[ 'goods_name' ] = $sku_info[ 'goods' ][ 'goods_name' ];
                            $goods[ 'sku_name' ] = $sku_info[ 'sku_name' ];
                            $goods[ 'sku_image' ] = $sku_info[ 'sku_image' ];
                            if (empty($goods[ 'sku_image' ])) {
                                $goods[ 'sku_image' ] = $sku_info[ 'goods' ][ 'goods_cover_thumb_mid' ];
                            }
                            $goods[ 'price' ] = $sku_info[ 'price' ];

                            if ($goods[ 'num' ] == 0) {
                                unset($item[ 'goods' ][ $goods_key ]);
                            }

                            $manjian_info[ 'gift_goods' ][] = $goods;

                        } else {
                            unset($item[ 'goods' ][ $goods_key ]);
                        }
                    }
                    $item[ 'goods' ] = array_values($item[ 'goods' ]);
                    if (!empty($item[ 'goods' ])) {
                        $is_join = true;
                    }
                }
                $manjian_info[ 'rule_json' ][ $key ] = $item;
            }
            $manjian_info[ 'is_join' ] = $is_join;
        }
        return $manjian_info;
    }

    /**
     * todo 金额格式化 封装调用
     * @param $money
     * @return float|int
     */
    public function moneyFormat($money)
    {
        return floor(strval(( $money ) * 100)) / 100;
    }

}
