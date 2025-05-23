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

namespace app\adminapi\controller\verify;

use app\service\admin\verify\VerifyService;
use core\base\BaseAdminController;
use think\Response;

class Verify extends BaseAdminController
{
    /**
     * 核销记录列表
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ 'relate_tag', 0 ],
            [ 'type', '' ],
            [ 'code', '' ],
            [ 'verifier_member_id', '' ],
            [ 'create_time', [] ]
        ]);
        return success(( new VerifyService() )->getPage($data));
    }

    /**
     * 核销信息
     * @param int $order_id
     * @return Response
     */
    public function detail(string $verify_code)
    {
        return success(( new VerifyService() )->getDetail($verify_code));
    }
}
