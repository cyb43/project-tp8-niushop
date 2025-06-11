<?php

namespace app\service\core\http;

class HttpHelper
{
    public function get($url, $data = [])
    {
        return $this->formatNiuCloudReturn($this->httpRequest('GET', $url, $data));
    }

    public function put($url, $data = [])
    {
        return $this->formatNiuCloudReturn($this->httpRequest('PUT', $url, $data));
    }

    public function post($url, $data = [])
    {
        return $this->formatNiuCloudReturn($this->httpRequest('POST', $url, $data));
    }

    public function formatNiuCloudReturn($return)
    {
        if ($return['code'] != 1) {
            throw new \Exception($return['msg']);
        }
        return $return['data'];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function httpRequest(string $method, string $url, array $data = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_PROXY, ''); // 清空代理设置
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
        ]);

        // 根据请求方法设置不同的CURL选项
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'GET':
                // 对于GET请求，参数通常放在URL中，而不是请求体
                if (!empty($data)) {
                    $url = $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
                    curl_setopt($curl, CURLOPT_URL, $url);
                }
                // 同时设置HTTP基本认证的另一种方式（保留原代码中的设置）
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//                curl_setopt($curl, CURLOPT_USERPWD, "[$username]:[$password]");
                break;
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
        }

        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new \Exception(curl_error($curl));
        }
        curl_close($curl);
        $response_data = json_decode($result, true);
        if(empty($response_data)){
            throw new \Exception("SYSTEM_IS_ERROR");
        }
        return $response_data;
    }
}