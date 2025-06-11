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

namespace app\dict\sys;

/**
 * 短信枚举类
 * Class SmsDict
 * @package app\dict\sys
 */
class SmsDict
{
    //阿里云短信
    public const ALISMS = 'aliyun';
    public const NIUSMS = 'niuyun';
    //腾讯云短信
    public const TENCENTSMS = 'tencent';
    public const SENDING = 'sending';
    public const SUCCESS = 'success';
    public const FAIL = 'fail';
    public const LOGIN = 'login';
    public const REGISTER = 'register';
    public const BIND_MOBILE = 'bind_mobile';
    public const FIND_PASS = 'find_pass';
    public const SCENE_TYPE = [
        self::LOGIN,
        self::REGISTER,
        self::BIND_MOBILE,
        self::FIND_PASS
    ];

    public static function getType()
    {
        $system = [
            self::NIUSMS => [
                'name' => '牛云短信',
                //配置参数
                'params' => [],
                'encrypt_params' => [],
                'show_type'=>'view',
                'view' => '/src/app/views/setting/sms_niu.vue',
                'component' => '',
            ],
            self::ALISMS => [
                'name' => '阿里云短信',
                //配置参数
                'params' => [
                    'sign' => '短信签名',
                    'app_key' => 'APP_KEY',
                    'secret_key' => 'SECRET_KEY'
                ],
                'encrypt_params' => ['secret_key'],
                'show_type'=>'component',
                'component' => '/src/app/views/setting/components/sms-ali.vue',
            ],
            self::TENCENTSMS => [
                'name' => '腾讯云短信',
                //配置参数
                'params' => [
                    'sign' => '短信签名',
                    'app_id' => 'APP_ID',
                    'secret_id' => 'SECRET_ID',
                    'secret_key' => 'SECRET_KEY'
                ],
                'encrypt_params' => ['secret_key'],
                'show_type'=>'component',
                'component' => '/src/app/views/setting/components/sms-tencent.vue',
            ],
        ];
        $extend = event('SmsType');
        return array_merge($system, ...$extend);
    }

    //支持的短信场景

    public static function getStatusType()
    {
        return [
            self::SENDING => 'dict_sms.status_sending',
            self::SUCCESS => 'dict_sms.status_success',
            self::FAIL => 'dict_sms.status_fail',
        ];
    }

}
