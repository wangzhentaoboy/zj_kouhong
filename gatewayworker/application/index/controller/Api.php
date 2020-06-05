<?php
/**
 * Created by PhpStorm.
 * User: 81965
 * Date: 2020/4/4
 * Time: 20:53
 */

namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Gateway;
use think\Request;
use payment\alipay\Alipay;
class Api extends Controller
{
    public function _initialize()
    {

        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
// 响应类型
        header('Access-Control-Allow-Methods:POST');
// 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
    }

//奖励

    public function reward()
    {

        $appSecurityKey = "f88ad12f0b012dbe9cf95771f9347d9d";
        $use_id = $video["user_id"] = $data["user_id"] = $_GET["user_id"];
        $trans_id = $video["trans_id"] = $data["trans_id"] = $_GET["trans_id"];
        $reward_amount = $video["reward_amount"] = $data["reward_amount"] = $_GET["reward_amount"];

        $reward_name = $video["reward_name"] = $data["reward_name"] = $_GET["reward_name"];
        $extra = $video["extra"] = $data["extra"] = $_GET["extra"];
        $extra = $video["time"] = time();


        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$use_id.\r\n" . "\r\n\r\n", FILE_APPEND);
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$trans_id.\r\n" . "\r\n\r\n", FILE_APPEND);
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$reward_amount.\r\n" . "\r\n\r\n", FILE_APPEND);

        //先报redis的数据更新到数据库中

        if (!$trans_id) {
            $ajaxdata["code"] = 201;
            $ajaxdata["msg"] = "trans_id不能为空";
            echo json_encode($ajaxdata);
            exit;
        }
        if (!$reward_amount) {
            $ajaxdata["code"] = 201;
            $ajaxdata["msg"] = "reward_amount不能为空";
            echo json_encode($ajaxdata);
            exit;
        }
//        $sign = input("sign");
//        $str = $appSecurityKey . ":" . $trans_id;
//        $selfSign = $this->SHA256Hex($str);
//        if ($selfSign == $sign) {
//            $data["code"] = 201;
//            $data["msg"] = "加密有误";
//            echo json_encode($data);
//            exit;
//        }
        //加记录

        $reward_amount_kouHong = $this->countKouhong($use_id);
//给用户加口红币
        $this->addgamecoin($use_id);
        $reward_amount_kouHong = $reward_amount_kouHong * 3600 * $reward_amount;
        $is_exit = DB::name("game_video_list")
            ->where("trans_id", "=", $trans_id)
            ->find();
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$reward_amount_kouHong.\r\n" . "\r\n\r\n", FILE_APPEND);
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$is_exit.\r\n" . "\r\n\r\n", FILE_APPEND);

        if ($is_exit) {
            $ajaxVideo["isValid"] = false;
            echo json_encode($ajaxVideo);
            exit;
        }

        $res = $this->addUseritrm($use_id);
        $LeftTimes = $this->getLeftTimes($use_id);
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$res.\r\n" . "\r\n\r\n", FILE_APPEND);
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$LeftTimes.\r\n" . "\r\n\r\n", FILE_APPEND);

        //今天超过17次不加
        if ($this->getLeftTimes($use_id) == 0) {
            $reward_amount_kouHong = 0;
        }
        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$reward_amount_kouHong.\r\n" . "\r\n\r\n", FILE_APPEND);


        //超过20次得到口精灵
        $addVideoList = DB::name("game_video_list")
            ->insert($video);

        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$addVideoList.\r\n" . "\r\n\r\n", FILE_APPEND);

        Db::startTrans();
        //口红气值
        $addkhxianqi = DB::name("game_useritem")
            ->where("uid", "=", $use_id)
            ->setInc("khxianqi", $reward_amount_kouHong);
//addVideotimes

        $addviewvideotime = DB::name("junsion_winaward_mem")
            ->where("id", "=", $use_id)
            ->setInc("viewvideotime");

        file_put_contents('1.txt', "【" . date('Y-m-d H:i:s') . "】.$addkhxianqi.\r\n" . "\r\n\r\n", FILE_APPEND);

        if ($addVideoList && $addkhxianqi && $addviewvideotime) {

            //给redis加数据
            $this->updateRedisUserinfo($use_id, $reward_amount_kouHong);
            Db::commit();
            $ajaxVideo["isValid"] = true;
            echo json_encode($ajaxVideo);
            exit;
        } else {
            Db::rollback();
            $ajaxVideo["isValid"] = false;
            echo json_encode($ajaxVideo);
            exit;
        }

    }

//看视频加口红币

