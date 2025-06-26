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

namespace addon\shop\app\api\controller\goods;

use addon\shop\app\service\api\goods\RankService;
use core\base\BaseApiController;


/**
 * 商品排行榜控制器
 * Class Rank
 */
class Rank extends BaseApiController
{
    /**
     * 获取商品排行配置
     * @return \think\Response
     */
    public function getRankConfig()
    {
        return success(( new RankService() )->getGoodsRankConfig());
    }

    /**
     * 获取榜单列表
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "name", "" ],
        ]);
        return success(( new RankService() )->getPage($data));
    }

    /**
     * 获取商品列表
     * @return \think\Response
     */
    public function goods()
    {
        $data = $this->request->params([
            [ 'rank_id', 0 ],
            [ 'page', 0 ],
            [ 'limit', 0 ],
        ]);
        return success(( new RankService() )->getGoodsPage($data));
    }

    /**
     * 获取商品排行榜列表供组件调用
     * @return \think\Response
     */
    public function components()
    {
        $data = $this->request->params([
            [ 'rank_id', 0 ],
            [ 'limit', 3 ],
        ]);

        return success(( new RankService() )->getRankComponents($data));
    }

}
