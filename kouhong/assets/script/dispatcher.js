var dispatcher = {
   callList:[],
   on(msgID,cb,target){
      if(this.callList[msgID]==undefined){
         this.callList[msgID] = [];
      }
      this.callList[msgID].push({cb:cb,target:target});
     
   },

   off(msgID,cb){
      for(let i =0;i<this.callList[msgID].length;i++){
         if(this.callList[msgID][i].cb==cb){
            this.callList[msgID].splice(i, 1);
            break;
         }
      }
   },

   emit(msgID,data){
      if(this.callList[msgID]==undefined) return;
      for(let i =0;i<this.callList[msgID].length;i++){
         this.callList[msgID][i].cb.call(this.callList[msgID][i].target,data);
      }
   },
   print(){
     
   }
};

module.exports= dispatcher;
