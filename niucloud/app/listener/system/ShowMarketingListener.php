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

namespace app\listener\system;

/**
 * 查询营销列表
 * Class ShowAppListener
 * @package app\listener\system
 */
class ShowMarketingListener
{
    public function handle()
    {
        // 应用：app、addon 待定
        // 营销：marketing
        // 工具：tool
        return [
            // 应用
            'app' => [

            ],
            // 工具
            'tool' => [
            ],
            // 营销
            'marketing' => [
                [
                    'title' => '签到管理',
                    'desc' => '客户每日签到发放奖励',
                    'icon' => 'static/resource/images/marketing/sign.png',
                    'key' => 'sign',
                    'url' => '/marketing/sign/config',
                ],
            ]
        ];
    }
}
