<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午3:31
 */

namespace App\Models\Logic;

use App\Models\Data\BuyData;
use App\Pool\Config\ElasticsearchPoolConfig;
use Elasticsearch\ClientBuilder;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Exception\PoolException;

/**
 * 采购搜索
 * 同时可以被controller server task使用
 * @Bean()
 * @uses      BuySearchLogic
 * @version   1.0
 * @author    Nihuan
 */
class BuySearchLogic
{
    /**
     * @Inject()
     * @var ElasticsearchPoolConfig
     */
    public $poolConfig;


    /**
     * @Inject()
     * @var BuyData
     */
    public $buyData;

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
     * 采购列表
     * author: nihuan
     * @param array $request
     * @return array
     */
    public function getBuyList(array $request)
    {
        $list = $should = $function = [];
        $count = 0;
        $hash_code = '';
        $size = empty($request['page_size']) ? $this->poolConfig->getSize() : $request['page_size'];
        $from = $request['page'] * $size;

        //过滤基本信息
        $filter = $this->baseFilter();
        if(!empty($request['keyword'])){
            //关键词搜索
            $this->analyzeKeyword($request['keyword'], $should);
            $this->functionScore($request['keyword'], $function);
        }

        //搜索语句拼接
        $query = [
            'from' => $from,
            'size' => $size,
            'query' => [
                'function_score' => [
                    'query' => [
                        'bool' => [
                            'should' => $should,
                            'minimum_should_match' => 1,
                            'filter' => $filter,
                        ],
                    ],
                    'functions' => $function
                ],
            ],
            '_source' => [
                'includes' => $this->searchSource(),
            ],
            'sort' => $this->searchSort()
        ];
        //搜索执行语句生成
        $params = [
            'index' => $this->poolConfig->getBuyMaster(),
            'type' => 'buy',
            'body' => $query,
        ];
        try {
            $client = $this->simpleConnectionPool();
            $result = $client->search($params);
            if(!empty($result)){
                $result_hits = $result['hits']['hits'];
                $list = $this->buyData->setData($result_hits);
                $count = $result['hits']['total'];
                $hash_code = $this->sethashCode();
            }
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
        return ['list' => $list, 'count' => $count, 'hash_code' => $hash_code];
    }


    /**
     * 采购关键词过滤
     * author: nihuan
     * @param string $keyword
     * @param array $should
     * @return array
     */
    private function analyzeKeyword(string $keyword, array &$should)
    {
        if(!empty($keyword)){
            $should = [
                [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'remark_na' => $keyword
                            ]
                        ]
                    ]
                ],
                [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => ['remark'],
                        'type' => 'best_fields',
                        'operator' => 'OR',
                        'zero_terms_query' => 'NONE'
                    ]
                ],
                [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'labels_normalized' => $keyword
                            ]
                        ]
                    ]
                ]
            ];
        }
        return $should;
    }


    /**
     * 采购关键词打分
     * author: nihuan
     * @param string $keyword
     * @param array $function
     * @return array
     */
    private function functionScore(string $keyword, array &$function)
    {
        if(!empty($keyword)){
            $function = [
                [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'term' => [
                                    'labels_normalized' => $keyword
                                ]
                            ]
                        ]
                    ],
                    'weight' => 2
                ],
                //采购描述不分词
                [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'term' => [
                                    'remark_na' => $keyword
                                ]
                            ]
                        ]
                     ],
                    'weight' => 8
                ],
                //采购描述分词
                [
                    'filter' => [
                        'multi_match' => [
                            'query' => $keyword,
                            'fields' => ['remark'],
                            'type' => 'best_fields',
                            'operator' => 'OR',
                            'zero_terms_query' => 'NONE'
                        ]
                    ],
                    'weight' => 6
                ],
            ];
        }
        return $function;
    }


    /**
     * 搜索返回字段
     * @author Nihuan
     * @return array
     */
    private function searchSource()
    {
        return [
            "amount", "status", "unit", "type", "pic", "is_customize", "remark", "province", "city", "role", "add_time", "refresh_time", "alter_time", "earnest", "audit_time", "user_id"
        ];
    }


    /**
     * 基本信息过滤
     * author: nihuan
     * @return array
     */
    private function baseFilter()
    {
        //过滤基本信息, 采购状态/审核通过/删除状态/上线状态/数量
        $filter = [
            [
                'term' => [
                    'del_status' => 1
                ]
            ],
            [
                'term' => [
                    'is_audit' => 0
                ]
            ],
            [
                'term' => [
                    'forbid' => "0"
                ]
            ],
            [
                'term' => [
                    'type_id' => 2
                ]
            ],
            [
                'term' => [
                    'status' => 0
                ]
            ],
            [
                'range' => [
                    'amount' => [
                        'from' => 1
                    ]
                ]
            ]
        ];
        return $filter;
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
                'refresh_time' => [
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