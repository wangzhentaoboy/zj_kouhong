<?php
/**
 * Created by PhpStorm.
 * User: ljf
 * Date: 2018/1/24
 * Time: 23:35
 */

namespace app\admin\Controller;

use think\Db;

class Admin extends Base
{

    /*
     * 后台用户列表
     * */
    public function admin_list()
    {
        $nickname = Input("nickname");
        if (Input("nickname")) {
            if (is_numeric($nickname)) {
                if (strlen($nickname) >= 6) {
                    $where['u.phone'] = array('like', "%$nickname%");
                } else {
                    $where["u.id"] = $nickname;
                }
            } else {
                $where['u.nickname'] = array('like', "%$nickname%");
            }
            $this->assign("nickname", $nickname);
        }else{
            $this->assign("nickname", "");
        }
        if (Input('group_type')) {
            $where['a.type'] = Input("group_type");
            $this->assign("group_type", input("group_type"));
        } else {
            $this->assign("group_type", 1);
        }
        $where["a.del"] = 0;
//        $count = M('admin')->alias("a")
//            ->where("del=0")->count();

        $count = Db::name('game_admin')->alias("a")

            ->where($where)
            ->count();

        /* $arr = M('activity')
           ->limit($p->firstRow, $p->listRows)*/
        $arr = Db::name('game_admin')->alias("a")

            ->where($where)

            ->paginate(10);

        //获取用户权限id
        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $this->assign('arr', $arr);

        $group = DB::name("game_auth_group")->select();
        $this->assign('group', $group);
        return $this->fetch();
    }


    /*
     * 修改密码
     * */
    public function admin_change()
    {
        if (IS_POST) {
            $id = $_POST['id'];
            $user = M('admin')->where('id=' . "'$id'")->find();
            //获取数据库中的密码
            $pwd = $user['password'];
            $salt = $user['salt'];
            $oldpwd = $_POST['oldpwd'];
            $newpwd = $_POST['newpwd'];
            $repwd = $_POST['repwd'];
            //获取传过来的旧密码加密值，对比一下
            $oldpassword = md5($oldpwd . $salt);
            if ($pwd != $oldpassword) {
                exit('原密码输入错误');
            }
            //修改数据库的密码为新密码
            $newsalt = self::rand_str(4);
            $newpassword = md5($newpwd . $newsalt);
            $data = array(
                'password' => $newpassword,
                'salt' => $newsalt,
            );
            $num = M('admin')->where('id=' . "'$id'")->save($data);
            if ($num) {
                $this->success("修改密码成功", __APP__ . '/device/fansChart');
                exit;
            } else {
                $this->error("修改密码失败", __APP__ . '/device/fansChart');
                exit;
            }
        }
        $id = $_GET['id'];
        //获取用户权限id
        $adminId = session('adminId');

        $ruleArr = getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $this->assign('adminId', $adminId);
        $this->assign('id', $id);
        $this->display();
    }

    /*
     * 添加管理员
     * */
    public function addAdmin()
    {
        //获取用户权限id
        //TODO 新增的店主密码为：admin1
        if ($_POST) {
            //代表此时表单提交了数据
          $username = $_POST['username'];
            $password = $_POST['password'];


            //如果有微信端的微信id就修改用户表的role

            $type = $_POST['type'];
            if ($type <= 0) {
                exit('请选择管理员所属组');
            }

            //获取盐值，准备存入数据库
            $salt = self::rand_str(4);
            $newpassword = md5($password . $salt);
            $logintime = time();
            $data = array(
                'username' => $username,
                'password' => $newpassword,
                'salt' => $salt,
                'type' => $type,
                'uid' => 0,
                'logintime' => $logintime,
                'del' => 0
            );

            //增加账号是否存在的验证
            $user = DB::name('game_admin')
                ->where(array('username' => $username))
                ->find();

            if ($user) {
                exit('账号已存在，请重新输入');
            }

            //开启事务，在添加管理员的同时，往权限关系表中加一条数据

            $num =  DB::name('game_admin')->insert($data);

            if (!$num) {

                exit('新增管理员失败');
            }
            //往权限表中加数据
            $where = [];
            $where['username'] = ['EQ', $username];
            $where['salt'] = ['EQ', $salt];
            $user = DB::name('game_admin')->where($where)->find();
            $uid = $user['id'];
            $type = $user['type'];
            $data1 = array(
                'uid' => $uid,
                'group_id' => $type
            );
            $result = DB::name('game_auth_group_access')->insert($data1);
            if ($result) {

                $this->success("新增管理员成功",  '/index.php/admin/Admin/admin_list');
                exit;
            } else {

                $this->error("新增管理员失败", '/index.php/admin/Admin/admin_list');
                exit;
            }

        }
        $adminId = session('adminId');
        $ruleArr = $this->getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        $arr = DB::name("game_auth_group")->select();
        $this->assign('arr', $arr);
        $adminId = session('adminId');
        $this->assign('adminId', $adminId);

        return $this->fetch();
    }