    public function addgamecoin($user_id)
    {

        //查找关系
        $agentid = DB::name("junsion_winaward_mem")
            ->where("id", "=", $user_id)
            ->value("agentid");

        if ($agentid) {
            //加0.02
            DB::name("junsion_winaward_mem")
                ->where("id", "=", $agentid)
                ->setInc("game_coin", 0.02);
            $if_exist = DB::name("game_relation")
                ->where("uid", "=", $agentid)
                ->where("date", "=", date("Y-m-d"))
                ->find();

            if ($if_exist) {
                DB::name("game_relation")
                    ->where("uid", "=", $agentid)
                    ->where("date", "=", date("Y-m-d"))
                    ->setInc("zhijie", 0.02);
                DB::name("game_relation")
                    ->where("uid", "=", $agentid)
                    ->where("date", "=", date("Y-m-d"))
                    ->setInc("money", 0.02);
            } else {

                $data["date"] = date("Y-m-d");
                $data["money"] = 0;
                $data["zhijie"] = 0.02;
                $data["jianjie"] = 0;
                $data["money"] = 0.02;
                $data["uid"] = $agentid;
                DB::name("game_relation")
                    ->where("uid", "=", $agentid)
                    ->where("date", "=", date("Y-m-d"))
                    ->insert($data);
            }

            $agentfid = DB::name("junsion_winaward_mem")
                ->where("id", "=", $agentid)
                ->value("agentid");


            if ($agentfid) {

                DB::name("junsion_winaward_mem")
                    ->where("id", "=", $agentfid)
                    ->setInc("game_coin", 0.01);

                $if_exist_fid = DB::name("game_relation")
                    ->where("uid", "=", $agentfid)
                    ->where("date", "=", date("Y-m-d"))
                    ->find();

                if ($if_exist) {
                    DB::name("game_relation")
                        ->where("uid", "=", $agentfid)
                        ->where("date", "=", date("Y-m-d"))
                        ->setInc("jianjie", 0.01);
                    DB::name("game_relation")
                        ->where("uid", "=", $agentfid)
                        ->where("date", "=", date("Y-m-d"))
                        ->setInc("money", 0.01);
                } else {

                    $data["date"] = date("Y-m-d");
                    $data["money"] = 0;
                    $data["zhijie"] = 0;
                    $data["jianjie"] = 0.01;
                    $data["money"] = 0.01;
                    $data["uid"] = $agentfid;
                    DB::name("game_relation")
                        ->where("uid", "=", $agentfid)
                        ->where("date", "=", date("Y-m-d"))
                        ->insert($data);
                }
            }

        }

    }

    //接口
    public function all_data()
    {


        if (!Input("user_id")) {
            $data["code"] = 201;
            $data["msg"] = "参数不能为空";
            echo json_encode($data);
            exit;
        }
        $user_id = Input("user_id");
        $myall = DB::name("game_relation")
            ->where("uid", "=", $user_id)
            ->sum("money");
        $mytoday = DB::name("game_relation")
            ->where("uid", "=", $user_id)
            ->where("date", "=", date("Y-m-d"))
            ->find();
        $my["num"] = DB::name("junsion_winaward_mem")
            ->where("agentid", "=", $user_id)
            ->count();

        $myInvite = DB::name("junsion_winaward_mem")
            ->where("agentid", "=", $user_id)
            ->column("id");
        if($myInvite){
            $myInviteNum = DB::name("junsion_winaward_mem")
                ->whereIn("agentid", $myInvite)
                ->count();
            $my["num"]= $my["num"]+$myInviteNum;
        }


        if ($myall) {
            $my["all"] = round($myall, 2);
        } else {
            $my["all"] = 0;
        }
        if ($mytoday) {
            $my["today_all"] = round($mytoday["money"], 2);
            $my["today_zhijie"] = round($mytoday["zhijie"], 2);
            $my["today_jianjie"] = round($mytoday["jianjie"], 2);

        } else {
            $my["today_all"] = 0;
            $my["today_zhijie"] = 0;
            $my["today_jianjie"] = 0;
        }


        //好友
        $agentid = DB::name("junsion_winaward_mem")
            ->where("id", "=", $user_id)
            ->value("agentid");

        if ($agentid) {
            $haoyoutoday = DB::name("junsion_winaward_mem")
                ->where("id", "=", $agentid)
                ->find();
            $agentnum = DB::name("junsion_winaward_mem")
                ->where("agentid", "=", $agentid)
                ->count("id");


            if ($haoyoutoday) {
                $agentall = DB::name("game_relation")
                    ->where("uid", "=", $agentid)
                    ->sum("money");
                $agent["all"] = round($agentall, 2);
            } else {
                $agent["all"] = 0;
            }
            $agent["headimgurl"] = $haoyoutoday["avatar"];
            $agent["agentnum"] = $agentnum;

        } else {
            $agent["all"] = 0;
            $agent["headimgurl"] = 0;
            $agent["agentnum"] = 0;
        }


        $data["my"] = $my;
        $data["agent"] = $agent;

        echo json_encode($data);
        exit;

    }

