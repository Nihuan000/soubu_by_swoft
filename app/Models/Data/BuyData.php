<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-29
 * Time: 下午2:22
 */

namespace App\Models\Data;

use App\Models\Dao\BuyDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * @Bean()
 * @uses BuyData
 * @author Nihuan
 */
class BuyData
{
    /**
     * 采购对象
     * @Inject()
     * @var BuyDao
     */
    private $buyDao;

    /**
     * 获取待索引条数
     * @author Nihuan
     * @param array $where
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexBuyCount(array $where)
    {
        return $this->buyDao->getBuyCount($where);
    }

    /**
     * 采购索引数据方法
     * @author Nihuan
     * @param $select_fields
     * @param $where
     * @param $limit
     * @param $last_id
     * @return array
     */
    public function getIndexBuyList($select_fields, $where, $limit, $last_id)
    {
        return $this->buyDao->getBuyList($select_fields, $where, $limit, $last_id);
    }
}