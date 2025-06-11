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

namespace app\service\core\notice;


use app\job\notice\Notice;
use app\model\sys\SysNotice;
use core\base\BaseCoreService;


/**
 * 支付渠道服务层
 */
class NoticeService extends BaseCoreService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new SysNotice();

    }

    /**
     * 消息发送
     * @param $key
     * @param $data
     * @return false|mixed
     * @throws \Exception
     */
    public static function send($key, $data){

        try {
            $template = (new CoreNoticeService())->getInfo($key);
            if(empty($template)) return false;

            return Notice::dispatch(['key' => $key, 'data' => $data, 'template' => $template], is_async:$template['async']);

        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}