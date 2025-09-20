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

namespace app\service\admin\auth;

use app\dict\sys\AppTypeDict;
use app\model\sys\SysUser;
use app\service\admin\captcha\CaptchaService;
use app\service\admin\sys\RoleService;
use app\service\admin\user\UserService;
use app\service\core\sys\CoreConfigService;
use core\base\BaseAdminService;
use core\exception\AuthException;
use core\util\TokenAuth;
use Exception;
use Throwable;

/**
 * 登录服务层
 * Class BaseService
 * @package app\service
 */
class LoginService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysUser();
    }

    /**
     * 用户登录
     * @param string $username
     * @param string $password
     * @param string $app_type
     * @return array|bool
     */
    public function login(string $username, string $password)
    {
        $config = ( new ConfigService() )->getConfig();
        $is_captcha = $config[ 'is_captcha' ];
        if ($is_captcha == 1) {
            ( new CaptchaService() )->verification();
        }

        $user_service = new UserService();
        $userinfo = $user_service->getUserInfoByUsername($username);
        if ($userinfo->isEmpty()) return false;

        if (!check_password($password, $userinfo->password)) return false;
        if (!$userinfo->status) {
            throw new AuthException('USER_LOCK');
        }

        if(!$userinfo->is_admin) {
            $role_service = new RoleService();
            $userrole = $role_service->getUserRoles($userinfo->role_ids);

            if (!empty($userrole)) {
                $role_count = 0;
                foreach ($userrole as $k => $v) {
                    if ($v[ 'status' ] == 0) {
                        $role_count++;
                    }
                }
                if ($role_count == count($userrole)) {
                    throw new AuthException('USER_LOCK');
                }
            }
        }

        //修改用户登录信息
        $userinfo->last_time = time();
        $userinfo->last_ip = app('request')->ip();
        $userinfo->login_count++;
        $userinfo->save();
        //创建token
        $token_info = $this->createToken($userinfo);

        //查询权限以及菜单
        $data = [
            'token' => $token_info['token'],
            'expires_time' => $token_info['params']['exp'],
            'userinfo' => [
                'uid' => $userinfo->uid,
                'username' => $userinfo->username,
                'head_img' => $userinfo->head_img
            ]
        ];

        // 获取站点布局
        $layout_config = ( new CoreConfigService() )->getConfig('SITE_LAYOUT');
        $data[ 'layout' ] = empty($layout_config) ? 'default' : $layout_config[ 'value' ][ 'key' ];
        return $data;
    }

    /**
     * 登陆退出(当前账户) (todo 这儿应该登出当前token, (登出一个账号还是全端口登出))
     * @return true
     */
    public function logout()
    {
        self::clearToken($this->uid, $this->request->adminToken());
        return true;
    }

    /**
     * 创建token
     * @param SysUser $userinfo
     * @return array
     */
    public function createToken(SysUser $userinfo)
    {
        $expire_time = env('system.admin_token_expire_time') ?? 3600;
        return TokenAuth::createToken($userinfo->uid, AppTypeDict::ADMIN, [ 'uid' => $userinfo->uid, 'username' => $userinfo->username ], $expire_time);
    }

    /**
     * 清理token
     * @param int $uid
     * @param string|null $type
     * @param string|null $token
     */
    public static function clearToken(int $uid, ?string $token = '')
    {
        TokenAuth::clearToken($uid, AppTypeDict::ADMIN, $token);//清除平台管理端的token
    }

    /**
     * 解析token
     * @param string|null $token
     * @return array
     */
    public function parseToken(?string $token)
    {
        if (empty($token)) {
            //定义专属于授权认证机制的错误响应, 定义专属语言包
            throw new AuthException('MUST_LOGIN', 401);
        }
        //暴力操作,截停所有异常覆盖为token失效
        try {
            $token_info = TokenAuth::parseToken($token, AppTypeDict::ADMIN);
        } catch (Throwable $e) {
            throw new AuthException('LOGIN_EXPIRE', 401);

        }
        if (!$token_info) {
            throw new AuthException('MUST_LOGIN', 401);
        }
        //验证有效次数或过期时间
        return $token_info;
    }

}
