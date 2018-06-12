<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-12
 * Time: 上午11:13
 * Desc: 通讯录索引mapping
 */

return [
    'mapping' => [
        'addressBook' => [
            'dynamic' => false,
            'properties' => [
                'safe_price' => [
                    'type' => 'double'
                ],
                'role' => [
                    'type' => 'integer'
                ],
                'name_na' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'level' => [
                    'type' => 'integer'
                ],
                'operation_mode' => [
                    'type' => 'integer'
                ],
                'strength_score' => [
                    'type' => 'long'
                ],
                'certification_type' => [
                    'type' => 'integer'
                ],
                'forbid' => [
                    'type' => 'integer'
                ],
                'portrait' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'name_search_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'has_margin' => [
                    'type' => 'integer'
                ],
                'phone' => [
                    'index' => 'not_analyzed',
                    'type' => 'string'
                ],
                'phone_search_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ]
            ]
        ]
    ]
];