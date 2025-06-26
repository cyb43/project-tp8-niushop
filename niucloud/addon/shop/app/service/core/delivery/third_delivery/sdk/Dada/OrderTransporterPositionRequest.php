<?php

namespace addon\shop\app\service\core\delivery\third_delivery\sdk\Dada;

use app\service\core\http\HttpHelper;

class OrderTransporterPositionRequest extends BaseRequest
{
    private $request_url = '/api/order/transporter/position';
    private $order_ids;


    // 商品列表
    public function setOrderIds(array $order_ids)
    {
        $this->order_ids = $order_ids;
        return $this;
    }

    public function getOrderIds()
    {
        return $this->order_ids;
    }

    // 添加单个商品
    public function addProductItem($order_id)
    {
        $this->order_ids[] = $order_id;
        return $this;
    }

    public function toArray()
    {
        return [
            'order_ids' => $this->order_ids,
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    private function validate()
    {
        if (empty($this->order_ids)) {
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