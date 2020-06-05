// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       panel:cc.Node,
       totalNum:cc.Label,
       selectNum:cc.Label,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {

    },

    show(){
        this.node.active = true;
        this.panel.scale = 0.3;
        this.panel.runAction(cc.sequence(cc.scaleTo(0.1,1.1),cc.scaleTo(0.1,1)));
        this.jingLingNum = cc.app.mconfig.getJingLingNum();
        this.totalNum.string = "一共"+this.jingLingNum+"个";
        this.select = 0;
        this.selectNum.string = this.select;
     },
 
     onClickClose(){
        this.panel.runAction(cc.sequence(cc.scaleTo(0.1,0),cc.callFunc(function(){
            this.node.active= false;
        }.bind(this))));  
     },

     onClickAdd(){
         if(this.select<this.jingLingNum){
             this.select++;
         }else{
             this.select=0;
         }
         this.selectNum.string = this.select;
     },

     onClickMin(){
        if(this.select>1){
            this.select-=1;
        }else{
            this.select=this.jingLingNum;
        }
        this.selectNum.string = this.select;
     },

     onClickExchange(){
        let that = this;
        let uid = cc.sys.localStorage.getItem("uid");
        let url  = "http://game.treemay.com/index/api/change?userid="+uid+"&khsprite="+this.select;
        cc.log("exchange url is  "+url);
        cc.app.http.openUrl(url,"GET",function(res){
           
            let data = JSON.parse(res);
            cc.log("data is onClickExchange "+JSON.stringify(data));
            that.onClickClose();
      });
        
     },


    // update (dt) {},
});
