<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-29
 * Time: 下午2:22
 */

namespace App\Models\Data;

use App\Models\Dao\UserDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      UserData
 * @author    Nihuan
 */
class UserData
{

    /**
     * 用户模型
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     *
     * @author Nihuan
     * @param string $select_fields
     * @param array $where
     * @param int $limit
     * @param int $last_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexUserList($select_fields, $where, $limit, $last_id)
    {
        return $this->userDao->getIndexListDao($select_fields,$where,$limit,$last_id);
    }

    /**
     * @author Nihuan
     * @param array $params 修改/注册时间列表
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getIndexUserCount($params)
    {
        return $this->userDao->getIndexUserCount($params);
    }
}