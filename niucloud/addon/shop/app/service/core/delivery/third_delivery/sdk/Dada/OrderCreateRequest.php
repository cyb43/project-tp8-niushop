<?php

namespace addon\shop\app\service\core\delivery\third_delivery\sdk\Dada;

use app\service\core\http\HttpHelper;

class OrderCreateRequest extends BaseRequest
{
    private $request_url = '/api/order/addOrder';
    private $cargo_num;
    private $cargo_price;
    private $cargo_type;
    private $cargo_weight;
    private $info;
    private $invoice_title;
    private $is_finish_code_needed = 0;
    private $is_prepay = 0;
    private $is_use_insurance = 0;
    private $is_expect_finish_order = 0;
    private $origin_id;
    private $pick_up_pos;
    private $product_list;
    private $receiver_address;
    private $receiver_lat;
    private $receiver_lng;
    private $receiver_name;
    private $receiver_phone;
    private $shop_no;
    private $tips;
    private $business_type;
    private $supplier_address;
    private $supplier_lat;
    private $supplier_lng;
    private $supplier_name;
    private $supplier_phone;

    // 货物数量
    public function setCargoNum($cargo_num)
    {
        $this->cargo_num = $cargo_num;
        return $this;
    }

    public function getCargoNum()
    {
        return $this->cargo_num;
    }

    // 货物价格
    public function setCargoPrice($cargo_price)
    {
        $this->cargo_price = $cargo_price;
        return $this;
    }

    public function getCargoPrice()
    {
        return $this->cargo_price;
    }

    // 货物类型
    public function setCargoType($cargo_type)
    {
        $this->cargo_type = $cargo_type;
        return $this;
    }

    public function getCargoType()
    {
        return $this->cargo_type;
    }

    // 货物重量
    public function setCargoWeight($cargo_weight)
    {
        $this->cargo_weight = $cargo_weight;
        return $this;
    }

    public function getCargoWeight()
    {
        return $this->cargo_weight;
    }

    // 备注信息
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    public function getInfo()
    {
        return $this->info;
    }

    // 发票抬头
    public function setInvoiceTitle($invoice_title)
    {
        $this->invoice_title = $invoice_title;
        return $this;
    }

    public function getInvoiceTitle()
    {
        return $this->invoice_title;
    }

    // 是否需要收货码
    public function setIsFinishCodeNeeded($is_finish_code_needed)
    {
        $this->is_finish_code_needed = $is_finish_code_needed;
        return $this;
    }

    public function getIsFinishCodeNeeded()
    {
        return $this->is_finish_code_needed;
    }

    // 是否垫付
    public function setIsPrepay($is_prepay)
    {
        $this->is_prepay = $is_prepay;
        return $this;
    }

    public function getIsPrepay()
    {
        return $this->is_prepay;
    }

    // 是否保价
    public function setIsUseInsurance($is_use_insurance)
    {
        $this->is_use_insurance = $is_use_insurance;
        return $this;
    }

    public function getIsUseInsurance()
    {
        return $this->is_use_insurance;
    } // 是否保价

    public function setIsExpectFinishOrder($is_expect_finish_order)
    {
        $this->is_expect_finish_order = $is_expect_finish_order;
        return $this;
    }

    public function getIsExpectFinishOrder()
    {
        return $this->is_expect_finish_order;
    }

    // 订单ID
    public function setOriginId($origin_id)
    {
        $this->origin_id = $origin_id;
        return $this;
    }

    public function getOriginId()
    {
        return $this->origin_id;
    }

    // 取货位置
    public function setPickUpPos($pick_up_pos)
    {
        $this->pick_up_pos = $pick_up_pos;
        return $this;
    }

    public function getPickUpPos()
    {
        return $this->pick_up_pos;
    }

    // 商品列表
    public function setProductList($product_list)
    {
        $this->product_list = $product_list;
        return $this;
    }

    public function getProductList()
    {
        return $this->product_list;
    }

    // 添加单个商品
    public function addProductItem($product_item)
    {
        $this->product_list[] = $product_item;
        return $this;
    }

    // 收货人地址
    public function setReceiverAddress($receiver_address)
    {
        $this->receiver_address = $receiver_address;
        return $this;
    }

    public function getReceiverAddress()
    {
        return $this->receiver_address;
    }

    // 收货人纬度
    public function setReceiverLat($receiver_lat)
    {
        $this->receiver_lat = $receiver_lat;
        return $this;
    }

    public function getReceiverLat()
    {
        return $this->receiver_lat;
    }

    // 收货人经度
    public function setReceiverLng($receiver_lng)
    {
        $this->receiver_lng = $receiver_lng;
        return $this;
    }

