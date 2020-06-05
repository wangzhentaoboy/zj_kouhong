// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       xian_qi_zhi:cc.Label,
       add_num:cc.RichText,
       num_sprite:cc.Label,
       buy_cost:cc.Label,
       buy_icon:cc.Sprite,
       buy_level:cc.Label,
       share_money:cc.Label,
       nvshen:cc.Node,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},
    onEnable(){

       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_ADD_XIANQI,this.onAddReward,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,this.onUpdateUserInfo,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_ON_SHOW_GIRL,this.onShowGirl,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_SOCKET_CLOSE,this.onLostConnect,this);

       cc.app.controller.startPumpTimer(this.node);
       cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
       cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
       this.schedule(this.onUpdateUserInfo.bind(this),4,this);
       if(cc.app.mconfig.getKHGirl()){
           cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_ON_SHOW_GIRL,cc.app.mconfig.getKHGirl());
       }
       
       //程序进入后台运行
       //jsBridge.onAppEnterBackground(this.onBackGround);
       //jsBridge.onAppEnterForeground(this.onForeGround);
    },

    onDisable(){
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_ADD_XIANQI);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_UPDATE_USERINFO);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_ON_SHOW_GIRL,this.onShowGirl);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_SOCKET_CLOSE,this.onLostConnect);
        this.unscheduleAllCallbacks();
    },

    onBackGround(){
        cc.app.mconfig.lastTime = new Date().getTime();
    },

    onForeGround(){
        if(new Date().getTime()-cc.app.mconfig.lastTime>19999){
            this.onLostConnect();
        }
    },
    
    onLostConnect(){
        cc.app.network.close();
        cc.app.controller.stopPumpTimer();
        cc.director.loadScene("loading");   
        cc.app.mconfig.lastTime = new Date().getTime();
    },
  
    start () {
        this.xian_qi_zhi.string = cc.app.mconfig.getXianQiZhi();
        this.num_sprite.string = "x"+cc.app.mconfig.getJingLingNum();
    },

    onShowGirl(time){
        if(this.nvshen.active) return;
        this.nvshen.active = true;
        this.nvshen.scale = 0.2;
        this.timeslut = time;
        this.nvshen.stopAllActions();
        this.nvshen.runAction(cc.sequence(cc.scaleTo(0.2,1.1),cc.scaleTo(0.1,1.0)));
        this.nvshen.getChildByName("txt").getComponent(cc.Label).string = this.formatTime(600-(new Date().getTime()/1000-time));
        this.schedule(this.onUpdateNvShen.bind(this),0.9);
    },

    onHideGirl(){
        this.nvshen.stopAllActions();
        this.nvshen.runAction(cc.sequence(cc.scaleTo(0.1,1.1),cc.scaleTo(0.1,0.0),cc.callFunc(function(){
            this.nvshen.active = true;
        }.bind(this))));
       
        this.unscheduleAllCallbacks();
        this.schedule(this.onUpdateUserInfo.bind(this),4);
    },

    onUpdateNvShen(){
        let off = 600-(new Date().getTime()/1000-this.timeslut);
        let that = this;
        let uid = cc.sys.localStorage.getItem("uid");
        cc.app.http.openUrl("http://game.treemay.com/index/api/userCount?userid="+uid,"GET",function(res){
            cc.log("data is onEnableopenPostUrl "+res);
            let data = JSON.parse(res);
            if(data.code==200){

                that.nvshen.getChildByName("money").getChildByName("num").getComponent(cc.Label).string = data.data.goddess
            }
        });
        this.nvshen.getChildByName("money").getChildByName("num").stopAllActions();
        this.nvshen.getChildByName("money").getChildByName("num").runAction(cc.sequence(cc.scaleTo(0.2,1.5),cc.scaleTo(0.1,1.0)));
        this.nvshen.getChildByName("txt").getComponent(cc.Label).string = "限时体验分红女神 \n"+this.formatTime(off);
        if(off<=0){
           this.onHideGirl();
        }
    },
    
    formatTime(time){
        return Math.floor(time/60)+":"+Math.floor(time%60);
    },
    onUpdateXianQiZhi(){
        this.xian_qi_zhi.string = cc.app.mconfig.getXianQiZhi();
    },
    onUpdateUserInfo(){
        if(cc.app.network._sock==null) this.onLostConnect();
        if(!this.xian_qi_zhi) return;
        this.xian_qi_zhi.string = cc.app.mconfig.getXianQiZhi();
        this.num_sprite.string = "x"+cc.app.mconfig.getJingLingNum();
        this.buy_cost.string = cc.app.mconfig.getCostByLv();
        let buylv = cc.app.mconfig.kouHongPath[Math.max(0,cc.app.mconfig.getKhMaxLv()-1)].buyLv;
     
        this.buy_icon.spriteFrame = cc.app.loader.getAtlasSpriteFrame(cc.app.mconfig.kouHongPath[buylv-1].path);
        this.buy_level.string = "LV"+buylv;
        let uid = cc.sys.localStorage.getItem("uid");
        let that = this;
        this.share_money.string = cc.app.mconfig.getShareMoney();

        cc.app.http.openUrl("http://game.treemay.com/index/api/getUserInfo?uid="+uid,"GET",function(res){
            console.log("增加仙气值了吗:"+res);
             let data = JSON.parse(res);
             cc.app.mconfig.setXianQiZhi(data.cmd_value.data.userinfo.khxianqi);
             cc.app.mconfig.setJingLingNum(data.cmd_value.data.userinfo.khsprite);
             cc.app.mconfig.setKhMaxLv(data.cmd_value.data.userinfo.khmaxlvl);
             cc.app.mconfig.setIskhGirl10(data.cmd_value.data.userinfo.iskhgirl10);
             cc.app.mconfig.setIskhGirl20(data.cmd_value.data.userinfo.iskhgirl20);
             cc.app.mconfig.setIsOffLine(data.cmd_value.data.offline);
             cc.app.mconfig.setKouHongInfo(data.cmd_value.data.userinfo.khinfo);
             cc.app.mconfig.setCostByLv(data.cmd_value.data.userinfo.khmaxlvl);
             cc.app.mconfig.setLeftVideoTimes(data.cmd_value.data.leftTimes);
             cc.app.mconfig.setVideoTimes(data.cmd_value.data.videoTimes);
             that.add_num.string ="<color=#000000>还差观看 </c><color=#ff0000>"+Math.max(0,20-cc.app.mconfig.getVideoTimes())+"</color><color=#000000>次视频就可以获得</c>";
             
       });
    },
    onAddReward(reward_amount=0){
        let uid = cc.sys.localStorage.getItem("uid");
        let time=new Date().getTime();
        cc.app.http.getUrl("http://game.treemay.com/index/api/reward?user_id="+uid+"&trans_id="+time+"&reward_amount="+reward_amount+"&extra=ttVideo&reward_name=xq","GET",function(res){
            let data=JSON.parse(res);
            if(data.isValid){
                console.log("增加仙气值成功");
                cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
            }
            else{
                console.log("增加仙气值失败");
            }
       });
    }
});
