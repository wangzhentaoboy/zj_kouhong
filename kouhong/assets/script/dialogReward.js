// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
       remain:cc.Label,
       panel:cc.Node,
       btn_play:cc.Button,
       bonus:cc.Label,
       btnTxt:cc.Label,
    },

    start () {
        this.videoAd=null;
    },

    onClickPlayRewardVedio(){
      //激励视频
      let that = this;
      let curt = new Date().getTime();
      let pre = cc.sys.localStorage.getItem("lastTime")||0;
      let pastTime = (curt-pre)/1000;
      if(pastTime<60) return;
      cc.sys.localStorage.setItem("lastTime",curt);
      //激励广告
      console.log("点击看广告");
      this.zijieAdVideo();
    },

    zijieAdVideo(){
      let that=this;
      if (!this.videoAd) {
          let adId = '729hklo2il1c72c1gf';
          if (tt.createRewardedVideoAd == 'undefined') return;
          this.videoAd = tt.createRewardedVideoAd({
              adUnitId: adId
          });
          this.videoAd.onClose((res) => {
              console.log('videoAd.onClose res = ' + JSON.stringify(res));
              if (res.isEnded == true) {
                cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_ADD_XIANQI,2);
                that.onClickClose();
              } else {
                  console.log("未看完完整广告，不能获得收益！");
              }
              
          });
      }
      console.log("广告组件对象："+this.videoAd);
      this.videoAd.show()
            .then(() => {
              console.log("广告显示成功");
            })
            .catch(err => {
              console.log("广告出错", err);
              // 可以手动加载一次
              that.videoAd.load().then(() => {
                console.log("手动加载成功");
                // 加载成功后需要再显示广告
                return that.videoAd.show();
              });
              
            });   
    },

    show(data){
       this.node.active = true;
       this.panel.scale = 0.3;
       this.remain.string = data.leftVideoTimes;
       this.panel.runAction(cc.sequence(cc.scaleTo(0.15,1.1),cc.scaleTo(0.1,1))); 
       if(data.leftVideoTimes<=0){
          this.btn_play.interactable = false;
       }else{
         this.btn_play.interactable = true;
       }
       let maxlv = cc.app.mconfig.getKhMaxLv();
       let rlv = cc.app.mconfig.kouHongPath[Math.max(0,maxlv-1)].buyLv;
       if(rlv>=14&&rlv<=25){
           this.btnTxt.string = "立即获得3小时收益"
       }else{
           this.btnTxt.string = "立即获得2小时收益"
       }
       let that = this;
       let uid  = cc.sys.localStorage.getItem('uid');
       cc.app.http.openUrl("http://game.treemay.com/index/api/twoHours?userid="+uid,"GET",function(res){
             let data = JSON.parse(res);
             that.bonus.string =cc.app.mconfig.formatNumber(data.data) ;
       });
    },

    onClickClose(){
       this.panel.runAction(cc.sequence(cc.scaleTo(0.2,0),cc.callFunc(function(){
           this.node.active= false;
       }.bind(this))));  
    },

    // update (dt) {},
});
