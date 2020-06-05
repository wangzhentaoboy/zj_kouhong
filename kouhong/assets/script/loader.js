// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html
const loader = {
   sceneName :'',
   urllist:[
      {url:"texture/bg_bar",type:cc.SpriteFrame},
      {url:"texture/bg_bottom_menu",type:cc.SpriteFrame},
      {url:"texture/bg_btn_buy",type:cc.SpriteFrame},
      {url:"texture/bg_cover",type:cc.SpriteFrame},
      {url:"texture/bg_top",type:cc.SpriteFrame},
      {url:"texture/kouhongyin",type:cc.SpriteFrame},
      {url:"texture/bg_xianqizhi_border",type:cc.SpriteFrame},
      {url:"texture/02",type:cc.SpriteFrame},
      {url:"texture/03",type:cc.SpriteFrame},
      {url:"texture/05",type:cc.SpriteFrame},
      {url:"texture/06",type:cc.SpriteFrame},
      {url:"texture/14",type:cc.SpriteFrame},
      {url:"texture/15",type:cc.SpriteFrame},
      {url:"texture/20",type:cc.SpriteFrame},
      {url:"texture/21",type:cc.SpriteFrame},
      {url:"texture/22",type:cc.SpriteFrame},
      {url:"texture/23",type:cc.SpriteFrame},
   ],
   init(sceneName){
      this.completedCount = 1;
      let that =this;
      if(this.sceneName.length>2) return;
    
      this.urllist.forEach((url=>{
         cc.loader.loadRes(url.url,url.type,this.onLoadComplete.bind(this));
      }));
      cc.loader.loadRes("atlas/bigtex", cc.SpriteAtlas, function (err, atlas) { 
         that.atlas = atlas; 
         console.log('............................loader init '+atlas+err);
         if(that.completedCount>=that.urllist.length+1){
            cc.director.loadScene(that.sceneName);
            that.sceneName = '';
         }else{
            that.completedCount +=1;
         }   
      });  
      this.sceneName = sceneName;
   },
 
   onLoadComplete(err){
      cc.app.dispatcher.emit(cc.app.Cmd.CMD_APP_LOAD_PROGRESS,{total:this.urllist.length,cur:this.completedCount});
      if(this.completedCount>=this.urllist.length+1){
         cc.director.loadScene(this.sceneName);
         this.sceneName = '';
      }else{
         this.completedCount +=1;
      }
  },
 
   getSpriteFrame(url){
       return cc.loader.getRes(url,cc.SpriteFrame);
   },

   getAtlasSpriteFrame(url){
      return this.atlas.getSpriteFrame(url);
  },
};

module.exports= loader;
