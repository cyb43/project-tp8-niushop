<?php

use think\Container;
use think\Response;
use think\facade\Lang;
use think\facade\Queue;
use think\facade\Cache;
use core\util\Snowflake;
use app\service\core\upload\CoreImageService;
use app\service\core\sys\CoreSysConfigService;

// 应用公共文件

/**
 * 接口操作成功，返回信息
 * @param array|string $msg
 * @param array|string|bool|null $data
 * @param int $code
 * @param int $http_code
 * @return Response
 */
function success($msg = 'SUCCESS', $data = [], int $code = 1, int $http_code = 200): Response
{
    if (is_array($msg)) {
        $data = $msg;
        $msg = 'SUCCESS';
    }
    return Response::create(['data' => $data, 'msg' => get_lang($msg), 'code' => $code], 'json', $http_code);

}

/**
 * 接口操作失败，返回信息
 * @param $msg
 * @param array|null $data
 * @param int $code
 * @param int $http_code
 * @return Response
 */
function fail($msg = 'FAIL', ?array $data = [], int $code = 0, int $http_code = 200): Response
{
    if (is_array($msg)) {
        $data = $msg;
        $msg = 'FAIL';
    }
    return Response::create(['data' => $data, 'msg' => get_lang($msg), 'code' => $code], 'json', $http_code);
}

/**
 * 自动侦测语言并转化
 * @param string $str
 * @return lang()
 */
function get_lang($str)
{
    return Lang::get($str);
}


/**
 * 把返回的数据集转换成Tree
 * @param $list 要转换的数据集
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int $root
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parent_id = $data[$pid];
            if ($root == $parent_id) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parent_id])) {
                    $parent =& $refer[$parent_id];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;

}

/**
 * 生成加密密码
 * @param $password
 * @param $salt  手动提供散列密码的盐值（salt）。这将避免自动生成盐值（salt）。,默认不填写将自动生成
 * @return string
 */
function create_password($password, $salt = '')
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 校验比对密码和加密密码是否一致
 * @param $password
 * @param $hash
 * @return bool
 */
function check_password($password, $hash)
{
    if (!password_verify($password, $hash)) return false;
    return true;
}


/**
 * 获取键对应的值
 * @param array $array 源数组
 * @param array $keys 要提取的键数组
 * @param string $index 二维组中指定提取的字段（唯一）
 * @return array
 */
function array_keys_search($array, $keys, $index = '', $is_sort = true)
{
    if (empty($array))
        return $array;
    if (empty($keys))
        return [];
    if (!empty($index) && count($array) != count($array, COUNT_RECURSIVE))
        $array = array_column($array, null, $index);
    $list = array();

    foreach ($keys as $key) {
        if (isset($array[$key])) {
            if ($is_sort) {
                $list[] = $array[$key];
            } else {
                $list[$key] = $array[$key];
            }
        }

    }
    return $list;
}


/**
 * @notes 删除目标目录
 * @param $path
 * @param $delDir
 * @return bool|void
 */
function del_target_dir($path, $delDir)
{
    //没找到，不处理
    if (!file_exists($path)) {
        return false;
    }

    //打开目录句柄
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir("$path/$item")) {
                    del_target_dir("$path/$item", $delDir);
                } else {
                    unlink("$path/$item");
                }
            }
        }
        closedir($handle);
        if ($delDir) {
            return rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}

/**
 * 获取一些公共的系统参数
 * @param string|null $key
 * @return array|mixed
 */
function system_name(?string $key = '')
{
    $params = [
        'admin_token_name' => env('system.admin_token_name', 'token'),///todo !!! 注意  header参数  不能包含_ , 会自动转成 -
        'api_token_name' => env('system.api_token_name', 'token'),
        'channel_name' => env('system.channel_name', 'channel'),
    ];
    if (!empty($key)) {
        return $params[$key];
    } else {
        return $params;
    }
}


/**
 * 获取日期(默认不传参 获取当前日期)
 * @param int|null $time
 * @return string
 */
function get_date_by_time(?int $time = null)
{
    return date('Y-m-d h:i:s', $time);
}

function get_start_and_end_time_by_day($day = '')
{
    $date = $day ?: date('Y-m-d');
    $day_start_time = strtotime($date);
    //当天结束之间
    $day_end_time = $day_start_time + 86400;
    return [$day_start_time, $day_end_time];
}

/**
 * 获取本周的 开始、结束时间
 * @param data $date 日期
 */
