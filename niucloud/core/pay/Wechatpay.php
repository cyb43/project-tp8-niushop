<?php

namespace core\pay;

use app\dict\common\ChannelDict;
use app\dict\pay\OnlinePayDict;
use app\dict\pay\RefundDict;
use app\dict\pay\TransferDict;
use core\exception\PayException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use think\facade\Log;
use think\Response;
use Throwable;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\CancelPlugin;
use Yansongda\Supports\Collection;


/**
 * 微信支付管理驱动类  todo  注意:暂时不考虑合单类业务
 * Class FileDriver
 * @package core\file
 */
class Wechatpay extends BasePay
{

    /**
     * @param array $config
     * @return void
     * @throws ContainerException
     */
    protected function initialize(array $config = [])
    {
        $this->config = $config;
        $config['mch_secret_cert'] = url_to_path($config['mch_secret_cert'] ?? '');
        $config['mch_public_cert_path'] = url_to_path($config['mch_public_cert_path'] ?? '');
        // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
        $config['mode'] = Pay::MODE_NORMAL;
        if (!empty($config['wechat_public_cert_path']) && !empty($config['wechat_public_cert_id'])) {
            $config['wechat_public_cert_path'] = [
                $config['wechat_public_cert_id'] => url_to_path($config['wechat_public_cert_path'])
            ];
        } else {
            unset($config['wechat_public_cert_path']);
            unset($config['wechat_public_cert_id']);
        }
        Pay::config($this->payConfig($config, 'wechat'));
    }


