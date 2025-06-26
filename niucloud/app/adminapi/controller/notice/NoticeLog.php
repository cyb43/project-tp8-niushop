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

namespace app\adminapi\controller\notice;

use app\service\admin\notice\NoticeLogService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 消息发送记录
 * Class NoticeLog
 * @description 消息发送记录
 * @package app\adminapi\controller\notice
 */
class NoticeLog extends BaseAdminController
{

    /**
     * 消息发送记录列表
     * @description 消息发送记录列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['key', ''],
            ['receiver', ''],
            ['create_time', []]
        ]);

        $res = (new NoticeLogService())->getPage($data);
        return success($res);
    }

    /**
     * 消息发送记录详情
     * @description 消息发送记录详情
     * @param $id
     * @return Response
     */
    public function info($id)
    {
        $res = (new NoticeLogService())->getInfo($id);
        return success($res);
    }

}
