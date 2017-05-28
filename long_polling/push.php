<?php

// 用户端收到的回复


/*set_time_limit(0);

$link=mysqli_connect('localhost','root','','test');
mysqli_query($link,"set names utf8");


$pos=$_COOKIE['username'];


// $sql="SELECT * FROM tb_comet WHERE receiver='$rec' AND flag=0 LIMIT 1 ";
$sql="SELECT * FROM tb_comet WHERE flag=0 AND pos!='$pos' LIMIT 1 ";
// $sql="SELECT * FROM tb_comet WHERE flag=0 LIMIT 1 ";


$rs=mysqli_query($link,$sql);
$row=mysqli_fetch_assoc($rs);
if(!empty($row)){

	echo json_encode($row);
	$id=$row['id'];
	mysqli_query($link,"UPDATE tb_comet SET flag=1 WHERE id=$id");
}
sleep(1);*/


set_time_limit(0);

$link=mysqli_connect('localhost','root','','test');
mysqli_query($link,"set names utf8");

// $rec=$_COOKIE['username'];
$sql="SELECT * FROM tb_comet WHERE flag=0 LIMIT 1 ";
while (1) {


	$rs=mysqli_query($link,$sql);
	$row=mysqli_fetch_assoc($rs);
	if(!empty($row)){

		echo json_encode($row);
		$id=$row['id'];
		mysqli_query($link,"UPDATE tb_comet SET flag=1 WHERE id=$id");
		break;
	}
	sleep(1);

}