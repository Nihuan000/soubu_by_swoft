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
                    'type' => 'string'
                ],
                'role' => [
                    'type' => 'integer'
                ],
                'city' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'is_customize' => [
                    'type' => 'integer'
                ],
                'remark_na' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'remark' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'del_status' => [
                    'type' => 'integer'
                ],
                'pic' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
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
                    'type' => 'string'
                ],
                'phone_is_public' => [
                    'type' => 'integer'
                ],
                'contact_num' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'category_id' => [
                    'analyzer' => 'lowercase_keyword',
                    'type' => 'string'
                ],
                'province' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'has_earnest' => [
                    'type' => 'integer'
                ],
                'push_key' => [
                    'analyzer' => 'ik_smart',
                    'boost' => '1.5',
                    'type' => 'string'
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
                    'type' => 'string'
                ],
                'labels_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'type_id' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'crafts_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'labels' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
                ],
                'unit' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'is_audit' => [
                    'type' => 'integer'
                ],
                'province_id' => [
                    'type' => 'integer'
                ],
                'uses_ids' => [
                    'analyzer' => 'ik_smart',
                    'type' => 'string'
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
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'add_time' => [
                    'type' => 'integer'
                ],
                'contacts' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
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