function get_weekinfo_by_time($date)
{
    $idx = strftime("%u", strtotime($date));
    $mon_idx = $idx - 1;
    $sun_idx = $idx - 7;
    return array(
        'week_start_day' => strftime('%Y-%m-%d', strtotime($date) - $mon_idx * 86400),
        'week_end_day' => strftime('%Y-%m-%d', strtotime($date) - $sun_idx * 86400),
    );
}


/**
 * 路径转链接
 * @param $path
 * @return string
 */
function path_to_url($path)
{
    return trim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '.');
}

/**
 * 链接转化路径
 * @param $url
 * @return string
 */
function url_to_path($url)
{
    if (str_contains($url, 'http://') || str_contains($url, 'https://')) return $url;//网络图片不必
    return public_path() . trim(str_replace('/', DIRECTORY_SEPARATOR, $url));
}

/**
 * 获取本地文件的对外网络路径
 * @param string $path
 * @return string
 */
function get_file_url(string $path)
{
    if (!$path) return '';
    if (!str_contains($path, 'http://') && !str_contains($path, 'https://')) {
        return request()->domain() . '/' . path_to_url($path);
    } else {
        return path_to_url($path);
    }
}

/**
 * 新增队列工作
 * @param $job
 * @param $data
 * @param $delay
 * @param $queue
 * @return bool
 */
function create_job($job, $data = '', $delay = 0, $queue = null)
{
    if ($delay > 0) {
        $is_success = Queue::later($delay, $job, $data, $queue);
    } else {
        $is_success = Queue::push($job, $data, $queue);
    }
    if ($is_success !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取插件对应资源文件(插件安装后获取)
 * @param $addon //插件名称
 * @param $file_name //文件名称（包含resource文件路径）
 */
function addon_resource($addon, $file_name)
{
    return "addon/" . $addon . "/" . $file_name;
}

/**
 * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）
 *
 * @param string $file 文件/目录
 * @return boolean
 */
function is_write($file)
{
    if (is_dir($file)) {
        $dir = $file;
        if ($fp = @fopen("$dir/test.txt", 'wb')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = true;
        } else {
            $writeable = false;
        }
    } else {
        if ($fp = @fopen($file, 'ab+')) {
            @fclose($fp);
            $writeable = true;
        } else {
            $writeable = false;
        }
    }
    return $writeable;
}

/**
 * 主要用于金额格式化(用于显示)
 * @param $number
 * @return int|mixed|string
 */
function format_money($number)
{
    if ($number == intval($number)) {
        return intval($number);
    } elseif ($number == sprintf('%.2f', $number)) {
        return sprintf('%.2f', $number);
    }
    return $number;
}

/**
 * 金额浮点数格式化
 * @param $number
 * @param $precision
 * @return string
 */
function format_float_money($number, $precision = 2)
{
    if ($precision > 0) {
        return sprintf('%.' . $precision . 'f', floor($number * (10 ** $precision)) / (10 ** $precision));
    } else {
        return sprintf('%.' . $precision . 'f', floor($number));
    }
}

/**
 * 金额保留小数点后*位
 * @param $number
 * @return float
 */
function format_round_money($number)
{
    return round($number, 2);
}

/**
 * 基础属性过滤(特殊字符..)
 * @param $string
 * @return void
 */
function filter($string)
{
    return $string;
}

/**
 * 生成编号
 * @param string $prefix
 * @param string $tag 业务标识 例如member_id ...
 * @return string
 * @throws Exception
 */
function create_no(string $prefix = '', string $tag = '')
{
    $data_center_id = 1;
    $machine_id = 2;
    $snowflake = new Snowflake($data_center_id, $machine_id);
    $id = $snowflake->generateId();
    return $prefix . date('Ymd') . $tag . $id;
}

/**
 * 多级目录不存在则创建
 * @param $dir
 * @param $mode
 * @return bool
 */
function mkdirs($dir, $mode = 0777)
{
    if (str_contains($dir, '.')) $dir = dirname($dir);
    if (is_dir($dir) || @mkdir($dir, $mode)) return true;
    if (!mkdirs(dirname($dir), $mode)) return false;
    return @mkdir($dir, $mode);
}

/**
 * 创建文件夹
 * @param $dir
 * @param $mode
 * @return true
 */
function mkdirs_or_notexist($dir, $mode = 0777)
{
    if (!is_dir($dir) && !mkdir($dir, $mode, true) && !is_dir($dir)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
    }
    return true;
}

/**
 * 删除缓存文件使用
 * @param $dir
 */
function rmdirs($dir)
{
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (is_dir($fullpath)) {
                rmdirs($fullpath);
            } else {
                unlink($fullpath);
            }
        }
    }
    closedir($dh);
}

