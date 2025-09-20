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
use core\exception\AdminException;
use think\facade\Cache;
use think\facade\Log;
use function DI\string;

class Kd100Search extends BaseElectronicSheetSearch
{

    protected $secret;
    protected $key;
    protected $url = "https://api.kuaidi100.com/label/order";
    /**
     * @param array $config
     * @return void
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->key = $config[ 'kd100_key' ] ?? '';
        $this->secret = $config[ 'kd100_secret' ] ?? '';
        if (empty($this->key) || empty($this->secret)){
            throw new AdminException('NOT_CONFIGURED_DELIVERY_KD100');
        }
    }

    /**
     * 电子面单
     * @param array $data
     * @return array
     */
    public function electronicSheet(array $data = [])
    {
        $kd100_config =  $data['es_template_info']['kd100_config'];
        $template_info = [
            'printType'=>'HTML',//打印类型，NON：只下单不打印（默认）； IMAGE:生成图片短链；HTML:生成html短链
            'partnerId'=>$kd100_config['month_code'],//单客户账户或月结账号
            'partnerKey'=>$kd100_config['customer_pwd'],//电子面单密码
            'partnerSecret'=>$kd100_config['month_code'],//电子面单密钥
            'partnerName'=>$kd100_config['customer_name'],//电子面单客户账户名称
            'code'=>$kd100_config['send_site'],//电子面单承载编号
            'checkMan'=>$kd100_config['send_staff'],//电子面单承载快递员名
            'payType'=>$kd100_config['pay_type'],//支付方式： SHIPPER：寄方付（默认） CONSIGNEE：到付 MONTHLY：月结 THIRDPARTY
            'expType'=>$data['es_template_info']['exp_type'],//产品类型
        ];
        $param_data =[
            'kuaidicom'=>$data['es_template_info']['company']['kd100_express_no_electronic_sheet'],//快递公司编码
            'recMan'=>[
                'name'=>$data['Receiver']['Name'],//收件人姓名
                'mobile'=>$data['Receiver']['Mobile'],//收件人手机
                'printAddr'=>$data['Receiver']['ProvinceName'].$data['Receiver']['CityName'].$data['Receiver']['ExpAreaName'].$data['Receiver']['Address'],//完整地址地址
            ],
            'sendMan'=>[
                'name'=>$data['Sender']['Name'],//寄件人姓名
                'mobile'=>$data['Sender']['Mobile'],//寄件人手机
                'printAddr'=>$data['Sender']['ProvinceName'].$data['Sender']['CityName'].$data['Sender']['ExpAreaName'].$data['Sender']['Address'],//完整地址地址
            ],
            'cargo'=>'商品',//物品名称,例：文件
            'count'=>$data['Quantity'],//包裹总数量
            'weight'=>$data['Weight'],//包裹总重量,极兔速递必填
            'remark'=>'',//备注
            'tempId'=>$data['es_template_info']['temp_id'],//主单模板，
            'childTempId'=>$data['es_template_info']['child_temp_id'],//子单模板
            'backTempId'=>$data['es_template_info']['back_temp_id'],//回单模板
        ];

        $param = array_merge($template_info,$param_data);
        $post_data['method'] = 'order';
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $post_data['key'] = $this->key;
        $post_data['t'] = time();
        $sign = md5($post_data['param'] . $post_data['t']. $this->key . $this->secret);
        $post_data[ 'sign' ] = strtoupper($sign);
        $result = $this->sendPost($this->url, $post_data);
        Log::write('快递100电子面单返回数据');
        Log::write($result);

        $res = [
            'success' => false,
            'reason' => '',
            'result_code' => 105,
        ];
        if (!empty($result[ 'code' ]) && $result[ 'code' ] == 200) {
            $res[ 'success' ] = $result[ 'success' ];
            $res[ 'result_code' ] = $result[ 'code' ];
            $data = $result[ 'data' ];
            $res[ 'print_template' ] = $data[ 'label' ];
            $res['order_info'] = [
                'LogisticCode'=>$data[ 'kuaidinum' ]
            ];
          }else{
            $res[ 'reason' ] = $result[ 'message' ];
        }
        return $res;
    }


    /**
     *  post提交数据
     * @param string $url 请求Url
     * @param array $datas 提交的数据
     * @return url响应返回的html
     */
    public function sendPost($url, $datas)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datas));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        // 第二个参数为true，表示格式化输出json
        $result = json_decode($result, true);
        return $result;
    }
}