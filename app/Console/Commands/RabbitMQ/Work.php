<?php

namespace App\Console\Commands\RabbitMQ;

use Illuminate\Console\Command;
use App\Lib\RabbitmqInterface;

class Work extends Command implements RabbitmqInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * rabbitMq配置
     * @var string[]
     */
    protected $config = [];

    /**
     * rabbmitMq连接实例
     * @var \AMQPConnection
     */
    private $rabbitMqConnection;

    /**
     * 设置交换机类型
     * @var array
     */
    private $amqpExType = [
		'direct' => ''
        // 'direct'  =>  AMQP_EX_TYPE_DIRECT, //直连交换机
        // 'fanout'  =>  AMQP_EX_TYPE_FANOUT, //扇形交换机
        // 'headers' => AMQP_EX_TYPE_HEADERS, //头交换机
        // 'topic'   => AMQP_EX_TYPE_TOPIC //主题交换机
    ];


    /**
     * @var int
     */
    protected $durable = '';
//    protected $durable = AMQP_DURABLE;



    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 在连接内创建一个通道
     * @return \AMQPChannel
     * @throws \AMQPConnectionException
     */
    public function connectionAmqpCHannel()
    {
        $ch = new \AMQPChannel($this->rabbitMqConnection );
        return $ch;
    }

    /**
     * 在通道中创建一个交换机
     * @param $channel
     * @return \AMQPExchange
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function connectionAmqpExchange($channel)
    {
        $exchange = new \AMQPExchange($channel);
        return $exchange;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config['host'] = env('MQHOST','127.0.0.1');
        $config['vhost'] = env('MQVHOST','/');
        $config['port'] = env('MQPORT','5672');
        $config['login'] = env('MQLOGIN','user');
        $config['password'] = env('MQPASSWORD','password');
        $this->config = $config; //rabbitMQ配置
        $this->init();
    }

    /**
     * 连接初始化
     * return void
     */
    protected function init()
    {
        $this->rabbitMqConnection = new \AMQPConnection($this->config);
        if (!$this->rabbitMqConnection->connect()) {
            echo "Cannot connect to the broker";
            return false;
        }
        //在连接内创建一个通道
        $channel = $this->connectionAmqpCHannel();
        //创建一个交换机
        $exchange = $this->connectionAmqpExchange($channel);
        //声明路由键
        $routingKey = 'lwf_laravel_1';
        //声明交换机名称
        $exchangeName = 'lwf_exchange_laravel_1';
        //设置交换机名称
        $exchange->setName($exchangeName);
        //交换机类型
        $exchange->setType($this->amqpExType['direct']);
        //设置交换机持久
        $exchange->setFlags($this->durable);
        //声明交换机
        $exchange->declareExchange();

        return $this->setAmqpQueue($channel,$exchange,$routingKey);
    }


    /**
     * 创建消息队列
     * return void
     */
    protected function setAmqpQueue($channel,$exchange,$routingKey,$queueName = 'lwf_laravel_queue_1')
    {
        try {
            $queue = new \AMQPQueue($channel);
            $queue->setName($queueName);
            //设置队列持久
            $queue->setFlags($this->durable);
            //声明消息队列
            $queue->declareQueue();
            //交换机和队列通过$routingKey进行绑定
            $queue->bind($exchange->getName(), $routingKey);
            $queue->consume(function ($envelope, $queue){
                //休眠两秒，
                sleep(2);
                //echo消息内容
                echo $envelope->getBody()."\n";
                //显式确认，队列收到消费者显式确认后，会删除该消息
                $queue->ack($envelope->getDeliveryTag());
            });
            return Result(200,'success');
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
}
