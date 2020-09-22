	<?php
	include "FML.php";
	$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
	$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT round FROM teams WHERE Rank=1"))['round'];
	//非比赛时间所有tmpcode都是0
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode>0"))==0){
		echo("<script> location.href='History/FMLlive_".$round.".html'; </script>");
	}
	else{
		printBroadcast($conn,"第".($round+1)."轮实时直播贴");
}
	mysqli_close($conn);
	?>
