<?php
/*php实现的webSocket服务器端*/
error_reporting(E_ALL);
set_time_limit(0);// 防止超时

class webSocket{
 	private $sockets=[];// 连接池
 	private $master;// 主进程
 	const maxTime=60;// select最大等待时间
 	const maxConnect=1024;// 最大等待连接数


 	public  function __construct($host,$port)
    {	// 创建一个套接字:存在于一个名字空间（地址族）中，但并未赋名
       	$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
       	// 设置socket：
       	socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
       	// 绑定socket:给一个未命socket分配一个本地名字来为socket建立本地捆绑(主机地址/端口号)
        socket_bind($this->master, $host, $port);
        // 监听socket:使一个进程可以接受其它进程的请求，从而成为一个服务器进程
        socket_listen($this->master, self::maxConnect);
        
        // 记录运行信息
        echo ("Server Started : ".date('Y-m-d H:i:s'). " \n");
        echo ("Listening on   : 127.0.0.1 port " . $port. " \n");
        echo ("Master socket  : " . $this->master . " \n");

        $this->sockets[0] = ['resource' => $this->master];
        while (true) {
            $this->doServer();
        }
    }
    /**
     * 开始
     * @date   2017-05-28
     */
    private function doServer()
    {	
    	// 获得sockets中所有resource,得到全部的 socket 资源
    	$sockets = array_column($this->sockets, 'resource');
    	// 获取read数组中活跃的socket，
        @socket_select($sockets, $write=NULL, $except=NULL, self::maxTime);

        foreach ($sockets as $so) {
        	// 当前连接是主进程
        	if($this->master == $so){
        		// 等待并接受客户请求,新的链接。return bool
        		$client = socket_accept( $this->master );
                if ($client==false) {
                	echo 'socket_accept failed';
                	continue;              
                }else{
                	$this->connect($client);
                	continue;
                }
            // 其他socket,则接收并处理数据
        	}else{
        		//从socket接收长度为2048字节的数据并保存到buffer中。return 字节数/false
                $bytes = @socket_recv($so, $buffer, 2048, 0);
                // 当客户端忽然中断时,服务器会接收到一个8字节长度的消息。未收到消息
                if($bytes<9){
                	$msg=$this->disConnect( $so );
                }else{
                	//未握手 先握手
                    if(!$this->sockets[(int)$so]['handshake'] ){

                       	$this->doHandShake( $so, $buffer );
                       	continue;
                    // 已经握手，处理数据
                    }else{
                        
                        $msg = $this->decode($buffer);
                        // array_unshift($recv_msg, 'receive_msg');
                		
                    }
                }
               
               	$this->dealMsg($so, $msg);
        	}
        }
    }

    /**
     * 拼装数据
     * @date   2017-05-28
     * @param  $socket
     * @param  $msg
     */
    private function dealMsg($socket, $msg)
    {
    	$response=[];
    	// login 登录 ；logout 退出 ；user 聊天
    	switch ($msg['type']) {
    		case 'login':
    			$this->sockets[(int)$socket]['uname'] = $msg['content'];
    			$user_list = array_column($this->sockets, 'uname');
    			
    			$response=[
    				'type'=>'login',
    				'content'=>$msg['content'],
    				'user_list'=>$user_list,
    				'stime'=>date('Y-m-d H:i:s')
    			];
    			break;
    		case 'logout':
    			$user_list = array_column($this->sockets, 'uname');
    			$response=[
    				'type'=>'logout',
    				'content'=>$msg['content'],
    				'user_list'=>$user_list,
    				'stime'=>date('Y-m-d H:i:s')
    			];
    			break;
    		case 'user':
    			$uname = $this->sockets[(int)$socket]['uname'];
    			$response=[
    				'type'=>'user',
    				'content'=>$msg['content'],
    				'from'=>$uname,
    				'receiver'=>$msg['receiver'],
    				'stime'=>date('Y-m-d H:i:s')
    			];
    			break;
    	}
    	$data=$this->frame(json_encode($response));

    	$this->broadcast($data);
    }

