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

namespace app\api\controller\channel;

use app\service\api\login\LoginService;
use app\service\api\wechat\WechatAppService;
use core\base\BaseController;

class App extends BaseController
{
    public function wechatLogin()
    {
        $data = $this->request->params([
            [ 'code', '' ],
        ]);
        $wechat_app_service = new WechatAppService();
        return success($wechat_app_service->loginByCode($data[ 'code' ]));
    }

    public function register()
    {
        $data = $this->request->params([
            [ 'register_type', '' ],
        ]);
        switch ($data['register_type']) {
            case 'wechat':
                return $this->wechatRegister();
        }
        return fail("不支持的注册方式");
    }

    public function wechatRegister() {
        $data = $this->request->params([
            [ 'openid', '' ],
            [ 'unionid', '' ],
            [ 'nickname', '' ],
            [ 'avatar', '' ],
            [ 'mobile_code', '' ],
            [ 'mobile', '' ],
        ]);

        //参数验证
        $this->validate($data, [
            'mobile' => 'mobile'
        ]);

        // 校验手机验证码（电脑端扫码）
        ( new LoginService() )->checkMobileCode($data[ 'mobile' ]);

        $wechat_app_service = new WechatAppService();
        return success($wechat_app_service->register($data[ 'openid' ], $data[ 'mobile' ], $data[ 'nickname' ], $data[ 'avatar' ], $data[ 'avatar' ]));
    }
}
