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

namespace app\adminapi\controller\addon;

use app\dict\addon\AddonDict;
use app\service\admin\addon\AddonService;
use app\service\core\addon\CoreAddonService;
use core\base\BaseAdminController;
use think\Response;


/**
 * 插件管理
 * Class Addon
 * @description 插件管理
 * @package app\adminapi\controller\addon
 */
class Addon extends BaseAdminController
{

    /**
     * 插件初始化
     * @description 插件初始化
     * @return Response
     */
    public function init()
    {
        return success((new CoreAddonService())->getInitList());
    }

    /**
     * 获取已下载插架
     * @description 获取已下载插架
     */
    public function getLocalAddonList()
    {
        return success((new CoreAddonService())->getLocalAddonList());
    }

    /**
     * 安装插件
     * @description 安装插件
     * @param string $addon
     */
    public function install($addon)
    {
        return success((new AddonService())->install($addon));
    }

    /**
     * 云安装插件
     * @description 云安装插件
     * @param $addon
     * @return Response
     */
    public function cloudInstall($addon)
    {
        return success(data:(new AddonService())->cloudInstall($addon));
    }

    /**
     * 获取安装任务
     * @description 获取安装任务
     * @return Response
     */
    public function getInstallTask() {
        return success(data:(new AddonService())->getInstallTask());
    }

    /**
     * 获取云安装日志
     * @description 获取云安装日志
     * @param $addon
     * @return mixed
     */
    public function cloudInstallLog($addon) {
        return success(data:(new AddonService())->cloudInstallLog($addon));
    }

    /**
     * 插件安装环境检测
     * @description 插件安装环境检测
     * @param $addon
     * @return Response
     */
    public function installCheck($addon)
    {
        return success(data:(new AddonService())->installCheck($addon));
    }

    /**
     * 取消安装
     * @description 取消安装
     * @param $addon
     * @return mixed
     */
    public function cancleInstall($addon)
    {
        return success(data:(new AddonService())->cancleInstall($addon));
    }

    /**
     * 卸载插件
     * @description 卸载插件
     * @param string $addon
     */
    public function uninstall($addon)
    {
        (new AddonService())->uninstall($addon);
        return success('ADDON_UNINSTALL_SUCCESS');
    }

    /**
     * 插件安装环境检测
     * @description 插件安装环境检测
     * @param $addon
     * @return Response
     */
    public function uninstallCheck($addon)
    {
        return success(data:(new AddonService())->uninstallCheck($addon));
    }

    /**
     * 插件列表
     * @description 插件列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['title', ''],
        ]);
        return success((new AddonService())->getPage($data));
    }

    /**
     * 插件详情
     * @description 插件详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success((new AddonService())->getInfo($id));
    }

    /**
     * 设置插件状态
     * @description 设置插件状态
     * @param int $id
     * @param int $status
     * @return Response
     */
    public function setStatus(int $id, int $status)
    {
        (new AddonService())->setStatus($id, $status);
        return success('SET_SUCCESS');
    }

    /**
     * 下载插件
     * @description 下载插件
     * @param $addon
     * @return Response
     */
    public function download($addon){
        $data = $this->request->params([
            ['version', '']
        ]);
        (new AddonService())->download($addon, $data['version']);
        return success('DOWNLOAD_SUCCESS');
    }

    /**
     * 查询已安装插件
     * @description 查询已安装插件
     * @return Response
     */
    public function getInstallList(){
        return success(data:(new AddonService())->getInstallList());
    }

    /**
     * 查询已安装有效应用
     * @description 查询已安装有效应用
     */
    public function getAddonList()
    {
        return success((new CoreAddonService())->getInstallAddonList());
    }

    /**
     * 插件类型
     * @description 插件类型
     * @return Response
     */
    public function getType(){
        return success(AddonDict::getType());
    }

    /**
     * 更新插件
     * @description 更新插件
     * @param $addon
     * @return Response
     */
    public function upgrade($addon = ''){
        return success('DOWNLOAD_SUCCESS', (new AddonService())->upgrade($addon));
    }

    public function showApp()
    {
        return success(data:(new AddonService())->getShowAppTools());
    }

    public function showMarketing()
    {
        return success(( new AddonService() )->getShowMarketingTools());
    }

    /**
     * 获取首页应用标签
     * @description 获取首页应用标签
     */
    public function getIndexAddonLabelList()
    {
        return success((new CoreAddonService())->getIndexAddonLabelList());
    }

    /**
     * 获取首页应用
     * @description 获取首页应用
     * @return Response
     */
    public function getIndexAddonList()
    {
        $data = $this->request->params([
            ['label_id', ''],
        ]);
        return success((new CoreAddonService())->getIndexAddonList($data['label_id']));
    }

}
