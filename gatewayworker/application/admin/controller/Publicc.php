<?php
/**
 * Created by PhpStorm.
 * User: ljf
 * Date: 2018/2/6
 * Time: 0:08
 */
namespace app\admin\controller;
use Think\Auth;
use Think\Controller;
use think\Db;

/*
 * 公用方法控制器
 * */
class Publicc extends Controller
{
  /**
   * [auth 权限认证]
   *
   */
  //TODO  无法加载Auth控制器
  public function auth($adminId) {
    //$auth        = new \Think\Auth();
    $auth        =  new \think\Auth();

    // 普通用户得到权限列表
    $getAuthList = $auth->getAuthList($adminId, 1);

    if ( empty($getAuthList) ) {
      session(null);
      $this->error('你的账号没任何操作权限！', U('Login/login'));
    }

    session('authList', $getAuthList);
      //当前模块名称
      $moduleName = request()->module();

//当前控制名称
      $controllerName = request()->controller();

//当前类方法名称
      $actionName = request()->action();

    if ( !$auth->check($controllerName.'-'.$actionName , $adminId) ) {

      // 无访问权限的时候才跳转
      $white = in_array($controllerName.'-'.$actionName, array(
        'Login-login','Index-index'
      ));

      $preJumpUrl = session('preJumpUrl');
//             if(session('adminId')==1) return true;
      if ( $white || empty($preJumpUrl) ) {
        // 找出可直接跳转的权限地址
        $canJumpList = Db::name('auth_rule')->where(array('direct_jump'=>'1'))->order('sort DESC')->getField('name', true);
        foreach ($getAuthList as $value) {
          foreach ( $canJumpList as $jumpValue ) {
            if ( strtolower($value) == strtolower($jumpValue) ) {
              $url = str_replace('-', '/', $jumpValue);
              session('preJumpUrl', $url);
              header('LOCATION:' . U($url));
              exit();
            }
          }
        }
      } else {
        header("Content-type:text/html;charset=utf-8");
        exit('你没有足够的权限访问该地址！<a href="' . U($preJumpUrl) . '">跳转到可访问页面</a>');
      }
    }
  }
}