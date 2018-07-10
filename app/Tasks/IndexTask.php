<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 上午11:26
 * Desc: 搜索数据索引任务
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\ProductData;
use App\Models\Entity\TbProcess;
use App\Models\Entity\TbOut2csv;
use App\Models\Entity\UserScore;
use App\Models\Entity\UserStrength;
use App\Models\Data\UserData;
use Elasticsearch\ClientBuilder;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Value;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Query;
use Swoft\Db\QueryBuilder;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;
use App\Pool\Config\ElasticsearchPoolConfig;

/**
 * index task
 * @Task("index")
 */
class IndexTask
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
     * 采购数据
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * Index Summary task
     * @Scheduled(cron="30 * * * * *")
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function summary()
    {
        $now_time = time();
        echo date('Y-m-d H:i:s',$now_time) . PHP_EOL;
        App::profileStart("tag");
        App::info('Index Request Check:');
        $this->shopService(['now_time' => $now_time]);
        $this->productService(['now_time' => $now_time]);
        $this->buyService(['now_time' => $now_time]);
        $this->recommendService(['now_time' => $now_time]);
        $this->addressBookService(['now_time' => $now_time]);
        App::info('Index Task End!');
        App::profileEnd("tag");
        App::counting("cache", 1, 10);
        echo 'Index Task End!' . PHP_EOL . PHP_EOL;
    }

    /**
     * shopService task
     * @param array $request
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function shopService($request)
    {
        $shop_alter_time_pre = $shop_reg_time_pre = 0;
        $shop_alter_time = $shop_reg_time = $request['now_time'];
        echo 'User Index Task Start' . PHP_EOL;
        App::info(' User Index');
        App::info(' Version:' . $this->elasticPoolConfig->getShopMaster());
        App::info(' Check if the index exists');
        $client = ClientBuilder::create()->setHosts($this->elasticPoolConfig->getUri())->build();
        $params = [
            'index' => $this->elasticPoolConfig->getShopMaster(),
        ];
        $index_exists = $client->indices()->exists($params);
        if($index_exists == false){
            $user_params = [
                'index' => $this->elasticPoolConfig->getShopMaster(),
                'body' => [
                    'settings' => $this->elasticPoolConfig->getSetting(),
                    'mappings' => $this->elasticPoolConfig->getShopMap()
                ]
            ];
            $responseRes = $client->indices()->create($user_params);
            $index_exists = $responseRes['acknowledged'];
        }
        if($index_exists === true){
            $out2csv = Query::table(TbOut2csv::class)
                ->selectDb($this->searchDbName)
                ->where('out2csv_id',$this->elasticPoolConfig->getShopMaster())
                ->limit(1)
                ->orderBy('id',QueryBuilder::ORDER_BY_DESC)
                ->get()
                ->getResult();
            if(!empty($out2csv)){
                $check_process = $this->get_process_record($out2csv[0]['id']);
                if($check_process){
                    App::info('User Index Task Is Running');
                    return;
                }
                $last_time = $out2csv[0]['parameter'];
                $last_time_list = json_decode($last_time,true);
                $shop_alter_time_pre = $last_time_list['shop_alter_time'];
                $shop_reg_time_pre = $last_time_list['shop_reg_time'];
            }

            try {
                //记录索引时间
                $last['shop_alter_time.pre'] = $shop_alter_time_pre;
                $last['shop_reg_time.pre'] = $shop_reg_time_pre;
                $last['shop_alter_time'] = $shop_alter_time;
                $last['shop_reg_time'] = $shop_reg_time;
                $o2c_id = $this->add_index_time($this->elasticPoolConfig->getShopMaster(), $last);
                $this->add_process_record($o2c_id);

                $select_fields = "user_id,name,name as name_na,portrait,operation_mode,phone,
                    province_id,province,city_id,city,detail_address,status,is_push,phone_is_protected,
                    phone_type,certification_type,role,reg_time,last_time AS login_time,safe_price, 
                    CASE WHEN safe_price > 0 THEN 1 ELSE 0 END has_margin,from_type,alter_time,company_size,level";

                $where = [
                    'pre_alter_time' => $shop_alter_time_pre,
                    'pre_reg_time' => $shop_reg_time_pre,
                    'shop_alter_time' => $shop_alter_time,
                    'shop_reg_time' => $shop_reg_time
                ];
                $count = $this->userData->getIndexUserCount($where);
                if($count > 0){
                    $last_id = 0;
                    $pages = ceil($count/$this->limit);
                    for ($i = 0; $i< $pages; $i++){
                        $user_list = $this->userData->getIndexUserList($select_fields, $where, $this->limit, $last_id);
                        App::info('执行SQL: '.get_last_sql());
                        if(!empty($user_list)){
                            $count_pages = ceil(count($user_list)/$this->page_limit);
                            for ($c = 0;$c < $count_pages; $c ++){
                                $list = array_splice($user_list, 0, $this->page_limit);
                                if(!empty($list)){
                                    $user_ids = array_column($list,'user_id');
                                    //deposit
                                    $deposit = $this->get_user_deposit($user_ids);
                                    $deposit_uids = array_flip(array_column($deposit,'userId'));
                                    //forbid
                                    $forbid = $this->get_forbid_users($user_ids);
                                    $forbid_uids = array_flip(array_column($forbid,'uid'));
                                    //main_product
                                    $main_product = $this->get_user_main_product($user_ids);
                                    $main_product_uids = array_flip(array_column($main_product,'user_id'));
                                    //orders_amount
                                    $order_amount = $this->get_user_month_order($user_ids);
                                    $order_amount_uids = array_flip(array_column($order_amount,'seller_id'));
                                    //strength_score
                                    $strength_score = $this->get_user_score($user_ids);
                                    $strength_score_uids = array_flip(array_column($strength_score,'user_id'));
                                    $bulk = $index = [];
                                    foreach ($list as $key => $user){
                                        $index = $user;
                                        $index['user_id'] = (int)$user['user_id'];
                                        $index['forbid'] = 0;
                                        $index['deposit'] = 0;
                                        $index['deposit_time'] = 0;
                                        $index['orders_amount'] = 0;
                                        $index['strength_score'] = 0;
                                        $index['main_product'] = '';
                                        $index['main_product_normalized'] = '';

                                        if (isset($deposit_uids[$user['user_id']])){
                                            $user_deposit = $deposit[$deposit_uids[$user['user_id']]];
                                            $index['deposit'] = intval($user_deposit['level']/5);
                                            $index['deposit_time'] = $user_deposit['startTime'];
                                        }

                                        if (isset($forbid_uids[$user['user_id']])){
                                            $index['forbid'] = 1;
                                        }
                                        if($user['status'] != 1){
                                            $index['forbid'] = 1;
                                        }

                                        if (isset($main_product_uids[$user['user_id']])){
                                            $main = $main_product[$main_product_uids[$user['user_id']]];
                                            $index['main_product'] = $main['parents'] . ',' . $main['tags'] . ',' . $main['sub_tag'];
                                            $index['main_product_normalized'] = str_replace(',',' ',$index['main_product']);
                                        }

                                        if(isset($order_amount_uids[$user['user_id']])){
                                            $order = $order_amount[$order_amount_uids[$user['user_id']]];
                                            $index['orders_amount'] = sprintf('%.2f',$order['total_amount']);
                                        }

                                        if(isset($strength_score_uids[$user['user_id']])){
                                            $score = $strength_score[$strength_score_uids[$user['user_id']]];
                                            $index['strength_score'] = intval($score['score_value']);
                                        }
                                        $bulk['body'][] = [
                                            'index' => [
                                                '_index' => $this->elasticPoolConfig->getShopMaster(),
                                                '_type' => 'shop',
                                                '_id' => $user['user_id']
                                            ]
                                        ];
                                        $bulk['body'][] = $index;
                                        $last_id = $user['user_id'];
                                    }
                                    $res = $client->bulk($bulk);
                                    $bulk = $index = [];
                                    if($res){
                                        App::info('Shop Index Result : ' . json_encode($res));
                                    }
                                }
                                $user_ids = $list = $deposit = $main_product = [];
                                $deposit_uids = $forbid_uids = $main_product_uids = $order_amount_uids = $strength_score_uids = [];
                            }
                            $user_list = [];
                        }
                        App::info(' Last Id:' . $last_id);
                    }
                }
                $this->alter_process_record($o2c_id);
            } catch (DbException $e) {
                App::debug('    SQL Failed to run');
            }
        }
        App::info(' User Index Task End!');
    }

    /**
     * @author Nihuan
     * @param $request
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function productService($request)
    {
        $pro_alter_time_pre = $shop_alter_time_pre = 0;
        $pro_alter_time = $shop_alter_time = $request['now_time'];
        echo 'Product Index Task Start' . PHP_EOL;
        App::info('Product Index');
        App::info('Version:' . $this->elasticPoolConfig->getProductMaster());
        App::info('Check if the index exists');
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
                    App::info('Product Index Task Is Running');
                    return;
                }
                $last_time = $out2csv[0]['parameter'];
                $last_time_list = json_decode($last_time,true);
                $shop_alter_time_pre = $last_time_list['shop_alter_time'];
                $pro_alter_time_pre = $last_time_list['pro_alter_time'];
            }

            try {
                //记录索引时间
                $last['pro_alter_time.pre'] = $pro_alter_time_pre;
                $last['shop_alter_time.pre'] = $shop_alter_time_pre;
                $last['pro_alter_time'] = $pro_alter_time;
                $last['shop_alter_time'] = $shop_alter_time;
                $o2c_id = $this->add_index_time($this->elasticPoolConfig->getProductMaster(), $last);
                $this->add_process_record($o2c_id);

                $select_fields = "pro_id AS product_id,cover,case when cover is not null and LENGTH(cover) > 0 then 1 else 0 end has_img,user_id,pro_name,name,name as name_na,uses,ingredient,crafts,price,cut_price,card_price,pro_item,gram_w,season,color,flower,status,is_recommend,del_status,fabric_detail,phone_is_public,clicks,is_up,pro_num,type,add_time,alter_time,refresh_time,refresh_count,card_ship_type,status,label_key,from_type,is_audit,valid_time";

                $where = [
                    'pre_alter_time' => $pro_alter_time_pre,
                    'pre_shop_time' => $shop_alter_time_pre,
                    'pro_alter_time' => $pro_alter_time,
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
                                        $index['is_recommend'] = (int)$product['is_recommend'];
                                        $index['lv1'] = $index['lv1_normalized'] = $index['suffix_normalized'] = '';
                                        $index['season'] = empty($product['season']) ? [] : explode(',',$product['season']);

                                        if(isset($product_use_ids[$product['user_id']])){
                                            $uses = $product_uses[$product_use_ids[$product['user_id']]];
                                            $use_ids = [];
                                            if(!empty($uses['use_ids'])){
                                                $use_list = explode(',',$uses['use_ids']);
                                                foreach ($use_list as $use) {
                                                    $use_ids[] = (int)$use;
                                                }
                                            }
                                            $index['use_ids'] = $use_ids;
                                            $use_pids = [];
                                            if(!empty($uses['use_pid'])){
                                                $pid_list = explode(',',$uses['use_pid']);
                                                foreach ($pid_list as $pid) {
                                                    $use_pids[] = (int)$pid;
                                                }
                                            }
                                            $index['use_pid'] = $use_pids;
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
                                            if($info['user_status'] != 1){
                                                $index['forbid'] = 1;
                                            }
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
                                                '_index' => $this->elasticPoolConfig->getProductMaster(),
                                                '_type' => 'product',
                                                '_id' => $product['product_id']
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
                $this->alter_process_record($o2c_id);
            } catch (DbException $e) {
                App::debug('SQL Failed to run');
            }
        }
        App::info('Product Index Task End!');
    }

    /**
     * buyService task
     * @param $request
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function buyService($request)
    {
        $buy_alter_time_pre = $buy_refresh_time_pre = 0;
        $buy_alter_time = $buy_refresh_time = $request['now_time'];
        echo 'Buy Index Task Start' . PHP_EOL;
        App::info('Buy Index');
        App::info('Version:' . $this->elasticPoolConfig->getBuyMaster());
        App::info('Check if the index exists');
        $client = ClientBuilder::create()->setHosts($this->elasticPoolConfig->getUri())->build();
        $params = [
            'index' => $this->elasticPoolConfig->getBuyMaster(),
        ];
        $index_exists = $client->indices()->exists($params);
        if($index_exists == false){
            $product_params = [
                'index' => $this->elasticPoolConfig->getBuyMaster(),
                'body' => [
                    'settings' => $this->elasticPoolConfig->getSetting(),
                    'mappings' => $this->elasticPoolConfig->getBuyMap()
                ]
            ];
            $responseRes = $client->indices()->create($product_params);
            $index_exists = $responseRes['acknowledged'];
        }

        if($index_exists === true){
            $out2csv = Query::table(TbOut2csv::class)
                ->selectDb($this->searchDbName)
                ->where('out2csv_id',$this->elasticPoolConfig->getBuyMaster())
                ->limit(1)
                ->orderBy('id',QueryBuilder::ORDER_BY_DESC)
                ->get()
                ->getResult();
            if(!empty($out2csv)){
                $check_process = $this->get_process_record($out2csv[0]['id']);
                if($check_process){
                    App::info('Buy Index Task Is Running');
                    return;
                }
                $last_time = $out2csv[0]['parameter'];
                $last_time_list = json_decode($last_time,true);
                $buy_refresh_time_pre = $last_time_list['buy_refresh_time'];
                $buy_alter_time_pre = $last_time_list['buy_alter_time'];
            }

            try {
                //记录索引时间
                $last['buy_alter_time.pre'] = $buy_alter_time_pre;
                $last['buy_refresh_time.pre'] = $buy_refresh_time_pre;
                $last['buy_alter_time'] = $buy_alter_time;
                $last['buy_refresh_time'] = $buy_refresh_time;
                $o2c_id = $this->add_index_time($this->elasticPoolConfig->getBuyMaster(), $last);
                $this->add_process_record($o2c_id);

                $select_fields = "buy_id,user_id,title, CASE WHEN pic <> '' THEN 1 ELSE 0 END has_img,amount,reward,CASE WHEN remark = '' THEN front_label_desc ELSE remark END remark,remark as remark_na,from_type,is_customize,contacts,contact_num,audit_time,type,status,del_status,is_audit,push_key,push_status,phone_is_public,clicks,add_time,alter_time,refresh_time,refresh_count,is_find,unit,pic";

                $where = [
                    'pre_alter_time' => $buy_alter_time_pre,
                    'pre_refresh_time' => $buy_refresh_time_pre,
                    'buy_alter_time' => $buy_alter_time,
                    'buy_refresh_time' => $buy_refresh_time
                ];
                $count = $this->buyData->getIndexBuyCount($where);
                if($count > 0){
                    $last_id = 0;
                    $pages = ceil($count/$this->limit);
                    for ($i = 0; $i< $pages; $i++){
                        $buy_list = $this->buyData->getIndexBuyList($select_fields, $where, $this->limit, $last_id);
                        if(!empty($buy_list)){
                            $count_pages = ceil(count($buy_list)/$this->page_limit);
                            for ($c = 0;$c < $count_pages; $c ++){
                                $list = array_splice($buy_list, 0, $this->page_limit);
                                if(!empty($list)){
                                    $buy_ids = array_column($list,'buy_id');
                                    //buy_earnest
                                    $buy_earnest = $this->get_buy_earnest($buy_ids);
                                    $buy_earnest_ids = array_flip(array_column($buy_earnest,'buy_id'));

                                    //buy_tag
                                    $buy_tag = $this->get_buy_tags($buy_ids);
                                    $buy_tag_ids = array_flip(array_column($buy_tag,'buy_id'));

                                    $user_ids = array_unique(array_column($list,'user_id'));
                                    //forbid
                                    $forbid = $this->get_forbid_users($user_ids);
                                    $forbid_uids = array_flip(array_column($forbid,'uid'));

                                    //user_info
                                    $user_info = $this->get_user_info($user_ids);
                                    $user_info_uids = array_flip(array_column($user_info,'user_id'));
                                    $bulk = $index = [];
                                    foreach ($list as $key => $buy){
                                        $index = $buy;
                                        $index['earnest'] = '';
                                        $index['has_earnest'] = 0;
                                        $index['forbid'] = 0;
                                        $index['labels'] = '';
                                        $index['parent'] = '';
                                        $index['proName_ids'] = '';
                                        $index['crafts_ids'] = '';
                                        $index['uses_ids'] = '';
                                        $index['category_id'] = '';
                                        $index['type_id'] = '';
                                        if(isset($buy_earnest_ids[$buy['buy_id']])){
                                            $earnest = $buy_earnest[$buy_earnest_ids[$buy['buy_id']]];
                                            $index['earnest'] = $earnest['earnest_amount'];
                                            if($index['earnest'] > 0){
                                                $index['has_earnest'] = 1;
                                            }
                                        }

                                        if(isset($buy_tag_ids[$buy['buy_id']])){
                                            $tag = $buy_tag[$buy_tag_ids[$buy['buy_id']]];
                                            if(!empty($tag['labels'])){
                                                $index['labels'] = $tag['labels'];
                                            }
                                            if(!empty($tag['parent'])){
                                                $index['labels'] .= $tag['parent'];
                                            }
                                            $index['parent'] = empty($tag['parent']) ? '' : $tag['parent'];
                                            $index['proName_ids'] = empty($tag['proName_ids']) ? '' : $tag['proName_ids'];
                                            $index['crafts_ids'] = empty($tag['crafts_ids']) ? '' : $tag['crafts_ids'];
                                            $index['uses_ids'] = empty($tag['uses_ids']) ? '' : $tag['uses_ids'];
                                            $index['category_id'] = empty($tag['category_id']) ? '' : $tag['category_id'];
                                            $index['type_id'] = empty($tag['type_id']) ? '' : $tag['type_id'];
                                        }

                                        if(isset($user_info_uids[$buy['user_id']])){
                                            $info = $user_info[$user_info_uids[$buy['user_id']]];
                                            $index['province'] = $info['province'];
                                            $index['city_id'] = (int)$info['city_id'];
                                            $index['city'] = $info['city'];
                                            $index['role'] = (int)$info['role'];
                                            $index['province_id'] = (int)$info['province_id'];
                                            $index['forbid'] = $info['user_status'] == 0 ? 1 : 0;
                                        }

                                        if (isset($forbid_uids[$buy['user_id']])){
                                            $index['forbid'] = 1;
                                        }

                                        $bulk['body'][] = [
                                            'index' => [
                                                '_index' => $this->elasticPoolConfig->getBuyMaster(),
                                                '_type' => 'buy',
                                                '_id' => $buy['buy_id']
                                            ]
                                        ];
                                        $bulk['body'][] = $index;
                                        $last_id = $buy['buy_id'];
                                    }
                                    $res = $client->bulk($bulk);
                                    $bulk = $index = [];
                                    if($res){
                                        App::info('Buy Index Result : ' . json_encode($res));
                                    }
                                }
                                $user_ids = $buy_ids = $list = [];
                                $buy_earnest = $forbid_uids = [];
                            }
                            $buy_list = [];
                        }
                        App::info('Last Id:' . $last_id);
                    }
                }
                $this->alter_process_record($o2c_id);
            } catch (DbException $e) {
                App::debug('SQL Failed to run');
            }
        }
        App::info('Buy Index Task End!');
    }

    /**
     * recommendService task
     * @param $request
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function recommendService($request)
    {
        $push_time_pre = 0;
        $push_time = $request['now_time'];
        echo 'Recommend Index Task Start' . PHP_EOL;
        App::info('Recommend Index');
        App::info('Version:' . $this->elasticPoolConfig->getRecommendMaster());
        App::info('Check if the index exists');
        $client = ClientBuilder::create()->setHosts($this->elasticPoolConfig->getUri())->build();
        $params = [
            'index' => $this->elasticPoolConfig->getRecommendMaster(),
        ];
        $index_exists = $client->indices()->exists($params);
        if($index_exists == false){
            $product_params = [
                'index' => $this->elasticPoolConfig->getRecommendMaster(),
                'body' => [
                    'settings' => $this->elasticPoolConfig->getSetting(),
                    'mappings' => $this->elasticPoolConfig->getRecommendMap()
                ]
            ];
            $responseRes = $client->indices()->create($product_params);
            $index_exists = $responseRes['acknowledged'];
        }

        if($index_exists === true){
            $out2csv = Query::table(TbOut2csv::class)
                ->selectDb($this->searchDbName)
                ->where('out2csv_id',$this->elasticPoolConfig->getRecommendMaster())
                ->limit(1)
                ->orderBy('id',QueryBuilder::ORDER_BY_DESC)
                ->get()
                ->getResult();
            if(!empty($out2csv)){
                $check_process = $this->get_process_record($out2csv[0]['id']);
                if($check_process){
                    App::info('Recommend Index Task Is Running');
                    return;
                }
                $last_time = $out2csv[0]['parameter'];
                $last_time_list = json_decode($last_time,true);
                $push_time_pre = $last_time_list['push_time'];
            }

            try {
                //最近100天有登录的供应商
                if($push_time_pre == 0){
                    $push_time_pre = time() - 8640000;
                }
                //记录索引时间
                $last['push_time.pre'] = $push_time_pre;
                $last['push_time'] = $push_time;
                $o2c_id = $this->add_index_time($this->elasticPoolConfig->getRecommendMaster(), $last);
                $this->add_process_record($o2c_id);

                $select_fields = "user_id,last_time,name,cid,status,level,certification_type,last_time AS login_time";

                $where = [
                    'pre_push_time' => $push_time_pre
                ];
                $count = $this->userData->getPushUserCount($where);
                if($count > 0){
                    $last_id = 0;
                    $pages = ceil($count/$this->limit);
                    for ($i = 0; $i< $pages; $i++){
                        $push_list = $this->userData->getPushUserList($select_fields, $where, $this->limit, $last_id);
                        if(!empty($push_list)){
                            $count_pages = ceil(count($push_list)/$this->page_limit);
                            for ($c = 0;$c < $count_pages; $c ++){
                                $list = array_splice($push_list, 0, $this->page_limit);
                                if(!empty($list)){
                                    $user_ids = array_column($list,'user_id');

                                    //deposit
                                    $deposit = $this->get_user_deposit($user_ids);
                                    $deposit_uids = array_flip(array_column($deposit,'userId'));

                                    //recommend_setting
                                    $remind_list = $this->get_push_remind($user_ids);
                                    $remind_uids = array_flip(array_column($remind_list,'user_id'));

                                    //user_ranking
                                    $ranking_list = $this->get_user_ranking($user_ids);
                                    $ranking_uids = array_flip(array_column($ranking_list,'user_id'));

                                    //main_product
                                    $main_product = $this->get_user_main_product($user_ids);
                                    $main_product_uids = array_flip(array_column($main_product,'user_id'));
                                    $bulk = $index = [];
                                    foreach ($list as $key => $user){
                                        $index = $user;
                                        $index['user_id'] = (int)$user['user_id'];
                                        $index['main_product'] = '';
                                        $index['main_product_normalized'] = '';
                                        $index['name'] = $user['name'];
                                        $index['main_type_ids'] = [];
                                        $index['main_product_ids'] = '';
                                        $index['parents'] = '';
                                        $index['parent_ids'] = '';
                                        $index['main_type'] = '';
                                        $index['ranking'] = -1;

                                        if (isset($deposit_uids[$user['user_id']])){
                                            $user_deposit = $deposit[$deposit_uids[$user['user_id']]];
                                            $index['deposit'] = intval($user_deposit['level']/5);
                                        }

                                        if(isset($remind_uids[$user['user_id']])){
                                            $user_remind = $remind_list[$remind_uids[$user['user_id']]];
                                            $index['push_type'] = (int)$user_remind['type'];
                                            $index['push_period'] = $user_remind['hours'];
                                        }

                                        if(isset($ranking_uids[$user['user_id']])){
                                            $user_ranking = $ranking_list[$ranking_uids[$user['user_id']]];
                                            $index['ranking'] = (int)$user_ranking['ranking'];
                                        }

                                        if (isset($main_product_uids[$user['user_id']])){
                                            $main = $main_product[$main_product_uids[$user['user_id']]];
                                            $index['main_product'] = $main['parents'] . ',' . $main['tags'] . ',' . $main['sub_tag'];
                                            $main_type_ids = [];
                                            if(!empty($main['main_type_ids'])){
                                                $type_ids = explode(',',$main['main_type_ids']);
                                                foreach ($type_ids as $main_type_id) {
                                                    $main_type_ids[] = $main_type_id;
                                                }
                                            }

                                            $index['main_product_ids'] = trim($main['sub_tag_ids']);
                                            $index['parents'] = trim($main['tags']);
                                            $index['parent_ids'] = trim($main['parent_ids']);
                                            $index['main_type'] = trim($main['parents']);
                                            $index['main_type_ids'] = $main_type_ids;
                                            $index['main_product_normalized'] = str_replace(',',' ',$index['main_product']);
                                        }

                                        $bulk['body'][] = [
                                            'index' => [
                                                '_index' => $this->elasticPoolConfig->getRecommendMaster(),
                                                '_type' => 'recommend',
                                                '_id' => $user['user_id']
                                            ]
                                        ];
                                        $bulk['body'][] = $index;
                                        $last_id = $user['user_id'];
                                    }
                                    $res = $client->bulk($bulk);
                                    $bulk = $index = [];
                                    if($res){
                                        App::info('Recommend Index Result : ' . json_encode($res));
                                    }
                                }
                                $user_ids = $deposit = $remind_list = $list = [];
                                $forbid_uids = [];
                            }
                            $push_list = [];
                        }
                        App::info('Last Id:' . $last_id);
                    }
                }
                $this->alter_process_record($o2c_id);
            } catch (DbException $e) {
                App::debug('SQL Failed to run');
            }
        }
        App::info('Recommend Index Task End!');
    }

    /**
     * addressBookService task
     * @param $request
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function addressBookService($request)
    {
        $alter_time_pre = 0;
        $alter_time = $request['now_time'];
        echo 'AddressBook Index Task Start' .PHP_EOL;
        App::info('AddressBook Index');
        App::info('Version:' . $this->elasticPoolConfig->getAddressBookMaster());
        App::info('Check if the index exists');
        $client = ClientBuilder::create()->setHosts($this->elasticPoolConfig->getUri())->build();
        $params = [
            'index' => $this->elasticPoolConfig->getAddressBookMaster(),
        ];
        $index_exists = $client->indices()->exists($params);
        if($index_exists == false){
            $product_params = [
                'index' => $this->elasticPoolConfig->getAddressBookMaster(),
                'body' => [
                    'settings' => $this->elasticPoolConfig->getSetting(),
                    'mappings' => $this->elasticPoolConfig->getAddressMap()
                ]
            ];
            $responseRes = $client->indices()->create($product_params);
            $index_exists = $responseRes['acknowledged'];
        }
        if($index_exists === true){
            $out2csv = Query::table(TbOut2csv::class)
                ->selectDb($this->searchDbName)
                ->where('out2csv_id',$this->elasticPoolConfig->getAddressBookMaster())
                ->limit(1)
                ->orderBy('id',QueryBuilder::ORDER_BY_DESC)
                ->get()
                ->getResult();
            if(!empty($out2csv)){
                $check_process = $this->get_process_record($out2csv[0]['id']);
                if($check_process){
                    App::info('AddressBook Index Task Is Running');
                    return;
                }
                $last_time = $out2csv[0]['parameter'];
                $last_time_list = json_decode($last_time,true);
                $alter_time_pre = $last_time_list['alter_time'];
            }

            try {
                //记录索引时间
                $last['alter_time.pre'] = $alter_time_pre;
                $last['alter_time'] = $alter_time;
                $o2c_id = $this->add_index_time($this->elasticPoolConfig->getAddressBookMaster(), $last);
                $this->add_process_record($o2c_id);

                $select_fields = "user_id,phone,name,role,portrait,status,level,certification_type,safe_price,operation_mode";

                $where = [
                    'pre_alter_time' => $alter_time_pre
                ];
                $count = $this->userData->getAddressCount($where);
                if($count > 0){
                    $last_id = 0;
                    $pages = ceil($count/$this->limit);
                    for ($i = 0; $i< $pages; $i++){
                        $address_list = $this->userData->getAddressList($select_fields, $where, $this->limit, $last_id);
                        if(!empty($address_list)){
                            $count_pages = ceil(count($address_list)/$this->page_limit);
                            for ($c = 0;$c < $count_pages; $c ++){
                                $list = array_splice($address_list, 0, $this->page_limit);
                                if(!empty($list)){
                                    $user_ids = array_column($list,'user_id');

                                    //forbid
                                    $forbid = $this->get_forbid_users($user_ids);
                                    $forbid_uids = array_flip(array_column($forbid,'uid'));

                                    $bulk = $index = [];
                                    foreach ($list as $key => $user){
                                        $index = $user;
                                        $index['user_id'] = (int)$user['user_id'];
                                        $index['name_na'] = $user['name'];
                                        $index['phone_search_normalized'] = $user['phone'];
                                        $index['name_search_normalized'] = $user['name'];
                                        if($index['safe_price'] > 0){
                                            $index['has_margin'] = 1;
                                        }

                                        if (isset($forbid_uids[$user['user_id']])){
                                            $index['forbid'] = 1;
                                        }

                                        $bulk['body'][] = [
                                            'index' => [
                                                '_index' => $this->elasticPoolConfig->getAddressBookMaster(),
                                                '_type' => 'addressBook',
                                                '_id' => $user['user_id']
                                            ]
                                        ];
                                        $bulk['body'][] = $index;
                                        $last_id = $user['user_id'];
                                    }
                                    $res = $client->bulk($bulk);
                                    $bulk = $index = [];
                                    if($res){
                                        App::info('AddressBook Index Result : ' . json_encode($res));
                                    }
                                }
                                $user_ids = $forbid = $list = [];
                                $forbid_uids = [];
                            }
                            $address_list = [];
                        }
                        App::info('Last Id:' . $last_id);
                    }
                }
                $this->alter_process_record($o2c_id);
            } catch (DbException $e) {
                App::debug('SQL Failed to run');
            }
        }
        App::info('AddressBook Index Task End!');
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
        $result = Db::query("
        SELECT user_id, 
            GROUP_CONCAT(distinct tag_name) sub_tag, 
            group_concat(distinct `tag_id`) sub_tag_ids , 
            GROUP_CONCAT(distinct parent_name) tags, 
            group_concat(distinct `parent_id`) parent_ids, 
            GROUP_CONCAT(distinct top_name) parents, 
            group_concat(distinct `top_id`) main_type_ids 
        FROM sb_user_subscription_tag 
        WHERE user_id IN ({$ids}) GROUP BY user_id
        ")->getResult();
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
        $user_list = Db::query("SELECT user_id,name,city_id,city,province_id,province,safe_price,role,status AS user_status FROM sb_user WHERE user_id IN ({$ids})")->getResult();
        return $user_list;
    }

    /**
     * @author Nihuan
     * @param $user_ids
     * @return mixed
     */
    protected function get_push_remind($user_ids)
    {
       $remind_list = Query::table('sb_push_remind_setting')->whereIn('user_id',$user_ids)->get(['user_id','type','hours'])->getResult();
        $remind_list = json_decode(json_encode($remind_list),true);
        return $remind_list;
    }

    /**
     * @author Nihuan
     * @param $buy_ids
     * @return mixed
     * @throws DbException
     */
    protected function get_buy_tags($buy_ids)
    {
        $ids = implode(',',$buy_ids);
        $tag_list = Db::query("SELECT
          buy_id, GROUP_CONCAT(distinct `tag_name`) labels,
          GROUP_CONCAT(distinct `parent_name`) parent,
          GROUP_CONCAT(DISTINCT CASE WHEN cate_id = 1 THEN parent_id END) proName_ids,
          GROUP_CONCAT(DISTINCT CASE WHEN cate_id = 4 THEN tag_id END) crafts_ids,
          GROUP_CONCAT(DISTINCT CASE WHEN cate_id = 5 THEN tag_id END) uses_ids,
          GROUP_CONCAT(distinct cate_id) category_id,
          GROUP_CONCAT(distinct top_id) type_id
        FROM sb_buy_relation_tag WHERE buy_id IN ({$ids}) GROUP BY buy_id")->getResult();
        return $tag_list;
    }

    /**
     * @author Nihuan
     * @param $buy_ids
     * @return mixed
     * @throws DbException
     */
    protected function get_buy_earnest($buy_ids)
    {
        $ids = implode(',',$buy_ids);
        $earnest_list = Db::query("SELECT buy_id,earnest_id,earnest_amount FROM sb_buy_earnest WHERE earnest_status IN (3,7,21) AND earnest_id IN (SELECT MAX(earnest_id) earnest_id FROM sb_buy_earnest WHERE buy_id IN ({$ids}) GROUP BY buy_id)")->getResult();
        return $earnest_list;
    }

    /**
     * @author Nihuan
     * @param $user_ids
     * @return mixed
     */
    protected function get_user_ranking($user_ids)
    {
        $ranking_list = Query::table('sb_supplier_ranking')->whereIn('user_id',$user_ids)->get(['user_id','ranking','deposit','product_number'])->getResult();
        $ranking_list = json_decode(json_encode($ranking_list),true);
        return $ranking_list;
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