    public function addUseritrm($use_id)
    {
        $redis = $this->redis();
        $key = $this->get_userinfo_key($use_id);
        $userinfo = $redis->get($key);//获取用户信息
        if (!$userinfo) {
            $ajaxVideo["isValid"] = false;
            echo json_encode($ajaxVideo);
            exit;
        }
        $userinfo = json_decode($userinfo, true);

        $useritem["khxianqi"] = $userinfo["khxianqi"];
        $useritem["khsprite"] = $userinfo["khsprite"];
        $useritem["khinfo"] = json_encode($userinfo["khinfo"]);
        $useritem["khmaxlvl"] = $userinfo["khmaxlvl"];
        $useritem["iskhgirl10"] = $userinfo["iskhgirl10"];
        $useritem["iskhgirl20"] = $userinfo["iskhgirl20"];

        $useritem_exist = DB::name("game_useritem")
            ->where("uid", "=", $use_id)->find();
        if ($useritem_exist) {
            DB::name("game_useritem")
                ->where("uid", "=", $use_id)
                ->update($useritem_exist);

        } else {
            $useritem["uid"] = $use_id;
            DB::name("game_useritem")
                ->where("uid", "=", $use_id)
                ->insert($useritem);
        }
    }

    public function updateRedisUserinfo($use_id, $num)
    {
        $redis = $this->redis();
        $key = $this->get_userinfo_key($use_id);
        $userinfo = $redis->get($key);//获取用户信息

        $userinfo = json_decode($userinfo, true);
        if ($this->getVideoTimes($use_id) > 19 && $this->getVideoTimes($use_id) < 21) {
            //得到一个口红精灵
            $userinfo["khsprite"] = $userinfo["khsprite"] + 1;
        }
        $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
        $userinfoJson = json_encode($userinfo);
        $userinfo = $redis->set($key, $userinfoJson);//获取用户信息
    }

    function SHA256Hex($str)
    {
        $re = hash('sha256', $str, true);
        return bin2hex($re);
    }

    function get_userinfo_key($uid)
    {
        return "khgame:userinfo:" . $uid;
    }

    //这里计算口红气值
    function countKouhong($user_id = "")
    {
        $redis = $this->redis();
        $key = $this->get_userinfo_key($user_id);
        $userinfo = $redis->get($key);//获取用户信息

        $userinfo = $redis->get($key);//获取用户信息
        $userinfo = json_decode($userinfo, true);

        $khinfo = $userinfo["khinfo"];//口红信息
        //$ims_game_iteminfo = Db::name("game_iteminfo")->select();

        $count = 0;
        foreach ($khinfo as $k => $v) {
            if ($v["khid"] > 5) {
                $num = Db::name("game_iteminfo")
                    ->where("item_lvl", "=", $v["khid"] - 5)
                    ->value("item_generate");

                $count += $num;
            }
        }
        return $count;

    }


