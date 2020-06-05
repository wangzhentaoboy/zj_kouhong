<?php

namespace app\admin\controller;

use app\index\controller\Api;
use think\Controller;
use think\Db;
use think\Gateway;
use think\Request;

class User extends Base
{


    function _empty()
    {

        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码

        return $this->fetch("404");

    }

    function index()
    {

        //获取session
        $adminId = session('adminId');
        //var_dump($adminId);
        $ruleArr = $this->getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);

        return $this->fetch();


    }


    public function user_list()
    {

        $where = [];
        $nickname = Input("nickname");
        if (Input("nickname")) {
            if (is_numeric($nickname)) {
                if (strlen($nickname) >= 6) {
                    $where['a.phone'] = array('like', "%$nickname%");
                } else {
                    $where["a.id"] = $nickname;
                }
            } else {
                $where['a.nickname'] = array('like', "%$nickname%");
            }
            $this->assign("nickname", $nickname);
        } else {
            $this->assign("nickname", 1);
        }

        /* if (I("phone")) {
        	$where['phone'] = array('like', "%".I("phone")."%");
        } */


//        $count = DB::name('junsion_winaward_mem')->alias("a")
//            ->join("left join ims_game_useritem b","a.id=b.uid")
//            ->where($where)
//            ->count();

        // $p = getpage($count, $page);
        $arr = DB::name('junsion_winaward_mem')->alias("a")
            ->join("ims_game_useritem b", "a.id=b.uid", "left")
            ->where($where)
            ->order('a.id desc')
            ->paginate(10);

//        foreach ($arr as $k=>$item) {
//           if($item["khinfo"]){
//                $khinfoarr=json_decode($item["khinfo"],true);
//               $arr["max"]=$this->getMax($khinfoarr);
//           }else{
//               $arr["max"]=0;
//           }
//
//        }
        //传过来的机器id
       //dump($arr);exit;

        //获取用户权限id
        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $this->assign('adminId', $adminId);
        $this->assign('arr', $arr);

        return $this->fetch();
    }

    public function cashout_log(){

        $logList = DB::name("game_cashout_log")->where("uid", $_GET["uid"])->order('id desc')->paginate(10);


        $this->assign('logList', $logList);

        return $this->fetch();
    }

    public function getMax($khinfo = [])
    {


        foreach ($khinfo as $k => $v) {
            $temp[] = $v["khid"];
        }

        $lvl = DB::name("game_iteminfo")
            ->where("id", "=", max($temp))
            ->value("item_lvl");

        return $lvl;


    }
    public function rate()
    {

        $rate = DB::name("game_38rate")->select();
        if ($_GET) {

            $rate = DB::name("game_38rate")->where("id", $_GET["id"])->find();
            $this->assign("rate", $rate);
            return $this->fetch("rate");
        }
        if ($_POST) {
            $data["name"] = $_POST["name"];
            $data["rate"] = $_POST["rate"];
            $data["addtime"] = time();
            $id = $_POST["id"];
            $rate = DB::name("game_38rate")
                ->where("id", $id)
                ->update($data);
            if ($rate) {
                $ajaxdata["code"] = 200;
                echo json_encode($ajaxdata);
                exit;
            } else {
                $ajaxdata["code"] = 201;
                $ajaxdata["msg"] = "添加失败";
                echo json_encode($ajaxdata);
                exit;
            }
        }
        $this->assign("rate", $rate);
        return $this->fetch("rate_list");
    }


}
