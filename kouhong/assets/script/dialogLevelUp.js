// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
        kouHongNode:cc.Node,
        offLevel:cc.Label,
        bonusToday:cc.Label,
        panel:cc.Node,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {

    },
    
    show(data){
        cc.log("show data is "+JSON.stringify(data));
        this.node.active = true;
        this.panel.scale = 0.3;
        this.panel.runAction(cc.sequence(cc.scaleTo(0.15,1.1),cc.scaleTo(0.1,1))); 
        this.kouHongNode.getComponent(cc.Sprite).spriteFrame = 
        cc.app.loader.getAtlasSpriteFrame(cc.app.mconfig.kouHongPath[data.lv-1].path);
        this.kouHongNode.getChildByName("name").getComponent(cc.Label).string = cc.app.mconfig.kouHongPath[data.lv-1].name;
        this.offLevel.string = data.offLevel;
        this.bonusToday.string = cc.app.mconfig.getShareMoney();
    },
 
     onClickClose(){
        this.panel.runAction(cc.sequence(cc.scaleTo(0.15,0),cc.callFunc(function(){
            this.node.active= false;
        }.bind(this))));  
     },

    // update (dt) {},
});
