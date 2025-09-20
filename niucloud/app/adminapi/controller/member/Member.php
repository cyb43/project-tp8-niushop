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

namespace app\adminapi\controller\member;

use app\dict\member\MemberDict;
use app\dict\member\MemberRegisterChannelDict;
use app\dict\member\MemberRegisterTypeDict;
use app\service\admin\member\MemberService;
use core\base\BaseAdminController;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;

/**
 * 会员管理
 * Class Member
 * @description 会员管理
 * @package app\adminapi\controller\member
 */
class Member extends BaseAdminController
{
    /**
     * 会员列表
     * @description 会员列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['keyword', ''],
            ['register_type', ''],
            ['register_channel', ''],
            ['create_time', []],
            ['member_label', 0],
            ['member_level', 0],
        ]);
        return success((new MemberService())->getPage($data));
    }

    /**
     * 会员详情
     * @description 会员详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success((new MemberService())->getInfo($id));
    }

    /**
     * 添加会员
     * @description 添加会员
     * @return Response
     */
    public function add()
    {
        $data = $this->request->params([
            ['nickname', ''],
            ['mobile', ''],
            ['member_no', ''],
            ['init_member_no', ''],
            ['password', ''],
            ['headimg', ''],
            ['member_label', []],
            ['sex', 0],
            ['birthday', ''],
            ['remark', ''],
            ['id_card', ''],
        ]);
        $this->validate($data, 'app\validate\member\Member.add');
        $res = (new MemberService())->add($data);
        return success('ADD_SUCCESS', ['member_id' => $res]);
    }

    /**
     * 修改会员
     * @description 修改会员
     * @param $member_id
     * @param $field
     * @return Response
     */
    public function modify($member_id, $field)
    {
        $data = $this->request->params([
            ['value', ''],
            ['field', $field],
        ]);
        $data[$field] = $data['value'];
        $data['member_id'] = $member_id;
        $this->validate($data, 'app\validate\member\Member.modify');
        (new MemberService())->modify($member_id, $field, $data['value']);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 更新
     * @description 更新
     * @return Response
     */
    public function edit($member_id)
    {
        $data = $this->request->params([
            ['nickname', ''],
            ['headimg', ''],
            ['password', ''],
            ['member_label', []],
            ['sex', 0],
            ['birthday', ''],
        ]);
        $this->validate($data, 'app\validate\member\Member.edit');
        $res = (new MemberService())->edit($member_id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 删除会员
     * @description 删除会员
     * @param $member_id
     * @return Response
     */
    public function del($member_id)
    {
        $res = (new MemberService())->deleteMember($member_id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 导出会员列表
     * @description 导出会员列表
     * @return Response
     */
    public function export()
    {
        $data = $this->request->params([
            ['keyword', ''],
            ['register_type', ''],
            ['register_channel', ''],
            ['create_time', []],
            ['member_label', 0],
        ]);
        (new MemberService())->exportMember($data);
        return success('SUCCESS');
    }

    /**
     * 会员使用场景
     * @description 会员使用场景
     * @return Response
     */
    public function getMemberRegisterType()
    {
        return success(MemberRegisterTypeDict::getType());
    }

    /**
     * 会员列表
     * @description 会员列表
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMemberList()
    {
        $data = $this->request->params([
            ['keyword', ''],
            ['member_ids', []],
        ]);
        return success((new MemberService())->getList($data));
    }

    /**
     * 获取会员注册渠道
     * @description 获取会员注册渠道
     * @return Response
     */
    public function getMemberRegisterChannelType()
    {
        return success(MemberRegisterChannelDict::getType());
    }

    /**
     * 设置会员的状态
     * @description 设置会员的状态
     * @param $status
     * @return Response
     */
    public function setStatus($status)
    {
        $data = $this->request->params([
            ['member_ids', []],

        ]);
        $this->validate(['status' => $status], 'app\validate\member\Member.set_status');
        (new MemberService())->setStatus($data['member_ids'], $status);
        return success('EDIT_SUCCESS');
    }

    /**
     * 获取状态枚举
     * @description 获取状态枚举
     * @return Response
     */
    public function getStatusList()
    {
        return success(MemberDict::getStatus());
    }

    /**
     * 获取会员编码
     * @description 获取会员编码
     * @return Response
     */
    public function getMemberNo()
    {
        $member_no = (new MemberService())->getMemberNo();
        return success('SUCCESS', $member_no);
    }

    /**
     * 获取会员权益字典
     * @description 获取会员权益字典
     * @return Response
     */
    public function getMemberBenefitsDict()
    {
        return success((new MemberService())->getMemberBenefitsDict());
    }

    /**
     * 获取会员礼包字典
     * @description 获取会员礼包字典
     * @return Response
     */
    public function getMemberGiftDict()
    {
        return success((new MemberService())->getMemberGiftDict());
    }

    /**
     * 获取成长值规则字典
     * @description 获取成长值规则字典
     * @return Response
     */
    public function getGrowthRuleDict()
    {
        return success((new MemberService())->getGrowthRuleDict());
    }

    /**
     * 获取积分规则字典
     * @description 获取积分规则字典
     * @return Response
     */
    public function getPointRuleDict()
    {
        return success((new MemberService())->getPointRuleDict());
    }

    /**
     * 获取会员权益内容
     * @description 获取会员权益内容
     * @return Response
     */
    public function getMemberBenefitsContent()
    {
        $data = $this->request->params([
            ['benefits', []],
        ]);
        return success((new MemberService())->getMemberBenefitsContent($data['benefits']));
    }

    /**
     * 获取会员礼包内容
     * @description 获取会员礼包内容
     * @return Response
     */
    public function getMemberGiftsContent()
    {
        $data = $this->request->params([
            ['gifts', []],
        ]);
        return success((new MemberService())->getMemberGiftsContent($data['gifts']));
    }

    /**
     * 批量操作
     * @description 批量操作
     * @return Response
     */
    public function batchModify()
    {
        $data = $this->request->params([
            ['is_all', 0],
            ['where', []],
            ['member_ids', []],
            ['value', ''],
            ['field', ''],
        ]);
        (new MemberService())->batchModify($data);
        return success('MODIFY_SUCCESS');
    }
}
