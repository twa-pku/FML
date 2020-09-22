<?php
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$type=mysqli_real_escape_string($conn,$_GET["type"]);//按照什么查询？目前支持按照球员名，游戏中编号，俱乐部名和FML球队名查询
$str=mysqli_real_escape_string($conn,$_GET["str"]);//查询字符串。支持模糊查询

$sql="SELECT * FROM current WHERE ".$type." LIKE '%".$str."%' ORDER BY KeyinFML LIMIT 100";
$result=mysqli_query($conn,$sql);
//在一个表格中打印所有符合条件的球员
if(mysqli_num_rows($result)>0){
	echo("<table border='1'>");
	echo("<tr>"."<th>"."Name"."</th>"."<th>"."Pos"."</th>"."<th>"."Club"."</th>"."<th>"."Number"."</th>"."<th>"."KeyinFML"."</th>"."<th>"."Team"."</th>"."<th>"."Price"."</th>"."</tr>");
	while($row=mysqli_fetch_assoc($result)){
		echo("<tr>"."<td>".$row['Name']."</td>"."<td>".$row['Pos']."</td>"."<td>".$row['Club']."</td>"."<td>".$row['Number']."</td>"."<td>".$row['KeyinFML']."</td>"."<td>".$row['Team']."</td>");
		echo("<td>".$row['Price']."</td>");
		echo("</tr>");
	}
	echo("</table>");
}
else{
	echo "没有对应的结果。<br>";
}
mysqli_close($conn);
?>
