<?php

// 回复消息

$link=mysqli_connect('localhost','root','','test');
mysqli_query($link,"set names utf8");

$rec=$_POST['receiver'];
$content=$_POST['content'];
$pos=$_POST['username'];

// flag 0 未读
$sql="INSERT INTO tb_comet (pos,receiver,content,flag) VALUES ('$pos','$rec','$content',0)";

echo mysqli_query($link,$sql)?'ok':'fail';