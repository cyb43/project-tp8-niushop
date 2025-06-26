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

use core\base\BaseAdminController;
use addon\shop\app\service\admin\goods\StatisticsService;

/**
 * 商品统计控制器
 * Class Brand
 * @package addon\shop\app\adminapi\controller\goods
 */
class Statistics extends BaseAdminController
{

    /**
     * 获取商品统计基本信息
     * @description 查看商品统计基本信息
     * @return \think\Response
     */
    public function getBasic()
    {
        $data = $this->request->params([
            [ "date", "" ],
        ]);
        return success(( new StatisticsService() )->getBasic($data));
    }

    /**
     * 获取商品统计图表信息
     * @description 查看商品统计图表信息
     * @return \think\Response
     */
    public function getTrend()
    {
        $data = $this->request->params([
            [ "date", "" ],
        ]);
        return success(( new StatisticsService() )->getTrend($data));
    }

    /**
     * 获取商品排行榜信息
     * @description 查看商品排行榜信息
     * @return \think\Response
     */
    public function getRank()
    {
        $data = $this->request->params([
            [ "category_ids", []],
            [ "type", "access_num"],
            [ "date", "" ],
            [ "goods_name", "" ],
        ]);
        return success(( new StatisticsService() )->getRank($data));
    }

    /**
     * 获取商品排行榜统计类型
     * @description 获取商品排行榜统计类型
     * @return \think\Response
     */
    public function getType()
    {
        $data = $this->request->params([
        ]);
        return success(( new StatisticsService() )->getType($data));
    }

    /**
     * 同步商品统计信息
     * @description 同步商品统计信息
     * @return \think\Response
     */
    public function syncStatGoods()
    {
        $data = $this->request->params([
            [ "date", [] ],
        ]);
        return success(( new StatisticsService() )->syncStatGoods($data['date']));
    }





}
