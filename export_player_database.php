<?php
include "FML.php";
if(checkCookie()){
header('Content-Type: application/vnd.ms-excel'); 
header('Content-Disposition: attachment;filename="database_current.csv"'); 
header('Cache-Control: max-age=0'); 
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
$fp=fopen("php://output", 'a');
$query=mysqli_query($conn,"SELECT * FROM current ORDER BY KeyinFML");
while($row=mysqli_fetch_assoc($query)){
	foreach ($row as $i => $v) {
		$row[$i]=iconv('utf-8', 'gbk', $v);
	}
	fputcsv($fp, $row);
}
mysqli_close($conn);
}
else{
	echo("没有权限");
}
?>
