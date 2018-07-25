<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-3
 * Time: 下午3:24
 * Desc: 店铺搜索逻辑层
 */

namespace App\Models\Logic;

use App\Pool\Config\ElasticsearchPoolConfig;
use App\Models\Data\ShopData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Exception\PoolException;
use Elasticsearch\ClientBuilder;

/**
 * 店铺搜索
 * 同时可以被controller server task使用
 * @Bean()
 * @uses      ShopSearchLogic
 * @version   1.0
 * @author    Nihuan
 */
class ShopSearchLogic
{

    /**
     * @Inject()
     * @var ElasticsearchPoolConfig
     */
    public $poolConfig;

    /**
     * @Inject()
     * @var ShopData
     */
    private $shopData;

    /**
     * @Inject()
     * @var ProductSearchLogic
     */
    private $productLogic;


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
     * 店铺搜索列表
     * @author Nihuan
     * @param array $request
     * @return array
     */
    public function getShopList(array $request)
    {
        $list = [];
        $count = 0;
        $hash_code = $recommend = '';
        $size = empty($request['page_size']) ? $this->poolConfig->getSize() : $request['page_size'];
        $from = $request['page'] * $size;
        //过滤基本信息, 状态/子帐号/黑名单/身份
        $filter = [
            [
                'term' => [
                    'status' => 1,
                ]
            ],[
              'term' => [
                  'forbid' => 0
              ]
            ],
            [
                'range' => [
                    'role' => [
                        'gte' => 2,
                        'lte' => 4,
                    ]
                ]
            ]
        ];

        //筛选字段过滤 地区/主营行业/认证类型/经营模式/保证金判断/等级
        $this->searchCommon($request, $filter);

        //关键词搜索组合
        $this->analyzeKeyword($request['keyword'], $filter);

        //搜索语句生成
        $query = [
            'from' => $from,
            'size' => $size,
            'query' => [
                'bool' => [
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
            'index' => $this->poolConfig->getShopMaster(),
            'type' => 'shop',
            'body' => $query,
        ];

        try {
            $client = $this->simpleConnectionPool();
            $result = $client->search($params);
            if(!empty($result)){
                $result_hits = $result['hits']['hits'];
                $list = $this->shopData->setData($result_hits);
                $count = $result['hits']['total'];
                if($count >= 1){
                    $list[0]['products'] = $this->productLogic->getShopProduct($list[0]['userId']);
                }
                $hash_code = $this->sethashCode();
            }
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
        return ['list' => $list, 'count' => $count, 'hash_code' => $hash_code, 'recommend' => $recommend];
    }

    /**
     * 多字段关键词过滤
     * @author Nihuan
     * @param string $keyword
     * @param array $filter
     * @return array
     */
    private function analyzeKeyword(string $keyword, array &$filter)
    {
        if(!empty($keyword)){
            $filter[] = [
                'multi_match' => [
                    'query' => $keyword,
                    'fields' => [
                        'name_na^3',
                        'name',
                        'main_product'
                    ],
                    'type' => 'best_fields',
                    'operator' => 'AND',
                    'zero_terms_query' => 'NONE'
                ]
            ];
        }
        return $filter;
    }

    /**
     * 筛选信息过滤
     * @author Nihuan
     * @param array $request
     * @param array $filter
     * @return array
     */
    private function searchCommon(array $request, array &$filter)
    {
        if($request['main_industry'] != 0){
            $filter[] = [
                'term' => [
                    'main_industry' => $request['main_industry']
                ]
            ];
        }
        if($request['area_id'] != 0){
            $filter[] = [
                'term' => [
                    'city_id' => $request['area_id']
                ]
            ];
        }
        if($request['operation_mode'] != -1){
            $filter[] = [
                'term' => [
                    'operation_mode' => $request['operation_mode']
                ]
            ];
        }

        if($request['level'] != -1){
            $filter[] = [
                'term' => [
                    'level' => $request['level']
                ]
            ];
        }

        if($request['certification'] != -1){
            $filter[] = [
                'term' => [
                    'certification_type' => $request['certification']
                ]
            ];
        }
        return $filter;
    }

    /**
     * 店铺搜索返回字段
     * @author Nihuan
     * @return array
     */
    private function searchSource()
    {
        return [
            "user_id","name", "portrait", "main_product", "province", "city", "status", "role", "deposit", "level", "certification_type", "operation_mode", "safe_price", "strength_score"
        ];
    }

    /**
     * 店铺搜索排序规则
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