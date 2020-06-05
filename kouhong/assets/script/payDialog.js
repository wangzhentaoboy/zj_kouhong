// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
      selectedCashType:0,
    
     btns_A:{
        default:[],
        type:cc.Node,
     },

     btns_B:{
        default:[],
        type:cc.Node,
     },
 
     firstNode:cc.Node,
     normNode:cc.Node,
     kouHongMoney:cc.Node,
     shareMoney:cc.Node,
     infoBgNode:cc.Node,
     payInfoNode:cc.Node,
     tipsTextNode:cc.Node
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},

    start () {
        this.cashType=1;//提现类型1金币，2分红
        this.payType=1;//支付类型，1支付宝
        this.money=0;//提现金额
        this.COLOR_WHITE = new cc.Color(255, 255, 255);
        this.COLOR_BLACK = new cc.Color(0, 0, 0);
        this.showDefualtBtn();
    },

    show(){
        this.node.active = true;
    },
 
    close(){
        this.node.active = false;
        cc.app.mconfig.setIsMainPage(true);
    },

    onClickBack(){
        this.node.active = false;
    },

    onClickRecord(){
        
    },

    onEnable(){
        let uid = cc.sys.localStorage.getItem("uid");
        let that =this;
        cc.app.mconfig.setIsMainPage(false);
        cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_KEY_BACK,this.close,this);
        cc.app.http.openUrl("http://game.treemay.com/index/api/userCount?userid="+uid,"GET",function(res){
            cc.log("data is onEnableopenPostUrl "+res);
            let data = JSON.parse(res);
            if(data.code==200){
                that.setMoneyInfo(data.data.myGamecoin,data.data.goddess,data.data.first_cashOut);
            }
        });
    },

    setMoneyInfo(v1,v2,v3){
        this.kouHongMoney.getChildByName('bg').active = true;
        this.kouHongMoney.getChildByName('num').color = this.COLOR_WHITE;
        this.shareMoney.getChildByName('bg').active = false;
        this.shareMoney.getChildByName('num').color = this.COLOR_BLACK;
        this.kouHongMoney.getChildByName('num').getComponent(cc.Label).string = "¥"+v1 +"(口红币)";
        this.shareMoney.getChildByName('num').getComponent(cc.Label).string = "¥"+v2+"(女神分红)";
        this.money=0.3;
        if(v3>0){
            this.money=20;
            this.firstNode.active=false;
            this.normNode.active=true;
            this.btns_B[0].getChildByName("bg").active=true;
            this.btns_B[0].getChildByName("num").color=this.COLOR_WHITE;
        }
        
    },
    resetAllBtn(money){
        this.money=money;
        this.btns_A[0].getChildByName("bg").active=false;
        this.btns_A[1].getChildByName("bg").active=false;
        this.btns_A[2].getChildByName("bg").active=false;
        this.btns_A[3].getChildByName("bg").active=false;
        this.btns_A[4].getChildByName("bg").active=false;
        this.btns_A[5].getChildByName("bg").active=false;
        
        this.btns_A[0].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_A[1].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_A[2].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_A[3].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_A[4].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_A[5].getChildByName("num").color=this.COLOR_BLACK;

        this.btns_B[0].getChildByName("bg").active=false;
        this.btns_B[1].getChildByName("bg").active=false;
        this.btns_B[2].getChildByName("bg").active=false;
        this.btns_B[3].getChildByName("bg").active=false;
        this.btns_B[4].getChildByName("bg").active=false;
       
        this.btns_B[0].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_B[1].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_B[2].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_B[3].getChildByName("num").color=this.COLOR_BLACK;
        this.btns_B[4].getChildByName("num").color=this.COLOR_BLACK;
       
    },


    showDefualtBtn(){
        this.resetAllBtn();
        this.btns_A[0].getChildByName("bg").active=true;
        this.btns_A[0].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickKouHongMoney(){
        this.cashType=1;
        this.kouHongMoney.getChildByName('bg').active = true;
        this.kouHongMoney.getChildByName('num').color = this.COLOR_WHITE;
        this.shareMoney.getChildByName('bg').active = false;
        this.shareMoney.getChildByName('num').color = this.COLOR_BLACK;
    },

    onClickShareMoney(){
        this.cashType=2;
        this.kouHongMoney.getChildByName('bg').active = false;
        this.kouHongMoney.getChildByName('num').color = this.COLOR_BLACK;
        this.shareMoney.getChildByName('bg').active = true;
        this.shareMoney.getChildByName('num').color = this.COLOR_WHITE;
    },

    onClickCash_03(){
        this.resetAllBtn(0.3);
        this.btns_A[0].getChildByName("bg").active=true;
        this.btns_A[0].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCash_20(){
        this.resetAllBtn(20);
        this.btns_A[1].getChildByName("bg").active=true;
        this.btns_A[1].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCash_50(){
        this.resetAllBtn(50);
        this.btns_A[2].getChildByName("bg").active=true;
        this.btns_A[2].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCash_100(){
        this.resetAllBtn(100);
        this.btns_A[3].getChildByName("bg").active=true;
        this.btns_A[3].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCash_500(){
        this.resetAllBtn(500);
        this.btns_A[4].getChildByName("bg").active=true;
        this.btns_A[4].getChildByName("num").color=this.COLOR_WHITE;
    },
    onClickCash_1000(){
        this.resetAllBtn(1000);
        this.btns_A[5].getChildByName("bg").active=true;
        this.btns_A[5].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCashB_20(){
        this.resetAllBtn(20);
        this.btns_B[0].getChildByName("bg").active=true;
        this.btns_B[0].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCashB_50(){
        this.resetAllBtn(50);
        this.btns_B[1].getChildByName("bg").active=true;
        this.btns_B[1].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCashB_100(){
        this.resetAllBtn(100);
        this.btns_B[2].getChildByName("bg").active=true;
        this.btns_B[2].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCashB_500(){
        this.resetAllBtn(500);
        this.btns_B[3].getChildByName("bg").active=true;
        this.btns_B[3].getChildByName("num").color=this.COLOR_WHITE;
    },
    onClickCashB_1000(){
        this.resetAllBtn(1000);
        this.btns_B[4].getChildByName("bg").active=true;
        this.btns_B[4].getChildByName("num").color=this.COLOR_WHITE;
    },

    onClickCash(){
        this.infoBgNode.active=true;
        this.payInfoNode.active=true;
        let uid = cc.sys.localStorage.getItem("uid");
        let that=this;
        cc.app.http.getUrl("http://game.treemay.com/index/api/getCashInfo?userid="+uid,"GET",function(res){
            cc.log("data is onEnableopenPostUrl "+res);
            let data = JSON.parse(res);
            if(data.code==200){
                if(data.data.qr_zfb)that.payInfoNode.getChildByName("account").getComponent(cc.EditBox).string=data.data.qr_zfb;
                if(data.data.qr_zfb_name)that.payInfoNode.getChildByName("name").getComponent(cc.EditBox).string=data.data.qr_zfb_name;
            }
        });
    },

    confirmCashOut(e){
        let account=this.payInfoNode.getChildByName("account").getComponent(cc.EditBox).string;
        let name=this.payInfoNode.getChildByName("name").getComponent(cc.EditBox).string;
        let uid = cc.sys.localStorage.getItem("uid");
        let that=this;
        let btn_node=e.target;
        btn_node.getChildByName("Background").getChildByName("Label").getComponent(cc.Label).string="处理中……";
        btn_node.getComponent(cc.Button).interactable=false;
        
        cc.app.http.getUrl("http://game.treemay.com/index/api/cashOut?userid="+uid+"&cashType="+this.cashType+"&payType="+this.payType+"&money="+this.money+"&qr_zfb="+account+"&qr_zfb_name="+name,"POST",function(res){
            cc.log("data is onEnableopenPostUrl "+res);
            let data = JSON.parse(res);
            if(data.code){
                
                that.tipsTextNode.getComponent(cc.Label).string=data.msg;
                that.tipsTextNode.runAction(cc.sequence(cc.fadeIn(0.5),cc.delayTime(2),cc.fadeOut(1),cc.callFunc(()=>{
                    if(data.code==200){
                        if(that.money==0.3){
                            that.money=20;
                            that.firstNode.active=false;
                            that.normNode.active=true;
                            that.btns_B[0].getChildByName("bg").active=true;
                            that.btns_B[0].getChildByName("num").color=that.COLOR_WHITE;
                        }
                        that.payInfoNode.active=false;
                        that.infoBgNode.active=false;
                    }
                    btn_node.getChildByName("Background").getChildByName("Label").getComponent(cc.Label).string="立即提现";
                    btn_node.getComponent(cc.Button).interactable=true;
                })));                
            }
        });
    }

    // update (dt) {},
});
