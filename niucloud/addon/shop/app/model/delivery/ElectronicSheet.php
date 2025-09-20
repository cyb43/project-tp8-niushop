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

namespace addon\shop\app\model\delivery;

use addon\shop\app\dict\delivery\ElectronicSheetDict;
use core\base\BaseModel;

/**
 * 电子面单模型
 * Class ElectronicSheet
 * @package addon\shop\app\model\delivery
 */
class ElectronicSheet extends BaseModel
{


    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shop_delivery_electronic_sheet';

    /**
     * 状态字段转化
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getPayTypeNameAttr($value, $data)
    {
        if (!empty($data[ 'pay_type' ])) {
            return ElectronicSheetDict::getPayType($data[ 'pay_type' ]) ?? '';
        }
        return '';
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'company_id', 'express_company_id');
    }

    /**
     * 搜索器:电子面单
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("id", $value);
        }
    }

    /**
     * 搜索器:电子面单模板名称
     * @param $value
     * @param $data
     */
    public function searchTemplateNameAttr($query, $value, $data)
    {
        if ($value != '') {
            $query->where("template_name", 'like', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }

    /**
     * 搜索器:电子面单物流公司id
     * @param $value
     * @param $data
     */
    public function searchExpressCompanyIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("express_company_id", $value);
        }
    }

    /**
     * 搜索器:电子面单快递员上门揽件（0：否，1：是）
     * @param $value
     * @param $data
     */
    public function searchIsNoticeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("is_notice", $value);
        }
    }

    /**
     * 搜索器:电子面单状态（1：开启，0：关闭）
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where("status", $value);
        }
    }

    public function getKdbirdConfigAttr($value, $data)
    {
        if(!empty($data['interface_data'])){
            $interface_data = json_decode($data['interface_data'], true);
            return is_array($interface_data) && $interface_data['kdbird'] ? $interface_data['kdbird'] : [];
        }
    }

    public function getKd100ConfigAttr($value, $data)
    {
        if(!empty($data['interface_data'])){
            $interface_data = json_decode($data['interface_data'], true);
            return is_array($interface_data) && $interface_data['kd100'] ? $interface_data['kd100'] : [];
        }
    }



}
