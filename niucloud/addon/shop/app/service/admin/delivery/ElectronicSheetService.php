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

namespace addon\shop\app\service\admin\delivery;

use addon\shop\app\dict\delivery\ElectronicSheetDict;
use addon\shop\app\model\delivery\ElectronicSheet;
use addon\shop\app\service\core\delivery\CoreElectronicSheetService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use Exception;
use think\facade\Db;


/**
 * 电子面单服务层
 * Class ElectronicSheetService
 * @package addon\shop\app\service\admin\delivery
 */
class ElectronicSheetService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ElectronicSheet();
    }

    /**
     * 获取电子面单分页列表
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getPage(array $where = [], $field = 'id,template_name,express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,status,exp_type,print_style,is_default,create_time')
    {
        $order = 'is_default desc,create_time desc';

        $search_model = $this->model->where([ [ 'electronic_sheet.id', '>', 0 ] ])
            ->withSearch([ 'id', 'template_name', 'express_company_id', 'pay_type', 'is_notice', 'status', 'is_default' ], $where)
            ->field($field)
            ->withJoin([
                'company' => [ 'company_name', 'express_no_electronic_sheet' ]
            ])
            ->order($order)->append([ 'pay_type_name' ]);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取电子面单列表
     * @param array $where
     * @param array $field
     * @return array
     */
    public function getList(array $where = [], $field = 'id,template_name,express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,status,exp_type,print_style,is_default,create_time')
    {
        $order = 'is_default desc,create_time desc';
        return $this->model->where([ [ 'id', '>', 0 ] ])->withSearch([ 'id', 'template_name', 'express_company_id', 'pay_type', 'is_notice', 'status', 'is_default' ], $where)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 获取电子面单信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'id,template_name,express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,status,exp_type,print_style,is_default';

        $info = $this->model->field($field)->where([ [ 'id', '=', $id ] ])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 添加电子面单
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        // 将同类型页面的默认值改为0，默认页面只有一个
        if (!empty($data[ 'is_default' ])) {
            $this->model->where([ [ 'id', '>', 0 ] ])->update([ 'is_default' => 0 ]);
        }

        $interface_data = [
            'kdbird'=>[
                'customer_name'=>$data['customer_pwd'],
                'customer_pwd'=>$data['customer_pwd'],
                'send_site'=>$data['send_site'],
                'send_staff'=>$data['send_staff'],
                'month_code'=>$data['month_code'],
                'pay_type'=>$data['pay_type'],
                'is_notice'=>$data['is_notice'],
                'print_style'=>$data['print_style']
            ],
//            'kd100'=>[
//                'customer_name'=>$data['kd100_customer_pwd'],
//                'customer_pwd'=>$data['kd100_customer_pwd'],
//                'send_site'=>$data['kd100_send_site'],
//                'send_staff'=>$data['kd100_send_staff'],
//                'month_code'=>$data['kd100_month_code'],
//                'pay_type'=>$data['kd100_pay_type'],
//            ]
        ];
        $data['interface_data'] = json_encode($interface_data);
        $res = $this->model->create($data);
        return $res->id;
    }

    /**
     * 电子面单编辑
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        // 将同类型页面的默认值改为0，默认页面只有一个
        if (!empty($data[ 'is_default' ])) {
            $this->model->where([ [ 'id', '>', 0 ] ])->update([ 'is_default' => 0 ]);
        }

        $interface_data = [
            'kdbird'=>[
                'customer_name'=>$data['customer_pwd'],
                'customer_pwd'=>$data['customer_pwd'],
                'send_site'=>$data['send_site'],
                'send_staff'=>$data['send_staff'],
                'month_code'=>$data['month_code'],
                'pay_type'=>$data['pay_type'],
                'is_notice'=>$data['is_notice'],
                'print_style'=>$data['print_style']
            ],
//            'kd100'=>[
//                'customer_name'=>$data['kd100_customer_pwd'],
//                'customer_pwd'=>$data['kd100_customer_pwd'],
//                'send_site'=>$data['kd100_send_site'],
//                'send_staff'=>$data['kd100_send_staff'],
//                'month_code'=>$data['kd100_month_code'],
//                'pay_type'=>$data['kd100_pay_type'],
//            ]
        ];
        $data['interface_data'] = json_encode($interface_data);
        $this->model->where([ [ 'id', '=', $id ] ])->update($data);
        return true;
    }

    /**
     * 删除电子面单
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        $model = $this->model->where([ [ 'id', '=', $id ] ])->find();
        $res = $model->delete();
        return $res;
    }

    /**
     * 设置默认电子面单模板
     * @param int $id
     * @return bool
     */
    public function setDefault(int $id)
    {
        try {
            $info = $this->getInfo($id);
            if (empty($info)) {
                return false;
            }
            Db::startTrans();
            $this->model->where([ [ 'id', '>', 0 ] ])->update([ 'is_default' => 0, 'update_time' => time() ]);
            $this->model->where([ [ 'id', '=', $id ] ])->update([ 'is_default' => 1, 'update_time' => time() ]);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new AdminException($e->getMessage());
        }
        return true;
    }

    /**
     * 设置 电子面单配置
     * @param $data
     * @return bool
     */
    public function setConfig($data)
    {
        ( new CoreElectronicSheetService() )->setElectronicSheetConfig( $data);
        return true;
    }

    /**
     * 获取 电子面单设置
     * @return array|mixed
     */
    public function getConfig()
    {
        return ( new CoreElectronicSheetService() )->getElectronicSheetConfig();
    }

    /**
     * 获取邮费支付方式类型
     * @return array|mixed|string
     */
    public function getPayType()
    {
        return ElectronicSheetDict::getPayType();
    }

    /**
     * 打印电子面单
     * @param array $data
     * @return mixed
     */
    public function printElectronicSheet($data = [])
    {
        $res = ( new CoreElectronicSheetService() )->printElectronicSheet($data);
        return $res;
    }

}
