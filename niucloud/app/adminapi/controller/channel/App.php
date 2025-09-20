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

namespace app\adminapi\controller\channel;

use app\service\admin\channel\AppService;
use app\service\admin\channel\H5Service;
use core\base\BaseAdminController;
use think\Response;

/**
 * app端配置
 * Class H5
 * @package app\adminapi\controller\channel
 */
class App extends BaseAdminController
{
    /**
     * 获取APP配置信息
     * @description 获取APP配置信息
     * @return Response
     */
    public function get()
    {
        return success((new AppService())->getConfig());
    }

    /**
     * 设置APP配置信息
     * @description 设置APP配置信息
     * @return Response
     */
    public function set()
    {
        $data = $this->request->params([
            ['wechat_app_id', ""],
            ['wechat_app_secret', ""],
        ]);
        (new AppService())->setConfig($data);
        return success('SET_SUCCESS');
    }
}
