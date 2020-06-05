<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Gateway;
use think\Request;

class Index extends Controller
{

    public function redis()
    {

        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
    }

    function _empty()
    {

        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码

        return $this->fetch("404");

    }


    public function demo()
    {
        //接受参数
        input("name");//代替   $_GET   $_POST
        //查找
        Db::name("game_iteminfo")->where("item_id", "=", 1)->select();//查找多个
        Db::name("game_iteminfo")->where("item_id", "=", 1)->find();//查找单个
        //新增
        $data["item_id"] = "1";
        $data["item_lvl"] = "1";
        $data["item_name"] = "Russell";
        $data["item_generate"] = "1";
        $data["item_consume"] = "1";
        Db::name("game_iteminfo")->insert($data);
        //修改
        $data["item_id"] = "1";
        $data["item_lvl"] = "1";
        $data["item_name"] = "Russell";
        $data["item_generate"] = "1";
        $data["item_consume"] = "1";

        Db::name("game_iteminfo")->where("id", "=", "1")->update($data);
// 删除
        Db::name("game_iteminfo")->where("id", "=", "1")->delete();

    }


    public function index1()
    {
        $this->uid = input('uid');
        session('uid', $this->uid);
        return $this->fetch();
    }

    function bind()
    {

        $uid = session('uid');
        $client_id = input('client_id');
        $gateway = new Gateway();
        $gateway->bindUid($client_id, $uid);
        $message = '绑定成功' . $uid . '-' . $client_id;
        $gateway->sendToUid($uid, $message);

    }

    function message()
    {
        $to_uid = input('uid');
        $message = input('msg');
        $gateway = new Gateway();
        $data['msg'] = $message;
        $data['from_uid'] = session('uid');
        $data['to_uid'] = $to_uid;
        $gateway->sendToUid($to_uid, json_encode($data)); //发给对方
//        $gateway->sendToUid($data['from_uid'], json_encode($data)); //发给自己
        echo json_encode($data);
    }


    //整个逻辑  登录时返回每个页面的逻辑
    function login($uid = "1111", $openid = "")
    {
        //把用户信息从redis取出来
        $redis = $this->redis();

//        $khinfo[0]["level"] = 0;
//        $khinfo[1]["level"] = 1;
//        $khinfo[2]["level"] = 2;
//        $khinfo[3]["level"] = 0;
//        $khinfo[4]["level"] = 0;
//        $khinfo[5]["level"] = 6;
//        $khinfo[6]["level"] = 2;
//        $khinfo[7]["level"] = 0;
//        $khinfo[8]["level"] = 0;
//        $khinfo[9]["level"] = 6;
//        $khinfo[10]["level"] = 2;
//        $khinfo[11]["level"] = 0;
//        $khinfo[12]["level"] = 0;
//
//        $khinfo=json_encode($khinfo);
//
//        $userinfo["khxianqi"]=10000;
//        $userinfo["khsprite"]=10000;
//        $userinfo["khinfo"]=$khinfo;
//        $userinfo["khmaxlvl"]=10;
//        $userinfo["iskhgirl10"]=1;
//        $userinfo["iskhgirl20"]=1;
//        $userinfo=json_encode($userinfo);
//        $redis->set($uid,$userinfo);//获取用户信息

        $userinfo = $redis->get($uid);//获取用户信息
        $userinfo = json_decode($userinfo, true);

        $khinfo = $userinfo["khinfo"];//口红信息
        $khinfo = json_decode($khinfo, true);
        $lasttime = $redis->get($uid . "lasttime");
        $ratio = 0.25;
        $time = time() - $lasttime;//断线多长时间
        $num = $this->countlipstick($khinfo, $time, $ratio);//断线一共产生多少仙气值
        //存在用户数据中
        $userinfo["khxianqi"] = $userinfo["khxianqi"] + $num;
        $userinfoJson = json_encode($userinfo);
        $redis->set($uid, $userinfoJson);//把用户信息重写在redis
    }




    //计算从离线时间到到等登录多长时间 然后增加口红值
    //计算口红产生的仙气值 cundao redis
    /**
     * @param array $arr 口红的数组
     * @param int $time 断线时间
     * @param int $ratio 断线计算为1/4
     * @return int
     */
    public function countlipstick($arr = [], $time = 0, $ratio = 0)
    {

        $num = 0;
        foreach ($arr as $k => $v) {
            $num = $num + ((pow(2, $v["level"]) - $v["level"] + 1) * $ratio * $time);
        }
        //存到redis里面 就行
        return $num;
    }

    //购买口红消耗仙气值

    public function buylipstick($uid = "1111")
    {
        $userinfo = $this->userinfo($uid);
        $userinfo = json_decode($userinfo, true);
        $khinfo = json_decode($userinfo["khinfo"], true);
        foreach ($khinfo as $key => $val) {
            $temp[] = $val["level"]; // 用一个空数组来承接字段
        }
        $max = max($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
        $min = min($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
        //假如消耗$n个口红值
        if ($min > 0) {
            $data["cmd_id"] = 201;
            $data["cmd_value"]["code"] = 2;
            $data["cmd_value"]["data"] = "购买失败，格子不够";
            echo json_encode($data);
            exit;
        }
        $n = 888;
        if ($userinfo["khxianqi"] < $n) {
            $data["cmd_id"] = 201;
            $data["cmd_value"]["code"] = 1;
            $data["cmd_value"]["data"] = " 购买失败，仙气值不足";
            echo json_encode($data);
            exit;
        }

        //购买 看一下是在那个格子显示将格子为0的显示
        //购买等级的口红
        $level = $max - 8;
        if ($level < 1) {
            $level = 1;
        }
        foreach ($khinfo as $key => $val) {
            if ($val["level"] == 0) {
                $khinfo[$key]["level"] = $level;
                break;
            }
        }
        $redis = $this->redis();
//购买成功
        $userinfo["khinfo"] = json_encode($khinfo);
        $userinfo["khxianqi"] = $userinfo["khxianqi"] - $n;
        $userinfoJson = json_encode($userinfo);
        $redis->set($uid, $userinfoJson);//把用户信息重写在redis   后续可以再看数据的展示

    }

    //查找用户信息

    public function userinfo($uid = "1111")
    {
        $redis = $this->redis();
        $userinfo = $redis->get($uid);

        return $userinfo;

    }

}
