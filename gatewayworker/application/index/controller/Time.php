<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Gateway;
use think\Request;

class Time extends Controller
{

    public function redis()
    {

        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
    }

    function get_userinfo_key($uid)
    {
        return "khgame:userinfo:" . $uid;
    }

    function index()
    {
        $redis = $this->redis();
        $arr = DB::name('junsion_winaward_mem')
            ->field("id")
            ->select();

        foreach ($arr as $k => $v) {
            $key = $this->get_userinfo_key($v["id"]);

            $userinfo = $redis->get($key);//获取用户信息
            if (!$userinfo) {
                continue;
            }
            $userinfo = json_decode($userinfo, true);
            if (!$userinfo["khinfo"]) {
                continue;
            }
            $usermax = $this->getMax($userinfo);

            $data["khxianqi"] = $userinfo["khxianqi"];
            $data["khsprite"] = $userinfo["khsprite"];

            $data["khinfo"] = json_encode($userinfo["khinfo"]);
            $data["khmaxlvl"] = $usermax;
            $data["iskhgirl10"] = $userinfo["iskhgirl10"];
            $data["iskhgirl20"] = $userinfo["iskhgirl20"];
            $useritem_exist = DB::name("game_useritem")
                ->where("uid", "=", $v["id"])
                ->find();
            //查找离线时间
            $lasttime = $redis->get($this->get_lastlogin_key($v["id"]));

            if ($lasttime) {
                $data["lastlogintime"] = $lasttime;
            }

            if ($useritem_exist) {
                DB::name("game_useritem")
                    ->where("uid", "=", $v["id"])
                    ->update($data);

            } else {
                $data["uid"] = $v["id"];
                DB::name("game_useritem")
                    ->where("uid", "=", $v["id"])
                    ->insert($data);
            }


        }


    }

    function get_lastlogin_key($uid)
    {
        return "khgame:lastlogin:" . $uid;
    }

    public function getMax($userinfo = [])
    {

        $khinfo = $userinfo["khinfo"];
        if (!$khinfo) {
            return 0;
        }
        foreach ($khinfo as $k => $v) {
            $temp[] = $v["khid"];
        }

        $lvl = DB::name("game_iteminfo")
            ->where("id", "=", max($temp))
            ->value("item_lvl");
        if (!$lvl) {
            return 0;
        }
        return $lvl;


    }

    //添加数据
    public function addgrade()
    {
//
        $uid = input("uid");
        $index = input("index");

        $khid = input("get.khid/d");
        $caltime = time();
        if(!$uid ||!$index||!$khid){
            echo "缺少参数";exit;
        }
//
        $redis = $this->redis();
        $key = $this->get_userinfo_key($uid);
        $userinfo = $redis->get($key);//获取用户信息
        $userinfo = json_decode($userinfo, true);

         $khinfo = $userinfo["khinfo"];//口红信息
       // $khinfo = '[{"index":0,"khid":0,"caltime":0},{"index":1,"khid":0,"caltime":0},{"index":2,"khid":0,"caltime":0},{"index":3,"khid":8,"caltime":1589473789},{"index":4,"khid":7,"caltime":1589473789},{"index":5,"khid":7,"caltime":1589473789},{"index":6,"khid":7,"caltime":1589473789},{"index":7,"khid":7,"caltime":1589473789},{"index":8,"khid":0,"caltime":0},{"index":9,"khid":0,"caltime":0},{"index":10,"khid":0,"caltime":0},{"index":11,"khid":0,"caltime":0}]';

        //$khinfo = json_decode($khinfo, true);

        foreach ($khinfo as $k => $v) {
            if ($v["index"] == $index) {
                $khinfo[$k]["khid"] = $khid;
                $khinfo[$k]["caltime"] = $caltime;
            }

        }
        $userinfo["khinfo"] = $khinfo;
        $userinfo = json_encode($userinfo);

        $redis->set($key, $userinfo);//获取用户信息

        dump(json_decode($userinfo, true));

    }


}
