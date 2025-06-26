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

namespace app\service\core\member;

use app\dict\pay\TransferDict;
use app\dict\sys\ConfigKeyDict;
use app\model\diy_form\DiyForm;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use core\exception\CommonException;

/**
 * 会员相关设置
 * Class CoreMemberConfigService
 * @package app\service\core\member
 */
class CoreMemberConfigService extends BaseCoreService
{
    /**
     * 获取注册与登录设置
     * @return array
     */
    public function getLoginConfig()
    {
        $info = ( new CoreConfigService() )->getConfig('LOGIN')[ 'value' ] ?? [];
        return [
            'is_username' => $info[ 'is_username' ] ?? 1, // 是否用户名密码登录
            'is_mobile' => $info[ 'is_mobile' ] ?? 0, // 是否手机验证码登录
            'is_auth_register' => $info[ 'is_auth_register' ] ?? 1, // 是否第三方自动注册
            'is_force_access_user_info' => $info[ 'is_force_access_user_info' ] ?? 0, // 是否强制获取用户信息
            'is_bind_mobile' => $info[ 'is_bind_mobile' ] ?? 0, // 是否强制绑定手机
            'agreement_show' => $info[ 'agreement_show' ] ?? 0, // 政策协议是否展示
            'bg_url' => $info[ 'bg_url' ] ?? '', // 背景图片
            'desc' => $info[ 'desc' ] ?? '精选好物，购物优惠的省钱平台' // 描述
        ];
    }

    /**
     * 注册与登录设置
     * @param array $data
     * @return true
     */
    public function setLoginConfig(array $data)
    {
        $config = [
            'is_username' => $data[ 'is_username' ] ?? 1,// 是否用户名密码登录
            'is_mobile' => $data[ 'is_mobile' ] ?? 0, // 是否手机验证码登录
            'is_auth_register' => $data[ 'is_auth_register' ] ?? 1,// 是否第三方自动注册
            'is_force_access_user_info' => $data[ 'is_force_access_user_info' ] ?? 0, // 是否强制获取用户信息
            'is_bind_mobile' => $data[ 'is_bind_mobile' ] ?? 0, // 是否强制绑定手机
            'agreement_show' => $data[ 'agreement_show' ] ?? 0, // 政策协议是否展示
            'bg_url' => $data[ 'bg_url' ] ?? '', // 背景图片
            'desc' => $data[ 'desc' ] ?? '' // 描述
        ];
        ( new CoreConfigService() )->setConfig('LOGIN', $config);
        return true;
    }

    /**
     * 获取会员设置
     * @return array
     */
    public function getMemberConfig()
    {
        $info = ( new CoreConfigService() )->getConfig('MEMBER')[ 'value' ] ?? [];

        $info[ 'form_id' ] = $info[ 'form_id' ] ?? '';
        if(!empty($info[ 'form_id' ])) {
            $diy_form_model = new DiyForm();
            $diy_form_count = $diy_form_model->where([
                [ 'form_id', '=', $info[ 'form_id' ] ]
            ])->count();
            if ($diy_form_count == 0) {
                $info[ 'form_id' ] = '';
            }
        }

        return [
            'prefix' => $info[ 'prefix' ] ?? '',// 会员编码前缀
            'length' => $info[ 'length' ] ?? 4, // 会员编码长度
            'form_id' => $info[ 'form_id' ], // 万能表单id
        ];
    }

    /**
     * 会员设置
     * @param array $data
     * @return true
     */
    public function setMemberConfig(array $data)
    {
        $config = [
            'prefix' => $data[ 'prefix' ] ?? '',// 会员编码前缀
            'length' => $data[ 'length' ] ?? 4,// 会员编码长度
            'form_id' => $data[ 'form_id' ] ?? '',// 万能表单id
        ];
        ( new CoreConfigService() )->setConfig('MEMBER', $config);
        return true;
    }

    /**
     * 获取成长值规则配置
     * @return array
     */
    public function getGrowthRuleConfig()
    {
        return ( new CoreConfigService() )->getConfigValue('GROWTH_RULE');
    }

    /**
     * 配置成长值规则
     * @param array $data
     * @return true
     */
    public function setGrowthRuleConfig(array $data)
    {
        ( new CoreConfigService() )->setConfig('GROWTH_RULE', $data);
        return true;
    }

    /**
     * 获取积分规则配置
     * @return array
     */
    public function getPointRuleConfig()
    {
        return ( new CoreConfigService() )->getConfigValue('POINT_RULE');
    }

    /**
     * 配置积分规则
     * @param array $data
     * @return true
     */
    public function setPointRuleConfig(array $data)
    {
        ( new CoreConfigService() )->setConfig('POINT_RULE', $data);
        return true;
    }

    /**
     * 获取会员提现设置
     * @return array
     */
    public function getCashOutConfig()
    {
        $config = ( new CoreConfigService() )->getConfig(ConfigKeyDict::MEMBER_CASH_OUT)[ 'value' ] ?? [];
        return [
            'is_open' => $config[ 'is_open' ] ?? '0',//是否启用提现
            'transfer_type' => $config[ 'transfer_type' ] ?? [],//提现方式
            'min' => $config[ 'min' ] ?? '0',//最低提现金额
//            'max'            => '0',//最高提现金额
            'rate' => $config[ 'rate' ] ?? '0',//手续费比率
            'is_auto_verify' => $config[ 'is_auto_verify' ] ?? '0',  //是否自动审核
            'is_auto_transfer' => $config[ 'is_auto_transfer' ] ?? '0',  //是否自动转账
        ];
    }

    /**
     * 会员提现配置
     * @param array $data
     * @return true
     */
    public function setCashOutConfig(array $data)
    {
        //校验转账方式是否合法
        $transfer_type_list = array_keys(TransferDict::getTransferType());
        if (array_diff(array_diff($data[ 'transfer_type' ], $transfer_type_list), $transfer_type_list)) throw new CommonException('TRANSFER_TYPE_NOT_EXIST');
        foreach ($transfer_type_list as $key => $item) {
            if (!in_array($item, $data[ 'transfer_type' ])) {
                unset($transfer_type_list[ $key ]);
            }
        }
        $transfer_type_list = array_values($transfer_type_list);
        $config = [
            'is_open' => $data[ 'is_open' ],//是否启用提现
            'transfer_type' => $transfer_type_list ?? [],//提现方式
            'min' => $data[ 'min' ] ?? '',//最低提现金额
            'is_auto_verify' => $data[ 'is_auto_verify' ] ?? 0,  //是否自动审核
            'is_auto_transfer' => $data[ 'is_auto_transfer' ] ?? 0,  //是否自动转账
//            'max'            => $data['max'] ?? '',//最高提现金额
            'rate' => $data[ 'rate' ] ?? '',//手续费比率
        ];
        ( new CoreConfigService() )->setConfig(ConfigKeyDict::MEMBER_CASH_OUT, $config);
        return true;
    }


}
