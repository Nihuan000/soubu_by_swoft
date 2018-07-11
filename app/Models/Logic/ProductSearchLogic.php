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


    /**
     * 产品搜索列表
     * @author Nihuan
     * @param array $request
     * @return array
     */
    public function getProductList(array $request)
    {
        $list = $must = [];
        $count = 0;
        $hash_code = $recommend = '';

        //过滤基本信息, 用户状态/审核通过/删除状态/上线状态/黑名单
        $filter = [
            [
                'term' => [
                    'user_status' => 1,
                ]
            ],[
                'term' => [
                    'forbid' => 0
                ]
            ],
            [
                'term' => [
                    'is_audit' => 1
                ]
            ],
            [
                'term' => [
                    'del_status' => 1
                ]
            ],
            [
                'term' => [
                    'is_up' => 1
                ]
            ]
        ];

        //筛选字段过滤
        $this->searchCommon($request, $filter);

        //关键词搜索
        $this->analyzeKeyword($request['keyword'], $must);

        //搜索语句生成
        $query = [
            'size' => $this->poolConfig->getSize(),
            'query' => [
                'bool' => [
                    'must' => $must,
                    'filter' => $filter,
                ]
            ],
            '_source' => [
                'includes' => $this->searchSource(),
            ],
            'sort' => $this->searchSort()
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
                $list = $this->productData->setData($result_hits);
                $count = $result['hits']['total'];
                $hash_code = $this->sethashCode();
            }
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
        return ['list' => $list, 'count' => $count, 'hash_code' => $hash_code, 'recommend' => $recommend];
    }


    /**
     * 关键词搜索
     * @author Nihuan
     * @param string $keyword
     * @param array $must
     * @return array
     */
    private function analyzeKeyword(string $keyword, array &$must)
    {
        if(!empty($keyword)){
            $must[] = [
                'multi_match' => [
                    'query' => $keyword,
                    'fields' => [
                        'name_na^3',
                        'name'
                    ],
                    'type' => 'best_fields',
                    'operator' => 'AND',
                    'zero_terms_query' => 'NONE'
                ]
            ];
        }
        return $must;
    }


    /**
     * 筛选字段过滤 面料类型/价格/季节/地区/用途/店铺/实商
     * @author Nihuan
     * @param array $request
     * @param array $filter
     * @return array
     */
    private function searchCommon(array $request, array &$filter)
    {
        //面料类型
        if($request['type'] != 0){
            $filter[] = [
                'term' => [
                    'type' => $request['type']
                ]
            ];
        }

        //价格
        if($request['price_gte'] > 0){
            $filter[] = [
                'range' => [
                    'price' => [
                        'from' => $request['price_gte']
                    ]
                ]
            ];
        }

        if($request['price_lte'] > 0){
            $filter[] = [
                'range' => [
                    'price' => [
                        'to' => $request['price_lte']
                    ]
                ]
            ];
        }

        //季节
        if(!empty($request['season'])){
            $season = json_decode($request['season'],true);
            foreach ($season as $item) {
                $filter[] = [
                    'term' => [
                        'season' => $item
                    ]
                ];
            }
        }

        //用途
        if(!empty($request['uses'])){
            $uses = json_decode($request['uses'],true);
            $filter[] = [
                'term' => [
                    'use_ids' => $uses
                ]
            ];
        }

        //店铺id
        if($request['shop_id'] > 0){
            $filter[] = [
                'term' => [
                    'user_id' => $request['shop_id']
                ]
            ];
        }

        //实商
        if($request['deposit'] > 0){
            $filter[] = [
                'term' => [
                    'deposit' => $request['deposit']
                ]
            ];
        }
        return $filter;
    }


    /**
     * 搜索返回字段
     * @author Nihuan
     * @return array
     */
    private function searchSource()
    {
        return [
            "name", "price", "fabric_detail", "type", "deposit", "city_id", "area", "cover", "uses", "alter_time", "spread", "city", "pro_num", "status", "user_id"
        ];
    }


    /**
     * 搜索结果排序
     * @author Nihuan
     * @return array
     */
    private function searchSort()
    {
        return [
            [
                'strength_score' => [
                    'order' => 'desc'
                ]
            ],
            [
                'deposit' => [
                    'order' => 'desc'
                ]
            ],
            [
                'orders_amount' => [
                    'order' => 'desc'
                ]
            ],
            [
                '_score' => [
                    'order' => 'desc'
                ]
            ]
        ];
    }


    /**
     * 唯一标识返回
     * @author Nihuan
     * @return string
     */
    private function sethashCode()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid =
            substr($charid, 8, 8)
            .substr($charid,12, 8)
            .substr($charid,16, 8)
            .substr($charid,20,8);
        return $uuid;
    }
}