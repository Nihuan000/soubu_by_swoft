<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-12
 * Time: 上午11:13
 * Desc: 通讯录索引mapping
 */

return [
    'addressBook_mapping' => [
        'addressbook' => [
            'dynamic' => false,
            'properties' => [
                'safe_price' => [
                    'type' => 'double'
                ],
                'role' => [
                    'type' => 'integer'
                ],
                'name_na' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'level' => [
                    'type' => 'integer'
                ],
                'operation_mode' => [
                    'type' => 'integer'
                ],
                'certification_type' => [
                    'type' => 'integer'
                ],
                'forbid' => [
                    'type' => 'integer'
                ],
                'portrait' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'name_search_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ],
                'has_margin' => [
                    'type' => 'integer'
                ],
                'phone' => [
                    'index' => true,
                    'type' => 'keyword'
                ],
                'phone_search_normalized' => [
                    'analyzer' => 'lowercase_whitespace',
                    'type' => 'string'
                ]
            ]
        ]
    ]
];