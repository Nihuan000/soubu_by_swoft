<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-4
 * Time: 下午2:59
 * Desc: 产品搜索逻辑层
 */

namespace App\Models\Logic;

use App\Pool\Config\ElasticsearchPoolConfig;
use App\Models\Data\ProductData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Exception\PoolException;
use Elasticsearch\ClientBuilder;

/**
 * 产品搜索
 * 同时可以被controller server task使用
 * @Bean()
 * @uses      ProductSearchLogic
 * @version   1.0
 * @author    Nihuan
 */
class ProductSearchLogic
{

    /**
     * @Inject()
     * @var ElasticsearchPoolConfig
     */
    public $poolConfig;

    /**
     * @Inject()
     * @var ProductData
     */
    public $productData;


    /**
     * @author Nihuan
     * @return \Elasticsearch\Client
     * @throws PoolException
     */
    public function simpleConnectionPool()
    {
        if (empty($this->poolConfig->getUri())) {
            throw new PoolException('You must to set uri by @Inject!');
        }
        $client = ClientBuilder::create()
            ->setConnectionPool('\Elasticsearch\ConnectionPool\SimpleConnectionPool',[])
            ->setHosts($this->poolConfig->getUri())->build();
        return $client;
    }

    /**
     * 获取指定店铺的产品
     * @author Nihuan
     * @param int $shopId
     * @return array
     */
    public function getShopProduct(int $shopId)
    {
        $product_list = [];
        //搜索语句生成
        $query = [
            'size' => 6,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'user_id' => $shopId
                            ]
                        ],
                        [
                            'term' => [
                                'is_up' => 1,
                            ]
                        ],[
                            'term' => [
                                'del_status' => 1
                            ]
                        ]
                    ]
                ]
            ],
            '_source' => [
                'includes' => ['_id','cover','name','price'],
            ],
            'sort' => [
                [
                    'is_recommend' => [
                        'order' => 'desc'
                    ]
                ],[
                    'add_time' => [
                        'order' => 'desc'
                    ]
                ]
            ]
        ];
        //完整query组合
        $params = [
            'index' => $this->poolConfig->getProductMaster(),
            'type' => 'product',
            'body' => $query,
        ];
        try {
            $client = $this->simpleConnectionPool();
            $result = $client->search($params);
            if(!empty($result)){
                $result_hits = $result['hits']['hits'];
                $product_list = $this->productData->simpleShopProduct($result_hits);
            }
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
        return $product_list;
    }
}