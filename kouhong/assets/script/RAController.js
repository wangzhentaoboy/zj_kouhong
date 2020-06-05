// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html
const RAController = {
   init(){
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_LOGIN,this.ackLogin,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_BUY,this.ackBuy,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_FUSION,this.ackFusion,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_REQ_PUMP,this.ackPump,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_SOCKET_CLOSE,this.onSocketClose,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_KOUHONG_INI,this.ackKouHongIni,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_PUTTRASH,this.ackPutTrash,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_PUTBAG,this.ackPutBag,this);
      cc.app.dispatcher.on(cc.app.Cmd.CMD_NET_ACK_GETBAG,this.ackGetBag,this);
   },
   reqLogin(uid,pwd){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_LOGIN;
      data.cmd_value = {uid:uid,openid:pwd};
    
      cc.app.network.send(JSON.stringify(data));  
   },
   
   ackLogin(data){
      cc.log(data);
      cc.log("ackLogin.."+JSON.stringify(data));
     
      if(data.code==0){
        
         cc.app.mconfig.setXianQiZhi(data.data.userinfo.khxianqi);
         cc.app.mconfig.setJingLingNum(data.data.userinfo.khsprite);
         cc.app.mconfig.setKhMaxLv(data.data.userinfo.khmaxlvl);
         cc.app.mconfig.setIskhGirl10(data.data.userinfo.iskhgirl10);
         cc.app.mconfig.setIskhGirl20(data.data.userinfo.iskhgirl20);
         cc.app.mconfig.setIsOffLine(data.data.offline);
         cc.app.mconfig.setKouHongInfo(data.data.userinfo.khinfo);
         cc.app.mconfig.setBagInfo(data.data.bag);
         cc.app.mconfig.setShareMoney(data.data.shareMoney);
         cc.app.mconfig.setCostByLv(data.data.userinfo.khmaxlvl)
         cc.app.mconfig.setKHGirl(data.khgirl)
         
         cc.log("data.khgirl.."+data.khgirl);
        
         cc.app.loader.init("mainScene");
         cc.sys.localStorage.setItem("lastLv",data.data.userinfo.khmaxlvl);
      }else{
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_LOGIN_FAILED);
      }
   },

   ackKouHongIni(data){
      cc.app.mconfig.kouHongConfig = data;
      cc.log("ackKouHongIni.."+JSON.stringify(data));
   },

   reqBuy(){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_BUY;
      data.cmd_value = {};
      cc.app.network.send(JSON.stringify(data));  
   },

   ackBuy(data){
      // code = 1   购买失败，仙气值不足
      // code = 2   购买失败，格子不够
      if(data.code==0){
         cc.app.mconfig.setXianQiZhi(data.data.khxianqi);
         cc.app.mconfig.setJingLingNum(data.data.khsprite);
         cc.app.mconfig.setKhMaxLv(data.data.khmaxlvl);
         cc.app.mconfig.setCostByLv(data.data.khmaxlvl)
         cc.app.mconfig.setIskhGirl10(data.data.iskhgirl10);
         cc.app.mconfig.setIskhGirl20(data.data.iskhgirl20);
         cc.app.mconfig.setKouHongInfo(data.data.khinfo);
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
      }else if(data.code==1){
         //jsBridge.toast("购买失败,仙气值不足!");
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_SHOW_REWARD_PANEL,{leftVideoTimes:cc.app.mconfig.getLeftVideoTimes()});
      }else if(data.code==2){
         console.log("购买失败,格子没有空位了!");
      }
      cc.log("data.code.."+data.code);
      cc.log("ackBuy.."+JSON.stringify(data));
      // cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_SHOW_REWARD_PANEL,{leftVideoTimes:cc.app.mconfig.getLeftVideoTimes()});
   },

   reqFusion(fromID,toID){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_FUSION;
      data.cmd_value = {
         fromindex:fromID, 
         toindex :toID
      };
      cc.app.network.send(JSON.stringify(data));  
   },

   ackFusion(data){
      if(data.code==0){
         cc.app.mconfig.setXianQiZhi(data.data.khxianqi);
         cc.app.mconfig.setJingLingNum(data.data.khsprite);
         cc.app.mconfig.setKhMaxLv(data.data.khmaxlvl);
         cc.app.mconfig.setIskhGirl10(data.data.iskhgirl10);
         cc.app.mconfig.setIskhGirl20(data.data.iskhgirl20);
         cc.app.mconfig.setCostByLv(data.data.khmaxlvl)
        
         if(cc.sys.localStorage.getItem("lastLv")<data.data.khmaxlvl &&data.data.khmaxlvl<38){
            cc.sys.localStorage.setItem("lastLv",data.data.khmaxlvl);
            cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_SHOW_LEVEL_UP_PANEL,{lv:data.data.khmaxlvl,offLevel:39-data.data.khmaxlvl,bonusToday:0});
         }
        // data.data.khgirl =new Date().getTime()/1000;
         if(data.khgirl){
            cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_ON_SHOW_GIRL,data.khgirl);
         }
         console.log("ackFusion.."+JSON.stringify(data));
         let dife=cc.app.mconfig.getKHIDDif(data.data.khinfo);
         cc.app.mconfig.setKouHongInfo(data.data.khinfo);
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
        
         if(cc.app.mconfig.next_fusion_lv ==38){
            cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_ON_SHOW_FUSION37_38,dife.khid-43);
         }
       
      }else if(data.code==1){
        // jsBridge.toast("升级失败!");
      }
   },

   reqPutBag(index){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_PUTBAG;
      data.cmd_value = {fromindex:index};
      cc.app.network.send(JSON.stringify(data));  
   },
   
   ackPutBag(data){
      cc.log("ackPutBag.."+JSON.stringify(data));
      if(data.code==0){
         cc.app.mconfig.setXianQiZhi(data.data.khxianqi);
         cc.app.mconfig.setJingLingNum(data.data.khsprite);
         cc.app.mconfig.setKhMaxLv(data.data.khmaxlvl);
         cc.app.mconfig.setIskhGirl10(data.data.iskhgirl10);
         cc.app.mconfig.setIskhGirl20(data.data.iskhgirl20);
         cc.app.mconfig.setKouHongInfo(data.data.khinfo);
         cc.app.mconfig.setBagInfo(data.bag);
         cc.app.mconfig.setCostByLv(data.data.khmaxlvl)
         console.log("放入背包成功");
      }else{
        
      }
   },

   reqGetBag(index){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_GETBAG;
      data.cmd_value = {khid:index};
      cc.log("reqGetBag.."+JSON.stringify(data));
      cc.app.network.send(JSON.stringify(data));  
     
   },
   
   ackGetBag(data){
      cc.log("ackGetBag.."+JSON.stringify(data));
      if(data.code==0){
         cc.app.mconfig.setXianQiZhi(data.data.khxianqi);
         cc.app.mconfig.setJingLingNum(data.data.khsprite);
         cc.app.mconfig.setKhMaxLv(data.data.khmaxlvl);
         cc.app.mconfig.setIskhGirl10(data.data.iskhgirl10);
         cc.app.mconfig.setIskhGirl20(data.data.iskhgirl20);
         cc.app.mconfig.setKouHongInfo(data.data.khinfo);
         cc.app.mconfig.setBagInfo(data.bag);
         cc.app.mconfig.setCostByLv(data.data.khmaxlvl)
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
         console.log("取出成功");
      }else{
        
      }
   },

   reqPutTrash(index){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_PUTTRASH;
      data.cmd_value = {fromindex:index};
      cc.app.network.send(JSON.stringify(data));  
   },
   
   ackPutTrash(data){
      cc.log("ackPutTrash.."+JSON.stringify(data));
      if(data.code==0){
         cc.app.mconfig.setKouHongInfo(data.data.khinfo);
         cc.app.mconfig.setXianQiZhi(data.data.khxianqi);
         cc.app.mconfig.setJingLingNum(data.data.khsprite);
         cc.app.mconfig.setKhMaxLv(data.data.khmaxlvl);
         
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_USERINFO,"");
         cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,"");
      }else{
        
      }
   },

   reqPump(){
      let data ={};
      data.cmd_id = cc.app.Cmd.CMD_NET_REQ_PUMP;
      data.cmd_value = {
      };
      cc.app.network.send(JSON.stringify(data));  
   },

   ackPump(data){
      console.log('.............心跳包返回');
   },

   deinit(){
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_LOGIN,this.ackLogin);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_BUY,this.ackBuy);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_FUSION,this.ackFusion);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_REQ_PUMP,this.ackPump);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_SOCKET_CLOSE,this.onSocketClose);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_KOUHONG_INI,this.ackKouHongIni);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_PUTTRASH,this.ackPutTrash);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_PUTBAG,this.ackPutBag);
      cc.app.dispatcher.off(cc.app.Cmd.CMD_NET_ACK_GETBAG,this.ackGetBag);
   },

   startPumpTimer(node){
      this.handler = cc.director.getScheduler().schedule(this.reqPump,node,15);
   },

   stopPumpTimer(){
      cc.director.getScheduler().unschedule(this.reqPump);
   },

   onSocketClose(){
      
   },
};

module.exports= RAController;
