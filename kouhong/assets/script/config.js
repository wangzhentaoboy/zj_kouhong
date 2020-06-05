var config = {
   kouHongPath:[
      {name:'她素',    path:'icon1',buyLv:1},
      {name:'露华浓',  path:'icon2',buyLv:1},
      {name:'美康粉黛',path:'icon3',buyLv:1},
      {name:'洛色',    path:'icon4',buyLv:1},
      {name:'bob',     path:'icon5',buyLv:1},
      {name:'橘朵',    path:'icon6',buyLv:1},
      {name:'gellas',  path:'icon7',buyLv:2},
      {name:'完美日记',path:'icon8',buyLv:3},
      {name:'ukiss',   path:'icon9',buyLv:3},
      {name:'玛丽黛佳国风唇釉',path:'icon10',buyLv:4},
      {name:'滋色毕加索口红',path:'icon11',buyLv:4},
      {name:'hold live',path:'icon12',buyLv:5},
      {name:'花西子',path:'icon13',buyLv:6},
      {name:'妙巴黎',path:'icon14',buyLv:7},
      {name:'VNK',path:'icon15',buyLv:8},
      {name:'透真',path:'icon16',buyLv:9},
      {name:'JC',path:'icon17',buyLv:10},
      {name:'芭妮兰',path:'icon18',buyLv:11},
      {name:'美宝莲',path:'icon19',buyLv:12},
      {name:'fastb',path:'icon20',buyLv:12},
      {name:'卡姿兰',path:'icon21',buyLv:13},
      {name:'百雀羚',path:'icon22',buyLv:14},
      {name:'3ce',path:'icon23',buyLv:15},
      {name:'红地球',path:'icon24',buyLv:16},
      {name:'悦诗风吟',path:'icon25',buyLv:16},
      {name:'欧莱雅小钢笔',path:'icon26',buyLv:17},
      {name:'梦妆',path:'icon27',buyLv:18},
      {name:'nars',path:'icon28',buyLv:19},
      {name:'蜜丝佛陀',path:'icon29',buyLv:20},
      {name:'mac ',path:'icon30',buyLv:21},
      {name:'雅诗兰黛',path:'icon31',buyLv:22},
      {name:'兰蔻',path:'icon32',buyLv:23},
      {name:'香奈儿',path:'icon33',buyLv:24},
      {name:'阿玛尼',path:'icon34',buyLv:25},
      {name:'纪梵希',path:'icon35',buyLv:26},
      {name:'ysl小金条',path:'icon36',buyLv:27},
      {name:'迪奥',path:'icon37',buyLv:28},
      {name:'TF',path:'icon38',buyLv:28},
      {name:'芭比波朗',path:'icon39',buyLv:28},
      {name:'爱马仕',path:'icon40',buyLv:28},
      {name:'娇兰宝石',path:'icon41',buyLv:29},
      {name:'Hourglass黄金烟管口红',path:'icon42',buyLv:29},
      {name:'口红王子',path:'icon43',buyLv:29},
      {name:'口红公主',path:'icon44',buyLv:29},
      {name:'许愿精灵',path:'icon45',buyLv:29},
   ],
   userInfo:{},
   kouHongInfo:{},
   kouHongConfig:{},
   tempXianQiZhi:0,
   backCount :2,
   isMainPage:true,
 
   setIsMainPage(bool){
      this.isMainPage = bool;
   },

   getIsMainPage(){
      return this.isMainPage;
   },

   setXianQiZhi(v){
      this.XianQiZhi = v;
   },

   addXianQiZhi(v){
      this.XianQiZhi+=v;
   },

   setJingLingNum(v){
      this.JingLingNum = v;
   },

   setKhMaxLv(v){
      this.Khmaxlvl = v;
   },

   setIskhGirl10(v){
      this.isKhGirl10 = v;
   },

   setIskhGirl20(v){
      this.isKhGirl20 = v;
   },

   setIsOffLine(v){
      this.offLine = v;
   },

   setBagInfo(v){
      this.bagInfo = v;
   },

   setKouHongInfo(v){
      this.KhInfo = v;
   },

   setVideoTimes(v){
      this.VideoTime = v;
   },

   setShareMoney(v){
      this.shareMoney = v;
   },

   setKouHongGirl(v){
      this.khgirl = v;
   },

   getVideoTimes(){
      return this.VideoTime;
   },

   setLeftVideoTimes(v){
      this.leftVideoTimes = v;
   },

   getLeftVideoTimes(){
      return this.leftVideoTimes;
   },

   getXianQiZhi(){
      return this.formatNumber(this.XianQiZhi);
   },

   getJingLingNum(){
      return this.JingLingNum;
   },
   getKouHongInfo(){
      return this.KhInfo;
   },
   //"khmaxlvl":0,"iskhgirl10":0,"iskhgirl20":0},"offline":0
   getKhMaxLv(){
      return  this.Khmaxlvl;
   },
   setCostByLv(lv){
   
      console.log('.........setCostByLv...........lv is .........'+lv);
      let rlv = this.kouHongPath[Math.max(0,lv-1)].buyLv;
     
      this.costBylv = this.formatNumber(this.kouHongConfig[rlv+5].item_consume)
   },
   setKHGirl(lv){
   
     this.khgirl = lv;
   },
   
   getKHGirl(){
      return this.khgirl;
   },
   setAllBonus(v){
      this.all_bonus = v;
   },
   setAllTodayBonus(v){
      this.todayAllBonus = v;
   },
   setTodayBonus(v){
      this.todayBonus = v;
   },

   setTodayIndirect(v){
      this.todayIndirect = v;
   },

   getCostByLv(){
      return this.costBylv;
   },

   getIskhgirl10(){
      return  this.isKhGirl10;
   },

   getIskhgirl20(){
      return  this.isKhGirl20;
   },
   getIsOffLine(){
      return  this.offLine;
   },

   getBagInfo(){
      return this.bagInfo;
   },

   getShareMoney(){
      return this.shareMoney;
   },
   getKouHongGirl(){
      return this.khgirl;
   },

   getAllBonus(){
      return this.all_bonus;
   },
   getAllTodayBonus(){
      return this.todayAllBonus;
   },
   getTodayBonus(){
      return this.todayBonus ;
   },

   getTodayIndirect(){
      return this.todayIndirect;
   },

   getKHIDDif(info){
      let ret = null;
      for(let i =0;i<this.KhInfo.length;i++){
         if(info[i].khid!=0 && this.KhInfo[i].khid!=info[i].khid){
            ret= info[i];
            break;
         }
      }
     
      return ret;
   },

   formatNumber(v){
      // //v = 366160000000000000;
      // v = 366160000000000;
      if(v<100000){
         return v;
      }else if(v>=100000&&v<100000000){
         return (v/1000).toFixed(2)+"k"
      }else if(v>=100000000&&v<100000000000){
         return (v/1000000).toFixed(2)+"m"
      }else if(v>=100000000000&&v<100000000000000){
         return (v/1000000000).toFixed(2)+"b"
      }else if(v>=100000000000000&&v<100000000000000000){
         return (v/1000000000000).toFixed(2)+"t"
      }else if(v>=100000000000000000){
         return (v/1000000000000000).toFixed(2)+"aa"
      }

   },
};

module.exports= config;
