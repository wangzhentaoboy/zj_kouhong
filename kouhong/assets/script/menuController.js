// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       kouHongFigure:cc.Node,
       rewardPanel:cc.Node,
       levelUpPanel:cc.Node,
       exchangePanel:cc.Node,
       tutorPanel:cc.Node,
       bagPanel:cc.Node,
       payPanel:cc.Node,
       friendHelp:cc.Node,
       howTo:cc.Node,
       randPanel:cc.Node,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {
       this.kouHongFigureClass = this.kouHongFigure.getComponent('figurePanel');
       this.rewardPanelClass = this.rewardPanel.getComponent('dialogReward');
       this.levelUpPanelClass = this.levelUpPanel.getComponent('dialogLevelUp');
       this.exchangePanelClass = this.exchangePanel.getComponent('dialogExchange');
       this.tutorPanelClass = this.tutorPanel.getComponent('tutorPanel');
       this.bagPanelClass = this.bagPanel.getComponent('dialogBag');
       this.payPanelClass = this.payPanel.getComponent('payDialog');
       this.friendHelpClass = this.friendHelp.getComponent('friendHelp');
       this.howToClass = this.howTo.getComponent('howTo');
       this.randList = this.randPanel.getComponent('randList');
       
       if(!cc.sys.localStorage.getItem("hasKey")){     
        this.tutorPanelClass.show();
        cc.sys.localStorage.setItem("hasKey",true); 
       }
    },

    onEnable(){
        cc.systemEvent.on(cc.SystemEvent.EventType.KEY_UP, this.onKeyUp, this);
        cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_SHOW_LEVEL_UP_PANEL,this.onShowLevelUp,this);
        cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_SHOW_REWARD_PANEL,this.onShowReward,this);
        cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_ON_SHOW_FUSION37_38,this.onShowFusion37,this);
        
    },

    onDisable(){
        cc.systemEvent.off(cc.SystemEvent.EventType.KEY_UP, this.onKeyUp);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_SHOW_LEVEL_UP_PANEL,this.onShowLevelUp);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_SHOW_REWARD_PANEL,this.onShowReward);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_ON_SHOW_FUSION37_38,this.onShowFusion37);
    },

    onKeyUp(event){
        switch(event.keyCode) {
            case cc.macro.back:
                cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_KEY_BACK);
                break;
        }
    },
    onClickPlayFullAdd(){
        //全屏视频
        
    },

    onShowFusion37(data){
       this.randList.show(data);
    },
    onShowLevelUp(data){
       this.levelUpPanelClass.show(data);
    },

    onShowReward(data){
        this.rewardPanelClass.show(data);
    },

    onClickBuy(){
        cc.app.controller.reqBuy();
       
    },

    onClickChuangGuan(){
        console.log("闯关");
       // location= "http://test.treemay.com/app/./index.php?i=2&c=entry&eid=25";
        cc.app.network.close();
    },

    onClickXinYuanLiHe(){
        console.log("心愿礼盒");
        //location = "http://test.treemay.com/app/./index.php?i=2&c=entry&eid=51";
        cc.app.network.close();
    },

    onClickKouHongNvShen(){
        
    },
    onClickMineInfo(){
        console.log("我的信息");
       // location = "http://test.treemay.com/app/./index.php?i=2&c=entry&eid=26";
        cc.app.network.close();
    },

    onClickKouHong(){
        this.kouHongFigureClass.show();
    },

    onClickBaoBao(){
        this.bagPanelClass.show();
    },

    onClickFriendHelp(){
        this.friendHelpClass.show();
    },

    onClickCash(){
        this.payPanelClass.show();
    },

    onClickTutor(){
        this.howToClass.show();
    },

    onClickKouHongJingLing(){
        this.exchangePanelClass.show();
    },



    // update (dt) {},
});
