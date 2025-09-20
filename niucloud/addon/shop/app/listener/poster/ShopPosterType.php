<?php
declare ( strict_types = 1 );

namespace addon\shop\app\listener\poster;


/**
 * 商品海报类型
 */
class ShopPosterType
{
    /**
     * 商品海报
     * @param array $data
     * @return array
     */
    public function handle($data = [])
    {
        return [
            [
                'type' => 'shop_goods',
                'addon' => 'shop',
                'name' => '商品海报',
                'desc' => '推广商品，分享后进入商品详情页',
                'icon' => 'addon/shop/poster/type_shop_goods.png'
            ],
            [
                'type' => 'shop_point_goods',
                'addon' => 'shop',
                'name' => '积分商品海报',
                'desc' => '推广积分商品，分享后进入积分商品详情页',
                'icon' => 'addon/shop/poster/type_shop_goods.png'
            ],
        ];

    }
}
