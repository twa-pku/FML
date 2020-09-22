<?php
include "FML.php";
//if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,$_GET["str"]);
$len=strlen($str);
$num=$str[$len-1];
if(is_numeric($num) && (int)$num>0){
	$str=substr($str,0,$len-1);
	$num=(int)$num;
}
else{
	$num=1;
}
//判断一下条件
$res=mysqli_query($conn,"SELECT tmpGoal,tmpresGoal,Team FROM current WHERE Name='".$str."'");
$resfetch=mysqli_fetch_assoc($res);
$team=$resfetch['Team'];
$tmpGoal=$resfetch['tmpGoal']+$resfetch['tmpresGoal'];
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode=1"))==0){
	echo("现在不是比赛时间！");
}
elseif(mysqli_num_rows($res)==0){
	echo("查无此人！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$str."' AND Team=''"))>0){
	echo("此人无主。");
}
elseif($num!=$tmpGoal+1){
	echo("输入的进球数不对。");
}
//没问题，则开始更新数据库
else{
	mysqli_query($conn,"UPDATE status SET LAST_MODIFIED=".time().",LAST_SCORED_PLAYER='".$str."' WHERE Activity='FML'");
	if(inFirstTeam($str,mysqli_fetch_assoc(mysqli_query($conn,"SELECT Lineup FROM teams WHERE Abbr='".$team."'"))['Lineup'])){//一线队或预备队
		updateGoals($conn,$str,1);
		writeLog("Add ".$str."'s goal to ".$team);
		echo("已添加".$str."到".$team);
	}
	else{
		updateGoals($conn,$str,1,"res");
		writeLog("Add ".$str."'s goal to ".strtolower($team));
		echo("已添加".$str."到".strtolower($team));
	}
}
mysqli_close($conn);
/*}
else{
	echo("没有权限");
}*/
?>
