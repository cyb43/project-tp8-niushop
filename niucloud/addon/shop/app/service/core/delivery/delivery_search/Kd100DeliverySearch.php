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
namespace addon\shop\app\service\core\delivery\delivery_search;

use addon\shop\app\service\core\delivery\delivery_search\BaseDeliverySearch;
use addon\shop\app\service\core\delivery\delivery_search\sdk\Kd100;
use addon\shop\app\model\delivery\Company;
use core\exception\AdminException;

class Kd100DeliverySearch extends BaseDeliverySearch
{

    protected $config;
    protected $kd100_app_key;
    protected $kd100_customer;


    /**
     * @param array $config
     * @return void
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->kd100_app_key = $config[ 'kd100_app_key' ] ?? '';
        $this->kd100_customer = $config[ 'kd100_customer' ] ?? '';
        if (empty($this->kd100_app_key) || empty($this->kd100_customer)){
            throw new AdminException('NOT_CONFIGURED_DELIVERY_KD100');
        }
        $this->config = [
            'kd100_app_key'   => $this->kd100_app_key,
            'kd100_customer'  => $this->kd100_customer
        ];
    }


    /**
     * @param array $data
     * @return mixed|void
     */
    public function search(array $data = [])
    {
        //查询数据
        if(!empty($data['company_id'])){
            $express_no = (new Company())->where('company_id',$data['company_id'])->value('kd100_express_no');
        }else{
            $express_no = $data['express_no'];
        }
        return (new Kd100($this->config))->orderTracesSubByJson(
             $express_no,
             $data[ 'logistic_no' ],
             $data[ 'mobile' ]
        );
    }
}