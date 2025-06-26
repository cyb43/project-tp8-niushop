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

namespace app\service\admin\member;

use app\dict\member\MemberRegisterChannelDict;
use app\dict\member\MemberRegisterTypeDict;
use app\model\member\Member;
use app\service\admin\sys\ExportService;
use app\service\core\member\CoreMemberService;
use core\base\BaseAdminService;
use core\dict\DictLoader;
use core\exception\AdminException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 会员服务层
 * Class MemberService
 * @package app\service\admin\member
 */
class MemberService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Member();
    }

    /**
     * 会员列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'member_id, member_no, username, mobile, password, register_channel, register_type, nickname, headimg, member_level, member_label, wx_openid, weapp_openid, wx_unionid, ali_openid, douyin_openid, login_ip, login_type, login_channel, login_count, login_time, create_time, last_visit_time, last_consum_time, sex, status, birthday, point, point_get, balance, balance_get, growth, growth_get, is_member, member_time, is_del, province_id, city_id, district_id, address, location, delete_time, money, money_get, commission, commission_get, commission_cash_outing';
        $search_model = $this->model->withSearch([ 'keyword', 'register_type', 'create_time', 'is_del', 'member_label', 'register_channel', 'member_level' ], $where)
            ->field($field)
            ->order('member_id desc')
            ->with('member_level_name_bind')
            ->append([ 'register_channel_name', 'register_type_name', 'sex_name', 'login_channel_name', 'login_type_name', 'status_name' ]);
        return $this->pageQuery($search_model, function ($item, $key) {
            $item = $this->makeUp($item);
        });
    }

    /**
     * 查询会员列表
     * @param array $where
     * @return array
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function getList(array $where = [])
    {
        $field = 'member_id, nickname, headimg';
        $temp_where = [];
        if (!empty($where[ 'member_ids' ])) {
            $temp_where[] = [ 'member_id', 'in', implode(',', $where[ 'member_ids' ]) ];
        }
        return $this->model->where($temp_where)->withSearch([ 'keyword' ], $where)->field($field)->order('member_id desc')->limit($this->getPageParam()[ 'limit' ] ?? 0)->select()->toArray();
    }

    /**
     * 会员详情
     * @param int $member_id
     * @return array|null
     */
    public function getInfo(int $member_id)
    {
        $field = 'member_id,member_no, id_card,remark,username, mobile, password, register_channel, register_type, nickname, headimg, member_level, member_label, wx_openid, weapp_openid, wx_unionid, ali_openid, douyin_openid, login_ip, login_type, login_channel, login_count, login_time, create_time, last_visit_time, last_consum_time, sex, status, birthday, point, point_get, balance, balance_get, growth, growth_get, is_member, member_time, is_del, province_id, city_id, district_id, address, location, delete_time, money, money_get, commission, commission_get, commission_cash_outing';
        return $this->makeUp($this->model->where([ [ 'member_id', '=', $member_id ] ])->field($field)->with('member_level_name_bind')->append([ 'register_channel_name', 'register_type_name', 'sex_name', 'login_channel_name', 'login_type_name', 'status_name' ])->findOrEmpty()->toArray());
    }

    /**
     * 添加会员
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {

        //检测手机是否重复
        if (!empty($data[ 'mobile' ])) {
            if (!$this->model->where([ [ 'mobile', '=', $data[ 'mobile' ] ] ])->findOrEmpty()->isEmpty())
                throw new AdminException('MOBILE_IS_EXIST');
        }
        if ($data[ 'init_member_no' ] != $data[ 'member_no' ]) {
            if (!$this->model->where([ [ 'member_no', '=', $data[ 'member_no' ] ] ])->findOrEmpty()->isEmpty())
                throw new AdminException('MEMBER_NO_IS_EXIST');
        } else {
            if (!$this->model->where([ [ 'member_no', '=', $data[ 'member_no' ] ] ])->findOrEmpty()->isEmpty()) {
                $data[ 'member_no' ] = $this->getMemberNo();
            }
        }

        $data[ 'username' ] = $data[ 'mobile' ];
        if (!empty($data[ 'username' ])) {
            if (!$this->model->where([ [ 'username', '=', $data[ 'username' ] ] ])->findOrEmpty()->isEmpty())
                throw new AdminException('MEMBER_IS_EXIST');
        }

        if (empty($data[ 'nickname' ]) && !empty($data[ 'mobile' ])) {
            $data[ 'nickname' ] = substr_replace($data[ 'mobile' ], '****', 3, 4);
        }

        $password_hash = create_password($data[ 'password' ]);
        $data[ 'password' ] = $password_hash;
        $data[ 'register_type' ] = MemberRegisterTypeDict::MANUAL;
        $data[ 'register_channel' ] = MemberRegisterChannelDict::MANUAL; // todo 公共化渠道

        $member = $this->model->create($data);
        $data[ 'member_id' ] = $member->member_id;
        event("MemberRegister", $data);
        return $member->member_id;
    }

    /**
     * 更新会员
     * @param int $member_id
     * @param array $data
     * @return true
     */
    public function edit(int $member_id, array $data)
    {
        $where = array(
            [ 'member_id', '=', $member_id ],
        );
        if (!empty($data[ 'password' ])) {
            $data[ 'password' ] = create_password($data[ 'password' ]);
        }
        $this->model->where($where)->update($data);
        return true;
    }

    /**
     * 修改字段
     * @param int $member_id
     * @param string $field
     * @param $data
     */
    public function modify(int $member_id, string $field, $data)
    {
        return ( new CoreMemberService() )->modify($member_id, $field, $data);
    }

    /**
     * 批量修改字段
     * @param $data
     * @return Member
     */
    public function batchModify($data)
    {
        return ( new CoreMemberService() )->batchModify($data);
    }

    /**
     * 组合整理数据
     * @param $data
     */
    public function makeUp($data)
    {
        //会员标签
        if (!empty($data[ 'member_label' ])) {
            $data[ 'member_label_array' ] = ( new MemberLabelService() )->getMemberLabelListByLabelIds($data[ 'member_label' ]);
        }
        return $data;
    }

    /**
     * 会员数量
     * @return int
     * @throws DbException
     */
    public function getCount(array $where = [])
    {
        $where[] = [ 'is_del', '=', 0 ];
        return $this->model->where($where)->count();
    }

    /**
     * 设置状态
     * @param array $member_ids
     * @param int $status
     * @return true
     */
    public function setStatus(array $member_ids, int $status)
    {
        $where = array(
            [ 'member_id', 'in', $member_ids ],
        );
        $data = array(
            'status' => $status
        );
        $this->model->where($where)->update($data);
        return true;
    }

    /**
     * 会员数据
     * @param $field
     * @return float
     */
    public function getSum($field)
    {
        return $this->model->where([ [ 'member_id', '>', 0 ] ])->sum($field);
    }

    /**
     * 创建会员编码
     * @return string|null
     */
    public function getMemberNo()
    {
        return ( new CoreMemberService() )->createMemberNo();
    }

    /**
     * 删除会员
     * @param int $member_id
     * @return true
     */
    public function deleteMember(int $member_id)
    {
        $this->model->destroy(function ($query) use ($member_id) {
            $query->where([ [ 'member_id', '=', $member_id ] ]);
        });
        return true;
    }

    /**
     * 导出会员列表
     * @param array $where
     * @return true
     */
    public function exportMember(array $where = [])
    {
        $field = 'member_id, member_no, username, mobile, nickname, point, balance, money, growth, commission, register_channel, status, create_time, last_visit_time';
        //查询导出数据
        $data = $this->model->withSearch([ 'keyword', 'register_type', 'create_time', 'is_del', 'member_label', 'register_channel' ], $where)->field($field)->append([ 'register_channel_name', 'status_name' ])->select()->toArray();
        //执行导出
        ( new ExportService() )->exportData('member', $data);
        return true;
    }

    /**
     * 获取会员权益字典
     * @return mixed
     */
    public function getMemberBenefitsDict()
    {
        return ( new DictLoader("MemberBenefits") )->load();
    }

    /**
     * 获取会员礼包字典
     * @return array|null
     */
    public function getMemberGiftDict()
    {
        return ( new DictLoader("MemberGift") )->load();
    }

    /**
     * 获取成长值规则字典
     * @return array|null
     */
    public function getGrowthRuleDict()
    {
        return ( new DictLoader("GrowthRule") )->load();
    }

    /**
     * 获取积分规则字典
     * @return array|null
     */
    public function getPointRuleDict()
    {
        return ( new DictLoader("PointRule") )->load();
    }

    /**
     * 获取会员权益内容
     * @param array $benefits
     * @return array|null
     */
    public function getMemberBenefitsContent(array $benefits)
    {
        return ( new CoreMemberService() )->getBenefitsContent($benefits);
    }

    /**
     * 获取会员礼包内容
     * @param array $benefits
     * @return array|null
     */
    public function getMemberGiftsContent(array $gifts)
    {
        return ( new CoreMemberService() )->getGiftContent($gifts);
    }
}
