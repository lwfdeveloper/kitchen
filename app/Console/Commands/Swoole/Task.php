<?php

namespace App\Console\Commands\Swoole;

use Illuminate\Console\Command;
use \Swoole\Http\Server;

class Task extends Command
{
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
//        $this->service = new Server($this->host, $this->port);
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

    public function onTask(\Swoole\Server $server, $task_id, $from_id, $data)
    {
        echo "异步任务Data处理中:".json_encode($data).PHP_EOL;
        sleep(2);
        $server->finish($data);
    }

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
