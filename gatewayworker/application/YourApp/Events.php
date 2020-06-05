<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);
//use app\index\controller;
use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

require_once 'Connection.php';

function get_userinfo_key( $uid )
{
    return "khgame:userinfo:" . $uid;
}
function get_userbag_key( $uid )
{
    return "khgame:bag:" . $uid;
}

function get_khgirl_key( $uid )
{
    return "khgame:khgirl:" . $uid;
}

function get_khgirl_hashkey( $uid )
{
    return "khgame:khgirlhash:" . $uid;
}

function get_lastlogin_key( $uid )
{
    return "khgame:lastlogin:" . $uid;
}

function get_thislogin_key( $uid )
{
    return "khgame:thislogin:" . $uid;
}

function cal_min_time($khinfo, $curtime)
{
    $min = 0;
    for($i=0; $i<12; $i++)
    {
        if( $khinfo[$i]['khid'] !=  0 )
        {
            $tmp = $curtime - $khinfo[$i]['caltime'];
            if($min == 0 )  $min = $tmp;
            else 
            {
                $min = $min < $tmp ? $min : $tmp;
            }
        }
    }
    return $min ;
}

function get_generate( $config , $lvl)
{

    foreach($config as $key =>  $value)
    {
        if( $value['item_lvl'] ==  $lvl )
            return $value;
    }

    return null;
}

//根据概率获取38级物品
function get_38generate( $config , $rate38)
{
    $rndNum = rand(0,100);
    $total = 0;
    $khid = 0;
    foreach($rate38 as $key =>  $value)
    {
        if( intval($value) != 0 )
        {
            $total =$total + intval($value);
            if( $rndNum <= $total  )
            {
                $khid = intval( $key )  ;
                break;
            }
        }       
    }
    //echo $khid;
    if( $khid  != 0 )
    {
        //var_dump( $config[$khid] );
        return $config[$khid];
    }
    return null;
}

//获取分享金
function get_shareCoin()
{
    $arr = array( 164, 162,165,160,162,163,161,160,166 );
    $index = date("w");
    return $arr[ $index ];
}

