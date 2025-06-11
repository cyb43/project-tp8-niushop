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

namespace app\adminapi\controller\pay;

use app\dict\pay\AccountLogDict;
use app\service\admin\pay\AccountLogService;
use core\base\BaseAdminController;
use think\Response;

class Account extends BaseAdminController
{
    /**
     * 账单列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['type', ''],
            ['trade_no', ''],
            ['create_time', []],
        ]);
        return success((new AccountLogService())->getPage($data));
    }

    /**
     * 账单详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success((new AccountLogService())->getInfo($id));
    }

    /**
     * 累计账单
     */
    public function stat()
    {
        return success((new AccountLogService())->stat());
    }

    public function accountType()
    {
        return success(AccountLogDict::getType());
    }
}
