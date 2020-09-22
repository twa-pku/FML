<?php
include "FML.php";
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
die('Could not connect: ' . mysqli_error($conn));
}
echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>最新大名单</title>
</head>
<body>");
printFMLSquads($conn);
echo("</body></html>");
?>
