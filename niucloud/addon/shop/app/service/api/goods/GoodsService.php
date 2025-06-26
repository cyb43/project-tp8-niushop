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

namespace addon\shop\app\service\api\goods;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\model\coupon\CouponGoods;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsCollect;
use addon\shop\app\model\goods\Label;
use addon\shop\app\model\goods\Service;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\service\api\marketing\DiscountService;
use addon\shop\app\service\api\marketing\NewcomerService;
use addon\shop\app\service\core\goods\CoreGoodsAccessNumService;
use addon\shop\app\service\core\goods\CoreGoodsActivePriceService;
use addon\shop\app\service\core\goods\CoreGoodsConfigService;
use addon\shop\app\service\core\goods\CoreGoodsStatService;
use addon\shop\app\service\core\goods\CoreGoodsLimitBuyService;
use addon\shop\app\service\core\order\CoreOrderConfigService;
use app\model\member\Member;
use core\base\BaseApiService;
use core\exception\CommonException;

/**
 *  商品服务层
 */
class GoodsService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Goods();
    }

    /**
     * 获取商品列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'goods_id,goods_name,sub_title,goods_type,goods_cover,unit,sale_num + goods.virtual_sale_num as sale_num,is_limit,limit_type,max_buy,min_buy,member_discount,virtual_receive_type,label_ids,brand_id,stock';

        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ],
            [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ],
            [ 'status', '=', 1 ]
        ];

        if (!empty($where[ 'keyword' ])) {
            $sku_where[] = [ 'goods_name|sub_title', 'like', '%' . $where[ 'keyword' ] . '%' ];
        }

        if (!empty($where[ 'start_price' ]) && !empty($where[ 'end_price' ])) {
            $money = [ $where[ 'start_price' ], $where[ 'end_price' ] ];
            sort($money);
            $sku_where[] = [ 'goodsSku.price', 'between', $money ];
        } else if (!empty($where[ 'start_price' ])) {
            $sku_where[] = [ 'goodsSku.price', '>=', $where[ 'start_price' ] ];
        } else if (!empty($where[ 'end_price' ])) {
            $sku_where[] = [ 'goodsSku.price', '<=', $where[ 'end_price' ] ];
        }

        // 查询优惠券包括的id
        if (!empty($where[ 'coupon_id' ])) {
            $coupon_goods_model = new CouponGoods();
            $coupon_list = $coupon_goods_model->where([
                [ 'coupon_id', '=', $where[ 'coupon_id' ] ]
            ])->field('goods_id,category_id')->select()->toArray();
            if (!empty($coupon_list)) {
                $goods_ids = array_values(array_filter(array_column($coupon_list, 'goods_id')));
                $category_ids = array_values(array_filter(array_column($coupon_list, 'category_id')));
                if (!empty($goods_ids)) {
                    $sku_where[] = [ 'goods.goods_id', 'in', $goods_ids ];
                } elseif (!empty($category_ids)) {
                    $like_arr = [];
                    foreach ($category_ids as $k => $v) {
                        $like_arr[] = "%" . $v . "%";
                    }
                    $sku_where[] = [ 'goods_category', "like", $like_arr, 'or' ];
                }
            }
        }

        // 参数过滤
        if (!empty($where[ 'order' ]) && in_array($where[ 'order' ], [ 'sale_num', 'price' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        } else {
            $sort_config = ( new CoreGoodsConfigService() )->getSortConfig();
            if($sort_config['sort_column' ] =='sale_price') {
                $sort_config['sort_column' ] = 'goodsSku.sale_price';
            }
            $order = $sort_config[ 'sort_column' ] . ' ' . $sort_config[ 'sort_type' ];
        }

        $search_model = $this->model
            ->withSearch([ "brand_id", "goods_category", "label_ids", 'service_ids' ], $where)
            ->field($field)
            ->withJoin([
                'goodsSku' => [ 'sku_id', 'sku_name', 'sku_image', 'sku_no', 'goods_id', 'sku_spec_format', 'price', 'market_price', 'sale_price', 'stock', 'weight', 'volume', 'member_price' ]
            ])
            ->where($sku_where)->order($order)->append([ 'goods_cover_thumb_mid', 'goods_label_name', 'goods_brand' ]);
        $list = $this->pageQuery($search_model);
        $goods_active_price_service = (new CoreGoodsActivePriceService());
        foreach ($list[ 'data' ] as $k => &$v) {
            if (!empty($v[ 'goodsSku' ])) {
                $v[ 'goodsSku' ][ 'member_discount' ] = $v[ 'member_discount' ];
                $list_show_price = $goods_active_price_service->getShowPrice($v[ 'goodsSku' ], $this->member_id);
                $v[ 'goodsSku' ][ 'show_price' ] = $list_show_price[ 'show_price' ];
                $v[ 'goodsSku' ][ 'show_type' ] = $list_show_price[ 'show_type' ];
            }
            // 限购查询当前会员已购数量
            $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($this->member_id, $v[ 'goods_id' ]);
            $v[ 'has_buy' ] = $has_buy;
        }
        return $list;
    }

    /**
     * 常规调用获取商品列表
     * @param array $where
     * @return array
     */
    public function getGoodsList(array $where = [])
    {
        $field = 'goods_id,goods_name,sub_title,goods_category,goods_type,goods_cover,unit,status,sale_num + goods.virtual_sale_num as sale_num,member_discount,is_discount';

        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ],
            [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ],
            [ 'status', '=', 1 ]
        ];

        if (!empty($where[ 'goods_ids' ])) {
            $sku_where[] = [ 'goods.goods_id', 'in', $where[ 'goods_ids' ] ];
        }

        // 参数过滤
        if (!empty($where[ 'order' ]) && in_array($where[ 'order' ], [ 'sale_num', 'price' ])) {
            $order = $where[ 'order' ] . ' desc';
        } else {
            $order = 'sort desc,create_time desc';
        }

        $list = $this->model
            ->withSearch([ 'goods_category', 'label_ids', 'service_ids' ], $where)
            ->field($field)
            ->withJoin([ 'goodsSku' ])
            ->where($sku_where)->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ])
            ->select()->toArray();

        if (!empty($list)){
            $goods_active_price_service = (new CoreGoodsActivePriceService());
            foreach ($list as $k => &$v) {
                if (!empty($v[ 'goodsSku' ])) {
                    $v[ 'goodsSku' ][ 'member_discount' ] = $v[ 'member_discount' ];
                    $list_show_price = $goods_active_price_service->getShowPrice($v[ 'goodsSku' ], $this->member_id);
                    $v[ 'goodsSku' ][ 'show_price' ] = $list_show_price[ 'show_price' ];
                    $v[ 'goodsSku' ][ 'show_type' ] = $list_show_price[ 'show_type' ];
                }
            }
        }

        return $list;
    }

    /**
     * 获取商品详情
     * @param array $data
     * @return array
     */
    public function getDetail(array $data)
    {
        $sku_id = $data[ 'sku_id' ];
        $goods_id = $data[ 'goods_id' ];
        if (!empty($goods_id)) {
            $goods_info = ( new Goods() )->where([ [ 'goods_id', '=', $goods_id ], [ 'delete_time', '=', 0 ] ])->count();
            if (empty($goods_info)) throw new CommonException('SHOP_GOODS_NOT_EXIST');//商品不存在
        }

        $goods_sku_model = new GoodsSku();

        if (empty($sku_id) && !empty($goods_id)) {
            // 查询默认规格项
            $default_sku_info = $goods_sku_model->where([ [ 'goods_id', '=', $goods_id ], [ 'is_default', '=', 1 ] ], 'sku_id')
                ->field('sku_id')->findOrEmpty()->toArray();
            if (!empty($default_sku_info)) {
                $sku_id = $default_sku_info[ 'sku_id' ];
            }
        }

        $field = 'sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, sale_num, is_default,member_price';

        $info = $goods_sku_model->where([ [ 'sku_id', '=', $sku_id ] ])
            ->field($field)
            ->with([
                'goods' => function ($query) {
                    $query->withField('goods_id, goods_name, goods_type, sub_title, goods_cover, goods_category, goods_image,goods_video,goods_desc,brand_id,label_ids,service_ids, unit, stock, sale_num + virtual_sale_num as sale_num, is_limit,limit_type,max_buy,min_buy,status,delivery_type,attr_ids,attr_format,member_discount,is_discount,poster_id,virtual_receive_type,is_gift,form_id')
                        ->append([ 'goods_type_name', 'goods_cover_thumb_mid', 'delivery_type_list', 'goods_image_thumb_small', 'goods_image_thumb_mid', 'goods_image_thumb_big', 'goods_brand' ]);
                },
                // 商品规格列表
                'skuList' => function ($query) {
                    $query->field('sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, is_default,member_price');
                },
                // 商品规格项/规格值列表
                'goodsSpec' => function ($query) {
                    $query->field('spec_id, goods_id, spec_name, spec_values');
                },
            ])
            ->append([ 'sku_image_thumb_small', 'sku_image_thumb_mid', 'sku_image_thumb_big' ])
            ->findOrEmpty()->toArray();
        if (!empty($info) && !empty($info[ 'goods' ])) {
            $info[ 'type' ] = $data[ 'type' ] ?? '';

            $goods_active_price_service = (new CoreGoodsActivePriceService());
            //获取展示信息（价格 标签）
            $info[ 'member_discount' ] = $info[ 'goods' ][ 'member_discount' ] ?? '';
            $detail_show_price = $goods_active_price_service->getShowPrice($info, $this->member_id);
            $info[ 'show_price' ] = $detail_show_price[ 'show_price' ];
            $info[ 'show_type' ] = $detail_show_price[ 'show_type' ];
            //组装数据
            if (!empty($info[ 'skuList' ])){
                foreach ($info[ 'skuList' ] as &$value){
                    $value['type'] = $data[ 'type' ] ?? '';
                    $value[ 'member_discount' ] = $info[ 'goods' ][ 'member_discount' ] ?? '';
                    $list_show_price = $goods_active_price_service->getShowPrice($value, $this->member_id);
                    $value[ 'show_price' ] = $list_show_price[ 'show_price' ];
                    $value[ 'show_type' ] = $list_show_price[ 'show_type' ];
                }
            }

            if (!empty($info[ 'goods' ][ 'service_ids' ])) {
                // 商品服务
                $goods_service_model = new Service();
                $info[ 'service' ] = $goods_service_model->where([
                    [ 'service_id', 'in', $info[ 'goods' ][ 'service_ids' ] ]
                ])->field('service_id, service_name, image, desc')
                    ->select()->toArray();
            }
            if (!empty($info[ 'goods' ][ 'label_ids' ])) {
                // 商品标签
                $goods_label_model = new Label();
                $info[ 'label_info' ] = $goods_label_model->where([
                    [ 'label_id', 'in', $info[ 'goods' ][ 'label_ids' ] ],
                    [ 'status', '=', 1 ]
                ])->field('label_id, label_name, memo,style_type,color_json,icon')
                    ->order('sort desc,label_id desc')->select()->toArray();
            }
//            if ($info[ 'show_type' ] == GoodsDict::DISCOUNT_PRICE) {
                // 参与限时折扣，查询活动信息
                if ($info[ 'goods' ][ 'is_discount' ] == 1) {
                    $discount_service = new DiscountService();
                    $info[ 'discount_info' ] = $discount_service->getInfoByGoods($info[ 'goods_id' ]);
                    if (!empty($info[ 'discount_info' ])) {
                        $info[ 'discount_info' ][ 'active' ][ 'start_time' ] = strtotime($info[ 'discount_info' ][ 'active' ][ 'start_time' ]);
                        $info[ 'discount_info' ][ 'active' ][ 'end_time' ] = strtotime($info[ 'discount_info' ][ 'active' ][ 'end_time' ]);
                        $info[ 'type' ] = ActiveDict::DISCOUNT;
                    }
                } else {
                    $info[ 'type' ] = '';
                    $info[ 'type_name' ] = '';
                }
//            }
            if (!empty($this->member_id)) {
                $goods_collect_model = new GoodsCollect();
                $collect_info = $goods_collect_model->where([ [ 'member_id', '=', $this->member_id ], [ 'goods_id', '=', $info[ 'goods_id' ] ] ])->findOrEmpty()->toArray();
                if (!empty($collect_info)) {
                    $info[ 'goods' ][ 'is_collect' ] = 1;
                } else {
                    $info[ 'goods' ][ 'is_collect' ] = 0;
                }

                // 查询会员价
                if (!empty($data[ 'type' ]) && $data[ 'type' ] == ActiveDict::NEWCOMER_DISCOUNT) {
                    // 查询新人价
                    $newcomer_service = new NewcomerService();
                    if ($newcomer_service->checkIfNewcomer()) {
                        $newcomer_info = $newcomer_service->getNewcomerInfo($info[ 'goods_id' ], $info[ 'sku_id' ]);
                        if (!empty($newcomer_info)) {
                            $info[ 'newcomer_price' ] = $newcomer_info[ 'newcomer_price' ];
                            $info[ 'newcomer_desc' ] = $newcomer_info[ 'newcomer_desc' ];
                            $info[ 'is_newcomer' ] = 1;
                            $info[ 'type' ] = ActiveDict::NEWCOMER_DISCOUNT;
                        }
                        $newcomer_service->getNewcomerPriceByList($info[ 'skuList' ]);
                    } else {
                        $info[ 'is_newcomer' ] = 0;
                        $info[ 'type' ] = '';
                        $info[ 'type_name' ] = '';
                    }
                }

                // 限购查询当前会员已购数量
                $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($this->member_id, $info[ 'goods' ][ 'goods_id' ]);
                $info[ 'goods' ][ 'has_buy' ] = $has_buy;
            } else {
                $info[ 'goods' ][ 'has_buy' ] = 0;
                $info[ 'goods' ][ 'is_collect' ] = 0;
            }

            $info[ 'type_name' ] = '';
            if (!empty($info[ 'type' ])) {
                $info[ 'type_name' ] = ActiveDict::getClass($info[ 'type' ]); // 查询来源活动类型
            }

            //查询评价设置是否显示
            $core_order_config_service = new CoreOrderConfigService();
            $evaluate_config = $core_order_config_service->getEvaluateConfig();
            $info[ 'evaluate_is_show' ] = $evaluate_config[ 'evaluate_is_show' ];

            // 商品统计-浏览次数
            CoreGoodsStatService::addStat([ 'goods_id' => $info[ 'goods' ][ 'goods_id' ], 'access_num' => 1 ]);
            ( new CoreGoodsAccessNumService() )->inc([ 'goods_id' => $info[ 'goods' ][ 'goods_id' ], 'access_num' => 1 ]);

            // 种草秀数据查询
            $info[ 'sow_show_list' ] = event('SowShowData', [ 'relate_id' => $info[ 'goods_id' ], 'relate_type' => 'shop', 'limit' => 3 ])[ 0 ] ?? [];
        }

        return $info;
    }

    /**
     * 获取商品规格信息，切换规格
     * @param int $sku_id
     * @return array
     */
    public function getSku(int $sku_id)
    {
        $field = 'sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, sale_num, is_default,member_price';

        $goods_sku_model = new GoodsSku();

        $info = $goods_sku_model->where([ [ 'sku_id', '=', $sku_id ] ])
            ->field($field)
            ->with([
                // 商品主表
                'goods' => function ($query) {
                    $query->withField('goods_id, goods_name, goods_type, sub_title, goods_cover, unit, stock, sale_num + virtual_sale_num as sale_num, status,member_discount,is_discount')
                        ->append([ 'goods_type_name', 'goods_cover_thumb_mid' ]);
                },
                // 商品规格列表
                'skuList' => function ($query) {
                    $query->field('sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, is_default,member_price');
                },
                // 商品规格项/规格值列表
                'goodsSpec' => function ($query) {
                    $query->field('spec_id, goods_id, spec_name, spec_values');
                },
            ])
            ->append([ 'sku_image_thumb_small', 'sku_image_thumb_mid', 'sku_image_thumb_big' ])
            ->findOrEmpty()->toArray();

        if (!empty($info)){
            if (!empty($this->member_id)) {
                // 限购查询当前会员已购数量
                $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($this->member_id, $info[ 'goods_id' ]);
                $info[ 'has_buy' ] = $has_buy;
            }
            $goods_active_price_service = (new CoreGoodsActivePriceService());
            //获取展示信息（价格 标签）
            $info[ 'member_discount' ] = $info[ 'goods' ][ 'member_discount' ] ?? '';
            $detail_show_price = $goods_active_price_service->getShowPrice($info, $this->member_id);
            $info[ 'show_price' ] = $detail_show_price[ 'show_price' ];
            $info[ 'show_type' ] = $detail_show_price[ 'show_type' ];
            //组装数据
            if (!empty($info[ 'skuList' ])){
                foreach ($info[ 'skuList' ] as &$value){
                    $value[ 'member_discount' ] = $info[ 'goods' ][ 'member_discount' ] ?? '';
                    $list_show_price = $goods_active_price_service->getShowPrice($value, $this->member_id);
                    $value[ 'show_price' ] = $list_show_price[ 'show_price' ];
                    $value[ 'show_type' ] = $list_show_price[ 'show_type' ];
                }
            }
        }


        return $info;
    }

    /**
     * 获取商品列表供组件调用
     * @param array $where
     * @return array
     */
    public function getGoodsComponents(array $where = [])
    {
        $field = 'goods_id,goods_name,sub_title,goods_type,goods_cover,unit,sale_num + goods.virtual_sale_num as sale_num,member_discount,label_ids,brand_id,is_limit,limit_type,max_buy,min_buy';

        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ],
            [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ],
            [ 'status', '=', 1 ]
        ];

        if (!empty($where[ 'goods_ids' ])) {
            $sku_where[] = [ 'goods.goods_id', 'in', $where[ 'goods_ids' ] ];
        }

        // 参数过滤
        if (!empty($where[ 'order' ]) && in_array($where[ 'order' ], [ 'sale_num', 'price' ])) {
            $order = $where[ 'order' ] . ' desc';
        } else {
            $sort_config = ( new CoreGoodsConfigService() )->getSortConfig();
            if($sort_config['sort_column' ] =='sale_price') {
                $sort_config['sort_column' ] = 'goodsSku.sale_price';
            }
            $order = $sort_config[ 'sort_column' ] . ' ' . $sort_config[ 'sort_type' ];
        }

        $list = $this->model
            ->withSearch([ "goods_category", "label_ids", 'service_ids' ], $where)
            ->field($field)
            ->withJoin([
                'goodsSku' => [ 'sku_id', 'sku_name', 'sku_image', 'sku_no', 'goods_id', 'sku_spec_format', 'price', 'market_price', 'sale_price', 'stock', 'weight', 'volume', 'member_price' ]
            ])
            ->where($sku_where)->order($order)->append([ 'goods_cover_thumb_mid', 'goods_label_name', 'goods_brand' ])
            ->limit($where[ 'num' ])
            ->select()->toArray();
        $goods_active_price_service = (new CoreGoodsActivePriceService());
        foreach ($list as $k => &$v) {
            if (!empty($v[ 'goodsSku' ])) {
                $v[ 'goodsSku' ][ 'member_discount' ] = $v[ 'member_discount' ];
                $list_show_price = $goods_active_price_service->getShowPrice($v[ 'goodsSku' ], $this->member_id);
                $v[ 'goodsSku' ][ 'show_price' ] = $list_show_price[ 'show_price' ];
                $v[ 'goodsSku' ][ 'show_type' ] = $list_show_price[ 'show_type' ];
            }
        }
        return $list;
    }

    public function getMemberInfo()
    {
        $member_model = new Member();
        $member_field = 'member_level';
        $member_info = $member_model->where([
            [ 'member_id', '=', $this->member_id ]
        ])->field($member_field)
            ->with([
                // 会员等级
                'memberLevelData' => function ($query) {
                    $query->field('level_id, level_name, status, level_benefits, level_gifts');
                },
            ])
            ->findOrEmpty()->toArray();
        return $member_info;
    }

    /**
     * 查询商品的会员价
     * @param $member_info
     * @param string $member_discount 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
     * @param string $member_price 会员价，json格式，指定会员价，数据结构为：{"level_12":"92.00","level_13":"72.00","level_14":"66.00","level_15":"45.00"}
     * @param $price
     * @return int|string
     */
    public function getMemberPrice($member_info, $member_discount, $member_price, $price)
    {
        if (empty($member_discount)) {
            return $price;
        }

        // 未找到会员，排除
        if (empty($member_info)) {
            return $price;
        }

        // 没有会员等级，排除
        if (!empty($member_info) && empty($member_info[ 'member_level' ])) {
            return $price;
        }

        if ($member_discount == 'discount') {
            // 按照会员等级折扣计算

            // 默认按会员享受折扣计算
            if (!empty($member_info[ 'memberLevelData' ][ 'level_benefits' ])
                && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ])
                && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'is_use' ])) {

                $price = number_format($price * $member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'discount' ] / 10, 2, '.', '');
            }

        } elseif ($member_discount == 'fixed_price') {
            // 指定会员价
            if (!empty($member_price)) {
                $member_price = json_decode($member_price, true);
                if (!empty($member_price[ 'level_' . $member_info[ 'member_level' ] ])) {
                    $member_level_price = $member_price[ 'level_' . $member_info[ 'member_level' ] ];
                    $price = number_format($member_level_price, 2, '.', '');
                }
            }
        }

        return $price;
    }

    /**
     * 查询商品的会员价
     * @param $member_info
     * @param string $member_discount 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
     * @param $sku_list
     * @return int
     */
    public function getMemberPriceByList($member_info, $member_discount, &$sku_list)
    {

        // 是否按照原价返回
        $is_default = false;

        if (empty($member_discount)) {
            $is_default = true;
        }

        // 未找到会员，排除
        if (empty($member_info)) {
            $is_default = true;
        }

        // 没有会员等级，排除
        if (!empty($member_info) && empty($member_info[ 'member_level' ])) {
            $is_default = true;
        }

        foreach ($sku_list as $k => &$v) {

            if ($is_default) {
                $v[ 'member_price' ] = $v[ 'price' ];
            } else {
                if ($member_discount == 'discount') {
                    // 按照会员等级折扣计算

                    // 默认按会员享受折扣计算
                    if (!empty($member_info[ 'memberLevelData' ][ 'level_benefits' ])
                        && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ])
                        && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'is_use' ])) {
                        $v[ 'member_price' ] = number_format($v[ 'price' ] * $member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'discount' ] / 10, 2, '.', '');
                    } else {
                        $v[ 'member_price' ] = $v[ 'price' ];
                    }

                } elseif ($member_discount == 'fixed_price') {
                    // 指定会员价
                    if (!empty($v[ 'member_price' ])) {
                        $member_price = json_decode($v[ 'member_price' ], true); // 会员价，json格式，指定会员价
                        if (!empty($member_price[ 'level_' . $member_info[ 'member_level' ] ])) {
                            $member_level_price = $member_price[ 'level_' . $member_info[ 'member_level' ] ];
                            $v[ 'member_price' ] = number_format($member_level_price, 2, '.', '');
                        } else {
                            $v[ 'member_price' ] = $v[ 'price' ];
                        }
                    }
                }
            }
        }

        return $sku_list;

    }

    /**
     * 猜你喜欢
     * @return void|array
     */
    public function getRecommend()
    {
        $field = 'goods_id,goods_name,sub_title,goods_category,goods_type,goods_cover,unit,status,sale_num + goods.virtual_sale_num as sale_num,member_discount,is_discount,virtual_receive_type';
        $order = 'sale_num desc';

        $where['goods_sku_is_default'] = 1;
        $where['status'] = 1;
        $search_model = $this->model
            ->withSearch([  'status' ], $where)
            ->field($field)
            ->withJoin([ 'goodsSku' ])
            ->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ]);
        $list = $this->pageQuery($search_model);
        if (!empty($list['data'])){
            $goods_active_price_service = (new CoreGoodsActivePriceService());
            foreach ($list[ 'data' ] as $k => &$v) {
                $v[ 'goodsSku' ][ 'member_discount' ] = $v[ 'goods' ][ 'member_discount' ] ?? '';
                //获取展示活动价格
                $show_price_data = $goods_active_price_service->getShowPrice($v[ 'goodsSku' ], $this->member_id);
                $v[ 'goodsSku' ][ 'show_price' ] = $show_price_data[ 'show_price' ];
                $v[ 'goodsSku' ][ 'show_type' ] = $show_price_data[ 'show_type' ];
            }
        }

        return $list;

    }

}
