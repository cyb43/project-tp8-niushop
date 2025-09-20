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
use addon\shop\app\dict\active\ManjianDict;
use addon\shop\app\model\coupon\Coupon;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\manjian\Manjian;
use addon\shop\app\model\manjian\ManjianGiveRecords;
use addon\shop\app\model\manjian\ManjianGoods;
use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use addon\shop\app\service\core\marketing\CoreManjianService;
use app\model\member\Member;
use core\base\BaseAdminService;
use core\exception\AdminException;
use think\db\Query;
use think\facade\Db;

/**
 * 满减送服务层
 * Class ManjianService
 * @package addon\shop\app\service\admin\marketing
 */
class ManjianService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Manjian();
    }

    /**
     * 获取满减送列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        //活动名称、活动类型、活动详情、活动状态、支付订单数、参与会员数、支付金额 、活动时间
        $field = 'manjian_id,manjian_name,condition_type,rule_json,status,total_order_num,total_member_num,total_order_money,start_time,end_time';
        $order = 'create_time desc';
        $search_model = $this->model
            ->where([ [ 'manjian_id', '>', 0 ] ])
            ->withSearch([ "manjian_name", "status", "create_time","active_id" ], $where)
            ->append([ 'status_name' ])
            ->field($field)
            ->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 关闭满减送
     * @param $manjian_id
     */
    public function closeManjian($manjian_id)
    {
        $manjian_info = $this->model->where([ [ 'manjian_id', '=', $manjian_id ] ])->field('status,end_time')->findOrEmpty()->toArray();
        if (!empty($manjian_info)) {
            if ($manjian_info[ 'end_time' ] >= time() && $manjian_info[ 'status' ] == ManjianDict::ACTIVE) {
                $this->model->where([ [ 'manjian_id', '=', $manjian_id ], [ 'status', '=', ManjianDict::ACTIVE ] ])->update([ 'status' => ManjianDict::CLOSE ]);
                ( new ManjianGoods() )->where([ [ 'manjian_id', '=', $manjian_id ] ])->update([ 'status' => ManjianDict::CLOSE ]);
            } else {
                throw new AdminException('MANJIANSONG_CLOSED');
            }
        } else {
            throw new AdminException('MANJIANSONG_NOT_FOUND');
        }
    }

    /**
     * 删除满减送活动
     * @param $manjian_id
     */
    public function del($manjian_id)
    {
        $manjian_info = $this->model->where([ [ 'manjian_id', '=', $manjian_id ] ])->field('status,end_time')->findOrEmpty()->toArray();
        if (!empty($manjian_info)) {
            if ($manjian_info[ 'status' ] == ActiveDict::ACTIVE) throw new AdminException('ACTIVE_NOT_DELETE');
            $this->model->where([ [ 'manjian_id', '=', $manjian_id ] ])->delete();
            ( new ManjianGoods() )->where([ [ 'manjian_id', '=', $manjian_id ] ])->delete();
        } else {
            throw new AdminException('MANJIANSONG_NOT_FOUND');
        }
    }

    /**
     * 满减送活动详情
     * @param $manjian_id
     */
    public function info($manjian_id)
    {
        //条件类型、活动名称、活动开始时间、活动结束时间、活动状态、活动创建时间、参与订单数、参与会员数、支付金额、活动详情(例：满100.00元减5.00元、包邮、送1积分...)
        $info = $this->model
            ->field('manjian_id,manjian_name,goods_type,start_time,end_time,status,create_time,total_order_num,total_member_num,total_order_money,rule_json')
            ->where([ [ 'manjian_id', '=', $manjian_id ] ])
            ->append([ 'status_name', 'goods_type_name' ])
            ->findOrEmpty()
            ->toArray();
        return $info;
    }

    /**
     * 会员
     * @param int $manjian_id
     * @param $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function member(int $manjian_id, $where)
    {
        $manjian_records = ( new ManjianGiveRecords() )->field('member_id,order_id')->withJoin([ 'order' => function($query) {
            $query->field('order.create_time,order_money as total_order_money');
        } ])->where([ [ 'manjian_id', '=', $manjian_id ] ])
            ->select()->toArray();
        $member_ids = array_unique(array_column($manjian_records, 'member_id'));
        $record_info = [];
        foreach ($manjian_records as $key => $value) {
            unset($value[ 'order' ]);
            $record_info[ $value[ 'member_id' ] ][] = $value;
        }

        $search_model = ( new Member() )->field('username,member_id, nickname, mobile, headimg')->where([ [ 'member_id', 'in', $member_ids ] ]);
        $list = $this->pageQuery($search_model);
        if (!empty($list[ 'data' ])) {
            foreach ($list[ 'data' ] as &$value) {
                $value[ 'total_order_money' ] = 0;
                $value[ 'total_num' ] = 0;
                $value[ 'finally_order_time' ] = 0;
                if (isset($record_info[ $value[ 'member_id' ] ])) {
                    $member_records = $record_info[ $value[ 'member_id' ] ];
                    $total_order_money = 0;
                    foreach (array_column($member_records, 'total_order_money') as $order_money) {
                        $total_order_money = bcadd($total_order_money, $order_money, 2);
                    }
                    $value[ 'total_order_money' ] = $total_order_money;
                    $value[ 'total_num' ] = count($member_records);
                    foreach ($member_records as $v) {
                        if ($v[ 'create_time' ] > $value[ 'finally_order_time' ]) {
                            $value[ 'finally_order_time' ] = $v[ 'create_time' ];
                        }
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 添加满减送
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data[ 'start_time' ] = strtotime($data[ 'start_time' ]);
        $data[ 'end_time' ] = strtotime($data[ 'end_time' ]);
        if (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] < time()) throw new AdminException('END_TIME_NOT_LESS_CURRENT_TIME');

        $goods_ids = [];
        if ($data[ 'goods_type' ] != ManjianDict::ALL_GOODS) {
            if (isset($data[ 'goods_data' ]) && !empty($data[ 'goods_data' ])) {
                $goods_ids = array_unique(array_column($data[ 'goods_data' ], 'goods_id'));
            }
        }
        //验证商品是否参加活动
        //有正在进行的全部商品活动
        $all_goods_count = $this->model
            ->where([
                [ 'goods_type', '=', ManjianDict::ALL_GOODS ],
                [ 'status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ],
            ])->where(function($query) use ($data) {
                $query->whereOr([
                    [ 'start_time|end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
                    [ [ 'start_time', '<=', $data[ 'start_time' ] ], [ 'end_time', '>=', $data[ 'end_time' ] ] ],
                    [ [ 'start_time', '>=', $data[ 'start_time' ] ], [ 'end_time', '<=', $data[ 'end_time' ] ] ]
                ]);
            })->count();
        if ($all_goods_count > 0) throw new AdminException('MANJIANSONG_ALL_GOODS_EXIT');

        $manjian_goods_model = new ManjianGoods();
        $select_goods_id = $manjian_goods_model
            ->where([ [ 'manjian_goods.manjian_goods_id', '>', 0 ] ])
            ->withJoin([
                'manjian' => function(Query $query) use ($data) {
                    $query->where([ [ 'manjian.status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ], [ 'manjian.goods_type', '=', ManjianDict::SELECTED_GOODS ] ])->where(function($query) use ($data) {
                        $query->whereOr([ [ 'manjian.start_time|manjian.end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '<=', $data[ 'start_time' ] ], [ 'manjian.end_time', '>=', $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '>=', $data[ 'start_time' ] ], [ 'manjian.end_time', '<=', $data[ 'end_time' ] ] ] ]);
                    });
                },
            ], 'inner')
            ->column('goods_id');

        $select_goods_not_id = $manjian_goods_model
            ->where([ [ 'manjian_goods.manjian_goods_id', '>', 0 ] ])
            ->withJoin([
                'manjian' => function(Query $query) use ($data) {
                    $query->where([ [ 'manjian.status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ], [ 'manjian.goods_type', '=', ManjianDict::SELECTED_GOODS_NOT ] ])->where(function($query) use ($data) {
                        $query->whereOr([ [ 'manjian.start_time|manjian.end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '<=', $data[ 'start_time' ] ], [ 'manjian.end_time', '>=', $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '>=', $data[ 'start_time' ] ], [ 'manjian.end_time', '<=', $data[ 'end_time' ] ] ] ]);
                    });
                },
            ], 'inner')
            ->column('goods_id');

        $error = [
            'code' => 1,
            'data' => []
        ];

        switch ($data[ 'goods_type' ]) {
            case ManjianDict::ALL_GOODS:
                if (!empty($select_goods_id) || !empty($select_goods_not_id)) {
                    throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                }
                break;
            case ManjianDict::SELECTED_GOODS_NOT:
                if (!empty($select_goods_id)) {
                    $goods_id_diff = array_diff($select_goods_id, $goods_ids);
                    if (!empty($goods_id_diff)) {
                        throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                    }
                }
                if (!empty($select_goods_not_id)) {
                    throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                }
                break;
            case ManjianDict::SELECTED_GOODS:
                if (!empty($select_goods_id)) {
                    $goods_id_intersect = array_intersect($goods_ids, $select_goods_id);
                    if (!empty($goods_id_intersect)) {
                        throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                    }
                }
                if (!empty($select_goods_not_id)) {
                    $goods_id_diff = array_diff($goods_ids, $select_goods_not_id);
                    if (!empty($goods_id_diff)) {
                        throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                    }
                }
                break;
        }

        Db::startTrans();
        try {
            // 满减送活动数据
            $manjian_value[ 'manjian_name' ] = $data[ 'manjian_name' ];
            $manjian_value[ 'condition_type' ] = $data[ 'condition_type' ];
            $manjian_value[ 'goods_type' ] = $data[ 'goods_type' ];
            $manjian_value[ 'join_member_type' ] = $data[ 'join_member_type' ];
            $manjian_value[ 'rule_type' ] = $data[ 'rule_type' ] ?? '';
            $manjian_value[ 'rule_json' ] = $data[ 'rule_json' ];
            $manjian_value[ 'goods_ids' ] = array_map(function($item) { return (string) $item; }, $goods_ids);
            $manjian_value[ 'level_ids' ] = array_map(function($item) { return (string) $item; }, $data[ 'level_ids' ]);
            $manjian_value[ 'label_ids' ] = array_map(function($item) { return (string) $item; }, $data[ 'label_ids' ]);
            $manjian_value[ 'status' ] = ManjianDict::NOT_ACTIVE;
            $manjian_value[ 'start_time' ] = $data[ 'start_time' ];
            $manjian_value[ 'end_time' ] = $data[ 'end_time' ];
            $manjian_value[ 'remark' ] = $data[ 'remark' ];

            if (!empty($data[ 'start_time' ]) && $data[ 'start_time' ] <= time()) {
                $manjian_value[ 'status' ] = ManjianDict::ACTIVE;
            } elseif (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] <= time()) {
                $manjian_value[ 'status' ] = ManjianDict::END;
            }
            $res = $this->model->create($manjian_value);

            $manjian_goods = [];
            if ($data[ 'goods_type' ] != ManjianDict::ALL_GOODS) {
                if (!empty($data[ 'goods_data' ])) {
                    foreach ($data[ 'goods_data' ] as $v) {
                        $manjian_goods[] = [
                            'manjian_id' => $res->manjian_id,
                            'goods_id' => $v[ 'goods_id' ],
                            'sku_id' => $v[ 'sku_id' ],
                            'goods_type' => $data[ 'goods_type' ],
                            'status' => $manjian_value[ 'status' ],
                        ];
                    }
                }
            }
            ( new Manjiangoods() )->saveAll($manjian_goods);

            Db::commit();
            $error[ 'data' ] = [ 'id' => $res->manjian_id ];
            return $error;
        } catch (\Exception $e) {
            Db::rollback();
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 获取满减送编辑数据
     * @param array $params
     * @return array
     */
    public function getInit($params = [])
    {
        $res = $active_goods = [];
        if (!empty($params[ 'manjian_id' ])) {
            //获取满减基本信息
            $manjisn_info = $this->model
                ->field('manjian_id,manjian_name,condition_type,goods_type,join_member_type,rule_type,rule_json,start_time,end_time,remark,level_ids,label_ids,total_order_money,total_member_num,total_order_num,status,create_time,total_point,total_balance,total_coupon_num,total_goods_num')
                ->where([ [ 'manjian_id', '=', $params[ 'manjian_id' ] ] ])
                ->append([ 'status_name' ])
                ->findOrEmpty()->toArray();

            $coupon_model = ( new Coupon() );
            $goods_model = ( new Goods() );
            if (!empty($manjisn_info[ 'rule_json' ])) {
                foreach ($manjisn_info[ 'rule_json' ] as &$value) {
                    if (isset($value[ 'coupon' ]) && !empty($value[ 'coupon' ])) {
                        $coupon_ids = array_column($value[ 'coupon' ], 'coupon_id');
                        $coupon_info = $coupon_model->field('id,title,type,price')->where([ [ 'id', 'in', $coupon_ids ] ])->append([ 'type_name' ])->select()->toArray();
                        $coupon_info = array_column($coupon_info, null, 'id');
                        foreach ($value[ 'coupon' ] as &$item) {
                            if (isset($coupon_info[ $item[ 'coupon_id' ] ])) {
                                $item[ 'type_name' ] = $coupon_info[ $item[ 'coupon_id' ] ][ 'type_name' ] ?? '';
                                $item[ 'title' ] = $coupon_info[ $item[ 'coupon_id' ] ][ 'title' ] ?? '';
                                $item[ 'price' ] = $coupon_info[ $item[ 'coupon_id' ] ][ 'price' ] ?? 0;
                            }
                        }
                    }
                    if (isset($value[ 'goods' ]) && !empty($value[ 'goods' ])) {
                        foreach ($value[ 'goods' ] as &$goods_item) {
                            $sku_id = $goods_item[ 'sku_id' ];
                            $goods_info = $goods_model->field('goods_id,goods_cover,goods_name')->where([ [ 'goods.goods_id', '=', $goods_item[ 'goods_id' ] ] ])
                                ->withJoin([ 'goodsSku' => function($query) use ($sku_id) {
                                    $query->field('sku_id,sku_name,sku_image,goodsSku.stock,goodsSku.price')->where([ [ 'sku_id', '=', $sku_id ] ]);
                                } ])->append([ 'goods_cover_thumb_small' ])->findOrEmpty()->toArray();
                            $goods_info[ 'num' ] = $goods_item[ 'num' ];
                            $goods_item = $goods_info;
                        }
                    }
                }
            }

            if (isset($manjisn_info[ 'label_ids' ]) && !empty($manjisn_info[ 'label_ids' ])) {
                $manjisn_info[ 'label_ids' ] = array_map('intval', $manjisn_info[ 'label_ids' ]);
            }
            if (isset($manjisn_info[ 'level_ids' ]) && !empty($manjisn_info[ 'level_ids' ])) {
                $manjisn_info[ 'level_ids' ] = array_map('intval', $manjisn_info[ 'level_ids' ]);
            }

            $res[ 'manjian_info' ] = $manjisn_info;

            $active_goods_info = [];
            //获取满减商品信息
            if (!empty($manjisn_info[ 'goods_type' ]) && $manjisn_info[ 'goods_type' ] != ManjianDict::ALL_GOODS) {
                $active_goods = ( new ManjianGoods() )->field('goods_id,sku_id')->where([ [ 'manjian_id', '=', $params[ 'manjian_id' ] ] ])->select()->toArray();
                foreach ($active_goods as $v) {
                    $goods_sku_id = $v[ 'sku_id' ];
                    $goods_info = $goods_model->field('goods_id,goods_cover,goods_name,status')->where([ [ 'goods.goods_id', '=', $v[ 'goods_id' ] ] ])
                        ->withJoin([ 'goodsSku' => function($query) use ($goods_sku_id) {
                            $query->field('sku_id,sku_name,sku_image,goodsSku.stock,goodsSku.price')->where([ [ 'sku_id', '=', $goods_sku_id ] ]);
                        } ])->append([ 'goods_cover_thumb_small' ])->findOrEmpty()->toArray();
                    if (!empty($goods_info)) {
                        $active_goods_info[] = $goods_info;
                    }
                }
            }
            $res[ 'manjian_goods' ] = $active_goods_info;
        }
        return $res;
    }

    /**
     * 编辑满减送
     * @param array $data
     * @return mixed
     */
    public function edit(int $manjian_id, array $data)
    {
        $data[ 'start_time' ] = strtotime($data[ 'start_time' ]);
        $data[ 'end_time' ] = strtotime($data[ 'end_time' ]);
        if (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] < time()) throw new AdminException('END_TIME_NOT_LESS_CURRENT_TIME');

        $goods_ids = [];
        if ($data[ 'goods_type' ] != ManjianDict::ALL_GOODS) {
            if (isset($data[ 'goods_data' ]) && !empty($data[ 'goods_data' ])) {
                $goods_ids = array_unique(array_column($data[ 'goods_data' ], 'goods_id'));
            }
        }

        //有正在进行的全部商品活动
        $all_goods_count = $this->model
            ->where([
                [ 'manjian_id', '<>', $manjian_id ],
                [ 'goods_type', '=', ManjianDict::ALL_GOODS ],
                [ 'status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ],
            ])->where(function($query) use ($data) {
                $query->whereOr([
                    [ 'start_time|end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
                    [ [ 'start_time', '<=', $data[ 'start_time' ] ], [ 'end_time', '>=', $data[ 'end_time' ] ] ],
                    [ [ 'start_time', '>=', $data[ 'start_time' ] ], [ 'end_time', '<=', $data[ 'end_time' ] ] ]
                ]);
            })->count();
        if ($all_goods_count > 0) throw new AdminException('MANJIANSONG_ALL_GOODS_EXIT');

        $manjian_goods_model = new ManjianGoods();
        $select_goods_id = $manjian_goods_model
            ->where([ [ 'manjian_goods.manjian_goods_id', '>', 0 ] ])
            ->withJoin([
                'manjian' => function(Query $query) use ($data, $manjian_id) {
                    $query->where([ [ 'manjian.manjian_id', '<>', $manjian_id ], [ 'manjian.status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ], [ 'manjian.goods_type', '=', ManjianDict::SELECTED_GOODS ] ])->where(function($query) use ($data) {
                        $query->whereOr([ [ 'manjian.start_time|manjian.end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '<=', $data[ 'start_time' ] ], [ 'manjian.end_time', '>=', $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '>=', $data[ 'start_time' ] ], [ 'manjian.end_time', '<=', $data[ 'end_time' ] ] ] ]);
                    });
                },
            ], 'inner')
            ->column('goods_id');

        $select_goods_not_id = $manjian_goods_model
            ->where([ [ 'manjian_goods.manjian_goods_id', '>', 0 ] ])
            ->withJoin([
                'manjian' => function(Query $query) use ($data, $manjian_id) {
                    $query->where([ [ 'manjian.manjian_id', '<>', $manjian_id ], [ 'manjian.status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ], [ 'manjian.goods_type', '=', ManjianDict::SELECTED_GOODS_NOT ] ])->where(function($query) use ($data) {
                        $query->whereOr([ [ 'manjian.start_time|manjian.end_time', 'between', [ $data[ 'start_time' ], $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '<=', $data[ 'start_time' ] ], [ 'manjian.end_time', '>=', $data[ 'end_time' ] ] ],
                            [ [ 'manjian.start_time', '>=', $data[ 'start_time' ] ], [ 'manjian.end_time', '<=', $data[ 'end_time' ] ] ] ]);
                    });
                },
            ], 'inner')
            ->column('goods_id');

        $error = [
            'code' => 1,
            'data' => []
        ];

        switch ($data[ 'goods_type' ]) {
            case ManjianDict::ALL_GOODS:
                if (!empty($select_goods_id) || !empty($select_goods_not_id)) {
                    throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                }
                break;
            case ManjianDict::SELECTED_GOODS_NOT:
                if (!empty($select_goods_id)) {
                    $goods_id_diff = array_diff($select_goods_id, $goods_ids);
                    if (!empty($goods_id_diff)) {
                        throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                    }
                }
                if (!empty($select_goods_not_id)) {
                    throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                }
                break;
            case ManjianDict::SELECTED_GOODS:
                if (!empty($select_goods_id)) {
                    $goods_id_intersect = array_intersect($goods_ids, $select_goods_id);
                    if (!empty($goods_id_intersect)) {
                        throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                    }
                }
                if (!empty($select_goods_not_id)) {
                    $goods_id_diff = array_diff($goods_ids, $select_goods_not_id);
                    if (!empty($goods_id_diff)) {
                        throw new AdminException('MANJIANSONG_GOODS_NOT_REPEAR');
                    }
                }
                break;
        }

        Db::startTrans();
        try {
            // 满减送活动数据
            $manjian_value[ 'manjian_name' ] = $data[ 'manjian_name' ];
            $manjian_value[ 'condition_type' ] = $data[ 'condition_type' ];
            $manjian_value[ 'goods_type' ] = $data[ 'goods_type' ];
            $manjian_value[ 'join_member_type' ] = $data[ 'join_member_type' ];
            $manjian_value[ 'rule_type' ] = $data[ 'rule_type' ] ?? '';
            $manjian_value[ 'rule_json' ] = $data[ 'rule_json' ];
            $manjian_value[ 'goods_ids' ] = array_map(function($item) { return (string) $item; }, $goods_ids);
            $manjian_value[ 'level_ids' ] = array_map(function($item) { return (string) $item; }, $data[ 'level_ids' ]);
            $manjian_value[ 'label_ids' ] = array_map(function($item) { return (string) $item; }, $data[ 'label_ids' ]);
            $manjian_value[ 'status' ] = ManjianDict::NOT_ACTIVE;
            $manjian_value[ 'start_time' ] = $data[ 'start_time' ];
            $manjian_value[ 'end_time' ] = $data[ 'end_time' ];
            $manjian_value[ 'remark' ] = $data[ 'remark' ];

            if (!empty($data[ 'start_time' ]) && $data[ 'start_time' ] <= time()) {
                $manjian_value[ 'status' ] = ManjianDict::ACTIVE;
            } elseif (!empty($data[ 'end_time' ]) && $data[ 'end_time' ] <= time()) {
                $manjian_value[ 'status' ] = ManjianDict::END;
            }
            $this->model->where([ [ 'manjian_id', '=', $manjian_id ] ])->update($manjian_value);

            $manjian_goods = [];
            if ($data[ 'goods_type' ] != ManjianDict::ALL_GOODS) {
                if (!empty($data[ 'goods_data' ])) {
                    foreach ($data[ 'goods_data' ] as $v) {
                        $manjian_goods[] = [
                            'manjian_id' => $manjian_id,
                            'goods_id' => $v[ 'goods_id' ],
                            'sku_id' => $v[ 'sku_id' ],
                            'goods_type' => $data[ 'goods_type' ],
                            'status' => $manjian_value[ 'status' ],
                        ];
                    }
                }
            }
            ( new Manjiangoods() )->where([ [ 'manjian_id', '=', $manjian_id ] ])->delete();
            ( new Manjiangoods() )->saveAll($manjian_goods);
            Db::commit();
            $error[ 'data' ] = [ 'id' => $manjian_id ];
            return $error;
        } catch (\Exception $e) {
            Db::rollback();
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 满减送商品校验
     * @param $data
     * @return array
     */
    public function checkGoods($data)
    {
        $error = [
            'code' => 1,
            'data' => []
        ];
        $goods_ids = $data[ 'goods_ids' ];
        $manjian_goods_model = new ManjianGoods();
        $select_goods_id = $manjian_goods_model
            ->where([ [ 'manjian_goods.manjian_goods_id', '>', 0 ] ])
            ->withJoin([
                'manjian' => function(Query $query) use ($data) {
                    $query->where([ [ 'manjian.status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ], [ 'manjian.goods_type', '=', ManjianDict::SELECTED_GOODS ] ])->where(function($query) use ($data) {
                        $query->whereOr([ [ 'manjian.start_time|manjian.end_time', 'between', [ strtotime($data[ 'start_time' ]), strtotime($data[ 'end_time' ]) ] ],
                            [ [ 'manjian.start_time', '<=', strtotime($data[ 'start_time' ]) ], [ 'manjian.end_time', '>=', strtotime($data[ 'end_time' ]) ] ],
                            [ [ 'manjian.start_time', '>=', strtotime($data[ 'start_time' ]) ], [ 'manjian.end_time', '<=', strtotime($data[ 'end_time' ]) ] ] ]);
                    });
                },
            ], 'inner')
            ->column('goods_id');

        if (!empty($select_goods_id) && $data[ 'goods_type' ] != ManjianDict::ALL_GOODS) {
            if (!empty($data[ 'manjian_id' ])) {
                $select_goods_ids = $manjian_goods_model->where([
                    [ 'manjian_id', '=', $data[ 'manjian_id' ] ]
                ])->column('goods_id');
                $goods_ids = array_diff($goods_ids, $select_goods_ids);
            }
            $goods_id_intersect = array_intersect($goods_ids, $select_goods_id);
            if (!empty($goods_id_intersect)) {
                foreach ($goods_id_intersect as $goods_id) {
                    $manjian_info = $manjian_goods_model->where([
                        [ 'goods_id', '=', $goods_id ],
                        [ 'status', 'in', [ ManjianDict::NOT_ACTIVE, ManjianDict::ACTIVE ] ],
                        [ 'goods_type', '=', $data[ 'goods_type' ] ]
                    ])->with([ 'manjian' => function($query) use ($data) {
                        $query->field('manjian_id,manjian_name');
                    } ])->findOrEmpty()->toArray();
                    if (!empty($manjian_info)) {
                        $error[ 'code' ] = -1;
                        $error[ 'data' ][] = [
                            'goods_id' => $goods_id,
                            'error_msg' => '该商品已参加【' . $manjian_info[ 'manjian' ][ 'manjian_name' ] . '】活动'
                        ];
                    }
                }
            }
        }
        return $error;
    }

    /**
     * 批量关闭
     * @param $data
     * @return bool
     */
    public function batchClose($data)
    {
        if (empty($data[ 'manjian_id' ])) throw new AdminException('MANJIANSONG_NOT_FOUND');

        $save_data = [];
        foreach ($data[ 'manjian_id' ] as $key => $value) {
            $save_data[ $key ][ 'manjian_id' ] = $value;
            $save_data[ $key ][ 'status' ] = ManjianDict::CLOSE;
        }
        $this->model->saveAll($save_data);
        return true;
    }

    /**
     * 批量删除
     * @param $data
     * @return bool
     */
    public function batchDelete($data)
    {
        if (empty($data[ 'manjian_id' ])) throw new AdminException('MANJIANSONG_NOT_FOUND');
        $this->model->where([ [ 'manjian_id', 'in', $data[ 'manjian_id' ] ] ])->delete();
        return true;
    }

    /**
     * 获取满减信息
     * @return array
     */
    public function getManjianInfo($data)
    {
        return ( new CoreManjianService() )->getManjianInfo($data);
    }

}
