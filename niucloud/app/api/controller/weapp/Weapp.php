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

namespace app\api\controller\weapp;

use app\service\api\notice\NoticeService;
use app\service\api\weapp\WeappAuthService;
use app\service\api\weapp\WeappDeliveryService;
use core\base\BaseApiController;
use think\Response;

class Weapp extends BaseApiController
{

    /**
     * 授权登录
     * @return Response
     */
    public function login()
    {
        $data = $this->request->params([
            [ 'code', '' ],
            [ 'nickname', '' ],
            [ 'headimg', '' ],
            [ 'mobile', '' ],
            [ 'mobile_code', '' ]
        ]);
        $weapp_auth_service = new WeappAuthService();
        return success($weapp_auth_service->login($data));
    }

    /**
     * 注册
     * @return Response
     */
    public function register()
    {
        $data = $this->request->params([
            [ 'openid', '' ],
            [ 'unionid', '' ],
            [ 'mobile_code', '' ],
            [ 'mobile', '' ],
        ]);

        $weapp_auth_service = new WeappAuthService();
        return success($weapp_auth_service->register($data[ 'openid' ], $data[ 'mobile' ], $data[ 'mobile_code' ], $data[ 'unionid' ]));
    }

    public function subscribeMessage()
    {
        $data = $this->request->params([ [ 'keys', '' ] ]);
        return success(( new NoticeService() )->getWeappNoticeTemplateId($data[ 'keys' ]));
    }

    /**
     * 查询小程序是否已开通发货信息管理服务
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getIsTradeManaged()
    {
        try {
            $wechat_template_service = new WeappDeliveryService();
            $result = $wechat_template_service->getIsTradeManaged();
            if ($result) {
                return success([ 'is_trade_managed' => true ]);
            }
        } catch (\Exception $e) {
        }
        return success([ 'is_trade_managed' => false ]);
    }

    /**
     * 通过外部交易号获取消息跳转路径
     */
    public function getMsgJumpPath()
    {
        $data = $this->request->params([
            [ 'out_trade_no', '' ],
        ]);

        $wechat_template_service = new WeappDeliveryService();
        $result = $wechat_template_service->getMsgJumpPath($data[ 'out_trade_no' ]);
        return success([ 'path' => $result ]);
    }

    /**
     * 更新openid
     * @return Response
     */
    public function updateOpenid()
    {
        $data = $this->request->params([ [ 'code', '' ] ]);
        $weapp_auth_service = new WeappAuthService();
        return success($weapp_auth_service->updateOpenid($data[ 'code' ]));
    }

}
