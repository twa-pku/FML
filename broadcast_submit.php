<?php
include "FML.php";
if(checkCookie()){
	//这个文件的任务有两个：填写数据库中所有和一轮比赛相关的字段（清理上一轮的信息已经在submit_round文件中做了），和输出BBS风格的直播帖以便复制粘贴到版面
	$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
		//要写入数据库的信息：每个球队的对手，每个球队的首发，每个球队的当轮编号，输出BBS风格的直播帖
		//开始更新球队数据
		mysqli_query($conn,"START TRANSACTION");
		for($i=1;$i<=16;$i++){
			$team1=$_POST["team".$i];
			//初始化teams数据库信息，目前所有球队都是0-0平局，平局数+1，积分+1
			//更新本轮对手，临时号
			//临时号标定了球队在直播帖上的位置，之后会用来确定球队本轮对手
			$draw1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Draw,resDraw,Points,resPoints FROM teams WHERE Abbr='".$team1."'"));
			mysqli_query($conn,"UPDATE teams SET Opponent='".$_POST["team".($i+1-2*(($i-1)%2))]."',tmpCode=".$i.",Draw=".($draw1['Draw']+1).",resDraw=".($draw1['resDraw']+1).",Points=".($draw1['Points']+1).",resPoints=".($draw1['resPoints']+1)." WHERE Abbr='".$team1."'");
			//开始更新首发阵容
			$lineup1=$_POST["squad".$i];
			if($lineup1==""){//判断球队本轮有没有发阵容，没发则字符串为空，那么沿用上一轮阵容
				mysqli_query($conn,"UPDATE teams SET isOldLineup=1 WHERE Abbr='".$team1."'");
			}
			else{//否则更新数据库中的阵容
				mysqli_query($conn,"UPDATE teams SET Lineup='".$lineup1."',isOldLineup=0 WHERE Abbr='".$team1."'");
			}
		}
		//更新status数据库，宣布比赛开始
		mysqli_query($conn,"UPDATE status SET LAST_MODIFIED=".time().",MATCH_ON=1 WHERE Activity='FML'");
		mysqli_query($conn,"COMMIT");
		$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Round FROM teams WHERE Rank=1"))["Round"]+1;
		printBroadcast($conn,$round);
		mysqli_close($conn);
	//在logs中写入比赛开始信息
	writeLog("Start round");
}
else{
	echo("没有权限");
}
?>
