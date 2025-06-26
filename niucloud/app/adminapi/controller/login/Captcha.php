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

namespace app\adminapi\controller\login;

use app\service\admin\captcha\CaptchaService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 验证码
 * Class Captcha
 * @description 验证码
 * @package app\adminapi\controller\login
 */
class Captcha extends BaseAdminController
{

    /**
     * 创建验证码
     * @description 创建验证码
     * @return Response
     */
    public function create()
    {
        return success((new CaptchaService())->create());
    }

    /**
     * 一次校验验证码
     * @description 一次校验验证码
     * @return Response
     */
    public function check()
    {
        return success((new CaptchaService())->check());
    }

    /**
     * 二次校验验证码
     * @description 二次校验验证码
     * @return Response
     */
    public function verification()
    {
        return success((new CaptchaService())->verification());
    }

}
