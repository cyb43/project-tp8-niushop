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

namespace app\dict\common;


/**
 * 渠道枚举类
 * Class ChannelDict
 * @package app\dict\common
 */
class CommonActiveDict
{
    const IMPULSE_BUY = 'impulse_buy';//顺手买     顺
    const GIFTCARD = 'gift_card';//礼品卡  礼

    const DISCOUNT = 'discount';// 限时折扣     折
    const EXCHANGE = 'exchange';// 积分商城     积
    const MANJIANSONG = 'manjiansong'; // 满减送   满减
    const NEWCOMER_DISCOUNT = 'newcomer_discount'; // 新人专享  新
    const PINTUAN = 'pintuan'; // 新人专享  新
    const SECKILL = 'seckill'; // 秒杀  秒

    public static function getActiveShort($active = '')
    {
        $data = [
            self::IMPULSE_BUY => [
                'name' => get_lang('common_active_short.impulse_buy_short'),
                'active_name' => get_lang('common_active_short.impulse_buy_name'),
                'bg_color' => "#FF7700"
            ],
            self::GIFTCARD => [
                'name' => get_lang('common_active_short.gift_card_short'),
                'active_name' => get_lang('common_active_short.gift_card_name'),
                'bg_color' => '#F00000'
            ],
            self::DISCOUNT => [
                'name' => get_lang('common_active_short.discount_short'),
                'active_name' => get_lang('common_active_short.discount_name'),
                'bg_color' => '#FFA322'
            ],
            self::EXCHANGE => [
                'name' => get_lang('common_active_short.exchange_short'),
                'active_name' => get_lang('common_active_short.exchange_name'),
                'bg_color' => '#00C441'
            ],
            self::MANJIANSONG => [
                'name' => get_lang('common_active_short.manjiansong_short'),
                'active_name' => get_lang('common_active_short.manjiansong_name'),
                'bg_color' => '#249DE9'
            ],
            self::NEWCOMER_DISCOUNT => [
                'name' => get_lang('common_active_short.newcomer_discount_short'),
                'active_name' => get_lang('common_active_short.newcomer_discount_name'),
                'bg_color' => '#BB27FF'
            ],
            self::SECKILL => [
                'name' => get_lang('common_active_short.seckill_short'),
                'active_name' => get_lang('common_active_short.seckill_name'),
                'bg_color' => '#F606CA'
            ],
            self::PINTUAN => [
                'name' => get_lang('common_active_short.pintuan_short'),
                'active_name' => get_lang('common_active_short.pintuan_name'),
                'bg_color' => '#FF1C77'
            ],
        ];
        return !empty($active) ? $data[$active] ?? [] : $data;
    }

    // 活动背景特效
    const ACTIVE_BG_EFFECT = [
        'normal' => [
            'title' => '无特效',
            'child_list' => []
        ],
        'promotion' => [
            'title' => '促销',
            'child_list' => [
                'red_packet' => '红包',
                'gift_box' => '礼盒',
                'big_wing' => '大元宝'
            ]
        ],
//        'graph' => [
//            'title' => '图形',
//            'child_list' => [
//                'english_letters' => '英文字母',
//                'lantern' => '灯笼',
//                'love' => '爱心'
//            ]
//        ],
//        'natural' => [
//            'title' => '自然',
//            'child_list' => [
//                'roses' => '玫瑰花',
//                'petals' => '花瓣',
//            ]
//        ],
        'festival' => [
            'title' => '节庆',
            'child_list' => [
//                'santa_claus' => '圣诞老人',
//                'christmas_trees' => '圣诞树',
                'blessing' => '花瓣',
                'firecrackers' => '爆竹',
                'i_love_you' => '我爱你',
                'kongmin_light' => '孔明灯',
//                'zongzi' => '粽子',
//                'moon_cake' => '月饼',
            ]
        ]
    ];

