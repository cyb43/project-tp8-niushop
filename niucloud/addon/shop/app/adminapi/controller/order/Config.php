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

namespace addon\shop\app\adminapi\controller\order;

use addon\shop\app\service\admin\order\ConfigService;
use core\base\BaseAdminController;

/**
 * 订单交易设置
 * Class Config
 * @package addon\shop\app\adminapi\controller\config
 */
class Config extends BaseAdminController
{
    /**
     * 交易设置配置
     * @description 设置交易设置配置
     * @return \think\Response
     */
    public function setConfig()
    {
        $data = $this->request->params([
            [ "is_close", 1 ],
            [ "close_length", "" ],
            [ "is_finish", "" ],
            [ "finish_length", 1 ],
            [ "no_allow_refund", "" ],
            [ "refund_length", "" ],
            [ "is_invoice", "" ],
            [ "invoice_type", "" ],
            [ "invoice_content", "" ],
            [ "is_evaluate", 1 ],
            [ "evaluate_is_to_examine", 1 ],
            [ "evaluate_is_show", 1 ],
            [ 'form_id', '' ]
        ]);

        ( new ConfigService() )->setConfig($data);
        return success('SUCCESS');
    }

    /**
     * 获取交易配置
     * @description 获取交易配置
     * @return \think\Response
     */
    public function getConfig()
    {
        return success(( new ConfigService() )->getConfig());
    }

}
