<?php

// iframe完成在线客服

set_time_limit(0);//设置脚本最大执行时间 单位s
ob_start();

$pad=str_repeat(' ', 8000);//8000个空白字符串
// 同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。

ob_flush();//ob_flush — 冲刷出（送出）输出缓冲区中的内容
flush();//把传送的内容 立即发送给浏览器而不要等脚本结束再一起发. 刷新PHP程序的缓冲,该函数将当前为止程序的所有输出发送到用户的浏览器。

$link=mysqli_connect('localhost','root','','test');
mysqli_query($link,"set names utf8");
// 死循环
while (true) {	
	// 若无limit1 则在 sleep(1)之内发了多条会出错
	$sql="SELECT * FROM tb_comet WHERE flag=0 LIMIT 1";
	$row=mysqli_fetch_assoc(mysqli_query($link,$sql));
	if(!empty($row)){
		echo $pad,"<br />";
		$msg=json_encode($row);
		echo "<script> parent.window.comet($msg) </script>";
		$id=$row['id'];
		ob_flush();
		flush();//把传送的内容 立即发送给浏览器而不要等脚本结束再一起发		
		//设为已读状态
		mysqli_query($link,"UPDATE tb_comet SET flag=1 WHERE id=$id");
		
	}
	sleep(1);

}