    function redis()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
    }

    //redis没数据的时候 给的数据
    public function addInfo($uid)
    {
        $redis = $this->redis();

        $khinfo = array();
        for ($i = 0; $i < 12; $i++) {
            $item = array();
            $item['index'] = $i;
            $item['khid'] = 0;  //
            $item['caltime'] = 0;
            $khinfo[$i] = $item;
        }

        $userinfo["khxianqi"] = 50000;//默认先给5w
        $userinfo["khsprite"] = 0;
        $userinfo["khinfo"] = $khinfo;
        $userinfo["khmaxlvl"] = DB::name("game_iteminfo")
            ->where("id", "=", "17")
            ->value("item_lvl");
        $userinfo["iskhgirl10"] = 0;
        $userinfo["iskhgirl20"] = 0;
        $userinfo = json_encode($userinfo);
        $key = $this->get_userinfo_key($uid);
        $redis->set($key, $userinfo);//获取用户信息
    }

    //测试
    public function getUser($uid)
    {

        $redis = $this->redis();
        $key = $this->get_userinfo_key($uid);
        $key = $this->get_userinfo_key($uid);
        $userinfo = $redis->get($key);//获取用户信息
        if (!$userinfo) {
            $this->addInfo($uid);//redis没有数据新增数据
        }
        $userinfo = $redis->get($key);//获取用户信息
        $userinfo = json_decode($userinfo, true);


        $khinfo = $userinfo["khinfo"];//口红信息
        $lasttime = $redis->get($this->get_userinfo_key($uid));
        echo $lasttime;
        exit;
        if (!$lasttime) {
            //把用户信息返回去
            $ajaxData["cmd_id"] = 200;
            $ajaxData["cmd_value"]["code"] = 0;
            $ajaxData["cmd_value"]["data"]["userinfo"] = $userinfo;
            $ajaxData["cmd_value"]["data"]["offline"] = 0;
            dump($ajaxData);
            exit;
            echo $strRet = json_encode($ajaxData);
            exit;
        }
        $num = 1;
        //存在用户数据中
        $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
        $userinfo["khinfo"] = $khinfo;

        //$userinfoJson = json_encode($userinfo);
        //$redis->set(get_userinfo_key($uid), $userinfoJson);//把用户信息重写在redis

        //把用户信息返回去


        dump($userinfo);
        $ajaxData["cmd_id"] = 200;
        $ajaxData["cmd_value"]["code"] = 0;
        $ajaxData["cmd_value"]["data"]["userinfo"] = $userinfo;
        $ajaxData["cmd_value"]["data"]["offline"] = 0;
        dump($ajaxData);
        echo json_encode($ajaxData);
        $strRet = json_encode($ajaxData);


    }

    //清空数据
    public function clear($uid)
    {

        $redis = $this->redis();
        $key = $this->get_userinfo_key($uid);
        $redis->delete($key);//删除指定键值

        $haskey=$this->get_khgirl_hashkey($uid);
        $redis->del($haskey);
        $data["code"] = 200;
        $data["msg"] = "重置数据成功";

        dump($data);
    }

    //清空数据
    public function getDateVideoTimes($uid)
    {


        $data["code"] = 200;
        $data["msg"] = "重置数据成功";
        echo json_encode($data);
    }

    public function getUserInfo()
    {
        $uid = input("uid");
        $redis = $this->redis();
        $key = $this->get_userinfo_key($uid);

        $userinfo = $redis->get($key);//获取用户信息
        $userinfo = json_decode($userinfo, true);

        $usermax = $this->getMax($userinfo);
        $userinfo["khmaxlvl"] = $usermax;
        $ajaxData["cmd_id"] = 200;
        $ajaxData["cmd_value"]["code"] = 0;
        $ajaxData["cmd_value"]["data"]["userinfo"] = $userinfo;
        $ajaxData["cmd_value"]["data"]["videoTimes"] = $this->getVideoTimes($uid);
        $ajaxData["cmd_value"]["data"]["leftTimes"] = $this->getLeftTimes($uid);
        echo json_encode($ajaxData);
        exit;

    }

    /**
     * @param array $userinfo
     *
     */
    public function getMax($userinfo = [])
    {

        $khinfo = $userinfo["khinfo"];
        foreach ($khinfo as $k => $v) {
            $temp[] = $v["khid"];
        }

        $lvl = DB::name("game_iteminfo")
            ->where("id", "=", max($temp))
            ->value("item_lvl");

        return $lvl;

    }

    public function getVideoTimes($uid)
    {
        $time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $count = Db::name("game_video_list")
            ->where("user_id", "=", $uid)
            ->where("time", ">", $time)
            ->count();
        return $count;
    }

    public function getLeftTimes($uid)
    {

        $h = date('H');
        if ($h >= 12 && $h <= 23) {

            $time = mktime(12, 0, 0, date('m'), date('d'), date('Y'));
            $nowTime = time();
        } else {
            $time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $nowTime = time();
        }

        $count = Db::name("game_video_list")
            ->where("user_id", "=", $uid)
            ->where("time", ">=", $time)
            ->where("time", "<=", $nowTime)
            ->count();

        if ($count >= 17) {
            return 0;
        } else {
            return 17 - $count;
        }
    }

    //兑换游戏币
    public function change()
    {

        $userid = Input("userid");
        $khsprite = Input("khsprite");
        if (!$userid || !$khsprite) {
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "参数不能为空";
            echo json_encode($data);
            exit;
        }
        $redis = $this->redis();
        $key = $this->get_userinfo_key($userid);

        $userinfo = $redis->get($key);//获取用户信息
        $userinfo = json_decode($userinfo, true);
        if ($userinfo["khsprite"] < $khsprite) {
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "口红精灵不足";
            echo json_encode($data);
            exit;
        }
        //兑换余额
        $credit["credit"] = $khsprite;
        $credit["remark"] = "精灵兑换";
        $credit["createtime"] = time();
        $credit["mid"] = $userid;

        $creditInfo = DB::name("junsion_winaward_mcredit")->insert($credit);
        $user = DB::name("junsion_winaward_mem")
            ->where("id", "=", $userid)
            ->setInc("credit", $khsprite);
        $userinfo["khsprite"] = $userinfo["khsprite"] - $khsprite;
        $userinfoJson = json_encode($userinfo);

        $redis->set($key, $userinfoJson);

        $data["cmd_id"] = 200;
        $data["cmd_value"] = "兑换成功";
        echo json_encode($data);
        exit;

    }

    //给上下级分钱关系

    public function SeparateAccounts($uid)
    {
        //给上级分钱


        //给上上级分钱

    }
    //直接收益简和间接收益

    //两个小时收益接口
    public function twoHours()
    {


        $use_id = input("userid");
        $reward_amount_kouHong = $this->countKouhong($use_id);

        $reward_amount_kouHong = $reward_amount_kouHong * 3600 * 2;

        $data["code"] = 200;
        $data["msg"] = "获取成功";
        $data["data"] = $reward_amount_kouHong;

        echo json_encode($data);
        exit;


    }

