<?php

namespace addon\shop\app\service\core\delivery\third_delivery\sdk\Dada;

use app\service\core\http\HttpHelper;

class OrderCreateByFeeOrderNoRequest extends BaseRequest
{
    private $request_url = '/api/order/addAfterQuery';
    private $delivery_no;
    private $enable_reset;
    private $info;

    public function setDeliveryNo($delivery_no)
    {
        $this->delivery_no = $delivery_no;
        return $this;
    }

    public function getDeliveryNo()
    {
        return $this->delivery_no;
    }

    public function setEnableReset($enable_reset)
    {
        $this->enable_reset = $enable_reset;
        return $this;
    }

    public function getEnableReset()
    {
        return $this->enable_reset;
    }

    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function toArray()
    {
        return [
            'delivery_no' => $this->delivery_no,
            'enable_reset' => $this->enable_reset,
            'info' => $this->info,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
    private function validate()
    {
        if (empty($this->delivery_no)) {
            throw new \Exception('单号不能为空');
        }
    }
    public function sendRequest()
    {
        $this->validate();
        $body = $this->toArray();
        foreach ($body as $key => $value) {
            if (is_null($value)) {
                unset($body[$key]);
            }
        }
        return $this->request($body,$this->request_url);
    }

}