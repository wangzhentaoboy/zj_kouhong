<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Gateway;
use think\Request;

class Index extends Base
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
    function index()
    {

        //获取session
        $adminId = session('adminId');
        //var_dump($adminId);
        $ruleArr = $this->getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr',$ruleArr);
        $this->assign('adminId',$adminId);

        return $this->fetch();


    }


    public function user_list()
    {
        $page = I('page') ? I('page') : 15;
        $nickname = I("nickname");
        if (I("nickname")) {
            if (is_numeric($nickname)) {
                if (strlen($nickname) >= 6) {
                    $where['phone'] = array('like', "%$nickname%");
                } else {
                    $where["id"] = $nickname;
                }
            } else {
                $where['nickname'] = array('like', "%$nickname%");
            }
            $this->assign("nickname", $nickname);
        }

        /* if (I("phone")) {
        	$where['phone'] = array('like', "%".I("phone")."%");
        } */
        $api = new ApiController();
        $this->assign('alldevice', $api->allDevicelist());
        if (!I("deviceid")) {

            $count = M('userinfo')->where($where)->count();
            $p = getpage($count, $page);
            $arr = M('userinfo')
                ->limit($p->firstRow, $p->listRows)
                ->where($where)
                ->order('id')
                ->select();
        } else {

        }

        //传过来的机器id

        foreach ($arr as &$v) {
            $res = M("order")->alias("o")
                ->join("smj_device as d on o.deviceid=d.deviceid")
                ->where("(o.openid='" . $v['openid'] . "' or o.openid='" . $v['sopenid'] . "') and o.openid!='' and o.state=2")
                ->field("d.name,o.deviceid,o.openid,count(*)")
                ->group("deviceid")
                ->order("count(*) desc")
                ->limit("1")
                ->find();

            $v["device_name"] = $res['name'] ? $res['name'] : '暂无购买记录';
            //  SELECT deviceid,openid ,count(*) FROM   smj_order  where openid = 'o_Osb5FCxoTdIPfv_lJHdyQsenDE' or openid = 'ok1sP1jpq7BDuua7euqCVSdknx64' GROUP BY deviceid order by count(*) desc limit 1;
            $role = $v['role'];

            switch ($role) {
                case 0:
                    $v['role_name'] = '普通用户';
                    break;
                case 18:
                    $v['role_name'] = '管理员';
                    break;
                case 21:
                    $v['role_name'] = '城市合伙人';
                    break;
                case 26:
                    $v['role_name'] = '运维人员';
                    break;
                case 27:
                    $v['role_name'] = '促销人员';
                    break;
                case 7:
                    $v['role_name'] = '总管理员';
                    break;
                case 6:
                    $v['role_name'] = '超级管理员';
                    break;
                case 1:
                    $v['role_name'] = '普通用户';
                    break;
            }
            if($v['role_name']=="普通用户" && $v['is_test']==1){
                $v['role_name'] = '测试人员';
            }
            $phone = $v['phone'];
            if (!$phone) {
                $v['phone'] = '未绑定';
            }
        }
        //获取用户权限id
        $adminId = session('adminId');
        $ruleArr = getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $this->assign('adminId', $adminId);
        $this->assign('arr', $arr);
        $this->assign('page', $p->show());
        $this->display();
    }

}
