<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 上午10:36
 */

namespace App\Controllers;

use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use App\Models\Entity\TbOut2csv;
use App\Models\Entity\TbProcess;
use App\Models\Entity\UserScore;
use App\Models\Entity\UserStrength;
use App\Pool\Config\ElasticsearchPoolConfig;
use Elasticsearch\ClientBuilder;
use Swoft\Bean\Annotation\Value;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Db\Query;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\QueryBuilder;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use App\Models\Entity\User;
use Swoft\App;

/**
 * action demo
 *
 * @Controller(prefix="/test")
 */
class TestController
{
    //每次读取数据量
    protected $limit = 5000;
    //批量写入ES数据量
    protected $page_limit = 1000;
    //定时器(分钟)
    protected $timer = 3;

    /**
     * @Value(env="${SEARCH_DB_NAME}")
     * @var string
     */
    protected $searchDbName;


    /**
     * The config of ElasticsearchPool
     *
     * @Inject()
     *
     * @var ElasticsearchPoolConfig
     */
    protected $elasticPoolConfig;


    /**
     * 用户数据
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 产品数据
     * @Inject()
     * @var ProductData
     */
    private $productData;

    /**
     * @RequestMapping()
     * @author Nihuan
     */
    public function index()
    {
        $userInfo = User::findAll(['name' => '供应商'])->getResult();

        return $userInfo;
    }


