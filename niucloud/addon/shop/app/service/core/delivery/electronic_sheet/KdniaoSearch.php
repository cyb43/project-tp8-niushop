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
namespace addon\shop\app\service\core\delivery\electronic_sheet;


use addon\shop\app\service\core\delivery\electronic_sheet\sdk\Kdbird;

class KdniaoSearch extends BaseElectronicSheetSearch
{

    protected $logistic_config;

    /**
     * @param array $config
     * @return void
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->logistic_config = $config;
    }

    /**
     * 电子面单
     * @param array $data
     * @return array
     */
    public function electronicSheet(array $data = [])
    {
        //后端暂不支持单独配置,先注释
//        $kdbird_config =  $data['es_template_info']['kdbird_config'];
//        $template_info = [
//            'ShipperCode' => $data['es_template_info']['company'][ 'express_no_electronic_sheet' ], // 快递公司编码
//            'CustomerName' => $kdbird_config[ 'customer_name' ], // 电子面单账号，申请：https://www.yuque.com/kdnjishuzhichi/rg4owd
//            'CustomerPwd' => $kdbird_config[ 'customer_pwd' ], // 电子面单密码，电子面单账号对照表：https://www.yuque.com/kdnjishuzhichi/dfcrg1/hrfw43
//            'SendSite' => $kdbird_config[ 'send_site' ],
//            'SendStaff' => $kdbird_config[ 'send_staff' ],
//            'MonthCode' => $kdbird_config[ 'month_code' ], // 月结账号
//            'PayType' => $kdbird_config[ 'pay_type' ], // 运费支付方式（1：现付，2：到付，3：月结）
//            'ExpType' => $data['es_template_info'][ 'exp_type' ], // 快递业务类型，https://www.yuque.com/kdnjishuzhichi/dfcrg1/hgx758hom5p6wz0l
//            'TemplateSize' => $kdbird_config[ 'print_style' ], // 模板规格，https://www.yuque.com/kdnjishuzhichi/dfcrg1/vpptucr1q5ahcxa7#iZvLV
//            'IsNotice' => $kdbird_config[ 'is_notice' ], // 是否通知快递员上门揽件跨越速运，京东快运必填
//        ];
        $template_info = [
            'ShipperCode' => $data['es_template_info'][ 'company' ][ 'express_no_electronic_sheet' ], // 快递公司编码
            'CustomerName' => $data['es_template_info'][ 'customer_name' ], // 电子面单账号，申请：https://www.yuque.com/kdnjishuzhichi/rg4owd
            'CustomerPwd' => $data['es_template_info'][ 'customer_pwd' ], // 电子面单密码，电子面单账号对照表：https://www.yuque.com/kdnjishuzhichi/dfcrg1/hrfw43
            'SendSite' => $data['es_template_info'][ 'send_site' ],
            'SendStaff' => $data['es_template_info'][ 'send_staff' ],
            'MonthCode' => $data['es_template_info'][ 'month_code' ], // 月结账号
            'PayType' => $data['es_template_info'][ 'pay_type' ], // 运费支付方式（1：现付，2：到付，3：月结）
            'ExpType' => $data['es_template_info'][ 'exp_type' ], // 快递业务类型，https://www.yuque.com/kdnjishuzhichi/dfcrg1/hgx758hom5p6wz0l
            'TemplateSize' => $data['es_template_info'][ 'print_style' ], // 模板规格，https://www.yuque.com/kdnjishuzhichi/dfcrg1/vpptucr1q5ahcxa7#iZvLV
            'IsNotice' => $data['es_template_info'][ 'is_notice' ], // 是否通知快递员上门揽件跨越速运，京东快运必填
        ];
        $data = array_merge($data, $template_info);
        unset($data[ 'es_template_info' ]);
        return ( new Kdbird($this->logistic_config) )->electronicSheetByJson($data);
    }
}