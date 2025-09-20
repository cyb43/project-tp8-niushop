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

use app\dict\verify\VerifyDict;
use app\service\admin\verify\VerifyService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 核销记录
 * Class Verify
 * @description 核销记录
 * @package app\adminapi\controller\verify
 */
class Verify extends BaseAdminController
{
    /**
     * 核销记录列表
     * @description 核销记录列表
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
     * @description 核销信息
     * @param string $verify_code
     * @return Response
     */
    public function detail(string $verify_code)
    {
        return success(( new VerifyService() )->getDetail($verify_code));
    }

    /**
     * 获取核销类型
     * @description 获取核销类型
     * @return Response
     */
    public function getVerifyType()
    {
        return success(VerifyDict::getType());
    }

    /**
     * @框架核销
     * @description 核销
     */
    public function verify(string $verify_code)
    {
        return success('VERIFY_SUCCESS', (new VerifyService())->verify($verify_code));
    }
    /**
     * @获取核销码对应信息
     * @description 核销
     */
    public function getInfoByCode(string $verify_code)
    {
        return success((new VerifyService())->getInfoByCode($verify_code));
    }
}
