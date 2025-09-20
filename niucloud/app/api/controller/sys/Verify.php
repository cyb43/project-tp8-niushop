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
     * 生成核销码
     * @return Response
     */
    public function getVerifyCode()
    {
        $data = $this->request->params([
            ['data', []],
            ['type', ''],
            ['need_barcode', 0]//是否需要条形码
        ]);
        return success(data: (new VerifyService())->getVerifyCode($data['type'], $data['data'], $data['need_barcode']));
    }

    /**
     * 获取核销码信息
     * @return Response
     */
    public function getInfoByCode()
    {
        $data = $this->request->params([
            ['code', ''],
        ]);
        return success(data: (new VerifyService())->getInfoByCode($data['code']));
    }

    /**
     * 核销
     * @param $code
     * @return Response
     */
    public function verify($code)
    {
        return success(data: (new VerifyService())->verify($code));
    }

    /**
     * 校验是否是核销员
     * @return Response
     */
    public function checkVerifier()
    {
        return success(data: (new VerifyService())->checkVerifier());
    }

    /**
     * 核销记录
     * @return Response
     */
    public function records()
    {
        $data = $this->request->params([
            ['relate_tag', 0],
            ['type', ''],
            ['code', ''],
            ['keyword', ''],
            ['order_id', ''],
            ['create_time', []]
        ]);
        return success(data: (new VerifyService())->getRecordsPageByVerifier($data));
    }

    /**
     * 获取核销详情
     * @param string|int $code
     * @return Response
     */
    public function detail(string|int $code)
    {
        return success(data: (new VerifyService())->getRecordsDetailByVerifier($code));

    }

    /**
     * 会员核销记录
     * @return Response
     */
    public function memberRecordList()
    {
        $data = $this->request->params([
            ['relate_tag', 0],
            ['type', ''],
            ['order_id', '']
        ]);
        return success(data: (new VerifyService())->getMemberRecordsList($data));
    }
}
