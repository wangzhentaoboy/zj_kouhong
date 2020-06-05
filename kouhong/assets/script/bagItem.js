// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,
    properties: {
       lv:cc.Label,
       num:cc.Label,
       icon:cc.Sprite,
       bg   :cc.Sprite,
       itemname:cc.Label,
    },

    // LIFE-CYCLE CALLBACKS:
    onLoad(){
        this.node.on(cc.Node.EventType.TOUCH_START,this.onTouchStart,this);
        this.node.on(cc.Node.EventType.TOUCH_MOVE,this.onTouchMove,this);
        this.node.on(cc.Node.EventType.TOUCH_END,this.onTouchEnded,this);
        cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_ON_SELECT_BAGITEM,this.onSelectItem,this);
        if (this.node._touchListener) {
           this.node._touchListener.setSwallowTouches(false);
        }
    },

    init(kid,num){
        this.lvv = cc.app.mconfig.kouHongConfig[kid].item_lvl;
        this.icon.spriteFrame = cc.app.loader.getAtlasSpriteFrame(cc.app.mconfig.kouHongPath[this.lvv-1].path);
        this.lv.string = this.lvv;
        this.kid = kid;
        this.itemname.string = cc.app.mconfig.kouHongPath[this.lvv-1].name;;
        this.num.string = num+"支";
    },

    onSelectItem(data){
        console.log('..................data:'+data);
        if(this.kid==data){
            this.onShowBlink(true);
        }else{
            this.onShowBlink(false); 
        }
    },

    onShowBlink(bool){
        this.bg.node.active = bool;
        this.bg.node.stopAllActions();
        this.bg.node.runAction(cc.repeatForever(cc.sequence(cc.fadeOut(0.5),cc.fadeIn(0.5))));
    },

    onTouchStart(event){
        console.log('..................onTouchEndedsss:'+this.kid);
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_ON_SELECT_BAGITEM,this.kid);
    },
 
    onTouchMove(event){
        
       
    },
 
    onTouchEnded(event){
       
       
    },
});
