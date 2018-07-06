<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 下午2:04
 */

namespace App\Pool\Config;


use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;

/**
 * the config of service user
 *
 * @Bean()
 */
class ElasticsearchPoolConfig
{

    /**
     * the name of pool
     *
     * @Value(name="${config.elasticsearch.elastic.name}", env="${ES_CLUSTER_NAME}")
     * @var string
     */
    protected $name = '';


    /**
     * the addresses of connection
     *
     * <pre>
     * [
     *  '127.0.0.1:9200',
     * ]
     * </pre>
     *
     * @Value(name="${config.elasticsearch.elastic.uri}", env="${ES_CLUSTER_HOSTS}")
     * @var array
     */
    protected $uri = [];


    /**
     * Connection timeout
     * @Value(name="${config.elasticsearch.elastic.timeout}", env="${ES_WAIT_TIME}")
     * @var int
     */
    protected $timeout = 3;


    /**
     * index settings
     * @Value(name="${config.settings}")
     * @var string
     */
    protected $setting;


    /**
     * @Value(name="${config.elasticsearch.elastic.productMaster}", env="${ES_PRODUCT_MASTER}")
     * @var string
     */
    protected $product_master;


    /**
     * @Value(name="${config.elasticsearch.elastic.productTask}", env="${ES_PRODUCT_TASK}")
     * @var
     */
    protected $product_task;


    /**
     * @Value(name="${config.elasticsearch.elastic.buyMaster}", env="${ES_BUY_MASTER}")
     * @var string
     */
    protected $buy_master;


    /**
     * @Value(name="${config.elasticsearch.elastic.buyTask}", env="${ES_BUY_TASK}")
     * @var
     */
    protected $buy_task;


    /**
     * @Value(name="${config.elasticsearch.elastic.shopMaster}", env="${ES_SHOP_MASTER}")
     * @var string
     */
    protected $shop_master;


    /**
     * @Value(name="${config.elasticsearch.elastic.shopTask}", env="${ES_SHOP_TASK}")
     * @var string
     */
    protected $shop_task;


    /**
     * @Value(name="${config.elasticsearch.elastic.recommendMaster}", env="${ES_RECOMMEND_MASTER}")
     * @var string
     */
    protected $recommend_master;


    /**
     * @Value(name="${config.elasticsearch.elastic.recommendTask}", env="${ES_RECOMMEND_TASK}")
     * @var string
     */
    protected $recommend_task;


    /**
     * @Value(name="${config.elasticsearch.elastic.addressBookMaster}", env="${ES_ADDRESSBOOK_MASTER}")
     * @var string
     */
    protected $addressBook_master;


    /**
     * @Value(name="${config.elasticsearch.elastic.addressBookTask}", env="${ES_ADDRESSBOOK_TASK}")
     * @var string
     */
    protected $addressBook_task;


    /**
     * @Value(name="${config.addressBook_mapping}")
     * @var array
     */
    protected $addressBook_mapping = [];


    /**
     * @Value(name="${config.recommend_mapping}")
     * @var array
     */
    protected $recommmend_mapping = [];


    /**
     * @Value(name="${config.buy_mapping}")
     * @var array
     */
    protected $buy_mapping = [];


    /**
     * @Value(name="${config.product_mapping}")
     * @var array
     */
    protected $product_mapping = [];


    /**
     * @Value(name="${config.shop_mapping}")
     * @var array
     */
    protected $shop_mapping = [];

    /**
     * @Value(env="${ES_PAGE_SIZE}")
     * @var int
     */
    protected $page_size = 20;


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getUri(): array
    {
        return $this->uri;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return array
     */
    public function getSetting(): array
    {
        return json_decode($this->setting,true);
    }

    /**
     * @return string
     */
    public function getProductMaster() : string
    {
        return $this->product_master;
    }

    /**
     * @return string
     */
    public function getProductTask() : string
    {
        return $this->product_task;
    }

    /**
     * @return string
     */
    public function getBuyMaster() : string
    {
        return $this->buy_master;
    }

    /**
     * @return string
     */
    public function getBuyTask() : string
    {
        return $this->buy_task;
    }

    /**
     * @return string
     */
    public function getShopMaster() : string
    {
        return $this->shop_master;
    }

    /**
     * @return string
     */
    public function getShopTask() : string
    {
        return $this->shop_task;
    }

    /**
     * @return string
     */
    public function getRecommendMaster() : string
    {
        return $this->recommend_master;
    }

    /**
     * @return string
     */
    public function getRecommendTask() : string
    {
        return $this->recommend_task;
    }

    /**
     * @return string
     */
    public function getAddressBookMaster() : string
    {
        return $this->addressBook_master;
    }

    /**
     * @return string
     */
    public function getAddressBookTask() : string
    {
        return $this->addressBook_task;
    }

    /**
     * @return array
     */
    public function getAddressMap(): array
    {
        return $this->addressBook_mapping;
    }

    /**
     * @return array
     */
    public function getShopMap(): array
    {
        return $this->shop_mapping;
    }

    /**
     * @return array
     */
    public function getBuyMap(): array
    {
        return $this->buy_mapping;
    }

    /**
     * @return array
     */
    public function getProductMap(): array
    {
        return $this->product_mapping;
    }

    /**
     * @return array
     */
    public function getRecommendMap(): array
    {
        return $this->recommmend_mapping;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->page_size;
    }

}