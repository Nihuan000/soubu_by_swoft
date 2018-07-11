<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-29
 * Time: 下午2:22
 */

namespace App\Models\Data;

use App\Models\Dao\ProductDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * @Bean()
 * @uses ProductData
 * @author Nihuan
 */
class ProductData
{

    /**
     * 产品模型
     * @Inject()
     * @var ProductDao
     */
    private $productDao;


    /**
     * 格式化店铺产品返回字段
     * @author Nihuan
     * @param array $data
     * @return array
     */
    public function simpleShopProduct(array $data)
    {
        $dataList = [];
        foreach ($data as $row) {
            $product['productId'] = intval($row['_id']);
            $product['name'] = $row['_source']['name'];
            $product['price'] = $row['_source']['price'];
            $product['cover'] = $row['_source']['cover'];
            $dataList[] = $product;
        }
        return $dataList;
    }


    /**
     * @author Nihuan
     * @param array $where
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexProductCount(array $where)
    {
        return $this->productDao->getProductCount($where);
    }


    /**
     * 产品索引数据方法
     * @author Nihuan
     * @param $select_fields
     * @param $where
     * @param $limit
     * @param $last_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexProductList($select_fields, $where, $limit, $last_id)
    {
        return $this->productDao->getProductList($select_fields,$where,$limit,$last_id);
    }


    /**
     * 格式化返回数据
     * @author Nihuan
     * @param array $data
     * @return array
     */
    public function setData(array $data)
    {
        $dataList = [];
        foreach ($data as $pro) {
            $product['productId'] = intval($pro['_id']);
            $product['name'] = $pro['_source']['name'];
            $product['price'] = sprintf('%.2f', $pro['_source']['price']);
            $product['fabricDetail'] = $pro['_source']['fabric_detail'];
            $product['type'] = intval($pro['_source']['type']);
            $product['areaId'] = intval($pro['_source']['city_id']);
            $product['cover'] = $pro['_source']['cover'];
            $product['uses'] = $pro['_source']['uses'];
            $product['alterTime'] = intval($pro['_source']['alter_time']);
            $product['spread'] = 0;
            $product['city'] = $pro['_source']['city'];
            $product['deposit'] = intval($pro['_source']['deposit']);
            $product['proNum'] = $pro['_source']['pro_num'];
            $product['userId'] = intval($pro['_source']['user_id']);
            $dataList[] = $product;
        }
        return $dataList;
    }

}