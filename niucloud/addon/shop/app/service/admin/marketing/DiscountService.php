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
use addon\shop\app\dict\active\DiscountDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\model\discount\Discount;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderDiscounts;
use addon\shop\app\model\order\OrderGoods;
use app\service\core\sys\CoreConfigService;
use core\exception\AdminException;
use core\base\BaseAdminService;
use core\exception\CommonException;
use think\db\Query;
use think\facade\Db;
use think\facade\Log;

/**
 * 限时折扣服务层
 * Class DiscountService
 * @package addon\shop\app\service\admin\marketing
 */
class DiscountService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Discount();
    }

    /**
     * 获取限时折扣列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'discount_id,name,remark,start_time,end_time,status,create_time,order_money,order_num,member_num,success_num';
        $order = 'discount_id desc';
        $search_model = $this->model->where([ [ 'discount_id', '>', 0 ] ])->withSearch([ "name", "status", "active_id" ], $where)->append([ 'status_name' ])->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取限时折扣详情
     * @param int $discount_id
     * @return array
     */
    public function getInfo(int $discount_id)
    {
        $info = $this->model->field('discount_id,name,remark,start_time,end_time,status,create_time,order_money,order_num,member_num,success_num')
            ->where([ [ 'discount_id', '=', $discount_id ]])
            ->append([ 'status_name' ])
            ->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 获取限时折扣详情
     * @param int $discount_id
     * @return array
     */
    public function getDetail(int $discount_id)
    {
        $info = $this->model->where([ [ 'discount_id', '=', $discount_id ] ])->append([ 'status_name' ])->findOrEmpty()->toArray();
        $active_goods = ( new DiscountGoods() )
            ->field('discount_goods_id,discount_id,goods_id,sku_id,type,rate,reduce_money,discount_price,is_enabled')
            ->where([  [ 'discount_id', '=', $discount_id ] ])
            ->withJoin([ 'goods'  => function($query){
                $query->field('goods.goods_id,goods_name,goods_cover,goods_type');
            },'goodsSku' => function($query){
                $query->field('goodsSku.sku_id,price,sku_name,sku_image');
            }])
            ->append([ 'goods_cover_thumb_small'])
            ->hidden(['goods', 'goodsSku'])
            ->select()
            ->toArray();

        $goods_sku_data = $goods_info =[];
        foreach($active_goods as $value){
            $goods_sku_data[$value['goods_id']][] = $value;
        }
        foreach ($goods_sku_data as $value){
            if (count($value) > 1){

                $filtered = array_filter($value, fn($item) => $item['is_enabled'] == 1);
                $rates = array_map(fn($item) => floatval($item['rate']), $filtered);
                $reduce_money = array_map(fn($item) => floatval($item['reduce_money']), $filtered);
                $discount_price = array_map(fn($item) => floatval($item['discount_price']), $filtered);

                $goods_info[$value[0]['goods_id']] = [
                    'goods_cover_thumb_small' => $value[0]['goods_cover_thumb_small'] ?? '',
                    'goods_id' => $value[0]['goods_id'] ?? '',
                    'sku_id' => $value[0]['sku_id'] ?? '',
                    'type' => $value[0]['type'] ?? '',
                    'goods_type' => $value[0]['goods_type'] ?? '',
                    'goods_type_name' => GoodsDict::getType($value[0]['goods_type'])[ 'name' ] ?? '',
                    'goods_name' => $value[0]['goods_name'] ?? '',
                    'price' => $value[0]['price'] ?? '',
                    'min_rate' => !empty($rates) ? $this->formatNumber(min($rates)) : null,
                    'max_rate' => !empty($rates) ? $this->formatNumber(max($rates)) : null,
                    'min_reduce_money' => !empty($reduce_money) ? min($reduce_money) : null,
                    'max_reduce_money' => !empty($reduce_money) ? max($reduce_money) : null,
                    'min_discount_price' => !empty($discount_price) ? min($discount_price) : null,
                    'max_discount_price' => !empty($discount_price) ? max($discount_price) : null,
                    'spec_type' => 'multi',
                    'goods_sku_list' => $value,
                ];
            }else{
                $goods_info[$value[0]['goods_id']] = [
                    'goods_cover_thumb_small' => $value[0]['goods_cover_thumb_small'] ?? '',
                    'goods_id' => $value[0]['goods_id'] ?? '',
                    'sku_id' => $value[0]['sku_id'] ?? '',
                    'type' => $value[0]['type'] ?? '',
                    'goods_type' => $value[0]['goods_type'] ?? '',
                    'goods_type_name' => GoodsDict::getType($value[0]['goods_type'])[ 'name' ] ?? '',
                    'goods_name' => $value[0]['goods_name'] ?? '',
                    'price' => $value[0]['price'] ?? '',
                    'rate' => $this->formatNumber($value[0]['rate']) ?? '',
                    'reduce_money' => $value[0]['reduce_money'] ?? '',
                    'discount_price' => $value[0]['discount_price'] ?? '',
                    'spec_type' => 'single',
                    'goods_sku_list' => $value,
                ];
            }
        }
        $info['goods_info'] = array_values($goods_info);
        return $info;
    }

    /**
     * 添加限时折扣
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $goods_list = $data['goods_list'];
        $goods_ids = array_column($goods_list, 'goods_id');
        $data[ 'start_time' ] = strtotime($data[ 'start_time' ]);
        $data[ 'end_time' ] = strtotime($data[ 'end_time' ]);
        $data[ 'create_time' ] = time();
        $data[ 'update_time' ] = time();

        $this->checkGoods($goods_list);

        $check_condition = [
            [ 'discount_goods.goods_id', 'in', $goods_ids ],
        ];

        $goods_where = [
            [ 'discount.status', 'in', [ DiscountDict::NOT_ACTIVE, DiscountDict::ACTIVE ] ]
        ];
        $goods_where_or = [
            [ 'discount.start_time|discount.end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
            [ [ 'discount.start_time', '<=', $data[ 'start_time' ] ], [ 'discount.end_time', '>=', $data[ 'end_time' ] ] ],
            [ [ 'discount.start_time', '>=', $data[ 'start_time' ] ], [ 'discount.end_time', '<=', $data[ 'end_time' ] ] ],
        ];

        $discount_goods_model = new DiscountGoods();
        $count = $discount_goods_model->where($check_condition)
            ->withJoin([
                'discount' => function(Query $query) use ($goods_where, $goods_where_or) {
                    $query->where($goods_where)->where(function($query) use ($goods_where_or) {
                        $query->whereOr($goods_where_or);
                    });
                },
            ], 'inner')
            ->count();

        if ($count > 0) throw new AdminException('ACTIVE_GOODS_NOT_REPEAR');

        Db::startTrans();
        try {
            $status = DiscountDict::NOT_ACTIVE;

            $time = time();
            if (!empty($data[ 'start_time' ]) && $data[ 'start_time' ] <= $time) {
                $status = DiscountDict::ACTIVE;
            } elseif (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] <= $time) {
                $status = DiscountDict::END;
            }
            $data[ 'status' ] = $status;
            $discount_id = $this->model->create($data)->discount_id;

            $discount_goods = [];
            foreach ($goods_list as $k => $v) {
                foreach($v['sku_list'] as $item){
                    $discount_goods[] = [
                        'discount_id' => $discount_id,
                        'goods_id' => $item[ 'goods_id' ],
                        'sku_id' => $item[ 'sku_id' ],
                        'status' => $status,
                        'type' => $item[ 'discount_type' ],
                        'rate' => $item[ 'discount_rate' ],
                        'reduce_money' => $item[ 'reduce_money' ],
                        'discount_price' => $item[ 'discount_price' ] ?? $item[ 'reduce_money' ],
                        'is_enabled' => $item[ 'is_enabled' ] ?? 1,
                    ];
                }
            }

            (new DiscountGoods())->saveAll($discount_goods);

            if (!empty($data[ 'start_time' ]) && $data[ 'start_time' ] <= $time) {
                $this->discountStartAfter($discount_id);
            } elseif (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] <= $time) {
                $this->discountEndAfter($discount_id);
            }

            Db::commit();
            return $discount_id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 编辑限时折扣
     * @param int $discount_id
     * @param array $data
     * @return bool
     */
    public function edit(int $discount_id, array $data)
    {
        $goods_list = $data['goods_list'];
        $goods_ids = array_column($goods_list, 'goods_id');
        $data[ 'start_time' ] = strtotime($data[ 'start_time' ]);
        $data[ 'end_time' ] = strtotime($data[ 'end_time' ]);
        $data[ 'create_time' ] = time();
        $data[ 'update_time' ] = time();

        $this->checkGoods($goods_list);

        $check_condition = [
            [ 'discount_goods.goods_id', 'in', $goods_ids ],
            [ 'discount_goods.discount_id', '<>', $discount_id ],
        ];

        $goods_where = [
            [ 'discount.status', 'in', [ DiscountDict::NOT_ACTIVE, DiscountDict::ACTIVE ] ]
        ];
        $goods_where_or = [
            [ 'discount.start_time|discount.end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
            [ [ 'discount.start_time', '<=', $data[ 'start_time' ] ], [ 'discount.end_time', '>=', $data[ 'end_time' ] ] ],
            [ [ 'discount.start_time', '>=', $data[ 'start_time' ] ], [ 'discount.end_time', '<=', $data[ 'end_time' ] ] ],
        ];

        $discount_goods_model = new DiscountGoods();
        $count = $discount_goods_model->where($check_condition)
            ->withJoin([
                'discount' => function(Query $query) use ($goods_where, $goods_where_or) {
                    $query->where($goods_where)->where(function($query) use ($goods_where_or) {
                        $query->whereOr($goods_where_or);
                    });
                },
            ], 'inner')
            ->count();
        if ($count > 0) throw new AdminException('ACTIVE_GOODS_NOT_REPEAR');

        $this->model->where([ [ 'discount_id', '=', $discount_id ] ])->update([ 'status' => DiscountDict::NOT_ACTIVE ]);
        $this->discountEndAfter($discount_id);

        $status = DiscountDict::NOT_ACTIVE;
        $time = time();
        if (!empty($data[ 'start_time' ]) && $data[ 'start_time' ] <= $time) {
            $status = DiscountDict::ACTIVE;
        } elseif (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] <= $time) {
            $status = DiscountDict::END;
        }

        $active_goods_data = $discount_goods_model->where([ [ 'discount_id', '=', $discount_id]])->select()->toArray();
        $active_goods_ids = array_unique(array_column($active_goods_data,'goods_id'));
        $discount_goods = [];
        foreach ($goods_list as $k=>$v){
            foreach($v['sku_list'] as $item){
                $discount_goods[] = [
                    'discount_id' => $discount_id,
                    'goods_id' => $item[ 'goods_id' ],
                    'sku_id' => $item[ 'sku_id' ],
                    'status' => $status,
                    'type' => $item[ 'discount_type' ],
                    'rate' => $item[ 'discount_rate' ],
                    'reduce_money' => $item[ 'reduce_money' ],
                    'discount_price' => $item[ 'discount_price' ] ?? $item[ 'reduce_money' ],
                    'is_enabled' => $item[ 'is_enabled' ] ?? 1,
                ];
            }
        }
        $delete_goods_ids = array_diff($active_goods_ids, $goods_ids);
        unset($data['goods_list']);
        Db::startTrans();
        try {
            $data[ 'discount_id' ] = $discount_id;
            $data[ 'status' ] = $status;

            $this->model->where([ [ 'discount_id', '=', $discount_id]])->update($data);

            foreach ($discount_goods as $v){
                $discount_goods_info = $discount_goods_model->where([ [ 'discount_id', '=', $v['discount_id']], [ 'goods_id', '=', $v['goods_id']], [ 'sku_id', '=', $v['sku_id']]])->findOrEmpty();
                if($discount_goods_info->isEmpty()){
                    $discount_goods_model->create($v);
                }else{
                    $discount_goods_model->where([ [ 'discount_id', '=', $v['discount_id']], [ 'goods_id', '=', $v['goods_id']], [ 'sku_id', '=', $v['sku_id']]])->update($v);
                }
            }

            $discount_goods_model->where([ [ 'goods_id', 'in', $delete_goods_ids], [ 'discount_id', '=', $discount_id]])->delete();

            if (!empty($data[ 'start_time' ]) && $data[ 'start_time' ] <= $time) {
                $this->discountStartAfter($discount_id);
            } elseif (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] <= $time) {
                $this->discountEndAfter($discount_id);
            }

            Db::commit();
            return $discount_id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 限时折扣商品校验
     * @param $data
     * @return array
     */
    public function checkGoodsData($data)
    {
        $error = [
            'code' => 1,
            'data' => []
        ];
        $goods_ids = $data[ 'goods_ids' ];
        $discount_goods_model = new DiscountGoods();
        $select_goods_id = $discount_goods_model
            ->where([ [ 'discount_goods.discount_goods_id', '>', 0 ] ])
            ->withJoin([
                'discount' => function(Query $query) use ($data) {
                    $query->where([  [ 'discount.status', 'in', [ DiscountDict::NOT_ACTIVE, DiscountDict::ACTIVE ] ] ])->where(function($query) use ($data) {
                        $query->whereOr([ [ 'discount.start_time|discount.end_time', 'between', [ strtotime($data[ 'start_time' ]), strtotime($data[ 'end_time' ]) ] ],
                            [ [ 'discount.start_time', '<=', strtotime($data[ 'start_time' ]) ], [ 'discount.end_time', '>=', strtotime($data[ 'end_time' ]) ] ],
                            [ [ 'discount.start_time', '>=', strtotime($data[ 'start_time' ]) ], [ 'discount.end_time', '<=', strtotime($data[ 'end_time' ]) ] ] ]);
                    });
                },
            ], 'inner')
            ->column('goods_id');

        if (!empty($select_goods_id)) {
            if (!empty($data[ 'discount_id' ])) {
                $select_goods_ids = $discount_goods_model->where([
                    [ 'discount_id', '=', $data[ 'discount_id' ] ]
                ])->column('goods_id');
                $goods_ids = array_diff($goods_ids, $select_goods_ids);
            }
            $goods_id_intersect = array_intersect($goods_ids, $select_goods_id);
            if (!empty($goods_id_intersect)) {
                foreach ($goods_id_intersect as $goods_id) {
                    $discount_info = $discount_goods_model->where([
                        [ 'goods_id', '=', $goods_id ],
                        [ 'status', 'in', [ DiscountDict::NOT_ACTIVE, DiscountDict::ACTIVE ] ],
                    ])->with([ 'discount' => function($query) use ($data) {
                        $query->field('discount_id,name');
                    } ])->findOrEmpty()->toArray();
                    if (!empty($discount_info)) {
                        $error[ 'code' ] = -1;
                        $error[ 'data' ][] = [
                            'goods_id' => $goods_id,
                            'error_msg' => '该商品已参加【' . $discount_info[ 'discount' ][ 'name' ] . '】活动'
                        ];
                    }
                }
            }
        }
        return $error;
    }

    /**
     * 商品结构校验
     * @param $goods_list
     * @return void
     */
    public function checkGoods($goods_list)
    {
        if (empty($goods_list)) throw new AdminException('DISCOUNT_GOODS_NOT_EMPTY');
        foreach ($goods_list as $k => $v) {
            if (empty($v[ 'sku_list' ])) throw new AdminException('DISCOUNT_GOODS_SKU_NOT_EMPTY');
            foreach ($v[ 'sku_list' ] as $key => $value) {
                if (!isset($value[ 'is_enabled' ])) throw new AdminException('DISCOUNT_IS_ENABLED_NOT_EMPTY');

                if ($value[ 'is_enabled' ]) {
                    if (empty($value[ 'discount_type' ])) throw new AdminException('DISCOUNT_GOODS_DISCOUNT_TYPE_NOT_EMPTY');
                    if (!in_array($value[ 'discount_type' ], [ 'discount', 'reduce', 'specify' ])) throw new AdminException('DISCOUNT_GOODS_DISCOUNT_TYPE_ERROR');
                    if (empty($value[ 'discount_price' ]) && $value[ 'discount_price' ] != 0) throw new AdminException('DISCOUNT_GOODS_DISCOUNT_PRICE_NOT_EMPTY');
                    if (empty($value[ 'discount_rate' ]) && $value[ 'discount_price' ] != 0) throw new AdminException('DISCOUNT_GOODS_DISCOUNT_RATE_NOT_EMPTY');
                    if (empty($value[ 'reduce_money' ]) && $value[ 'discount_price' ] != 0) throw new AdminException('DISCOUNT_GOODS_REDUCE_MONEY_NOT_EMPTY');
                }
            }
        }
    }

    /**
     * 删除限时折扣
     * @param int $discount_id
     * @return bool
     */
    public function del(int $discount_id)
    {
        $info = $this->model->where([  ['discount_id', '=', $discount_id] ])->findOrEmpty();
        if ($info->isEmpty()) throw new AdminException('ACTIVE_NOT_FOUND');
        if ($info->status == DiscountDict::ACTIVE) throw new AdminException('ACTIVE_NOT_DELETE');
        $this->model->where([ [ 'discount_id', '=', $discount_id ] ])->delete();
        (new DiscountGoods())->where([ [ 'discount_id', '=', $discount_id ] ])->delete();
        return true;
    }

    /**
     * 批量删除限时折扣
     * @param array $ids
     * @return bool
     */
    public function batchDel($ids)
    {
        $discount_list = $this->model->where([  [ 'discount_id', 'in', $ids ] ])->select()->toArray();
        foreach ($discount_list as $value){
            if ($value[ 'status' ] == DiscountDict::ACTIVE) throw new AdminException('ACTIVE_NOT_DELETE');
        }

        Db::startTrans();
        try {
            $this->model->where([ [ 'discount_id', 'in', $ids ] ])->delete();
            (new DiscountGoods())->where([ [ 'discount_id', 'in', $ids ] ])->delete();

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 批量关闭限时折扣
     * @param array $ids
     * @return bool
     */
    public function batchClose($ids)
    {
        $discount_goods_model = new DiscountGoods();
        $shop_goods_model = new Goods();
        $shop_goods_sku_model = new GoodsSku();

        $discount_list = $this->model->where([ [ 'discount_id', 'in', $ids ] ])->select()->toArray();
        if (empty($discount_list)) throw new AdminException('ACTIVE_NOT_FOUND');

        $discount_goods_ids = $discount_goods_model->where([ [ 'discount_id', 'in', $ids ] ])->column('goods_id');

        Db::startTrans();
        try {
            $this->model->where([  ['discount_id', 'in', $ids], ['status', '=', DiscountDict::ACTIVE] ])->update([ 'status' => DiscountDict::CLOSE ]);
            $discount_goods_model->where([  ['discount_id', 'in', $ids]])->update([ 'status' => DiscountDict::CLOSE ]);
            $shop_goods_model->where([ [ 'goods_id', 'in', $discount_goods_ids ] ])->update([ 'is_discount' => 0 ]);
            $shop_goods_sku_model->where([ [ 'goods_id', 'in', $discount_goods_ids ] ])->update([ 'sale_price' => Db::raw('price') ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }



    }

    /**
     * 设置商品限时折扣
     * @param $discount_ids
     * @return void
     */
    public function setGoodsDiscount($discount_ids)
    {
        $list = $this->model->where([ [ 'discount_id', 'in', $discount_ids ],['status','=',DiscountDict::ACTIVE] ])->select()->toArray();
        if (empty($list)) return true;
        $discount_goods_model = new DiscountGoods();
        $shop_goods_model = new Goods();
        $shop_goods_sku_model = new GoodsSku();
        $discount_goods_list = $discount_goods_model->where([ [ 'discount_id', 'in', $discount_ids ] ])->field('goods_id, discount_id,sku_id,is_enabled,discount_price')->select()->toArray();
        $discount_goods_ids = array_unique(array_column($discount_goods_list, 'goods_id' ));
        $shop_goods_model->where([ [ 'goods_id', 'in', $discount_goods_ids ] ])->update([ 'is_discount' => 1]);
        foreach ($discount_goods_list as $k => $v) {
            if ($v[ 'is_enabled' ]) {
                $shop_goods_sku_model->where([ [ 'sku_id', '=', $v[ 'sku_id' ] ] ])->update([
                    'sale_price' => $v[ 'discount_price' ]
                ]);
            }
        }
        return true;
    }

    /**
     * 设置商品取消限时折扣
     * @param $discount_id
     * @return bool
     */
    public function cancelGoodsDiscount($discount_ids)
    {
        $list = $this->model->where([ [ 'discount_id', 'in', $discount_ids ],['status','=',DiscountDict::ACTIVE] ])->select()->toArray();
        if (empty($list)) return true;

        $discount_goods_model = new DiscountGoods();
        $shop_goods_model = new Goods();
        $shop_goods_sku_model = new GoodsSku();

        $discount_goods_list = $discount_goods_model->where([ [ 'discount_id', 'in', $discount_ids ] ])->field('goods_id, discount_id')->select()->toArray();
        $discount_goods_ids = array_column($discount_goods_list, 'goods_id');
        $shop_goods_model->where([ [ 'goods_id', 'in', $discount_goods_ids ] ])->update([ 'is_discount' => 0 ]);
        $shop_goods_sku_model->where([ [ 'goods_id', 'in', $discount_goods_ids ] ])->update([ 'sale_price' => Db::raw('price') ]);

        return true;
    }


    /**
     * 活动关闭
     * @return void
     */
    public function discountClose($discount_id)
    {
        $this->model->where([ ['discount_id', '=', $discount_id], ['status', '=', DiscountDict::ACTIVE] ])->update([ 'status' => DiscountDict::CLOSE ]);
        (new DiscountGoods())->where([ ['discount_id', '=', $discount_id]])->update([ 'status' => DiscountDict::CLOSE ]);
        $this->discountEndAfter($discount_id);
        return true;
    }

    /**
     * 活动开启
     * @return void
     */
    public function discountStartAfter($discount_ids)
    {
        if (!is_array($discount_ids)) {
            $discount_ids = [$discount_ids];
        }
        $this->setGoodsDiscount($discount_ids);
        return true;

    }

    /**
     * 活动结束
     * @return void
     */
    public function discountEndAfter($discount_ids)
    {
        if (!is_array($discount_ids)){
            $discount_ids = [$discount_ids];
        }
        $this->cancelGoodsDiscount($discount_ids);
        return true;
    }


    /**
     * 领取记录
     * @param int $id
     * @return void
     */
    public function order(int $discount_id, $where)
    {
        $order = 'create_time desc';
        $search_model = ( new Order() )
            ->where([ [ 'order.pay_time', '>', 0 ] ])
            ->withSearch([ 'search_type', 'order_from', 'join_status', 'create_time', 'pay_time' ], $where)
            ->with([
                'member' => function($query) {
                    $query->field('member_id, nickname, mobile, headimg');
                },
                'pay'
            ])
            ->withJoin([ 'shopOrderDiscount' => function($query) use ($discount_id) {
                $query->where([ [ 'discount_type', '=', ActiveDict::DISCOUNT ], [ 'discount_type_id', '=', $discount_id ] ]);
            } ], 'left')->order($order);

        $order_status_list = OrderDict::getStatus();
        $list = $this->pageQuery($search_model, function($item, $key) use ($order_status_list) {
            $item[ 'order_status_data' ] = $order_status_list[ $item[ 'status' ] ] ?? [];
            $item_pay = $item[ 'pay' ];
            if (!empty($item_pay)) {
                $item_pay->append([ 'type_name' ]);
            }
        });
        return $list;
    }


    /**
     * 会员
     * @param int $discount_id
     * @param $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function member(int $discount_id, $where)
    {
        $active_goods_ids = ( new DiscountGoods() )->where([ [ 'discount_id', '=', $discount_id ] ])->column('goods_id');
        $order = 'create_time desc';
        $search_model = ( new Order() )
            ->where([ [ 'order.pay_time', '>', 0 ] ])
            ->withJoin([ 'shopOrderDiscount' => function($query) use ($discount_id) {
                $query->where([ [ 'discount_type', '=', ActiveDict::DISCOUNT ], [ 'discount_type_id', '=', $discount_id ] ]);
            } ], 'left')
            ->with([ 'member' => function($query) {
                $query->field('username,member_id, nickname, mobile, headimg');
            }, 'orderGoods' ])
            ->order($order)
            ->field('order.member_id,COUNT(order.member_id) as member_count, group_concat(order.create_time) as create_time_data,group_concat(order.order_id) as order_ids')
            ->group('order.member_id');
        $list = $this->pageQuery($search_model, function($item, $key) use ($active_goods_ids) {
            $create_time_data = explode(',', $item[ 'create_time_data' ]);
            $item[ 'create_time' ] = date('Y-m-d H:i:s', end($create_time_data));

        });
        if (!empty($list[ 'data' ])) {
            $member_ids = array_column($list[ 'data' ], 'member_id');
            $data_list = ( new Order() )
                ->where([ [ 'order.member_id', 'in', $member_ids ], [ 'order.pay_time', '>', 0 ] ])
                ->withJoin([ 'shopOrderDiscount' => function($query) use ($discount_id) {
                    $query->where([ [ 'discount_type', '=', ActiveDict::DISCOUNT ], [ 'discount_type_id', '=', $discount_id ] ]);
                } ], 'left')
                ->with([ 'orderGoods' ])
                ->select()->toArray();

            foreach ($list[ 'data' ] as $key => $item) {
                $item[ 'active_order_money' ] = 0;
                foreach ($data_list as $k => $v) {
                    if ($item[ 'member_id' ] == $v[ 'member_id' ]) {
                        foreach ($v[ 'orderGoods' ] as $order_goods) {
                            if (in_array($order_goods[ 'goods_id' ], $active_goods_ids)) {
                                $item[ 'active_order_money' ] += $order_goods[ 'order_goods_money' ];
                            }
                        }
                    }

                }
                $list[ 'data' ][ $key ] = $item;
            }
        }

        return $list;
    }

    /**
     * 商品
     * @param int $discount_id
     * @param $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function goods(int $discount_id, $where)
    {
        $active_goods_model = new DiscountGoods();
        $search_model = $active_goods_model
            ->alias('discount_goods')
            ->where([
                [ 'discount_goods.discount_id', '=', $discount_id ],
                [ 'goods.goods_name', 'like', '%' . $where[ 'keyword' ] . '%' ],
            ])
            ->field('
                goods.goods_name,
                goods.goods_cover,
                discount_goods_id,
                discount_id,
                discount_goods.goods_id,
                discount_goods.sku_id,
                goods_sku.price,
                SUM(discount_goods.order_money) as order_money,
                SUM(discount_goods.order_num) as order_num,
                SUM(discount_goods.member_num) as member_num,
                SUM(discount_goods.success_num) as success_num
            ')
            ->leftJoin('shop_goods goods', 'discount_goods.goods_id = goods.goods_id')
            ->leftJoin('shop_goods_sku goods_sku', 'discount_goods.sku_id = goods_sku.sku_id')
            ->group('discount_goods.goods_id')
            ->append([ 'goods_cover_thumb_small' ]);
        return $this->pageQuery($search_model);
    }

    /**
     * 配置设置
     * @param array $params
     * @return array
     */
    public function setConfig($key, $data)
    {
        ( new CoreConfigService() )->setConfig($key, $data);
        return true;
    }

    /**
     * 折扣页面轮播图配置
     */
    public function getDiscountBannerConfig()
    {
        $data = ( new CoreConfigService() )->getConfigValue('SHOP_DISCOUNT_BANNER_CONFIG');
        if (empty($data)) {
            $data = [];
        }
        return $data;
    }

    /**
     * 订单支付后累计
     * @param $order
     * @return bool
     */
    public function orderPayAfter($order)
    {
        $order_discount_model = new OrderDiscounts();
        $order_discount = $order_discount_model->field('order_id, order_goods_ids, num, money, discount_type, discount_type_id, member_id, goods_id, sku_id')->where([ [ 'order_id', '=', $order[ 'order_id' ] ], [ 'discount_type', '=', ActiveDict::DISCOUNT ] ])->field('order_id,discount_type_id')->select()->toArray();
        if (empty($order_discount)) return true;

        $discount_model = (new Discount());
        $discount_goods_model = (new DiscountGoods());

        Db::startTrans();
        try {
            foreach ($order_discount as $value){
                $member_goods_count = $order_discount_model->where([
                    [ 'discount_type', '=', ActiveDict::DISCOUNT ],
                    [ 'discount_type_id', '=', $value[ 'discount_type_id' ] ],
                    [ 'order_id', '<>', $value[ 'order_id' ] ],
                    [ 'member_id', '=', $value[ 'member_id' ] ],
                ])->count();

                $discount_goods_query = $discount_goods_model->where([
                    [ 'discount_id', '=', $value[ 'discount_type_id' ] ],
                    [ 'goods_id', '=', $value[ 'goods_id' ] ],
                    [ 'sku_id', '=', $value[ 'sku_id' ] ],
                ])->inc('order_money',$value[ 'money' ])->inc('order_num')->inc('success_num');

                $discount_query = $discount_model->where([
                    [ 'discount_id', '=', $value[ 'discount_type_id' ] ],
                ])->inc('order_money',$value[ 'money' ])->inc('order_num')->inc('success_num');

                if (empty($member_goods_count)){
                    $discount_goods_query->inc('member_num');
                    $discount_query->inc('member_num');
                }

                $discount_goods_query->update();
                $discount_query->update();
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }

    }

    /**
     * 数字格式转化转换
     * @param $num
     * @return string
     */
    public function formatNumber($num) {
        return ($num == intval($num)) ? intval($num) : round($num, 1);
    }

}
