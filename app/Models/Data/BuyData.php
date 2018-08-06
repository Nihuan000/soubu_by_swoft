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
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexBuyList($select_fields, $where, $limit, $last_id)
    {
        return $this->buyDao->getBuyList($select_fields, $where, $limit, $last_id);
    }


    /**
     * 格式化返回数据
     * @author Nihuan
     * @param array $data
     * @return array
     */
    public function setData(array $data)
    {
        $buyList = [];
        foreach ($data as $buy) {
            $row['buyId'] = intval($buy['_id']);
            $row['amount'] = (int)$buy['_source']['amount'];
            $row['status'] = (int)$buy['_source']['status'];
            $row['unit'] = (string)$buy['_source']['unit'];
            $row['type'] = (int)$buy['_source']['type'];
            $row['pic'] = (string)$buy['_source']['pic'];
            $row['isCustomize'] = (int)$buy['_source']['is_customize'];
            $row['remark'] = (string)$buy['_source']['remark'];
            $row['province'] = isset($buy['_source']['province']) ? (string)$buy['_source']['province'] : '';
            $row['city'] = isset($buy['_source']['city']) ? (string)$buy['_source']['city'] : '';
            $row['role'] = isset($buy['_source']['role']) ? (int)$buy['_source']['role'] : 1;
            $row['addTime'] = (int)$buy['_source']['add_time'];
            $row['alterTime'] = $buy['_source']['refresh_time'] > $buy['_source']['alter_time'] ? (int)$buy['_source']['refresh_time'] : (int)$buy['_source']['alter_time'];
            $row['earnest'] = (string)sprintf('%.2f',$buy['_source']['earnest']);
            $buyList[] = $row;
        }
        return $buyList;
    }
}