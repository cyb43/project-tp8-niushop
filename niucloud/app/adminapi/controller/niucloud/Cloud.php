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

namespace app\adminapi\controller\niucloud;

use app\service\admin\niucloud\NiucloudService;
use app\service\core\niucloud\CoreCloudBuildService;
use core\base\BaseAdminController;
use core\util\niucloud\CloudService;

/**
 * 云编译
 * Class Cloud
 * @description 云编译
 * @package app\adminapi\controller\niucloud
 */
class Cloud extends BaseAdminController
{
    /**
     * 云编译
     * @description 云编译
     * @return \think\Response
     */
    public function build() {
        return success(data:(new CoreCloudBuildService())->cloudBuild());
    }

    /**
     * 安装时云编译
     * @return \think\Response
     */
    public function installBuild() {
        return success(data:(new CoreCloudBuildService())->installBuild());
    }

    /**
     * 获取云编译日志
     * @description 获取云编译日志
     * @return \think\Response
     */
    public function getBuildLog() {
        return success(data:(new CoreCloudBuildService())->getBuildLog());
    }

    /**
     * 获取云编译任务
     * @description 获取云编译任务
     * @return \think\Response
     */
    public function getBuildTask() {
        return success(data:(new CoreCloudBuildService())->getBuildTask());
    }

    /**
     * 清除云编译任务
     * @description 清除云编译任务
     * @return \think\Response
     */
    public function clearBuildTask() {
        return success(data:(new CoreCloudBuildService())->clearTask());
    }

    /**
     * 编译前环境检测
     * @description 编译前环境检测
     * @return \think\Response
     */
    public function buildPreCheck() {
        return success(data:(new CoreCloudBuildService())->buildPreCheck());
    }

    /**
     * 连通测试
     * @description 连通测试
     * @return \think\Response
     */
    public function connectTest()
    {
        $data = $this->request->params([
            [ 'url', '' ],
        ]);
        $is_connected = (new CloudService(true,$data['url']))->is_connected;
        return success('SUCCESS',$is_connected);
    }

    /**
     * 设置本地地址
     * @description 连通测试
     * @return \think\Response
     */
    public function setLocalCloudCompileConfig()
    {
        $data = $this->request->params([
            [ 'url', '' ],
            [ 'is_open', 0 ],
        ]);
        return success('SUCCESS',(new NiucloudService())->setLocalCloudCompileConfig($data));
    }

    /**
     * 获取本地地址
     * @description 连通测试
     * @return \think\Response
     */
    public function getLocalCloudCompileConfig()
    {
        return success('SUCCESS',(new NiucloudService())->getLocalCloudCompileConfig());
    }
}
