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

namespace app\adminapi\controller\applet;

use app\service\admin\applet\AppletVersionSiteService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 站点小程序版本升级下载控制器
 * @description 站点小程序版本升级下载
 */
class SiteVersion extends BaseAdminController
{
    /**
     * 列表
     * @description 列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
        ]);
        return success((new AppletVersionSiteService())->getPage($data));
    }

    /**
     * 详情
     * @description 详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success((new AppletVersionSiteService())->getInfo($id));
    }

    /**
     * 查询最后一个下载或升级的版本
     * @description 查询最后一个下载或升级的版本
     * @param string $type
     * @return Response
     */
    public function getLastVersion(string $type)
    {
        return success((new AppletVersionSiteService())->getLastVersion($type));
    }

    /**
     * 查看可升级的最高版本
     * @description 查看可升级的最高版本
     * @param string $type
     * @return Response
     */
    public function getUpgradeVersion(string $type)
    {
        return success((new AppletVersionSiteService())->getUpgradeVersion($type));
    }

}
