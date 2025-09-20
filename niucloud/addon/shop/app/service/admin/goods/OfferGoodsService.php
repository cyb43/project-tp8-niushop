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

namespace addon\shop\app\service\admin\goods;

use addon\shop\app\model\goods\Goods;

use addon\shop\app\model\goods\GoodsSpec;
use core\base\BaseAdminService;
use think\db\Query;


/**
 * 输出商品服务层
 * Class GoodsService
 * @package addon\shop\app\service\admin\goods
 */
class OfferGoodsService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Goods();
    }

    /**
     * 获取输出商品初始化数据
     * @return array
     */
    public function getWhereInit()
    {
        return [
            'label_list' => (new LabelService())->getList(),
            'brand_list' => (new BrandService())->getList(),
            'category_list' => (new CategoryService())->getTree(),
            'type_list' => (new GoodsService())->getType(),
        ];
    }

    /**
     * 输出商品列表
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getOfferGoodsList($param)
    {
        $field = 'goods_id,goods_name,sub_title,goods_image,unit,goods_video,goods_desc,goods_id as source_id,goods_cover,stock';
        $order = 'create_time desc';
        $where = [
            'status' => 1,
            'goods_name' => $param['goods_name'] ?? '',
            'goods_type' => $param['goods_type'] ?? '',
            'brand_id' => $param['brand_id'] ?? '',
            'goods_category' => $param['goods_category'] ?? '',
            'label_ids' => $param['label_ids'] ?? '',
        ];
        $search_model = $this->model
            ->withSearch(["goods_name", 'goods_type', 'brand_id', 'goods_category', 'label_ids', "status"], $where)
            ->field($field)
            ->with([
                'sku_list' => function (Query $query) {
                    $query->field(['goods_id', 'sku_id', 'is_default', 'sku_name', 'sku_image', 'price', 'is_default', 'sku_spec_format', 'sku_id as source_sku_id', 'stock'])->order('is_default desc');
                },
                'goods_spec' => function (Query $query) {
                    $query->field(['goods_id', 'spec_name', 'spec_values']);
                },
            ])
            ->hidden(['goods_id', 'sku_list' => ['goods_id', 'sku_id']])
            ->append(['goods_cover_thumb_small'])
            ->order($order);
        $list = $this->pageQuery($search_model, function (&$item, $key) {
            $item = $item->toArray();
            $temp_sku_data = array_filter(array_column($item['sku_list'], 'sku_spec_format'));
            $item['spec_type'] = 'single';
            if (!empty($temp_sku_data)) {
                // 多规格
                $item['spec_type'] = 'multi';
            }
            return $item;
        });
        return $list;
    }


    /**
     * 商品详情
     * @param $param
     * @return array
     */
    public function getOfferGoodsInfo($param)
    {

        $field = 'goods_id,goods_name,sub_title,goods_image,goods_video,goods_desc,goods_id as source_id';
        $order = 'create_time desc';
        $where = [
            'status' => 1,
            'goods_id' => $param['goods_id'] ?? '',
        ];

        $info = $this->model
            ->withSearch(["goods_id", "status"], $where)
            ->field($field)
            ->with([
                'sku_list' => function (Query $query) {
                    $query->field(['goods_id', 'sku_id', 'sku_name', 'sku_image', 'price', 'is_default', 'sku_spec_format', 'sku_id as source_sku_id']);
                },
                'goods_spec' => function (Query $query) {
                    $query->field(['goods_id', 'spec_name', 'spec_values']);
                },
            ])
            ->hidden(['goods_id', 'sku_list' => ['goods_id', 'sku_id']])
            ->append(['goods_cover_thumb_small'])
            ->order($order)
            ->findOrEmpty()
            ->toArray();

        if (!empty($info)) {
            $temp_sku_data = array_filter(array_column($info['sku_list'], 'sku_spec_format'));
            $info['spec_type'] = 'single';
            if (!empty($temp_sku_data)) {
                // 多规格
                $info['spec_type'] = 'multi';
            }
        }
        return $info;
    }


    /**
     * 下单前商品效验
     * @return array
     */
    public function checkGoods($param)
    {

        $goods_id = $param['goods_id'];
        $sku_id = $param['sku_id'];
        $goods_num = $param['num'];
        $goods_info = $this->model
            ->where([['goods_id', '=', $goods_id], ['status', '=', 1]])
            ->with([
                'goodsSku' => function (Query $query) {
                    $query->field(['goods_id', 'sku_id', 'stock']);
                },
            ])
            ->hasWhere('goodsSku', function (Query $query) use ($sku_id) {
                $query->where('sku_id', '=', $sku_id);
            })
            ->findOrEmpty()
            ->toArray();

        if (empty($goods_info)) {
            return [
                'code' => -1,
                'message' => '商品不存在或已下架'
            ];
        }
        if ($goods_info['goodsSku']['stock'] < $goods_num) {
            return [
                'code' => -1,
                'message' => '商品库存不足'
            ];
        }
        return [
            'code' => 1,
            'message' => '成功'
        ];
    }


}
