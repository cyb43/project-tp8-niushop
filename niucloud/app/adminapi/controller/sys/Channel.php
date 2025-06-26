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

namespace app\adminapi\controller\sys;

use app\dict\common\ChannelDict;
use core\base\BaseAdminController;
use think\Response;

/**
 * 渠道管理
 * Class Channel
 * @description 渠道管理
 */
class Channel extends BaseAdminController
{

    /**
     * 获取渠道列表
     * @description 获取渠道列表
     * @return Response
     */
    public function getChannelType()
    {
        return success(ChannelDict::getType());
    }
}
