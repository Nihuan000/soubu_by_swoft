<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-12
 * Time: 上午10:37
 * Desc: 索引mapping setting
 */

return [
  'settings' => '{
    "analysis":{
        "filter":{
            "min3_length":{
                "type":"length",
                "min":3,
                "max":4
            }
        },
        "tokenizer":{
            "ngram_1_to_3":{
                "type":"nGram",
                "min_gram":1,
                "max_gram":3
            }
        },
        "analyzer":{
            "lowercase_whitespace":{
                "type":"custom",
                "tokenizer":"whitespace",
                "filter":[
                    "lowercase"
                ]
            },
            "lowercase_keyword":{
                "type":"custom",
                "tokenizer":"standard",
                "filter":[
                    "lowercase"
                ]
            },
            "lowercase_keyword_ngram":{
                "type":"custom",
                "tokenizer":"ngram_1_to_3",
                "filter":[
                    "lowercase",
                    "min3_length",
                    "stop",
                    "trim",
                    "unique"
                ]
            }
        }
    }
}'
];