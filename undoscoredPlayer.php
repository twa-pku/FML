<?php
include "FML.php";
if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,$_GET["str"]);

//整个文档完全就是把submitscoredPlayer.php做的事反过来做一遍，结构相同
$res=mysqli_query($conn,"SELECT tmpGoal,tmpresGoal,Team FROM current WHERE Name='".$str."'");
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode=1"))==0){
	echo("现在不是比赛时间！");
}
elseif(mysqli_num_rows($res)==0){
	echo("查无此人！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$str."' AND Team=''"))>0){
	echo("此人无主。");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$str."' AND tmpGoal+tmpresGoal>0"))==0){
	echo("本轮未提交此人进球。");
}
else{
	$team=mysqli_fetch_assoc($res)['Team'];
	if(inFirstTeam($str,mysqli_fetch_assoc(mysqli_query($conn,"SELECT Lineup FROM teams WHERE Abbr='".$team."'"))['Lineup'])){
		updateGoals($conn,$str,-1,"");
	}
	else{
		updateGoals($conn,$str,-1,"res");
	}
	writeLog("Delete ".$str."'s goal");
	echo("已撤销".$str."的进球");
}
mysqli_close($conn);
}
else{
	echo("没有权限");
}
?>
