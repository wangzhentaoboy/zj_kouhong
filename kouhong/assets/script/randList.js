// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
        items:{
            default:[],
            type:cc.Node,
        },
        panel:cc.Node,
        accelerate:0.01,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},
    onEnable(){
         this.itemBgs=[];
         this.lvList = [38,39,40,41,42,43,44,45];
         this.state = 0;
         this.index = 0;
         let that = this;
         this.items.forEach(element => {
             that.itemBgs.push(element.getChildByName("bg"));
         });
    },

    start () {

    },

    show(target){
        console.log('................target.............'+target);
        this.node.active = true;
        this.panel.scale = 0.3;
        this.targetIdex = target+8;
        this.panel.runAction(cc.sequence(cc.scaleTo(0.15,1.1),cc.scaleTo(0.1,1))); 
    },
    
    hide(){
        this.node.active = false;
    },

    lightItem(index){
        this.itemBgs[index].opacity = 255;
        this.itemBgs[index].stopAllActions();
        this.itemBgs[index].runAction(cc.fadeOut(0.5));
    },

    onClickStart(){
        this.state = 1;
        this.index =0;
    },

    onClickClose(){
        this.hide();
    },

    update (dt) {
        if(this.state==1){
             //zhuan
            this.index+=this.accelerate;
            this.lightItem(Math.floor(this.index)%8);
            if(this.accelerate<=0.3){
                this.accelerate+=0.01; 
            }

            if(Math.floor(this.index)/7>2){
                this.state = 2;
                this.index = 0;
            }
            
        }else if(this.state==2){
            this.index+=this.accelerate;
            if(this.accelerate>0.0){
                this.accelerate-=0.09/(this.targetIdex*2); 
            }else{
                this.accelerate = 0;
                this.state = 3; 
                this.hide();
                cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_SHOW_LEVEL_UP_PANEL,{lv:this.lvList[this.targetIdex-8],offLevel:1,kid:0});
            }
           
            this.lightItem(Math.floor(this.index)%8);
        }
    },
});
