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

namespace app\adminapi\controller\wechat;

use app\service\admin\wechat\WechatMenuService;
use core\base\BaseAdminController;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use think\Response;

/**
 * 微信公众号管理菜单
 */
class Menu extends BaseAdminController
{

    /**
     * 获取微信菜单
     * @description 获取微信菜单
     * @return Response
     */
    public function info()
    {
        $wechat_menu_service = new WechatMenuService();
        return success($wechat_menu_service->getInfo());
    }

    /**
     * 更新微信菜单
     * @description 更新微信菜单
     * @return Response
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function edit()
    {
        $wechat_menu_service = new WechatMenuService();
        $data = $this->request->params([
            ['button', []]
        ]);
        $wechat_menu_service->edit($data['button']);
        return success('EDIT_SUCCESS');
    }
}
