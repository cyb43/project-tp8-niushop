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

use app\service\admin\channel\H5Service;
use core\base\BaseAdminController;
use think\Response;

/**
 * H5配置
 * Class H5
 * @package app\adminapi\controller\channel
 */
class H5 extends BaseAdminController
{
    /**
     * 获取H5配置信息
     * @description 获取H5配置信息
     * @return Response
     */
    public function get()
    {
        return success((new H5Service())->getH5());
    }

    /**
     * 设置H5配置信息
     * @description 设置H5配置信息
     * @return Response
     */
    public function set()
    {
        $data = $this->request->params([
            ['is_open', 0],
        ]);

        (new H5Service())->setH5($data);
        return success('SET_SUCCESS');
    }
}
