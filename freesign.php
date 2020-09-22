<?php
include "FML.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
if(checkCookie()){
$team=mysqli_real_escape_string($conn,strtoupper($_GET['team']));
$player=mysqli_real_escape_string($conn,$_GET['player']);
$money=mysqli_real_escape_string($conn,$_GET['money']);

//自由签不合规的条件列举
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."'"))==0){
	echo("球队输入错误。");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."'"))==0){
	echo("查无此人");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND Team=''"))==0){
	echo("该球员已有主。");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Team='".$team."'"))>=22) {
 	echo($team."已满22人！");
}
elseif(!is_numeric($money)){
	echo("请输入正确的金额");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."' AND Money>=".$money))==0) {
 	echo($team."没有足够的资金！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND OwnerNum<3"))==0){
	echo($player."已经被签约三次！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND (Owner1='".$team."' OR Owner2='".$team."' OR Owner3='".$team."')"))>0){
	echo($player."已经被".$team."签约过！");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."' AND Money>=".$money+10))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Team='".$team."' AND Pos='G'"))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND Pos='G'"))==0) {
	echo($team."没有足够的资金再签下门将！");
}
//否则，自由签有效，更新数据库
else{
	mysqli_query($conn,"UPDATE current SET Team='".$team."',Price=".$money.",OwnerNum=(SELECT OwnerNum FROM current WHERE Name='".$player."')+1 WHERE Name='".$player."'");
	mysqli_query($conn,"UPDATE teams SET Money=(SELECT Money FROM teams WHERE Abbr='".$team."')-".$money." WHERE Abbr='".$team."'");//调整money
	$res=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owner1,Owner2,Owner3 FROM current WHERE Name='".$player."'"));
	if($res['Owner1']==""){
		mysqli_query($conn,"UPDATE current SET Owner1='".$team."' WHERE Name='".$player."'");
	}
	elseif($res['Owner2']==""){
		mysqli_query($conn,"UPDATE current SET Owner2='".$team."' WHERE Name='".$player."'");
	}
	elseif($res['Owner3']==""){
		mysqli_query($conn,"UPDATE current SET Owner3='".$team."' WHERE Name='".$player."'");
	}
	//在日志中记录签约
	writeLog($team." sign ".$player);
	echo($player."已加入".$team."。");
}
mysqli_close($conn);
}
else{
	echo("没有权限");
	mysqli_close($conn);
}
?>
