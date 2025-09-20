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

namespace app\dict\notice;

class NoticeTypeDict
{
    //短信
    public const SMS = 'sms';
    //微信公众号
    public const WECHAT = 'wechat';
    //微信小程序
    public const WEAPP = 'weapp';

    public const TEMPLATE_NEED_PULL = -4;
    public const TEMPLATE_AUDIT_STATUS_NEED_AGAIN_REPORT = -3;
    public const TEMPLATE_AUDIT_STATUS_NEED_EDIT = -2;
    public const TEMPLATE_AUDIT_STATUS_NOT_REPORT = -1;
    public const TEMPLATE_AUDIT_STATUS_WAIT = 1;
    public const TEMPLATE_AUDIT_STATUS_PASS = 2;
    public const TEMPLATE_AUDIT_STATUS_REFUSE = 3;

    public const TEMPLATE_TYPE_VERIFY = 1;
    public const TEMPLATE_TYPE_INDUSTRY_NOTICE = 2;
    public const TEMPLATE_TYPE_SEM = 3;


    //助通签名审核状态 1- 待审核 2- 通过 3-拒绝
    public const API_AUDIT_RESULT_WAIT = 1;
    public const API_AUDIT_RESULT_PASS = 2;
    public const API_AUDIT_RESULT_REFUSE = 3;
    public const BALANCE_RECHARGE_ADD = 1;//充值余额
    public const BALANCE_RECHARGE_REDUCE = 2;//扣减余额

    /**
     * 获取消息类型
     * @return array
     */
    public static function getType()
    {
        return [
            self::SMS => get_lang('dict_notice.type_sms'),//短信
            self::WECHAT => get_lang('dict_notice.type_wechat'),//微信公众号
            self::WEAPP => get_lang('dict_notice.type_weapp'),//微信小程序
        ];
    }

    /**
     * 获取模版类型
     * @return array
     */
    public static function getTemplateType($type = '')
    {
        $data = [
            self::TEMPLATE_TYPE_VERIFY => get_lang('dict_sms_api.template_type_verify_code'),
            self::TEMPLATE_TYPE_INDUSTRY_NOTICE => get_lang('dict_sms_api.template_type_industry_notice'),
            self::TEMPLATE_TYPE_SEM => get_lang('dict_sms_api.template_type_sem'),
        ];
        return $type ? $data[$type] : $data;
    }

    /**
     * 获取消息类型
     * @return array
     */
    public static function getTemplateAuditStatus($type = '')
    {
        $data = [
            self::TEMPLATE_AUDIT_STATUS_NEED_AGAIN_REPORT => get_lang('dict_sms_api.template_status_again_report'),
            self::TEMPLATE_AUDIT_STATUS_NEED_EDIT => get_lang('dict_sms_api.template_status_need_edit'),
            self::TEMPLATE_AUDIT_STATUS_NOT_REPORT => get_lang('dict_sms_api.template_status_not_report'),
            self::TEMPLATE_AUDIT_STATUS_WAIT => get_lang('dict_sms_api.template_status_wait'),
            self::TEMPLATE_AUDIT_STATUS_PASS => get_lang('dict_sms_api.template_status_pass'),
            self::TEMPLATE_AUDIT_STATUS_REFUSE => get_lang('dict_sms_api.template_status_refuse'),
            self::TEMPLATE_NEED_PULL => get_lang('dict_sms_api.template_need_pull'),
        ];
        return $type ? $data[$type] : $data;
    }

    /**
     * 获取消息类型
     * @return array
     */
    public static function getSignAuditType($type = '')
    {
        $data = [
            self::API_AUDIT_RESULT_WAIT => get_lang('dict_sms_api.sign_audit_status_wait'),
            self::API_AUDIT_RESULT_PASS => get_lang('dict_sms_api.sign_audit_status_pass'),
            self::API_AUDIT_RESULT_REFUSE => get_lang('dict_sms_api.sign_audit_status_refuse'),
        ];
        return $type ? $data[$type] : $data;
    }

    /**
     * 获取消息类型
     * @return array
     */
    public static function getBalanceAllocateType($type = '')
    {
        $data = [
            self::BALANCE_RECHARGE_ADD => get_lang('dict_sms_api.balance_add'),
            self::BALANCE_RECHARGE_REDUCE => get_lang('dict_sms_api.balance_reduce'),
        ];
        return $type ? $data[$type] : $data;
    }


    public static function getSignSource($source = '')
    {
        return [
            ['type' => 1, 'name' => '企业名称'],
            ['type' => 2, 'name' => '事业单位'],
            ['type' => 3, 'name' => '商标'],
            ['type' => 4, 'name' => 'APP'],
            ['type' => 5, 'name' => '小程序'],
        ];
    }

    public static function getSignType($type = '')
    {
        return [
            ['type' => 0, 'name' => '全称'],
            ['type' => 1, 'name' => '简称'],
        ];
    }

    public static function getSignDefault($type = '')
    {
        return [
            ['type' => 0, 'name' => '否'],
            ['type' => 1, 'name' => '是'],
        ];
    }

    const PARAMS_TYPE_VALID_CODE = 'valid_code';
    const PARAMS_TYPE_MOBILE_NUMBER = 'mobile_number';
    const PARAMS_TYPE_OTHER_NUMBER = 'other_number';
    const PARAMS_TYPE_AMOUNT = 'amount';
    const PARAMS_TYPE_DATE = 'date';
    const PARAMS_TYPE_CHINESE = 'chinese';
    const PARAMS_TYPE_OTHERS = 'others';
    public static function getApiParamsType()
    {
        return [
            [
                'name' => '验证码',
                'type' => self::PARAMS_TYPE_VALID_CODE,
                'desc' => '4-6位纯数字',
                'rule' => '/^\d$/',
                'min'=>4,
                'max'=>6
            ],
            [
                'name' => '手机号',
                'type' => self::PARAMS_TYPE_MOBILE_NUMBER,
                'desc' => '1-15位纯数字',
                'rule' => '/^\d$/',
                'min'=>1,
                'max'=>15
            ],
            [
                'name' => '其他号码',
                'type' => self::PARAMS_TYPE_OTHER_NUMBER,
                'desc' => '1-32位字母+数字组合',
                'rule'=>'/^[a-zA-Z0-9]$/',
                'min'=>1,
                'max'=>32
            ],
            [
                'name' => '金额',
                'type' => self::PARAMS_TYPE_AMOUNT,
                'desc' => '支持数字或数字的中文 （壹贰叁肆伍陆柒捌玖拾佰仟万亿 圆元整角分厘毫）',
                'rule' => "/^(?:\d+|(?:[零壹贰叁肆伍陆柒捌玖拾佰仟万亿圆角分厘毫]+|圆|元|整)+)$/u"
            ],
            [
                'name' => '日期',
                'type' => self::PARAMS_TYPE_DATE,
                'desc' => '符合时间的表达方式 也支持中文：2019年9月3日16时24分35秒',
                'rule' => ''
            ],
            [
                'name' => '中文',
                'type' => self::PARAMS_TYPE_CHINESE,
                'desc' => '1-32中文，支持中文园括号()',
                'rule' => '/^[\p{Han}()（）]$/u',
                'min'=>1,
                'max'=>32
            ],
            [
                'name' => '其他',
                'type' => self::PARAMS_TYPE_OTHERS,
                'desc' => ' 1-35个中文数字字母组合，支持中文符号和空格',
                'rule' => '/^[\p{Han}\p{N}\p{L}\p{P}\p{S}\s]$/u',
                'min'=>1,
                'max'=>35
            ],
        ];
    }


}