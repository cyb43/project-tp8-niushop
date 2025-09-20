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

namespace addon\shop\app\adminapi\controller\goods;

use addon\shop\app\service\admin\goods\ConfigService;
use core\base\BaseAdminController;
use addon\shop\app\service\admin\goods\GoodsService;


/**
 * 商品控制器
 * Class Goods
 * @description 商品
 * @package addon\shop\app\adminapi\controller\goods
 */
class Goods extends BaseAdminController
{

    /**
     * 获取商品列表
     * @description 查看商品列表
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            ["goods_name", ""],
            ["goods_category", ''],
            ["goods_type", ""],
            ["brand_id", ""],
            ["label_ids", ""],
            ["start_sale_num", ""],
            ["end_sale_num", ""],
            ["start_price", ""],
            ["end_price", ""],
            ["status", ""],
            ['order', ''],
            ['sort', '']
        ]);
        return success((new GoodsService())->getPage($data));
    }

    /**
     * 商品详情
     * @description 查看商品详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success((new GoodsService())->getInfo($id));
    }

    /**
     * 获取商品添加/编辑数据
     * @description 获取初始化数据
     * @return \think\Response
     */
    public function init()
    {
        $data = $this->request->params([
            ["goods_id", 0],
        ]);
        return success((new GoodsService())->getInit($data));
    }

    /**
     * 添加商品
     * @description 添加商品
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            ["goods_name", ""],
            ["sub_title", ""],
            ["goods_type", ""],
            ["goods_image", ""],
            ["goods_video", ""],
            ["goods_category", ''],
            ["brand_id", 0],
            ["label_ids", ""],
            ['service_ids', ''],
            ['supplier_id', 0],
            ["status", 0],
            ["sort", 0],
            ['attr_ids', []],
            ['attr_format', ''],
            ['is_gift', 0],

            // 规格类型，single：单规格，multi：多规格
            ['spec_type', ''],

            // 单规格数据
            ["price", 0],
            ["market_price", 0],
            ["cost_price", 0],
            ["price", 0],
            ["weight", 0],
            ["volume", 0],
            ["stock", 0],
            ["sku_no", ''],
            ["unit", "件"],
            ["virtual_sale_num", 0],
            ["is_limit", 0],
            ["limit_type", 1],
            ["max_buy", 0],
            ["min_buy", 0],

            // 多规格数据
            ['goods_spec_format', ''],
            ['goods_sku_data', ''],

            // 配送设置
            ["delivery_type", ""],
            ["is_free_shipping", 0],
            ['fee_type', ''],
            ['delivery_money', 0],
            ["delivery_template_id", 0],

            // 商品详情
            ["goods_desc", "", false],

            ['member_discount', ''], // 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
            ['poster_id', 0], // 海报id
            ['form_id', 0], // 万能表单id
            ['diy_detail_id', 0] // 自定义详情id
        ]);

        $this->validate($data, 'addon\shop\app\validate\goods\Goods.add');
        $id = (new GoodsService())->add($data);
        return success('ADD_SUCCESS', ['id' => $id]);
    }

    /**
     * 商品编辑
     * @description 编辑商品
     * @param $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            ["goods_name", ""],
            ["sub_title", ""],
            ["goods_type", ""],
            ["goods_image", ""],
            ["goods_video", ""],
            ["goods_category", ''],
            ["brand_id", 0],
            ["label_ids", ""],
            ['service_ids', ''],
            ['supplier_id', 0],
            ["status", 0],
            ["sort", 0],
            ['attr_ids', []],
            ['attr_format', ''],
            ['is_gift', 0],

            // 规格类型，single：单规格，multi：多规格
            ['spec_type', ''],

            // 单规格数据
            ["price", 0],
            ["market_price", 0],
            ["cost_price", 0],
            ["price", 0],
            ["weight", 0],
            ["volume", 0],
            ["stock", 0],
            ["sku_no", ''],
            ["unit", "件"],
            ["virtual_sale_num", 0],
            ["is_limit", 0],
            ["limit_type", 1],
            ["max_buy", 0],
            ["min_buy", 0],

            // 多规格数据
            ['goods_spec_format', ''],
            ['goods_sku_data', ''],

            // 配送设置
            ["delivery_type", ""],
            ["is_free_shipping", 0],
            ['fee_type', ''],
            ['delivery_money', 0],
            ["delivery_template_id", 0],

            // 商品详情
            ["goods_desc", "", false],

            ['member_discount', ''], // 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price

            ['poster_id', 0], // 海报id
            ['form_id', 0], // 万能表单id
            ['diy_detail_id', 0] // 自定义详情id
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Goods.edit');
        $res = (new GoodsService())->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商品删除
     * @description 删除商品
     * @return \think\Response
     */
    public function del()
    {
        $data = $this->request->params([
            ['is_all', 0],
            ['where', []],
            ['goods_ids', ''],//is_all == 1时 goods_ids 为未选中的ID
        ]);
        (new GoodsService())->del($data);
        return success('DELETE_SUCCESS');
    }

