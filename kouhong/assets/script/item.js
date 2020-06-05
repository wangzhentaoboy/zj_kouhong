// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       lv:1,
       value:0,
       sleepFlag:true,
       genNum:cc.Label,
    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {
        this.node.active = false;
        this.opos = this.node.position;
    },

    start () {
        this.numPos = this.genNum.node.position;
        this.handle = 0;
    },

    set(khid,calTime){
     
        this.khid = khid;
        this.lv = cc.app.mconfig.kouHongConfig[this.khid].item_lvl;
        this.bornTime = calTime;
        this.item_generate = cc.app.mconfig.kouHongConfig[this.khid].item_generate;
        this.item_consume  = cc.app.mconfig.kouHongConfig[this.khid].item_consume;
        let spriteFrame =  cc.app.loader.getAtlasSpriteFrame(cc.app.mconfig.kouHongPath[this.khid-6].path);
        this.getComponent(cc.Sprite).spriteFrame =spriteFrame;
    },

    floatValue(){
        this.genNum.node.active = true;
        this.genNum.node.position = this.numPos;
        this.genNum.node.opacity = 255;
        this.genNum.string = "+"+cc.app.mconfig.formatNumber(this.item_generate*4);
        let that = this;
        this.genNum.node.stopAllActions();
     
        let time = parseInt(new Date().getTime()/1000);
       
        cc.app.mconfig.addXianQiZhi(this.item_generate*(time-this.bornTime));
        this.bornTime = time;
        this.genNum.node.runAction(cc.sequence(cc.spawn(cc.moveBy(0.6,cc.Vec2(0,150)),cc.fadeOut(0.6)),cc.callFunc(function(){that.genNum.node.active = false;})));
    },

    resetValue(){
        this.genNum.node.active = false;
        this.genNum.node.opacity = 255;
      //  this.node.scale = 0.0;
        this.khid = -1;
    },
    setPosition(pos){
        this.opos = pos;
        this.node.position = pos;
    },

    follow(offx,offy){
        this.node.x+=offx;
        this.node.y+=offy;
    },

    levelUp(){
      
       // this.getComponent(cc.Sprite).spriteFrame = cc.app.loader.getSpriteFrame(cc.app.mconfig.kouHongConfig[this.lv].path);
    },

    goBack(){
        this.node.runAction(cc.moveTo(0.15,this.opos));
    },

    goTo(pos){
        this.opos = pos;
        this.node.runAction(cc.moveTo(0.15,pos));
    },
    levelEqual(lv){
        return this.lv==lv;
    },
    sleep(){
        this.sleepFlag = true;
        this.resetValue();
    },
    wakeUp(){
        this.sleepFlag = false;
    },

    isSleep(){
        return this.sleepFlag;
    },

    appear(){
        this.node.active = true;
        // this.node.stopAllActions();
        // this.node.runAction(cc.sequence(cc.scaleTo(0.2,1.1),cc.scaleTo(0.1,1.0)));
        this.unscheduleAllCallbacks();
        this.schedule(this.floatValue.bind(this),4);
    },
    disappear(){
        this.sleep();
        this.node.active = false;
        this.unscheduleAllCallbacks();
    },
 
});
