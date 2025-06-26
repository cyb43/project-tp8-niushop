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

use addon\shop\app\service\admin\goods\VirtualGoodsService;
use core\base\BaseAdminController;


/**
 * 虚拟商品控制器
 * Class VirtualGoods
 * @package addon\shop\app\adminapi\controller\goods
 */
class VirtualGoods extends BaseAdminController
{
    /**
     * 获取商品添加/编辑数据
     * @description 获取商品添加/编辑数据
     * @return \think\Response
     */
    public function init()
    {
        $data = $this->request->params([
            [ "goods_id", 0 ],
        ]);
        return success(( new VirtualGoodsService() )->getInit($data));
    }

    /**
     * 添加商品
     * @description 添加虚拟商品
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "goods_name", "" ],
            [ "sub_title", "" ],
            [ "goods_type", "" ],
            [ "goods_image", "" ],
            [ "goods_video", "" ],
            [ "goods_category", '' ],
            [ "brand_id", 0 ],
            [ "label_ids", "" ],
            [ 'service_ids', '' ],
            [ 'supplier_id', 0 ],
            [ "status", 0 ],
            [ "sort", 0 ],
            [ 'attr_ids', 0 ],
            [ 'attr_format', '' ],
            [ 'is_gift', 0 ],

            // 规格类型，single：单规格，multi：多规格
            [ 'spec_type', '' ],

            // 单规格数据
            [ "price", 0 ],
            [ "market_price", 0 ],
            [ "cost_price", 0 ],
            [ "price", 0 ],
            [ "stock", 0 ],
            [ "sku_no", '' ],
            [ "unit", "件" ],
            [ "virtual_sale_num", 0 ],
            [ "is_limit", 0 ],
            [ "limit_type", 1 ],
            [ "max_buy", 0 ],
            [ "min_buy", 0 ],

            // 多规格数据
            [ 'goods_spec_format', '' ],
            [ 'goods_sku_data', '' ],

            // 收发货设置
            [ 'virtual_auto_delivery', 0 ], // 虚拟商品是否自动发货
            [ 'virtual_receive_type', 'artificial' ], // 虚拟商品收货方式，auto：自动收货，artificial：买家确认收货，verify：到店核销
            [ 'virtual_verify_type', 0 ], // 虚拟商品核销有效期类型，0：不限，1：购买后几日有效，2：指定过期日期
            [ 'virtual_indate', 0 ], // 虚拟到期时间

            // 商品详情
            [ "goods_desc", "" ],

            [ 'member_discount', '' ], // 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
            [ 'poster_id', 0 ], // 海报id
            [ 'form_id', 0 ] // 万能表单id
        ]);

        $this->validate($data, 'addon\shop\app\validate\goods\Goods.add');
        $id = ( new VirtualGoodsService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品编辑
     * @description 编辑虚拟商品
     * @param $id  商品id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "goods_name", "" ],
            [ "sub_title", "" ],
            [ "goods_type", "" ],
            [ "goods_image", "" ],
            [ "goods_video", "" ],
            [ "goods_category", '' ],
            [ "brand_id", 0 ],
            [ "label_ids", "" ],
            [ 'service_ids', '' ],
            [ 'supplier_id', 0 ],
            [ "status", 0 ],
            [ "sort", 0 ],
            [ 'attr_ids', 0 ],
            [ 'attr_format', '' ],
            [ 'is_gift', 0 ],

            // 规格类型，single：单规格，multi：多规格
            [ 'spec_type', '' ],

            // 单规格数据
            [ "price", 0 ],
            [ "market_price", 0 ],
            [ "cost_price", 0 ],
            [ "price", 0 ],
            [ "stock", 0 ],
            [ "sku_no", '' ],
            [ "unit", "件" ],
            [ "virtual_sale_num", 0 ],
            [ "is_limit", 0 ],
            [ "limit_type", 1 ],
            [ "max_buy", 0 ],
            [ "min_buy", 0 ],

            // 多规格数据
            [ 'goods_spec_format', '' ],
            [ 'goods_sku_data', '' ],

            // 收发货设置
            [ 'virtual_auto_delivery', 0 ], // 虚拟商品是否自动发货
            [ 'virtual_receive_type', 'artificial' ], // 虚拟商品收货方式，auto：自动收货，artificial：买家确认收货，verify：到店核销
            [ 'virtual_verify_type', 0 ], // 虚拟商品核销有效期类型，0：不限，1：购买后几日有效，2：指定过期日期
            [ 'virtual_indate', 0 ], // 虚拟到期时间

            // 商品详情
            [ "goods_desc", "" ],

            [ 'member_discount', '' ], // 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
            [ 'poster_id', 0 ], // 海报id
            [ 'form_id', 0 ] // 万能表单id
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Goods.edit');
        $res = ( new VirtualGoodsService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

}
