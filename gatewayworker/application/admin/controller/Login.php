<?php
/**
 * Created by PhpStorm.
 * User: ljf
 * Date: 2018/1/8
 * Time: 23:43
 */

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Gateway;
use think\Request;
use think\Session;

class Login extends Controller
{


    public function login()
    {

        return $this->fetch();


    }

    /*
     *登陆的时候获取一个session_id 存到数据库
     * */
    public
    function insert_sessionid($adminid = 1)
    {

        M("admin")->where("id=$adminid")->save(array("session_id" => session_id()));
    }

    /*
     * 登录
     * @param  account
     * @param pwd
     * */
    public
    function check($unionid = "")
    {


        $account = $_POST['account'];
        $account = $this->rsa_decode($account);
        $pwd = $_POST['pwd'];
        $password = $this->rsa_decode($pwd);
        $map = array(
            'del' => 0,
            'username' => $account
        );
        $arr = DB::name('game_admin')->where($map)->find();
        $relPwd = $arr['password'];
        $salt = $arr['salt'];
        $md5pwd = md5($password . $salt);

        if ($relPwd == $md5pwd) {

            cookie('adminId', $arr['id']);
            Session::set("adminId", $arr['id']);
            Session::set("adminAccount", $arr['username']);
            Session::set("userId", $arr['uid']);
            $data["code"] = 200;
            $data["msg"] = "登录成功";
            echo json_encode($data);
            exit;
        } else {
            $data["code"] = 201;
            $data["msg"] = "登录失败";
            echo json_encode($data);
            exit;
        }
    }

    /*
     * 存入登陆日志
     * */
    public
    function add_login_log($uid = "1")
    {


        $log['uid'] = $uid;
//        $log['login_ip'] = $this->ip();
        $log['login_ip'] = $ip = get_client_ip();
        $Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
        $area = $Ip->getlocation($ip)["country"]; // 获取某个IP地址所在的位置
//        $ip_info = $this->getCity($log['login_ip']);

//        if ($ip_info['country'] != '中国') {
//            $log['login_location'] = '未知地址';
//        } else {
//            $location['country'] = $ip_info['country'];
//            $location['region'] = $ip_info['region'];
//            $location['city'] = $ip_info['city'];
//            $log['login_location'] = implode(" ", $location);
//        }
        $log['login_location'] = $area;
        if ($log['login_location'] == '') {
            $log['login_location'] = '未知地址';
        }

        $log['login_browser'] = $this->getBroswer();
        $log['login_os'] = $this->getOs();
        $log['login_time'] = time();
        M("admin_login")->add($log);
    }

    function rsa_decode($data)
    {
        //读取私钥文件
        //dirname(dirname(__FILE__))  是获取本文件绝对路径的上一层目录
        $private_key = file_get_contents(dirname(dirname(__FILE__)) . '/keys/rsa_private_key.pem');
        openssl_private_decrypt(
            base64_decode($data),
            $decode_result,
            $private_key
        );
        return $decode_result;
    }

    /*
    * 退出登录
    * */
    public
    function quit()
    {
        Session::set("adminId",NULL);
        Session::set("adminAccount", NULL);
        Session::set("userId", NULL);
        $this->success("退出登录成功",  '/index.php/admin/Login/login.html');
        exit;
    }

    /*
     * 扫码登陆
     * */
    public
    function scanLofin()
    {


        $redirect_uri = "http://wxsmj.wanjiejixie.com/admin.php/Login/codeinfo";
        $redirect_uri = urlencode($redirect_uri);//该回调需要url编码
        $appID = C("Loginappid");
        $scope = "snsapi_login";//写死，微信暂时只支持这个值
//准备向微信发请求
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid=" . $appID . "&redirect_uri=" . $redirect_uri
            . "&response_type=code&scope=" . $scope . "&state=STATE#wechat_redirect";
//请求返回的结果(实际上是个html的字符串)
        // header($url);
        header("Location:" . $url);
//        $result = file_get_contents($url);
//
//
////替换图片的src才能显示二维码
//        $result = str_replace("/connect/qrcode/", "https://open.weixin.qq.com/connect/qrcode/", $result);
        // echo $result; //返回页面
    }

//回调
    public
    function codeinfo()
    {
        if ($code = $_GET["code"]) {
            $code = $_GET["code"];
            $appid = C("Loginappid");
            $secret = C("loginappSecret");
            if (!empty($code))  //有code
            {
                //通过code获得 access_token + openid
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid
                    . "&secret=" . $secret . "&code=" . $code . "&grant_type=authorization_code";
                $jsonResult = file_get_contents($url);
                $resultArray = json_decode($jsonResult, true);
                $access_token = $resultArray["access_token"];
                $openid = $resultArray["openid"];
                //通过access_token + openid 获得用户所有信息,结果全部存储在$infoArray里,后面再写自己的代码逻辑
                $infoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid;
                $infoResult = file_get_contents($infoUrl);
                $infoArray = json_decode($infoResult, true);
                $unionid = $infoArray["unionid"];
                $this->check($unionid);

            }
        }

    }

