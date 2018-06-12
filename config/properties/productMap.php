<?php
/**
 * Created by PhpStorm.
 * User =>  nihuan
 * Date =>  18-6-12
 * Time =>  上午11 => 11
 * Desc =>  产品索引mapping
 */

return [
    'mapping' => [
        'product' => [
            'dynamic' => false,
            'properties' => [
                'pro_item' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'strength_score' => [
                    'type' => 'long'
                ],
                'type' => [
                    'type' => 'integer'
                ],
                'refresh_time' => [
                    'type' => 'integer'
                ],
                'product_number' => [
                    'type' => 'integer'
                ],
                'cover' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'has_img' => [
                    'type' => 'integer'
                ],
                'phone_is_public' => [
                    'type' => 'integer'
                ],
                'has_margin' => [
                    'type' => 'integer'
                ],
                'province' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'price' => [
                    'ignore_malformed' => 'true',
                    'type' => 'double'
                ],
                'product_id' => [
                    'type' => 'long'
                ],
                'season' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'refresh_count' => [
                    'type' => 'integer'
                ],
                'orders_total' => [
                    'type' => 'long'
                ],
                'cut_price' => [
                    'ignore_malformed' => 'true',
                    'type' => 'double'
                ],
                'deposit_time' => [
                    'type' => 'integer'
                ],
                'shop_name' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'is_up' => [
                    'type' => 'integer'
                ],
                'crafts' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'use_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'user_id' => [
                    'type' => 'long'
                ],
                'is_recommend' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'gram_w' => [
                    'ignore_malformed' => 'true',
                    'type' => 'double'
                ],
                'deposit' => [
                    'type' => 'integer'
                ],
                'card_ship_type' => [
                    'ignore_malformed' => 'true',
                    'type' => 'integer'
                ],
                'status' => [
                    'ignore_malformed' => 'true',
                    'type' => 'integer'
                ],
                'city_id' => [
                    'type' => 'integer'
                ],
                'pro_name' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'color' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'name_na' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'city' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'lv1_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'del_status' => [
                    'ignore_malformed' => 'true',
                    'type' => 'integer'
                ],
                'forbid' => [
                    'type' => 'integer'
                ],
                'card_price' => [
                    'ignore_malformed' => 'true',
                    'type' => 'double'
                ],
                'alter_time' => [
                    'type' => 'integer'
                ],
                'label_key' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'from_type' => [
                    'type' => 'integer'
                ],
                'use_pid' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'user_status' => [
                    'ignore_malformed' => 'true',
                    'type' => 'integer'
                ],
                'safe_price' => [
                    'type' => 'double'
                ],
                'ingredient' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'lv1' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'fabric_detail' => [
                    'analyzer' => 'ik_smart',
                    'boost' => '0.2',
                    'type' => 'string'
                ],
                'flower' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'spread' => [
                    'type' => 'integer'
                ],
                'suffix_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'is_audit' => [
                    'type' => 'integer'
                ],
                'province_id' => [
                    'type' => 'integer'
                ],
                'orders_amount' => [
                    'type' => 'double'
                ],
                'clicks' => [
                    'type' => 'long'
                ],
                'uses' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'add_time' => [
                    'type' => 'integer'
                ],
                'pro_num' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ]
            ]
        ]
    ]
];