    /**
     * 广播消息[排除master]
     * @date   2017-05-28
     * @param  $data 
     */
    private function broadcast($data) {
        foreach ($this->sockets as $so) {
            if ($so['resource'] == $this->master) {
                continue;
            }
            socket_write($so['resource'], $data, strlen($data));
        }
    }


    /**
     * 添加到连接列表，但还未握手
     * @date   2017-05-28
     * @param  $socket
     */
    private function connect($socket)
    {	
    	
    	socket_getpeername($socket, $ip, $port);
    	$msg=[
    		'resource' => $socket,
    		'handshake'=>false,
    		'uname' => '',
    	];
    	// int把socket转换为唯一的ID
    	$this->sockets[(int)$socket] = $msg;
    }

    /**
     * 客户端断开连接
     * @date   2017-05-28
     * @param  $socket 
     */
    private function disConnect($socket)
    {

    	$msg = [
            'type' => 'logout',
            'content' => $this->sockets[(int)$socket]['uname'],
        ];
        // 关闭此连接
        // socket_close( $this->sockets[(int)$socket] );
       
        // 池中删掉当前断开用户
        unset($this->sockets[(int)$socket]);

        return $msg;
    }

    /**
     * 握手协议
     * @date   2017-05-28
     * @param  $socket 
     * @param  $buffer 
     * @return true
     */
    private function doHandShake($socket, $buffer)
    {

    	// var_dump($socket);
    	
		/*eg 某一个http头：
		...	
		Sec-WebSocket-Key: mzcXXwRLMT1z84nnUpCPTg==
		...
		*/	

        // 获取到客户端的升级密匙 
        $line= substr($buffer, strpos($buffer, 'Sec-WebSocket-Key:') + 18);
        $key = trim(substr($line, 0, strpos($line, "\r\n")));// mzcXXwRLMT1z84nnUpCPTg==

        // 升级密匙,拼接头部信息。
        $upgrade_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));// 升级key的算法
         $upgrade_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $upgrade_message .= "Upgrade: websocket\r\n";
        $upgrade_message .= "Sec-WebSocket-Version: 13\r\n";
        $upgrade_message .= "Connection: Upgrade\r\n";
        $upgrade_message .= "Sec-WebSocket-Accept:" . $upgrade_key . "\r\n\r\n";

        // 写入升级信息
        socket_write($socket, $upgrade_message, strlen($upgrade_message));
        // 修改为握手状态
        $this->sockets[(int)$socket]['handshake'] = true;

        // 握手成功,客户端发送用户名;
        $msg = [
            'type' => 'handshake',
            'content' => 'done',
        ];
      
        $msg = $this->frame(json_encode($msg));
        socket_write($socket, $msg, strlen($msg));
        return true;
    }


    /**
     * 解码 解析数据帧
     * @date  	 2017-05-28
     * @param  	 $buffer
     * @return 	 string  
     */
    private function decode($buffer) {
        $decoded = '';
        //  ord返回字符串首个字符的ASCII值
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }

        return json_decode($decoded, true);
    }

    /**
     * 帧处理，将普通信息组装成websocket数据帧
     * @date   2017-05-28
     * @param  $msg 
     */
    private function frame($msg)
    {
    	$frame = [];
        $frame[0] = '81';
        $len = strlen($msg);
        if ($len < 126) {
            $frame[1] = $len < 16 ? '0' . dechex($len) : dechex($len);
        } else if ($len < 65025) {
            $s = dechex($len);
            $frame[1] = '7e' . str_repeat('0', 4 - strlen($s)) . $s;
        } else {
            $s = dechex($len);
            $frame[1] = '7f' . str_repeat('0', 16 - strlen($s)) . $s;
        }

        $data = '';
        $l = strlen($msg);
        for ($i = 0; $i < $l; $i++) {
            $data .= dechex(ord($msg{$i}));
        }
        $frame[2] = $data;

        $data = implode('', $frame);

        return pack("H*", $data);
    }

}


$ws = new webSocket("127.0.0.1", "8080");