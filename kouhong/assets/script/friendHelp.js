// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       firendNum:cc.Label,
       totalSalary:cc.Label,
       salaryToday:cc.Label,
       invSalary:cc.Label,
       otherSalary:cc.Label,
       tipInfo:cc.Node,
       head:cc.Node,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {
        cc.app.mconfig.setIsMainPage(false);
    },

    onEnable(){
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_KEY_BACK,this.close,this);
       cc.app.mconfig.setIsMainPage(false);
       let uid = cc.sys.localStorage.getItem("uid");
       let that = this;
       cc.app.http.openUrl("http://game.treemay.com/index/api/all_data?user_id="+uid,"GET",function(res){
            let data = JSON.parse(res);
            cc.app.mconfig.setAllBonus(data.my.all);
            cc.app.mconfig.setAllTodayBonus(data.my.today_all);
            cc.app.mconfig.setTodayBonus(data.my.today_zhijie);
            cc.app.mconfig.setTodayIndirect(data.my.today_jianjie);
            that.firendNum.string = data.my.num;
            that.totalSalary.string = data.my.all;

            that.salaryToday.string = data.my.today_all,
            that.invSalary.string = data.my.today_zhijie,
            that.otherSalary.string = data.my.today_jianjie
            
            that.tipInfo.getChildByName("txt2").getComponent(cc.Label).string = data.agent.agentnum;
            that.tipInfo.getChildByName("txt4").getComponent(cc.Label).string = data.agent.all+"元";

            var remoteUrl = "http://unknown.org/someres.png";
            cc.loader.load(remoteUrl, function (err, texture) {
                that.head.getComponent(cc.Sprite).spriteFrame.setTexture(texture);
            });
      });
    },
    
    onDisable(){
       cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_KEY_BACK);
    },

    show(data){
        this.node.active = true;
    },

    close(){
        this.node.active = false;
        cc.app.mconfig.setIsMainPage(true);
    },

    onClickInvite(){
        console.log("分享到地址");
    },

    onClickFriend(){
        console.log("邀请朋友");
        cc.app.network.close();
    },

    // update (dt) {},
});
