<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 上午11:26
 * Desc: 搜索数据索引任务
 */

namespace App\Tasks;

use App\Models\Entity\TbProcess;
use App\Models\Entity\TbOut2csv;
use App\Models\Entity\UserScore;
use App\Models\Entity\UserStrength;
use App\Models\Data\UserData;
use Elasticsearch\ClientBuilder;
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
     * 用户模型
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * Index Summary task
     * @Scheduled(cron="10 * * * * *")
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function summary()
    {
        App::profileStart("tag");
        App::info('Index Request Check:');
        $now_time = time();
        $this->shopService(['now_time' => $now_time]);
        App::info('Index Task End!');
        App::profileEnd("tag");
        App::counting("cache", 1, 10);
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
        echo 'User Index Task Start' . "\n";
        App::info('User Index Task Start');
        App::info('Version:' . $this->elasticPoolConfig->getShopMaster());
        App::info('Check if the index exists');
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
                        var_dump(0);
                        $user_list = $this->userData->getIndexUserList($select_fields, $where, $this->limit, $last_id);
                        App::info('执行SQL: '.get_last_sql());
                        if(!empty($user_list)){
                            $count_pages = ceil(count($user_list)/$this->page_limit);
                            for ($c = 0;$c < $count_pages; $c ++){
                                var_dump(1);
                                $list = array_splice($user_list, 0, $this->page_limit);
                                var_dump(2);
                                if(!empty($list)){
                                    $user_ids = array_column($list,'user_id');
                                    var_dump(3);
                                    //deposit
                                    $deposit = $this->get_user_deposit($user_ids);
                                    $deposit_uids = array_flip(array_column($deposit,'userId'));
                                    var_dump(4);
                                    //forbid
                                    $forbid = $this->get_forbid_users($user_ids);
                                    $forbid_uids = array_flip(array_column($forbid,'uid'));
                                    var_dump(5);
                                    //main_product
                                    $main_product = $this->get_user_main_product($user_ids);
                                    $main_product_uids = array_flip(array_column($main_product,'user_id'));
                                    var_dump(6);
                                    //orders_amount
                                    $order_amount = $this->get_user_month_order($user_ids);
                                    $order_amount_uids = array_flip(array_column($order_amount,'seller_id'));
                                    var_dump(7);
                                    //strength_score
                                    $strength_score = $this->get_user_score($user_ids);
                                    $strength_score_uids = array_flip(array_column($strength_score,'user_id'));
                                    var_dump(8);
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
                                    var_dump(9);
                                    $res = $client->bulk($bulk);
                                    $bulk = $index = [];
                                    var_dump(10);
                                    if($res){
                                        App::info('Shop Index Result : ' . json_encode($res));
                                    }
                                }
                                $user_ids = $list = $deposit = $main_product = [];
                                $deposit_uids = $forbid_uids = $main_product_uids = $order_amount_uids = $strength_score_uids = [];
                            }
                            $user_list = [];
                        }
                        App::info('Last Id:' . $last_id);
                    }
                }
                $this->alter_process_record($o2c_id);
            } catch (DbException $e) {
                App::debug('SQL Failed to run');
            } catch (DbException $e) {
                App::debug($e->getMessage());
            }
        }
        App::info('User Index Task End!');
    }

    /**
     * productService task
     */
    public function productService()
    {

    }

    /**
     * buyService task
     */
    public function buyService()
    {

    }

    /**
     * recommendService task
     */
    public function recommendService()
    {

    }

    /**
     * addressBookService task
     */
    public function addressBookService()
    {

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