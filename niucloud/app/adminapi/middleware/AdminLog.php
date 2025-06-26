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

namespace app\adminapi\middleware;


use app\Request;
use app\service\admin\user\UserLogService;
use Closure;
use ReflectionClass;

/**
 * admin用户操作日志
 * Class AdminCheckToken
 * @package app\adminapi\middleware
 */
class AdminLog
{
    public function handle(Request $request, Closure $next)
    {
        //写入日志
        if ($request->method() != 'GET') {
            $path = $request->rule()->getRoute();
            if(strstr($path,'@')){
                $arr = explode('@', $path);
                $controller = $arr[0] ?? "";
                $action = $arr[1] ?? "";
            }else{
                //暂时只有APP目录下使用这样的路由定义
                list($controllerStr, $action) = explode('/', $path, 2);
                list($module, $controller) = explode('.', $controllerStr, 2);
                // 拼接完整类名（根据 TP 命名空间规则调整）
                $controllerClass = "app\\adminapi\\controller\\{$module}\\{$controller}";
                $controller = $controllerClass;
            }

            $operation = $this->extractDescFromAnnotation($controller, $action);
            $data = [
                'uid' => $request->uid(),
                'username' => $request->username(),
                'url' => $request->url(),
                'params' => $request->param(),
                'ip' => $request->ip(),
                'type' => $request->method(),
                'operation' => $operation,
            ];
            (new UserLogService())->add($data);
        }

        return $next($request);
    }

    private function extractDescFromAnnotation($controllerClass, $actionMethod)
    {
        try {
            if (!class_exists($controllerClass)) {
                return '';
            }

            $reflection = new ReflectionClass($controllerClass);
            $controller_comment = $reflection->getDocComment();
            $controller_desc = '';
            if (preg_match('/@description\s+(.+)/', $controller_comment, $matches)) {
                $controller_desc = $matches[1];
            }
            if (!$reflection->hasMethod($actionMethod)) {
                return '';
            }

            $method = $reflection->getMethod($actionMethod);
            $docComment = $method->getDocComment();

            if (preg_match('/@description\s+(.+)/', $docComment, $matches)) {
                return (empty($controller_desc) ? "" : $controller_desc.'-').$matches[1];
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