/**
 * 获取唯一随机字符串
 * @param int $len
 * @return string
 */
function unique_random($len = 10)
{
    $str = 'qwertyuiopasdfghjklzxcvbnmasdfgh';
    str_shuffle($str);
    return substr(str_shuffle($str), 0, $len);
}

/**
 * 校验事件结果
 * @param $result
 * @return bool
 */
function check_event_result($result)
{
    if (empty($result) || is_array($result)) {
        return true;
    }
    foreach ($result as $v) {
        if (!$v) return false;
    }
    return true;
}

/**
 * 二维数组合并
 * @param array $array1
 * @param array $array2
 * @return array
 */
function array_merge2(array $array1, array $array2)
{
    foreach ($array2 as $array2_k => $array2_v) {
        if (array_key_exists($array2_k, $array1)) {
            if (is_array($array2_v)) {
                foreach ($array2_v as $array2_kk => $array2_vv) {
                    if (array_key_exists($array2_kk, $array1[$array2_k])) {
                        if (is_array($array2_vv)) {
                            $array1[$array2_k][$array2_kk] = array_merge($array1[$array2_k][$array2_kk], $array2_vv);
                        }
                    } else {
                        $array1[$array2_k][$array2_kk] = $array2_vv;
                    }
                }
            } else {
                $array1[$array2_k] = $array2_v;
            }
        } else {
            $array1[$array2_k] = $array2_v;
        }
    }
    return $array1;
}

/**
 * 通过目录获取文件结构1
 * @param $dir
 * @return array
 */
function get_files_by_dir($dir)
{
    $dh = @opendir($dir);             //打开目录，返回一个目录流
    $return = array();
    while ($file = @readdir($dh)) {     //循环读取目录下的文件
        if ($file != '.' and $file != '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $file;     //设置目录，用于含有子目录的情况
            if (is_dir($path)) {
                $return[] = $file;
            }
        }
    }
    @closedir($dh);             //关闭目录流
    return $return;               //返回文件
}


/**
 * 文件夹文件拷贝
 * @param string $src 来源文件夹
 * @param string $dst 目的地文件夹
 * @param array $files 文件夹集合
 * @param array $exclude_dirs 排除无需拷贝的文件夹
 * @param array $exclude_files 排除无需拷贝的文件
 * @return bool
 */
function dir_copy(string $src = '', string $dst = '', &$files = [], $exclude_dirs = [], $exclude_files = [])
{
    if (empty($src) || empty($dst)) {
        return false;
    }
    if (!file_exists($src)) {
        return false;
    }
    $dir = opendir($src);
    dir_mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                // 排除目录
                if (count($exclude_dirs) && in_array($file, $exclude_dirs)) continue;
                dir_copy($src . '/' . $file, $dst . '/' . $file, $files, $exclude_dirs, $exclude_files);
            } else {
                // 排除文件
                if (count($exclude_files) && in_array($file, $exclude_files)) continue;
                $copyResult = copy($src . '/' . $file, $dst . '/' . $file);
                $files[] = $dst . '/' . $file;
                if (!$copyResult) {
                    closedir($dir);
                    throw new \core\exception\CommonException("文件{$file}拷贝失败请检查是否有足够的权限");
                }
            }
        }
    }
    closedir($dir);
    return true;
}

/**
 * 删除文件
 * @param string $dst
 * @param array $dirs
 * @return bool
 */
function dir_remove(string $dst = '', array $dirs = [])
{
    if (empty($dirs) || empty($dst)) {
        return false;
    }
    foreach ($dirs as $v) {
        @unlink($dst . $v);
    }
    return true;
}

/**
 * 创建文件夹
 *
 * @param string $path 文件夹路径
 * @param int $mode 访问权限
 * @param bool $recursive 是否递归创建
 * @return bool
 */
function dir_mkdir($path = '', $mode = 0777, $recursive = true)
{
    clearstatcache();
    if (!is_dir($path)) {
        if (mkdir($path, $mode, $recursive)) {
            return chmod($path, $mode);
        } else {
            throw new \core\exception\CommonException("目录{$path}创建失败请检查是否有足够的权限");
        }
    }
    return true;
}


