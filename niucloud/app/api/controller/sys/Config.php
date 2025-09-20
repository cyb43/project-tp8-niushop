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

use app\service\api\diy\DiyConfigService;
use app\service\api\diy\DiyService;
use app\service\api\member\MemberConfigService;
use app\service\api\member\MemberLevelService;
use app\service\api\member\MemberService;
use app\service\api\sys\ConfigService;
use core\base\BaseApiController;
use think\Response;

class Config extends BaseApiController
{

    /**
     * 获取版权信息
     * @return Response
     */
    public function getCopyright()
    {
        return success(( new ConfigService() )->getCopyright());
    }

    /**
     * 场景域名
     * @return Response
     */
    public function getSceneDomain()
    {
        return success(( new ConfigService() )->getSceneDomain());
    }

    /**
     * 获取站点信息
     * @return Response
     */
    public function site()
    {
        return success(( new ConfigService() )->getWebSite());
    }

    /**
     * 获取手机端首页列表
     */
    public function getWapIndexList()
    {
        $data = $this->request->params([
            [ 'title', '' ],
            [ 'key', '' ] // 多个查询，逗号隔开
        ]);
        return success(( new ConfigService() )->getWapIndexList($data));
    }

    /**
     * 获取地图配置
     * @return Response
     */
    public function getMap()
    {
        return success(( new ConfigService() )->getMap());
    }

    /**
     * 获取初始化数据信息
     * @return Response
     */
    public function init()
    {
        $data = $this->request->params([
            [ 'url', '' ],
            [ 'openid', '' ]
        ]);

        $res = [];
        $res[ 'tabbar_list' ] = ( new DiyConfigService() )->getBottomList();
        $res[ 'map_config' ] = ( new ConfigService() )->getMap();
        $res[ 'site_info' ] = ( new ConfigService() )->getWebSite();
        $res[ 'member_level' ] = ( new MemberLevelService() )->getList();
        $res[ 'login_config' ] = ( new MemberConfigService() )->getLoginConfig($data[ 'url' ]);
        $res[ 'theme_list' ] = ( new DiyService() )->getDiyTheme();
        $openid_field = match ( $this->request->getChannel() ) {
            'wechat' => 'wx_openid',
            'weapp' => 'weapp_openid',
            default => ''
        };
        // 根据来源查询是否已经存在用户, 如果存在则快捷登录时不再弹出授权弹框
        // 根据来源查询是否绑定手机号, 如果绑定并且开启强制绑定则快捷登录时不再弹出绑定手机弹窗
        $res[ 'member_exist' ] = 0;
        $res[ 'member_mobile_exist' ] = 0;
        if (!empty($data[ 'openid' ])) {
            if (!empty($openid_field)){
                $res[ 'member_exist' ] = ( new MemberService() )->getCount([ [ $openid_field, '=', $data[ 'openid' ] ] ]) > 0 ? 1 : 0;

                $res[ 'member_mobile_exist' ] = ( new MemberService() )->getCount([ [ $openid_field, '=', $data[ 'openid' ] ], [ 'mobile', '<>', '' ] ]) > 0 ? 1 : 0;
            }
        }

        ( new MemberService() )->initMemberData();

        event('initWap');
        return success($res);
    }

    /**
     * 获取公众号用户是否绑定手机
     * @return Response
     */
    public function getMemberMobileExist()
    {
        $data = $this->request->params([
            [ 'openid', '' ]
        ]);

        $openid_field = match ( $this->request->getChannel() ) {
            'wechat' => 'wx_openid',
            'weapp' => 'weapp_openid',
            default => ''
        };

        // 根据来源查询是否绑定手机号, 如果绑定并且开启强制绑定则快捷登录时不再弹出绑定手机弹窗
        $res[ 'member_mobile_exist' ] = 0;
        if (!empty($data[ 'openid' ])) {
            if (!empty($openid_field)) {
                $res['member_mobile_exist'] = (new MemberService())->getCount([ [ $openid_field, '=', $data['openid'] ], ['mobile', '<>', ''] ]) > 0 ? 1 : 0;
            }
        }

        return success($res);
    }
}
