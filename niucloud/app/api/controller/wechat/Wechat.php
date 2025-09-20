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

namespace app\api\controller\wechat;

use app\service\api\login\LoginService;
use app\service\api\wechat\WechatAuthService;
use core\base\BaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;

class Wechat extends BaseController
{

    //todo  csrf  验证也需要
    /**
     * 获取跳转获取code
     * @return Response
     */
    public function getCodeUrl()
    {
        $data = $this->request->params([
            [ 'url', '' ],
            [ 'scopes', '' ]
        ]);
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->authorization($data[ 'url' ], $data[ 'scopes' ]));
    }

    /**
     * code获取微信信息
     * @return Response
     */
    public function getWechatUser()
    {
        $data = $this->request->params([
            [ 'code', '' ],
        ]);
        $wechat_auth_service = new WechatAuthService();
        $data = $wechat_auth_service->userFromCode($data[ 'code' ]);
        return success([ 'data' => json_encode($data) ]);
    }

    /**
     * 授权信息登录
     * @return Response
     */
    public function wechatLogin()
    {
        $data = $this->request->params([
            [ 'data', '' ],
        ]);
        $wechat_auth_service = new WechatAuthService();
        [ $avatar, $nickname, $openid, $unionid ] = json_decode($data[ 'data' ], true);
        return success($wechat_auth_service->login($openid, $nickname, $avatar, $unionid));
    }

    /**
     * 授权code登录
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function login()
    {
        $data = $this->request->params([
            [ 'code', '' ],
        ]);
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->loginByCode($data[ 'code' ]));
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
            [ 'mobile', '' ],
        ]);
        //参数验证
        $this->validate($data, [
            'mobile' => 'mobile'
        ]);

        // 校验手机验证码（电脑端扫码）
        ( new LoginService() )->checkMobileCode($data[ 'mobile' ]);

        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->register($data[ 'openid' ], $data[ 'mobile' ], wx_unionid: $data[ 'unionid' ]));
    }

    /**
     * 同步
     * @return Response
     */
    public function sync()
    {
        $data = $this->request->params([
            [ 'code', '' ],
        ]);
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->sync($data[ 'code' ]));
    }

    /**
     * 获取jssdk config
     * @return Response
     */
    public function jssdkConfig()
    {
        $data = $this->request->params([
            [ 'url', '' ],
        ]);
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->jssdkConfig($data[ 'url' ]));
    }

    /**
     * 扫码登录
     * @return Response
     */
    public function scanLogin()
    {
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->scanLogin());
    }

    /**
     * 更新openid
     * @return Response
     */
    public function updateOpenid()
    {
        $data = $this->request->params([ [ 'code', '' ] ]);
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->updateOpenid($data[ 'code' ]));
    }

    /**
     * 更新openid
     * @return Response
     */
    public function updateOpenidByH5()
    {
        $data = $this->request->params([ [ 'wx_openid', '' ] ]);
        $wechat_auth_service = new WechatAuthService();
        return success($wechat_auth_service->updateOpenidByH5($data[ 'wx_openid' ]));
    }
}
