<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-29
 * Time: 下午2:22
 */

namespace App\Models\Dao;

use App\Models\Entity\User;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Db;
use Swoft\Db\Query;
use Swoft\Db\QueryBuilder;

/**
 * 用户模型数据对象
 * @Bean()
 * @uses UserDao
 * @author Nihuan
 */
class UserDao
{

    /**
     * 获取用户列表
     * @author Nihuan
     * @param string $select_fields
     * @param array $params
     * @param int $limit
     * @param int $last_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexListDao(string $select_fields, array $params, int $limit, int $last_id)
    {
        $user_list = Db::query(
            "select {$select_fields} FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE '%搜布%' AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?)) AND user_id > ? ORDER BY user_id ASC LIMIT {$limit}",
            [
                $params['pre_alter_time'],
                $params['shop_alter_time'],
                $params['pre_reg_time'],
                $params['shop_reg_time'],
                $last_id
            ])->getResult();
        return $user_list;
    }

    /**
     * 数量获取
     * @author Nihuan
     * @param array $params
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexUserCount(array $params)
    {
        $count = Db::query(
            "select count(*) AS user_count FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE '%搜布%' AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?))",
            [
                $params['pre_alter_time'],
                $params['shop_alter_time'],
                $params['pre_reg_time'],
                $params['shop_reg_time']
            ])->getResult();
        return $count[0]['user_count'];
    }
}