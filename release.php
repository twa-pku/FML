<?php
include "FML.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
if(checkCookie()){
$team=mysqli_real_escape_string($conn,strtoupper($_GET['team']));
$player=mysqli_real_escape_string($conn,$_GET['player']);

//判断是否能解约
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND Team='".$team."'"))==0){
	echo($player."不在".$team."!");
}
//能则更新数据库并写日志
else{
	mysqli_query($conn,"UPDATE current SET Team='',Price=0 WHERE Name='".$player."'");
	writeLog($player." in ".$team." released");
	echo("操作成功");
}
mysqli_close($conn);
}
else{
	echo("没有权限");
	mysqli_close($conn);
}
?>
