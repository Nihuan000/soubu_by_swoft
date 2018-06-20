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
                    'type' => 'string',
                ],
                'phone' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
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
                    'index' => 'not_analyzed',
                    'type' => 'string'
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
                'shop_main_product' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'login_time' => [
                    'type' => 'integer'
                ],
                'has_margin' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'name_na' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'province' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
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
                'shop_main_product_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'portrait' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
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
                    'type' => 'string'
                ],
                'from_type' => [
                    'type' => 'integer'
                ],
                'strength_score' => [
                    'type' => 'long'
                ],
                'main_product_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'forbid' => [
                    'type' => 'integer'
                ]
            ]
        ]
    ]
];