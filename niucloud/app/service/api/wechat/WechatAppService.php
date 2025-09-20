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

namespace app\service\api\wechat;


use app\dict\member\MemberLoginTypeDict;
use app\dict\member\MemberRegisterTypeDict;
use app\dict\scan\ScanDict;
use app\service\api\login\LoginService;
use app\service\api\login\RegisterService;
use app\service\api\member\MemberConfigService;
use app\service\api\member\MemberService;
use app\service\core\scan\CoreScanService;
use app\service\core\wechat\CoreWechatAppService;
use app\service\core\wechat\CoreWechatFansService;
use app\service\core\wechat\CoreWechatServeService;
use core\base\BaseApiService;
use core\exception\ApiException;
use core\exception\AuthException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;


/**
 * 微信配置模型
 * Class WechatConfigService
 * @package app\service\core\wechat
 */
class WechatAppService extends BaseApiService
{
    /**
     * 处理授权回调
     * @param string $code
     * @return array
     */
    public function userFromCode(string $code)
    {
        $userinfo = CoreWechatAppService::userFromCode($code);
        if (empty($userinfo)) throw new ApiException('WECHAT_EMPOWER_NOT_EXIST');
        $token_response = $userinfo->getTokenResponse();
        if (empty($token_response)) throw new ApiException('WECHAT_EMPOWER_NOT_EXIST');
        $scope = $token_response[ 'scope' ];
        if ($scope == 'snsapi_base') {//静默授权
            $openid = $token_response[ 'openid' ] ?? '';
        } else {
            $openid = $userinfo->getId();//对应微信的 openid
            $nickname = $userinfo->getNickname();//对应微信的 nickname
            $avatar = $userinfo->getAvatar();//对应微信的 头像地址
        }
        $unionid = $userinfo->getRaw()[ 'unionid' ] ?? '';
        if (empty($openid)) throw new ApiException('WECHAT_EMPOWER_NOT_EXIST');
        $is_snapshotuser = $userinfo->getRaw()[ 'is_snapshotuser' ] ?? 0;
        if ($is_snapshotuser == 1) throw new ApiException('WECHAT_SNAPSHOUTUSER');
        //todo 这儿还可能会获取用户昵称 头像  性别 ....用以更新会员信息
        return [ $avatar ?? '', $nickname ?? '', $openid, $unionid ];
        //todo  业务落地
    }

    /**
     * 登录通过code
     * @param string $code
     * @return array|string[]|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function loginByCode(string $code)
    {
        [ $avatar, $nickname, $openid, $unionid ] = $this->userFromCode($code);
        return $this->login($openid, $nickname, $avatar, $unionid);
    }

    /**
     * 公众号登录
     * @param string $openid
     * @param string $nickname
     * @param string $avatar
     * @param string $unionid
     * @return array|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function login(string $openid, string $nickname = '', string $avatar = '', string $unionid = '')
    {

        $member_service = new MemberService();
        $member_info = $member_service->findMemberInfo([ 'wxapp_openid' => $openid]);
        if ($member_info->isEmpty() && !empty($unionid)) {
            $member_info = $member_service->findMemberInfo([ 'wx_unionid' => $unionid]);
            if (!$member_info->isEmpty()) {
                $member_info->wx_openid = $openid;
            }
        }
        if ($member_info->isEmpty()) {
            $config = ( new MemberConfigService() )->getLoginConfig();
            $is_auth_register = $config[ 'is_auth_register' ];
            $is_force_access_user_info = $config[ 'is_force_access_user_info' ];
            $is_bind_mobile = $config[ 'is_bind_mobile' ];

            // 开启自动注册会员
            if ($is_auth_register) {

                // 开启强制获取会员信息并且开启强制绑定手机号，必须获取手机号才能进行注册，由于公众号无法主动获取，所以不能注册
                if ($is_force_access_user_info && $is_bind_mobile) {
                    return [ 'avatar' => $avatar, 'nickname' => $nickname, 'openid' => $openid, 'unionid' => $unionid ];
                } else if ($is_force_access_user_info) {
                    // 开启强制获取会员信息时，必须获取到昵称和头像才能进行注册
//                    if (!empty($nickname) && !empty($avatar)) {
                        if (empty($nickname)){
                            $nickname = unique_random();
                        }
                        return $this->register($openid, '', $nickname, $avatar, $unionid); // 获取到昵称和头像，然后进行注册
//                    } else {
//                        return [ 'avatar' => $avatar, 'nickname' => $nickname, 'openid' => $openid, 'unionid' => $unionid ];
//                    }
                } else if ($is_bind_mobile) {
                    // 开启强制绑定手机号，必须获取手机号才能进行注册，由于公众号无法主动获取，所以不能注册
                    return [ 'openid' => $openid, 'unionid' => $unionid ];
                } else if (!$is_force_access_user_info && !$is_bind_mobile) {
                    // 关闭强制获取用户信息、并且关闭强制绑定手机号的情况下允许注册
                    return $this->register($openid, '', $nickname, $avatar, $unionid);
                }

            } else {
                return [ 'openid' => $openid, 'unionid' => $unionid ]; // 将重要信息返回给前端保存
            }
        } else {
            // 可能会更新用户和粉丝表
            $login_service = new LoginService();
            // 若用户头像为空，那么从微信获取头像和昵称，然后进行更新
            if (empty($member_info->headimg)) {
                if (!empty($avatar)) $member_info->headimg = $avatar;
                if (!empty($nickname)) $member_info->nickname = $nickname;
            }
            return $login_service->login($member_info, MemberLoginTypeDict::WECHAT);
        }
    }

    /**
     * 同步数据
     * @param string $code
     * @return true
     */
    public function sync(string $code)
    {
        [ $avatar, $nickname, $openid ] = $this->userFromCode($code);
        //更新粉丝
        $core_wechat_fans_service = new CoreWechatFansService();
        //这儿或许可以异步
        $core_wechat_fans_service->edit($openid, [ 'avatar' => $avatar, 'nickname' => $nickname ]);
        $member_service = new MemberService();
        $member_info = $member_service->findMemberInfo([ 'wxapp_openid' => $openid]);
        if ($member_info->isEmpty()) throw new AuthException('MEMBER_NOT_EXIST');//账号不存在
        $member_service->editByFind($member_info, [ 'headimg' => $avatar, 'nickname' => $nickname ]);
        return true;
    }

    /**
     * 注册
     * @param string $openid
     * @param string $mobile
     * @param string $nickname
     * @param string $avatar
     * @param string $wx_unionid
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function register(string $openid, string $mobile = '', string $nickname = '', string $avatar = '', string $wx_unionid = '')
    {
        $member_service = new MemberService();
        $member_info = $member_service->findMemberInfo([ 'wxapp_openid' => $openid]);
        if (!$member_info->isEmpty()) throw new AuthException('MEMBER_IS_EXIST');//账号已存在, 不能在注册
        if (!empty($wx_unionid)) {
            $member_info = $member_service->findMemberInfo([ 'wx_unionid' => $wx_unionid]);
            if (!$member_info->isEmpty()) throw new AuthException('MEMBER_IS_EXIST');//账号已存在, 不能在注册
        }
        $register_service = new RegisterService();
        return $register_service->register($mobile,
            [
                'wxapp_openid' => $openid,
                'nickname' => $nickname,
                'headimg' => $avatar,
                'wx_unionid' => $wx_unionid
            ],
            MemberRegisterTypeDict::APP
        );

    }

}
