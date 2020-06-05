<?php
/**
 * Created by PhpStorm.
 * User: ljf
 * Date: 2018/2/6
 * Time: 22:26
 */

namespace app\admin\Controller;

/*
 * 添加权限管理的类
 * */
use think\Db;

class Auth extends Base
{
    /*
     * 权限组列表
     * */
    public function roleList()
    {
        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        //获取权限组信息
        $count = Db::name('game_auth_group')->count();

        $arr = DB::name('game_auth_group')
            ->paginate(10);





        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);

        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $this->assign('arr', $arr);

        return $this->fetch();
    }

    /*
     * 改变权限组的状态
     * @param $id
     * */
    public function changeRole()
    {
        $adminId = session('adminId');
        $ruleArr = getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $id = $_GET['id'];
        //var_dump($id);
        if ($id == 1) {
            $this->error("对不起，超级管理员无法禁用自己", __APP__ . '/Auth/roleList.html');
            exit;
        }
        $arr = DB::name('auth_group')->where('id=' . "'$id'")->find();
        //var_dump($arr);
        $status = $arr['status'];
        if ($status == 0) {
            $data['status'] = 1;
            $num = DB::name('auth_group')->where('id=' . "'$id'")->save($data);
            if ($num) {
                $this->success("修改状态成功", __APP__ . '/Auth/roleList.html');
                exit;
            } else {
                $this->error("修改状态失败", __APP__ . '/Auth/roleList.html');
                exit;
            }
        } else {
            $data['status'] = 0;
            $num = DB::name('auth_group')->where('id=' . "'$id'")->save($data);
            if ($num) {
                $this->success("修改状态成功", __APP__ . '/Auth/roleList.html');
                exit;
            } else {
                $this->error("修改状态失败", __APP__ . '/Auth/roleList.html');
                exit;
            }
        }
    }

    /*
     * 删除权限组
     * */
    public function delRole()
    {
        $id = $_GET['id'];
        //var_dump($id);
        if ($id == 1) {
            $this->error("对不起，超级管理员无法删除自己", __APP__ . '/Auth/roleList.html');
            exit;
        }
        //执行删除操作
        $num = DB::name('auth_group')->where('id=' . "'$id'")->delete();
        if ($num) {
            $this->success("删除权限组成功", __APP__ . '/Auth/roleList.html');
            exit;
        } else {
            $this->error("删除权限组失败", __APP__ . '/Auth/roleList.html');
            exit;
        }
    }

    //系统设置
    public function set_sys()
    {
        //获取用户权限id
        $adminId = session('adminId');
        $ruleArr = getRules($adminId);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        if ($_POST) {
            $result = M("sys_enum")->where("id=" . $_POST['id'])->save(array('value' => $_POST['value']));
            echo $result;
        } else {
            $set_list = M("sys_enum")->where("id!=3")->order("id asc")->select();
            $this->assign("set_list", $set_list);
            $this->display();
        }
    }


    //网站登录日志
    //登录日志
    function login_log()
    {
        //获取用户权限id
        $adminId = session('adminId');
        $ruleArr = getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);

        $page = Input('page') ? Input('page') : 10;

        $where = array();
        if (Input('username')) {
            $where['a.username'] = array('like', "%" . Input('username') . "%");
            $this->assign("username", Input('username'));
        }

        $count = M()->table("smj_admin_login as l")->join("left join smj_admin as a on l.uid=a.uid")->where($where)->count();

        $p = getpage($count, $page);
        $arr = M()->table("smj_admin_login as l")
            ->join("left join smj_admin as a on l.uid=a.uid")
            ->join("left join smj_userinfo u on u.id = l.uid")
            ->limit($p->firstRow, $p->listRows)
            ->where($where)
            ->order("l.id desc")->select();
        $this->assign('arr', $arr);
        $this->assign('page', $p->show());
        $this->display();
    }

    /**
     * App登陆日志
     *
     */
    function login_app_log()
    {
        //获取用户权限id
        $adminId = session('adminId');
        $ruleArr = getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $page = Input('page') ? Input('page') : 15;
        $where = array();
        if (Input('username')) {
            $where['u.nickname'] = array('like', "%" . Input('username') . "%");
            $this->assign("username", Input('username'));
        }
        $count = M()
            ->table("smj_applog as l")
            ->join("left join smj_userinfo u on u.id = l.uid")
            ->join("left join smj_device d on d.deviceid =l.deviceid")
            ->where($where)
            ->count();
        $p = getpage($count, $page);
        $arr = M()->table("smj_applog as l")
            ->join("left join smj_userinfo u on u.id = l.uid")
            ->join("left join smj_device d on d.deviceid =l.deviceid")
            ->limit($p->firstRow, $p->listRows)
            ->where($where)
            ->order("l.id desc")
            ->select();

        $this->assign('arr', $arr);
        $this->assign('page', $p->show());
        $this->display();
    }

    //菜单规则列表
    function node_list()
    {
        $where["level"] = 1;
        $arr = DB::name('game_auth_rule')
            ->where($where)
            ->order("id asc")
            ->select();
        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $this->assign('arr', $arr);
        $arr2 = DB::name('game_auth_rule')
            ->where("level=2")
            ->order("sort asc")
            ->select();
        /* foreach ($arr2 as $k=>$v){
            $arr3 = DB::name('game_auth_rule')->where("level=3 and pid=".$v['id'])->order("sort asc")->select();
            $arr2[$k]['arr3'] = $arr3;
        } */
        $this->assign('arr2', $arr2);
        $arr3 = DB::name('game_auth_rule')
            ->where("level=3")
            ->order("sort asc")
            ->select();
        $this->assign('arr3', $arr3);
        return $this->fetch();
    }

    //添加/编辑菜单
    function add_node()
    {
        $id = Input("id") ? Input("id") : 0;
        if ($_POST) {
            $data = Input("post.");
            if ($id > 0) {
                $level_info = DB::name('game_auth_rule')->where("id=" . $data['pid'])->field("level")->find();
                $data['level'] = $level_info['level'] + 1;
                $res = DB::name('game_auth_rule')->where("id=$id")->update($data);
            } else {
                if ($data['pid'] > 0) {
                    $level_info = DB::name('game_auth_rule')->where("id=" . $data['pid'])->field("level")->find();
                    $data['level'] = $level_info['level'] + 1;
                } else {
                    $data['level'] = 1;
                }
                $res = DB::name('game_auth_rule')->insert($data);
            }
            if ($res) {
                if ($id == 0) {
                    //if($data['pid']>0){
                    $info = DB::name('game_auth_group')->where("id=1")->find();
                    $rules = $info['rules'] . "," . $res;
                    DB::name('game_auth_group')
                        ->where("id=1")
                        ->update(array('rules' => $rules));
                    //}
                }
                $this->success("操作成功",   '/index.php/admin/Auth/node_list.html');
            } else {
                $this->error("操作失败");
            }
            die;
        }
        $where["id"] = $id;
        $info = DB::name('game_auth_rule')->where($where)->find();
        $arr = DB::name('game_auth_rule')->where("level=1")->order("sort asc")->select();
        foreach ($arr as $k => $v) {
            $arr2 = DB::name('game_auth_rule')->where("pid=" . $v['id'])->order("sort asc")->select();
            $arr[$k]['level_arr'] = $arr2;
        }
        $this->assign('id', $id);
        $this->assign('info', $info);
        $this->assign('arr', $arr);
        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        return $this->fetch();
    }

    function del_node()
    {
        $type = Input("type");
        $id = Input('id');
        $where["id"] = $id;
        if ($type != 3) {
            $where1["pid"] = $id;
            $info = DB::name('game_auth_rule')->where($where1)->find();
            if ($info) {
                $this->error("请先删除子菜单");
                die;
            }
        }
        $res = DB::name('game_auth_rule')->where($where)->delete();
        if ($res) {
            if ($type != 1) {
                $arr = DB::name('auth_group')->where("id=1")->find();
                $rules_arr = explode(",", $arr['rules']);
                foreach ($rules_arr as $k => $v) {
                    if ($v == $id) {
                        unset($rules_arr[$k]);
                    }
                }
                sort($rules_arr);
                $rules_str = implode(",", $rules_arr);
                DB::name('auth_group')->where("id=1")->save(array('rules' => $rules_str));
            }
            $this->success("删除成功", __APP__ . "/Auth/node_list.html");
        } else {
            $this->error("删除失败");
        }
    }

    //编辑权限
    function edit_auth()
    {

        $id = input("id") ? input("id") : 0;
        $group_info = DB::name("game_auth_group")->where("id=$id")->find();
        $this->assign("group_info", $group_info);
        $rules_arr = explode(",", $group_info['rules']);
        $rules_info =  DB::name("game_auth_rule")
            ->where("level>0")
            ->field("id,pid as parent,title as text,level")
            ->order("sort asc")
            ->select();
        foreach ($rules_info as $k => $v) {
            if ($v['parent'] == 0) {
                $rules_info[$k]['parent'] = '#';
                //$rules_info[$k]['state']['selected'] = true;
                $rules_info[$k]['state']['opened'] = true;
            }
            $rules_info[$k]['type'] = 'menu';
            $rules_info[$k]['state']['selected'] = false;
            $rules_info[$k]['state']['opened'] = true;

            foreach ($rules_arr as $kk => $vv) {
                if ($v['id'] == $vv) {
                    if ($v['level'] == 2) {
                        $info = DB::name("game_auth_rule")->where("level=3 and pid=" . $v['id'])->field("id")->find();
                        if (empty($info)) {
                            $rules_info[$k]['state']['selected'] = true;
                        }
                    }
                    if ($v['level'] == 3) {
                        $rules_info[$k]['state']['selected'] = true;
                    }
                }
            }
            unset($v['level']);
        }
        $this->assign("content", json_encode($rules_info));
        return $this->fetch();
    }

    /*
     * 添加用户组信息
    * */
    public function addRole()
    {


        $id = $_POST['id'];
        if ($id == 1) {
            $this->error("无法更改超级管理员权限");
            exit;
        }
        $rules_arr = explode(",", $_POST['rules']);
        sort($rules_arr);
        $rules_str = implode(",", $rules_arr);
        $data = array(
            'title' => $_POST['title'],
            'status' => 1,
            'rules' => $rules_str
        );
        if ($id > 0) {
            $num = DB::name('game_auth_group')->where('id=' . "'$id'")->update($data);
        } else {
            $num = DB::name('game_auth_group')->insert($data);
        }
        if ($num) {
            echo 200;
        } else {
            echo 201;
        }
    }


}