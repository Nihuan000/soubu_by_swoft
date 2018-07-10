<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-29
 * Time: 下午2:22
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;

/**
 * 采购模型数据对象
 * @Bean()
 * @uses BuyDao
 * @author Nihuan
 */
class BuyDao
{
    /**
     * 数量获取
     * @author Nihuan
     * @param array $params
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getBuyCount(array $params)
    {
        $or = [
            "(alter_time > ? AND alter_time <= ?)",
            "(refresh_time > ? AND refresh_time <= ?)"
        ];
        $where = implode(' OR ', $or);
        $count = Db::query(
            "select count(*) AS buy_count FROM sb_buy WHERE $where",
            [
                $params['pre_alter_time'],
                $params['buy_alter_time'],
                $params['pre_refresh_time'],
                $params['buy_refresh_time']
            ])->getResult();
        return $count[0]['buy_count'];
    }

    /**
     * @author Nihuan
     * @param string $select_fields
     * @param array $params
     * @param int $limit
     * @param int $last_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getBuyList(string $select_fields, array $params, int $limit, int $last_id)
    {
        $or = [
            "(alter_time > ? AND alter_time <= ?)",
            "(refresh_time > ? AND refresh_time <= ?)"
        ];
        $where = implode(' OR ', $or);

        $buy_list = Db::query("select {$select_fields} FROM sb_buy WHERE ($where) AND buy_id > ? ORDER BY buy_id ASC LIMIT {$limit}",
            [
                $params['pre_alter_time'],
                $params['buy_alter_time'],
                $params['pre_refresh_time'],
                $params['buy_refresh_time'],
                $last_id
            ])->getResult();
        return $buy_list;
    }
}