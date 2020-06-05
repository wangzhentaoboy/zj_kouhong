// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

cc.Class({
    extends: cc.Component,

    properties: {
      grids:{
        default:[],
        type:cc.Node,
      },

      items:{
        default:[],
        type:cc.Node,
      },
      bagNode:cc.Node,
      iconTrash:cc.Sprite,
      sellPanel:cc.Node,
      isSelect:false,
      selectID :-1,
    },

    // LIFE-CYCLE CALLBACKS:

    // onLoad () {},
    onEnable(){
       this.node.on(cc.Node.EventType.TOUCH_START,this.onTouchStart,this);
       this.node.on(cc.Node.EventType.TOUCH_MOVE,this.onTouchMove,this);
       this.node.on(cc.Node.EventType.TOUCH_END,this.onTouchEnded,this);
       cc.app.dispatcher.on(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,this.onUpdateKouHongInfo,this);
     
       this.sellClass = this.sellPanel.getComponent('sellPanel');
       if (this.node._touchListener) {
          this.node._touchListener.setSwallowTouches(false);
       }
    },

    onDisable(){
        this.node.off(cc.Node.EventType.TOUCH_START,this.onTouchStart,this);
        this.node.off(cc.Node.EventType.TOUCH_MOVE,this.onTouchMove,this);
        this.node.off(cc.Node.EventType.TOUCH_END,this.onTouchEnded,this);
        cc.app.dispatcher.off(cc.app.Cmd.CMD_APP_UPDATE_KOUHONGINFO,this.onUpdateKouHongInfo);
    },

    start () {
        
        this.initObjectClass();
        this.initItemByConfig();
    },
    
    getAvailable(){
        for(let i =0;i<this.itemClasses.length;i++){
            if(this.itemClasses[i].isSleep()){
                this.itemClasses[i].wakeUp();
                return this.itemClasses[i];
            }
        }
        return null;
    },

    resetPool(){
        for(let i =0;i<this.itemClasses.length;i++){
            this.itemClasses[i].disappear();
            this.gridClasses[i].empty();
        }
    },
    
    initObjectClass(){
        let that =this
        this.gridClasses = [];
        this.itemClasses = [];
        
        this.grids.forEach((element,index)=>{
            that.gridClasses[index]= element.getComponent('grid');
        });

        this.items.forEach((element,index)=>{
            that.itemClasses[index]= element.getComponent('item');    
            that.itemClasses[index].sleep();             
        });
    },

    onUpdateKouHongInfo(){
        this.initItemByConfig();
    },
    initItemByConfig(){
        let that = this;
        this.resetPool();
        cc.app.mconfig.getKouHongInfo().forEach((element)=>{
            if(element.khid>0){
                let item = that.getAvailable();
                if(item==null) return;
                item.set(element.khid,element.caltime);
                that.gridClasses[element.index].put(item);
            }
        });
    },

    getSelectGrid(pos){
        let that =this;
        this.gridClasses.forEach((element,index) => {
            if(element.node.getBoundingBox().contains(pos)&&!this.isSelect&&!element.isEmpty()){
                that.isSelect = true;
                that.selectID = index;
            }
        });
    },

    getTargetGrid(pos){
        let retIndex = -1;
        this.gridClasses.forEach((element,index) => {
            if(element.node.getBoundingBox().contains(pos)){
                 retIndex = index;
            }
        });
        return retIndex;
    },

    checkFusion(pos){
      
        if(this.isSelect){
            let targetIndex = this.getTargetGrid(pos);
            this.isSelect = false;
            if(targetIndex>=0&&!this.gridClasses[targetIndex].isEmpty()){
                if(this.selectID==targetIndex){
                    this.gridClasses[this.selectID].itemObj.goBack();
                    return;
                }
                if(this.gridClasses[this.selectID].getLv()== this.gridClasses[targetIndex].getLv()){
                    //等级相同，进行合成
                    cc.app.controller.reqFusion(this.selectID,targetIndex);
                    cc.app.mconfig.next_fusion_lv = this.gridClasses[this.selectID].itemObj.lv+1;
                    this.gridClasses[this.selectID].itemObj.disappear();
                    this.gridClasses[this.selectID].empty();
                    this.gridClasses[targetIndex].lvUp();
                    console.log('........合成操作..........................'+this.gridClasses);
                }else{
                    //不能合成，进行交换
                    console.log('........ //不能合成，进行交换..........................'+this.gridClasses);
                    cc.app.controller.reqFusion(this.selectID,targetIndex);
                   // this.gridClasses[this.selectID].itemObj.goBack();
                   let swap = this.gridClasses[this.selectID].itemObj;
                   this.gridClasses[this.selectID].itemObj = this.gridClasses[targetIndex].itemObj;
                   this.gridClasses[targetIndex].itemObj = swap;
                   this.gridClasses[this.selectID].itemObj.goTo(this.grids[this.selectID].position);
                   this.gridClasses[targetIndex].itemObj.goTo(this.grids[targetIndex].position);
                   this.gridClasses[this.selectID].updateInfo();
                   this.gridClasses[targetIndex].updateInfo();
                }
            }else{//目标框是空的
               if(targetIndex>=0){
                 cc.app.controller.reqFusion(this.selectID,targetIndex);
                 this.gridClasses[targetIndex].put(this.gridClasses[this.selectID].itemObj);
                 this.gridClasses[this.selectID].empty();               
               }else{
                   if(this.bagNode.getBoundingBoxToWorld().contains(pos)){
                      //放入背包操作
                       console.log('放入背包的操作!');
                       this.gridClasses[this.selectID].itemObj.disappear();
                       this.gridClasses[this.selectID].empty();
                       cc.app.controller.reqPutBag(this.selectID);
                   }else if(this.iconTrash.node.getBoundingBoxToWorld().contains(pos)){
                       //垃圾箱回收
                       console.log('放入垃圾箱操作!');
                     
                       this.sellClass.show({selectID:this.selectID,
                        lv:this.gridClasses[this.selectID].itemObj.lv,
                        khid : this.gridClasses[this.selectID].itemObj.khid});
                        this.gridClasses[this.selectID].itemObj.disappear();
                        this.gridClasses[this.selectID].empty();
                   }else{
                      this.gridClasses[this.selectID].itemObj.goBack();
                   }
               }
            }
        }
    },
 
    onTouchStart(event){
   
       this.getSelectGrid(event.touch.getLocation());
    },

    onTouchMove(event){
      
        if(this.isSelect&&this.gridClasses[this.selectID].itemObj){
            this.gridClasses[this.selectID].itemObj.follow(event.getDelta().x,event.getDelta().y);
        }
    },

    onTouchEnded(event){
        console.log('..................onTouchEnded:');
        this.checkFusion(event.getLocation());
    },

    // update (dt) {},
});
