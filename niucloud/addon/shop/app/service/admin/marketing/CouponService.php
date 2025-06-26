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

use addon\shop\app\dict\coupon\CouponDict;
use addon\shop\app\dict\coupon\CouponMemberDict;
use addon\shop\app\job\marketing\CouponSend;
use addon\shop\app\model\coupon\Coupon;
use addon\shop\app\model\coupon\CouponGoods;
use addon\shop\app\model\coupon\CouponMember;
use addon\shop\app\model\coupon\CouponSendRecord;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\service\admin\goods\CategoryService;
use addon\shop\app\service\core\coupon\CoreCouponMemberService;
use app\model\member\Member;
use app\model\member\MemberLabel;
use app\model\member\MemberLevel;
use app\service\core\sys\CoreConfigService;
use core\exception\AdminException;
use core\base\BaseAdminService;
use core\exception\CommonException;
use think\db\Query;
use think\facade\Db;
use function DI\string;

/**
 * 优惠券服务层
 * Class CouponService
 * @package addon\shop\app\service\admin\marketing
 */
class CouponService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Coupon();
    }

    public function getInit()
    {
        // 查询商品分类
        $goods_category_tree = [];
        $goods_category_service = new CategoryService();
        $goods_category = $goods_category_service->getTree();
        foreach ($goods_category as $k => $v) {
            $children = [];
            if (!empty($v['child_list'])) {
                foreach ($v['child_list'] as $ck => $cv) {
                    $children[] = [
                        'value' => $cv['category_id'],
                        'label' => $cv['category_name'],
                    ];
                }
            }
            $goods_category_tree[] = [
                'value' => $v['category_id'],
                'label' => $v['category_name'],
                'children' => $children
            ];
        }
        $res['goods_category_tree'] = $goods_category_tree;
        return $res;
    }

    /**
     * 获取优惠券列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getPage(array $where = [])
    {
        $field = 'id,title,price,type,receive_type,start_time,end_time,remain_count,receive_count,give_count,status,limit_count,min_condition_money,receive_status,valid_type,length,valid_end_time';
        $order = 'id desc';
        $search_model = $this->model->where([['id', '>', 0]])->withSearch(["title", "status"], $where)->append(['is_show_send', 'type_name', 'receive_type_name', 'status_name'])->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        $coupon_member_model = new CouponMember();
        foreach ($list['data'] as $k => &$v) {
            if ($v['remain_count'] != '-1') {
                $v['sum_count'] = $v['remain_count'] + $v['receive_count'];
            } else {
                $v['sum_count'] = '-1';
            }
            //查询已使用数量
            $v['receive_use_count'] = $coupon_member_model->where([['coupon_id', '=', $v['id']], ['use_time', '>', 1]])->count();
        }
        return $list;
    }

    /**
     * 获取优惠券选择分页列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getSelectPage(array $where = [])
    {
        $verify_coupon_ids = [];
        // 检测优惠券id集合是否存在，移除不存在的优惠券id，纠正数据准确性
        if (!empty($where['verify_coupon_ids'])) {
            $verify_coupon_ids = $this->model->where([
                ['id', 'in', $where['verify_coupon_ids']]
            ])->withSearch(["title"], $where)->field('id')->select()->toArray();

            if (!empty($verify_coupon_ids)) {
                $verify_coupon_ids = array_column($verify_coupon_ids, 'id');
            }
        }

        $field = 'id,title,price,type,receive_type,start_time,end_time,remain_count,receive_count,status,limit_count,min_condition_money,receive_status,valid_type,length,valid_end_time';
        $order = 'id desc';
        $search_model = $this->model->where([['status', '=', CouponDict::NORMAL]])->withSearch(["title"], $where)->append(['type_name', 'receive_type_name', 'status_name'])->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        $list['verify_coupon_ids'] = $verify_coupon_ids;
        return $list;
    }

    /**
     * 查询选中的优惠券
     * @param string $coupon_ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSelectedList(string $coupon_ids = '')
    {
        if (empty($coupon_ids)) return [];
        $field = 'id,title,price,type,receive_type,start_time,end_time,remain_count,receive_count,status,limit_count,min_condition_money,receive_status,valid_type,length,valid_end_time';
        $order = 'id desc';
        return $this->model->where([['id', 'in', $coupon_ids]])->append(['type_name', 'receive_type_name', 'status_name'])->field($field)->order($order)->select()->toArray();
    }

    /**
     * 获取优惠券详情
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $info = $this->model->where([['id', '=', $id]])->findOrEmpty()->toArray();
        if ($info['remain_count'] == '-1') {
            $info['limit'] = 2;
        } else {
            $info['limit'] = 1;
        }
        if ($info['min_condition_money'] == '0.00') {
            $info['threshold'] = 2;
        } else {
            $info['threshold'] = 1;
        }
        if ($info['limit_count'] == 0) {
            $info['limit_count'] = '';
        }

        if ($info['type'] == 2) {
            $goods_coupon_model = new CouponGoods();
            $goods_coupon_list = $goods_coupon_model->where([['coupon_id', '=', $id]])->field('category_id')->select()->toArray();
            $info['goods_category_ids'] = array_column($goods_coupon_list, 'category_id');
        } else {
            $info['goods_category_ids'] = [];
        }
        if ($info['type'] == 3) {
            $goods_coupon_model = new CouponGoods();
            //获取商品ids
            $goods_coupon_ids = $goods_coupon_model->where([['coupon_id', '=', $id]])->column('goods_id');
            //获取选中商品信息
            $goods_list = (new Goods())->field('goods_id,goods_cover,goods_name,status')
                ->where([['goods.goods_id', 'in', $goods_coupon_ids], ['goods.status', '=', 1], ['goodsSku.is_default', '=', 1]])
                ->withJoin(['goodsSku' => function ($query) {
                    $query->field('sku_id,sku_name,sku_image,goodsSku.stock,goodsSku.price');
                }])
                ->hidden(['goodsSku'])
                ->append(['goods_cover_thumb_small'])->select()->toArray();
            $info['goods_ids'] = $goods_coupon_ids;
            $info['goods_list'] = $goods_list;
        } else {
            $info['goods_ids'] = [];
        }
        if ($info['remain_count'] != '-1') {
            $info['sum_count'] = $info['remain_count'] + $info['receive_count'];
        } else {
            $info['remain_count'] = 1000;
            $info['sum_count'] = '不限量';
        }

        //查询已使用数量
        $coupon_member_model = new CouponMember();
        $info['receive_invalid_count'] = $coupon_member_model->where([['coupon_id', '=', $info['id']], ['status', '=', CouponMemberDict::INVALID]])->count();
        $info['receive_use_count'] = $coupon_member_model->where([['coupon_id', '=', $info['id']], ['use_time', '>', 1]])->count();

        //查询已过期数量
        $info['receive_expire_count'] = $coupon_member_model->where([['coupon_id', '=', $info['id']], ['use_time', '=', 0], ['expire_time', '<', time()]])->count();

        return $info;
    }

    /**
     * 添加优惠券
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        if ($data['threshold'] == 2) {
            $data['min_condition_money'] = 0;
        }
        if ($data['receive_type'] == 2) {
            unset($data['receive_time']);
            unset($data['valid_time']);
            unset($data['limit_count']);
            unset($data['remain_count']);
        } else {
            if ($data['receive_type_time'] == 1) {
                if (!empty($data['receive_time'])) {
                    $data['start_time'] = strtotime($data['receive_time'][0]);
                    $data['end_time'] = strtotime($data['receive_time'][1]);
                    $now_time = strtotime(date('Y-m-d', time()));
                    if ($data['start_time'] > $now_time) {
                        $data['status'] = 0; // 活动未开始
                    }
                } else {
                    $data['start_time'] = '';
                    $data['end_time'] = '';
                }
            } else {
                $data['start_time'] = '';
                $data['end_time'] = '';
            }
            if (!empty($data['valid_time'])) {
                $data['valid_start_time'] = time();
                $data['valid_end_time'] = strtotime($data['valid_time']);
                if ($data['valid_end_time'] <= $data['valid_start_time']) throw new AdminException('SHOP_COUPON_VALID_END_TIME_NOT_ALLOW_LT_START_TIME');
            }
            if ($data['limit'] == 2) {
                $data['remain_count'] = '-1';
            }
        }

        if (!empty($data['goods_ids']) || !empty($data['goods_category_ids'])) {

            $coupon_goods_model = new CouponGoods();
            if (!empty($data['goods_ids'])) {
                Db::startTrans();
                try {
                    $res = $this->model->create($data);
                    $coupon_goods = [];
                    foreach ($data['goods_ids'] as $value) {
                        $coupon_goods[] = [
                            'coupon_id' => $res->id,
                            'goods_id' => $value,
                        ];
                    }
                    $coupon_goods_model->saveAll($coupon_goods);
                    Db::commit();
                    return $res->id;
                } catch (\Exception $e) {
                    Db::rollback();
                    throw new CommonException($e->getMessage());
                }
            }
            if (!empty($data['goods_category_ids'])) {
                Db::startTrans();
                try {
                    $res = $this->model->create($data);
                    $coupon_goods = [];
                    foreach ($data['goods_category_ids'] as $category) {
                        $coupon_goods[] = [
                            'coupon_id' => $res->id,
                            'category_id' => $category[count($category) - 1],
                        ];
                    }

                    $coupon_goods_model->saveAll($coupon_goods);
                    Db::commit();
                    return $res->id;
                } catch (\Exception $e) {
                    Db::rollback();
                    throw new CommonException($e->getMessage());
                }
            }
        } else {
            $res = $this->model->create($data);
            return $res->id;
        }
    }

    /**
     * 编辑优惠券
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $coupon_ids = $this->checkCouponInUse();
        if (in_array($id, $coupon_ids)) {
            throw new AdminException('SHOP_COUPON_IN_USE_NOT_ALLOW_EDIT');
        }
        if ($data['threshold'] == 2) {
            $data['min_condition_money'] = 0;
        }
        unset($data['threshold']);
        if ($data['receive_type'] == 2) {
            unset($data['receive_time']);
            unset($data['valid_time']);
            unset($data['limit_count']);
            unset($data['remain_count']);

        } else {
            if ($data['receive_type_time'] == 1) {
                if (!empty($data['receive_time'])) {
                    $data['start_time'] = strtotime($data['receive_time'][0]);
                    $data['end_time'] = strtotime($data['receive_time'][1]);
                    $now_time = strtotime(date('Y-m-d', time()));
                    if ($data['start_time'] > $now_time) {
                        $data['status'] = 0; // 活动未开始
                    }
                } else {
                    $data['start_time'] = '';
                    $data['end_time'] = '';
                    $data['status'] = 1;
                }
            } else {
                $data['start_time'] = '';
                $data['end_time'] = '';
                $data['status'] = 1;
            }

            if ($data['valid_type'] == 2) {
                if (!empty($data['valid_time'])) {
                    $data['valid_start_time'] = time();
                    $data['valid_end_time'] = strtotime($data['valid_time']);
                    if ($data['valid_end_time'] <= $data['valid_start_time']) throw new AdminException('SHOP_COUPON_VALID_END_TIME_NOT_ALLOW_LT_START_TIME');
                }
            } else {
                $data['valid_start_time'] = '';
                $data['valid_end_time'] = '';
            }
            if ($data['limit'] == 2) {
                $data['remain_count'] = '-1';
            }

        }
        unset($data['limit']);
        unset($data['receive_time']);
        unset($data['valid_time']);
        unset($data['receive_type_time']);
        unset($data['type']);

        $coupon_goods_model = new CouponGoods();
        if (!empty($data['goods_ids'])) {
            Db::startTrans();
            try {

                $coupon_goods = [];
                $coupon_goods_model->where([['coupon_id', '=', $id]])->delete();
                foreach ($data['goods_ids'] as $value) {
                    $coupon_goods[] = [
                        'coupon_id' => $id,
                        'goods_id' => $value,
                    ];
                }
                $coupon_goods_model->saveAll($coupon_goods);
                unset($data['goods_ids']);
                unset($data['goods_category_ids']);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw new CommonException($e->getMessage());
            }
        } else if (!empty($data['goods_category_ids'])) {
            Db::startTrans();
            try {
                $coupon_goods = [];
                $coupon_goods_model->where([['coupon_id', '=', $id]])->delete();
                foreach ($data['goods_category_ids'] as $category) {
                    $coupon_goods[] = [
                        'coupon_id' => $id,
                        'category_id' => $category[count($category) - 1],
                    ];
                }
                $coupon_goods_model->saveAll($coupon_goods);
                unset($data['goods_ids']);
                unset($data['goods_category_ids']);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw new CommonException($e->getMessage());
            }
        } else {
            unset($data['goods_ids']);
            unset($data['goods_category_ids']);
            Db::startTrans();
            try {
                $coupon_goods_model->where([['coupon_id', '=', $id]])->delete();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw new CommonException($e->getMessage());
            }
        }
        $res = $this->model->where([
            ['id', '=', $id]
        ])->update($data);
        (new CoreCouponMemberService())->refeshMemberCouponExpireTime($id);
        return $res;
    }

    /**
     * 删除优惠券
     * @param $ids
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function del($ids)
    {
        if (empty($ids)) {
            return true;
        }
        $coupon_list = $this->model->where([
            ['id', 'in', implode(',', $ids)],
        ])->field('id,status,title')->select()->toArray();
        $coupon_names = array_column($coupon_list, 'title', 'id');

        $use_coupon_list = (new CouponMember())->where([
            ['coupon_id', 'in', implode(',', $ids)],
            ['status', '=', CouponMemberDict::WAIT_USE]
        ])->field('coupon_id')->select()->toArray();

        $not_del_coupon_list = array_unique(array_column($use_coupon_list, 'coupon_id'));
        if (count($not_del_coupon_list) > 0) {
            $err_str = '';
            foreach ($not_del_coupon_list as $coupon_id) {
                $err_str .= $coupon_names[$coupon_id] . '、';
            }
            $err_str = rtrim($err_str, '、');
            $err_str .= " \n" . get_lang('SHOP_COUPON_IN_USE_NOT_ALLOW_DEL');
            throw new AdminException($err_str);
        }
        return $this->model->where([['id', 'in', implode(',', $ids)]])->delete();
    }

    /**
     * 领取记录
     * @param int $id
     * @return void
     */
    public function getMemberCoupon($data)
    {
        $coupon_member_model = new CouponMember();
        $member_where = [];
        if (isset($data['keywords']) && $data['keywords'] != '') {
            $member_where = [['member.member_no|member.username|member.nickname|member.mobile', 'like', '%' . $this->model->handelSpecialCharacter($data['keywords']) . '%']];
        }
        $memberList = $coupon_member_model->where([['coupon_id', '=', $data['id']]])->withJoin([
            'member' => ['member_id', 'member_no', 'username', 'mobile', 'nickname'],
        ])->where($member_where)->order('id desc')->append(['status_name', 'receive_type_name']);
        $list = $this->pageQuery($memberList);
        return $list;
    }

    /**
     * 领取状态修改
     * @param $id
     * @param $status
     * @return true
     */
    public function setStatus($id, $status)
    {
        $coupon_ids = $this->checkCouponInUse();
        if (in_array($id, $coupon_ids)) {
            throw new AdminException('SHOP_COUPON_IN_USE_NOT_ALLOW_EDIT');
        }
        $data = array(
            'receive_status' => $status
        );
        $this->model->where([['id', '=', $id]])->update($data);
        return true;
    }

    /**
     * 优惠券失效
     * @param $id
     * @param $status
     * @return true
     */
    public function couponInvalid($ids)
    {
        $use_coupon_ids = $this->checkCouponInUse();
        $coupon_list = $this->model->where([
            ['id', 'in', implode(',', $ids)]
        ])->field('id,status,title')->select()->toArray();
        $coupon_names = array_column($coupon_list, 'title', 'id');
        $diffIds = array_unique(array_intersect($use_coupon_ids, $ids));
        if (count($diffIds) > 0) {
            $err_str = '';
            foreach ($diffIds as $coupon_id) {
                $err_str .= $coupon_names[$coupon_id] . '、';
            }
            $err_str = rtrim($err_str, '、');
            $err_str .= " \n" . get_lang('SHOP_COUPON_IN_USE_NOT_ALLOW_EDIT');
            throw new AdminException($err_str);
        }
        $data = array(
            'status' => CouponDict::INVALID
        );
        $res = $this->model->where([['id', 'in', implode(',', $ids)]])->update($data);
        $coupon_member_model = new CouponMember();
        if ($res) $coupon_member_model->where([['coupon_id', 'in', implode(',', $ids)], ['status', '=', CouponMemberDict::WAIT_USE]])->update(['status' => CouponMemberDict::INVALID]);
        return true;
    }


    /**
     * 优惠券使用检测
     * @return array
     */
    public function checkCouponInUse()
    {
        $coupon_ids = [];
        $sign_config = (new CoreConfigService())->getConfig('SIGN_CONFIG');
        if (!empty($sign_config) && !empty($sign_config['value'])) {
            $sign_info = $sign_config['value'];
            if (!empty($sign_info['day_award']) && !empty($sign_info['day_award']['shop_coupon'])) {
                $coupon_ids = $sign_info['day_award']['shop_coupon']['coupon_id'];
            }
            if (!empty($sign_info['continue_award'])) {
                foreach ($sign_info['continue_award'] as $item) {
                    if (!empty($item['shop_coupon'])) {
                        $coupon_ids = array_merge($coupon_ids, $item['shop_coupon']['coupon_id']);
                    }
                }
            }
            $coupon_ids = array_values(array_unique($coupon_ids));
        }
        return $coupon_ids;
    }

    public function getSendReordsPageList($coupon_id, $data)
    {
        $where[] = ['coupon_id', '=', $coupon_id];
        if (!empty($data['range_type'])) {
            $where[] = ['range_type', '=', $data['range_type']];
        }
        $model = (new CouponSendRecord())->where($where)->with([
            'coupon' => function ($query) {
                $query->field('title,id');
            }
        ])->withSearch(['create_time'], $data)->field("success_num,status,id,coupon_id,range_type,range_param,end_time,admin_username,create_time")->append(['status_name', 'range_param_value', 'range_type_name'])->order('create_time desc');
        $list = $this->pageQuery($model);
        return $list;
    }

    /**
     * 添加发券记录
     * @param $coupon_id
     * @param $data
     * @return true
     * @throws \think\db\exception\DbException
     */
    public function sendCoupon($coupon_id, $data)
    {
        $coupon = (new Coupon())->where([['id', '=', $coupon_id]])->findOrEmpty();
        if ($coupon->isEmpty()) {
            throw new CommonException('COUPON_NOT_EXIST');
        }
        if ($coupon->status != CouponDict::NORMAL) {
            throw new CommonException('COUPON_INVALID');
        }
        $range_param = $data['range_param'] ?? [];
        list($member_num, $range_param) = $this->getSendMemberNum($data['range_type'], $range_param);
        $data = [
            'coupon_id' => $coupon_id,
            'send_num' => $data['send_num'],
            'range_type' => $data['range_type'],
            'range_param' => $range_param,
            'member_num' => $member_num,
            'status' => CouponDict::SEND_STATUS_WAIT,
            'admin_uid' => $this->uid,
            'admin_username' => $this->username,
            'create_time' => time(),
        ];
        $record_id = (new CouponSendRecord())->insertGetId($data);
//        (new CouponSend())->doJob($record_id);
        CouponSend::dispatch(['record_id' => $record_id]);
        return true;
    }

    /**
     * 获取发送用户数量
     * @param $range_type
     * @param $range_param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getSendMemberNum($range_type, $range_param)
    {
        switch ($range_type) {
            case CouponDict::SEND_RANGE_ALL:
                $member_num = (new Member())->where([
                    ['member_id', '>', 0]
                ])->count();
                break;
            case CouponDict::SEND_RANGE_MEMBER:
                $member_num = count($range_param['member_ids']);
                break;
            case CouponDict::SEND_RANGE_MEMBER_LEVEL:
                $member_level = $range_param['member_level'];
                $member_num = (new Member())->where([
                    ['member_level', 'in', implode(',', $member_level)],
                ])->count();
                $level_names = (new MemberLevel())->where([
                    ['level_id', 'in', implode(',', $member_level)],
                ])->column('level_name');
                $range_param['level_name'] = implode('、', $level_names);
                break;
            case CouponDict::SEND_RANGE_MEMBER_LABEL:
                $member_label = $range_param['member_label'];
                $member_num = (new Member())->where([
                    ['member_id', '>', 0]
                ])->withSearch(['member_label'], ['member_label' => $member_label])->count();
                $label_names = (new MemberLabel())->where([
                    ['label_id', 'in', implode(',', $member_label)],
                ])->column('label_name') ?? [];
                $range_param['label_name'] = implode('、', $label_names);;
                break;
            default:
                $member_num = 0;
                break;
        }
        return [$member_num, $range_param];

    }

}
