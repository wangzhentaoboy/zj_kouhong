// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
        icon:cc.Sprite,
        tname:cc.Label,
        getNum:cc.Label,
        panel:cc.Node,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},
    onEnable(){

    },
    onDisable(){

    },
    
    onClickSell(){
        cc.app.controller.reqPutTrash(this.selectID);
        this.close();
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
    },

    show(data){
        this.selectID = data.selectID;
        this.icon.spriteFrame =  cc.app.loader.getAtlasSpriteFrame(cc.app.mconfig.kouHongPath[data.lv-1].path);
        this.getNum.string = "回收该口红将会获得"+ cc.app.mconfig.formatNumber(cc.app.mconfig.kouHongConfig[data.khid].item_consume*0.1)+"仙气值";
        this.tname.string = cc.app.mconfig.kouHongPath[data.lv-1].name;
        this.node.active = true;
        this.panel.scale = 0.3;
        this.panel.runAction(cc.sequence(cc.scaleTo(0.15,1.1),cc.scaleTo(0.1,1))); 
    },

    close(){
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
        this.node.active = false;
    },
    start () {

    },

    // update (dt) {},
});
