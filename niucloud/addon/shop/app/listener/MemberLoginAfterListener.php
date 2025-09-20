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

namespace addon\shop\app\listener;

use addon\shop\app\service\core\marketing\CoreNewcomerService;
use think\facade\Log;

/**
 * 会员登录后事件
 * Class MemberLoginAfterListener
 * @package addon\shop\app\listener
 */
class MemberLoginAfterListener
{
    public function handle(array $params)
    {
        //Log::write('MemberLoginAfterListener:' . json_encode($params));
        //登录后判断是否是新人专享活动新人，如果未参与过，给与参与资格
        $core_newcomer_service = new CoreNewcomerService();
        $core_newcomer_service->checkIfNewcomer($params[ 'member_id' ]);
        return true;
    }
}
