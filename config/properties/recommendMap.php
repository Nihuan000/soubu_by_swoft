<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-12
 * Time: 上午11:13
 * Desc: 商机推荐索引mapping
 */

return [
  'recommend_mapping' => [
      'recommend' => [
          'dynamic' => false,
          'properties' => [
              'name' => [
                  'type' => 'text',
                  'analyzer' => 'lowercase_keyword_ngram'
              ],
              'main_product' => [
                  'type' => 'text',
                  'analyzer' => 'ik_smart'
              ],
              'main_product_ids' => [
                  'type' => 'text',
                  'analyzer' => 'ik_smart'
              ],
              'main_product_normalized' => [
                  'type' => 'text',
                  'analyzer' => 'lowercase_whitespace'
              ],
              'login_time' => [
                  'type' => 'integer'
              ],
              'certification_type' => [
                  'type' => 'integer'
              ],
              'cid' => [
                  'type' => 'keyword',
                  'index' => true
              ],
              'status' => [
                  'type' => 'integer'
              ],
              'level' => [
                  'type' => 'integer'
              ],
              'ranking' => [
                  'type' => 'integer'
              ],
              'deposit' => [
                  'type' => 'integer'
              ],
              'push_type' => [
                  'type' => 'integer'
              ],
              'push_period' => [
                  'type' => 'text',
                  'analyzer' => 'ik_smart'
              ],
              'parent_normalized' => [
                  'type' => 'text',
                  'analyzer' => 'lowercase_whitespace'
              ],
              'parent_ids' => [
                  'type' => 'text',
                  'analyzer' => 'ik_smart'
              ],
              'main_type_normalized' => [
                  'type' => 'text',
                  'analyzer' => 'lowercase_whitespace'
              ],
              'main_type_ids' => [
                  'type' => 'text',
                  'analyzer' => 'ik_smart'
              ],
              'main_type' => [
                  'type' => 'text',
                  'analyzer' => 'ik_smart'
              ],
              'product_name_normalized' => [
                  'type' => 'text',
                  'analyzer' => 'lowercase_whitespace'
              ],
          ]
      ]
  ]
];