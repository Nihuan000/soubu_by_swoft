<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-10
 * Time: 下午1:49
 * Desc: 产品搜索入口
 */

namespace App\Controllers\Api;

use App\Models\Logic\ProductSearchLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;

/**
 * 产品搜索/推荐控制器
 * @Controller(prefix="/product")
 */
class ProductController
{

    /**
     * 产品搜索逻辑层
     * @Inject()
     * @var ProductSearchLogic
     */
    protected $productSearchLogic;


    /**
     * @author Nihuan
     * @RequestMapping(route="product_list", method={RequestMethod::POST})
     * @param Request $request
     * @return array
     */
    public function get_product_list(Request $request)
    {
        $shopId = (int)$request->post('shopId',0);
        $type = (int)$request->post('type',0);
        $keyword = $request->post('keyword','');
        $provinceId = (int)$request->post('provinceId',0);
        $cityId = (int)$request->post('cityId',0);
        $isNew = $request->post('isNew',0);
        $priceGte = (double)$request->post('priceGte',0);
        $priceLte = (double)$request->post('priceLte',0);
        $season = $request->post('season','');
        $uses = $request->post('uses','');
        $deposit = (int)$request->post('deposit',0);
        $params = [
            'shop_id' => $shopId,
            'type' => $type,
            'keyword' => $keyword,
            'province_id' => $provinceId,
            'city_id' => $cityId,
            'is_new' => $isNew,
            'price_gte' => $priceGte,
            'price_lte' => $priceLte,
            'season' => $season,
            'uses' => $uses,
            'deposit' => $deposit
        ];
        $searchRes = $this->productSearchLogic->getProductList($params);
        return compact('searchRes');
    }


    /**
     * 产品关联产品搜索
     * @author Nihuan
     * @RequestMapping()
     * @param Request $request
     * @return array
     */
    public function get_relation_list(Request $request)
    {
        $product_id = (int)$request->post('productId');
        $page = (int)$request->post('page');
        $pageSize = (int)$request->post('pageSize');
        $shopSelf = (int)$request->post('shopSelf');
        $exceptSelf = (int)$request->post('exceptSelf');
        $is_self = 1;
        if($shopSelf == 1) {
            $is_self = 1;
        }
        if($exceptSelf == 1){
            $is_self = 0;
        }
        $params = [
            'product_id' => $product_id,
            'page' => $page,
            'pageSize' => $pageSize,
            'is_self' => $is_self
        ];
        $searchRes = [];
        return compact('searchRes');
    }
}