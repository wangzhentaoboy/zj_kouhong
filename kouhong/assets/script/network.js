// Learn cc.Class:
//  - https://docs.cocos.com/creator/manual/en/scripting/class.html
// Learn Attribute:
//  - https://docs.cocos.com/creator/manual/en/scripting/reference/attributes.html
// Learn life-cycle callbacks:
//  - https://docs.cocos.com/creator/manual/en/scripting/life-cycle-callbacks.html

var network = {
    _sock:null,  //当前的webSocket的对象
    url:"ws://game.treemay.com:8282",
    //url:"ws://127.0.0.1:8282",
    //url:"ws://121.40.165.18:8800",
    
    connect: function () {
        //当前接口没有打开
        //重新连接
        console.log('on connecting ');
        this._sock = new WebSocket(this.url);
        this._sock.onopen = this.onOpen.bind(this);
        this._sock.onclose = this.onClose.bind(this);
        this._sock.onmessage = this.onMessage.bind(this);
        return this;
    },

    onOpen:function(event){
        console.log('on open '+this._sock.readyState);
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_SOCKET_OPEN,event);
    },
    onClose:function(event){
        cc.log('on close ');
        cc.app.dispatcher.emit(cc.app.Cmd.CMD_SOCKET_CLOSE,event);
    },
    onMessage:function(event){
      
        let data = JSON.parse(event.data);
        if(data.cmd_id!=null){
            cc.app.dispatcher.emit(data.cmd_id,data.cmd_value);
        } 
    },

    send:function(msg){
        this._sock.send(msg);
        console.log("send msg"+msg);
    },
 
    close(){
        this._sock.close();
        this._sock = null;
    }


};

module.exports=network;
