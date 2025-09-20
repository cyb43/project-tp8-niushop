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

namespace addon\shop\app\listener\system;

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
                [
                    'title' => '商品榜单',
                    'desc' => '展示热销商品排行榜',
                    'icon' => 'static/resource/images/marketing/goods_rank.png',
                    'key' => 'shop_goods_rank',
                    'url' => '/shop/marketing/goods_rank/config',
                ],
            ],
            // 营销
            'marketing' => [
                [
                    'title' => '优惠券',
                    'desc' => '设置商家优惠券',
                    'icon' => 'static/resource/images/marketing/coupon.png',
                    'key' => 'shop_goods_coupon',
                    'url' => '/shop/marketing/coupon/list',
                ],
                [
                    'title' => '满减送',
                    'desc' => '购满指定金额享受优惠',
                    'icon' => 'static/resource/images/marketing/manjian.png',
                    'key' => 'shop_goods_manjian',
                    'url' => '/shop/marketing/manjian/list',
                ],
                [
                    'title' => '限时折扣',
                    'desc' => '商品限时促销打折',
                    'icon' => 'static/resource/images/marketing/discount.png',
                    'key' => 'shop_goods_discount',
                    'url' => '/shop/marketing/discount/list',
                ],
                [
                    'title' => '积分商城',
                    'desc' => '客户积分兑换更多好物',
                    'icon' => 'static/resource/images/marketing/exchange.png',
                    'key' => 'shop_point_goods',
                    'url' => '/shop/marketing/exchange/goods_list',
                ],
                [
                    'title' => '新人专享',
                    'desc' => '新人专属优惠活动',
                    'icon' => 'static/resource/images/marketing/newcomer.png',
                    'key' => 'shop_goods_newcomer_discount',
                    'url' => '/shop/marketing/newcomer/config',
                ],
            ]
        ];
    }
}
