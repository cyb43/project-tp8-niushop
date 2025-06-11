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

namespace addon\shop\app\service\api\marketing;

use addon\shop\app\dict\active\DiscountDict;
use addon\shop\app\model\discount\Discount;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\model\goods\Goods;
use app\service\core\sys\CoreConfigService;
use core\base\BaseApiService;
use think\facade\Db;

/**
 * 限时折扣服务层
 * Class DiscountService
 * @package addon\shop\app\service\api\marketing
 */
class DiscountService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Discount();
    }

    /**
     * 查询商品正在参与的限时折扣活动
     * @param $goods_id
     * @return mixed
     */
    public function getInfoByGoods($goods_id)
    {
        $active_goods_model = new DiscountGoods();

        $field = 'discount_goods_id as active_goods_id,discount_id';

        $info = $active_goods_model->where([
            [ 'status', '=', 'active' ],
            [ 'goods_id', '=', $goods_id ]
        ])->field($field)
            ->with([
                'discount' => function($query) {
                    $query->withField('discount_id,name as active_name, remark as active_desc, start_time, end_time');
                }
            ])
            ->findOrEmpty()->toArray();
        $info['active_id'] = $info['discount_id'];
        $info['discount']['active_id'] = $info['active_id'];
        $info['active'] = $info['discount'];
        unset($info['discount']);
        return $info;

    }

    /**
     * 折扣页面轮播图配置
     */
    public function getDiscountBannerConfig()
    {
        $data = ( new CoreConfigService() )->getConfigValue('SHOP_DISCOUNT_BANNER_CONFIG');
        if (empty($data)) {
            $data = [];
        }
        return $data;
    }


    /**
     * 获取商品列表
     * @param array $where
     * @return array
     */
    public function getGoodsPage(array $where = [])
    {
        $field = 'goods_id,label_ids,goods_name,sub_title,goods_category,goods_type,goods_cover,unit,status,sale_num + virtual_sale_num as sale_num,member_discount,is_discount';

        $sku_where = [
            [ 'goods.status', '=', 1 ],
        ];

        if (!empty($where[ 'keyword' ])) {
            $sku_where[] = [ 'goods_name|sub_title', 'like', '%' . $where[ 'keyword' ] . '%' ];
        }

        // 参数过滤
        if (!empty($where[ 'order' ]) && in_array($where[ 'order' ], [ 'sale_num', 'sale_price' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        } else {
            $order = 'sort desc,create_time desc';
        }
        $search_model = ( new Goods() )
            ->alias('goods')
            ->withSearch([ "brand_id", "goods_category", "label_ids", 'service_ids' ], $where)
            ->field($field)
            ->with([ 'skuList'])
            ->withJoin([ 'discountGoods' => function($query) use ($where) {
                $discount_where = [ [ 'discountGoods.status', 'in', [ DiscountDict::ACTIVE, DiscountDict::NOT_ACTIVE ] ] ];
                if ($where[ 'discount_id' ]) $discount_where[] = [ 'discount_id', '=', $where[ 'discount_id' ] ];
                $query->field('discount_id,discountGoods.status as discount_status')->where($discount_where);
            } ], 'left')
            ->group('goods.goods_id')
            ->where($sku_where)->order($order)->hidden(['discountGoods'])->append([ 'goods_type_name', 'goods_cover_thumb_mid', 'goods_label_name', 'discount_status_name' ]);
        $list = $this->pageQuery($search_model);

        $discount_model = (new DiscountGoods());

        if (!empty($list[ 'data' ])){
            foreach ($list[ 'data' ] as $key => $item) {
                $sku_list = $item[ 'skuList' ] ?? [];
                $sku_list = array_column($sku_list, null, 'sku_id');
                $discount_goods_value = $discount_model->where([['discount_id','=',$item['discount_id']],['goods_id','=',$item['goods_id']]])->column('*','sku_id');
                foreach ($discount_goods_value as $k => $v) {
                    if (!empty($v[ 'is_enabled' ])) {
                        $goods_sku = $sku_list[ $v[ 'sku_id' ] ] ?? [];
                        $discount_price = $v[ 'discount_price' ] ?? $goods_sku[ 'price' ];

                        if (( !empty($item[ 'goodsSku' ]) && $item[ 'goodsSku' ][ 'discount_price' ] > $discount_price ) || empty($item[ 'goodsSku' ])) {
                            $item[ 'goodsSku' ] = $goods_sku;
                            $item[ 'goodsSku' ][ 'discount_type' ] = $v[ 'type' ] ?? '';
                            $item[ 'goodsSku' ][ 'discount_rate' ] = $v[ 'rate' ] ?? '111';
                            $item[ 'goodsSku' ][ 'discount_price' ] = $discount_price;
                            $item[ 'goodsSku' ][ 'reduce_money' ] = $v[ 'reduce_money' ] ?? 0;
                        }

                    }
                }
                if (empty($item[ 'goodsSku' ])) {
                    $item[ 'goodsSku' ] = $item[ 'skuList' ][ 0 ] ?? [];
                    $item[ 'goodsSku' ][ 'discount_type' ] = 'discount';
                    $item[ 'goodsSku' ][ 'discount_rate' ] = '10';
                    $item[ 'goodsSku' ][ 'discount_price' ] = $item[ 'goodsSku' ][ 'price' ] ?? '0';
                    $item[ 'goodsSku' ][ 'reduce_money' ] = 0;

                }
                $list[ 'data' ][ $key ] = $item;
            }
        }

        return $list;
    }

    public function getList(array $where)
    {
        $field = 'discount_id,name,remark,start_time,end_time,status,create_time,update_time';

        $order = Db::raw('FIELD(status, "' . DiscountDict::ACTIVE . '","' . DiscountDict::NOT_ACTIVE . '"), start_time asc');

        return $this->model->where([
            [ 'status', 'in', [ DiscountDict::ACTIVE, DiscountDict::NOT_ACTIVE ] ],
        ])->withSearch([ "name" ], $where)->append([ 'status_name' ])->field($field)->limit($where[ 'limit' ])->order($order)->select()->toArray();

    }

}
