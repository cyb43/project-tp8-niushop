<?php

namespace app\command\workerman;

use app\command\WorkerCommand;
use app\dict\schedule\ScheduleDict;
use app\model\sys\SysSchedule;
use app\service\core\addon\CoreAddonService;
use app\service\core\schedule\CoreScheduleService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Log;
use Workerman\Crontab\Crontab;
use Workerman\RedisQueue\Client;
use Workerman\Timer;
use Workerman\Worker;

class Workerman extends Command
{
    use WorkerCommand;

    public function configure()
    {
        // 指令配置
        $this->setName('workerman')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            ->addOption('mode', 'm', Option::VALUE_OPTIONAL, 'Run the workerman server in daemon mode.')
            ->setDescription('Workerman，高性能PHP应用容器');
    }

    /**
     * 执行任务
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $this->resetCli($input, $output);
        //计划任务
        Worker::$pidFile = runtime_path() . 'workerman_schedule.pid';
        $worker = new Worker();
        $worker->name = 'schedule_work';
        $worker->count = 1;

        // 设置时区，避免运行结果与预期不一致
        date_default_timezone_set('PRC');
        $worker->onWorkerStart = function() use ($output) {
            $output->writeln('[' . date('Y-m-d H:i:s') . ']' . " Schedule Starting...");
//            // 每分钟的第1秒执行.用于计划任务是否仍在执行
            new Crontab('*/10 * * * * *', function() {
                $file = root_path('runtime') . '.schedule';
                file_put_contents($file, time());
            });
            $core_schedule_service = new CoreScheduleService();
            //查询所有的计划任务
            $task_list = $core_schedule_service->getList([ 'status' => ScheduleDict::ON ]);

            foreach ($task_list as $item) {
                //获取定时任务时间字符串
                new Crontab($this->getCrontab($item[ 'time' ]), function() use ($core_schedule_service, $item, $output) {
                    if (!empty($item[ 'class' ])) {
                        $core_schedule_service->execute($item, $output);
                    }
                });
            }
            $output->writeln('[' . date('Y-m-d H:i:s') . ']' . " Schedule Started.");
        };

        //消息队列
        Worker::$pidFile = runtime_path() . 'workerman_queue.pid';
        Worker::$logFile = runtime_path() . 'workerman.log';
        $worker = new Worker();
        $worker->name = 'queue_work';
//        $worker->count = 3;

        $worker->onWorkerStart = function() use ($output) {
            $output->writeln('[' . date('Y-m-d H:i:s') . ']' . " Queue Starting...");
            // 定时，每10秒一次
            Timer::add(30, function() use ($output) {
                ( new SysSchedule() )->select();
            });
            $redis_option = [
                'connect_timeout' => 10,
                'max_attempts' => 3,
                'retry_seconds' => 5,
                'prefix' => md5(root_path())
            ];
            if (!empty(env('redis.redis_password'))) {
                $redis_option[ 'auth' ] = env('redis.redis_password');
            }
            $redis_option[ 'db' ] = env('redis.select');
            $client = new Client('redis://' . env('redis.redis_hostname') . ':' . env('redis.port'), $redis_option);
            $queue_list = $this->getAllQueue();

            foreach ($queue_list as $queue_class_name) {
                $queue_class_name = str_replace('.php', '', $queue_class_name);
                // 订阅
                $client->subscribe($queue_class_name, function($data) use ($queue_class_name, $output) {
                    $output->writeln('[queue][' . date('Y-m-d H:i:s') . ']' . " Processing:" . $queue_class_name);
                    try {
                        $class_name = '\\' . $queue_class_name;
                        $class = new  $class_name();
                        $class->fire($data);
                    } catch (\Throwable $e) {
                        Log::write(date('Y-m-d H:i:s') . ',队列有错误:' . $queue_class_name . '_' . $e->getMessage() . '_' . $e->getFile() . '_' . $e->getLine());
                    }
                    $output->writeln('[queue][' . date('Y-m-d H:i:s') . ']' . " Processed:" . $queue_class_name);
                });
            }
            // 消费失败触发的回调(可选)
            $client->onConsumeFailure(function(\Throwable $exception, $package) use ($output) {
                $output->writeln('[queue]队列 ' . $package[ 'queue' ] . " 消费失败," . $exception->getMessage());
            });
            $output->writeln('[' . date('Y-m-d H:i:s') . ']' . " Queue Started.");
        };
        Worker::runAll();
    }

    /**
     * 获取计划任务所需的时间字符串
     * 0   1   2   3   4   5
     * |   |   |   |   |   |
     * |   |   |   |   |   +------ day of week (0 - 6) (Sunday=0)
     * |   |   |   |   +------ month (1 - 12)
     * |   |   |   +-------- day of month (1 - 31)
     * |   |   +---------- hour (0 - 23)
     * |   +------------ min (0 - 59)
     * +-------------- sec (0-59)[可省略，如果没有0位,则最小时间粒度是分钟]
     * @param $data
     * @return string
     */
    protected function getCrontab($data) : string
    {
        $sec = $data[ 'sec' ] ?? '*';
        $min = $data[ 'min' ] ?? '*';
        $hour = $data[ 'hour' ] ?? '*';
        $day = $data[ 'day' ] ?? '*';
        $week = $data[ 'week' ] ?? '*';
        $type = $data[ 'type' ] ?? '';
        switch ($type) {
            case 'sec':// 每隔几秒
                $crontab = '*/' . $sec . ' * * * * *';
                break;
            case 'min':// 每隔几分
                $crontab = '0 */' . $min . ' * * * *';
                break;
            case 'hour':// 每隔几时第几分钟执行
                $crontab = '0 ' . $min . ' */' . $hour . ' * * *';
                break;
            case 'day':// 每隔几日第几小时第几分钟执行
                $crontab = '0 ' . $min . ' ' . $hour . ' */' . $day . ' * *';
                break;
            case 'week':// 每周一次,周几具体时间执行
                $crontab = '0 ' . $min . ' ' . $hour . ' * * ' . $week;
                break;
            case 'month':// 每月一次,某日具体时间执行
                $crontab = '0 ' . $min . ' ' . $hour . ' ' . $day . ' * *';
                break;
        }
        return $crontab ?? '0 */1 * * * *';
    }

    /**
     * 捕获所有队列任务
     * @return array
     */
    public function getAllQueue()
    {
        $class_list = [];
        $system_dir = root_path() . 'app' . DIRECTORY_SEPARATOR . 'job';
        $addon_dir = root_path() . 'addon' . DIRECTORY_SEPARATOR;
        if (is_dir($system_dir)) {
            search_dir($system_dir, $app_data, root_path());
            $class_list = array_merge($class_list, $app_data);
        }

        $addons = ( new CoreAddonService() )->getInstallAddonList();
        foreach ($addons as $v) {

            $addon_path = $addon_dir . $v[ 'key' ] . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'job';
            if (is_dir($addon_path)) {
                search_dir($addon_path, $addon_data, root_path());
                $class_list = array_merge($class_list, array_filter($addon_data));
            }
        }

        foreach ($class_list as &$v) {
            $v = str_replace('.php', '', $v);
            $v = str_replace('/', '\\', $v);
        }
        return $class_list;
    }
}
