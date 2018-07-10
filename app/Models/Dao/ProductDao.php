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
 * 产品模型数据对象
 * @Bean()
 * @uses ProductDao
 * @author Nihuan
 */
class ProductDao
{

    /**
     * 产品对象层
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * 数量获取
     * @author Nihuan
     * @param array $params
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getProductCount(array $params)
    {
        $or = [
            "(alter_time > ? AND alter_time <= ?)"
        ];
        if($params['pre_shop_time'] > 0){
            $user = $this->userDao->getUserIdsByParams($params);
            if(!empty($user)){
                $uid = implode(',',$user);
                $or[] = "user_id IN ($uid)";
            }

        }
        $where = implode(' OR ', $or);

        $count = Db::query(
            "select count(*) AS product_count FROM sb_product WHERE $where",
            [
                $params['pre_alter_time'],
                $params['pro_alter_time']
            ])->getResult();
        return $count[0]['product_count'];
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
    public function getProductList(string $select_fields, array $params, int $limit, int $last_id)
    {
        $or = [
            "(alter_time > ? AND alter_time <= ?)"
        ];
        if($params['pre_shop_time'] > 0){
            $user = $this->userDao->getUserIdsByParams($params);
            if(!empty($user)){
                $uid = implode(',',$user);
                $or[] = "user_id IN ($uid)";
            }
        }
        $where = implode(' OR ', $or);

        $product_list = Db::query("select {$select_fields} FROM sb_product WHERE ($where) AND pro_id > ? ORDER BY pro_id ASC LIMIT {$limit}",
            [
                $params['pre_alter_time'],
                $params['pro_alter_time'],
                $last_id
            ])->getResult();
        return $product_list;
    }
}