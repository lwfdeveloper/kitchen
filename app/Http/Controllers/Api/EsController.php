<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Elasticsearch\ClientBuilder;
use Faker\Factory;

class EsController extends Controller
{
    private $EsClient;
    private $facker;
    /**
     * 为了简化测试，本测试默认只操作一个Index，一个Type，
     */
    private $index = 'eslwf';
    private $type = 'user';

    public function __construct()
    {
        /**
         * 实例化 ES 客户端
         */
        $this->EsClient = ClientBuilder::create()->setHosts(['127.0.0.1'])->build();
        $this->facker = Factory::create('zh_CN');
    }

    /**
     * 根据条件搜索
     * @return mixed
     */
    public function search($query = [], $from = 0, $size = 5)
    {
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        'match' => [
                            'first_name' => '雷'
                        ]
                    ],
                    'filter' => [
                        'range' => [
                            'age' => ['gt' => 76]
                        ]
                    ]
                ]
            ]
        ];
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            '_source' => ['first_name','age'],
            'body' =>$query
        ];
        return $this->EsClient->search($params);
    }

    /**
     * 获取当前index下所有的文档
     * @return mixed
     */
    public function searchAll()
    {
        return $this->EsClient->search();
    }

    /**
     * 批量生成文档
     * @param $num
     */
    public function generateDoc($num = 100)
    {
        foreach (range(1,$num) as $item) {
            $this->putDoc([
                'first_name' => $this->facker->name,
                'last_name' => $this->facker->name,
                'age' => $this->facker->numberBetween(1,90)
            ]);
        }
    }

    /**
     * 添加一个文档到 Index 的Type中
     * @param array $body
     * @return void
     */
    public function putDoc($body = []) {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            //'id' => 1, #可以手动指定id，也可以不指定随机生成
            'body' => $body
        ];
        $this->EsClient->index($params);
    }

    /**
     * 获取单个文档
     * @param $id
     * @return array
     */
    public function getDoc($id) {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' =>$id
        ];
        return $this->EsClient->get($params);
    }

    /**
     * 获取Index的文档模板信息
     * @return array
     */
    public function getMapping() {
        $params = [
            'index' => $this->index
        ];
        return $this->EsClient->indices()->getMapping($params);
    }

    /**
     * 获取 ES 的状态信息，包括index 列表
     * @return array
     */
    public function esStatus() {
        return $this->EsClient->indices()->stats();
    }

    /**
     * 删除一个文档
     * @param $id
     * @return array
     */
    public function delDoc($id) {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' =>$id
        ];
        return $this->EsClient->delete($params);
    }
}
