<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午3:31
 */

namespace App\Controllers\Api;
use App\Models\Logic\BuySearchLogic;
use Swoft\Http\Message\Server\Request;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;

/**
 * 采购搜索控制器
 * @Controller(prefix="/buy")
 */
class BuyController
{

    /**
     * @inject()
     * @var BuySearchLogic
     */
    private $buySearchLogic;

    /**
     * 采购列表搜索
     * @author Nihuan
     * @RequestMapping(route="buy_list", method={RequestMethod::POST})
     * @param Request $request
     * @return array
     */
    public function get_buy_list(Request $request)
    {
        $keyword = $request->post('keyword');
        $areaId = $request->post('areaId');
        $proNameIds = $request->post('proNameIds');
        $usesIds = $request->post('usesIds');
        $parentId = $request->post('parentId');
        $isHot = $request->post('isHot');
        $isCustomize = $request->post('isCustomize');
        $buyIds = $request->post('buyIds');
        $page = $request->post('page');
        $pageSize = $request->post('pageSize');
        $params = [
            'keyword' => $keyword,
            'areaId' => (int)$areaId,
            'proNameIds' => json_decode($proNameIds,true),
            'usesIds' => json_decode($usesIds,true),
            'parentId' => (int)$parentId,
            'isHot' => (int)$isHot,
            'isCustomize' => (int)$isCustomize,
            'buyIds' => json_decode($buyIds,true),
            'page' => (int)$page,
            'pageSize' => (int)$pageSize
        ];
        $searchRes = $this->buySearchLogic->getBuyList($params);
        return compact('searchRes');
    }
}