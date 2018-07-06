<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-4
 * Time: 下午1:48
 */

namespace App\Models\Data;

use Swoft\Bean\Annotation\Bean;

/**
 * @Bean()
 * @uses ShopData
 * @author Nihuan
 */
class ShopData
{

    /**
     * 格式化返回字段
     * @author Nihuan
     * @param array $data
     * @return array
     */
    public function setData(array $data)
    {
        $dataList = [];
        foreach ($data as $row) {
            $shop['userId'] = intval($row['_source']['user_id']);
            $shop['name'] = $row['_source']['name'];
            $shop['portrait'] = $row['_source']['portrait'];
            $shop['mainProduct'] = $row['_source']['main_product'];
            $shop['province'] = $row['_source']['province'];
            $shop['city'] = $row['_source']['city'];
            $shop['deposit'] = intval($row['_source']['deposit']);
            $shop['level'] = intval($row['_source']['level']);
            $shop['certificationType'] = intval($row['_source']['certification_type']);
            $shop['operationMode'] = intval($row['_source']['operation_mode']);
            $shop['safePrice'] = $row['_source']['safe_price'];
            $shop['products'] = [];
            $dataList[] = $shop;
        }
        return $dataList;
    }
}