    public function getReceiverLng()
    {
        return $this->receiver_lng;
    }

    // 收货人姓名
    public function setReceiverName($receiver_name)
    {
        $this->receiver_name = $receiver_name;
        return $this;
    }

    public function getReceiverName()
    {
        return $this->receiver_name;
    }

    // 收货人电话
    public function setReceiverPhone($receiver_phone)
    {
        $this->receiver_phone = $receiver_phone;
        return $this;
    }

    public function getReceiverPhone()
    {
        return $this->receiver_phone;
    }

    // 门店编号
    public function setShopNo($shop_no)
    {
        $this->shop_no = $shop_no;
        return $this;
    }

    public function getShopNo()
    {
        return $this->shop_no;
    }

    // 小费
    public function setTips($tips)
    {
        $this->tips = $tips;
        return $this;
    }

    public function getTips()
    {
        return $this->tips;
    }

    // 业务类型
    public function setBusinessType($business_type)
    {
        $this->business_type = $business_type;
        return $this;
    }

    public function getBusinessType()
    {
        return $this->business_type;
    }

    // 商家地址
    public function setSupplierAddress($supplier_address)
    {
        $this->supplier_address = $supplier_address;
        return $this;
    }

    public function getSupplierAddress()
    {
        return $this->supplier_address;
    }

    // 商家纬度
    public function setSupplierLat($supplier_lat)
    {
        $this->supplier_lat = $supplier_lat;
        return $this;
    }

    public function getSupplierLat()
    {
        return $this->supplier_lat;
    }

    // 商家经度
    public function setSupplierLng($supplier_lng)
    {
        $this->supplier_lng = $supplier_lng;
        return $this;
    }

    public function getSupplierLng()
    {
        return $this->supplier_lng;
    }

    // 商家名称
    public function setSupplierName($supplier_name)
    {
        $this->supplier_name = $supplier_name;
        return $this;
    }

    public function getSupplierName()
    {
        return $this->supplier_name;
    }

    // 商家电话
    public function setSupplierPhone($supplier_phone)
    {
        $this->supplier_phone = $supplier_phone;
        return $this;
    }

    public function getSupplierPhone()
    {
        return $this->supplier_phone;
    }

    // 将对象转换为数组
    public function toArray()
    {
        return [
            'callback' => $this->callback,
            'cargo_num' => $this->cargo_num,
            'cargo_price' => $this->cargo_price,
            'cargo_type' => $this->cargo_type,
            'cargo_weight' => $this->cargo_weight,
            'info' => $this->info,
            'invoice_title' => $this->invoice_title,
            'is_finish_code_needed' => $this->is_finish_code_needed ? 1 : 0,
            'is_prepay' => $this->is_prepay ? 1 : 0,
            'is_use_insurance' => $this->is_use_insurance ? 1 : 0,
            'origin_id' => $this->origin_id,
            'pick_up_pos' => $this->pick_up_pos,
            'product_list' => $this->product_list,
            'receiver_address' => $this->receiver_address,
            'receiver_lat' => $this->receiver_lat,
            'receiver_lng' => $this->receiver_lng,
            'receiver_name' => $this->receiver_name,
            'receiver_phone' => $this->receiver_phone,
            'shop_no' => $this->shop_no,
            'tips' => $this->tips,
            'business_type' => $this->business_type,
            'supplier_address' => $this->supplier_address,
            'supplier_lat' => $this->supplier_lat,
            'supplier_lng' => $this->supplier_lng,
            'supplier_name' => $this->supplier_name,
            'supplier_phone' => $this->supplier_phone,
        ];
    }

    // 将对象转换为JSON
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    private function validate()
    {
        if (empty($this->shop_no)) {
            throw new \Exception('门店编号不能为空');
        }
        if (empty($this->origin_id)) {
            throw new \Exception('第三方订单ID不能为空');
        }
        if (empty($this->cargo_price)) {
            throw new \Exception('订单金额不能为空');
        }
        if (empty($this->receiver_name)) {
            throw new \Exception('收货人名称不能为空');
        }
        if (empty($this->receiver_address)) {
            throw new \Exception('收货人地址不能为空');
        }
        if (empty($this->receiver_lat) || empty($this->receiver_lng)) {
            throw new \Exception('收货人地址经纬度不能为空');
        }
        if (empty($this->callback)) {
            throw new \Exception('回调URL不能为空');
        }
        if ($this->cargo_weight <= 0) {
            throw new \Exception('订单重量必须大于0');
        }
        if (empty($this->receiver_phone)) {
            throw new \Exception('收货人手机号不能为空');
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