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
use addon\shop\app\service\admin\goods\EvaluateService;


/**
 * 商品评价控制器
 * Class Evaluate
 * @package addon\shop\app\adminapi\controller\goods
 */
class Evaluate extends BaseAdminController
{
    /**
     * 获取商品评价列表
     * @description 查看商品评价-分页
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ 'goods_name', '' ]
        ]);
        return success(( new EvaluateService() )->getPage($data));
    }

    /**
     * 商品评价详情
     * @param int $id
     * @description 查看商品评价详情
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new EvaluateService() )->getInfo($id));
    }

    /**
     * 添加商品评价
     * @description 添加商品评价
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "goods_id", 0 ],
            [ "member_head", "" ],
            [ "member_name", "" ],
            [ "content", "" ],
            [ "images", "" ],
            [ "is_anonymous", 0 ],
            [ "scores", 0 ],
            [ "is_show", 0 ],
            [ "create_time", 0 ]
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Evaluate.add');
        $id = ( new EvaluateService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 商品评价删除
     * @description 删除商品评价
     * @param $id  商品评价id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new EvaluateService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 评价审核
     * @description 审核评价
     * @param $id
     */
    public function evaluateAudit($id)
    {
        $data = $this->request->params([
            [ "is_audit", 0 ],
        ]);
        ( new EvaluateService() )->audit($id, $data);

        return success('SUCCESS');
    }

    /**
     * 审核通过
     * @description 审核-通过评价
     * @param $id
     * @return \think\Response
     */
    public function adopt($id)
    {
        ( new EvaluateService() )->auditAdopt($id);

        return success('SUCCESS');
    }

    /**
     * 审核拒绝
     * @description 审核-拒绝通过评价
     * @param $id
     * @return \think\Response
     */
    public function refuse($id)
    {
        ( new EvaluateService() )->auditRefuse($id);

        return success('SUCCESS');
    }

    /**
     * 评价回复
     * @description 回复评价
     * @param $id
     */
    public function evaluateReply($id)
    {
        $data = $this->request->params([
            [ "explain_first", '' ],
        ]);
        ( new EvaluateService() )->reply($id, $data);

        return success('SUCCESS');
    }

    /**
     * 置顶
     * @description 置顶评价
     * @param $id
     */
    public function topping($id)
    {
        ( new EvaluateService() )->topping($id);

        return success('SUCCESS');
    }

    /**
     * 取消置顶
     * @description 取消置顶评价
     * @param $id
     */
    public function cancelTopping($id)
    {
        ( new EvaluateService() )->cancelTopping($id);

        return success('SUCCESS');
    }
}
