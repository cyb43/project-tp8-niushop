<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

use core\dict\DictLoader;

$system = [
    //默认驱动
    'default' => 'dada',
    'drivers' => [
        //达达
        'dada' => [
            'driver' => 'addon\shop\app\service\core\delivery\third_delivery\Dada',  //反射类的名字
            'app_key' => '',
            'secret_key' => '',
            'sign' => '',
        ],
    ]
];

return (new DictLoader("Config"))->load(['data' => $system, 'name' => 'third_delivery']);
