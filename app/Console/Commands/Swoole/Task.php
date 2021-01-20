<?php

namespace App\Console\Commands\Swoole;

use Illuminate\Console\Command;
use \Swoole\Http\Server;
use \Swoole\Coroutine as Go;
use \Swoole\Database\PDOConfig;
use \Swoole\Database\PDOPool;
use \Swoole\Runtime;
use Faker\Factory;

class Task extends Command
{

    public function demo()
    {
        Runtime::enableCoroutine();
        // 记录时间
        $s = microtime(true);
        $fackerLib = $this->facker;
        Go\run(function ()use($fackerLib){
            $pool = new PDOPool((new PDOConfig)
                ->withHost('106.15.230.238')
                ->withPort(3306)
                ->withDbName('hyperf')
                ->withCharset('utf8mb4')
                ->withUsername('shangong')
                ->withPassword('fengyi123.')
            );

            for ($n = 1024; $n--;) {
                Go::create(function () use ($pool,$fackerLib) {
                    $pdo = $pool->get();
                    for ($i = 10000; $i--;) {
                        $statement = $pdo->prepare('INSERT INTO `hyperf`.`test_search`(`name`, `age`) VALUES (?, ?)');
                        if (!$statement) {
                            throw new RuntimeException('Prepare failed');
                        }
                        $result = $statement->execute([$fackerLib->name,rand(1,90)]);
                        if (!$result) {
                            throw new RuntimeException('Execute failed');
                        }
                    }
                    $pool->put($pdo);
                });
            }

        });
        $s = microtime(true) - $s;
        echo 'Use ' . $s . ' queries' . PHP_EOL;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:task';

    /**
     * 连接service
     * @var
     */
    private $service;

    /**
     * 任务处理成功返回code
     * @var int
     */
    public static $successCode = 200;

    /**
     * 服务host
     * @var string
     */
    private $host = "0.0.0.0";

    /**
     * 端口号
     * @var int
     */
    private $port = 9502;

    /**
     * 任务处理失败返回code
     * @var int
     */
    public static $errorCode = 0;

    /**
     * 参数为空返回code
     * @var int
     */
    public static $paramsCode = 400;


    private $facker;
   
    public function __construct()
    {
        parent::__construct();
        $this->facker = Factory::create('zh_CN');
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->init();
    }


    /**
     * 初始化server
     * return void
     */
    private function init()
    {
        $this->service = new Server($this->host,$this->port);
        $this->service->set([
            'task_worker_num' => 4
        ]);

        $this->service->on("request", [$this, 'onRequest']);
        $this->service->on("task", [$this, 'onTask']);
        $this->service->on("finish", [$this, 'onFinish']);

        $this->service->start();
    }

    /**
     * 任务请求接受
     * @param $request
     * @param $response
     * @return mixed
     */
    public function onRequest($request, $response)
    {
        if(strpos($request->server['request_uri'],'.ico') !== false){
            $response->end(self::$errorCode);
        }else{
            $data = $request->post;
            if(!isset($data)){
                return $this->resultSuccess($response,self::$paramsCode);
            }
            $this->service->task($data);
            $response->end(self::$successCode);
        }
    }


    /**
     * 处理任务
     * @param \Swoole\Server $server
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    public function onTask(\Swoole\Server $server, $task_id, $from_id, $data)
    {
        echo "异步任务Data处理中:".json_encode($data).PHP_EOL;
        sleep(5);
        $server->finish($data);
    }


    /**
     * task进程任务处理结束回调，可选
     * @param \Swoole\Server $server
     * @param $task_id
     * @param $data
     * @return mixed
     */
    public function onFinish(\Swoole\Server $server, $task_id, $data)
    {
        echo "异步任务结束,数据:".json_encode($data).PHP_EOL;
        $result = ['data' => $data];
        return Result(200,'success',$result);
    }


    /**
     * 返回函数
     * return string
     */
    protected function resultSuccess($response,$code = 0)
    {
        return $response->end($code);
    }
}