    /**
     * 公众号支付
     * @param array $params
     * @return mixed|Collection
     */
    public function mp(array $params)
    {
        try {
            $result = $this->returnFormat(Pay::wechat()->mp([
                'out_trade_no' => $params['out_trade_no'],
                'description' => $params['body'],
                'amount' => [
                    'total' => $params['money'],
                ],
                'payer' => [
                    'openid' => $params['openid'],
                ],
            ]));
            $code = $result['code'] ?? 0;
            if ($code == 0) return $result;
            //支付错误抛出
            throw new PayException($result['message']);
        } catch (\Exception  $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 手机网页支付
     * @param array $params
     * @return mixed
     */
    public function wap(array $params)
    {
        try {
            $order = [
                'out_trade_no' => $params['out_trade_no'],
                'description' => $params['body'],
                'amount' => [
                    'total' => $params['money'],
                ],
                'scene_info' => [
                    'payer_client_ip' => request()->ip(),
                    'h5_info' => [
                        'type' => 'Wap',
                    ]
                ],
            ];
            //这儿有些特殊, 默认情况下，H5 支付所使用的 appid 是微信公众号的 appid，即配置文件中的 mp_app_id 参数，如果想使用关联的小程序的 appid，则只需要在调用参数中增加 ['_type' => 'mini'] 即可
            if (!empty($order['type'])) {
                $order['_type'] = 'mini'; // 注意这一行
            }
            return $this->returnFormat(Pay::wechat()->h5($order));
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }
    }

    public function web(array $params)
    {

    }

    /**
     * app支付
     * @param array $params
     * @return mixed|ResponseInterface
     */
    public function app(array $params)
    {
        try {
            return $this->returnFormat(Pay::wechat()->app([
                'out_trade_no' => $params['out_trade_no'],
                'description' => $params['body'],
                'amount' => [
                    'total' => $params['money'],
                ],
            ]));
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 小程序支付
     * @param array $params
     * @return mixed|ResponseInterface
     */
    public function mini(array $params)
    {
        try {
            return $this->returnFormat(Pay::wechat()->mini([
                'out_trade_no' => $params['out_trade_no'],
                'description' => $params['body'],
                'amount' => [
                    'total' => $params['money'],
                    'currency' => 'CNY',//一般是人民币
                ],
                'payer' => [
                    'openid' => $params['openid'],
                ]
            ]));
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 付款码支付
     * @param array $params
     * @return mixed|Collection
     */
    public function pos(array $params)
    {
        try {
            $order = [
                'out_trade_no' => $params['out_trade_no'],
                'body' => $params['body'],
                'total_fee' => $params['money'],
                'spbill_create_ip' => request()->ip(),
                'auth_code' => $params["auth_code"],
            ];
            $result = Pay::wechat()->pos($order);
            return $this->returnFormat($result);
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 扫码支付
     * @param array $params
     * @return mixed|Collection
     */
    public function scan(array $params)
    {
        try {
            return $this->returnFormat(Pay::wechat()->scan([
                'out_trade_no' => $params['out_trade_no'],
                'description' => $params['body'],
                'amount' => [
                    'total' => $params['money'],
                ],
            ]));
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 转账(微信的转账是很多笔的)
     * @param array $params
     * @return array
     */
    public function transfer(array $params)
    {

        $to_data = $params['to_no'];//收款人数据
        $channel = $to_data['channel'] ?? '';//渠道
        $open_id = $to_data['open_id'] ?? '';//openid
        $transfer_scene_id = (string)$to_data['transfer_scene_id'] ?? '';//openid
        $transfer_scene_report_infos = $to_data['transfer_scene_report_infos'] ?? [];//openid
        $user_recv_perception = $to_data['user_recv_perception'] ?? '';//openid
        if(empty($this->config['mch_id']) || empty($this->config['mch_secret_key']) || empty($this->config['mch_secret_cert']) || empty($this->config['mch_public_cert_path'])){
            throw new PayException('WECHAT_TRANSFER_CONFIG_NOT_EXIST');
        }
        $transfer_no = $params['transfer_no'] ?? '';
        $remark = $params['remark'] ?? '';
        $order = [
            '_action' => 'mch_transfer', // 微信官方老版本下线后，此部分可省略
            'out_bill_no' => $transfer_no,
            'transfer_scene_id' => $transfer_scene_id,
            'openid' => $open_id,
            // 'user_name' => '闫嵩达'  // 明文传参即可，sdk 会自动加密
            'transfer_amount' => (int)$params['money'],
            'transfer_remark' => $remark,
            'transfer_scene_report_infos' =>$transfer_scene_report_infos,
            'notify_url' => $this->config['notify_url'],
            'user_recv_perception' => $user_recv_perception
        ];
        if($channel == ChannelDict::WEAPP){
            $order['_type'] = 'mini';
        }
        $tran_status_list = [
            'PROCESSING' => TransferDict::DEALING,
            'ACCEPTED' => TransferDict::DEALING,

            'WAIT_USER_CONFIRM' => TransferDict::WAIT_USER,//等待收款用户确认
            'TRANSFERING' => TransferDict::WAIT_USER_ING,//转账中
            'FAIL' => TransferDict::FAIL,
            'SUCCESS' => TransferDict::SUCCESS,
            'CANCELING' => TransferDict::FAIL_ING,
            'CANCELLED' => TransferDict::FAIL,
        ];
        try {
            $result = $this->returnFormat(Pay::wechat()->transfer($order));
            if (!empty($result['code'])) {
//            if($result['code'] == 'PARAM_ERROR'){
//                throw new PayException();
//            }else if($result['code'] == 'INVALID_REQUEST'){
//                throw new PayException();
//            }
                if ($result['code'] == 'INVALID_REQUEST') {
                    throw new PayException(700010);
                }
                throw new PayException($result['message']);
            }
            $result['mch_id'] = $this->config['mch_id'];
            $result['appid'] =  $channel == ChannelDict::WEAPP ? $this->config['mini_app_id'] : $this->config['mp_app_id'];
            return ['out_bill_no' => $result['out_bill_no'], 'transfer_bill_no' => $result['transfer_bill_no'], 'status' => $tran_status_list[$result['state']], 'reason' => $result['fail_reason'] ?? '', 'package_info' => $result['package_info'] ?? [], 'extra' => $result];
        } catch (\Exception $e) {
//            if($e->getCode() == 9402){
//                return ['batch_id' => '', 'out_batch_no' => $order['out_batch_no'], 'status' => TransferDict::DEALING];
//            }
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }

    }

    /**
     * 支付关闭
     * @param string $out_trade_no
     * @return void
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function close(string $out_trade_no)
    {
        try {
            $result = Pay::wechat()->close([
                'out_trade_no' => $out_trade_no,
            ]);
            return $this->returnFormat($result);
        }catch(Throwable $e){
            return false;
        }
        return true;
    }

    /**
     * 退款
     * @param array $params
     * @return array
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function refund(array $params)
    {
        $out_trade_no = $params['out_trade_no'];
        $money = $params['money'];
        $total = $params['total'];
        $refund_no = $params['refund_no'];
        $result = Pay::wechat()->refund([
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => $refund_no,
            'amount' => [
                'refund' => $money,
                'total' => $total,
                'currency' => 'CNY',
            ],
        ]);
        $result = $this->returnFormat($result);

        $refund_status_array = [
            'SUCCESS' => RefundDict::SUCCESS,
            'CLOSED' => RefundDict::FAIL,
            'PROCESSING' => RefundDict::DEALING,
            'ABNORMAL' => RefundDict::FAIL,
        ];
        return [
            'status' => $refund_status_array[$result['status']],
            'refund_no' => $refund_no,
            'out_trade_no' => $out_trade_no,
            'pay_refund_no' => $result['refund_id']
        ];
    }


    /**
     * 异步回调
     * @param string $action
     * @param callable $callback
     * @return ResponseInterface|Response
     */
    public function notify(string $action, callable $callback)
    {
        try {
            Log::write('wechat_start'.$action);
            $result = $this->returnFormat(Pay::wechat()->callback());

            Log::write('wechat_start_1');

            Log::write($result);
            Log::write('wechat_start_1');
            if ($action == 'pay') {//支付
                if ($result['event_type'] == 'TRANSACTION.SUCCESS') {
                    $pay_trade_data = $result['resource']['ciphertext'];

                    $temp_params = [
                        'trade_no' => $pay_trade_data['transaction_id'],
                        'mch_id' => $pay_trade_data['mchid'],
                        'status' => OnlinePayDict::getWechatPayStatus($pay_trade_data['trade_state'])
                    ];

                    $callback_result = $callback($pay_trade_data['out_trade_no'], $temp_params);
                    if (is_bool($callback_result) && $callback_result) {
                        return Pay::wechat()->success();
                    }
                }
            } else if ($action == 'refund') {//退款
                if ($result['event_type'] == 'REFUND.SUCCESS') {
                    $refund_trade_data = $result['resource']['ciphertext'];
                    $refund_status_array = [
                        'SUCCESS' => RefundDict::SUCCESS,
                        'CLOSED' => RefundDict::FAIL,
                        'PROCESSING' => RefundDict::DEALING,
                        'ABNORMAL' => RefundDict::FAIL,
                    ];
                    $temp_params = [
                        'trade_no' => $refund_trade_data['transaction_id'],
                        'mch_id' => $refund_trade_data['mchid'],
                        'refund_no' => $refund_trade_data['out_refund_no'],
                        'status' => $refund_status_array[$refund_trade_data['refund_status']],
                    ];

                    $callback_result = $callback($refund_trade_data['out_trade_no'], $temp_params);
                    if (is_bool($callback_result) && $callback_result) {
                        return Pay::wechat()->success();
                    }
                }
            }else if ($action == 'transfer') {//转账
                if ($result['event_type'] == 'MCHTRANSFER.BILL.FINISHED') {
                    $refund_trade_data = $result['resource']['ciphertext'];
                    $tran_status_list = [
                        'PROCESSING' => TransferDict::DEALING,
                        'ACCEPTED' => TransferDict::DEALING,

                        'WAIT_USER_CONFIRM' => TransferDict::WAIT_USER,//等待收款用户确认
                        'TRANSFERING' => TransferDict::WAIT_USER_ING,//转账中
                        'FAIL' => TransferDict::FAIL,
                        'SUCCESS' => TransferDict::SUCCESS,
                        'CANCELING' => TransferDict::FAIL_ING,
                        'CANCELLED' => TransferDict::FAIL,
                    ];
                    $temp_params = [
//                        'out_bill_no' => $refund_trade_data['out_bill_no'],
                        'mch_id' => $refund_trade_data['mch_id'],
                        'out_bill_no' => $refund_trade_data['out_bill_no'] ?? '',
                        'transfer_bill_no' => $refund_trade_data['transfer_bill_no'] ?? '',
                        'status' => $tran_status_list[$refund_trade_data['state']],
                        'reason' => $refund_trade_data['fail_reason'] ?? '',
                    ];
                    Log::write('wechat_start_2');

                    Log::write($temp_params);
                    Log::write('wechat_start_2');
                    $callback_result = $callback($refund_trade_data['out_bill_no'], $temp_params);
                    if (is_bool($callback_result) && $callback_result) {
                        return Pay::wechat()->success();
                    }
                }
            }
            return $this->fail();

        } catch ( Throwable $e ) {
//            throw new PayException($e->getMessage());
            Log::write('wechat_error'.$e->getMessage().$e->getLine().$e->getFile());
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 查询普通支付订单
     * @param array $params
     * @return array|MessageInterface|Collection|null
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function getOrder(array $params = [])
    {

        $out_trade_no = $params['out_trade_no'];
        $transaction_id = $params['transaction_id'] ?? '';
        $order = [

        ];
        if (!empty($out_trade_no)) {
            $order['out_trade_no'] = $out_trade_no;
        }
        if (!empty($transaction_id)) {
            $order['transaction_id'] = $transaction_id;
        }
        $result = Pay::wechat()->query($order);
        if (empty($result))
            return $result;
        $result = $this->returnFormat($result);
        return [
            'status' => OnlinePayDict::getWechatPayStatus($result['trade_state']),
        ];
    }

    /**
     * 查询退款单据
     * @param string|null $out_trade_no
     * @param string|null $refund_no
     * @return array|Collection|MessageInterface|null
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function getRefund(?string $out_trade_no, ?string $refund_no = '')
    {
        $order = [
            '_action' => 'refund',
            'transaction_id' => $out_trade_no,
            'out_refund_no' => $refund_no,
        ];
        $result = Pay::wechat()->query($order);
        if (empty($result))
            return $result;
        $result = $this->returnFormat($result);
        $refund_status_array = [
            'SUCCESS' => RefundDict::SUCCESS,
            'CLOSED' => RefundDict::FAIL,
            'PROCESSING' => RefundDict::DEALING,
            'ABNORMAL' => RefundDict::FAIL,
        ];
        return [
            'status' => $refund_status_array[$result['status']],
            'refund_no' => $refund_no,
            'out_trade_no' => $out_trade_no
        ];
    }

    /**
     * 获取转账订单(todo  切勿调用)
     * @param string $transfer_no
     * @return array
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function getTransfer(string $transfer_no, $out_batch_no = '')
    {
        $order = [
            'out_bill_no' => $transfer_no,
            'transfer_bill_no' => $out_batch_no,
            '_action' => 'transfer',
        ];
        $tran_status_list = [
            'PROCESSING' => TransferDict::DEALING,
            'ACCEPTED' => TransferDict::DEALING,

            'WAIT_USER_CONFIRM' => TransferDict::WAIT_USER,//等待收款用户确认
            'TRANSFERING' => TransferDict::WAIT_USER_ING,//转账中
            'FAIL' => TransferDict::FAIL,
            'SUCCESS' => TransferDict::SUCCESS,
            'CANCELING' => TransferDict::FAIL_ING,
            'CANCELLED' => TransferDict::FAIL,
        ];
        try {
            $result = Pay::wechat()->query($order);
            $result = $this->returnFormat($result);
            //微信转账状态
//            $transfer_status_array = [
//                'INIT' => TransferDict::DEALING,//初始态。 系统转账校验中
//                'WAIT_PAY' => TransferDict::DEALING,
//                'PROCESSING' => TransferDict::DEALING,
//                'FAIL' => TransferDict::FAIL,
//                'SUCCESS' => TransferDict::SUCCESS,
//            ];
            return [
                'status' => $tran_status_list[$result['state']],
                'transfer_no' => $transfer_no,
                'reason' => $result['fail_reason'] ?? '',
                'out_bill_no' => $result['out_bill_no'] ?? '',
                'transfer_bill_no' => $result['transfer_bill_no'] ?? ''
            ];
        }catch(Throwable $e){
            return [
                'status' => TransferDict::DEALING,
                'transfer_no' => $transfer_no,
                'reason' => $e->getMessage(),
            ];
        }
    }


    /**
     * 转账取消
     * @param array $params
     * @return array
     */
    public function transferCancel(array $params)
    {
        if(empty($this->config['mch_id']) || empty($this->config['mch_secret_key']) || empty($this->config['mch_secret_cert']) || empty($this->config['mch_public_cert_path'])){
            throw new PayException('WECHAT_TRANSFER_CONFIG_NOT_EXIST');
        }
        $transfer_no = $params['transfer_no'] ?? '';

        $tran_status_list = [
            'CANCELING' => TransferDict::FAIL_ING,
            'CANCELLED' => TransferDict::FAIL,
        ];
        $order = [
            '_action' => 'cancel', // 微信官方老版本下线后，此部分可省略
            'out_bill_no' => $transfer_no,
            'notify_url' => $this->config['notify_url'],
        ];
        try {

            $allPlugins = Pay::wechat()->mergeCommonPlugins([CancelPlugin::class]);

            $result = Pay::wechat()->pay($allPlugins, $order);
//            $result = $this->returnFormat(Pay::wechat()->transfer($order));
            if (!empty($result['code'])) {
//
                if ($result['code'] == 'INVALID_REQUEST') {
                    throw new PayException(700010);
                }
                throw new PayException($result['message']);
            }

            return ['out_bill_no' => $result['out_bill_no'], 'transfer_bill_no' => $result['transfer_bill_no'], 'status' => $tran_status_list[$result['state']]];
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw new PayException($e->response->all()['message'] ?? '');
            }
            throw new PayException($e->getMessage());
        }

    }
    public function fail($message = '')
    {
        $response = [
            'code' => 'FAIL',
            'message' => $message ?: '失败',
        ];
        return response($response, 400, [], 'json');
    }
}