/**
 * 分割sql语句
 * @param string $content sql内容
 * @param bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
 * @param array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀my_替换成me_
 * @return array|string 除去注释之后的sql语句数组或一条语句
 */
function parse_sql($content = '', $string = false, $replace = [])
{
    // 纯sql内容
    $pure_sql = [];
    // 被替换的前缀
    $from = '';
    // 要替换的前缀
    $to = '';
    // 替换表前缀
    if (!empty($replace)) {
        $to = current($replace);
        $from = current(array_flip($replace));
    }
    if ($content != '') {
        // 多行注释标记
        $comment = false;
        // 按行分割，兼容多个平台
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = explode("\n", trim($content));
        // 循环处理每一行
        foreach ($content as $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }
            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }
            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }
            // 多行注释开始
            if (str_starts_with($line, '/*')) {
                $comment = true;
                continue;
            }
            // 多行注释结束
            if (str_ends_with($line, '*/')) {
                $comment = false;
                continue;
            }
            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }
            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`' . $from, '`' . $to, $line);
            }
            // sql语句
            $pure_sql[] = $line;
        }
        // 只返回一条语句
        if ($string) {
            return implode("", $pure_sql);
        }
        // 以数组形式返回sql语句
        $pure_sql = implode("\n", $pure_sql);
        $pure_sql = explode(";\n", $pure_sql);
    }
    return $pure_sql;
}

/**
 * 递归查询目录下所有文件
 * @param $path
 * @param $data
 * @param $search
 * @return void
 */
function search_dir($path, &$data, $search = '')
{
    if (is_dir($path)) {
        $path .= DIRECTORY_SEPARATOR;
        $fp = dir($path);
        while ($file = $fp->read()) {
            if ($file != '.' && $file != '..') {
                search_dir($path . $file, $data, $search);
            }
        }
        $fp->close();
    }
    if (is_file($path)) {
        if ($search) $path = str_replace($search, '', $path);
        $data[] = $path;
    }
}

function remove_empty_dir($dirs)
{

}

/**
 * 获取文件地图
 * @param $path
 * @param array $arr
 * @return array
 */
function getFileMap($path, $arr = [])
{
    if (is_dir($path)) {
        $dir = scandir($path);
        foreach ($dir as $file_path) {
            if ($file_path != '.' && $file_path != '..') {
                $temp_path = $path . '/' . $file_path;
                if (is_dir($temp_path)) {
                    $arr[$temp_path] = $file_path;
                    $arr = getFileMap($temp_path, $arr);
                } else {
                    $arr[$temp_path] = $file_path;
                }
            }
        }
        return $arr;
    }
}

/**
 * 如果不存在则写入缓存
 * @param string|null $name
 * @param $value
 * @param $tag
 * @param $options
 * @return mixed|string
 */
function cache_remember(string $name = null, $value = '', $tag = null, $options = null)
{
    if (!empty($hit = Cache::get($name)))//可以用has
        return $hit;
    if ($value instanceof Closure) {
        // 获取缓存数据
        $value = Container::getInstance()->invokeFunction($value);
    }
    if (is_null($tag)) {
        Cache::set($name, $value, $options['expire'] ?? null);
    } else {
        Cache::tag($tag)->set($name, $value, $options['expire'] ?? null);
    }
    return $value;

}

/**
 * 项目目录
 * @return string
 */
function project_path()
{
    return dirname(root_path()) . DIRECTORY_SEPARATOR;
}

/**
 * 图片转base64
 * @param string $path
 * @param $is_delete `转换后是否删除原图`
 * @return string
 */
function image_to_base64(string $path, $is_delete = false)
{
    if (!file_exists($path)) return 'image not exist';

    $mime = getimagesize($path)['mime'];
    $image_data = file_get_contents($path);
    // 将图片转换为 base64
    $base64_data = base64_encode($image_data);

    if ($is_delete) @unlink($path);

    return "data:$mime;base64,$base64_data";
}

/**
 * 获取缩略图
 * @param $image
 * @param string $thumb_type
 * @param bool $is_throw_exception
 * @return mixed
 * @throws Exception
 */
function get_thumb_images($image, $thumb_type = 'all', bool $is_throw_exception = false)
{

    return ( new CoreImageService() )->thumb($image, $thumb_type, $is_throw_exception);
}

