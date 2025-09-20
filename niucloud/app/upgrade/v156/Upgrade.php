<?php

namespace app\upgrade\v156;


use app\model\diy\Diy;
use app\model\diy_form\DiyForm;

class Upgrade
{

    public function handle()
    {
        $this->handleDiyData();
        $this->handleDiyFormData();
    }

    /**
     * 处理自定义数据
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function handleDiyData()
    {
        $diy_model = new Diy();
        $where = [
            [ 'value', '<>', '' ],
        ];
        $field = 'id,value';
        $list = $diy_model->where($where)->field($field)->select()->toArray();

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $diy_data = json_decode($v[ 'value' ], true);

                if (!isset($diy_data[ 'global' ][ 'topStatusBar' ][ 'control' ])) {
                    $diy_data[ 'global' ][ 'topStatusBar' ][ 'control' ] = true;
                }

                if (!isset($diy_data[ 'global' ][ 'bottomTabBar' ])) {
                    $diy_data[ 'global' ][ 'bottomTabBar' ] = [
                        'control' => true,
                        'isShow' => true
                    ];
                }

                if (isset($diy_data[ 'global' ][ 'bottomTabBarSwitch' ])) {
                    unset($diy_data[ 'global' ][ 'bottomTabBarSwitch' ]);
                }

                $diy_data = json_encode($diy_data);
                $diy_model->where([ [ 'id', '=', $v[ 'id' ] ] ])->update([ 'value' => $diy_data ]);
            }
        }

    }

    /**
     * 处理万能表单数据
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function handleDiyFormData()
    {
        $diy_form_model = new DiyForm();
        $where = [
            [ 'value', '<>', '' ]
        ];
        $field = 'form_id,value';
        $list = $diy_form_model->where($where)->field($field)->select()->toArray();

        if (!empty($list)) {
            foreach ($list as $k => $v) {

                if (!isset($v[ 'value' ][ 'global' ][ 'topStatusBar' ][ 'control' ])) {
                    $v[ 'value' ][ 'global' ][ 'topStatusBar' ][ 'control' ] = true;
                }

                if (!isset($v[ 'value' ][ 'global' ][ 'bottomTabBar' ])) {
                    $v[ 'value' ][ 'global' ][ 'bottomTabBar' ] = [
                        'control' => true,
                        'isShow' => true
                    ];
                }

                if (isset($v[ 'value' ][ 'global' ][ 'bottomTabBarSwitch' ])) {
                    unset($v[ 'value' ][ 'global' ][ 'bottomTabBarSwitch' ]);
                }

                $diy_form_model->where([ [ 'form_id', '=', $v[ 'form_id' ] ] ])->update([ 'value' => $v[ 'value' ] ]);
            }
        }

    }

}
