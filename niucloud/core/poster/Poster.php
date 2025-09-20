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
namespace core\poster;

use core\exception\CommonException;
use Kkokk\Poster\Facades\Poster as PosterInstance;

class Poster extends BasePoster
{

    /**
     * @param array $config
     * @return void
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
    }

    /**
     * 创建海报
     * @param array $poster_data
     * @param string $dir
     * @param string $file_path
     * @return mixed|string
     * @throws \Exception
     */
    public function createPoster(array $poster_data, string $dir, string $file_path)
    {
        if (!extension_loaded('imagick')){
            throw new CommonException('海报组件未配置完善,请联系管理员');//POSTER_CREATE_ERROR
        }
        $bg_type = $poster_data[ 'global' ][ 'bgType' ];
        $instance = PosterInstance::extension('imagick')->config([
            'path' => realpath($dir) . DIRECTORY_SEPARATOR . $file_path,
            'dpi' => [480,480]
        ]);
        $bg_width = $poster_data[ 'global' ][ 'width' ];
        $bg_height = $poster_data[ 'global' ][ 'height' ];
        if ($bg_type == 'url' && !empty($poster_data[ 'global' ][ 'bgUrl' ]) && is_file($poster_data[ 'global' ][ 'bgUrl' ])) {
            $im = $instance->buildIm($bg_width, $bg_height)->buildImage([
                'src' => $poster_data[ 'global' ][ 'bgUrl' ],
//                'angle' => 80
            ], 0, 0, 0, 0, $bg_width, $bg_height);
        } else {
            $im = $instance->buildIm($bg_width, $bg_height, $this->getRgbColor($poster_data[ 'global' ][ 'bgColor' ]));
        }
        $align_array = [
            'center', 'left', 'right', 'top', 'bottom'
        ];
        foreach ($poster_data[ 'value' ] as $k => $v) {
            $type = $v[ 'type' ];
            switch ($type) {
                case 'text':
                    $font_size = ceil($v[ 'fontSize' ]);
                    $default_font = 'static' . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . 'PingFang-Medium.ttf';
                    // $font = $default_font;
                    $font = $v[ 'fontFamily' ] ? : $default_font;
                    // $content_list = $this->getText($v[ 'value' ], $font_size, $font, $v[ 'space' ] ?? 0, $v[ 'width' ], $v[ 'height' ], $v[ 'lineHeight' ] + $font_size);
                    $base_y = $this->getY($v[ 'y' ]);
                    $base_y_num = $base_y;
                    $temp_line_height = $v[ 'lineHeight' ];
                    $base_y_num += $temp_line_height;

                    $im = $im->buildText($v[ 'value' ], $this->getX($v[ 'x' ]), $base_y_num, $font_size, $this->getRgbColor($v[ 'fontColor' ]), $v[ 'width' ], $font, $v[ 'weight' ] ? 10 : null); # 合成文字
                    break;
                case 'image':
                    if (is_file($v[ 'value' ])) {
                        $im = $im->buildImage($v[ 'value' ], $this->getX($v[ 'x' ]), $this->getY($v[ 'y' ]), 0, 0, $v[ 'width' ], $v[ 'height' ], false, $v[ 'shape' ] ?? 'normal'); # 合成图片
                    }
                    break;
                case 'draw':
                    if (!empty($v[ 'draw_type' ]) && $v[ 'draw_type' ] == 'Polygon') {
                        $points = $v[ 'points' ];
                        $im = $im->buildLine($points[ 0 ][ 0 ], $points[ 0 ][ 1 ], $points[ 2 ][ 0 ], $points[ 2 ][ 1 ], $this->getRgbColor($v[ 'bgColor' ]), 'filled_rectangle');
                    }
                    break;
            }
        }

        $path = $im->getPoster();

        return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(realpath(''), '', $path[ 'url' ]));
    }


    public function getX($dst_x)
    {
        if (is_int($dst_x)) {
            return $dst_x;
        } else {
            return [ $dst_x, 0 ];
        }
    }

    public function getY($dst_y)
    {
        if (is_int($dst_y)) {
            return $dst_y;
        } else {
            return [ $dst_y, 0 ];
        }
    }

    public function getRgbColor($color)
    {
        $color = $color ? : '#FFFFFF';
        if (!str_contains($color, '#')) {
            $color = str_replace('rgba(', '', $color);
            $color = str_replace(')', '', $color);
            list($r, $g, $b) = explode(',', $color);
            list($r, $g, $b) = array_map(function($v) { return (int) $v; }, [ $r, $g, $b ]);
        } else {
            list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
        }
        return [ $r, $g, $b, 1 ];
    }

    /**
     * 获取高度限制过后的文本
     * @param $content
     * @param $fontSize
     * @param $font
     * @param $space
     * @param $max_ws
     * @param $max_hs
     * @param $line_height
     * @return mixed
     */
    public function getText($content, $fontSize, $font, $space, $max_ws, $max_hs, $line_height)
    {
        $calcSpace = $space > $fontSize ? ( $space - $fontSize ) : 0; // 获取间距计算值

        // $fontSize = ( $fontSize * 3 ) / 4; // px 转化为 pt

        mb_internal_encoding('UTF-8'); // 设置编码
        // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
        $contents = '';
        $contentsArr = [];
        $letter = [];
        $line = 1;
        $calcSpaceRes = 0;
        // 将字符串拆分成一个个单字 保存到数组 letter 中
        for ($i = 0; $i < mb_strlen($content); $i++) {
            $letter[] = mb_substr($content, $i, 1);
        }
        $textWidthArr = [];
        $contentStr = '';
        $line_num = 1;
        $total_width = 0;
        $content_list = [];
        foreach ($letter as $l) {
            $textStr = $contentStr . $l;
            $fontBox = imagettfbbox($fontSize, 0, $font, $textStr);
            $textWidth = abs($fontBox[ 2 ]) + $calcSpaceRes;
            $textWidthArr[ $line ] = $textWidth;
            // 判断拼接后的字符串是否超过预设的宽度
            if (( $textWidth > $max_ws ) && ( $contents !== '' )) {
                $line_num++;
                if (( $line_num * $line_height ) > $max_hs) {
                    break;
                }
                $contents .= "\n";
                $contentStr = "";
                $line++;
            }
            if (empty($content_list[ $line_num - 1 ])) $content_list[ $line_num - 1 ] = '';
            $content_list[ $line_num - 1 ] .= $l;
            $contents .= $l;
            $contentStr .= $l;
            $line === 1 && $calcSpaceRes += $calcSpace;
            $text_width = max(array_values($textWidthArr));
        }
        return $content_list;
    }
}