    /*
     * 编辑管理员
     * @param $id
     * */
    public
    function editAdmin()
    {
        $adminId = session('adminId');
        $ruleArr = getRules($adminId);
        //var_dump($ruleArr);
        $this->assign('ruleArr', $ruleArr);
        if (IS_POST) {
            $id = $_POST['id'];
            $username = $_POST['username'];
            if (!$username) {
                exit('请输入管理员账号');
            }
            $type = $_POST['type'];
//            if (!$type) {
//                exit('请输入管理员类型');
//            }
            //开启事务，在修改管理员的同时，修改权限关系表中的该条数据
            M()->startTrans();
            $data = array(
                'username' => $username
            );
            $num1 = M('admin')->where('id=' . "'$id'")->save($data);
            if (!$num1) {
                M()->rollback();
                $this->error("修改管理员信息失败", __APP__ . '/Admin/admin_list');
            }
//            $data1 = array(
//                'group_id' => $type
//            );
//            $num2 = M('auth_group_access')->where('uid=' . "'$id'")->save($data1);
            if ($num1) {
                M()->commit();
                $this->success("修改管理员信息成功", __APP__ . '/Admin/admin_list');
                exit;
            } else {
                M()->rollback();
                $this->error("修改管理员信息失败", __APP__ . '/Admin/admin_list');
                exit;
            }

        }

        $group = M("auth_group")->select();
        $this->assign("group", $group);
        $id = $_GET['id'];
        $arr = M('admin')->where('id=' . "'$id'")->find();
        $adminId = session('adminId');
        $this->assign('arr', $arr);
        $this->assign('adminId', $adminId);
        $this->display();
    }

    /*
     * 删除管理员
     * @param $id
     * */
    public function delAdmin()
    {
        $id = $_GET['id'];
        if (!$id) {
            $this->error("参数有误",   '/index.php/admin/Admin/admin_list');
        }
        if ($id == 1) {
            $this->error("总管理员不能删除", '/index.php/admin/Admin/admin_list');
        }

        //先删除用户
        $uid =  DB::name('game_admin')->where("id=$id")->value("uid");
        $num =  DB::name('game_admin')->where('id=' . "'$id'")->delete();


        //删除对应的权限关系
        $num1 =  DB::name('game_auth_group_access')
            ->where('uid=' . "'$id'")
            ->delete();

        if ($num1 && $num1) {

            $this->success("删除管理员信息成功", "/index.php/admin/Admin/admin_list");
            exit;
        } else {

            $this->error("删除管理员信息失败", "/index.php/admin/Admin/admin_list");
            exit;
        }
    }


    /**
     * 随机生成固定长度的随机数
     * @param String  len  截取长度，默认八位
     * @param Int     type 返回类型 [1=>字母+数字,2=>纯数字,3=>纯字母]
     * @return String
     */
    public static function rand_str($len = 8, $type = 1)
    {
        switch ($type) {
            case 1:
                $str = "0123456789qwertyuiopasdfghjklzxcvbnm";
                break;
            case 2:
                $str = "0123456789";
                break;
            default:
                $str = "qwertyuiopasdfghjklzxcvbnm";
        }
        return substr(str_shuffle($str), 0, $len);
    }

