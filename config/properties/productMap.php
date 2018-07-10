<?php
/**
 * Created by PhpStorm.
 * User =>  nihuan
 * Date =>  18-6-12
 * Time =>  上午11 => 11
 * Desc =>  产品索引mapping
 */

return [
    'product_mapping' => [
        'product' => [
            'dynamic' => false,
            'properties' => [
                'pro_item' => [
                    'index' => true,
                    'type' => 'keyword'
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
                'cover' => [
                    'index' => true,
                    'type' => 'keyword'
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
                    'index' => true,
                    'type' => 'keyword'
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
                    'type' => 'text'
                ],
                'refresh_count' => [
                    'type' => 'integer'
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
                    'type' => 'text'
                ],
                'is_up' => [
                    'type' => 'integer'
                ],
                'crafts' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'use_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'user_id' => [
                    'type' => 'long'
                ],
                'is_recommend' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
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
                    'type' => 'text'
                ],
                'color' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'name_na' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'city' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'lv1_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'text'
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
                    'type' => 'text'
                ],
                'from_type' => [
                    'type' => 'integer'
                ],
                'use_pid' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
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
                    'type' => 'text'
                ],
                'lv1' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'fabric_detail' => [
                    'analyzer' => 'ik_smart',
                    'boost' => '0.2',
                    'type' => 'text'
                ],
                'flower' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'spread' => [
                    'type' => 'integer'
                ],
                'suffix_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'text'
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
                    'type' => 'text'
                ],
                'add_time' => [
                    'type' => 'integer'
                ],
                'valid_time' => [
                    'type' => 'integer'
                ],
                'pro_num' => [
                    'index' => true,
                    'type' => 'keyword'
                ]
            ]
        ]
    ]
];