    /**
     * @author Nihuan
     * @RequestMapping()
     * @throws DbException
     */
    public function productService()
    {
        $pro_alter_time_pre = $pro_add_time_pre = $shop_alter_time_pre = 0;
        $pro_alter_time = $pro_add_time = $shop_alter_time = time();
        echo 'Product Index Task Start' . "\n";
//        App::info('Product Index Task Start');
//        App::info('Version:' . $this->elasticPoolConfig->getProductMaster());
//        App::info('Check if the index exists');
        $client = ClientBuilder::create()->setHosts($this->elasticPoolConfig->getUri())->build();
        $params = [
            'index' => $this->elasticPoolConfig->getProductMaster(),
        ];
        $index_exists = $client->indices()->exists($params);
        if($index_exists == false){
            $product_params = [
                'index' => $this->elasticPoolConfig->getProductMaster(),
                'body' => [
                    'settings' => $this->elasticPoolConfig->getSetting(),
                    'mappings' => $this->elasticPoolConfig->getProductMap()
                ]
            ];
            $responseRes = $client->indices()->create($product_params);
            $index_exists = $responseRes['acknowledged'];
        }

        if($index_exists === true){
            $out2csv = Query::table(TbOut2csv::class)
                ->selectDb($this->searchDbName)
                ->where('out2csv_id',$this->elasticPoolConfig->getProductMaster())
                ->limit(1)
                ->orderBy('id',QueryBuilder::ORDER_BY_DESC)
                ->get()
                ->getResult();
            if(!empty($out2csv)){
                $check_process = $this->get_process_record($out2csv[0]['id']);
                if($check_process){
//                    App::info('Product Index Task Is Running');
                    return;
                }
                $last_time = $out2csv[0]['parameter'];
                $last_time_list = json_decode($last_time,true);
                $shop_alter_time_pre = $last_time_list['shop_alter_time'];
                $pro_alter_time_pre = $last_time_list['pro_alter_time'];
                $pro_add_time_pre = $last_time_list['pro_add_time'];
            }

//            try {
                //记录索引时间
                $last['pro_add_time.pre'] = $pro_add_time_pre;
                $last['pro_alter_time.pre'] = $pro_alter_time_pre;
                $last['shop_alter_time.pre'] = $shop_alter_time_pre;
                $last['pro_add_time'] = $pro_add_time;
                $last['pro_alter_time'] = $pro_alter_time;
                $last['shop_alter_time'] = $shop_alter_time;
//                $o2c_id = $this->add_index_time($this->elasticPoolConfig->getProductMaster(), $last);
//                $this->add_process_record($o2c_id);

                $select_fields = "pro_id AS product_id,cover,case when cover is not null and LENGTH(cover) > 0 then 1 else 0 end has_img,user_id,pro_name,name,name as name_na,uses,ingredient,crafts,price,cut_price,card_price,pro_item,gram_w,season,color,flower,status,is_recommend,del_status,fabric_detail,phone_is_public,clicks,is_up,pro_num,type,add_time,alter_time,refresh_time,refresh_count,card_ship_type,status,label_key,from_type,is_audit,valid_time";

                $where = [
                    'pre_alter_time' => $pro_alter_time_pre,
                    'pre_add_time' => $pro_add_time_pre,
                    'pre_shop_time' => $shop_alter_time_pre,
                    'pro_alter_time' => $pro_alter_time,
                    'pro_add_time' => $pro_add_time,
                    'shop_alter_time' => $shop_alter_time
                ];
                $count = $this->productData->getIndexProductCount($where);
                if($count > 0){
                    $last_id = 0;
                    $pages = ceil($count/$this->limit);
                    for ($i = 0; $i< $pages; $i++){
                        $product_list = $this->productData->getIndexProductList($select_fields, $where, $this->limit, $last_id);
                        if(!empty($product_list)){
                            $count_pages = ceil(count($product_list)/$this->page_limit);
                            for ($c = 0;$c < $count_pages; $c ++){
                                $list = array_splice($product_list, 0, $this->page_limit);
                                if(!empty($list)){
                                    $pro_ids = array_column($list,'product_id');
                                    //product_use
                                    $product_use_ids = [];
                                    $product_uses = $this->get_product_uses($pro_ids);
                                    if($product_uses[0]['pro_id'] != null){
                                        $product_use_ids = array_flip(array_column($product_uses,'pro_id'));
                                    }

                                    $user_ids = array_unique(array_column($list,'user_id'));
                                    //deposit
                                    $deposit = $this->get_user_deposit($user_ids);
                                    $deposit_uids = array_flip(array_column($deposit,'userId'));
                                    //forbid
                                    $forbid = $this->get_forbid_users($user_ids);
                                    $forbid_uids = array_flip(array_column($forbid,'uid'));
                                    //orders_amount
                                    $order_amount = $this->get_user_month_order($user_ids);
                                    $order_amount_uids = array_flip(array_column($order_amount,'seller_id'));
                                    //strength_score
                                    $strength_score = $this->get_user_score($user_ids);
                                    $strength_score_uids = array_flip(array_column($strength_score,'user_id'));
                                    //user_info
                                    $user_info = $this->get_user_info($user_ids);
                                    $user_info_uids = array_flip(array_column($user_info,'user_id'));
                                    $bulk = $index = [];
                                    foreach ($list as $key => $product){
                                        $index = $product;
                                        $index['strength_score'] = 0;
                                        $index['has_margin'] = 0;
                                        $index['province'] = '';
                                        $index['deposit_time'] = 0;
                                        $index['shop_name'] = '';
                                        $index['use_ids'] = '';
                                        $index['deposit'] = 0;
                                        $index['city_id'] = 0;
                                        $index['city'] = '';
                                        $index['forbid'] = 0;
                                        $index['use_pid'] = '';
                                        $index['user_status'] = 0;
                                        $index['safe_price'] = 0;
                                        $index['province_id'] = 0;
                                        $index['orders_amount'] = 0;
                                        $index['lv1'] = $index['lv1_normalized'] = $index['suffix_normalized'] = '';

                                        $index['season'] = explode(',',$product['season']);
                                        if(isset($product_use_ids[$product['user_id']])){
                                            $uses = $product_uses[$product_use_ids[$product['user_id']]];
                                            $index['use_ids'] = explode(',',$uses['use_ids']);
                                            $index['use_pid'] = explode(',',$uses['use_pid']);
                                        }

                                        if(isset($user_info_uids[$product['user_id']])){
                                            $info = $user_info[$user_info_uids[$product['user_id']]];
                                            $index['has_margin'] = $info['safe_price'] > 0 ? 1 : 0;
                                            $index['province'] = $info['province'];
                                            $index['shop_name'] = $info['name'];
                                            $index['city_id'] = (int)$info['city_id'];
                                            $index['city'] = $info['city'];
                                            $index['user_status'] = $info['user_status'];
                                            $index['province_id'] = (int)$info['province_id'];
                                            $index['safe_price'] = $info['safe_price'];
                                        }

                                        if (isset($deposit_uids[$product['user_id']])){
                                            $user_deposit = $deposit[$deposit_uids[$product['user_id']]];
                                            $index['deposit'] = intval($user_deposit['level']/5);
                                            $index['deposit_time'] = $user_deposit['startTime'];
                                        }

                                        if (isset($forbid_uids[$product['user_id']])){
                                            $index['forbid'] = 1;
                                        }

                                        if(isset($order_amount_uids[$product['user_id']])){
                                            $order = $order_amount[$order_amount_uids[$product['user_id']]];
                                            $index['orders_amount'] = sprintf('%.2f',$order['total_amount']);
                                        }

                                        if(isset($strength_score_uids[$product['user_id']])){
                                            $score = $strength_score[$strength_score_uids[$product['user_id']]];
                                            $index['strength_score'] = intval($score['score_value']);
                                        }
                                        $bulk['body'][] = [
                                            'index' => [
                                                '_index' => $this->elasticPoolConfig->getShopMaster(),
                                                '_type' => 'shop',
                                                '_id' => $product['user_id']
                                            ]
                                        ];
                                        $bulk['body'][] = $index;
                                        $last_id = $product['product_id'];
                                    }
                                    $res = $client->bulk($bulk);
                                    $bulk = $index = [];
                                    if($res){
                                        App::info('Product Index Result : ' . json_encode($res));
                                    }
                                }
                                $user_ids = $pro_ids = $list = $deposit = $main_product = [];
                                $deposit_uids = $product_uses = $forbid_uids = $main_product_uids = $order_amount_uids = $strength_score_uids = [];
                            }
                            $product_list = [];
                        }
                        App::info('Last Id:' . $last_id);
                    }
                }
//                $this->alter_process_record($o2c_id);
//            } catch (DbException $e) {
//                App::debug('SQL Failed to run');
//            } catch (DbException $e) {
//                App::debug($e->getMessage());
//            } catch (MysqlException $e) {
//            }
        }
        App::info('User Index Task End!');
    }


