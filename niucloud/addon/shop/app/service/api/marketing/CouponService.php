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

namespace addon\shop\app\service\api\marketing;


use addon\shop\app\dict\coupon\CouponDict;
use addon\shop\app\dict\coupon\CouponMemberDict;
use addon\shop\app\model\coupon\Coupon;
use addon\shop\app\model\coupon\CouponGoods;
use addon\shop\app\model\coupon\CouponMember;
use app\model\member\Member;
use app\service\core\sys\CoreSysConfigService;
use core\base\BaseApiService;
use core\exception\CommonException;
use think\facade\Db;

/**
 *  优惠券服务层
 */
class CouponService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Coupon();
    }

    /**
     * 获取优惠券列表
     * @return array
     */
    public function getPage($data)
    {
        $goods_coupon_id = [];
        if ($data[ 'goods_id' ] != '') {
            $coupon_goods_model = new CouponGoods();
            $goods_coupon_list = $coupon_goods_model->where([ [ 'goods_id', '=', $data[ 'goods_id' ] ] ])->select()->toArray();
            if (!empty($goods_coupon_list)) {
                $goods_coupon_id = array_column($goods_coupon_list, 'coupon_id');
            } else {
                $goods_coupon_id = [ -1 ];
            }
        }

        $category_coupon_id = [];
        if (!empty($data[ 'category_id' ])) {
            $coupon_goods_model = new CouponGoods();
            $category_coupon_list = $coupon_goods_model->where([ [ 'category_id', 'in', $data[ 'category_id' ] ] ])->select()->toArray();
            if (!empty($category_coupon_list)) {
                $category_coupon_id = array_column($category_coupon_list, 'coupon_id');
            } else {
                $category_coupon_id = [ -1 ];
            }
        }
        $field = 'id,title,start_time,end_time,remain_count,receive_count,limit_count,status,price,min_condition_money,type
        ,receive_type,valid_type,length,valid_start_time,valid_end_time,sort';

        // 参数过滤
        if (!empty($data[ 'order' ]) && in_array($data[ 'order' ], [ 'create_time', 'price' ])) {
            $order = $data[ 'order' ] . ' ' . $data[ 'sort' ];
        } else {
            $order = 'id desc';
        }

        $where[] = [ 'status', '=', CouponDict::NORMAL ];
        $where[] = [ 'receive_type', '=', CouponDict::USER ];

        $time_where = function($query) {
            $nowtime = time();
            $time_where = [
                [
                    [ 'start_time', '<=', $nowtime ],
                    [ 'end_time', '>=', $nowtime ],
                ],
                [
                    [ 'start_time', '=', 0 ],
                    [ 'end_time', '=', 0 ],
                ]
            ];
            $query->whereOr($time_where);
        };
        if (!empty($category_coupon_id) || !empty($goods_coupon_id)) {
            $category_where = [];
            if (!empty($category_coupon_id)) {
//                $category_where = function ($query) use($category_coupon_id, $time_where){
//                    $query->where([
//                        ['id', 'in', $category_coupon_id],
//                        ['type', '=', CouponDict::CATEGORY]
//                    ]);
//                };
                $category_where = [
                    [ 'id', 'in', $category_coupon_id ],
                    [ 'type', '=', CouponDict::CATEGORY ]
                ];
            }
            $goods_where = [];
            if (!empty($goods_coupon_id)) {
                $goods_where = [
                    [ 'id', 'in', $goods_coupon_id ],
                    [ 'type', '=', CouponDict::GOODS ]
                ];
            }
            $all_where = [
                [ 'type', '=', CouponDict::ALL ]
            ];

            $common_where = function($query) use ($category_where, $goods_where, $all_where) {
                $temp_where = [ $all_where ];
                if ($category_where) {
                    $temp_where[] = $category_where;
                }
                if ($goods_where) {
                    $temp_where[] = $goods_where;
                }
                $query->whereOr($temp_where);
            };

            $search_model = $this->model
                ->field($field)
                ->where($where)
                ->where($common_where)
                ->where($time_where)
                ->order($order)
                ->append([ 'coupon_price', 'coupon_min_price', 'receive_type_name', 'type_name' ]);
        } else {
            $type_where = [];
            if (!empty($data[ 'type' ])) {
                $type_where = [
                    [ 'type', '=', $data[ 'type' ] ]
                ];
            }

            $search_model = $this->model
                ->field($field)
                ->where($where)
                ->where($type_where)
                ->where($time_where)
                ->order($order)
                ->append([ 'coupon_price', 'coupon_min_price', 'receive_type_name', 'type_name' ]);
        }
        $list = $this->pageQuery($search_model);
        $coupon_member = new CouponMember();
        $member_info = ( new Member() )->where([ [ 'member_id', '=', $this->member_id ] ])->field('member_id')->findOrEmpty()->toArray();
        foreach ($list[ 'data' ] as $k => &$v) {
            if ($v[ 'remain_count' ] != '-1') {
                $v[ 'sum_count' ] = $v[ 'remain_count' ] + $v[ 'receive_count' ];
            } else {
                $v[ 'sum_count' ] = '-1';
            }

            if ($member_info) {
                $coupon_member_count = $coupon_member->where([ [ 'member_id', '=', $this->member_id ], [ 'coupon_id', '=', $v[ 'id' ] ], [ 'receive_type', '=', 'receive' ] ])->count();
                if ($coupon_member_count) {
                    $v[ 'is_receive' ] = 1;
                    $v[ 'member_receive_count' ] = $coupon_member_count;
                } else {
                    $v[ 'is_receive' ] = 0;
                    $v[ 'member_receive_count' ] = 0;
                }
            } else {
                $v[ 'member_receive_count' ] = 0;
                $v[ 'is_receive' ] = 0;
            }
        }
        return $list;
    }

    /**
     * 查询优惠券详情
     */
    public function getDetail($id)
    {
        $info = $this->model->where([ [ 'id', '=', $id ] ])->append([ 'coupon_price', 'coupon_min_price' ])->findOrEmpty()->toArray();

        if (empty($info)) {
            throw new CommonException('COUPON_NOT_EXIST');
        }
        if ($info[ 'remain_count' ] != '-1') {
            $info[ 'sum_count' ] = $info[ 'remain_count' ] + $info[ 'receive_count' ];
        } else {
            $info[ 'sum_count' ] = '-1';
        }
        $coupon_member = new CouponMember();
        $member_info = ( new Member() )->where([ [ 'member_id', '=', $this->member_id ] ])->field('member_id')->findOrEmpty()->toArray();

        if ($member_info) {
            $coupon_member_count = $coupon_member->where([ [ 'member_id', '=', $this->member_id ], [ 'coupon_id', '=', $id ], [ 'receive_type', '=', 'receive' ] ])->count();
            if ($coupon_member_count) {
                $info[ 'is_receive' ] = 1;
                $info[ 'member_receive_count' ] = $coupon_member_count;
            } else {
                $info[ 'is_receive' ] = 0;
                $info[ 'member_receive_count' ] = 0;
            }
        } else {
            $info[ 'is_receive' ] = 0;
            $info[ 'member_receive_count' ] = 0;
        }

        return $info;
    }

    /**
     * 优惠券领取
     */
    public function receive($data)
    {
        $member_id = $this->member_id;
        $coupon_id = $data[ 'coupon_id' ];
        $number = $data[ 'number' ];
        $type = $data[ 'type' ];
        $coupon_member_model = new CouponMember();
        Db::startTrans();
        try {
            //判断是否符合设置的领取方式
            $receive_type = $this->getReceiveType();
            if (!in_array($type, $receive_type)) {
                throw new CommonException('COUPON_RECEIVE_TYPE_NOT_EXIST');
            }
            //判断是否已经领取过
            $member_coupon_count = $coupon_member_model->where([ [ 'coupon_id', '=', $coupon_id ], [ 'member_id', '=', $member_id ], [ 'receive_type', '=', 'receive' ] ])->count();
            //判断优惠券数量是否充足
            $info = $this->model->where([ [ 'id', '=', $coupon_id ] ])->findOrEmpty()->toArray();
            if (empty($info)) {
                throw new CommonException('COUPON_NOT_EXIST');
            }

            if($info['status'] != CouponDict::NORMAL){
                throw new CommonException('COUPON_INVALID');//优惠券已失效
            }

            if($info['receive_type'] != CouponDict::USER) {
                throw new CommonException('COUPON_CAN_NOT_MANUAL_RECEIVE');//该优惠券不可手动领取
            }

            if ($member_coupon_count == $info[ 'limit_count' ]) {
                throw new CommonException('COUPON_RECEIVE_EXCESS');//领取超过可领取数量
            }

            if ($info[ 'remain_count' ] != '-1' && $info[ 'remain_count' ] == 0) {
                throw new CommonException('COUPON_STOCK_INSUFFICIENT');//优惠券已被领完
            }
            if (strtotime($info[ 'start_time' ]) > 0) {
                $time = time();
                if ($time < strtotime($info[ 'start_time' ])) {
                    throw new CommonException('COUPON_RECEIVE_NOT_TIME');//优惠券不在领取时间范围内
                }
                if ($time > strtotime($info[ 'end_time' ])) {
                    throw new CommonException('COUPON_RECEIVE_NOT_TIME');//优惠券不在领取时间范围内
                }
            }

            if ($info[ 'remain_count' ] != -1) {
                $coupon_data = [
                    'remain_count' => $info[ 'remain_count' ] - $number,
                    'receive_count' => $info[ 'receive_count' ] + $number,
                ];
            } else {
                $coupon_data = [
                    'receive_count' => $info[ 'receive_count' ] + $number,
                ];
            }

            if ($info[ 'valid_type' ] == 1) {
                $expire_time = 86400 * $info[ 'length' ] + time();
            } else {
                $expire_time = $info[ 'valid_end_time' ];
            }
            $member_coupon_data = [
                'coupon_id' => $coupon_id,
                'member_id' => $member_id,
                'create_time' => time(),
                'expire_time' => $expire_time,
                'receive_type' => $type,
                'type' => $info[ 'type' ],
                'title' => $info[ 'title' ],
                'price' => $info[ 'price' ],
                'status' => CouponMemberDict::WAIT_USE,
                'min_condition_money' => $info[ 'min_condition_money' ]
            ];
            $this->model->where([ [ 'id', '=', $coupon_id ] ])->update($coupon_data);
            $coupon_member_model->create($member_coupon_data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }

    }

    /**
     * 会员优惠券领取记录
     */
    public function getMemberPage($data)
    {
        if (!empty($data[ 'status' ])) {
            $where[] = [ 'status', '=', $data[ 'status' ] ];
        }
        if (in_array($data[ 'type' ], [CouponDict::ALL, CouponDict::CATEGORY, CouponDict::GOODS])) {
            $where[] = [ 'type', '=', $data[ 'type' ] ];
        }
//        elseif ($data[ 'type' ] == -1){
//            $where[] = [ 'expire_time', '>', time() + 86400 * 3 ];
//        }
        $where[] = [ 'member_id', '=', $this->member_id ];
        $coupon_member_model = new CouponMember();
        $search_model = $coupon_member_model
            ->where($where)
            ->order([ 'id desc' ])
            ->append([ 'coupon_price', 'coupon_min_price', 'receive_type_name', 'type_name' ]);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 会员优惠券按状态查询数量
     * @param $data
     * @return mixed
     */
    public function getCouponCountByStatus()
    {
        $count_list = [];
        $status_array = array_keys(CouponMemberDict::getStatus());
        foreach ($status_array as $v) {
            $coupon_member_model = new CouponMember();
            $count = $coupon_member_model->where([[ 'member_id', '=', $this->member_id ], [ 'status', '=', $v]])->count();
            $count_list[] = [
                'status' => $v,
                'status_name' => CouponMemberDict::getStatus($v),
                'count' => $count
            ];
        }
        return $count_list;
    }

    /**
     * 会员已领取优惠券数量
     * @param $data
     * @return mixed
     */
    public function getMemberCount($data)
    {
        if (!empty($data[ 'status' ])) {
            $where[] = [ 'status', '=', $data[ 'status' ] ];
        }
        $where[] = [ 'member_id', '=', $this->member_id ];
//        $where[] = [ 'expire_time', '>', time() ];
        $coupon_member_model = new CouponMember();
        $count = $search_model = $coupon_member_model
            ->where($where)
            ->count();
        return $count;
    }

    //获取优惠券领取方式
    public function getReceiveType()
    {
        $data = event('CouponReceiveType', []);
        if (empty($data)) {
            return [];
        }
        foreach ($data as &$value) {
            foreach ($value as $v) {
                $type[] = $v[ 'name' ];

            }
        }
        return $type;
    }

    /**
     * 获取优惠券类型
     * @return array
     */
    public function getCouponType()
    {
//        $list[] = [
//            'label' => '快过期',
//            'value' => '-1'
//        ];
        $type = CouponDict::getType();
        foreach ($type as $k => $v) {
            $list[] = [
                'label' => $v,
                'value' => $k,
            ];
        }
        return $list;
    }

    /**
     * 获取优惠券列表供组件调用
     * @return array
     */
    public function getCouponComponents($data)
    {
        $field = 'id,title,start_time,end_time,remain_count,receive_count,limit_count,status,price,min_condition_money,type
        ,receive_type,valid_type,length,valid_start_time,valid_end_time,sort';
        $order = 'id desc';

        $where[] = ['status', '=', CouponDict::NORMAL];
        $where[] = [ 'receive_type', '=', CouponDict::USER ];
        if (!empty($data[ 'coupon_ids' ])) {
            $where[] = [ 'id', 'in', $data[ 'coupon_ids' ] ];
        }

        $time_where = function($query) {
            $nowtime = time();
            $time_where = [
                [
                    [ 'start_time', '<=', $nowtime ],
                    [ 'end_time', '>=', $nowtime ]
                ],
                [
                    [ 'start_time', '=', 0 ],
                    [ 'end_time', '=', 0 ]
                ]
            ];
            $query->whereOr($time_where);
        };
        $list = $this->model
            ->field($field)
            ->where($where)
            ->where($time_where)
            ->order($order)
            ->limit($data[ 'num' ])
            ->append([ 'coupon_price', 'coupon_min_price', 'receive_type_name', 'type_name' ])
            ->select()->toArray();

        return $list;
    }

    /**
     * 查询优惠券二维码
     * @param $id
     * @return string
     */
    public function getQrcode($id)
    {
        $info = $this->model->where([ [ 'id', '=', $id ] ])->findOrEmpty()->toArray();

        if (empty($info)) {
            throw new CommonException('COUPON_NOT_EXIST');
        }

        $url = ( new CoreSysConfigService() )->getSceneDomain()[ 'wap_url' ];
        $page = 'addon/shop/pages/coupon/detail';

        $data = [
            [
                'key' => 'mid',
                'value' => $this->member_id
            ],
            [
                'key' => 'coupon_id',
                'value' => $id
            ]
        ];
        $dir = 'upload/shop_coupon_qrcode';
        $channel = 'weapp';

        $path = qrcode($url, $page, $data, $dir, $channel);
        return $path;
    }
}
