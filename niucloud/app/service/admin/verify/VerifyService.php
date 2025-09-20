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

namespace app\service\admin\verify;

use app\model\verify\Verify;
use app\service\core\verify\CoreVerifyService;
use core\base\BaseAdminService;

/**
 * 订单核销
 * Class VerifyService
 * @package app\service\admin\verify
 */
class VerifyService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Verify();
    }

    /**
     * 获取核销记录列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getPage(array $where = [])
    {
        $search_model = $this->model->where([['id', '>', 0]])->withSearch(['code', 'type', 'create_time', 'verifier_member_id', 'relate_tag', 'keyword', 'order_id'], $where)
            ->with(['member' => function ($query) {
                $query->field('member_id, nickname, mobile, headimg');
            }])->field('*')->order('create_time desc')->append(['type_name']);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取核销记录列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getList(array $where = [])
    {
        $list = $this->model->where([['id', '>', 0]])->withSearch(['code', 'type', 'create_time', 'verifier_member_id', 'relate_tag', 'keyword', 'order_id'], $where)
            ->with(['member' => function ($query) {
                $query->field('member_id, nickname, mobile, headimg');
            }])->field('*')->order('create_time desc')->append(['type_name'])->select()->toArray();
        return $list;
    }

    /**
     * 获取核销信息
     * @param string $verify_code
     * @return array
     */
    public function getDetail(string $verify_code)
    {
        $info = $this->model->where([
            ['code', '=', $verify_code]
        ])->field('*')
            ->with(['member' => function ($query) {
                $query->field('member_id, nickname, mobile, headimg');
            }])->append(['type_name'])->findOrEmpty()->toArray();

        $info['verify_info'] = event('VerifyInfo', $info);
        return $info;

    }

    /**
     * 框架后台核销
     * @param string $verify_code
     * @param $num
     * @return true
     */
    public function verify(string $verify_code, $num = 1)
    {

        return (new CoreVerifyService())->adminVerify($verify_code);
    }

    /**
     * 框架后台核销
     * @param string $verify_code
     * @param $num
     * @return true
     */
    public function getInfoByCode(string $verify_code)
    {

        return (new CoreVerifyService())->adminGetInfoByCode($verify_code);

    }

}
