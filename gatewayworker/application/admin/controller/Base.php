<?php
/**
 * Created by PhpStorm.
 * User: ljf
 * Date: 2018/1/10
 * Time: 0:46
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Session;

header('Content-Type:text/html;charset=utf-8');

class Base extends Controller
{
    public function _initialize()
    {

        $title="美女大作战后台管理系统";
        $this->assign("title",$title);


//当前模块名称
        $moduleName = request()->module();

//当前控制名称
        $controllerName = request()->controller();

//当前类方法名称
        $actionName = request()->action();

        $this->assign("CONTROLLER_NAME", $controllerName);
        $this->assign("ACTION_NAME", $actionName);
        session_start();
        //$adminId = cookie('adminId');
        Session::set('name','thinkphp');
        $adminId= Session::get('adminId');

        if (!isset($adminId)) {
            $this->success("您还没有登录",  '/index.php/admin/Login/login');
            exit;
        }   else {
            session_write_close();
            $nextWeekTime = 3600 * 24 * 7;
            session_cache_expire($nextWeekTime / 60);
            session_set_cookie_params($nextWeekTime);
            session_start();
            $admin = Db::name("game_admin")->where("id=" . $adminId)->field("type")->find();


//            $userinfp = DB::name("userinfo")
//                ->where("id='" . session("userId") . "'")
//                ->find();
            $this->assign("userinfp", []);
            $this->assign("msg", $this->greetings());

            $admin = DB::name("game_admin")
                ->where("id=$adminId")
                ->field("type")
                ->find();
            $this->assign("type", $admin['type']);
            $group =  DB::name("game_auth_group")->where("status=1 and id=" . $admin['type'])->field("rules")->find();
            $rules1 =  DB::name("game_auth_rule")->where("level=1 and id in(" . $group['rules'] . ")")->field("id,name,title")->order("sort asc")->select();
            $rules2 =  DB::name("game_auth_rule")->where("is_open=1 and level=2 and id in(" . $group['rules'] . ")")->field("id,name,title,pid")->order("sort asc")->select();
            foreach ($rules1 as $k => $v) {
                $arr = [];
                foreach ($rules2 as $kk => $vv) {
                    if ($vv['pid'] == $v['id']) {
                        $rules2[$kk]['name'] = str_replace("-", "/", $vv['name']);
                        $item = substr(strstr($vv['name'], '-'), 1);
                        $rules2[$kk]['action'] = $item;
                        array_push($arr, $item);
                        $rules3 = DB::name("game_auth_rule")->where("level=3 and pid=" . $vv['id'])->field("id,name,title,pid")->order("sort asc")->select();
                        if ($rules3) {
                            foreach ($rules3 as $kkk => $vvv) {
                                if ($vvv['pid'] == $vv['id']) {
                                    $item2 = substr(strstr($vvv['name'], '-'), 1);
                                    array_push($arr, $item2);
                                }
                            }
                        }
                    }
                }
                $rules1[$k]['menu'] = $arr;
            }

            $this->assign("rules1", $rules1);
            $this->assign("rules2", $rules2);
            // action('Publicc/auth', array($adminId));
        }
    }


    function greetings()
    {
        $hour = date("H");
        switch ($hour) {
            case $hour < 6:
                $text = "你好，又是一个不眠夜!";
                break;
            case $hour < 9:
                $text = "你好,新的一天开始了,加油!";
                break;
            case $hour < 12:
                $text = "上午好";
                break;
            case $hour < 14:
                $text = "中午好！";
                break;
            case $hour < 17:
                $text = "下午好！别打盹哦！";
                break;
            case $hour < 18:
                $text = "下午好！快下班了呢";
                break;
            case $hour < 19:
                $text = "傍晚好！还在加班吗？";
                break;
            case $hour < 22:
                $text = "晚上好！夜色好美啊！";
                break;
            default:
                $text = "晚上好！";
                break;
        }
        return $text;
    }

    public function ifSamePlace($adminid)
    {
        return true;
        $sessionId = DB::name("admin")->where("id=$adminid")->getField("session_id");
        if (session_id() == $sessionId) {
            return true;
        } else {
            return false;
        }
    }

    function getRules($adminId)
    {
        $ruleArr = DB::name('game_admin')->where('id='."'$adminId'")->find();
        $type = $ruleArr['type'];
        $ruleStr1 = DB::name('game_auth_group')->where('id='."'$type'")->find();
        $ruleStr = $ruleStr1['rules'];
        //var_dump($ruleStr);
        $ruleArr = explode(',',$ruleStr);
        return $ruleArr;
    }

    function getpage($count, $pagesize = 5)
    {
        $p = new Pag($count, $pagesize);
        $p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录 第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
        $p->setConfig('prev', '上一页');
        $p->setConfig('next', '下一页');
        $p->setConfig('last', '末页');
        $p->setConfig('first', '首页');
        $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $p->lastSuffix = false; //最后一页不显示为总页数
        return $p;
    }
}