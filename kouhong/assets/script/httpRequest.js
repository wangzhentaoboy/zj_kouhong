var httpRequest = {
   openUrl(url,type,cb){
      console.log("请求参数:"+url);
      let that = this;  
      this.xhr = cc.loader.getXMLHttpRequest();
      this.xhr.timeout = 5000;// 5 seconds for timeout
    
      this.xhr.onreadystatechange = function(){
         if (that.xhr.readyState === 4 && (that.xhr.status >= 200 && that.xhr.status < 300)) {
            var respone =that.xhr.responseText;
            cb(respone);
         }
      };
     
      this.xhr.open(type, url, true);
      this.xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");  
      this.xhr.send();
   },

   getUrl(url,type,cb){
      console.log("请求参数2:"+url);
      let xhr = cc.loader.getXMLHttpRequest();
      xhr.timeout = 5000;// 5 seconds for timeout
    
      xhr.onreadystatechange = function(){
         if (xhr.readyState === 4 && (xhr.status >= 200 && xhr.status < 300)) {
            var respone =xhr.responseText;
            cb(respone);
         }
      };
     
      xhr.open(type, url, true);
      xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");  
      xhr.send();
   },
};

module.exports= httpRequest;
