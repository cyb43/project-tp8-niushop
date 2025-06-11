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

namespace addon\shop\app\api\controller\exchange;

use addon\shop\app\service\api\marketing\pointexchange\OrderCreateService;
use core\base\BaseApiController;
use think\Response;


class OrderCreate extends BaseApiController
{

    /**
     * 计算
     * @return Response
     */
    public function calculate()
    {
        $data = $this->request->params([
            ['order_key', []],
            ['sku_data', []],
            ['delivery', []],
            ['discount', []],//优惠
        ]);
        return success('SUCCESS', (new OrderCreateService())->calculate($data));
    }

    /**
     * 订单创建
     * @return Response
     */
    public function create()
    {
        $data = $this->request->params([
            ['order_key', []],
            ['delivery', [] ],
            ['member_remark', ''],//买家留言
            ['invoice', []],//发票
        ]);
        return success('SUCCESS', (new OrderCreateService())->create($data));
    }

    /**
     * 获取自提点
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStore()
    {
        $data = $this->request->params([
            ['latlng', []],
        ]);
        return success('SUCCESS', (new OrderCreateService())->getStore($data['latlng']));
    }
}
