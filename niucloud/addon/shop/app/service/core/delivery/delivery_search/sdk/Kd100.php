<?php

namespace addon\shop\app\service\core\delivery\delivery_search\sdk;


class Kd100
{
    private $key; // 授权key
    private $customer; // 快递100分配的公司编码
    private $url; //

    public function __construct($config)
    {
        $this->key = $config[ "kd100_app_key" ];
        $this->customer = $config[ "kd100_customer" ];
        $this->url = 'https://poll.kuaidi100.com/poll/query.do';
    }

    public function orderTracesSubByJson($shipper_code, $logistic_code, $mobile)
    {
        $param = [
            'com' => $shipper_code,             // 快递公司编码
            'num' => $logistic_code,     // 快递单号
        ];
        if(!empty($mobile)){
            $param[ 'phone' ] = $mobile; // 手机号
        }
        $post_data = array();
        $post_data[ 'customer' ] = $this->customer;
        $post_data[ 'param' ] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $sign = md5($post_data[ 'param' ] . $this->key . $post_data[ 'customer' ]);
        $post_data[ 'sign' ] = strtoupper($sign);
        $result = $this->sendPost($this->url, $post_data);
        $res = [];

        if (!empty($result[ 'result' ]) && $result[ 'result' ] == false) {
            $res[ "success" ] = false;
            $res[ "reason" ] = $result[ "message" ];
        }
        if (!empty($result[ 'status' ]) && $result[ "status" ] != 200) {
            $res[ "success" ] = false;
            $res[ "reason" ] = $result[ "message" ];
        } else if (!empty($result[ 'status' ]) && $result[ "status" ] == 200) {
            $list = [];
            if (!empty($result[ 'data' ])) {
                foreach ($result[ 'data' ] as $trace) {
                    $list[] = [
                        'datetime' => $trace[ 'time' ],
                        'remark' => $trace[ 'context' ]
                    ];
                }
            }
            $res = [
                'success' => $result[ 'message' ],
                'status' => !empty($result[ 'status' ]) ? $result[ 'status' ] : '',
                'list' => array_reverse($list)
            ];
        }else{
            $res = [
                'success' => false,
                'reason' => '暂无物流信息'
            ];
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
        $data = json_decode($result, true);
        return $data;
    }

    /**
     * 物流跟踪状态
     * @param $state
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