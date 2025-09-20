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

namespace app\service\admin\pay;

use app\dict\pay\TransferDict;
use app\model\pay\Pay;
use app\service\core\pay\CoreTransferSceneService;
use core\base\BaseAdminService;
use core\exception\AdminException;
/**
 * 支付服务层
 */
class TransferService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Pay();
    }

    public function getWechatTransferScene(){
        return (new CoreTransferSceneService())->getWechatTransferScene();
    }

    /**
     * 设置转账场景id
     * @param $scene
     * @param $data
     * @return void
     */
    public function setSceneId($scene, $data){
        $core_transfer_service = new CoreTransferSceneService();
        $config = $core_transfer_service->getWechatTransferSceneConfig() ?? [];
        $scene_list = TransferDict::getWechatTransferScene();
        if(empty($scene_list[$scene])) throw new AdminException('MERCHANT_TRANSFER_SCENARIOS_THAT_DO_NOT_EXIST');
        $config[$scene] = $data['scene_id'];
        $core_transfer_service->setWechatTransferSceneConfig($config);
        return true;
    }

    /**
     * 设置业务转账场景配置
     * @param $type
     * @param $data
     * @return void
     */
    public function setTradeScene($type, $data){
        $core_transfer_service = new CoreTransferSceneService();
        $core_transfer_service->setTradeScene($type, $data);
        return true;
    }
}
