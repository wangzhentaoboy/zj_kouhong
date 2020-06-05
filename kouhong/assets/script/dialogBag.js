// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
        itemPrefab:cc.Prefab,
        content:cc.Node,
        itemNum:0,
        selectID:-1,
    },

    addItem(kid,num){
       if(num<=0) return;
        let item = cc.instantiate(this.itemPrefab);
        this.content.addChild(item);
        item.position = cc.v2(0,this.itemNum*item.height);
        item.getComponent("bagItem").init(kid,num);
        cc.log("item.position "+item.position);
        this.itemNum--;
    },
    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},
    initByData(){
        this.itemNum = 0;
        this.selectID = -1;
        let data = cc.app.mconfig.getBagInfo();
        for(let key  in data){
              cc.log("key is "+key + "value is "+data[key]);
              this.addItem(key,data[key]);
        }
    },
    onEnable () {
        cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_ON_SELECT_BAGITEM,this.onSelectItem,this);
    },

    onDisable(){
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_ON_SELECT_BAGITEM,this.onSelectItem);
    },

    show(){
        this.node.active = true;
        this.initByData();
    },

    onSelectItem(data){
        cc.log("key is  onSelectItem is "+data);
        this.selectID = data;
    },

    onClickConfirm(){
        cc.log("key is  onClickConfirm is "+this.selectID);
        if( this.selectID>=0){
            cc.app.controller.reqGetBag(this.selectID);
            this.close();
        }
    },

    close(){
        this.content.removeAllChildren();
        this.node.active = false;
    },

    // update (dt) {},
});
