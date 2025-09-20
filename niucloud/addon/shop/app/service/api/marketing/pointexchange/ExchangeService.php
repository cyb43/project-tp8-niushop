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

namespace addon\shop\app\service\api\marketing\pointexchange;


use addon\shop\app\model\exchange\Exchange;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\service\api\goods\GoodsService;
use core\base\BaseApiService;
use app\model\member\Member;
use app\model\member\MemberAccountLog;
use app\dict\member\MemberAccountTypeDict;


/**
 * 积分商城服务层
 * Class ExchangeService
 * @package addon\shop\app\service\api\marketing\pointexchange
 */
class ExchangeService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Exchange();
    }

    /**
     * 获取积分商城列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'status,total_exchange_num,stock,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        if (!empty($where[ 'order' ]) && in_array($where[ 'order' ], [ 'total_order_num', 'total_exchange_num', 'price' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        } else {
            $order = 'total_order_num desc,sort desc';
        }
        $search_model = $this->model->where([ [ 'id', '>', 0 ] ])->withSearch([ 'names', 'status', 'create_time' ], $where)
            ->append([ 'type_name', 'status_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid', 'goods_label', 'goods_brand' ])->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取积分商城详情
     * @param int $id
     * @return array
     */
    public function getDetail(int $id)
    {

        $field = 'total_exchange_num,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $info = $this->model->where([ [ 'id', '=', $id ] ])->append([ 'type_name', 'goods_cover_thumb_mid', 'goods_image_thumb_small', 'goods_image_thumb_mid', 'goods_image_thumb_big' ])->field($field)->findOrEmpty()->toArray();
        if (!empty($info)) {
            $goods_id = 0;
            foreach ($info[ 'product_detail' ] as $k => $v) {
                if (!empty($v[ 'goods_id' ])) {
                    $goods_id = $v[ 'goods_id' ];
                }
            }
            $goods_info = ( new  GoodsService() )->getDetail([ 'goods_id' => $goods_id, 'sku_id' => $info[ 'product_detail' ][ 0 ][ 'sku_id' ] ]);

            $goods_info[ 'goods' ][ 'goods_desc' ] = $info[ 'content' ];
            $goods_info[ 'sale_num' ] = $info[ 'total_exchange_num' ];
            $goods_info[ 'goods' ][ 'goods_name' ] = $info[ 'names' ];
            $goods_info[ 'goods' ][ 'goods_image' ] = $info[ 'image' ];
            $goods_info[ 'goods' ][ 'goods_image_thumb_big' ] = $info[ 'goods_image_thumb_big' ];
            $goods_info[ 'goods' ][ 'goods_cover_thumb_mid' ] = $info[ 'goods_cover_thumb_mid' ];
            $goods_info[ 'goods' ][ 'sub_title' ] = $info[ 'title' ];
            $product_detail_array = $info[ 'product_detail' ];
            $product_detail_array = array_column($product_detail_array, null, 'sku_id');
            $sku_ids = array_keys($product_detail_array);
            $reset_sku_id = reset($product_detail_array)[ 'sku_id' ];
            $goods_info[ 'price' ] = $product_detail_array[ $reset_sku_id ][ 'price' ];
//            $goods_info[ 'market_price' ] = $product_detail_array[ $reset_sku_id ][ 'price' ];
            $goods_info[ 'sale_price' ] = $product_detail_array[ $reset_sku_id ][ 'price' ];
            $goods_info[ 'stock' ] = min($goods_info[ 'stock' ], $product_detail_array[ $reset_sku_id ][ 'stock' ]);
            $goods_info[ 'point' ] = $product_detail_array[ $reset_sku_id ][ 'point' ];
            $goods_info[ 'exchange_id' ] = $id;
            if (!empty($goods_info[ 'skuList' ])) {
                foreach ($goods_info[ 'skuList' ] as $k => &$item) {
                    $item[ 'limit_num' ] = 0;
                    $item[ 'is_join_exchange' ] = 1;
                    if (!in_array($item[ 'sku_id' ], $sku_ids)) {
                        $item[ 'is_join_exchange' ] = 0;
                        unset($goods_info[ 'skuList' ][ $k ]);
                    }
                    $item[ 'point' ] = 0;
                    $item[ 'is_default' ] = $reset_sku_id == $item[ 'sku_id' ] ? 1 : 0;
                    if (isset($product_detail_array[ $item[ 'sku_id' ] ])) {
                        $item[ 'price' ] = $product_detail_array[ $item[ 'sku_id' ] ][ 'price' ];
//                        $item[ 'market_price' ] = $product_detail_array[ $item[ 'sku_id' ] ][ 'price' ];
                        $item[ 'sale_price' ] = $product_detail_array[ $item[ 'sku_id' ] ][ 'price' ];
                        $item[ 'stock' ] = min($item[ 'stock' ], $product_detail_array[ $item[ 'sku_id' ] ][ 'stock' ]);
                        $item[ 'point' ] = $product_detail_array[ $item[ 'sku_id' ] ][ 'point' ];
                        $item[ 'limit_num' ] = $product_detail_array[ $item[ 'sku_id' ] ][ 'limit_num' ];
                    }
                }
                $goods_info[ 'skuList' ] = array_values($goods_info[ 'skuList' ]);
            }
        }
        return $goods_info ?? [];
    }

    /**
     * 获取积分商城详情
     * @param $where
     * @return mixed
     */
    public function getInfo($where)
    {
        $field = 'stock,total_exchange_num,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $info = $this->model->where([ [ 'id', '>', 0 ] ])->withSearch([ 'names', 'status', 'create_time', 'product_detail', 'sku_id', 'goods_id' ], $where)->append([ 'type_name' ])->field($field)->findOrEmpty()->toArray();
        return $info;
    }

    /**********************************************************组件调用***************************************************************************************/


    /**
     * 获取商品列表供组件调用
     * @param array $where
     * @return array
     */
    public function getGoodsComponents(array $where = [])
    {
        $field = 'total_exchange_num,stock,id,type,names,title,image,product_detail,point,price,limit_num,total_point_num,total_price_num,total_order_num,total_member_num';
//        $goods_where[] = [ 'stock', '>', 0 ];
        $goods_where[] = [ 'status', '=', 1 ];
        if (!empty($where[ 'order' ]) && in_array($where[ 'order' ], [ 'total_order_num', 'total_exchange_num', 'price' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        } else {
            $order = 'total_order_num desc,sort desc';
        }
        $list = $this->model->where($goods_where)->withSearch([ 'names', 'ids' ], $where)
            ->append([ 'type_name', 'goods_cover_thumb_small','goods_cover_thumb_mid', 'goods_label', 'goods_brand' ])
            ->field($field)->order($order)->limit($where[ 'num' ])->select()->toArray();
        return $list;
    }

    /**
     * 获取组件调用 会员当前积分数+今日积分数
     * @return array
     */
    public function getPointInfo()
    {
        $point_data = ( new Member() )->where([ [ 'member_id', '=', $this->member_id ] ])->field('point')->findOrEmpty()->toArray();
        $condition = [
            [ 'account_type', '=', MemberAccountTypeDict::POINT ],
            [ 'account_data', '>', 0 ],
            [ 'member_id', '=', $this->member_id ],
        ];
        $today_point = ( new MemberAccountLog() )->where($condition)->whereBetweenTime('create_time', strtotime("today"), strtotime("today") + 86400)->sum('account_data');
        $point_data[ 'today_point' ] = $today_point;
        return $point_data;
    }

}