/**
 * 版本号转整数 例如1.0.0=001.000.000=001000000=1000000
 * @param $version
 * @return int
 */
function version_to_int($version)
{
    $version_array = explode(".", $version);

    $v1 = sprintf('%03s', (int)$version_array[0] ?? 0);
    $v2 = sprintf('%03s', (int)$version_array[1] ?? 0);
    $v3 = sprintf('%03s', (int)$version_array[2] ?? 0);
    return (int)"{$v1}{$v2}{$v3}";
}

/**
 * 整数版本号转字符串例如 1000000=001000000=001.000.000=1.0.0
 * @param int $ver
 * @return string
 */
function version_to_string($ver)
{
    if ($ver > 999) {
        if ($ver > 999999) {
            $ver .= "";
            $v3 = (int)substr($ver, -3);
            $v2 = (int)substr($ver, -6, 3);
            $v1 = (int)substr($ver, 0, -6);
        } else {
            $ver .= "";
            $v3 = (int)substr($ver, -3);
            $v2 = (int)substr($ver, 0, -3);
            $v1 = 0;
        }
    } else {
        $v3 = $ver;
        $v2 = 0;
        $v1 = 0;
    }
    return "{$v1}.{$v2}.{$v3}";
}

/**
 * 检测文件是否是本地图片
 * @param string $file_path
 * @return bool
 */
function check_file_is_remote(string $file_path)
{
    return str_contains($file_path, 'https://') || str_contains($file_path, 'http://') || str_contains($file_path, '.com');
}

/**
 * 文件拷贝
 * @param string $source_file
 * @param string $to_file
 * @return bool
 */
