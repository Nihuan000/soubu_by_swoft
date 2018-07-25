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


    public function getBuyList(array $request)
    {
        
    }
}