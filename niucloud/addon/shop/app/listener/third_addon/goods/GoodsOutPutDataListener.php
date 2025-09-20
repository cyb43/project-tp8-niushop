<?php

namespace addon\shop\app\listener\third_addon\goods;


use addon\shop\app\service\admin\goods\OfferGoodsService;

/**
 * 三方应用获取商城商品列表
 */
class GoodsOutPutDataListener
{

    public function handle($params)
    {
        if (!empty($params['key']) && $params['key'] == 'shop') {
            return (new OfferGoodsService())->getOfferGoodsList($params);
        }
    }

}