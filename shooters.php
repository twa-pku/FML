<?php
include "FML.php";
$num1=$_GET['num1'];
$num2=$_GET['num2'];
if($num1<0)
$num1=0;
if($num2<0)
$num2=0;
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
//这个函数是实时的，考虑到玩家对于实时射手榜的要求可能不是很高
//$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT round FROM teams WHERE Rank=1"))['round'];
//echo("<script>location.href='History/top_goalscorers_".$round.".html';</script>");
//未来可以做成只显示进球数最多的一部分球员
//未来可能可以做成实时的
printTopGoalScorers($conn,"实时射手榜",$num1,$num2);
	mysqli_close($conn);
?>
