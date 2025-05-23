<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\service\core\pay;

use app\dict\pay\TransferDict;
use app\model\pay\Pay;
use app\model\pay\Transfer;
use app\model\pay\TransferScene;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use core\exception\PayException;
use Exception;
use think\facade\Db;
use think\facade\Log;
use think\Model;
use Throwable;

/**
 * 支付转账服务层
 * Class CoreTransferService
 * @package app\service\core\pay
 */
class CoreTransferService extends BaseCoreService
{
    protected $pay_event;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Transfer();
        $this->pay_event = new CorePayEventService();
    }

    /**
     * 创建转账单据
     * @param string $main_type
     * @param int $main_id
     * @param float $money
     * @param string $trade_type
     * @param string $remark
     * @return string|null
     * @throws Exception
     */
    public function create(string $main_type, int $main_id, float $money, string $trade_type, string $remark)
    {
        $transfer_no = create_no();
        $transfer_data = array(
            'money' => $money,
            'trade_type' => $trade_type,
            'transfer_no' => $transfer_no,
            'main_id' => $main_id,
            'main_type' => $main_type,
            'transfer_status' => TransferDict::WAIT,
            'remark' => $remark,
        );
        $this->model->create($transfer_data);
        return $transfer_no;
    }


    /**
     * 转账
     * @param string $transfer_no
     * @param string $transfer_type
     * @param array $data
     * @return true
     */
    public function transfer(string $transfer_no, string $transfer_type, array $data)
    {
        $transfer = $this->findTransferByTransferNo($transfer_no);

        if ($transfer->isEmpty()) throw new PayException('TRANSFER_ORDER_INVALID');
        if (!in_array($transfer[ 'transfer_status' ], [ TransferDict::WAIT, TransferDict::FAIL ])) throw new PayException('TRANFER_STATUS_NOT_IN_WAIT_TANSFER');

        $transfer_account = $data[ 'transfer_account' ] ?? '';
        $transfer_realname = $data[ 'transfer_realname' ] ?? '';
        $transfer_data = array(
            'transfer_type' => $transfer_type,//转账方式
            'transfer_realname' => $transfer_realname,//名称
            'transfer_mobile' => $data[ 'transfer_mobile' ] ?? '',//手机号
            'transfer_bank' => $data[ 'transfer_bank' ] ?? '',//转账银行
            'transfer_account' => $transfer_account,//转账账号
            'openid' => $data[ 'openid' ] ?? '',
            'transfer_voucher' => $data[ 'transfer_voucher' ] ?? '',
            'transfer_remark' => $data[ 'transfer_remark' ] ?? '',
            'transfer_payee' => $data[ 'transfer_payee' ] ?? [],
            'transfer_payment_code' => $data[ 'transfer_payment_code' ] ?? ''
        );
        $transfer->save($transfer_data);
        switch ($transfer_type) {
            case TransferDict::WECHAT:
//                $out_batch_no = create_no();
                $transfer_account = $data[ 'transfer_payee' ] ?? [];
                $scene_data = ( new CoreTransferSceneService() )->getSceneInfoByType($transfer[ 'trade_type' ]);
                //通过业务获取业务场景
                $temp_infos = $scene_data[ 'infos' ] ?? [];//转账场景信息
                if (!empty($temp_infos)) {
                    $transfer_scene_report_infos = [];
                    foreach ($temp_infos as $key => $item) {
                        $transfer_scene_report_infos[] = [
                            'info_type' => $key,
                            'info_content' => $item
                        ];
                    }
                }
                $transfer_account[ 'transfer_scene_report_infos' ] = $transfer_scene_report_infos ?? [];//
                $transfer_account[ 'user_recv_perception' ] = $scene_data[ 'perception' ];//收款感知
                $transfer_account[ 'transfer_scene_id' ] = $scene_data[ 'scene_id' ];
//                $transfer_account['out_batch_no'] = $out_batch_no;
                break;
        }
        $params = [];
        $return_result = [];
        if (TransferDict::getTransferType()[ $transfer_type ][ 'is_online' ]) {
            try {
                $result = $this->pay_event->init('transfer', $transfer_type)->transfer($transfer[ 'money' ], $transfer_no, $transfer_account, $transfer_realname, $transfer[ 'remark' ]);
//                $params['batch_id'] = $result['batch_id'];
//                $params['out_batch_no'] = $result['out_batch_no'];
                //将返回的数据交给转账通知

                if ($transfer_type == TransferDict::WECHAT) {
                    $update_data = [
                        'out_batch_no' => $result[ 'transfer_bill_no' ] ?? '',
                        'package_info' => $result[ 'package_info' ] ?? '',
                        'extra' => $result[ 'extra' ] ?? [],
                    ];
                    $transfer->save($update_data);
                    $return_result[ 'status' ] = $result[ 'status' ];
                    $return_result[ 'package_info' ] = $result[ 'package_info' ];
                    $return_result[ 'extra' ] = $result[ 'extra' ] ?? [];
                }

                $this->transferNotify($transfer_no, $result);
//                return true;
            } catch (Throwable $e) {
                $this->fail($transfer_no, [ 'reason' => get_lang($e->getMessage()) ]);
                throw new PayException($e->getMessage());
            }
        } else {
            $return_result = [
                'status' => TransferDict::SUCCESS,
            ];
            $this->success($transfer_no, $params);
        }

        return $return_result;
    }

    /**
     * 通过转账单号查询转账
     * @param string $transfer_no
     * @return Pay|array|mixed|Model
     */
    public function findTransferByTransferNo(string $transfer_no)
    {
        $where = array(
            [ 'transfer_no', '=', $transfer_no ]
        );
        return $this->model->where($where)->findOrEmpty();
    }


    /**
     * 转账回调
     * @param string $transfer_no
     * @param $params
     * @return true
     */
    public function transferNotify(string $transfer_no, $params)
    {
        Log::write('transferNotify' . $transfer_no);
        $transfer = $this->findTransferByTransferNo($transfer_no);
        if ($transfer->isEmpty()) throw new PayException('TRANSFER_ORDER_INVALID');
        if (!in_array($transfer[ 'transfer_status' ], [ TransferDict::WAIT, TransferDict::DEALING, TransferDict::FAIL, TransferDict::WAIT_USER, TransferDict::WAIT_USER_ING, TransferDict::FAIL_ING ])) throw new PayException('TRANFER_STATUS_NOT_IN_WAIT_TANSFER');
        $status = $params[ 'status' ] ?? TransferDict::DEALING;
        Log::write('transferNotifyStatus' . $status);
        switch ($status) {
            case TransferDict::SUCCESS:
                Log::write('transferNotifyStatus1' . $status);
                $this->success($transfer_no);
                break;
            case TransferDict::FAIL:
                $this->fail($transfer_no, $params);
                break;
//            case TransferDict::DEALING:
//                $this->dealing($transfer_no);
//                break;
            default:
                $this->dealing($transfer_no, $status);
                break;
        }
        return true;
    }

    /**
     * 转账完成
     * @param string $transfer_no
     * @param string $transfer_type
     * @param array $params
     * @return bool
     */
    public function success(string $transfer_no, array $params = [])
    {
        $transfer = $this->findTransferByTransferNo($transfer_no);
        Log::write('transferNotifyStatus2' . $transfer[ 'transfer_status' ]);
        if ($transfer->isEmpty()) throw new PayException('TRANSFER_ORDER_INVALID');
        if (!in_array($transfer[ 'transfer_status' ], [ TransferDict::WAIT, TransferDict::DEALING, TransferDict::FAIL, TransferDict::WAIT_USER, TransferDict::WAIT_USER_ING, TransferDict::FAIL_ING ])) throw new PayException('TRANFER_STATUS_NOT_IN_WAIT_TANSFER');

        $trade_type = $transfer->trade_type;
        $data = [
            'transfer_time' => time(),
            'transfer_status' => TransferDict::SUCCESS,
//            'batch_id' => $params['batch_id'] ?? '',
//            'out_batch_no' => $params['out_batch_no'] ?? '',
//            'transfer_type' => $transfer_type,
        ];
        // 启动事务
        Db::startTrans();
        try {
            $transfer->save($data);
            Log::write('transferNotifyStatus3' . TransferDict::SUCCESS);
            $result = event('TransferSuccess', [ 'transfer_no' => $transfer_no, 'trade_type' => $trade_type ]);
            if (!check_event_result($result)) {
                Db::rollback();
                return false;
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (Throwable $e) {
            // 回滚事务
            Db::rollback();
            throw new PayException($e->getMessage());
        }

    }

    /**
     * 转账失败
     * @param string $transfer_no
     * @param string $transfer_type
     * @param array $params
     * @return true
     */
    public function fail(string $transfer_no, array $params = [])
    {
        $transfer = $this->findTransferByTransferNo($transfer_no);

        if ($transfer->isEmpty()) throw new PayException('TRANSFER_ORDER_INVALID');
        if (!in_array($transfer[ 'transfer_status' ], [ TransferDict::WAIT, TransferDict::DEALING, TransferDict::FAIL, TransferDict::WAIT_USER, TransferDict::WAIT_USER_ING, TransferDict::FAIL_ING ])) throw new PayException('TRANFER_STATUS_NOT_IN_WAIT_TANSFER');
        $data = array(
            'transfer_time' => time(),
            'transfer_status' => TransferDict::FAIL,
            'transfer_fail_reason' => $params[ 'reason' ] ?? ''
        );
        //允许修改的值

        $transfer->save($data);
        return true;
    }


    public function check(array $data)
    {
        $transfer_no = $data[ 'transfer_no' ];
        $transfer = $this->findTransferByTransferNo($transfer_no);
        if ($transfer->isEmpty()) throw new PayException('TRANSFER_ORDER_INVALID');
        if (!in_array($transfer[ 'transfer_status' ], [ TransferDict::WAIT, TransferDict::DEALING, TransferDict::FAIL, TransferDict::WAIT_USER, TransferDict::WAIT_USER_ING, TransferDict::FAIL_ING ])) throw new PayException('TRANFER_IS_CHANGE');//只有待转账和转账中的订单可以校验

        //查询第三方支付单据
        $transfer_info = $this->pay_event->init('transfer', $transfer->transfer_type)->getTransfer($transfer_no, $transfer[ 'out_batch_no' ] ?? '');
        if (empty($transfer_info)) throw new PayException('TRANSFER_ORDER_INVALID');//查询不到转账信息
        $status = $transfer_info[ 'status' ];
        switch ($status) {
            case TransferDict::SUCCESS:
                $this->success($transfer_no);
                break;

            case TransferDict::FAIL:
                $this->fail($transfer_no);
                break;
//            case TransferDict::DEALING:
            default:
                $this->dealing($transfer_no, $status);
                break;
        }
        return $status;
    }

    /**
     * 处理中
     * @param $transfer_no
     * @param $type
     * @return true
     */
    public function dealing($transfer_no, $status)
    {
        $this->model->where([
            [ 'transfer_no', '=', $transfer_no ]
        ])->update(
            [
//                'transfer_status' => TransferDict::DEALING,
                'transfer_status' => $status,
            ]
        );
        return true;
    }

    /**
     * 用户确认转账信息
     * @param string $transfer_no
     * @param string $channel
     * @param array $data
     * @return void
     */
//    public function confirm(string $transfer_no, string $channel, array $data ){
//        $transfer_no = $data['transfer_no'];
//        $transfer = $this->findTransferByTransferNo($transfer_no);
//        if($transfer['transfer_type'] == TransferDict::WECHAT){
//            //todo 判断微信转账是否已经超时
//
//            //todo 判断转账的主体授权信息是否一致
//            $transfer_payee = $transfer['transfer_payee'];
//            $transfer_open_id = $transfer_payee['open_id'];
//            $open_id = $data['open_id'];
//
//        }
//    }

    /**
     * 撤销转账
     * @param string $transfer_no
     * @return void
     */
    public function cancel(string $transfer_no)
    {

        try {
            $transfer = $this->findTransferByTransferNo($transfer_no);
            if ($transfer->isEmpty()) throw new PayException('TRANSFER_ORDER_INVALID');
            if (!in_array($transfer[ 'transfer_status' ], [ TransferDict::WAIT, TransferDict::DEALING, TransferDict::FAIL, TransferDict::WAIT_USER, TransferDict::WAIT_USER_ING, TransferDict::FAIL_ING ])) throw new PayException('TRANFER_IS_CHANGE');//只有待转账和转账中的订单可以校验

            //查询第三方支付单据
            $result = $this->pay_event->init('transfer', $transfer->transfer_type)->transferCancel([
                'transfer_no' => $transfer_no
            ]);
            $this->transferNotify($transfer_no, $result);
            if ($result[ 'status' ] == TransferDict::FAIL_ING) {//撤销中的话也要返回错误,不能让业务认为撤销成功了
                throw new PayException('TRANSFER_IS_FAILING');
            }
            return true;
        } catch (Throwable $e) {
            throw new PayException($e->getMessage());
        }

    }

}
