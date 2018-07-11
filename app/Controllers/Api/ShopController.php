<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-3
 * Time: 下午1:21
 * Desc: 店铺搜索入口
 */

namespace App\Controllers\Api;

use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;
use App\Models\Logic\ShopSearchLogic;

/**
 * 店铺搜索控制器
 * @Controller(prefix="/shop")
 */
class ShopController
{

    /**
     * 店铺搜索逻辑层
     * @Inject()
     * @var ShopSearchLogic
     */
    private $shopSearchLogic;

    /**
     * 别名注入
     *
     * @RequestMapping(route="shop_list", method={RequestMethod::POST})
     * @param Request $request
     * @return array
     */
    public function get_user_list(Request $request)
    {
        $keyword = $request->post('keyword', '');
        $operation_mode = $request->post('operation_mode',0);
        $level = $request->post('level', -1);
        $certification = $request->post('certification',-1);
        $main_industry = $request->post('main_industry',-1);
        $area_id = $request->post('area_id',0);
        $area_name = $request->post('area_name','');
        $params = [
            'keyword' => $keyword,
            'main_industry' => $main_industry,
            'area_id' => $area_id,
            'area_name' => $area_name,
            'operation_mode' => $operation_mode,
            'level' => $level,
            'certification' => $certification,
        ];
        $searchRes = $this->shopSearchLogic->getShopList($params);
        return compact('searchRes');
    }
}