//获取购买的等级
function get_buyLvl( $maxLvl, $maxNum = 0  )
{
    $retLvl = 1;
    if( $maxLvl <= 6 )
    {
        return $retLvl;
    }
    else if( $maxLvl <= 8 )
    {
        return ($maxLvl-5);
    }
    else if( $maxLvl <= 10 )
    {
        return ($maxLvl-6);
    }
    else if( $maxLvl <= 19 )
    {
        return ($maxLvl-7);
    }
    else if( $maxLvl <= 24 )
    {
        return ($maxLvl-8);
    }
    else if( $maxLvl <= 37 )
    {
        return ($maxLvl-9);
    }
    else if( $maxLvl == 38 )
    {
        if(  $maxNum <= 3 )
        {
            return 28;
        }
        else if(  $maxNum <= 5 )
        {
            return 29;
        }
        else 
         {
            return 30;
         }
    }

    return $retLvl ;
}

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static $db_mysql = null;
    public static $db_redis = null;
    public static $kh_config = null;
    public static $rate_config = null;

    public static function onWorkerStart($businessWorker)
    {


        echo "Gatewayworker Start\n";

        ini_set('default_socket_timeout', -1); //redis不超时

        self::$db_redis = new \Redis();
        self::$db_redis->connect('127.0.0.1', 6379);

        //self::$kh_config = 

        self::$db_mysql = new \Workerman\MySQL\Connection('47.103.84.38', '3306', 'test_treemay_com', 'CMinGAKP7riGSC6b', 'test_treemay_com');
       
        $res = self::$db_mysql->select('*')
        ->from('ims_game_iteminfo')
        ->query();
        foreach($res as $key =>  $value)
        {
            self::$kh_config[ $value['id'] ] = $value;
        }

        $rate = self::$db_mysql->select('*')
        ->from('ims_game_38rate')
        ->query();
        foreach($rate as $key =>  $value)
        {
            self::$rate_config[ $value['khid'] ] = $value['rate_new'];
        }


        
        //var_dump(self::$rate_config);
    }

    public static function getMax($userinfo = [])
    {

       
        $khinfo = $userinfo["khinfo"];
        $max = 0;
        $gezi_num = 0;  
        $item_index = -1;
        foreach ($khinfo as $key => $val)
        {
            if( $val["khid"] != 0 )
            {
                $cf = self::$kh_config[ $val["khid"] ];
                if( $cf  )
                {
                    $max = $max > $cf["item_lvl"] ? $max : $cf["item_lvl"];
                    $gezi_num = $gezi_num + 1 ;
                }
                
            }
           
        }

        return $max;
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {

        echo "User connect!";

        // 连接到来后，定时30秒关闭这个链接，需要30秒内发认证并删除定时器阻止关闭连接的执行
        $_SESSION['auth_timer_id'] = Timer::add(30, function ($client_id) {
           // Gateway::closeClient($client_id);
        }, array($client_id), false);
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {

        $msg = json_decode($message, true);

        if ($msg) {
            if (!isset($_SESSION['uid']) && $msg['cmd_id'] != 100) {
                return;
            }

            switch ($msg['cmd_id']) {
                case 100 :   //用户登录处理流程
                {
                    // 认证成功，删除 30关闭连接定 的时器
                    Timer::del($_SESSION['auth_timer_id']);
                    Events::onUserLogin($client_id, $msg['cmd_value']);
                    break;
                }
                case 101:   //用户购买口红
                {
                    Events::buylipstick($client_id,$_SESSION['uid']);
                    break;
                }
                case 102:  //合成口红
                {
                    Events::onComposite($client_id, $msg['cmd_value']);
                    break;
                }
                case 103:  //垃圾箱回收口红
                {
                    Events::onRecycle($client_id, $msg['cmd_value']);
                    break;
                }
                case 104: // 存口红
                {
                    Events::onStore($client_id, $msg['cmd_value']);
                    break;
                }
                case 105: //取口红
                {
                    Events::onFetchBag($client_id, $msg['cmd_value']);
                    break;
                }
                case 106: //合成口红女神
                {
                    Events::onCompositeGirl($client_id, $msg['cmd_value']);
                    break;
                }
            }
        } else {
            //客户端发送的不是json格式的数据
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        // 向所有人发送
        //GateWay::sendToAll("$client_id logout\r\n");
        if ( isset($_SESSION['uid']) ) 
        {
            $thisLogintime = time();
            $uid = $_SESSION['uid'];
            $lasttime = self::$db_redis->get( get_lastlogin_key($uid) );

            $userinfo = self::$db_redis->get( get_userinfo_key( $uid ) );
            $userinfo = json_decode($userinfo, true);
            $khinfo = $userinfo["khinfo"];

            $num = self::countlipstick($khinfo, $thisLogintime, $lasttime, 1);//断线一共产生多少仙气值
            $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
            $userinfo["khinfo"] = $khinfo;

            $userinfoJson = json_encode($userinfo);
            self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis

            self::$db_redis->set( get_lastlogin_key($_SESSION['uid'] ), time() );
        }
        
    }

    public static function onUserLogin($client_id, $data)
    {

       // $data = json_decode($data, true);
        $where["openid"] = $openid = $data["openid"];
        $where["id"] = $uid = $data["uid"];
        //验证用户名密码
        $res = self::$db_mysql->select('*')
            ->from('ims_junsion_winaward_mem')
            ->where("id= :id and openid= :openid " )
            ->bindValues($where)
            ->query();
        
        
        if (!$res[0]) {// //登录失败
            $ajaxData["cmd_id"] = 200;
            $ajaxData["cmd_value"]["code"] = 1;
            $ajaxData["cmd_value"]["data "] = [];
            $strRet = json_encode($ajaxData);
            GateWay::sendToClient($client_id, $strRet );
            Gateway::closeClient($client_id);
            return;
        }

        //登录成功
        $_SESSION['uid'] = $uid;

        //登录成功之后给玩家发送配置
        $retData["cmd_id"] = 99;
        $retData["cmd_value"] = self::$kh_config ;
        $strConfig = json_encode($retData);
        GateWay::sendToClient($client_id, $strConfig );

        $redis = self::$db_redis;  
        $thisLogintime = time();
        $redis->set( get_thislogin_key( $uid ) , $thisLogintime );
        
        $key = get_userinfo_key($uid) ;
        $userinfo = $redis->get($key);//获取用户信息
        if (!$userinfo) {
            self::addInfo($uid);//redis没有数据新增数据
        }
        $userinfo = $redis->get($key);//获取用户信息
        $userinfo = json_decode($userinfo, true);

        $khinfo = $userinfo["khinfo"];//口红信息
       // $khinfo = json_decode($khinfo, true);
       
        $lasttime = $redis->get( get_lastlogin_key($uid) );
        $baginfo = self::$db_redis->hGetAll(get_userbag_key($uid));

        //每日分红
        $shareMoney = get_shareCoin();
        //用户第一次登录没有离线时间
        if (!$lasttime) 
        {
            //把用户信息返回去

            $ajaxData["cmd_id"] = 200;
            $ajaxData["cmd_value"]["code"] = 0;
            $ajaxData["cmd_value"]["data"]["userinfo"] = $userinfo;
            $ajaxData["cmd_value"]["data"]["offline"] = 0;
            $ajaxData["cmd_value"]["data"]["bag"] =$baginfo ;
            $ajaxData["cmd_value"]["data"]["shareMoney"] =$shareMoney ;

            $strRet = json_encode($ajaxData);
            GateWay::sendToClient($client_id, $strRet );
           
        } 
        else 
        {
            //离线产生的收益
            $boffline = true;
            
            $time = $thisLogintime - $lasttime;//断线多长时间
            if( $time < (10*60) )
            {
                $boffline = false;
            }
            $ratio = $boffline ? 0.25 : 1 ;

            $num = self::countlipstick($khinfo, $thisLogintime, $lasttime, $ratio);//断线一共产生多少仙气值
            //存在用户数据中
            $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
            $userinfo["khinfo"] = $khinfo;

            $userinfoJson = json_encode($userinfo);
            $redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis

            //把用户信息返回去
            $ajaxData["cmd_id"] = 200;
            $ajaxData["cmd_value"]["code"] = 0;
            $ajaxData["cmd_value"]["data"]["userinfo"] = $userinfo;
            $ajaxData["cmd_value"]["data"]["offline"] =$boffline ? $num : 0 ;
            $ajaxData["cmd_value"]["data"]["bag"] =$baginfo ;
            $ajaxData["cmd_value"]["data"]["shareMoney"] =$shareMoney ;

            $bExist = false;
            $girlTime = self::$db_redis->get( get_khgirl_key( $uid ) ) ;
            if( $girlTime )
            {
                $num = time() - intval($girlTime);
                if( $num > 10*60 )
                {
                    self::$db_redis->del( get_khgirl_key( $uid ) ) ;
                }
                else 
                {
                    $bExist = true;
                    $ajaxData["cmd_value"]["khgirl"] = $girlTime;
                }
            }

            //echo json_encode($ajaxData);
            $strRet = json_encode($ajaxData);
            GateWay::sendToClient($client_id, $strRet );
        }


    }

    //redis没数据的时候 给的数据
    public static function addInfo($uid)
    {
        $redis = self::$db_redis;

        $khinfo = array();
        for($i=0; $i<12; $i++)
        {
            $item = array();
            $item['index'] = $i;
            $item['khid'] = 0;  // 
            $item['caltime'] = 0;
            $khinfo[$i] = $item;
        }
        
        $userinfo["khxianqi"] = 50000;//默认先给5w
        $userinfo["khsprite"] = 0;
        $userinfo["khinfo"] = $khinfo;
        $userinfo["khmaxlvl"] = 0;
        $userinfo["iskhgirl10"] = 0;
        $userinfo["iskhgirl20"] = 0;
        $userinfo = json_encode($userinfo);
        $key = get_userinfo_key($uid);
        $redis->set($key, $userinfo);//获取用户信息
    }

    //计算从离线时间到到等登录多长时间 然后增加口红值
    //计算口红产生的仙气值 cundao redis
    /**
     * @param array $arr 口红的数组
     * @param int $time 断线时间
     * @param int $ratio 断线计算为1/4
     * @return int
     */
    public static function countlipstick(& $khinfo = [], $thisLogintime=0, $lasttime = 0, $ratio = 0)
    {
        $num = 0;
        $time = cal_min_time($khinfo, $thisLogintime);
        for($i=0; $i<12; $i++)
        {
            $khid = $khinfo[$i]['khid'];
            if( $khid != 0 )
            {
                $num = $num + self::$kh_config[$khid]["item_generate"] * $ratio * $time;
                $khinfo[$i]['caltime'] = $khinfo[$i]['caltime'] + $time;
            }
        }
        //存到redis里面 就行
        return  $num  ;
    }

    //购买口红
     public static function buylipstick($client_id, $uid )
    {

        $userinfo = self::$db_redis->get( get_userinfo_key( $uid ) );
        $userinfo = json_decode($userinfo, true);
        $khinfo = $userinfo["khinfo"];
        $max = 0;
        $gezi_num = 0;  
        $item_index = -1;
        foreach ($khinfo as $key => $val)
        {
            if( $val["khid"] != 0 )
            {
                $cf = self::$kh_config[ $val["khid"] ];
                if( $cf  )
                {
                    $max = $max > $cf["item_lvl"] ? $max : $cf["item_lvl"];
                    $gezi_num = $gezi_num + 1 ;
                }
                
            }
            else 
            {
                if( $item_index == -1 )
                    $item_index =  $key ;
            }
        }

        //假如消耗$n个口红值
        if ($gezi_num >= 12 )   //修改bug
        {
            $data["cmd_id"] = 201;
            $data["cmd_value"]["code"] = 2;
            //$data["cmd_value"]["data"] = "购买失败，格子不够";
            //echo json_encode($data);
            $strRet = json_encode($data);
            GateWay::sendToClient($client_id, $strRet );
            return;
        }

        $thisLogintime = time();
        $lasttime = self::$db_redis->get( get_lastlogin_key($uid) );

        $num = self::countlipstick($khinfo, $thisLogintime, $lasttime, 1);//断线一共产生多少仙气值
        //存在用户数据中
        $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
        $userinfo["khinfo"] = $khinfo;
       
        $maxNum = 0;
        if( $max == 38 )
        {
            foreach ($khinfo as $key => $val)
            {
                if( $val["khid"] != 0 )
                {
                    $cf = self::$kh_config[ $val["khid"] ];
                    if( $cf  )
                    {
                        if( $cf["item_lvl"] == 38 )
                        {
                            $maxNum ++ ;
                        }   
                    }
                }
            }
        }
       // $lvl = $max > 8 ? ($max-8) : 1 ;
        $lvl = get_buyLvl( $max, $maxNum   );
        $tmp_config = get_generate( self::$kh_config, $lvl );
        if( !$tmp_config )
        {
            echo "get_generate error " . $lvl . "\n";
            return;
        }
        if ($userinfo["khxianqi"] < $tmp_config['item_consume']) 
        {
            $data["cmd_id"] = 201;
            $data["cmd_value"]["code"] = 1;
            //$data["cmd_value"]["data"] = " 购买失败，仙气值不足";
            
            $userinfoJson = json_encode($userinfo);
            self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis

            $strRet = json_encode($data);
            GateWay::sendToClient($client_id, $strRet );
            return;
        }

        
        //购买成功
        $item = array();
        $item['index'] = $item_index;
        $item['khid'] = $tmp_config['id'];  // 
        $item['caltime'] = $thisLogintime;
        $userinfo["khinfo"][$item_index] = $item ;
        $userinfo["khxianqi"] = $userinfo["khxianqi"] - $tmp_config['item_consume'];
        $userinfoJson = json_encode($userinfo);
        self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示
       
        $data["cmd_id"] = 201;
        $data["cmd_value"]["code"] = 0;
        $data["cmd_value"]["data"] = $userinfo;
        
        $strRet = json_encode($data);
        GateWay::sendToClient($client_id, $strRet );

    }

    
    //存口红
    public static function onStore($client_id, $cmd_value)
    {
        $fromindex = $cmd_value['fromindex'];

        if( ($fromindex<0 || $fromindex>11) )
        {
            echo "onStore " . $_SESSION['uid'] . "cmd_value error \n";
            return;
        }
        $uid = $_SESSION['uid'];
        $userinfo = self::$db_redis->get( get_userinfo_key( $uid ) );
        $userinfo = json_decode($userinfo, true);
        $khinfo = $userinfo["khinfo"];
        if( $khinfo[$fromindex]['khid'] == 0 )
        {          
            return;
        }

        $thisLogintime = time();
        $lasttime = self::$db_redis->get( get_lastlogin_key($uid) );

        $num = self::countlipstick($khinfo, $thisLogintime, $lasttime, 1);//断线一共产生多少仙气值
        //存在用户数据中
        $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;

        $khid = $khinfo[$fromindex]['khid'];
        self::$db_redis->hIncrBy(get_userbag_key($uid), $khid, 1);

        $khinfo[$fromindex]['khid'] = 0;
        $khinfo[$fromindex]['caltime'] = 0;
        //get_userbag_key

        $userinfo["khinfo"] = $khinfo ;
        $userinfo["khmaxlvl"] = Events::getMax($userinfo);
        $userinfoJson = json_encode($userinfo);
        self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示
        
        $baginfo = self::$db_redis->hGetAll(get_userbag_key($uid));
        //var_dump( $baginfo );

        $data["cmd_id"] = 204;
        $data["cmd_value"]["code"] = 0;
        $data["cmd_value"]["data"] = $userinfo;
        $data["cmd_value"]["bag"] = $baginfo;
        $strRet = json_encode($data);
        GateWay::sendToClient($client_id, $strRet );
        return;
    }

    //取口红
    public static function onFetchBag($client_id, $cmd_value)
    {
        $khid = $cmd_value['khid'];
        $uid = $_SESSION['uid'];
        $num = self::$db_redis->hGet(get_userbag_key($uid), $khid  );

        if( $num )
        {
            $userinfo = self::$db_redis->get( get_userinfo_key( $uid ) );
            $userinfo = json_decode($userinfo, true);
            $khinfo = $userinfo["khinfo"];
            
            $item_index = -1;
            foreach ($khinfo as $key => $val)
            {
                if( $val["khid"] == 0 )
                {
                    
                    $item_index =  $key ;
                    break;
                }
                
            }

            //假如消耗$n个口红值
            if ($item_index != -1 )   
            {
                $khinfo[$item_index]['khid'] = $khid;
                $khinfo[$item_index]['caltime'] = time();

                $num = intval($num) - 1;
                self::$db_redis->hSet(get_userbag_key($uid), $khid , $num  );

                $userinfo["khinfo"] = $khinfo ;
                $userinfo["khmaxlvl"] = Events::getMax($userinfo);
                $userinfoJson = json_encode($userinfo);
                self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示

                $baginfo = self::$db_redis->hGetAll(get_userbag_key($uid));
                //var_dump( $baginfo );

                $data["cmd_id"] = 205;
                $data["cmd_value"]["code"] = 0;
                $data["cmd_value"]["data"] = $userinfo;
                $data["cmd_value"]["bag"] = $baginfo;
                $strRet = json_encode($data);
                GateWay::sendToClient($client_id, $strRet );

            }
        }

    }

    //垃圾回收
    public static function onRecycle($client_id, $cmd_value)
    {
        $fromindex = $cmd_value['fromindex'];

        if( ($fromindex<0 || $fromindex>11) )
        {
            echo "onRecycle " . $_SESSION['uid'] . "cmd_value error \n";
            return;
        }
        $uid = $_SESSION['uid'];
        $userinfo = self::$db_redis->get( get_userinfo_key( $uid ) );
        $userinfo = json_decode($userinfo, true);
        $khinfo = $userinfo["khinfo"];
        if( $khinfo[$fromindex]['khid'] == 0 )
        {          
            return;
        }

        $thisLogintime = time();
        $lasttime = self::$db_redis->get( get_lastlogin_key($uid) );

        $num = self::countlipstick($khinfo, $thisLogintime, $lasttime, 1);//断线一共产生多少仙气值
        //存在用户数据中
        $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;

        $khid =  $khinfo[$fromindex]['khid'] ;
    
        $consume = self::$kh_config[$khid]['item_consume'];
        $userinfo["khxianqi"] = $userinfo["khxianqi"] +  $consume/1000 ;

        $khinfo[$fromindex]['khid'] = 0;
        $khinfo[$fromindex]['caltime'] = 0;

        $userinfo["khinfo"] = $khinfo ;
        $userinfo["khmaxlvl"] = Events::getMax($userinfo);
        $userinfoJson = json_encode($userinfo);
        self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示
        
        $data["cmd_id"] = 203;
        $data["cmd_value"]["code"] = 0;
        $data["cmd_value"]["data"] = $userinfo;
        
        $strRet = json_encode($data);
        GateWay::sendToClient($client_id, $strRet );
        return;

    }
    
    public static function onCompositeGirl($client_id, $cmd_value)
    {

    }

    public static function onComposite($client_id, $cmd_value)
    {
        $fromindex = $cmd_value['fromindex'];
        $toindex   = $cmd_value['toindex'];

        if( ($fromindex<0 || $fromindex>11) || ( $toindex<0 || $toindex>11 ) || ( $fromindex == $toindex ) )
        {
            echo "onComposite " . $_SESSION['uid'] . "cmd_value error \n";
            return;
        }
        $uid = $_SESSION['uid'];
        $userinfo = self::$db_redis->get( get_userinfo_key( $uid ) );
        $userinfo = json_decode($userinfo, true);
        $khinfo = $userinfo["khinfo"];
        if( $khinfo[$fromindex]['khid'] == 0 )
        {          
            return;
        }

        if( $khinfo[$toindex]['khid'] == 0 )
        {  
            $khinfo[$toindex]['khid'] = $khinfo[$fromindex]['khid'] ;
            $khinfo[$toindex]['caltime'] = $khinfo[$fromindex]['caltime'] ;
            
            $khinfo[$fromindex]['khid'] = 0;
            $khinfo[$fromindex]['caltime'] = 0;

            $userinfo["khinfo"] = $khinfo ;
            $userinfo["khmaxlvl"] = Events::getMax($userinfo);
            $userinfoJson = json_encode($userinfo);
            self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示
            
            $data["cmd_id"] = 202;
            $data["cmd_value"]["code"] = 1;
            $data["cmd_value"]["data"] = $userinfo;
            
            $strRet = json_encode($data);
            GateWay::sendToClient($client_id, $strRet );
            return;
        }

        if( $khinfo[$fromindex]['khid'] != $khinfo[$toindex]['khid'] )
        {
            $tmp_item = $khinfo[$toindex];

            $khinfo[$toindex]['khid'] = $khinfo[$fromindex]['khid'] ;
            $khinfo[$toindex]['caltime'] = $khinfo[$fromindex]['caltime'] ;
            
            $khinfo[$fromindex]['khid'] = $tmp_item['khid'];
            $khinfo[$fromindex]['caltime'] = $tmp_item['caltime'];

            $userinfo["khinfo"] = $khinfo ;
            $userinfo["khmaxlvl"] = Events::getMax($userinfo);
            $userinfoJson = json_encode($userinfo);
            self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示
            
            $data["cmd_id"] = 202;
            $data["cmd_value"]["code"] = 1;
            $data["cmd_value"]["data"] = $userinfo;
            
            $strRet = json_encode($data);
            GateWay::sendToClient($client_id, $strRet );
            return;
        }
        else 
        {
            $khid =  $khinfo[$fromindex]['khid'] ;
            if( self::$kh_config[$khid]['item_lvl'] == 38 )
            {
                //todo  38级合成
                return;
            }
            $lvl = self::$kh_config[$khid]['item_lvl'];
            
            $item_config = get_generate( self::$kh_config , $lvl+1 );

            if( intval($lvl) == 37 )  //37级合成38级单独规则
            {
                $item_config = get_38generate( self::$kh_config , self::$rate_config );
            }
            
            //随机生成口红女神
            $bExist = false;
            $girlTime = self::$db_redis->get( get_khgirl_key( $uid ) ) ;
            if( $girlTime )
            {
                $num = time() - intval($girlTime);
                if( $num > 10*60 )
                {
                    self::$db_redis->del( get_khgirl_key( $uid ) ) ;
                }
                else 
                {
                    $bExist = true;
                }
            }
            $bGenerateGirl = false ;
            $generateTime = time();
            if( !$bExist )
            {
                $rndNum = rand(0,100);
                if( $userinfo["khmaxlvl"] <= 10  )
                {
                   // if( intval( $userinfo["iskhgirl10"] ) <=3 && $rndNum<=30  )
                    if( intval( $userinfo["iskhgirl10"] ) <=3   )
                    {
                        $userinfo["iskhgirl10"] = $userinfo["iskhgirl10"] + 1;
                        $bGenerateGirl = true ;
                    }
                }
                else if(  $userinfo["khmaxlvl"] <= 20 )
                {
                    //if( intval( $userinfo["iskhgirl20"] ) <=4 && $rndNum<=30  )
                    if( intval( $userinfo["iskhgirl20"] ) <=4   )
                    {
                        $userinfo["iskhgirl20"] = $userinfo["iskhgirl20"] + 1;
                        $bGenerateGirl = true ;
                    }
                }

            }
            if( $bGenerateGirl )
            {
                self::$db_redis->set( get_khgirl_key( $uid ), $generateTime  ) ;
                $data["cmd_value"]["khgirl"] = $generateTime;

                $total_times_girl = $userinfo["iskhgirl10"] + $userinfo["iskhgirl20"];
                $field_girl = "field_girl" . $total_times_girl ;
                $field_money = "field_money" . $total_times_girl ;
                self::$db_redis->hset( get_khgirl_hashkey($uid) , $field_girl, $generateTime );
                self::$db_redis->hset( get_khgirl_hashkey($uid) , $field_money, get_shareCoin() );
                //get_khgirl_hashkey
            }
            else 
            {
                if( $bExist )
                {
                    $data["cmd_value"]["khgirl"] = $girlTime;
                }
            }

            $thisLogintime = time();
            $lasttime = self::$db_redis->get( get_lastlogin_key($uid) );

            $num = self::countlipstick($khinfo, $thisLogintime, $lasttime, 1);//断线一共产生多少仙气值
            //存在用户数据中
            $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
            //$userinfo["khinfo"] = $khinfo;

            $item = array();
            $item['index'] = $fromindex;
            $item['khid'] = 0;  // 
            $item['caltime'] = 0;
            $khinfo[$fromindex] = $item;

            $khinfo[$toindex]['khid'] = $item_config['id'];
            $khinfo[$toindex]['caltime'] = $thisLogintime;

            $userinfo["khinfo"] = $khinfo;
            $userinfo["khmaxlvl"] = Events::getMax($userinfo);
            $userinfoJson = json_encode($userinfo);
            self::$db_redis->set( get_userinfo_key($uid) , $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示
            
            $data["cmd_id"] = 202;
            $data["cmd_value"]["code"] = 0;
            $data["cmd_value"]["data"] = $userinfo;
            
            $strRet = json_encode($data);
            GateWay::sendToClient($client_id, $strRet );
            return;
        }

    }

    //查找用户信息

    public function userinfo($uid = "1111")
    {
        $redis = $this->redis();
        $userinfo = $redis->get($uid);

        return $userinfo;

    }

}
