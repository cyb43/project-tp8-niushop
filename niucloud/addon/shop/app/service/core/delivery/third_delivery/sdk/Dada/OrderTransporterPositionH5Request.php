<?php

namespace addon\shop\app\service\core\delivery\third_delivery\sdk\Dada;

use app\service\core\http\HttpHelper;

class OrderTransporterPositionH5Request extends BaseRequest
{
    private $request_url = '/api/order/transporter/track';
    private $order_id;


    // 商品列表
    public function setOrderId( $order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }


    public function toArray()
    {
        return [
            'order_id' => $this->order_id,
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    private function validate()
    {
        if (empty($this->order_id)) {
            throw new \Exception('订单ID不能为空');
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
        return $this->request($body, $this->request_url);
    }
}