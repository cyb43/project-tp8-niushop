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

namespace app\service\api\member;

use app\model\diy_form\DiyFormRecords;
use app\model\member\Member;
use app\service\core\member\CoreMemberConfigService;
use app\service\core\member\CoreMemberService;
use core\base\BaseApiService;
use core\exception\ApiException;
use core\util\Barcode;
use think\Model;

/**
 * 会员服务层
 * Class MemberService
 * @package app\service\api\member
 */
class MemberService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Member();
    }

    /**
     * 新增会员
     */
    public function add(array $data){
        return $this->model->create($data)?->member_id ?? 0;
    }

    /**
     * 更新会员
     * @param array $data
     * @return true
     */
    public function edit(array $data)
    {
        $member = $this->findMemberInfo(['member_id' => $this->member_id]);

        if($member->isEmpty()) throw new ApiException('MEMBER_NOT_EXIST');
        $member->allowField(['nickname', 'headimg', 'birthday', 'sex', 'last_visit_time'])->save($data);
        return true;
    }

    /**
     * 获取会员信息
     * @return array
     */
    public function getInfo()
    {
        $field = 'member_id, username, member_no, mobile, register_channel, nickname, headimg, member_level, member_label, login_ip, login_type, login_time, create_time, last_visit_time, last_consum_time, sex, status, birthday, point, balance, growth, is_member, member_time, is_del, province_id, city_id, district_id, address, location, money, money_get, wx_openid, weapp_openid, commission, commission_get, commission_cash_outing';
        $member_info = $this->model->where([['member_id', '=', $this->member_id]])->with(['member_level_name_bind'])->field($field)->append(['sex_name'])->findOrEmpty()->toArray();
        if (!empty($member_info)) {
            $config = (new CoreMemberConfigService())->getMemberConfig();
            $member_info['form_id'] = $config['form_id'];
            $member_info['form_record_id'] = '';
            if (!empty($member_info['form_id'])) {
                $diy_form_records_model = new DiyFormRecords();
                $form_records = $diy_form_records_model->field('record_id')->where([['form_id', '=', $member_info['form_id']], ['member_id', '=', $member_info['member_id']]])->order('create_time desc')->findOrEmpty()->toArray();
                if (!empty($form_records)) {
                    $member_info['form_record_id'] = $form_records['record_id'];
                }
            }
        }
        return $member_info;
    }

    /**
     * 检测会员信息是否存在
     * @return int
     */
    public function getCount($condition)
    {
        return $this->model->where($condition)->count();
    }

    /**
     * 会员中心信息
     */
    public function center()
    {
        $field = 'member_id, username, member_no, mobile, register_channel, nickname, headimg, member_level, member_label, login_ip, login_type, login_time, create_time, last_visit_time, last_consum_time, sex, status, birthday, point, balance, growth, is_member, member_time, is_del, province_id, city_id, district_id, address, location, money, money_get, commission, commission_get, commission_cash_outing';
        return $this->model->where([['member_id', '=', $this->member_id]])->field($field)->append(['sex_name'])->findOrEmpty()->toArray();
    }

    /**
     * 获取会员的模型对象(todo  慎用!!!  现主要用于登录)
     * @param array $data
     * @return Member|array|mixed|Model  !!! 仔细看,返回值是模型对象  如果想要判断是否为空  请用 $member->isEmpty()
     */
    public function findMemberInfo(array $data){
        //会员账号
        if(!empty($data['username']))
            $where[] = ['username', '=', $data['username']];
        //会员手机号
        if(!empty($data['mobile']))
            $where[] = ['mobile', '=', $data['mobile']];
        //会员id
        if(!empty($data['member_id']))
            $where[] = ['member_id', '=', $data['member_id']];
        //微信公众号openid
        if(!empty($data['wx_openid']))
            $where[] = ['wx_openid', '=', $data['wx_openid']];
        //微信小程序openid
        if(!empty($data['weapp_openid']))
            $where[] = ['weapp_openid', '=', $data['weapp_openid']];
        // 微信unionid
        if(!empty($data['wx_unionid']))
            $where[] = ['wx_unionid', '=', $data['wx_unionid']];
        if(!empty($data['username|mobile']))
            $where[] = ['username|mobile', '=', $data['username|mobile']];
        if(empty($where)){
            $where[] = ['member_id', '=', -1];
        }
        return $this->model->where($where)->findOrEmpty();
    }

    /**
     * 通过对象修改会员信息
     * @param $member
     * @param $data
     * @return void
     */
    public function editByFind($member, $data){
        return $member->save($data);
    }

    /**
     * 修改字段
     * @param string $field
     * @param $data
     * @return null
     */
    public function modify(string $field, $data)
    {
        return (new CoreMemberService())->modify($this->member_id, $field, $data);
    }

    public function getQrcode(){
        // 生成会员二维码
        $qrcode_dir = 'upload/member/temp';
        if (!is_dir($qrcode_dir)) mkdir($qrcode_dir, intval('0755', 8), true);
        $id = "member-".$this->member_id;
        $qrcode_path = "{$qrcode_dir}/order_qrcode_{$this->member_id}.png";
        \core\util\QRcode::png($id, $qrcode_path, 'L', 16, 1);

        // 生成会员条形码
        $barcode_path = (new Barcode(14, $id))->generateBarcode($qrcode_dir, 2);
        $detail = [];
        $detail['verify_code_qrcode'] = image_to_base64($qrcode_path, true);
        $detail['verify_code_barcode'] = image_to_base64($barcode_path);
        return $detail;
    }

    /**
     * 初始化会员数据
     */
    public function initMemberData(){
        if ($this->member_id) {
            event("MemberLoginAfter", ['member_id' => $this->member_id]);
        }
    }
}