    /**
     * 获取实力商家
     * @author Nihuan
     * @param array $user_ids
     * @return array
     */
    protected function get_user_deposit(array $user_ids)
    {
        $result = UserStrength::findAll(['user_id'=> $user_ids, 'is_expire' => 0],['fields' => ['user_id', 'level', 'start_time']])->getResult();
        $deposit_list = json_decode(json_encode($result),true);
        return $deposit_list;
    }


    /**
     * 获取用户主营
     * @author Nihuan
     * @param array $user_ids
     * @return mixed
     * @throws DbException
     */
    protected function get_user_main_product(array $user_ids)
    {
        $ids = implode(',',$user_ids);
        $result = Db::query("SELECT user_id, GROUP_CONCAT(distinct tag_name) sub_tag, GROUP_CONCAT(distinct parent_name) tags, GROUP_CONCAT(distinct top_name) parents, GROUP_CONCAT(distinct top_id) parent_tags FROM sb_user_subscription_tag WHERE user_id IN ({$ids}) GROUP BY user_id")->getResult();
        return $result;
    }

    /**
     * 获取用户月订单额
     * @author Nihuan
     * @param array $user_ids
     * @return mixed
     * @throws DbException
     */
    protected function get_user_month_order(array $user_ids)
    {
        $ids = implode(',',$user_ids);
        $last_time = time() - 2592000;
        $order_list = Db::query("SELECT seller_id,sum(total_order_price) AS total_amount FROM sb_order WHERE seller_id IN ({$ids}) AND status = 4 AND take_time >= {$last_time} GROUP BY seller_id")->getResult();
        return $order_list;
    }