//提现页面
    public function userCount()
    {

        $userid = Input("userid");

        if (!$userid) {
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "参数不能为空";
            echo json_encode($data);
            exit;
        }

        $myInfo = DB::name("junsion_winaward_mem")
            ->where("id", "=", $userid)
            ->field("game_coin,first_cashOut")
            ->find();

        $info["myGamecoin"] = $myInfo['game_coin'];
        $info["first_cashOut"] = $myInfo['first_cashOut'];
        $fenhong=$this->getHash($userid);
        $cashfenhong = DB::name("game_cashout_log")
            ->where("type=2 and uid =".$userid)
            ->sum("money");
        $cashfenhong=$cashfenhong?$cashfenhong:0;
        $info["goddess"] =$fenhong>0?$fenhong-$cashfenhong:0;

        $data["code"] = 200;
        $data["msg"] = "获取成功";
        $data["data"] = $info;
        echo json_encode($data);
        exit;
    }

    function getHash($userid)
    {

        $redis = $this->redis();
//        $field_girl="field_girl";
//        $generateTime=time();
//        $field_money="field_money";
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_girl1", $generateTime );
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_money1", 1 );
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_girl2", 1 );
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_money2", 1 );

        $res = $redis->hgetall($this->get_khgirl_hashkey($userid));

        if ($res) {

            $cont = count($res);

            for ($i = 0; $i < ($cont / 2); $i++) {
                $bbb[] = array_slice($res, $i * 2, 2);
            }
            $spire = 0;
            foreach ($bbb as $k => $v) {
                $timeot = time() - $v["field_girl" . ($k + 1)];;
                if ($timeot > 600) {
                    $time = 600;
                } else {
                    $time = $timeot;
                }
                $spire = ($spire + (($time / 86400) * $v["field_money" . ($k + 1)]));
                $spire=round($spire,2);
            }
        } else {
            $spire = 0;
        }

        return $spire;
    }

    function get_khgirl_hashkey($userid)
    {

        return "khgame:khgirlhash:" . $userid;

    }

    function testgetHash($userid)
    {
        $redis = $this->redis();
//        $field_girl="field_girl";
//        $generateTime=time();
//        $field_money="field_money";
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_girl1", $generateTime );
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_money1", 1 );
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_girl2", 1 );
//        $res=$redis->hset($this->get_khgirl_hashkey($userid),"field_money2", 1 );

        $res = $redis->hgetall($this->get_khgirl_hashkey($userid));

        if ($res) {

            $cont = count($res);

            for ($i = 0; $i < ($cont / 2); $i++) {
                $bbb[] = array_slice($res, $i * 2, 2);
            }
            $spire = 0;
            foreach ($bbb as $k => $v) {
                $timeot = time() - $v["field_girl" . ($k + 1)];;
                if ($timeot > 600) {
                    $time = 600;
                } else {
                    $time = $timeot;
                }

                dump($time / 86400);
                dump($v["field_money" . ($k + 1)]);

                $spire = ($spire + (($time / 86400) * $v["field_money" . ($k + 1)]));
                $spire=round($spire,2);
            }
        } else {
            $spire = 0;
        }

        return $spire;

    }

    function getCashInfo(){
        $user_id = input("userid",0);
        if (!$user_id) {
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "参数不能为空";
            echo json_encode($data);
            exit;
        }
        $userInfo = DB::name("junsion_winaward_mem")
            ->where("id", "=", $user_id)
            ->field("qr_zfb,qr_zfb_name")
            ->find();
        $data["code"] = 200;
        $data["msg"] = "获取成功";
        $data["data"] = $userInfo;

        echo json_encode($data);
        exit;
    }

    function setZfbInfo($user_id,$qr_zfb,$qr_zfb_name){

        $data['qr_zfb']=$qr_zfb;
        $data['qr_zfb_name']=$qr_zfb_name;
        DB::name("junsion_winaward_mem")
            ->where("id", "=",$user_id)
            ->update($data);
    }
    //提现
    function cashOut(){
        $param=Request::instance()->param();
        $user_id = $param["userid"];
        $cash_type=$param["cashType"];
        $money = $param["money"];
        $pay_type=$param["payType"];
        $qr_zfb=$param["qr_zfb"];
        $qr_zfb_name=$param["qr_zfb_name"];

        if (!$user_id||!$money||!is_numeric($money)) {
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "参数不能为空";
            echo json_encode($data);
            exit;
        }

        $allow=true;
        switch ($cash_type){
            case 1:
                $userInfo = DB::name("junsion_winaward_mem")
                    ->where("id", "=", $user_id)
                    ->field("game_coin,qr_zfb,qr_zfb_name")
                    ->find();
                $allow= $money<=$userInfo['game_coin'];
                if($userInfo['qr_zfb']&&$userInfo['qr_zfb_name']){
                    $qr_zfb=$userInfo['qr_zfb'];
                    $qr_zfb_name=$userInfo['qr_zfb_name'];
                }
                break;
            case 2:
                $goddess = $this->getHash($user_id);
                //获得总共提现的分红
                $fenhong = DB::name("game_cashout_log")
                    ->where("type=2 and uid =".$user_id)
                    ->sum("money");
                $allow= $money<=($goddess-$fenhong);
                break;
            default:
                $allow=false;
                break;
        }
        if(!$allow){
            $data["code"] = 202;
            $data["msg"] = "可提现余额不足";
            echo json_encode($data);
            exit;
        }
        switch ($pay_type){
            case 1:
                if($this->zfbCashOut($qr_zfb,$qr_zfb_name,$money)){
                    $this->updateCash($cash_type,$user_id,$money);
                    $this->setZfbInfo($user_id,$qr_zfb,$qr_zfb_name);
                    $this->insertCashout_log($cash_type,$user_id,$money);
                    $data["code"] = 200;
                    $data["msg"] = "提现已经提交,请注意查收!";
                }
                else{
                    $data["code"] = 201;
                    $data["msg"] = "提现失败,请检查账户是否正确!";
                }
                break;
            default:
                $data["code"] = 203;
                $data["msg"] = "支付方式不对!";
                break;
        }
        echo json_encode($data);
        exit;
    }

    function zfbCashOut($identity,$zfbname,$price){
        $orderno = "T".date('YmdHis',time());
        $alipay=new Alipay();
        $res=$alipay->zfbWith($identity,$zfbname,$orderno,$price);
        if($res['alipay_fund_trans_uni_transfer_response']['code'] == 10000){
            return true;
        } else {
            return false;
        }
    }
    //更新数据库用户资产
    private function updateCash($cash_type,$user_id,$price){
        switch ($cash_type){
            case 1:
                DB::name("junsion_winaward_mem")
                    ->where("id", "=",$user_id)
                    ->setDec("game_coin", $price);
                DB::name("junsion_winaward_mem")
                    ->where("id", "=",$user_id)
                    ->where("first_cashOut", "=",0)
                    ->setInc("first_cashOut", 1);
                break;
            case 2:
                DB::name("junsion_winaward_mem")
                    ->where("id", "=",$user_id)
                    ->where("first_cashOut", "=",0)
                    ->setInc("first_cashOut", 1);
                break;
            default:

                break;
        }
    }
    //添加提现记录
    private function insertCashout_log($cash_type,$user_id,$price){
        $data["type"]=$cash_type;
        $data["money"]=$price;
        $data["uid"]=$user_id;
        $data["datetime"]=time();
        DB::name("game_cashout_log")->insert($data);
    }
    //第三方登录
    public function authorizeLogin(){
        //抖音
        $code = Input("code");
        $codeType = Input("type");
        $nickName = Input("nickName");
        $avatarUrl = Input("avatarUrl");
        if(!$code||!$codeType){
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "参数不能为空";
            echo json_encode($data);
            exit;
        }
        $url="";
        $gettData=[];
        switch ($codeType){
            case "tt":
                $url="https://developer.toutiao.com/api/apps/jscode2session";
                $gettData['appid']="ttc60cb0b15926a271";
                $gettData['secret']="4284080769b72860c506aaf2d484fe336c1e7e6b";
                $gettData['code']=$code;
                $gettData['anonymous_code']=Input("anonymousCode");
                break;
            default:
                break;
        }
        if(!empty($url)){
            if(is_array($gettData)&&count($gettData)>0){
                $url = $url . '?' . http_build_query($gettData);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
            //https请求 不验证证书和host
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($ch);
            curl_close($ch);
            if($data){
                $data=json_decode($data,true);
                $openid=$data['openid']?$data['openid']:$data['anonymous_openid'];
                $this->checkUser($openid,$nickName,$avatarUrl);
            }
            else{
                $data["cmd_id"] = 201;
                $data["cmd_value"] = "授权失败";
                echo json_encode($data);
                exit;
            }
        }
        else{
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "授权地址错误";
            echo json_encode($data);
            exit;
        }
    }

    //检测用户是否存在，不存在则写入，存在则查询id和openid
    private function checkUser($openid='',$nickName='',$avatarUrl=''){
        if(!empty($openid)){
            $userInfo = DB::name("junsion_winaward_mem")
                ->where("openid", "=", $openid)
                ->field("id,openid")
                ->find();
            if($userInfo){
                $data["code"] = 200;
                $data["msg"] = "获取成功";
                $data["data"] = $userInfo;
                echo json_encode($data);
                exit;
            }
            else{
                $row["openid"]=$openid;
                $row["weid"]=2;
                $row["nickname"]=!empty($nickName)?$nickName:"字动".time();
                $row["avatar"]=$avatarUrl;
                $id=DB::name("junsion_winaward_mem")->insert($row);
                if($id){
                    $userInfo["id"]=$id;
                    $userInfo["openid"]=$openid;
                    $data["code"] = 200;
                    $data["msg"] = "获取成功";
                    $data["data"] =$userInfo;
                    echo json_encode($data);
                    exit;
                }
                else{
                    $data["cmd_id"] = 201;
                    $data["cmd_value"] = "登入失败";
                    echo json_encode($data);
                    exit;
                }
            }
        }
        else{
            $data["cmd_id"] = 201;
            $data["cmd_value"] = "授权失败";
            echo json_encode($data);
            exit;
        }
    }
}