    public
    function ip($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    public
    function getCity($ip = '')
    {
        if ($ip == '') {
            $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
            $ip = json_decode(file_get_contents($url), true);
            $data = $ip;
        } else {
            $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
            $ip = json_decode(file_get_contents($url));
            if ((string)$ip->code == '1') {
                return false;
            }
            $data = (array)$ip->data;
        }
        return $data;
    }

    public
    function getBroswer()
    {
        $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
        if (stripos($sys, "Firefox/") > 0) {
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp[0] = "Firefox";
            $exp[1] = $b[1];  //获取火狐浏览器的版本号
        } elseif (stripos($sys, "Maxthon") > 0) {
            preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
            $exp[0] = "傲游";
            $exp[1] = $aoyou[1];
        } elseif (stripos($sys, "MSIE") > 0) {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp[0] = "IE";
            $exp[1] = $ie[1];  //获取IE的版本号
        } elseif (stripos($sys, "OPR") > 0) {
            preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
            $exp[0] = "Opera";
            $exp[1] = $opera[1];
        } elseif (stripos($sys, "Edge") > 0) {
            //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
            preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
            $exp[0] = "Edge";
            $exp[1] = $Edge[1];
        } elseif (stripos($sys, "Chrome") > 0) {
            preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
            $exp[0] = "Chrome";
            $exp[1] = $google[1];  //获取google chrome的版本号
        } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
            preg_match("/rv:([\d\.]+)/", $sys, $IE);
            $exp[0] = "IE";
            $exp[1] = $IE[1];
        } elseif (stripos($sys, 'Safari') > 0) {
            preg_match("/safari\/([^\s]+)/i", $sys, $safari);
            $exp[0] = "Safari";
            $exp[1] = $safari[1];
        } else {
            $exp[0] = "未知浏览器";
            $exp[1] = "";
        }
        return $exp[0] . '(' . $exp[1] . ')';
    }

    /**
     * 获取客户端操作系统信息包括win10
     * @param  null
     * @author  Jea杨
     * @return string
     */
    public
    function getOs()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
            $os = 'Windows 95';
        } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
            $os = 'Windows ME';
        } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
            $os = 'Windows 98';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
            $os = 'Windows Vista';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
            $os = 'Windows 7';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
            $os = 'Windows 8';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
            $os = 'Windows 10';#添加win10判断
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
            $os = 'Windows XP';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
            $os = 'Windows 2000';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
            $os = 'Windows NT';
        } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
            $os = 'Windows 32';
        } else if (preg_match('/linux/i', $agent)) {
            $os = 'Linux';
        } else if (preg_match('/unix/i', $agent)) {
            $os = 'Unix';
        } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'SunOS';
        } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'IBM OS/2';
        } else if (preg_match('/Mac/i', $agent)) {
            $os = 'Mac';
        } else if (preg_match('/PowerPC/i', $agent)) {
            $os = 'PowerPC';
        } else if (preg_match('/AIX/i', $agent)) {
            $os = 'AIX';
        } else if (preg_match('/HPUX/i', $agent)) {
            $os = 'HPUX';
        } else if (preg_match('/NetBSD/i', $agent)) {
            $os = 'NetBSD';
        } else if (preg_match('/BSD/i', $agent)) {
            $os = 'BSD';
        } else if (preg_match('/OSF1/i', $agent)) {
            $os = 'OSF1';
        } else if (preg_match('/IRIX/i', $agent)) {
            $os = 'IRIX';
        } else if (preg_match('/FreeBSD/i', $agent)) {
            $os = 'FreeBSD';
        } else if (preg_match('/teleport/i', $agent)) {
            $os = 'teleport';
        } else if (preg_match('/flashget/i', $agent)) {
            $os = 'flashget';
        } else if (preg_match('/webzip/i', $agent)) {
            $os = 'webzip';
        } else if (preg_match('/offline/i', $agent)) {
            $os = 'offline';
        } elseif (preg_match('/ucweb|MQQBrowser|J2ME|IUC|3GW100|LG-MMS|i60|Motorola|MAUI|m9|ME860|maui|C8500|gt|k-touch|X8|htc|GT-S5660|UNTRUSTED|SCH|tianyu|lenovo|SAMSUNG/i', $agent)) {
            $os = 'mobile';
        } else {
            $os = '未知操作系统';
        }
        return $os;
    }


}



