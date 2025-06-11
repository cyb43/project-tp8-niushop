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
 * 云存储枚举类
 * Class StorageDict
 * @package app\dict\sys
 */
class StorageDict
{
    //本地存储
    public const LOCAL = 'local';

    //七牛云存储
    public const QINIU = 'qiniu';

    //阿里云存储
    public const ALI = 'aliyun';

    //腾讯云存储
    public const TENCENT = 'tencent';

    public const ON = '1';//开启
    public const OFF = '0';//关闭


    public static function getType()
    {
        $system = [
            self::LOCAL => [
                'name' => '本地存储',
                'site_name' => '默认存储',
                //配置参数
                'params' => [
                ],
                'component' => '/src/app/views/setting/components/storage-local.vue',
            ],
            self::QINIU => [
                'name' => '七牛云存储',
                //配置参数

                'params' => [
                    'bucket' => '存储空间',
                    'access_key' => 'ACCESS_KEY',
                    'secret_key' => 'SECRET_KEY',
                    'domain' => '空间域名'
                ],
                'encrypt_params' => ['secret_key'],
                'component' => '/src/app/views/setting/components/storage-qiniu.vue',
            ],

            self::ALI => [
                'name' => '阿里云存储',
                //配置参数
                'params' => [
                    'bucket' => '存储空间',
                    'access_key' => 'ACCESS_KEY_ID',
                    'secret_key' => 'ACCESS_KEY_SECRET',
                    'endpoint' => 'Endpoint',
                    'domain' => '空间域名'
                ],
                'encrypt_params' => ['secret_key'],
                'component' => '/src/app/views/setting/components/storage-ali.vue',
            ],

            self::TENCENT => [
                'name' => '腾讯云存储',
                //配置参数
                'params' => [
                    'bucket' => '存储空间',
                    'region' => 'REGION',
                    'access_key' => 'SECRET_ID',
                    'secret_key' => 'SECRET_KEY',
                    'domain' => '空间域名'
                ],
                'encrypt_params' => ['secret_key'],
                'component' => '/src/app/views/setting/components/storage-tencent.vue',
            ],

        ];
        $extend = event('StorageType');
        return array_merge($system, ...$extend);
    }

}
