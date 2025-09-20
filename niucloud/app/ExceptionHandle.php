<?php
namespace app;

use app\dict\sys\AppTypeDict;
use core\exception\AuthException;
use core\exception\ServerException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\RouteNotFoundException;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;
use Throwable;
use UnexpectedValueException;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $e
     * @return void
     */
    public function report(Throwable $e): void
    {
        // 使用内置的方式记录异常日志
//        parent::report($exception);
        if (!$this->isIgnoreReport($e) && env('app_debug', false)) {
            $data = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
                'previous' => $e->getPrevious(),
            ];
            //这个类可能会分开拆成两个
            $app_type = request()->appType() ;
            $app_type = empty($app_type) ? str_replace('/', '', request()->rootUrl()) : $app_type;
            //写入日志内容
            $log = [
                '服务主体：'.($app_type == AppTypeDict::ADMIN ? request()->uid() : request()->memberId()),//服务发起者                                                                     //用户ID
                'IP：'.request()->ip(),//ip
                '耗时（毫秒）：'.ceil((microtime(true) * 1000) - (request()->time(true) * 1000)),//耗时（毫秒）
                '请求类型：'.request()->method(),//请求类型
                '应用：'.$app_type,//应用
                '路由：'.request()->baseUrl(),//路由
                '请求参数：'.json_encode(request()->param() ?? []),//请求参数
                '错误信息：'.json_encode($data),//错误信息
            ];
            Log::write('DEBUG：>>>>>>>>>'.PHP_EOL.implode(PHP_EOL, $log).PHP_EOL.'---------', 'error');
        }
    }

    /**
     * Render an exception into an HTTP response.
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        $massageData = env('app_debug', false) ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
            'trace' => $e->getTrace(),
            'previous' => $e->getPrevious(),
        ] : [];

        // 添加自定义异常处理机制
        if (strpos($e->getMessage(), 'open_basedir') !== false) {
            return fail('OPEN_BASEDIR_ERROR');
        }
        if (strpos($e->getMessage(), 'Allowed memory size of') !== false) {
            return fail('PHP_SCRIPT_RUNNING_OUT_OF_MEMORY');
        }
        if ($e instanceof DbException) {
            return fail(get_lang('DATA_GET_FAIL').':'.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
                'previous' => $e->getPrevious(),
            ]);
        } elseif ($e instanceof ValidateException) {
            return fail($e->getMessage());
        } else if($e instanceof UnexpectedValueException){
            return fail($e->getMessage(), [], 401);
        }else if($e instanceof AuthException){
            return fail($e->getMessage(), [], $e->getCode() ?: 400);
        }else if($e instanceof ServerException){
            return fail($e->getMessage(), http_code:$e->getCode());
        } else if ($e instanceof RouteNotFoundException) {
            return fail('当前访问路由未定义或不匹配 路由地址：' . request()->baseUrl());
        } else if($e instanceof \RuntimeException){
            return fail($e->getMessage(), $massageData);
        } else {
            return $this->handleException($e);
        }
    }

    private function handleException(Throwable $e) {
        $trace = array_map(function ($class){
            return str_replace('\\', '/', $class);
        }, array_column($e->getTrace(), 'class'));

        foreach ($trace as $class) {
            if (preg_match('#^addon/([^/]+)/#', $class, $matches)) {
                return fail("{$matches[1]}插件内{$class}第{$e->getLine()}行出现异常，异常信息：" .$e->getMessage());
            }
        }

        $debug = env("APP_DEBUG", false);

        return fail("{$trace[0]}第{$e->getLine()}行出现异常，异常信息：" .$e->getMessage(), $debug ? $e->getTrace() : []);
    }
}
