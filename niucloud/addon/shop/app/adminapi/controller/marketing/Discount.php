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

namespace addon\shop\app\adminapi\controller\marketing;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\service\admin\marketing\DiscountService;
use core\base\BaseAdminController;


/**
 * 限时折扣控制器
 * Class Discount
 * @package addon\shop\app\adminapi\controller\marketing
 */
class Discount extends BaseAdminController
{

    public function lists()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "status", "" ],
        ]);
        return success(( new DiscountService() )->getPage($data));
    }

    /**
     * 详情-基础信息
     * @param int $discount_id
     * @return \think\Response
     */
    public function info(int $discount_id)
    {
        return success(( new DiscountService() )->getInfo($discount_id));
    }

    /**
     * 详情
     * @param int $discount_id
     * @return \think\Response
     */
    public function detail(int $discount_id)
    {
        return success(( new DiscountService() )->getDetail($discount_id));
    }

    /**
     * 添加限时折扣
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "name", '' ],
            [ "remark", "" ],
            [ "start_time", '' ],
            [ "end_time", '' ],
            [ "goods_list", [] ],
        ]);

        $id = ( new DiscountService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 限时折扣编辑
     * @param int $discount_id
     * @return \think\Response
     */
    public function edit(int $discount_id)
    {
        $data = $this->request->params([
            [ "name", '' ],
            [ "remark", "" ],
            [ "start_time", '' ],
            [ "end_time", '' ],
            [ "goods_list", [] ],
        ]);

        ( new DiscountService() )->edit($discount_id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 限时折扣商品校验
     * @return \think\Response
     */
    public function checkGoods()
    {
        $data = $this->request->params([
            [ "start_time", '' ],//开始时间
            [ "end_time", '' ],//结束时间
            [ "goods_ids", [] ],//校验的商品id
            [ "discount_id", 0 ],//限时折扣活动id
        ]);

        return success('SUCCESS', data:( new DiscountService() )->checkGoodsData($data));
    }

    /**
     * 获取活动状态
     * @return \think\Response
     */
    public function status()
    {
        return success(ActiveDict::getStatus());
    }

    /**
     * 删除活动
     * @param int $discount_id
     * @return \think\Response
     */
    public function del(int $discount_id)
    {
        ( new DiscountService() )->del($discount_id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 活动关闭
     * @param int $discount_id
     * @return \think\Response
     */
    public function close(int $discount_id)
    {
        ( new DiscountService() )->discountClose($discount_id);
        return success('SUCCESS');
    }

    /**
     * 参与订单
     * @param int $active_id
     * @return \think\Response
     */
    public function order(int $active_id)
    {
        $data = $this->request->params([
            [ 'status', '' ],
            [ 'create_time', [] ],
            [ 'pay_time', [] ],
            [ 'search_type', 'order_no' ],
            [ 'search_name', '' ],
        ]);

        return success(( new DiscountService() )->order($active_id, $data));
    }

    /**
     * 参与会员
     * @param int $active_id
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function member(int $active_id)
    {
        $data = $this->request->params([
            [ 'keyword', '' ],
        ]);

        return success(( new DiscountService() )->member($active_id, $data));
    }

    /**
     * 参与商品
     * @param int $active_id
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function goods(int $active_id)
    {
        $data = $this->request->params([
            [ 'keyword', '' ],
        ]);

        return success(( new DiscountService() )->goods($active_id, $data));
    }

    /**
     * 获取轮播图配置
     * @return void
     */
    public function banner()
    {
        return success(( new DiscountService() )->getDiscountBannerConfig());
    }

    /**
     * 设置轮播图配置
     * @return \think\Response
     */
    public function setBanner()
    {
        $data = $this->request->params([
            [ 'list', [] ],
        ]);
        $res = ( new DiscountService() )->setConfig('SHOP_DISCOUNT_BANNER_CONFIG', $data[ 'list' ]);
        return success('SUCCESS', $res);
    }

}
