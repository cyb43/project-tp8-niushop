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
use app\service\admin\verify\VerifierService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 核销人员
 * Class Verifier
 * @description 核销人员
 * @package app\adminapi\controller\verify
 */
class Verifier extends BaseAdminController
{
    /**
     * 核销人员列表
     * @description 核销人员列表
     * @return Response
     */
    public function lists()
    {
        return success(data: ( new VerifierService() )->getPage());
    }

    /**
     * 核销人员列表
     * @description 核销人员列表
     * @return Response
     */
    public function select()
    {
        return success(data: ( new VerifierService() )->getList());
    }

    /**
     * 获取核销员信息
     * @description 获取核销员信息
     * @param int $id
     * @return Response
     */
    public function detail($id)
    {
        return success(data: ( new VerifierService() )->getDetail($id));
    }

    /**
     * 添加核销员
     * @description 添加核销员
     * @return Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ 'member_id', 0 ],
            [ 'verify_type', '' ],
        ]);
        return success(data: ( new VerifierService() )->add($data));
    }

    /**
     * 添加核销员
     * @description 添加核销员
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ 'verify_type', '' ],
        ]);
        ( new VerifierService() )->edit($id,$data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 删除核销员
     * @description 删除核销员
     */
    public function del(int $id)
    {
        return success('DELETE_SUCCESS', ( new VerifierService() )->del($id));
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
}
