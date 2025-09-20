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

namespace addon\shop\app\service\admin\marketing;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\model\active\Active;
use addon\shop\app\model\active\ActiveGoods;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\service\core\marketing\CoreActiveService;
use addon\shop\app\service\core\marketing\CoreNewcomerService;
use core\exception\AdminException;
use core\base\BaseAdminService;
use core\exception\CommonException;
use think\db\Query;

/**
 * 新人专享服务层
 * Class NewcomerService
 * @package addon\shop\app\service\admin\marketing
 */
class NewcomerService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Active();
    }

    /**
     * 新人专享设置
     * @param array $data
     * @return bool
     */
    public function setConfig(array $data)
    {
        $goods_data = json_decode($data[ 'goods_data' ], true);
        if ($data[ 'active_status' ] == ActiveDict::ACTIVE) {
            $this->checkGoods($goods_data);
        }
        try {
            $active_value[ 'validity_type' ] = $data[ 'validity_type' ];
            $active_value[ 'validity_day' ] = $data[ 'validity_day' ];
            $active_value[ 'validity_time' ] = !empty($data[ 'validity_time' ]) ? strtotime($data[ 'validity_time' ]) : 0;
            $active_value[ 'participation_way' ] = $data[ 'participation_way' ];
            $active_value[ 'appoint_time' ] = !empty($data[ 'appoint_time' ]) ? strtotime($data[ 'appoint_time' ]) : 0;
            $active_value[ 'limit_num' ] = $data[ 'limit_num' ];
            $active_value[ 'banner_list' ] = $data[ 'banner_list' ];

            $active_goods = [];
            foreach ($goods_data as $k => $v) {
                $active_goods[] = [
                    'goods_id' => $v[ 'goods_id' ],
                    'sku_id' => $v[ 'sku_id' ],
                    'active_goods_type' => ActiveDict::GOODS_SINGLE,
                    'active_class' => ActiveDict::NEWCOMER_DISCOUNT,
                    'active_goods_value' => json_encode($v),
                    'active_goods_status' => $data[ 'active_status' ],
                    'active_goods_price' => $v[ 'newcomer_price' ],
                ];
            }
            $data[ 'active_goods_info' ] = $data[ 'goods_data' ];
            $data[ 'active_goods' ] = $active_goods;
            $data[ 'active_type' ] = ActiveDict::GOODS;
            $data[ 'active_goods_type' ] = ActiveDict::GOODS_SINGLE;
            $data[ 'active_class' ] = ActiveDict::NEWCOMER_DISCOUNT;
            $data[ 'active_value' ] = $active_value;

            $info = $this->model->where([[ 'active_class', '=', ActiveDict::NEWCOMER_DISCOUNT ] ])->findOrEmpty();
            if ($info->isEmpty()) {
                ( new CoreActiveService() )->add($data);
            } else {
                ( new CoreActiveService() )->edit($info[ 'active_id' ], $data);
            }
            return true;
        } catch (\Exception $e) {
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 获取新人专享配置
     * @return array
     */
    public function getConfig()
    {
        $value = ( new CoreNewcomerService() )->getConfig();
        if (!empty($value[ 'active_id' ])) {
            $active_goods = ( new ActiveGoods() )->field('goods_id,sku_id,active_goods_value')->where([ [ 'active_id', '=', $value[ 'active_id' ] ], [ 'active_class', '=', ActiveDict::NEWCOMER_DISCOUNT ] ])
                ->with([
                    'goods' => function ($query) {
                        $query->field('goods_id,goods_name,goods_type,goods_cover')->append([ 'goods_type_name', 'goods_cover_thumb_mid' ]);
                    },
                    'goodsSkuOne' => function ($query) {
                        $query->field('sku_id,sku_name,sku_image,goods_id,price,stock');
                    }
                ])
                ->select()->toArray();
            foreach ($active_goods as $k => &$item) {
                if (!empty($item[ 'goods' ])) {
                    $item[ 'goods_name' ] = $item[ 'goods' ][ 'goods_name' ];
                    $item[ 'goods_type' ] = $item[ 'goods' ][ 'goods_type' ];
                    $item[ 'goods_type_name' ] = $item[ 'goods' ][ 'goods_type_name' ];
                    $item[ 'sku_name' ] = $item[ 'goodsSkuOne' ][ 'sku_name' ];
                    $item[ 'sku_image' ] = $item[ 'goodsSkuOne' ][ 'sku_image' ];
                    if (empty($item[ 'sku_image' ])) {
                        $item[ 'sku_image' ] = $item[ 'goods' ][ 'goods_cover_thumb_mid' ];
                    }
                    $item[ 'price' ] = $item[ 'goodsSkuOne' ][ 'price' ];
                    $item[ 'stock' ] = $item[ 'goodsSkuOne' ][ 'stock' ];
                    $item[ 'active_goods_value' ] = json_decode($item[ 'active_goods_value' ], true);
                } else {
                    unset($active_goods[ $k ]);
                }
            }
            $active_goods = array_values($active_goods);
            $value[ 'active_goods' ] = $active_goods;
        }
        return $value;
    }

    /**
     * 商品结构校验
     * @param $goods_data
     * @return void
     */
    public function checkGoods($goods_data)
    {
        if (empty($goods_data)) throw new AdminException('ACTIVE_GOODS_NOT_EMPTY');
        foreach ($goods_data as $v) {
            if (empty($v)) throw new AdminException('ACTIVE_GOODS_SKU_NOT_EMPTY');
            if (!isset($v[ 'newcomer_price' ]) || $v[ 'newcomer_price' ] == '') throw new AdminException('ACTIVE_GOODS_NEWCOMER_PRICE_NOT_EMPTY');
        }
    }

    /**
     * 获取商品选择分页列表
     * @param array $where
     * @return array
     */
    public function getSelectPage(array $where = [])
    {
        $goods_model = new Goods();
        $goods_sku_model = new GoodsSku();
        $active_goods_model = new ActiveGoods();
        $active_goods = $active_goods_model->field('goods_id,sku_id,active_goods_value')->where([
            [ 'active_class', '=', ActiveDict::NEWCOMER_DISCOUNT ],
//            [ 'active_goods_status', '=', ActiveDict::ACTIVE ]
        ])->select()->toArray();
        $active_goods_ids = array_unique(array_column($active_goods, 'goods_id'));
        $active_sku_ids = array_column($active_goods, 'sku_id');
        $active_goods = array_column($active_goods, null, 'sku_id');

        $field = 'goods_id, goods_name, goods_type, goods_cover,goods_image, stock,sub_title,goods_desc';
        $order = 'sort desc,create_time desc';

        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ],
            [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ]
        ];

        if (!empty($where[ 'keyword' ])) {
            $sku_where[] = [ 'goods_name|sub_title', 'like', '%' . $where[ 'keyword' ] . '%' ];
        }

        if ($where[ 'select_type' ] == 'all') {
            $sku_where[] = [ 'goods.stock', '>', 0 ];
            $sku_where[] = [ 'status', '=', 1 ];
            $sku_where[] = [ 'goods.goods_id', 'in', $active_goods_ids ];
        }
        if ($where[ 'select_type' ] == 'selected') {
            $goods_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $where[ 'sku_ids' ] ]
            ])->column('goods_id');
            $sku_where[] = [ 'goods.goods_id', 'in', $goods_ids ];
        }

        $verify_goods_ids = [];
        $verify_sku_ids = [];
        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_goods_ids' ]) && !empty($active_goods_ids)) {
            $verify_goods_ids = $goods_model->where([
                [ 'goods_id', 'in', $where[ 'verify_goods_ids' ] ],
                [ 'status', '=', 1 ]
            ])->field('goods_id')->select()->toArray();

            if (!empty($verify_goods_ids)) {
                // 移除不在新人活动列表中的商品
                foreach ($verify_goods_ids as $k => $v) {
                    if (!in_array($v[ 'goods_id' ], $active_goods_ids)) unset($verify_goods_ids[ $k ]);
                }
                $verify_goods_ids = array_values($verify_goods_ids);
                $verify_goods_ids = array_column($verify_goods_ids, 'goods_id');
            }
        }

        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_sku_ids' ]) && !empty($active_sku_ids)) {
            $verify_sku_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $where[ 'verify_sku_ids' ] ]
            ])->field('sku_id')->select()->toArray();

            if (!empty($verify_sku_ids)) {
                // 移除不在新人活动列表中的商品
                foreach ($verify_sku_ids as $k => $v) {
                    if (!in_array($v[ 'sku_id' ], $active_sku_ids)) unset($verify_sku_ids[ $k ]);
                }
                $verify_sku_ids = array_values($verify_sku_ids);
                $verify_sku_ids = array_column($verify_sku_ids, 'sku_id');
            }
        }

        $search_model = $goods_model
            ->withSearch([ "goods_category", "goods_type" ], $where)
            ->field($field)
            ->withJoin([
                'goodsSku' => [ 'sku_id', 'sku_name', 'goods_id', 'price', 'stock', 'sku_spec_format' ],
            ])
            ->with([
                'skuList' => function (Query $query) use ($active_sku_ids) {
                    $query->where([ [ 'sku_id', 'in', $active_sku_ids ] ]);
                }
            ])
            ->where($sku_where)->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ]);
        $list = $this->pageQuery($search_model);

        foreach ($list[ 'data' ] as &$item) {
            if (!empty($item[ 'skuList' ]) && !empty($active_goods)) {
                foreach ($item[ 'skuList' ] as &$sku_item) {
                    $active_goods_value = json_decode($active_goods[ $sku_item[ 'sku_id' ] ][ 'active_goods_value' ], true);
                    $sku_item[ 'newcomer_price' ] = $active_goods_value[ 'newcomer_price' ] ?? 0;
                }
                if (!empty($item[ 'goodsSku' ])) {
                    if (in_array($item[ 'goodsSku' ][ 'sku_id' ], $active_sku_ids)) {
                        $active_goods_value = json_decode($active_goods[ $item[ 'goodsSku' ][ 'sku_id' ] ][ 'active_goods_value' ], true);
                        $item[ 'goodsSku' ][ 'newcomer_price' ] = $active_goods_value[ 'newcomer_price' ] ?? 0;
                    } else {
                        $item[ 'goodsSku' ][ 'newcomer_price' ] = $item[ 'skuList' ][ 0 ][ 'newcomer_price' ];
                    }
                }
            }
        }

        $list[ 'verify_goods_ids' ] = $verify_goods_ids;
        $list[ 'verify_sku_ids' ] = $verify_sku_ids;

        return $list;
    }

    /**
     * 获取已选商品列表
     * @param array $where
     * @return array
     */
    public function getSelectSku(array $where = [])
    {
        $goods_model = new Goods();
        $active_goods_model = new ActiveGoods();
        $active_goods = $active_goods_model->field('goods_id,sku_id,active_goods_value')->where([
            [ 'active_class', '=', ActiveDict::NEWCOMER_DISCOUNT ],
//            [ 'active_goods_status', '=', ActiveDict::ACTIVE ]
        ])->select()->toArray();
        $active_goods_ids = array_unique(array_column($active_goods, 'goods_id'));
        $active_sku_ids = array_column($active_goods, 'sku_id');
        $active_goods = array_column($active_goods, null, 'sku_id');
        $field = 'goods_id, goods_name, goods_type, goods_cover, stock';
        $order = 'sort desc,create_time desc';

        $select_goods_list = [];// 已选商品列表

        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_goods_ids' ]) && !empty($active_goods_ids)) {
            $verify_goods_ids = $goods_model->where([
                [ 'goods_id', 'in', $where[ 'verify_goods_ids' ] ],
                [ 'status', '=', 1 ]
            ])->field('goods_id')->select()->toArray();

            if (!empty($verify_goods_ids)) {
                // 移除不在新人活动列表中的商品
                foreach ($verify_goods_ids as $k => $v) {
                    if (!in_array($v[ 'goods_id' ], $active_goods_ids)) unset($verify_goods_ids[ $k ]);
                }
                $verify_goods_ids = array_values($verify_goods_ids);
                $verify_goods_ids = array_column($verify_goods_ids, 'goods_id');
            }

            $select_goods_list = $goods_model
                ->field($field)
                ->with([
                    'skuList' => function (Query $query) use ($active_sku_ids) {
                        $query->where([ [ 'sku_id', 'in', $active_sku_ids ] ]);
                    }
                ])
                ->where([
                    [ 'goods_id', 'in', $verify_goods_ids ]
                ])
                ->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ])
                ->select()->toArray();

            foreach ($select_goods_list as &$item) {
                if (!empty($item[ 'skuList' ]) && !empty($active_goods)) {
                    foreach ($item[ 'skuList' ] as &$sku_item) {
                        $active_goods_value = json_decode($active_goods[ $sku_item[ 'sku_id' ] ][ 'active_goods_value' ], true);
                        $sku_item[ 'newcomer_price' ] = $active_goods_value[ 'newcomer_price' ] ?? 0;
                    }
                    if (!empty($item[ 'goodsSku' ])) {
                        if (in_array($item[ 'goodsSku' ][ 'sku_id' ], $active_sku_ids)) {
                            $active_goods_value = json_decode($active_goods[ $item[ 'goodsSku' ][ 'sku_id' ] ][ 'active_goods_value' ], true);
                            $item[ 'goodsSku' ][ 'newcomer_price' ] = $active_goods_value[ 'newcomer_price' ] ?? 0;
                        } else {
                            $item[ 'goodsSku' ][ 'newcomer_price' ] = $item[ 'skuList' ][ 0 ][ 'newcomer_price' ];
                        }
                    }
                }
            }
        }

        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_sku_ids' ]) && !empty($active_sku_ids)) {
            $goods_sku_model = new GoodsSku();
            $verify_sku_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $where[ 'verify_sku_ids' ] ]
            ])->field('sku_id')->select()->toArray();

            if (!empty($verify_sku_ids)) {
                // 移除不在新人活动列表中的商品
                foreach ($verify_sku_ids as $k => $v) {
                    if (!in_array($v[ 'sku_id' ], $active_sku_ids)) unset($verify_sku_ids[ $k ]);
                }
                $verify_sku_ids = array_values($verify_sku_ids);
                $verify_sku_ids = array_column($verify_sku_ids, 'sku_id');
            }

            $goods_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $verify_sku_ids ]
            ])->field('goods_id')->select()->toArray();
            if (!empty($goods_ids)) {
                $goods_ids = array_column($goods_ids, 'goods_id');
                $goods_ids = array_unique($goods_ids);
            }

            $select_goods_list = $goods_model
                ->field($field)
                ->with([
                    'skuList' => function (Query $query) use ($verify_sku_ids) {
                        $query->where([ [ 'sku_id', 'in', $verify_sku_ids ] ]);
                    }
                ])
                ->where([
                    [ 'goods_id', 'in', $goods_ids ]
                ])
                ->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ])
                ->select()->toArray();

            foreach ($select_goods_list as &$item) {
                if (!empty($item[ 'skuList' ]) && !empty($active_goods)) {
                    foreach ($item[ 'skuList' ] as &$sku_item) {
                        $active_goods_value = json_decode($active_goods[ $sku_item[ 'sku_id' ] ][ 'active_goods_value' ], true);
                        $sku_item[ 'newcomer_price' ] = $active_goods_value[ 'newcomer_price' ] ?? 0;
                    }
                    if (!empty($item[ 'goodsSku' ])) {
                        if (in_array($item[ 'goodsSku' ][ 'sku_id' ], $verify_sku_ids)) {
                            $active_goods_value = json_decode($active_goods[ $item[ 'goodsSku' ][ 'sku_id' ] ][ 'active_goods_value' ], true);
                            $item[ 'goodsSku' ][ 'newcomer_price' ] = $active_goods_value[ 'newcomer_price' ] ?? 0;
                        } else {
                            $item[ 'goodsSku' ][ 'newcomer_price' ] = $item[ 'skuList' ][ 0 ][ 'newcomer_price' ];
                        }
                    }
                }
            }
        }

        return $select_goods_list;
    }

}
