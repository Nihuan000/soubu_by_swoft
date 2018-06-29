<?php
/**
 * Created by PhpStorm.
 * User =>  nihuan
 * Date =>  18-6-12
 * Time =>  上午11 => 13
 * Desc =>  采购索引mapping
 */

return [
    'buy_mapping' => [
        'buy' => [
            'dynamic' => false,
            'properties' => [
                'parent' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'role' => [
                    'type' => 'integer'
                ],
                'city' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'is_customize' => [
                    'type' => 'integer'
                ],
                'remark_na' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'remark' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'del_status' => [
                    'type' => 'integer'
                ],
                'pic' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'forbid' => [
                    'type' => 'integer'
                ],
                'type' => [
                    'type' => 'integer'
                ],
                'refresh_time' => [
                    'type' => 'integer'
                ],
                'audit_time' => [
                    'type' => 'integer'
                ],
                'has_img' => [
                    'type' => 'integer'
                ],
                'proName_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'phone_is_public' => [
                    'type' => 'integer'
                ],
                'contact_num' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'category_id' => [
                    'analyzer' => 'lowercase_keyword',
                    'type' => 'text'
                ],
                'province' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'has_earnest' => [
                    'type' => 'integer'
                ],
                'push_key' => [
                    'analyzer' => 'ik_smart',
                    'boost' => '1.5',
                    'type' => 'text'
                ],
                'sms_status' => [
                    'type' => 'integer'
                ],
                'refresh_count' => [
                    'type' => 'integer'
                ],
                'alter_time' => [
                    'type' => 'integer'
                ],
                'from_type' => [
                    'type' => 'integer'
                ],
                'user_status' => [
                    'type' => 'integer'
                ],
                'amount' => [
                    'type' => 'integer'
                ],
                'push_status' => [
                    'type' => 'integer'
                ],
                'label_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'labels_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'text'
                ],
                'type_id' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'crafts_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'labels' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'unit' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'is_audit' => [
                    'type' => 'integer'
                ],
                'province_id' => [
                    'type' => 'integer'
                ],
                'uses_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'text'
                ],
                'user_id' => [
                    'type' => 'long'
                ],
                'is_find' => [
                    'type' => 'integer'
                ],
                'clicks' => [
                    'type' => 'long'
                ],
                'earnest' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'add_time' => [
                    'type' => 'integer'
                ],
                'contacts' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'city_id' => [
                    'type' => 'integer'
                ],
                'status' => [
                    'type' => 'integer'
                ]
            ]
        ]
    ]
];