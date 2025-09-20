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

namespace addon\shop\app\service\core\goods;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\active\DiscountDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\service\api\goods\GoodsService;
use addon\shop\app\service\api\marketing\NewcomerService;
use core\base\BaseCoreService;

/**
 * 商品活动价服务层
 */
class CoreGoodsActivePriceService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询商品的活动价格
     * @param $sku_info
     * @param $member_id
     * @return array
     */
    public function getActivePrice($sku_info, $member_id)
    {
        $price = floatval($sku_info[ 'price'  ]);
        $show_price_data = [
            [ 'show_price' => $price, 'show_type' => GoodsDict::ORIGINAL_PRICE ]
        ];

        $goods_service = new GoodsService();

        if ($member_id > 0) {
            $member_price = $goods_service->getMemberPrice($goods_service->getMemberInfo(), $sku_info[ 'member_discount' ], $sku_info[ 'member_price' ], $price);
            $show_price_data[] = [ 'show_price' => $member_price, 'show_type' => GoodsDict::MEMBER_PRICE ];
        }

        $discount = (new DiscountGoods())->where([
            [ 'goods_id', '=', $sku_info['goods_id'] ],
            [ 'sku_id', '=', $sku_info['sku_id'] ],
            [ 'status', '=', DiscountDict::ACTIVE ],
            [ 'is_enabled', '=', DiscountDict::YES ],
        ])->findOrEmpty();

        if (!$discount->isEmpty()) {
            $discount_price = floatval($sku_info[ 'sale_price' ] ?? 0);
            $show_price_data[] = [ 'show_price' => $discount_price, 'show_type' => GoodsDict::DISCOUNT_PRICE, 'discount_id' => $discount['discount_id'] ];
        }

        return $this->getMinShowPrice($show_price_data, $price);
    }

    /**
     * 查询商品的展示价格
     * @param $sku_info
     * @param $member_id
     * @return array
     */
    public function getShowPrice($sku_info, $member_id)
    {
        $type = $sku_info[ 'type' ] ?? '';
        $price = floatval($sku_info[ 'price' ]);
        $show_price_data = [
            [ 'show_price' => $price, 'show_type' => GoodsDict::ORIGINAL_PRICE ]
        ];

        $goods_service = new GoodsService();

        if ($member_id > 0) {
            $member_price = $goods_service->getMemberPrice($goods_service->getMemberInfo(), $sku_info[ 'member_discount' ], $sku_info[ 'member_price' ], $price);
            $show_price_data[] = [ 'show_price' => $member_price, 'show_type' => GoodsDict::MEMBER_PRICE ];
        }

        $discount = (new DiscountGoods())->where([
            [ 'goods_id', '=', $sku_info['goods_id'] ],
            [ 'sku_id', '=', $sku_info['sku_id'] ],
            [ 'status', '=', DiscountDict::ACTIVE ],
            [ 'is_enabled', '=', DiscountDict::YES ],
        ])->findOrEmpty();

        if (!$discount->isEmpty()) {
            $discount_price = floatval($sku_info[ 'sale_price' ] ?? 0);
            $show_price_data[] = [ 'show_price' => $discount_price, 'show_type' => GoodsDict::DISCOUNT_PRICE ];
        }

//        // 如果有活动类型，只保留该类型对应的价格和原价
//        if (!empty($type)) {
//            $show_type = '';
//
//            switch ($type){
//                case ActiveDict::DISCOUNT:
//                    $show_type = GoodsDict::DISCOUNT_PRICE;
//                    break;
//            }
//            if (!empty($show_type)){
//                $show_price_data = array_filter($show_price_data, function ($item) use ($show_type) {
//                    return in_array($item[ 'show_type' ], [GoodsDict::ORIGINAL_PRICE, $show_type]);
//                });
//            }else{
//                $show_price_data = [[ 'show_price' => $price, 'show_type' => GoodsDict::ORIGINAL_PRICE ]];
//            }
//
//            // 重建索引
//            $show_price_data = array_values($show_price_data);
//        }
        return $this->getMinShowPrice($show_price_data, $price);
    }

    /**
     * 提取最低价格（优先 original_price）
     * @param $price_data
     * @param $original_price
     * @return array
     */
    private function getMinShowPrice(array $price_data, float $original_price): array
    {
        $priority = [
            'discount_price'  => 1,
            'member_price'    => 2,
            'original_price'  => 3,
        ];

        usort($price_data, function ($one, $two) use ($priority){
            $price_cmp = floatval($one[ 'show_price' ]) <=> floatval($two[ 'show_price' ]);

            if ($price_cmp === 0) {
                // 价格相等时按优先级排序，数字越小优先级越高
                return ($priority[ $one[ 'show_type' ] ] ?? 99) <=> ($priority[ $two[ 'show_type' ] ] ?? 99);
            }

            return $price_cmp;
        });

        $min = $price_data[0];

        if ($min[ 'show_type' ] !== GoodsDict::ORIGINAL_PRICE && floatval($min[ 'show_price' ]) == $original_price) {
            $min[ 'show_type' ] = GoodsDict::ORIGINAL_PRICE;
        }
        return $min;
    }



}