    /**
     * 获取搜索过滤用户
     * @author Nihuan
     * @param $user_ids
     * @return mixed
     */
    protected function get_forbid_users($user_ids)
    {
        $forbid_list = Query::table('sb_agent_user')->whereIn('uid',$user_ids)->where('is_delete',0)->where('type',5)->get(['uid'])->getResult();
        return $forbid_list;
    }

    /**
     * 获取用户实力值
     * @author Nihuan
     * @param $user_ids
     * @return mixed
     */
    protected function get_user_score($user_ids)
    {
        $score_list = UserScore::findAll(['user_id' => $user_ids], ['fields' => ['user_id','score_value']])->getResult();
        $score_list = json_decode(json_encode($score_list),true);
        return $score_list;
    }

    /**
     * 获取产品用途列表
     * @author Nihuan
     * @param $pro_ids
     * @return mixed
     * @throws DbException
     */
    protected function get_product_uses($pro_ids)
    {
        $ids = implode(',',$pro_ids);
        $order_list = Db::query("SELECT pro_id, GROUP_CONCAT(distinct top_id) use_pid, GROUP_CONCAT(distinct tag_id) use_ids FROM sb_product_relation_use WHERE pro_id IN ({$ids})")->getResult();
        return $order_list;
    }


    /**
     * @author Nihuan
     * @param $user_ids
     * @return mixed
     * @throws DbException
     */
    protected function get_user_info($user_ids)
    {
        $ids = implode(',',$user_ids);
        $user_list = Db::query("SELECT user_id,name,city_id,city,province_id,province,safe_price,status AS user_status FROM sb_user WHERE user_id IN ({$ids})")->getResult();
        return $user_list;
    }


    /**
     * @author Nihuan
     * @param string $master
     * @param array $time_list
     * @throws \Swoft\Db\Exception\MysqlException
     * @return mixed
     */
    protected function add_index_time(string $master, array $time_list)
    {
        $data = [
            'out2csv_id' => $master,
            'parameter' => json_encode($time_list)
        ];
        return Query::table(TbOut2csv::class)->selectDb($this->searchDbName)->insert($data)->getResult();
    }

    /**
     * 写入索引进程记录
     * @author Nihuan
     * @param $o2c_id
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    protected function add_process_record($o2c_id)
    {
        $data = [
            'o2c_id' => $o2c_id,
            'uuid' => $this->guid(),
            'type' => 3,
            'start_time' => time(),
            'state' => 1
        ];
        return Query::table(TbProcess::class)->selectDb($this->searchDbName)->insert($data)->getResult();
    }

    /**
     * 获取当前索引状态
     * @author Nihuan
     * @param $o2c_id
     * @return mixed
     */
    protected function get_process_record($o2c_id)
    {
        return Query::table(TbProcess::class)
            ->selectDb($this->searchDbName)
            ->where('o2c_id',$o2c_id)
            ->andWhere('state',1)
            ->get()
            ->getResult();
    }

    /**
     * 修改索引进程状态
     * @author Nihuan
     * @param $o2c_id
     * @return mixed
     */
    protected function alter_process_record($o2c_id)
    {
        $mictime = microtime(1) * 1000;
        return Query::table(TbProcess::class)->selectDb($this->searchDbName)->where('o2c_id',$o2c_id)->update(['state' => 0, 'end_time' => $mictime])->getResult();
    }

    /**
     * uuid 生成
     * @author Nihuan
     * @return string
     */
    protected function guid() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid =
            substr($charid, 8, 4)
            .substr($charid,12, 4)
            .substr($charid,16, 4)
            .substr($charid,20,3);
        return $uuid;
    }
}