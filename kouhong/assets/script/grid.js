// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       txt_lv:cc.Label,
       dot:cc.Sprite,
       itemObj:null,
    },

    // LIFE-CYCLE CALLBACKS:

    onLoad () {
        this.txt_lv.string = '';
        this.dot.node.active = false;
    },

    start () {
      
    },

    getLv(){
        return this.itemObj.lv;
    },
    
    updateInfo(){
        this.txt_lv.string = this.itemObj.lv;
        this.dot.node.active = true;
    }, 

    lvUp(){
        this.itemObj.levelUp();
        this.txt_lv.string = this.itemObj.lv+1;

    },
    put(obj){
        this.itemObj = obj;
        this.itemObj.setPosition(this.node.position);
        this.itemObj.appear();
        this.txt_lv.string = obj.lv;
        this.dot.node.active = true;
    },
     
    empty(){
      
        this.itemObj = null;
        this.txt_lv.string = '';
        this.dot.node.active = false;
    },

    isEmpty(){
        return this.itemObj==null;
    },

    // update (dt) {},
});
