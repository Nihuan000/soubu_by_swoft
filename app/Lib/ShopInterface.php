<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午2:33
 */

namespace App\Lib;

use Swoft\Core\ResultInterface;

/**
 * The interface of shop service
 *
 * @method ResultInterface deferGetUsers(array $ids)
 * @method ResultInterface deferGetUser(string $id)
 * @method ResultInterface deferGetUserByCond(int $type, int $uid, string $name, float $price, string $desc = "desc")
 */
interface ShopInterface
{
    public function get_shop_list();
}