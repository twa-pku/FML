<?php
include "FML.php";
//这个文件可以做成：在添加进球时查阅数据库，或在有人点击按钮时查阅
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode>0"))==0){//本轮比赛是否正在进行
	//若不正在进行，则输出已保存的最新积分榜
	$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT round FROM teams WHERE Rank=1"))['round'];
	echo("<script>location.href='History/league_table_".$round.".html';</script>");
}
//否则从数据库中输出积分榜
else{
	printLeagueTable($conn,"实时积分榜");
}
mysqli_close($conn);
?>
