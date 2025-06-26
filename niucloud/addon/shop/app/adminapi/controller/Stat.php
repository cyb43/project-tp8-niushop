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

namespace addon\shop\app\adminapi\controller;

use addon\shop\app\service\admin\goods\GoodsService;
use addon\shop\app\service\admin\StatService;
use addon\shop\app\service\admin\order\OrderService;
use Carbon\Carbon;
use core\base\BaseAdminController;

class Stat extends BaseAdminController
{
    /**
     * 总计
     * @description 查看统计数据-总计
     * @return \think\Response
     */
    public function total() {
        return success(data: (new StatService())->getStat());
    }

    /**
     * 今日
     * @description 查看统计数据-今日
     * @return void
     */
    public function today() {
        return success(data: (new StatService())->getStat(date('Y-m-d', time())) );
    }

    /**
     * 昨日
     * @description 查看统计数据-昨日
     * @return void
     */
    public function yesterday() {
        $yesterday =  Carbon::yesterday();
        return success(data: (new StatService())->getStat(date('Y-m-d', $yesterday->getTimestamp())) );
    }

    /**
     * 获取阶段统计数据
     * @description 查看统计数据-阶段统计数据
     * @return \think\Response
     */
    public function stat() {
        $data = $this->request->params([
            ['start_date', date('Y-m-d', strtotime('-6 day')) ],
            ['end_date', date('Y-m-d', strtotime('+1 day'))]
        ]);
        return success(data: (new StatService())->getStatData($data['start_date'], $data['end_date']));
    }

    /**
     * 获取统计数据按时段
     * @description 查看统计数据-统计数据按时段
     * @return \think\Response
     */
    public function hourStat() {
        $data = $this->request->params([
            ['date', date('Y-m-d', time()) ],
        ]);
        return success(data: (new StatService())->getHourStatData($data['date']));
    }

    /**
     * 订单统计
     * @description 查看统计数据-订单
     * @return \think\Response
     */
    public function order() {
        return success(data: (new OrderService())->getOrderCount());
    }

    /**
     * @description 查看统计数据-商品
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function goods()
    {
        return success(data: (new GoodsService())->getGoodsCount());
    }
}
