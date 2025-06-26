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

use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\goods\RankDict;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\goods\Rank;
use addon\shop\app\model\goods\Stat;
use addon\shop\app\service\core\goods\CoreGoodsActivePriceService;
use addon\shop\app\service\core\goods\CoreGoodsRankConfigService;
use core\base\BaseApiService;


/**
 * 商品榜单服务层
 * Class RankService
 * @package addon\shop\app\service\api\goods
 */
class RankService extends BaseApiService
{
    protected $goodsStatModel;
    protected $goodsModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Rank();
        $this->goodsStatModel = new Stat();
        $this->goodsModel = new Goods();
    }

    /**
     * 获取商品排行榜配置
     * @return array
     */
    public function getGoodsRankConfig()
    {
        return ( new CoreGoodsRankConfigService() )->getGoodsRankConfig();
    }

    /**
     * 获取商品榜单列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'rank_id,name,goods_source';
        $order = 'sort desc,create_time desc';
        $list = $this->model->where([ [ 'status', '=', RankDict::ON ] ])->withSearch([ "name" ], $where)->field($field)->order($order)->select()->toArray();

        return $list;
    }

    /**
     * 获取商品榜单列表
     * @param array $data
     * @return array
     */
    public function getGoodsPage($data)
    {
        $rank_id = $data[ 'rank_id' ];
        $rank_info = $this->model->field('rank_id,name,rank_type,rule_type,goods_source,goods_json,category_ids,brand_ids,label_ids')->where([ [ 'rank_id', '>', 0 ] ])->find($rank_id);
        $list = [];
        if ($rank_info) {
            $list = $this->getGoodsList($rank_info, 0,$data);
        }
        return $list;
    }

    /**
     * 获取商品榜单组件列表
     * @param array $data
     * @return array
     */
    public function getRankComponents($data)
    {
        $rank_id = $data[ 'rank_id' ];
        $limit = $data[ 'limit' ];
        $where[] = [
            [ 'status', '=', RankDict::ON ]
        ];
        $order = '';
        if ($rank_id > 0) {
            $where[] = [ 'rank_id', '=', $rank_id ];
            $order = 'sort desc,create_time desc';
        }else{
            $where[] = [ 'rank_id', '>', 0 ];
        }

        $rank_info = $this->model->field('rank_id,name,rank_type,rule_type,goods_source,goods_json,category_ids,brand_ids,label_ids')->where($where)->order($order)->findOrEmpty()->toArray();
        $data = [];
        if (!empty($rank_info)) {
            $data[ 'rank_id' ] = $rank_info[ 'rank_id' ];
            $data[ 'name' ] = $rank_info[ 'name' ];
            $data[ 'goods_list' ] = $this->getGoodsList($rank_info, $limit);
        }
        return $data;
    }

    /**
     * 根据榜单设置条件查询对应的商品信息
     * @param $rank_info
     * @param int $limit
     * @param array $data
     * @return array
     * @throws \think\db\exception\DbException
     */
    protected function getGoodsList($rank_info, $limit = 0,$data = [])
    {
        // 根据排行周期 获取时间区间
        $date_end = strtotime(date('Y-m-d 23:59:59'));
        switch ($rank_info[ 'rank_type' ]) {
            case RankDict::DAY:
                $day = 1;
                break;
            case RankDict::WEEK:
                $day = 7;
                break;
            case RankDict::MONTH:
                $day = 30;
                break;
            case RankDict::QUARTER:
                $day = 90;
                break;
        }
        $date_start = $date_end - 86400 * $day + 1;
        $field = 'goods.goods_id,goods_name,goods_cover,unit,goods.sale_num + goods.virtual_sale_num as sale_num,member_discount,sum(stat.sale_num)+ goods.virtual_sale_num as stat_sale_num,sum(stat.collect_num) as stat_collect_num,sum(stat.evaluate_num) as stat_evaluate_num,sum(stat.access_num) as stat_access_num,goods.create_time,goods.sort as goods_sort';
        $where[] = [
            [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ],
            [ 'goods.status', '=', 1 ],
            [ 'goods.delete_time', '=', 0 ],
        ];
        $order = 'stat_' . $rank_info[ 'rule_type' ] . '_num desc,goods_sort desc,create_time desc';
        // 来源类型 goods=指定商品，category=指定分类，brand=指定品牌, label=指定标签
        if ($rank_info[ 'goods_source' ] == RankDict::GOODS) {
            // 根据配置的排行周期查询对应的商品
            $goods_json = $rank_info[ 'goods_json' ];
            $config_goods_ids = array_column($goods_json, 'goods_id');
            $list = $this->goodsModel
                ->alias('goods')
                ->field($field)
                ->where([ [ 'goods.goods_id', 'in', $config_goods_ids ], [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ], [ 'goods.status', '=', 1 ],[ 'goods.delete_time', '=', 0 ] ])
                ->whereBetweenTime('date_time', $date_start, $date_end)
                ->join('shop_goods_stat stat', 'goods.goods_id = stat.goods_id', 'left')
                ->group('goods.goods_id')
                ->append([ 'goods_cover_thumb_mid' ])
                ->select()
                ->toArray();
            $sort_goods_json = array_column($goods_json, null, 'goods_id');
            foreach ($list as &$item) {
                $item[ 'sort' ] = $sort_goods_json[ $item[ 'goods_id' ] ][ 'sort' ] ?? 0;
            }

            usort($list, function($list_one, $list_two)use ($rank_info) {
                // 按 排序号 排序
                if ($list_one['sort'] != $list_two['sort']) {
                    return $list_two['sort'] <=> $list_one['sort'];
                }
                // 如果 排序号 相等，则按 排序规则 排序
                if ($list_one['stat_'.$rank_info[ 'rule_type' ].'_num'] != $list_two['stat_'.$rank_info[ 'rule_type' ].'_num']) {
                    return $list_two['stat_'.$rank_info[ 'rule_type' ].'_num'] <=> $list_one['stat_'.$rank_info[ 'rule_type' ].'_num'];
                }
                //如果 排序规则 相等，则按 商品排序号 排序
                if ($list_one['goods_sort'] != $list_two['goods_sort']) {
                    return $list_two['goods_sort'] <=> $list_one['goods_sort'];
                }

                // 如果 商品排序号 也相等，则按 创建时间 排序
                return $list_two['create_time'] <=> $list_one['create_time'];
            });

            if ($limit == 0) {
                $goods_list = [
                    'total' => count($list),
                    'per_page' => 1,
                    'current_page' => 1,
                    'last_page' => 1,
                    'data' => $list,
                ];
            } else {
                $goods_list = array_slice($list, 0, $limit);
            }

        } elseif ($rank_info[ 'goods_source' ] == RankDict::CATEGORY) {
            $category_ids = $rank_info[ 'category_ids' ];
            $query = $this->goodsModel
                ->alias('goods')
                ->field($field)
                ->where($where)
                ->whereBetweenTime('date_time', $date_start, $date_end)
                ->withSearch([ 'goods_category' ], [ 'goods_category' => $category_ids ])
                ->join('shop_goods_stat stat', 'goods.goods_id = stat.goods_id', 'left')
                ->group('goods.goods_id')
                ->limit($limit)
                ->order($order)
                ->append([ 'goods_cover_thumb_mid', 'goods_label_name', 'goods_brand' ]);
            if ($limit == 0) {
                $goods_list = $this->pageQuery($query);
            } else {
                $goods_list = $query->select()->toArray();
            }
        } elseif ($rank_info[ 'goods_source' ] == RankDict::BRAND) {
            $brand_ids = $rank_info[ 'brand_ids' ];
            $query = $this->goodsModel
                ->alias('goods')
                ->field($field)
                ->where($where)
                ->whereBetweenTime('date_time', $date_start, $date_end)
                ->whereIn('goods.brand_id', $brand_ids)
                ->join('shop_goods_stat stat', 'goods.goods_id = stat.goods_id', 'left')
                ->group('goods.goods_id')
                ->limit($limit)
                ->order($order)
                ->append([ 'goods_cover_thumb_mid', 'goods_label_name', 'goods_brand' ]);
            if ($limit == 0) {
                $goods_list = $this->pageQuery($query);
            } else {
                $goods_list = $query->select()->toArray();
            }
        } elseif ($rank_info[ 'goods_source' ] == RankDict::LABEL) {
            $label_ids = $rank_info[ 'label_ids' ];
            $query = $this->goodsModel
                ->alias('goods')
                ->field($field)
                ->where($where)
                ->whereBetweenTime('date_time', $date_start, $date_end)
                ->withSearch([ 'label_ids' ], [ 'label_ids' => $label_ids ])
                ->join('shop_goods_stat stat', 'goods.goods_id = stat.goods_id', 'left')
                ->group('goods.goods_id')
                ->limit($limit)
                ->order($order)
                ->append([ 'goods_cover_thumb_mid', 'goods_label_name', 'goods_brand' ]);
            if ($limit == 0) {
                $goods_list = $this->pageQuery($query);
            } else {
                $goods_list = $query->select()->toArray();
            }
        } else {
            $query = $this->goodsModel
                ->alias('goods')
                ->field($field)
                ->where($where)
                ->whereBetweenTime('date_time', $date_start, $date_end)
                ->join('shop_goods_stat stat', 'goods.goods_id = stat.goods_id', 'left')
                ->group('goods.goods_id')
                ->limit($limit)
                ->order($order)
                ->append([ 'goods_cover_thumb_mid', 'goods_label_name', 'goods_brand' ]);
            if ($limit == 0) {
                $goods_list = $this->pageQuery($query);
            } else {
                $goods_list = $query->select()->toArray();
            }
        }
        //获取商品SKU、会员价格信息
        $goods_active_price_service = (new CoreGoodsActivePriceService());
        if (isset($goods_list[ 'data' ])) {
            if (!empty($goods_list[ 'data' ])) {
                foreach ($goods_list[ 'data' ] as $key => &$item) {
                    $item[ 'rank_num' ] = $key + 1;
                    $item[ 'goodsSku' ] = $this->getGoodsSku($item);
                    if (!empty($item[ 'goodsSku' ])) {
                        $item[ 'goodsSku' ][ 'member_discount' ] = $item[ 'member_discount' ];
                        $show_price = $goods_active_price_service->getShowPrice($item[ 'goodsSku' ], $this->member_id);
                        $item[ 'goodsSku' ][ 'show_price' ] = $show_price[ 'show_price' ];
                        $item[ 'goodsSku' ][ 'show_type' ] = $show_price[ 'show_type' ];
                    }
                }
            }
        } else {
            if (!empty($goods_list)) {
                foreach ($goods_list as $key => &$item) {
                    $item[ 'rank_num' ] = $key + 1;
                    $item[ 'goodsSku' ] = $this->getGoodsSku($item);
                    if (!empty($item[ 'goodsSku' ])) {
                        $item[ 'goodsSku' ][ 'member_discount' ] = $item[ 'member_discount' ];
                        $show_price = $goods_active_price_service->getShowPrice($item[ 'goodsSku' ], $this->member_id);
                        $item[ 'goodsSku' ][ 'show_price' ] = $show_price[ 'show_price' ];
                        $item[ 'goodsSku' ][ 'show_type' ] = $show_price[ 'show_type' ];
                    }
                }
            }
        }
        return $goods_list;
    }

    /**
     * 根据排行周期查询商品统计数据
     */
    protected function getGoodsIds($data)
    {
        $rank_type = $data[ 'rank_type' ];
        $date_end = strtotime(date('Y-m-d 23:59:59'));
        switch ($rank_type) {
            case RankDict::DAY:
                $day = 1;
                break;
            case RankDict::WEEK:
                $day = 7;
                break;
            case RankDict::MONTH:
                $day = 30;
                break;
            case RankDict::QUARTER:
                $day = 90;
                break;
        }
        $date_start = $date_end - 86400 * $day + 1;
        $goods_ids = $this->goodsStatModel->where([ [ 'id', '>', 0 ] ])->whereBetweenTime('date_time', $date_start, $date_end)->column('goods_id');
        return $goods_ids;
    }

    /**
     * 获取商品SKU
     * @param $data
     * @return mixed
     */
    protected function getGoodsSku($data)
    {
        $goods_sku_info = ( new GoodsSku() )->field('sku_id,sku_name,sku_image,goods_id,price,sku_spec_format,sale_price,stock,member_price')
            ->where([
                [ 'goods_id', '=', $data[ 'goods_id' ] ]
            ])
            ->find();
        return $goods_sku_info;
    }

}