    //设置商户信息
    function set_shop_info()
    {
        $uid = I("uid") ? I("uid") : 0;
        $type = I("type") ? I("type") : 0;
        $this->assign("type", $type);
        $this->assign("uid", $uid);
        $info = M("shop_info")->where("uid=" . $uid . " and type=" . $type)->find();
        if ($type == 1) {
            $info = M("shop_info")->where("type=1")->find();
        }
        if (IS_POST) {
            $data = I("post.");
            $wx_mch_id = $data['mch_id'];
            if ($_FILES) {
                if ($_FILES['apiclient_cert']['tmp_name']) {
                    $image_name = 'apiclient_cert.pem';
                    $url = "./Public/cert/$wx_mch_id/";
                    $path = str_replace('\\', '/', $url);
                    $pathArr = explode('/', $path);
                    $filePath = '';
                    foreach ($pathArr as $k => $v) {
                        $filePath .= $v . '/';
                        if (!is_dir($filePath)) {
                            mkdir($filePath, 0777);
                        }
                    }
                    $file_url = "/Public/cert/$wx_mch_id/$image_name";
                    $res = move_uploaded_file($_FILES['apiclient_cert']['tmp_name'], "./Public/cert/$wx_mch_id/" . $image_name);
                    if ($res) {
                        $data['apiclient_cert'] = $file_url;
                    } else {
                        $this->error('apiclient_cert证书上传失败');
                        die;
                    }
                }
                if ($_FILES['apiclient_key']['tmp_name']) {
                    $image_name = 'apiclient_key.pem';
                    $url = "./Public/cert/$wx_mch_id/";
                    $path = str_replace('\\', '/', $url);
                    $pathArr = explode('/', $path);
                    $filePath = '';
                    foreach ($pathArr as $k => $v) {
                        $filePath .= $v . '/';
                        if (!is_dir($filePath)) {
                            mkdir($filePath, 0777);
                        }
                    }
                    $file_url = "/Public/cert/$wx_mch_id/$image_name";
                    $res = move_uploaded_file($_FILES['apiclient_key']['tmp_name'], "./Public/cert/$wx_mch_id/" . $image_name);
                    if ($res) {
                        $data['apiclient_key'] = $file_url;
                    } else {
                        $this->error('apiclient_key证书上传失败');
                        die;
                    }
                }
            }
            if ($info) {
                if ($type == 1) {
                    $res = M("shop_info")->where("type=1")->save($data);
                } else {
                    $res = M("shop_info")->where("uid=" . $uid)->save($data);
                }
            } else {
                $res = M("shop_info")->add($data);
            }
            if ($res) {
                $this->success('操作成功');
                die;
            } else {
                $this->error('操作失败');
                die;
            }
        }
        $this->assign("info", $info);
        $this->display();
    }

    //修改管理员身份
    function set_user_role()
    {
        $id = I("id") ? I("id") : '';
        $info = M("admin")->where("id=$id")->find();
        $type = I("type") ? I("type") : '';
        if (empty($info) && $type == '') {
            echo json_encode(array('code' => 201, 'msg' => '参数错误'));
            die;
        }
        if ($info['type'] == $type) {
            echo json_encode(array('code' => 202, 'msg' => '您未改变管理员身份'));
            die;
        }
        M()->startTrans();
        $res1 = M("admin")->where("id=$id")->save(array('type' => $type));
        $res2 = M("userinfo")->where("id=" . $info['uid'])->save(array("role" => $type == 1 ? 6 : $type));
        $res3 = M('auth_group_access')->where("uid=" . $id)->save(array("group_id" => $type));
        if ($res1 && $res2 && $res3) {
            M()->commit();
            echo json_encode(array('code' => 200, 'msg' => '修改成功'));
            die;
        } else {
            M()->rollback();
            echo json_encode(array('code' => 203, 'msg' => '修改失败'));
            die;
        }
    }


}