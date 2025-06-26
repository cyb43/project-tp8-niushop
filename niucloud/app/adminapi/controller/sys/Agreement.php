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

namespace app\adminapi\controller\sys;

use app\service\admin\sys\AgreementService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 协议控制器
 * Class Agreement
 * @package app\adminapi\controller\sys
 */
class Agreement extends BaseAdminController
{
    /**
     * 协议列表
     * @description 协议列表
     * @return Response
     */
    public function lists()
    {
        $res = (new AgreementService())->getList();
        return success($res);
    }

    /**
     * 协议内容
     * @description 协议内容
     * @param string $key
     * @return Response
     */
    public function info(string $key)
    {
        $res = (new AgreementService())->getAgreement($key);
        return success($res);
    }

    /**
     * 协议更新
     * @description 协议更新
     * @param string $key
     * @return Response
     */
    public function edit(string $key)
    {
        $data = $this->request->params([
            ['title', ''],
            ['content', ''],
        ], false);
        $this->validate($data, 'app\validate\sys\Agreement.edit');
        (new AgreementService())->setAgreement($key, $data['title'], $data['content']);
        return success('EDIT_SUCCESS');
    }

}
