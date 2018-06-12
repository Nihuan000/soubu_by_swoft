<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 上午10:36
 */

namespace App\Controllers;

use App\Models\Entity\TbOut2csv;
use App\Pool\Config\ElasticsearchPoolConfig;
use Swoft\Db\Db;
use Swoft\Db\Query;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use App\Models\Entity\SbUser;
use Swoft\App;

/**
 * action demo
 *
 * @Controller(prefix="/test")
 */
class TestController
{

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
        $userInfo = SbUser::findAll(['name' => '供应商'])->getResult();

        return $userInfo;
    }



    public function shopService()
    {
        $shop_alter_time_pre = $shop_reg_time_pre = 0;
        $shop_alter_time = $shop_reg_time = time();
        echo 'User Index Task Start' . "\n";
        App::info('User Index Task Start');
        App::info('Version:' . $this->elasticPoolConfig->getShopMaster());
        $last_time = Query::table(TbOut2csv::class)->selectDb('SoubuSearch')->where('out2csv_id',$this->elasticPoolConfig->getShopMaster())->orderBy('id','DESC')->get()->getResult();
        echo get_last_sql() . "\n";
        if(!empty($last_time)){
            $last_time_list = json_decode($last_time,true);
            $shop_alter_time = $last_time_list['shop_alter_time'];
            $shop_reg_time = $last_time_list['shop_reg_time'];
            $shop_alter_time_pre = $last_time_list['shop_alter_time.pre'];
            $shop_reg_time_pre = $last_time_list['shop_reg_time.pre'];
        }

        $select_fields = "user_id,name,name as name_na,portrait,operation_mode,phone,province_id,province,city_id,city,detail_address,status,is_push,phone_is_protected,phone_type,certification_type,role,reg_time,last_time AS login_time,safe_price, CASE WHEN safe_price > 0 THEN 1 ELSE 0 END has_margin,from_type,alter_time,company_size,level";
        $count = Db::query('select count(*) AS user_count FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE "%搜布%" AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?))', [$shop_alter_time_pre, $shop_alter_time, $shop_reg_time_pre, $shop_reg_time])->getResult();
//    $user_list = Db::query('select user_id,name,name as name_na,portrait,operation_mode,phone,province_id,province,city_id,city,detail_address,status,is_push,phone_is_protected,phone_type,certification_type,role,reg_time,last_time AS login_time,safe_price, CASE WHEN safe_price > 0 THEN 1 ELSE 0 END has_margin,from_type,alter_time,company_size,level FROM sb_user WHERE role IN (2,3,4) AND name NOT LIKE "%搜布%" AND ((alter_time > ? and alter_time <= ?) OR (reg_time > ? AND reg_time <= ?))',
//        [$shop_alter_time_pre, $shop_alter_time, $shop_reg_time_pre, $shop_reg_time])->getResult();
            echo get_last_sql() . PHP_EOL;
        App::info('User Index Task End!');
    }
}