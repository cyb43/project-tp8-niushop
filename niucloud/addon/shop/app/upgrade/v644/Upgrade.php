<?php

namespace addon\shop\app\upgrade\v644;

use addon\shop\app\listener\diy\ThemeColorListener;
use addon\shop\app\model\goods\Brand;
use app\service\admin\diy\DiyService;

class Upgrade
{

    public function handle()
    {
        $this->handleData();
        $this->handleThemeColorData();
    }

    /**
     * 处理商品数据
     */
    private function handleData()
    {
        try {

            $brand_model = new Brand();
            // 更新商品品牌自定义颜色
            $brand_model->where([ [ 'brand_id', '>', 0 ] ])->update([
                'color_json' => [
                    'text_color' => 'rgba(255, 255, 255, 1)',
                    'bg_color' => 'rgba(255, 65, 66, 1)',
                    'border_color' => ''
                ]
            ]);
        } catch (\Exception $e) {

        }

    }

    /**
     * 处理主题风格配色数据
     */
    private function handleThemeColorData()
    {
        $listener = new ThemeColorListener();
        $addon_theme = $listener->handle([ 'key' => 'shop' ]);
        $diy_service = new DiyService();
        $diy_service->initAddonThemeColorData('shop', $addon_theme);
    }

}
