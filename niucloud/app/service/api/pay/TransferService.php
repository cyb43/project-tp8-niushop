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

namespace app\service\api\pay;

use app\dict\common\ChannelDict;
use app\service\core\member\CoreMemberService;
use app\service\core\pay\CorePayService;
use core\base\BaseApiService;

/**
 * 支付业务
 */
class TransferService extends BaseApiService
{

    public $core_pay_service;
    public function __construct()
    {
        parent::__construct();
        $this->core_pay_service = new CorePayService();
    }

    /**
     * 去支付
     * @param string $transfer_no
     * @param array $data
     * @return void
     */
    public function confirm(string $transfer_no, array $data = []){

//        $member = (new CoreMemberService())->getInfoByMemberId($this->member_id);
//        switch ($this->channel) {
//            case ChannelDict::WECHAT://公众号
//                $openid = $openid ? $openid : $member['wx_openid'] ?? '';
//                break;
//            case ChannelDict::WEAPP://微信小程序
//                $openid = $openid ? $openid : $member['weapp_openid'] ?? '';
//                break;
//        }

//        /**/return $this->core_pay_service->pay($trade_type, $trade_id, $type, $this->channel, $openid, $return_url, $quit_url, $buyer_id, $voucher, $this->member_id);
    }


}
