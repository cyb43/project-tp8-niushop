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

namespace addon\shop\app\listener\marketing;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\model\active\ActiveGoods;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\service\admin\goods\GoodsService;
use app\dict\common\CommonActiveDict;

/**
 * 获取商品参与的营销活动
 * Class CheckGoodsMarking
 * @package addon\shop\app\listener
 */
class GetGoodsJoinInfo
{
    public function handle(array $params)
    {
        $goods_ids = $params['goods_ids'];
        $is_get_count = $params['is_get_count'] ?? 0;
        $is_all = $params['is_all'] ?? 0;
        if ($is_all) {
            $query = (new GoodsService())->getBatchAllQuery($params['where'], $goods_ids);
            $example_goods_ids = $query->column('goods.goods_id');
            $condition[] = ['goods_id', 'in', $example_goods_ids];
        } else {
            $condition[] = ['goods_id', 'in', $goods_ids];
        }
        $return = [];

        $query = (new ActiveGoods())->where([
            ['active_goods_status', '=', 'active'],
            ['active_goods_type', 'in', [ActiveDict::GOODS_SINGLE, ActiveDict::GOODS_INDEPENDENT]],
            ['active_class', '<>', ActiveDict::DISCOUNT]
        ])->where($condition)->with([
            'active' => function ($query) {
                $query->withField('active_id,active_name, active_desc, start_time, end_time');
            }
        ]);
        $discount_query = (new DiscountGoods())->where([
            ['status', '=', 'active']
        ])->where($condition)->with([
            'discount' => function ($query) {
                $query->withField('discount_id,name');
            }
        ]);
        if ($is_get_count == 1) {
            return $query->count() + $discount_query->count();
        }
        $common_active_dict_arr = CommonActiveDict::getActiveShort();

        $active_goods_list = $query->group('goods_id,active_id')->select()->toArray();
        foreach ($active_goods_list as $item) {
            $return[$item['goods_id']]['active_' . $item['active_id']] = [
                'join_id' => $item['active_id'],
                'join_type' => 'active_' . $item['active_class'],
                'short' => $common_active_dict_arr[$item['active_class']],
                'name' => $item['active']['active_name'] ?? $common_active_dict_arr[$item['active_class']]['active_name'],
            ];
        }
        // 折扣商品条件
        $discount_goods_list = $discount_query->group('goods_id,discount_id')->select()->toArray();
        $short_info = $common_active_dict_arr['discount'];
        foreach ($discount_goods_list as $item) {
            $return[$item['goods_id']]['discount_' . $item['discount_id']] = [
                'join_id' => $item['discount_id'],
                'join_type' => 'discount',
                'short' => $short_info,
                'name' => $item['discount']['name'] ?? $short_info['active_name'],
            ];
        }
        return $return;
    }
}
