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
use addon\shop\app\service\admin\goods\RankService;

/**
 * 商品排行榜控制器
 * Class Rank
 * @package addon\shop\app\adminapi\controller\goods
 */
class Rank extends BaseAdminController
{

    /**
     * 设置商品排行榜配置
     * @description 设置商品排行榜配置
     * @return \think\Response
     */
    public function setRankConfig()
    {
        $data = $this->request->params([
//            [ "rank_name", "" ],
            [ "rank_images", "" ],
//            [ "rank_remark", "" ],
            [ "no_color", "" ],
            [ "select_color", "" ],
            [ "select_bg_color_start", "" ],
            [ "select_bg_color_end", "" ],
        ]);
        ( new RankService() )->setGoodsRankConfig($data);
        return success('SUCCESS');
    }

    /**
     * 获取商品排行配置
     * @description 查看商品排行配置
     * @return \think\Response
     */
    public function getRankConfig()
    {
        return success(( new RankService() )->getGoodsRankConfig());
    }

    /**
     * 获取商品排行榜分页列表
     * @description 查看商品排行榜列表-分页
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "rank_type", "" ],
            [ 'order', '' ],
            [ 'sort', '' ]
        ]);
        return success(( new RankService() )->getPage($data));
    }

    /**
     * 添加商品排行榜
     * @description 添加商品排行榜
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "rank_type", '' ],
            [ "goods_source", '' ],
            [ "rule_type", '' ],
            [ "goods_json", [] ],
            [ "category_ids", [] ],
            [ "brand_ids", [] ],
            [ "label_ids", [] ],
            [ "sort", 0 ],
            [ "status", 1 ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Rank.add');
        $id = ( new RankService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 编辑商品排行榜
     * @description 编辑商品排行榜
     * @param int $id 排行榜id
     * @return \think\Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "rank_type", '' ],
            [ "goods_source", '' ],
            [ "rule_type", '' ],
            [ "goods_json", [] ],
            [ "category_ids", [] ],
            [ "brand_ids", [] ],
            [ "label_ids", [] ],
            [ "sort", 0 ],
            [ "status", 1 ],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Rank.edit');
        ( new RankService() )->edit($id, $data);
        return success('EDIT_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品排行榜详情
     * @description 查看商品排行榜详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new RankService() )->getInfo($id));
    }

    /**
     * 删除
     * @description 删除商品排行榜
     * @param int $id 排行榜id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new RankService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 商品排行榜选项列表
     * @description 商品排行榜选项列表
     * @param int $id
     * @return \think\Response
     */
    public function getOptionData()
    {
        return success(( new RankService() )->getOptionData());
    }

    /**
     * 修改榜单排序号
     * @description 修改榜单排序号
     * @return \think\Response
     */
    public function editSort()
    {
        $data = $this->request->params([
            [ "rank_id", "" ],
            [ "sort", 0 ],
        ]);
        ( new RankService() )->editSort($data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 批量删除
     * @description 批量删除商品排行榜
     * @param int $id
     * @return \think\Response
     */
    public function batchDelete()
    {
        $data = $this->request->params([
            [ "rank_id", [] ],
        ]);
        ( new RankService() )->batchDelete($data);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取排行榜分页列表（用于弹框选择）
     * @description 获取排行榜分页列表
     * @return \think\Response
     */
    public function select()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "rank_type", "" ],
            [ "verify_rank_ids", [] ],
        ]);
        return success(( new RankService() )->getSelectPage($data));
    }

    /**
     * 修改状态
     * @description 修改商品排行榜状态
     * @return \think\Response
     */
    public function modifyStatus()
    {
        $data = $this->request->params([
            [ 'rank_id', '' ],
            [ 'status', '' ],
        ]);
        ( new RankService() )->modifyStatus($data);
        return success('SUCCESS');
    }

}
