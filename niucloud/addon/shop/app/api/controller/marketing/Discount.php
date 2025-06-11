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

namespace addon\shop\app\api\controller\marketing;

use addon\shop\app\service\api\marketing\DiscountService;
use core\base\BaseApiController;
use think\Response;

class Discount extends BaseApiController
{
    /**
     * 轮播图配置
     * @return Response
     */
    public function config()
    {
        return success(( new DiscountService() )->getDiscountBannerConfig());
    }

    /**
     * 获取商品列表
     * @return \think\Response
     */
    public function goods()
    {
        $data = $this->request->params([
            [ 'keyword', '' ], // 搜索关键词
            [ "goods_category", "" ], // 商品分类
            [ "brand_id", "" ], // 品牌id
            [ "label_ids", "" ], // 商品标签
            [ "start_price", "" ], // 价格开始区间
            [ "end_price", "" ], // 价格结束区间
            [ 'order', '' ], // 排序方式（综合：空，销量：sale_num，价格：price）
            [ 'sort', 'desc' ], // 升序：asc，降序：desc
            [ 'discount_id', 0 ],
        ]);

        return success(( new DiscountService() )->getGoodsPage($data));
    }

    /**
     * 获取限时折扣列表
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ 'active_name', '' ], // 搜索关键词
            [ 'limit', 10 ],
        ]);

        return success(( new DiscountService() )->getList($data));
    }

}
