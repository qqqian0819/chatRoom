<?php

// 回复消息

$link=mysqli_connect('localhost','root','','test');
mysqli_query($link,"set names utf8");

// setcookie('usernmae','admin');

$rec=$_POST['receiver'];
$content=$_POST['content'];

$pos=$_COOKIE['username'];

$sql="INSERT INTO tb_comet (pos,receiver,content,flag) VALUES ('admin','$rec','$content',0)";

echo mysqli_query($link,$sql)?'ok':'fail';