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

namespace app\api\controller\diy;

use app\service\api\diy_form\DiyFormService;
use core\base\BaseApiController;
use think\Response;

class DiyForm extends BaseApiController
{
    /**
     * 万能表单详情
     * @return Response
     */
    public function info()
    {
        $data = $this->request->params([
            [ 'form_id', '' ],
        ]);
        return success(( new DiyFormService() )->getInfo($data[ 'form_id' ]));
    }

    /**
     * 提交填表记录
     * @return Response
     */
    public function addRecord()
    {
        $data = $this->request->params([
            [ 'form_id', '' ],
            [ 'value', [] ],
            [ 'relate_id', '' ],
        ]);
        return success('SUCCESS', ( new DiyFormService() )->addRecord($data));
    }

    /**
     * 编辑填表记录
     * @return Response
     */
    public function editRecord()
    {
        $data = $this->request->params([
            [ 'record_id', '' ],
            [ 'value', [] ],
            [ 'relate_id', '' ],
        ]);
        return success('SUCCESS', ( new DiyFormService() )->editRecord($data));
    }

    /**
     * 获取表单填写结果信息
     * @return Response
     */
    public function getResult()
    {
        $data = $this->request->params([
            [ 'record_id', '' ],
        ]);
        return success('SUCCESS', ( new DiyFormService() )->getResult($data));
    }

    /**
     * 获取表单填写记录
     * @return Response
     */
    public function getRecord()
    {
        $data = $this->request->params([
            [ 'record_id', '' ],
        ]);
        return success('SUCCESS', ( new DiyFormService() )->getFormRecordInfo($data));
    }

    /**
     * 获取个人资料表单填写记录
     * @return Response
     */
    public function getMemberInfoRecord()
    {
        return success('SUCCESS', ( new DiyFormService() )->getMemberInfoFormRecordInfo());
    }
}
