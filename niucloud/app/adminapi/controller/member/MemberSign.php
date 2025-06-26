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

namespace app\adminapi\controller\member;

use app\service\admin\member\MemberSignService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 会员签到
 * Class MemberSign
 * @description 会员签到
 * @package app\adminapi\controller\member
 */
class MemberSign extends BaseAdminController
{
    /**
     * 会员签到记录
     * @description 会员签到记录
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['create_time', []],
            ['keywords', '']
        ]);
        return success(( new MemberSignService() )->getPage($data));
    }

    /**
     * 会员签到详情
     * @description 会员签到详情
     * @param int $sign_id
     * @return Response
     */
    public function info(int $sign_id)
    {
        return success(( new MemberSignService() )->getInfo($sign_id));
    }

    /**
     * 设置签到设置
     * @description 设置签到设置
     * @return Response
     */
    public function setSign()
    {
        $data = $this->request->params([
            [ 'is_use', 0 ],
            [ 'sign_period', 0 ],
            [ 'day_award', '' ],
            [ 'continue_award', '' ],
            [ 'rule_explain', '' ]
        ]);
        ( new MemberSignService() )->setSign($data);
        return success();
    }

    /**
     * 获取签到设置
     * @description 获取签到设置
     * @return Response
     */
    public function getSign()
    {
        return success(( new MemberSignService() )->getSign());
    }

}
