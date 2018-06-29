<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 上午10:36
 */

namespace App\Controllers;

use App\Models\Entity\TbOut2csv;
use App\Models\Entity\UserScore;
use App\Models\Entity\UserStrength;
use App\Pool\Config\ElasticsearchPoolConfig;
use Elasticsearch\ClientBuilder;
use Swoft\Bean\Annotation\Value;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Query;
use Swoft\Bean\Annotation\Inject;
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
    protected $limit = 30000;
    //批量写入ES数据量
    protected $page_limit = 5000;
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
     * @RequestMapping()
     * @author Nihuan
     */
    public function index()
    {
        $userInfo = User::findAll(['name' => '供应商'])->getResult();

        return $userInfo;
    }



    public function shopService()
    {
        $shop_alter_time_pre = $shop_reg_time_pre = 0;
        $shop_alter_time = $shop_reg_time = time();
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
            $last_time = Query::table(TbOut2csv::class)->selectDb($this->searchDbName)->where('out2csv_id',$this->elasticPoolConfig->getShopMaster())->get()->getResult();
            if(!empty($last_time)){
                $last_time_list = json_decode($last_time,true);
                $shop_alter_time_pre = $last_time_list['shop_alter_time'];
                $shop_reg_time_pre = $last_time_list['shop_reg_time'];
            }

            try {
                $select_fields = "user_id,name,name as name_na,portrait,operation_mode,phone,
            province_id,province,city_id,city,detail_address,status,is_push,phone_is_protected,
            phone_type,certification_type,role,reg_time,last_time AS login_time,safe_price, 
            CASE WHEN safe_price > 0 THEN 1 ELSE 0 END has_margin,from_type,alter_time,company_size,level";

                $count = Db::query('select count(*) AS user_count FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE "%搜布%" AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?))', [$shop_alter_time_pre, $shop_alter_time, $shop_reg_time_pre, $shop_reg_time])->getResult();
                if($count[0]['user_count'] > 0){
                    $last_id = 0;
                    $pages = ceil($count[0]['user_count']/$this->limit);
                    for ($i = 0; $i< $pages; $i++){
                        $user_list = Db::query("select {$select_fields} FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE '%搜布%' AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?)) AND user_id > ? ORDER BY user_id ASC LIMIT {$this->limit}", [$shop_alter_time_pre, $shop_alter_time, $shop_reg_time_pre, $shop_reg_time, $last_id])->getResult();
                        App::info('执行SQL: '.get_last_sql());
                        if(!empty($user_list)){
                            $count_pages = ceil(count($user_list)/$this->page_limit);
                            $bulk = ['index' => $this->elasticPoolConfig->getShopMaster(), 'type' => 'shop'];
                            for ($c = 0;$c < $count_pages; $c ++){
                                $list = array_slice($user_list, $c*$this->page_limit, $this->page_limit);
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
                                    foreach ($list as $key => $user){
                                        $list[$key]['forbid'] = 0;
                                        $list[$key]['deposit'] = 0;
                                        $list[$key]['deposit_time'] = 0;
                                        $list[$key]['orders_amount'] = 0;
                                        $list[$key]['strength_score'] = 0;
                                        $list[$key]['main_product'] = '';
                                        $list[$key]['main_product_normalized'] = '';

                                        if (isset($deposit_uids[$user['user_id']])){
                                            $user_deposit = $deposit[$deposit_uids[$user['user_id']]];
                                            $list[$key]['deposit'] = intval($user_deposit['level']/5);
                                            $list[$key]['deposit_time'] = $user_deposit['startTime'];
                                        }

                                        if (isset($forbid_uids[$user['user_id']])){
                                            $list[$key]['forbid'] = 1;
                                        }

                                        if (isset($main_product_uids[$user['user_id']])){
                                            $main = $main_product[$main_product_uids[$user['user_id']]];
                                            $list[$key]['main_product'] = $main['parents'] . ',' . $main['tags'] . ',' . $main['sub_tag'];
                                            $list[$key]['main_product_normalized'] = str_replace(',',' ',$list[$key]['main_product']);
                                        }

                                        if(isset($order_amount_uids[$user['user_id']])){
                                            $order = $order_amount[$order_amount_uids[$user['user_id']]];
                                            $list[$key]['orders_amount'] = sprintf('%.2f',$order['total_amount']);
                                        }

                                        if(isset($strength_score_uids[$user['user_id']])){
                                            $score = $strength_score[$strength_score_uids[$user['user_id']]];
                                            $list[$key]['strength_score'] = intval($score['score_value']);
                                        }
                                        $last_id = $user['user_id'];
                                    }
                                    $bulk['body'] = $list;
                                    $res = $client->bulk($bulk);
                                    if($res){
                                        App::info('Shop Index Result : ' . $res);
                                    }
                                }
                                $user_ids = $list = $deposit = $main_product = [];
                                $deposit_uids = $forbid_uids = $main_product_uids = $order_amount_uids = $strength_score_uids = [];

                            }
                            $user_list = [];
                        }
                    }
                    $last['shop_alter_time.pre'] = $shop_alter_time_pre;
                    $last['shop_reg_time.pre'] = $shop_reg_time_pre;
                    $last['shop_alter_time'] = $shop_alter_time;
                    $last['shop_reg_time'] = $shop_reg_time;
                    $this->update_index_time($this->elasticPoolConfig->getShopMaster(), $last);
                }
            } catch (DbException $e) {
                App::debug('SQL Failed to run');
            } catch (DbException $e) {
                App::debug($e->getMessage());
            }
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
     * @author Nihuan
     * @param string $master
     * @param array $time_list
     */
    protected function update_index_time(string $master, array $time_list)
    {
        $data = [
            'parameter' => json_encode($time_list)
        ];
        Query::table(TbOut2csv::class)->selectDb($this->searchDbName)->where('out2csv_id',$master)->update($data)->getResult();
    }
}