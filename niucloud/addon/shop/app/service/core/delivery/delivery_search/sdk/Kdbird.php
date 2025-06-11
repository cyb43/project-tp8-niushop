<?php

namespace addon\shop\app\service\core\delivery\delivery_search\sdk;


class Kdbird
{
    private $EBusinessID; // 授权key
    private $AppKey; // 快递鸟分配的公司编码
    private $is_pay;

    private $traces_url = 'https://api.kdniao.com/api/dist'; // 实时查询物流轨迹信息

    public function __construct($config)
    {
        $this->EBusinessID = $config[ "kdniao_id" ];
        $this->AppKey = $config[ "kdniao_app_key" ];
        $this->is_pay = $config[ "kdniao_is_pay" ];
    }

    /**
     * 物流跟踪
     * @param $shipper_code
     * @param $logistic_code
     * @param $mobile
     * @return array
     */
    public function orderTracesSubByJson($shipper_code, $logistic_code, $mobile)
    {
        $request_array = array(
            'ShipperCode' => $shipper_code,
            'LogisticCode' => $logistic_code,
            'CustomerName' => substr($mobile, 7, 10)
        );

        $requestData = json_encode($request_array);

        $data = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '8001',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );

        if ($this->is_pay == 2) $data[ 'RequestType' ] = 8002;

        $data[ 'DataSign' ] = $this->encrypt($requestData, $this->AppKey);
        $result = $this->sendPost($this->traces_url, $data);
        //根据公司业务处理返回的信息......
        $result = json_decode($result, true);
        $res = [];
        if ($result[ "Success" ] == false) {
            $res[ "success" ] = false;
            $res[ "reason" ] = $result[ "Reason" ];
        } else {
            $list = [];
            if (!empty($result[ 'Traces' ])) {
                foreach ($result[ 'Traces' ] as $trace) {
                    $list[] = [
                        'datetime' => $trace[ 'AcceptTime' ],
                        'remark' => $trace[ 'AcceptStation' ]
                    ];
                }
            }
            $res = [
                'success' => $result[ 'Success' ],
                'reason' => !empty($result[ 'Reason' ]) ? $result[ 'Reason' ] : '',
                'status' => !empty($result[ 'State' ]) ? $result[ 'State' ] : '',
                'status_name' => !empty($result[ 'State' ]) ? $this->getStatusName($result[ 'State' ]) : '',
                'shipper_code' => !empty($result[ 'ShipperCode' ]) ? $result[ 'ShipperCode' ] : '',
                'logistic_code' => !empty($result[ 'LogisticCode' ]) ? $result[ 'LogisticCode' ] : '',
                'list' => $list
            ];
        }
        return $res;
    }

    /**
     * 电商Sign签名生成
     * @param string $data 内容
     * @param $appkey
     * @return string DataSign签名
     */
    public function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

    /**
     * post提交数据
     * @param string $url 请求Url
     * @param array $data 提交的数据
     * @return false|string
     */
    public function sendPost($url, $data)
    {
        $postdata = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**
     * 物流跟踪状态
     * @param $status
     * @return mixed|string
     */
    public function getStatusName($status)
    {
        $data = [
            0 => "暂无轨迹信息",
            1 => "已揽收",
            2 => "在途中",
            3 => "已签收",
            4 => "问题件",
            5 => "转寄",
            6 => "清关"
        ];
        $status_name = isset($data[ $status ]) ? $data[ $status ] : '';
        return $status_name;
    }
}
