// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    onEnable(){
      cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_KEY_BACK,this.close,this);
      cc.app.mconfig.setIsMainPage(false);
    },
    onDisable(){
      cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_KEY_BACK);
    },
    start () {
     
    },

    show(){
       this.node.active = true;
    },

    close(){
       this.node.active = false;
       cc.app.mconfig.setIsMainPage(true);
    },

    // update (dt) {},
});
