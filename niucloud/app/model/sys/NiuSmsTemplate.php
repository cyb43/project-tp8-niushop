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

namespace app\model\sys;


use app\dict\notice\NoticeTypeDict;
use core\base\BaseModel;

/**
 * 牛云短信消息模型审核表
 * Class SysMessage
 * @package app\model\sys
 */
class NiuSmsTemplate extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    public $json = ['report_info','param_json'];
    public $jsonAssoc = true;
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'niu_sms_template';

    public function getTemplateTypeNameAttr($value, $data)
    {
        return NoticeTypeDict::getTemplateType($data['template_type']);
    }

    public function getAuditStatusNameAttr($value, $data)
    {
        return NoticeTypeDict::getTemplateAuditStatus($data['audit_status']);
    }
}
