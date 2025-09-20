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

namespace addon\shop\app\service\core\pointexchange;


use addon\shop\app\model\goods\GoodsSku;
use core\base\BaseCoreService;
use addon\shop\app\model\exchange\Exchange;


/**
 * 商品库存服务层
 */
class CoreGoodsStockService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Exchange();
    }


    /**
     * 增加库存
     * @param $data
     * @return void
     */
    public function inc($data)
    {
        $where[ 'sku_id' ] = $data[ 'sku_id' ];
        $exchange_goods_info = ( new CorePointexChangeService )->getInfo($where);
        if (!empty($exchange_goods_info)) {
            $sku_key = array_search($data[ 'sku_id' ], array_column($exchange_goods_info[ 'product_detail' ], 'sku_id'));
            $exchange_sku_info = $exchange_goods_info[ 'product_detail' ][ $sku_key ];
            $exchange_goods_info[ 'product_detail' ][ $sku_key ][ 'stock' ] += $data[ 'num' ];
            $update_array[ 'stock' ] = $exchange_sku_info[ 'stock' ] + $data[ 'num' ];
            $update_array[ 'product_detail' ] = json_encode($exchange_goods_info[ 'product_detail' ]);
            $this->model->where([ [ 'id', '=', $exchange_goods_info[ 'id' ] ] ])->update($update_array);
            return true;
        }
    }

    /**
     * 减少库存
     * @param $data
     * @return void
     */
    public function dec($data)
    {

        $where[ 'sku_id' ] = $data[ 'sku_id' ];
        $exchange_goods_info = ( new CorePointexChangeService )->getInfo($where);
        if (!empty($exchange_goods_info)) {
            $sku_key = array_search($data[ 'sku_id' ], array_column($exchange_goods_info[ 'product_detail' ], 'sku_id'));
            $exchange_sku_info = $exchange_goods_info[ 'product_detail' ][ $sku_key ];
            $sku_where[] = [ 'sku_id', '=', $exchange_sku_info[ 'sku_id' ] ];
            $goods_sku_info = ( new  GoodsSku() )->where($sku_where)->field('stock')->findOrEmpty()->toArray();
            if ($exchange_sku_info[ 'stock' ] < $goods_sku_info[ 'stock' ]) {
                $exchange_goods_info[ 'product_detail' ][ $sku_key ][ 'stock' ] -= $data[ 'num' ];
            } else {
                $exchange_goods_info[ 'product_detail' ][ $sku_key ][ 'stock' ] = $goods_sku_info[ 'stock' ];
            }
            $stock = array_sum(array_column($exchange_goods_info[ 'product_detail' ], 'stock'));
            $update_array[ 'product_detail' ] = json_encode($exchange_goods_info[ 'product_detail' ]);
            //数量不足直接下架
            $update_array[ 'stock' ] = max($stock, 0);
            //todo 目前不考虑 切默认展示的sku 限购等数据
            //        array_multisort(array_column($exchange_goods_info['product_detail'], "point"), SORT_ASC, $product_detail);
            //        $update_array['point'] = $product_detail[0]['point'];
            //        $update_array['price'] = $product_detail[0]['price'];
            //        $update_array['limit_num'] = $product_detail[0]['limit_num'];
            $this->model->where([ [ 'id', '=', $exchange_goods_info[ 'id' ] ] ])->update($update_array);
            return true;
        }

    }

}
