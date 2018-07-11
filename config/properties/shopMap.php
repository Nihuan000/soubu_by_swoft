<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-12
 * Time: 上午10:49
 * Desc: 店铺索引mapping
 */

return [
    'shop_mapping' => [
        'shop' => [
            'dynamic' => false,
            'properties' => [
                'detail_address' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text',
                ],
                'phone' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'phone_is_protected' => [
                    'type' => 'integer'
                ],
                'deposit' => [
                    'type' => 'integer'
                ],
                'operation_mode' => [
                    'type' => 'integer'
                ],
                'has_portrait' => [
                    'type' => 'boolean'
                ],
                'is_push' => [
                    'type' => 'integer'
                ],
                'city' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'city_id' => [
                    'type' => 'integer'
                ],
                'safe_price' => [
                    'type' => 'double'
                ],
                'level' => [
                    'type' => 'integer'
                ],
                'login_time' => [
                    'type' => 'integer'
                ],
                'has_margin' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text',
                ],
                'name_na' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'province' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'role' => [
                    'type' => 'integer'
                ],
                'alter_time' => [
                    'type' => 'integer'
                ],
                'certification_type' => [
                    'type' => 'integer'
                ],
                'phone_type' => [
                    'type' => 'integer'
                ],
                'status' => [
                    'type' => 'integer'
                ],
                'province_id' => [
                    'type' => 'integer'
                ],
                'deposit_time' => [
                    'type' => 'integer'
                ],
                'reg_time' => [
                    'type' => 'integer'
                ],
                'portrait' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'order_status' => [
                    'type' => 'integer'
                ],
                'company_size' => [
                    'type' => 'integer'
                ],
                'orders_amount' => [
                    'type' => 'double'
                ],
                'main_product' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text',
                ],
                'from_type' => [
                    'type' => 'integer'
                ],
                'strength_score' => [
                    'type' => 'long'
                ],
                'main_product_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'text'
                ],
                'forbid' => [
                    'type' => 'integer'
                ]
            ]
        ]
    ]
];