function file_copy(string $source_file, string $to_file)
{
    if (!file_exists($source_file)) return false;

    // 检查目标文件是否存在
    if (!file_exists($to_file)) {
        // 创建目录
        $directory = dirname($to_file);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    if (copy($source_file, $to_file)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 创建并生成二维码
 * @param $url
 * @param $page
 * @param $data
 * @param string $dir
 * @param string $channel
 * @param true[] $style
 * @param bool $outfile
 * @return string
 */
function qrcode($url, $page, $data, $dir = '', $channel = 'h5', $style = ['is_transparent' => true], $outfile = true)
{
    if ($outfile) {
        $dir = $dir ? : 'upload' . '/' . 'qrcode/';//二维码默认存储位置
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        $file_path = md5($url . $page . serialize($data) . serialize($style) . $channel);
        $path = $dir . '/' . $file_path . '.png';
        if (file_exists($path)) {
            return $path;
        }
    }

    $result = array_values(array_filter(event('GetQrcodeOfChannel', [
        'filepath' => $path ?? '',
        'url' => $url,
        'page' => $page,
        'data' => $data,
        'channel' => $channel,
        'outfile' => $outfile
    ])));
    if (!empty($result[0])) {
        $path = $result[0];
    }
    return $path;
}

/**
 * 获取海报
 * @param $id
 * @param $type
 * @param array $param
 * @param string $channel
 * @param bool $is_throw_exception
 * @return string|null
 */
function poster($id, $type, array $param = [], string $channel = '', bool $is_throw_exception = true)
{
    return ( new \app\service\core\poster\CorePosterService() )->get($id, $type, $param, $channel, $is_throw_exception);
}

/**
 * 是否是url链接
 * @param $string
 * @return bool
 */
function is_url($string)
{
    if (strstr($string, 'http://') === false && strstr($string, 'https://') === false) {
        return false;
    } else {
        return true;
    }
}

/**
 * 获取站点插件
 * @return array
 */
function get_install_addons() : array
{
    $addons = Cache::get("local_install_addons");
    return is_null($addons) ? [] : $addons;
}

function get_wap_domain()
{
    $wap_url = ( new CoreSysConfigService() )->getSceneDomain()[ 'wap_url' ];
    return $wap_url;
}

/**
 * $str为要进行截取的字符串，$length为截取长度（汉字算一个字，字母算半个字
 * @param $str
 * @param int $length
 * @param bool $is_need_apostrophe
 * @return string
 */
function str_sub($str, $length = 10, $is_need_apostrophe = true)
{
    return mb_substr($str, 0, $length, 'UTF-8') . ($is_need_apostrophe ? '...' : '');
}

/**
 * $str为要进行截取的字符串，$length为截取长度（汉字算一个字，字母算半个字
 * @param $str
 * @param int $length
 * @param bool $is_need_apostrophe
 * @return string
 */
function str_sub2($str, $length = 10, $is_need_apostrophe=true)
{
    if (mb_strlen($str, 'UTF-8') > $length) {
        return mb_substr($str, 0, $length, 'UTF-8') . ($is_need_apostrophe ? '...' : '');
    } else {
        return mb_substr($str, 0, $length, 'UTF-8');
    }
}

/**
 * 使用正则表达式匹配特殊字符
 * @param $str
 * @return bool
 */
function is_special_character($str)
{
    $pattern = '/[!@#$%^&*()\[\]{}<>\|?:;"]/';
    if (preg_match($pattern, $str)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 时间格式转换
 * @param $time
 * @return string
 */
function get_last_time($time = null)
{
    $text = '';
    $time = $time === null || $time > time() ? time() : intval($time);
    $t = time() - $time; //时间差 （秒）
    $y = date('Y', $time) - date('Y', time());//是否跨年
    switch ($t) {
        case 0:
            $text = '刚刚';
            break;
        case $t < 60:
            $text = $t . '秒前'; // 一分钟内
            break;
        case $t < 60 * 60:
            $text = floor($t / 60) . '分钟前'; //一小时内
            break;
        case $t < 60 * 60 * 24:
            $text = floor($t / (60 * 60)) . '小时前'; // 一天内
            break;
        case $t < 60 * 60 * 24 * 3:
            $text = floor($time / (60 * 60 * 24)) == 1 ? '昨天' . date('H:i', $time) : '前天' . date('H:i', $time); //昨天和前天
            break;
        case $t < 60 * 60 * 24 * 30:
            $text = date('m-d H:i', $time); //一个月内
            break;
        case $t < 60 * 60 * 24 * 365 && $y == 0:
            $text = date('m-d', $time); //一年内
            break;
        default:
            $text = date('Y-m-d', $time); //一年以前
            break;
    }
    return $text;
}

/**
 * 检查目录及其子目录的权限
 * @param $dir `要检查的目录路径`
 * @param $data
 * @param $exclude_dir `排除排除无需检测的的文件夹`
 * @return array|array[]|mixed
 */
function checkDirPermissions($dir, $data = [], $exclude_dir = [])
{
    if (!is_dir($dir)) {
        throw new \RuntimeException(sprintf('指定的路径 "%s" 不是一个有效的目录', $dir));
    }

    if (empty($data)) {
        $data = [
            'unreadable' => [],
            'not_writable' => []
        ];
    }

    try {
        if (!is_readable($dir)) {
            $data['unreadable'][] = $dir;
        }
        if (!is_writable($dir)) {
            $data['not_writable'][] = $dir;
        }
        if (is_readable($dir)) {
            $dh = opendir($dir);
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

                // 忽略指定目录
                $is_exclude = false;
                foreach ($exclude_dir as $k => $item) {
                    if (strpos($fullPath, $item)) {
                        $is_exclude = true;
                        break;
                    }
                }

                if ($is_exclude) continue;

                // 判断是否为目录，如果是则递归调用
                if (is_dir($fullPath)) {
                    $data = checkDirPermissions($fullPath, $data, $exclude_dir); // 递归调用自身来检查子目录
                } else {
                    // 如果是文件，则检查其读写权限
                    if (!is_readable($fullPath)) $data['unreadable'][] = $fullPath;
                    if (!is_writable($fullPath)) $data['not_writable'][] = $fullPath;
                }
            }
            closedir($dh);
        }
        return $data;
    } catch (Exception $e) {
        $data['unreadable'][] = $dir;
        $data['not_writable'][] = $dir;
        return $data;
    }
}

/**
 * 下载网络图片
 * @param $img_url `图片URL`
 * @param $file_name `本地保存位置`
 * @return bool
 */
function downloadImage($img_url, $file_name)
{

    // 初始化 cURL 会话
    $ch = curl_init($img_url);

    // 打开本地文件以写入模式
    $fp = fopen($file_name, 'wb');

    // 设置 cURL 选项
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // 跳过 SSL 验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 执行 cURL 会话
    curl_exec($ch);

    // 检查是否有错误发生
    if (curl_errno($ch)) {
//        echo 'Curl error: ' . curl_error($ch);
        return false;
    }

    // 关闭 cURL 会话和文件句柄
    curl_close($ch);
    fclose($fp);
    return true;
}
