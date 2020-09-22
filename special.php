<?php
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
//格式：球队名3+空格1+位置1+空格1+(4+1*2)+1+18+1+19+1+3
echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>去除意甲球员后的大名单</title>
</head>
<body>");
$seriea=array('Juventus', 'Napoli', 'Atalanta', 'Internazionale', 'Milan', 'Roma', 'Torino', 'Lazio', 'Sampdoria', 'Bologna','Sassuolo', 'Udinese', 'SPAL', 'Parma', 'Cagliari', 'Fiorentina', 'Genoa', 'Brescia', 'Lecce', 'Verona');
$teams=mysqli_query($conn,"SELECT Abbr,Fullname,Money,Managers FROM teams ORDER BY Abbr");
while($row=mysqli_fetch_assoc($teams)){//共16支球队，循环16次
	//先输出头部，格式为球队名3+空格1+人数2+“人”字2+空格35+“剩余资金”4字8+空格0-2+资金3-1
	$team=$row['Abbr'];
	$query=mysqli_query($conn,"SELECT Team,Pos,KeyinFML,Name,Club,Price FROM current WHERE Team='".$team."' ORDER BY field(Pos,'G','D','M','F')");
	$num=mysqli_num_rows($query);
	echo("<div>".$row['Fullname']);/*
	for($i=1;$i<=35;$i++)
		echo("&nbsp;");
	echo("剩余资金 ");
	if($row['Money']<10)
		echo("&nbsp;&nbsp;");
	elseif ($row['Money']<100) {
		echo("&nbsp;");
	}
	echo($row['Money']);*/
        //玩家ID占一行
	echo("</div><div>".$row['Managers']."</div>");
	$n=0;
	//输出该玩家拥有的球员，//格式：球队名3+空格1+位置1+空格1+游戏中编号4+“号”字2+空格1+（球员姓名+空格）19+（俱乐部名+空格）20+（价格+空格）3
	while($player=mysqli_fetch_assoc($query)){
		if(in_array($player['Club'],$seriea))
			continue;
		echo("<div>".$player['Team']." ".$player['Pos']." ".$player['KeyinFML']."号 ".$player['Name']);
		$len=strlen($player['Name']);
		for($i=$len;$i<=18;$i++){//用空格补足球员名
			echo("&nbsp;");
		}
		echo($player['Club']);
		$len=strlen($player['Club']);
		for($i=$len;$i<=19;$i++){//用空格补足俱乐部名
			echo("&nbsp;");
		}
		if($player['Price']<100)//价格必定大于等于10，因此只判断是否为三位数
			echo("&nbsp;");
		echo($player['Price']);
		echo("</div>");
		$n+=1;
	}
	echo("共".$n."人");
	//两个球队之间空一行
	echo("<p></p>");
}
echo("</body></html>");
mysqli_close($conn);
?>
