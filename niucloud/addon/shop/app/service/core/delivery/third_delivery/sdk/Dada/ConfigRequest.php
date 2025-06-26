<?php

namespace addon\shop\app\service\core\delivery\third_delivery\sdk\Dada;

class ConfigRequest extends BaseRequest
{
    private $cancel_reason_url = '/api/order/cancel/reasons';
    public function getCancelReasonList()
    {
        return $this->request([], $this->cancel_reason_url);
    }
}