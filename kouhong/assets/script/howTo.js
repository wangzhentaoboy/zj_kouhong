// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
        // foo: {
        //     // ATTRIBUTES:
        //     default: null,        // The default value will be used only when the component attaching
        //                           // to a node for the first time
        //     type: cc.SpriteFrame, // optional, default is typeof default
        //     serializable: true,   // optional, default is true
        // },
        // bar: {
        //     get () {
        //         return this._bar;
        //     },
        //     set (value) {
        //         this._bar = value;
        //     }
        // },
    },

    onEnable(){
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_KEY_BACK,this.close,this);
    },
    onDisable(){
       cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_KEY_BACK);
    },
    start () {
        cc.app.mconfig.setIsMainPage(false);
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
