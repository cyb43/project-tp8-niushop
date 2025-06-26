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

namespace app\adminapi\controller\pay;

use app\service\admin\pay\PayService;
use core\base\BaseAdminController;

/**
 * 支付管理
 * Class Pay
 * @description 支付管理
 * @package app\adminapi\controller\pay
 */
class Pay extends BaseAdminController
{
    /**
     * 待审核支付记录
     * @description 待审核支付记录
     * @return mixed
     */
    public function audit(){
        $data = $this->request->params([
            ['create_time', []],
            ['out_trade_no', ''],
            ['status', '']
        ]);
        return success(data: (new PayService())->getAuditPage($data));
    }

    /**
     * 查询详情
     * @description 查询详情
     * @param int $id
     * @return \think\Response
     */
    public function detail(int $id){
        return success(data: (new PayService())->getDetail($id));
    }

    /**
     * 支付审核通过
     * @description 支付审核通过
     * @param string $out_trade_no
     * @return \think\Response
     */
    public function pass(string $out_trade_no){
        return success(data: (new PayService())->pass($out_trade_no));
    }

    /**
     * 审核拒绝
     * @description 审核拒绝
     * @param string $out_trade_no
     */
    public function refuse(string $out_trade_no){
        $reason = input('reason', '');
        return success(data: (new PayService())->refuse($out_trade_no, $reason));
    }

    /**
     *  去支付
     * @description 去支付
     * @return \think\Response
     */
    public function pay()
    {
        $data = $this->request->params([
            ['type', ''],
            ['trade_type', ''],//业务类型
            ['trade_id', ''],//业务id
            ['quit_url', ''],
            ['buyer_id', ''],
            ['return_url', ''],
            ['voucher', ''],
            ['openid', '']
        ]);

        return success('SUCCESS',(new PayService())->pay($data['type'], $data['trade_type'], $data['trade_id'], $data['return_url'], $data['quit_url'], $data['buyer_id'], $data['voucher'], $data['openid']));
    }

    /**
     * 支付信息
     * @description 支付信息
     * @param $trade_type
     * @param $trade_id
     * @return \think\Response
     */
    public function info($trade_type, $trade_id)
    {
        return success((new PayService())->getInfoByTrade($trade_type, $trade_id));
    }

    /**
     * 找朋友帮忙付支付信息
     * @description 找朋友帮忙付支付信息
     * @param $trade_type
     * @param $trade_id
     * @param $channel
     * @return \think\Response
     */
    public function friendspayInfo($trade_type, $trade_id, $channel)
    {
        return success(data:(new PayService())->getFriendspayInfoByTrade($trade_type, $trade_id, $channel));
    }

    /**
     * 支付方式列表
     * @description 支付方式列表
     * @return \think\Response
     */
    public function payTypeList()
    {
        return success(data:(new PayService())->getPayTypeList());
    }
}