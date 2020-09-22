<?php
header('Content-Type: application/vnd.ms-excel'); 
header('Content-Disposition: attachment;filename="Players19-20.csv"'); 
header('Cache-Control: max-age=0'); 
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
$fp=fopen("php://output", 'a');
$row=array("Name","Pos","Club","Number","KeyinFML","Team","Price");//只向玩家导出球员数据库的名字、位置、俱乐部、号码、游戏中编号、游戏中所属球队和游戏中价格字段
fputcsv($fp, $row);
$query=mysqli_query($conn,"SELECT Name,Pos,Club,Number,KeyinFML,Team,Price FROM current WHERE Club<>'' ORDER BY KeyinFML");
//排除数据库中已经转出的球员，这些球员对于更新射手榜有用，但是不应该被玩家看到
while($row=mysqli_fetch_assoc($query)){
	foreach ($row as $i => $v) {
		$row[$i]=iconv('utf-8', 'gbk', $v);
	}
	fputcsv($fp, $row);
}
mysqli_close($conn);
?>
