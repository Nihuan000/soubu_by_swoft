<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 上午11:26
 * Desc: 搜索数据索引任务
 */

namespace App\Tasks;

use App\Models\Entity\SbUser;
use App\Models\Entity\TbOut2csv;
use Elasticsearch\ClientBuilder;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Query;
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
    protected $limit = 30000;
    //批量写入ES数据量
    protected $page_limit = 5000;
    //定时器(分钟)
    protected $timer = 3;

    /**
     * The config of ElasticsearchPool
     *
     * @Inject()
     *
     * @var ElasticsearchPoolConfig
     */
    protected $elasticPoolConfig;

    /**
     * Index Summary task
     * @Scheduled(cron="3-5 * * * * *")
     */
    public function summary()
    {
        echo "Index Request Check  \n";
        App::info('Index Request Check:');
        $now_time = time();
        echo $now_time . "\n";
        $this->shopService(['now_time' => $now_time]);
        App::info('Index Task End!');
    }

    /**
     * shopService task
     * @param array $request
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
            'index' => $this->elasticPoolConfig->getShopMaster()
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
            $last_time = Query::table(TbOut2csv::class)->selectDb('SoubuSearch')->where('out2csv_id',$this->elasticPoolConfig->getShopMaster())->get()->getResult();
            if(!empty($last_time)){
                $last_time_list = json_decode($last_time,true);
                $shop_alter_time = $last_time_list['shop_alter_time'];
                $shop_reg_time = $last_time_list['shop_reg_time'];
                $shop_alter_time_pre = $last_time_list['shop_alter_time.pre'];
                $shop_reg_time_pre = $last_time_list['shop_reg_time.pre'];
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
                        $user_list = Db::query('select ? FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE "%搜布%" AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?)) AND user_id > ? ORDER BY user_id ASC LIMIT ?', [$select_fields, $shop_alter_time_pre, $shop_alter_time, $shop_reg_time_pre, $shop_reg_time, $last_id, $this->limit])->getResult();
                        App::info('执行SQL: '.get_last_sql());
                        if(!empty($user_list)){
                            foreach ($user_list as $item) {

                                $last_id = $item['user_id'];
                            }
                        }
                    }
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
}