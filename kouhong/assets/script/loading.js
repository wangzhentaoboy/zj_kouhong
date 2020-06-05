// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html
import loader from "loader";
import dispatcher from "dispatcher";
import config from "config";
import network from "network";
import cmdEnum from "cmdEnum";
import httpRequest from "httpRequest";
import RAController from "RAController";
cc.Class({
    extends: cc.Component,

    properties: {
       lb:cc.Label,
       percent:0,
       promstr:'加载中..',
       progress:cc.ProgressBar,
    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {
        cc.app={};
        cc.app.loader = loader;
        cc.app.dispatcher = dispatcher;
        cc.app.mconfig = config;
        cc.app.network = network;
        cc.app.Cmd = cmdEnum;
        cc.app.http = httpRequest;
        cc.app.controller = RAController;
    },
    onEnable(){
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_LOAD_PROGRESS,this.onLoading,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_SOCKET_OPEN,this.onConnect,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_SOCKET_CLOSE,this.onError,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_LOGIN_FAILED,this.onLoginFailed,this);
    },
    onDisable(){
      cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_LOAD_PROGRESS,this.onLoading);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_SOCKET_OPEN,this.onConnect);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_SOCKET_CLOSE,this.onError);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_LOGIN_FAILED,this.onLoginFailed);
   },

    initAdd(){
       let that=this;
      //抖音登录
      tt.login({
         success (res) {
             console.log(`login调用成功${res.isLogin} ${res.code} ${res.anonymousCode}`);
             let code=res.isLogin?res.code:res.anonymousCode;
             let nickName="";
             let avatarUrl="";
             if(res.isLogin){
               tt.getSetting(
                  {
                     success(res){
                        console.log("获取授权信息成功："+JSON.stringify(res)+"|"+res.authSetting["scope.userInfo"]);
                        if(!res.authSetting["scope.userInfo"]){
                           tt.openSetting({
                              success(){

                              },
                              fail(){

                              }
                           })
                        }
                     },
                     fail(res){
                        console.log("获取授权信息失败："+JSON.stringify(res));
                     }
                  }
               )
               tt.authorize({
                  scope: "scope.userInfo",
                  success() {
                     console.log("用户授权成功");
                     tt.getUserInfo({
                        success(res){
                           let userInfo=res.userInfo;
                           nickName=userInfo.nickName;
                           avatarUrl=userInfo.avatarUrl;
                           console.log("用户信息:"+JSON.stringify(res));
                           that.authLogin(code,nickName,avatarUrl);
                        },
                        fail(res){
                           console.log(`getUserInfo 调用失败`);
                           that.authLogin(code,nickName,avatarUrl);
                        }
                     });
                  },
                  fail(){
                     console.log("用户授权失败");
                     that.authLogin(code,nickName,avatarUrl);
                  }
                });
             }
             else{
               that.authLogin(code,nickName,avatarUrl);
             }
             
         },
         fail (res) {
            that.lb.string = "登录失败！";
         }
     });
      
    },

    authLogin(code,nickName,avatarUrl){
      cc.app.http.getUrl("http://game.treemay.com/index/api/authorizeLogin?code="+code+"&type=tt&nickName="+nickName+"&avatarUrl="+avatarUrl,"GET",function(res){
            console.log("authorizeLogin data is:"+res);
            let data = JSON.parse(res);
            if(data.code==200){
               cc.sys.localStorage.setItem("uid",data.data.id);
               cc.sys.localStorage.setItem("openid",data.data.openid);
               cc.app.controller.init();
               cc.app.network.connect();
            }
      });
    },

    onConnect(){
      this.lb.string = "服务器连接成功!";  
      let uid=cc.sys.localStorage.getItem("uid");
      let openid=cc.sys.localStorage.getItem("openid");
      cc.app.mconfig.lastTime = new Date().getTime();
      cc.app.controller.reqLogin(uid,openid);
    },

    onError(data){
      this.lb.string = "服务器连接失败!"+data; 
    },

    onLoginFailed(){
      this.lb.string = "登录失败，账号或密码错误!" 
      cc.app.controller.stopPumpTimer();
    },

    start () {
       this.initAdd();
       this.lb.string = "正在连接游戏服务器...";
       this.progress.progress = 0;
       console.log("start 正在连接游戏服务器");
    },

    onLoading(data){
       this.lb.string = this.promstr+Math.floor(data.cur*100/data.total)+"%";
       this.progress.progress = data.cur/data.total;
    },
});