    /**
     * 商品恢复
     * @description 恢复商品
     * @return \think\Response
     */
    public function recycle()
    {
        $data = $this->request->params([
            ['goods_ids', ''],
        ]);
        (new GoodsService())->recycle($data['goods_ids']);
        return success('SUCCESS');
    }

    /**
     * 获取商品类型
     * @description 获取商品类型
     * @return \think\Response
     */
    public function type()
    {
        return success((new GoodsService())->getType());
    }

    /**
     * 修改商品排序号
     * @description 修改商品排序号
     * @return \think\Response
     */
    public function editSort()
    {
        $data = $this->request->params([
            ['goods_id', ''],
            ['sort', ''],
        ]);
        (new GoodsService())->editSort($data);
        return success('SUCCESS');
    }

    /**
     * 修改商品上下架状态
     * @description 修改商品上下架状态
     * @return \think\Response
     */
    public function editStatus()
    {
        $data = $this->request->params([
            ['is_all', 0],
            ['where', []],
            ['goods_ids', ''],
            ['status', ''],
        ]);
        (new GoodsService())->editStatus($data);
        return success('SUCCESS');
    }

    /**
     * 修改商品上下架状态（单商品）
     * @description 修改商品上下架状态（单商品）
     * @return \think\Response
     */
    public function editSingleStatus()
    {
        $data = $this->request->params([
            ['goods_id', 0],
            ['status', 0],
        ]);
        (new GoodsService())->editSingleStatus($data);
        return success('SUCCESS');
    }

    /**
     * 复制商品
     * @description 复制商品
     * @param int $goods_id
     * @return \think\Response
     */
    public function copy(int $goods_id)
    {
        (new GoodsService())->copy($goods_id);
        return success('SUCCESS');
    }

    /**
     * 获取回收站商品分页列表
     * @description 查看回收站商品分页列表
     * @return \think\Response
     */
    public function recyclePages()
    {
        $data = $this->request->params([
            ["goods_name", ""],
            ["goods_category", ''],
            ["goods_type", ""],
            ['order', ''],
            ['sort', '']
        ]);
        return success((new GoodsService())->getRecyclePage($data));
    }

    /**
     * 商品选择分页列表（按照单商品）
     * @description 获取商品选择分页列表（按照单商品）
     * @return \think\Response
     */
    public function select()
    {
        $data = $this->request->params([
            ['keyword', ''], // 搜索关键词
            ["goods_category", ""], // 商品分类
            ["goods_type", ""], // 商品分类
            ["select_type", "all"], // 商品分类
            ["start_price", ""],
            ["end_price", ""],
            ['goods_ids', []], // 已选商品id集合
            ['sku_ids', []], // 已选商品规格id集合
            ['verify_goods_ids', []], // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
            ['verify_sku_ids', []], // 检测商品规格id集合是否存在，移除不存在的商品规格id，纠正数据准确性
            ['is_gift', 0] // 是否查询赠品(0:不查赠品 1:查询赠品)
        ]);

        return success((new GoodsService())->getSelectPage($data));
    }

