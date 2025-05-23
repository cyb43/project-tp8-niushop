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

namespace app\job\wxoplatform;

use app\service\admin\wxoplatform\WeappVersionService;
use core\base\BaseJob;

/**
 * 队列异步调用支付定时未支付恢复
 */
class VersionUploadSuccess extends BaseJob
{

    /**
     * 消费
     * @param $data
     * @return true
     */
    protected function doJob($task_key, $is_all)
    {
        WeappVersionService::uploadSuccess($task_key, $is_all);
        return true;
    }

}
