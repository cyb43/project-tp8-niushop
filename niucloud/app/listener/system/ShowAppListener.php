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
 * 查询应用列表
 * Class ShowAppListener
 * @package app\listener\system
 */
class ShowAppListener
{
    public function handle()
    {
        // 应用：app、addon 待定
        // 营销：promotion
        // 工具：tool
        return [
            // 应用
            'app' => [

            ],
            // 工具
            'tool' => [
                [
                    'title' => '核销管理',
                    'desc' => '管理核销员及核销记录',
                    'icon' => 'static/resource/images/marketing/verifier.png',
                    'key' => 'verify',
                    'url' => '/marketing/verify/index',
                ],
                [
                    'title' => '万能表单',
                    'desc' => '适用于各种应用场景，满足多样化的业务需求',
                    'icon' => 'static/resource/images/diy_form/icon.png',
                    'key' => 'diy_form',
                    'url' => '/diy_form/list',
                ],
                [
                    'title' => '小票打印',
                    'desc' => '支持打印机添加，便捷创建小票打印模板',
                    'icon' => 'static/resource/images/tool/printer_icon.png',
                    'key' => 'printer_management',
                    'url' => '/printer/list',
                ],
                [
                    'title' => '数据导出',
                    'desc' => '展示导出文件，支持删除与下载',
                    'icon' => 'static/resource/images/tool/export_icon.png',
                    'key' => 'setting_export',
                    'url' => '/setting/export',
                ],

            ],
            // 营销
            'promotion' => [

            ]
        ];
    }
}
