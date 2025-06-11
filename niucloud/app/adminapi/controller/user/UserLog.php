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

namespace app\adminapi\controller\user;

use app\service\admin\user\UserLogService;
use core\base\BaseAdminController;
use think\Response;

class UserLog extends BaseAdminController
{
    /**
     * 操作日志列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['uid', 0],
            ['create_time', []],
            ['ip', ''],
            ['username', ''],
            ['type', ''],
            ['url', ''],
        ]);

        $list = (new UserLogService())->getPage($data);
        return success($list);

    }

    /**
     * 操作日志详情
     * @param $id
     * @return Response
     */
    public function info($id)
    {
        return success((new UserLogService())->getInfo($id));
    }

    /**
     * 清空日志
     * @return Response
     */
    public function destroy()
    {
        (new UserLogService())->destroy();
        return success("SUCCESS");
    }


}
