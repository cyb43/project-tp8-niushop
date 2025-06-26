<?php

namespace addon\shop\app\service\core\delivery\third_delivery\sdk\Dada;

use addon\shop\app\dict\delivery\DeliveryLocalDict;
use app\service\core\http\HttpHelper;

class BaseRequest
{
    protected $test_domain = 'https://newopen.qa.imdada.cn';
    protected $domain = 'https://newopen.imdada.cn';
    protected $app_secret = 'c0984f3aece2b32e7f28d07d4a413cd6';
    protected $app_key = 'dada48ca0d46ea5f745';
    protected $shop_id = '844132245';
    protected $shop_store_no = '76f7a09af586492e';
    protected $callback = 'delivery/third/callback/%s/%s/%s';

    // 设置App Secret
    public function setAppSecret(string $app_secret): self
    {
        $this->app_secret = $app_secret;
        return $this;
    }

    public function getAppSecret(): string
    {
        return $this->app_secret;
    }

    // 设置App Key
    public function setAppKey(string $app_key): self
    {
        $this->app_key = $app_key;
        return $this;
    }

    public function getAppKey(): string
    {
        return $this->app_key;
    }

    // 设置商户ID
    public function setShopId(string $shop_id): self
    {
        $this->shop_id = $shop_id;
        return $this;
    }

    public function getShopId(): string
    {
        return $this->shop_id;
    }

    // 设置门店编号
    public function setShopStoreNo(string $shop_store_no): self
    {
        $this->shop_store_no = $shop_store_no;
        return $this;
    }

    public function getShopStoreNo(): string
    {
        return $this->shop_store_no;
    }

    // 回调地址
    public function setCallback($order_no)
    {
        $this->callback = sprintf($this->callback,DeliveryLocalDict::DADA,$order_no);
        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    // 获取基础参数
    public function getBaseParams(): array
    {
        return [
            'app_key' => $this->app_key,
            'shop_id' => $this->shop_id,
            'shop_store_no' => $this->shop_store_no,
            'app_secret' => $this->app_secret,
        ];
    }

    // 获取基础参数
    public function setBaseParams($config)
    {
        $this->app_key = $config['app_key'];
        $this->shop_id = $config['shop_id'];
        $this->shop_store_no = $config['shop_store_no'];
        $this->app_secret = $config['app_secret'];
        return $this;
    }


    /**
     * 构建请求参数
     *
     * @param mixed $body 接口的body参数内容
     * @return array 构建真正的api接口参数
     */
    protected function generateParams($body)
    {
        $data = [
            "source_id" => $this->shop_id,
            "app_key" => $this->app_key,
            "timestamp" => time(),
            "format" => "json",
            "v" => "1.0",
            "body" => empty($body) ? "" : json_encode($body),
        ];

        $data["signature"] = $this->signature($data);
        return $data;
    }

    /**
     * 生成签名
     *
     * @param array $data 请求参数
     * @param string $app_secret 应用密钥
     * @return string 签名结果
     */
    private function signature($data)
    {
        // 请求参数按照【属性名】字典升序排序
        ksort($data);

        // 按照属性名+属性值拼接
        $signStr = '';
        foreach ($data as $key => $value) {
            $signStr .= $key . $value;
        }

        // 拼接后的结果首尾加上appSecret
        $finalSignStr = $this->app_secret . $signStr . $this->app_secret;

        // MD5加密并转为大写
        return strtoupper(md5($finalSignStr));
    }

    protected function request($body, $method_url)
    {
        $product_type = env('product_type');
        if ($product_type != 'product') {
            $domain = $this->test_domain;
        } else {
            $domain = $this->domain;
        }
        $request_data = $this->generateParams($body);
        $res = (new HttpHelper())->httpRequest('POST', $domain . $method_url, $request_data);
        if ($res['code'] != 0) {
            throw new \Exception('DD' . $res['code'] . ":" . $res['msg']);
        }
        return $res['result'];
    }
}