    // 背景音乐模板
    const ACTIVE_BG_MUSIC = [
        'popular' => [
            'title' => '流行',
            'child_list' => [
//                'red_packet_bonus_song' => [
//                    'title' => '红包返利歌曲',
//                    'url' => ''
//                ],
                'quiet_guitar_soundtrack' => [
                    'title' => '安静吉他配乐',
                    'url' => 'static/resource/audio/quiet_guitar_soundtrack.mp3'
                ],
                'city_of_hope' => [
                    'title' => 'City Of Hope',
                    'url' => 'static/resource/audio/city_of_hope.mp3'
                ],
                'intro' => [
                    'title' => 'Intro',
                    'url' => 'static/resource/audio/intro.mp3'
                ],
                'dreams_exploring_world' => [
                    'title' => '探索世界的梦想',
                    'url' => 'static/resource/audio/dreams_exploring_world.mp3'
                ],
            ]
        ],
        'holiday' => [
            'title' => '节日',
            'child_list' => [
                'spring_festival_overture' => [
                    'title' => '过年春节序曲',
                    'url' => 'static/resource/audio/spring_festival_overture.mp3'
                ],
            ]
        ],
        'classical' => [
            'title' => '古典',
            'child_list' => [
                'elegant_chinese_style' => [
                    'title' => '古典高雅中国风',
                    'url' => 'static/resource/audio/elegant_chinese_style.mp3'
                ],
                'city_hope_soundtrack' => [
                    'title' => '希望之城配乐',
                    'url' => 'static/resource/audio/city_hope_soundtrack.mp3'
                ],
                'ink_painting_chinese_style' => [
                    'title' => '墨水画中国风',
                    'url' => 'static/resource/audio/ink_painting_chinese_style.mp3'
                ],
            ]
        ],
        'epic' => [
            'title' => '史诗',
            'child_list' => [
                'destiny_honor' => [
                    'title' => 'Destiny & Honor',
                    'url' => 'static/resource/audio/destiny_honor.mp3'
                ],
                'electric_romeo' => [
                    'title' => 'Electric Romeo',
                    'url' => 'static/resource/audio/electric_romeo.mp3'
                ],
            ]
        ],
        'piano' => [
            'title' => '钢琴',
            'child_list' => [
                'tapeworm_compilation' => [
                    'title' => 'Tapeworm Compilation I',
                    'url' => 'static/resource/audio/tapeworm_compilation.mp3'
                ],
            ]
        ],
        'happy' => [
            'title' => '欢快',
            'child_list' => [
                'laugh_smile_no_vox' => [
                    'title' => 'A Laugh And A Smile No Vox',
                    'url' => 'static/resource/audio/laugh_smile_no_vox.mp3'
                ],
                'easy_run_soundtrack' => [
                    'title' => '轻松奔跑配乐',
                    'url' => 'static/resource/audio/easy_run_soundtrack.mp3'
                ],
                'theme_happy_no_1' => [
                    'title' => 'Theme-Happy No.1',
                    'url' => 'static/resource/audio/theme_happy_no_1.mp3'
                ],
                'sunny_jim' => [
                    'title' => 'Sunny Jim',
                    'url' => 'static/resource/audio/sunny_jim.mp3'
                ],
                'energetic_warm_soundtrack' => [
                    'title' => '活力温暖配乐',
                    'url' => 'static/resource/audio/energetic_warm_soundtrack.mp3'
                ],
                'light_soothing_soundtrack' => [
                    'title' => '轻快舒缓配乐',
                    'url' => 'static/resource/audio/light_soothing_soundtrack.mp3'
                ],
                'inspirational_bright_music' => [
                    'title' => '励志明亮配乐',
                    'url' => 'static/resource/audio/inspirational_bright_music.mp3'
                ],
                'sharp_rhythm_whistling' => [
                    'title' => '节奏鲜明口哨',
                    'url' => 'static/resource/audio/sharp_rhythm_whistling.mp3'
                ],
                'cheerful_korean_music' => [
                    'title' => '欢快韩风配乐',
                    'url' => 'static/resource/audio/cheerful_korean_music.mp3'
                ],
            ]
        ],
    ];
}