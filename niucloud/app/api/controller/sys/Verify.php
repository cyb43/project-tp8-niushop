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

namespace app\api\controller\sys;

use app\service\api\verify\VerifyService;
use core\base\BaseApiController;
use think\Response;

class Verify extends BaseApiController
{

    /**
     * 获取验证码
     * @return Response
     */
    public function getVerifyCode()
    {
        $data = $this->request->params([
            [ 'data', [] ],
            [ 'type', '' ]
        ]);
        return success(data: ( new VerifyService() )->getVerifyCode($data[ 'type' ], $data[ 'data' ]));
    }

    /**
     * 获取核销码信息
     * @return Response
     */
    public function getInfoByCode()
    {
        $data = $this->request->params([
            [ 'code', '' ],
        ]);
        return success(data: ( new VerifyService() )->getInfoByCode($data[ 'code' ]));
    }

    /**
     * 核销
     * @param $code
     * @return Response
     */
    public function verify($code)
    {
        return success(data: ( new VerifyService() )->verify($code));
    }

    /**
     * 校验是否是核销员
     * @return Response
     */
    public function checkVerifier()
    {
        return success(data: ( new VerifyService() )->checkVerifier());
    }

    /**
     * 核销记录
     * @return void
     */
    public function records()
    {
        $data = $this->request->params([
            [ 'relate_tag', 0 ],
            [ 'type', '' ],
            [ 'code', '' ],
            [ 'keyword', '' ],
            [ 'create_time', [] ]
        ]);
        return success(data: ( new VerifyService() )->getRecordsPageByVerifier($data));
    }

    /**
     * 获取核销详情
     * @param $code
     * @return Response
     */
    public function detail(string|int $code)
    {
        return success(data: ( new VerifyService() )->getRecordsDetailByVerifier($code));

    }
}
