<?php
$db_ip="localhost";
$db_admin_username="admin";
$db_admin_password="";
$db_guest_username="guest";
$db_guest_password="";
$db_name="fml";
$db_port="3306";
$db_sock="/var/lib/mysql/mysql.sock";

function printBroadcast($conn,$name){
	echo("<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>".$name."</title>
</head>
<body>
	<div>一线队</div>");
//开始输出一线队直播帖
		doPrintBroadcast($conn);
		//开始输出首发阵容
		echo("首发阵容");
		printLineups($conn);
		//开始输出预备队比分，格式和一线队完全相同，除了队名小写以外
		echo("预备队");
		doPrintBroadcast($conn,"res");
	echo("</body></html>");
}
function doPrintBroadcast($conn,$str=""){
	//输出格式：球队1(排名)-空格*4-进球-空格*1-“-”号-空格*1-球队2进球-空格*4-球队2(排名)-空格*8-球队3(排名)-空格*4-进球-空格*1-“-”号-空格*1-球队4进球-空格*4-球队4(排名)-后面空一行
	if($str!="" && $str!="res"){
		echo("参数不对！");
		return;
	}
	for($i=0;$i<4;$i++){
			echo("<div>");
			$info1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT ".$str."Rank,Abbr,tmp".$str."Goal FROM teams WHERE tmpCode=".($i*4+1)));
			$info2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT ".$str."Rank,Abbr,tmp".$str."Goal FROM teams WHERE tmpCode=".($i*4+2)));
			printMatch($str,$info1,$info2);
			printWithFormat("",13,0);
			//echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$info3=mysqli_fetch_assoc(mysqli_query($conn,"SELECT ".$str."Rank,Abbr,tmp".$str."Goal FROM teams WHERE tmpCode=".($i*4+3)));
			$info4=mysqli_fetch_assoc(mysqli_query($conn,"SELECT ".$str."Rank,Abbr,tmp".$str."Goal FROM teams WHERE tmpCode=".($i*4+4)));
			printMatch($str,$info3,$info4);
			echo("</div>");
			//打印进球球员
			//待写，大概思路是用四个变量表示每个球队的进球球员数量，然后for循环打印球员，进球数及空格，直到所有数字都<=0
			printScoredPlayers($str,$conn,$i);
			echo("<p></p>");
		}
}
function printMatch($str,$info1,$info2){
	printAbbr($str,$info1["Abbr"]);
			echo("(");
			if($info1[$str."Rank"]<10)
				echo("0");
			echo($info1[$str."Rank"]);
			echo(")");
			echo("&nbsp;&nbsp;&nbsp;&nbsp;");
			echo($info1["tmp".$str."Goal"]);
			echo("&nbsp;-&nbsp;");
			echo($info2["tmp".$str."Goal"]);
			echo("&nbsp;&nbsp;&nbsp;&nbsp;");
			printAbbr($str,$info2['Abbr']);
			echo("(");
			if($info2[$str."Rank"]<10)
				echo("0");
			echo($info2[$str."Rank"]);
			echo(")");
}
function printAbbr($str,$echostr){
	if($str=="")
				echo($echostr);
			else
				echo(strtolower($echostr));
}
function printScoredPlayers($str,$conn,$i){//按照格式打印直播帖中的球员进球部分
	$array = array(array(),array(),array(),array());
	$maxlen=0;
	for($j=1;$j<=4;$j++){
		$team=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Abbr FROM teams WHERE tmpCode=".($i*4+$j)))['Abbr'];
		$query=mysqli_query($conn,"SELECT tmp".$str."Goal,Name FROM current WHERE Team='".$team."' AND tmp".$str."Goal>0");
		while($row=mysqli_fetch_assoc($query)){
			array_push($array[$j-1],$row['Name'],$row["tmp".$str."Goal"]);
		}
		if($maxlen<count($array[$j-1]))
			$maxlen=count($array[$j-1]);
	}
	for($j=0;$j<$maxlen;$j+=2){
		echo("<div>");
		for($k=0;$k<4;$k++){
			if($j>=count($array[$k]))
				$tmpstr="";
			else{
				$tmpstr=$array[$k][$j];
				if($array[$k][$j+1]>1)
					$tmpstr=$tmpstr."*".$array[$k][$j+1];
			}
			printWithFormat($tmpstr,20,0);
		}
		echo("</div>");
	}
	echo("<p></p>");
}
function inFirstTeam($player,$lineup){
	$array=explode(' ',str_replace("\"","",str_replace("/", " ", strtolower($lineup))));
	if(in_array(strtolower($player), $array))
		return true;
	return false;
}
function printLineups($conn){
	//数据库增加一个变量，表示是否沿用上轮阵容
	for($i=0;$i<8;$i++){
			$info1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Abbr,Lineup,isOldLineup FROM teams WHERE tmpCode='".($i*2+1)."'"));
			printOneLineup($info1);
			$info2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Abbr,Lineup,isOldLineup FROM teams WHERE tmpCode='".($i*2+2)."'"));
			printOneLineup($info2);
			echo("<p></p>");
		}
}
function printOneLineup($info1){
	//格式：球队名-空格或*-球队阵容
			echo("<div>");
			echo($info1['Abbr']);
			if($info1['isOldLineup']==1){//判断球队本轮有没有发阵容，没发则沿用上一轮阵容且在阵容前加*号
				echo("*");
			}
			else{//否则加空格
				echo(" ");
			}
			echo(str_replace("\"","",$info1['Lineup']));
			echo("</div>");
}
function checkCookie(){
	if(isset($_COOKIE['us_ern-ame']) && $_COOKIE['us_ern-ame']==md5('admin'))
		return true;
	return false;
}
function printFile($func,$conn,$str,$filename){
	ob_start();
	$func($conn,$str);
	$handle=fopen($filename,'w');
	$ob=ob_get_contents();
	fwrite($handle, $ob);
	fclose($handle);
	ob_end_clean();
}
function updateGoals($conn,$player,$n,$str=''){
	$team=mysqli_fetch_assoc(mysqli_query($conn,"SELECT tmpGoal,tmpresGoal,Team FROM current WHERE Name='".$player."'"))['Team'];
	$resultf=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."'"));
	//通过tmpCode找到本轮对手
	$tmpCode=$resultf['tmpCode'];
	$resulta=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode=".($tmpCode+1-2*(($tmpCode-1)%2))));
	$team2=$resulta['Abbr'];
	/*要更新的字段：
	teams:
	胜/平/负，积分，本轮进球
	current:
	本轮进球
	*/
		//判断比赛结果
		//除了之前进球差为0或-1外，其余情况不会导致胜负发生变化
	if($resultf['tmp'.$str.'Goal']+($n-1)/2==$resulta['tmp'.$str.'Goal']){
			mysqli_query($conn,"UPDATE teams SET ".$str."Win=".($resultf[$str.'Win']+$n).",".$str."Draw=".($resultf[$str.'Draw']-$n).",".$str."Points=".($resultf[$str.'Points']+2*$n).",tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n).",".$str."Goalfor=".($resultf[$str."Goalfor"]+$n)." WHERE Abbr='".$team."'");
			mysqli_query($conn,"UPDATE teams SET ".$str."Lose=".($resulta[$str.'Lose']+$n).",".$str."Draw=".($resulta[$str.'Draw']-$n).",".$str."Points=".($resulta[$str.'Points']-$n).",".$str."Goalagainst=".($resulta[$str."Goalagainst"]+$n)." WHERE Abbr='".$team2."'");
		}
		elseif($resultf["tmp".$str."Goal"]+($n+1)/2==$resulta["tmp".$str."Goal"]){
			mysqli_query($conn,"UPDATE teams SET ".$str."Lose=".($resultf[$str.'Lose']-$n).",".$str."Draw=".($resultf[$str.'Draw']+$n).",".$str."Points=".($resultf[$str.'Points']+$n).",tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n).",".$str."Goalfor=".($resultf[$str."Goalfor"]+$n)." WHERE Abbr='".$team."'");
			mysqli_query($conn,"UPDATE teams SET ".$str."Win=".($resulta[$str.'Win']-$n).",".$str."Draw=".($resulta[$str.'Draw']+$n).",".$str."Points=".($resulta[$str.'Points']-2*$n).",".$str."Goalagainst=".($resulta[$str."Goalagainst"]+$n)." WHERE Abbr='".$team2."'");
		}
		else{
			mysqli_query($conn,"UPDATE teams SET tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n).",".$str."Goalfor=".($resultf[$str."Goalfor"]+$n)." WHERE Abbr='".$team."'");
			mysqli_query($conn,"UPDATE teams SET ".$str."Goalagainst=".($resulta[$str."Goalagainst"]+$n)." WHERE Abbr='".$team2."'");
		}
	$nowgoal=mysqli_fetch_assoc(mysqli_query($conn,"SELECT tmp".$str."Goal FROM current WHERE Name='".$player."'"))["tmp".$str."Goal"]+$n;
	mysqli_query($conn,"UPDATE current SET tmp".$str."Goal=".$nowgoal." WHERE Name='".$player."'");
}
function printTopGoalscorers($conn,$name,$num1=0,$num2=0){
	echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>".$name."</title>
</head>
<body>
	<h2>一线队射手榜</h2>
	<table>
		<tr><th>球员</th><th>球队</th><th>进球</th></tr>");
	doPrintTopGoalScorers($conn,"",$num1);
	echo("</table>
		<p></p>
	<h2>预备队射手榜</h2>
	<table>
		<tr><th>球员</th><th>球队</th><th>进球</th></tr>");
	doPrintTopGoalScorers($conn,"res",$num2);
	echo("</table>
</body>
</html>");
}
function doPrintTopGoalscorers($conn,$str="",$num=0){
	$result=mysqli_query($conn,"SELECT Name,OwnerNum,Owner1,Owner2,Owner3,".$str."Goal,tmp".$str."Goal FROM current WHERE ".$str."Goal+tmp".$str."Goal>".$num." ORDER BY ".$str."Goal+tmp".$str."Goal DESC");
		while($row=mysqli_fetch_assoc($result)){
			echo("<tr><td>".$row['Name']."</td><td>");
			if($str==""){
				if($row['OwnerNum']==3){
					echo($row['Owner1']."&".$row['Owner2']."&".$row['Owner3']);
				}
				elseif ($row['OwnerNum']==2) {
					echo($row['Owner1']."&".$row['Owner2']);
				}
				else
					echo($row['Owner1']);
			}
			else{
				if($row['OwnerNum']==3){
					echo(strtolower($row['Owner1']."&".$row['Owner2']."&".$row['Owner3']));
				}
				elseif ($row['OwnerNum']==2) {
					echo(strtolower($row['Owner1']."&".$row['Owner2']));
				}
				else
					echo(strtolower($row['Owner1']));
			}
			echo("</td><td>".($row[$str.'Goal']+$row['tmp'.$str.'Goal'])."</td></tr>");
		}
}
function printLeagueTable($conn,$name){
	echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>".$name."</title>
</head>
<body>
	<h2>一线队积分榜</h2>
	<table>
		<tr><th>球队</th><th>排名</th><th>胜</th><th>平</th><th>负</th><th>进球</th><th>失球</th><th>积分</th><th>近期</th><th>排名变化</th></tr>");
		doPrintLeagueTable($conn);
	echo("</table>
	<h2>预备队积分榜</h2>
	<table>
		<tr><th>球队</th><th>排名</th><th>胜</th><th>平</th><th>负</th><th>进球</th><th>失球</th><th>积分</th><th>近期</th><th>排名变化</th></tr>");
		doPrintLeagueTable($conn,"res");
	echo("</table>
</body>
</html>");
}
function doPrintLeagueTable($conn,$str=""){
	$result=mysqli_query($conn,"SELECT Abbr,".$str."Rank,".$str."Win,".$str."Draw,".$str."Lose,".$str."Goalfor,".$str."Goalagainst,".$str."Points,pre".$str."Rank,".$str."charResult FROM teams ORDER BY ".$str."Rank");
		$n=1;
		while($row=mysqli_fetch_assoc($result)){
			if(strlen($row[$str."charResult"])<5){
				$charres=$row[$str.'charResult'];
			}
			else{
				$charres=substr($row[$str.'charResult'],-5);
				if(strspn($charres, "W")==5 || strspn($charres, "D")==5 || strspn($charres, "L")==5 ){
					$c=$charres[0];
					$num=0;
					$sum=strlen($row[$str.'charResult']);
					while($sum-1-$num>=0 && $row[$str.'charResult'][$sum-1-$num]==$c)
						$num=$num+1;
					$charres=$c." - ".$num;
				}
			}
			if($str=="")
			echo("<tr><td>".$row['Abbr']);
			else
			echo("<tr><td>".strtolower($row['Abbr']));
			echo("</td><td>".$n."</td><td>".$row[$str.'Win']."</td><td>".$row[$str.'Draw']."</td><td>".$row[$str.'Lose']."</td><td>".$row[$str.'Goalfor']."</td><td>".$row[$str.'Goalagainst']."</td><td>".$row[$str.'Points']."</td><td>".$charres."</td><td>".($row["pre".$str."Rank"]-$row[$str.'Rank'])."</td></tr>");
			$n=$n+1;
		}
}
function writeLog($str){
	$file=fopen("logs.txt", "a");
	fwrite($file,$str." at ".date('Y-m-d H:i:s',time()+8*3600)."\n");
	fclose($file);
}
function printFMLSquads($conn){
//格式：球队名3+空格1+位置1+空格1+(4+1*2)+1+18+1+19+1+3
$teams=mysqli_query($conn,"SELECT Abbr,Money,Managers FROM teams ORDER BY Abbr");
while($row=mysqli_fetch_assoc($teams)){//共16支球队，循环16次
	$team=$row['Abbr'];
	$query=mysqli_query($conn,"SELECT Team,Pos,KeyinFML,Name,Club,Price FROM current WHERE Team='".$team."' ORDER BY field(Pos,'G','D','M','F')");
	printOneFMLSquadHeader($row,mysqli_num_rows($query));
	while($player=mysqli_fetch_assoc($query))
		printOneFMLSquadPlayer($player);
	//两个球队之间空一行
	echo("<p></p>");
}
}
function printOneFMLSquadHeader($team,$num){
	//第一行，格式：球队名3+空格1+（人数+“人”字+空格）39+（“剩余资金”+空格）9+资金3
	echo("<div>".$team['Abbr']." ");
	printWithFormat($num."人",39,0);
	echo("剩余资金 ");
	printWithFormat($team['Money'],3,1);
	//玩家ID占一行
	echo("</div><div>".$team['Managers']."</div>");
}
function printOneFMLSquadPlayer($player){
	//输出该玩家拥有的球员，//格式：球队名3+空格1+位置1+空格1+游戏中编号4+“号”字2+空格1+（球员姓名+空格）19+（俱乐部名+空格）20+（价格+空格）3
	echo("<div>".$player['Team']." ".$player['Pos']." ".$player['KeyinFML']."号 ");
	printWithFormat($player['Name'],19,0);
	printWithFormat($player['Club'],20,0);
	printWithFormat($player['Price'],3,1);
	echo("</div>");
}
function printWithFormat($str,$num,$sign){
	$len=strlen($str);
	if($len>$num){
		echo(substr($str,0,$num));
	}
	else{
		if($sign==0)//左对齐
		echo($str);
		for($i=$len;$i<$num;$i++)
			echo("&nbsp;");
		if($sign==1)//右对齐
			echo($str);
	}
}
?>