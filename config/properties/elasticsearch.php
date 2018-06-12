<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 下午2:22
 */

return [
    'elastic'     => [
        'name'        => 'soubu-search',
        'uri'         => [
            '192.168.1.222:8200',
        ],
        'timeout' => 3,
        'xPackSwitch'   => 0,
        'xPackUser'   => 'nihuan:123456',
        'productTask' => 'product_increment',
        'shopTask' => 'shop_increment',
        'buyTask' => 'buy_increment',
        'recommendTask' => 'recommend_increment',
        'addressBookTask' => 'addressBook_increment',
        'productMaster' => 'product_5_5_0',
        'shopMaster' => 'shop_5_5_0',
        'buyMaster' => 'buy_5_5_0',
        'recommendMaster' => 'recommend_5_5_0',
        'addressBookMaster' => 'addressBook_5_5_0',
    ],
];