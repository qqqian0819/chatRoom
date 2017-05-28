# chatRoom
基于PHP实现的简单的web聊天室  
实现方式：长连接/轮询/webSocket  
本项目没有实现各种华丽的功能,只是简单的用三种技术实现即时通信，帮组小白加深理解区别与联系。  
由于现在几乎采用webSocket方式，所以适当的添加了上线下线通知等功能  

# 目录结构    
  
├─streaming               长连接目录  
│  ├─push.html            长连接前端渲染页面文件  
│  └─flush.php            php实现长连接推送聊天信息文件  
│
├─long_polling            长轮询目录  
│  ├─client.html          长轮询客户端渲染页面  
│  └─push.php             PHP长轮询  
│     
├─webSocket               webSocket目录    
│  ├─client.html          webSocket客户端页面    
│  └─push.php             php实现webSocket服务器端    
│  
├─connect.php           连接数据库保存聊天记录文件(长连接 长轮询公用)  
├─chat.sql              创建数据库表文件文件）
├─uname.js            	生成唯一默认用户名(公用)  
├─favicon.png           logo图片  
 
  
# 使用方式   
 + 长连接：    
 	step1. 创建相应mysql表    
		注意：本人创建于数据库`test`下表名`tb_comet`。用户名`root`,密码``。记得配置    
	step2. 在同一或不同浏览器上直接运行多个push.html页面即可长连接实现web聊天功能    
 + 长轮询    
 	step1. 创建相应mysql表    
 	step2. 运行client.html，即可实现即时通信。     
 + webSocket   
 	step1. 运行socket.php     
 	step2. 运行多个index.html即可实现即时通信      
  
  
# 关于作者  
 + 2018届计算机科学与技术本科专业  
 + Email:qqqian0819@Gmail.com  
 + 新浪微博:一枚奋斗的girl  
 + 欢迎交朋友或者给我成为你同事的机会。  
 + 求职方向：PHP/前端  