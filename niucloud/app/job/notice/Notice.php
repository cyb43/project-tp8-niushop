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

namespace app\job\notice;

use core\base\BaseJob;
use core\exception\NoticeException;
use think\facade\Log;

/**
 * 消息发送任务
 */
class Notice extends BaseJob
{

    /**
     * 消费
     * @param $key
     * @param $data
     * @param $template
     * @return true
     */
    protected function doJob($key, $data, $template)
    {
        try {
            //通过业务获取模板变量属于以及发送对象
            $result = event('NoticeData', ['key' => $key, 'data' => $data, 'template' => $template]);
            $notice_data = array_values(array_filter($result))[0] ?? [];
            Log::write("消息发送任务");
            Log::write($notice_data);
            if (empty($notice_data)) throw new NoticeException('NOTICE_TEMPLATE_IS_NOT_EXIST');
            event('Notice', ['key' => $key, 'to' => $notice_data['to'], 'vars' => $notice_data['vars'], 'template' => $template]);
            return true;
        }catch (\Exception $e){
            Log::write("消息发送任务异常".$e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