    /**
     * 商品选择分页列表(带sku)
     * @return \think\Response
     */
    public function selectGoodsSku()
    {
        $data = $this->request->params([
            ['keyword', ''], // 搜索关键词
            ["goods_category", ""], // 商品分类
            ["goods_type", ""], // 商品分类
            ['goods_ids', ''], // 已选商品id集合
            ['verify_goods_ids', ''], // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
            ['verify_sku_ids', []], // 检测商品规格id集合是否存在，移除不存在的商品规格id，纠正数据准确性
            ['is_gift', 0] // 是否查询赠品(0:不查赠品 1:查询赠品)
        ]);

        return success((new GoodsService())->getSelectSku($data));
    }

    /**
     * 商品选择分页列表（代客下单专用）
     * @description 获取商品选择分页列表（代客下单专用）
     * @return \think\Response
     */
    public function buyGoodsSelect()
    {
        $data = $this->request->params([
            ['keyword', ''], // 搜索关键词
            ["goods_category", ""], // 商品分类
            ["goods_type", ""], // 商品分类
            ["member_id", 0], // 会员id
        ]);

        return success((new GoodsService())->getBuyGoodsSelect($data));
    }

    /**
     * 已选商品分页列表（代客下单专用）
     * @description 获取已选商品分页列表（代客下单专用）
     * @return \think\Response
     */
    public function buyGoodsSelected()
    {
        $data = $this->request->params([
            ['sku_ids', []],
            ['member_id', 0],
        ]);

        return success((new GoodsService())->getBuyGoodsSelected($data));
    }

    /**
     * 获取商品规格信息，切换规格（代客下单专用）
     * @description 获取商品规格信息，切换规格（代客下单专用）
     */
    public function buySkuSelect()
    {
        $data = $this->request->params([
            ['sku_id', 0],
            ['member_id', 0],
        ]);
        return success((new GoodsService())->getBuySkuSelect($data));
    }

    /**
     * 查询商品SKU规格列表
     * @description 查询商品SKU规格列表
     * @return \think\Response
     */
    public function sku()
    {
        $data = $this->request->params([
            ['goods_id', '']
        ]);

        return success((new GoodsService())->getSkuList($data));
    }

    /**
     * 编辑商品规格列表库存
     * @description 编辑商品规格列表库存
     * @return \think\Response
     */
    public function editGoodsListStock()
    {
        $data = $this->request->params([
            ['goods_id', 0],
            ['sku_list', '']
        ]);
        (new GoodsService())->editGoodsListStock($data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 编辑商品规格列表价格
     * @description 编辑商品规格列表库存
     * @return \think\Response
     */
    public function editGoodsListPrice()
    {
        $data = $this->request->params([
            ['goods_id', 0],
            ['sku_list', '']
        ]);
        (new GoodsService())->editGoodsListPrice($data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 编辑商品规格列表会员价格
     * @description 编辑商品规格列表会员价格
     * @return \think\Response
     */
    public function editGoodsListMemberPrice()
    {
        $data = $this->request->params([
            ['goods_id', 0],
            ['member_discount', ''], // 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
            ['sku_list', '']
        ]);
        (new GoodsService())->editGoodsListMemberPrice($data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 查询商品参与营销活动的数量
     * @description 查询商品参与营销活动的数量
     * @return \think\Response
     */
    public function getActiveGoodsCount()
    {
        $data = $this->request->params([
            ['goods_id', '']
        ]);

        return success(data: (new GoodsService())->getActiveGoodsCount($data['goods_id']));
    }

    /**
     * 批量设置商品
     * @description 批量设置商品
     * @return \think\Response
     */
    public function batchSet()
    {
        $data = $this->request->params([
            ['is_all', 0],
            ['goods_ids', []],
            ['set_value', []],
            ['set_type', ''],
            ['where', []],

        ]);
        (new GoodsService())->batchSet($data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 获取批量设置类型
     * @description 获取批量设置类型
     * @return \think\Response
     */
    public function getBatchSetDict()
    {
        return success((new GoodsService())->getBatchSetDict());
    }

    /**
     * 验证商品编码是否重复
     * @description 验证商品编码是否重复
     * @return \think\Response
     */
    public function verifySkuNo()
    {
        $params = $this->request->params(
            [
                ['sku_no', ''],
                ['goods_id', 0]
            ]);
        (new ConfigService())->verifySkuNo($params);
        return success('SUCCESS');
    }

}
