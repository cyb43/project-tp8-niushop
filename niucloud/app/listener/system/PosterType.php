<?php
declare ( strict_types = 1 );

namespace app\listener\system;


/**
 * 系统海报类型
 */
class PosterType
{
    /**
     * 系统海报
     * @param array $data
     * @return array
     */
    public function handle($data = [])
    {
        return [
            [
                'type' => 'friendspay',
                'addon' => '',
                'name' => '找朋友帮忙付海报',
                'desc' => '找朋友帮忙付，分享后进入帮付页面',
                'icon' => 'static/resource/images/poster/type_friendspay.png'
            ],
        ];

    }
}
