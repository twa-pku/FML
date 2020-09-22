<?php
include "FML.php";
if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
//检查是否正在进行该轮
$match_on=mysqli_fetch_assoc(mysqli_query($conn,"SELECT MATCH_ON FROM status WHERE Activity='FML'"))['MATCH_ON'];
if($match_on==0){
	echo("<script>alert('没有比赛正在进行！');window.close();</script>");
	return;
}

/*要做的事情：
teams：
将之前的排名保存到prerank。
将所有球队的一线队进球和其对手进球比较，得到临时积分和结果。		更新直播帖，并另存为文件。
更新积分，轮次，战绩字符串。将积分排序，得到排名。			更新积分榜并另存为文件。
给每个球队发钱。
将临时进球，预备队进球，tmpcode，临时积分清零。
current:
更新每个球员的一线队/预备队进球。		更新射手榜，并另存为文件。
将临时进球清零。
status:
结束这一轮。
*/

//首先更新teams数据库中除了最新排名外的其它元素，并输出直播帖保存。一线队和预备队格式相同
$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Round FROM teams WHERE Rank=1"))['Round']+1;
printFile("printBroadcast",$conn,"第".$round."轮双线直播帖&首发阵容","/home/FML-server/History/FMLlive_".$round.".html");
	for($i=0;$i<8;$i++){
		$res1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode=".(1+2*$i)));
		$res2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode=".(2+2*$i)));
		$team1=$res1['Abbr'];
		$team2=$res2['Abbr'];
		//上面是输出直播帖，下面开始更新数据库
		//根据结果更新近期结果字符串
		if($res1['tmpGoal']>$res2['tmpGoal']){
			$charres1=$res1['charResult']."W";
			$charres2=$res2['charResult']."L";
		}
		elseif($res1['tmpGoal']<$res2['tmpGoal']){
			$charres1=$res1['charResult']."L";
			$charres2=$res2['charResult']."W";
		}
		else{
			$charres1=$res1['charResult']."D";
			$charres2=$res2['charResult']."D";
		}
		//更新轮次，进球丢球，积分，此前排名及钱
		mysqli_query($conn,"UPDATE teams SET Round=".$round.",Money=".$res1['Money']."+(".$res2['tmpGoal']."*5),preRank=".$res1['Rank'].",charResult='".$charres1."' WHERE Abbr='".$team1."'");
		mysqli_query($conn,"UPDATE teams SET Round=".$round.",Money=".$res2['Money']."+(".$res1['tmpGoal']."*5),preRank=".$res2['Rank'].",charResult='".$charres2."' WHERE Abbr='".$team2."'");
		if($res1['tmpresGoal']>$res2['tmpresGoal']){
			$charres1=$res1['rescharResult']."W";
			$charres2=$res2['rescharResult']."L";
		}
		elseif($res1['tmpresGoal']<$res2['tmpresGoal']){
			$charres1=$res1['rescharResult']."L";
			$charres2=$res2['rescharResult']."W";
		}
		else{
			$charres1=$res1['rescharResult']."D";
			$charres2=$res2['rescharResult']."D";
		}
		mysqli_query($conn,"UPDATE teams SET preresRank=".$res1['resRank'].",rescharResult='".$charres1."' WHERE Abbr='".$team1."'");
		mysqli_query($conn,"UPDATE teams SET preresRank=".$res2['resRank'].",rescharResult='".$charres2."' WHERE Abbr='".$team2."'");
	}
//以下清空teams数据库的临时数据，并更新排名
$result=mysqli_query($conn,"SELECT Abbr FROM teams ORDER BY Points DESC,Goalfor DESC,Goalagainst DESC");
$n=1;
while($array=mysqli_fetch_assoc($result)){
	mysqli_query($conn,"UPDATE teams SET Rank=".$n.",tmpCode=0,tmpGoal=0,tmpresGoal=0 WHERE Abbr='".$array['Abbr']."'");
	$n=$n+1;
}
$resresult=mysqli_query($conn,"SELECT Abbr FROM teams ORDER BY resPoints DESC,resGoalfor DESC,resGoalagainst DESC");
$n=1;
while($array=mysqli_fetch_assoc($resresult)){
	mysqli_query($conn,"UPDATE teams SET resRank=".$n." WHERE Abbr='".$array['Abbr']."'");
	$n=$n+1;
}
//输出本轮积分榜
printFile("printLeagueTable",$conn,"第".$round."轮积分榜","/home/FML-server/History/league_table_".$round.".html");
//下面更新current数据库并输出射手榜
$tmpscoredplayer=mysqli_query($conn,"SELECT Name,Goal,resGoal,tmpGoal,tmpresGoal,Team FROM current WHERE tmpGoal+tmpresGoal>0");
$query=mysqli_query($conn,"SELECT Abbr,Lineup FROM teams");
$lineup=array();
//每个队首发阵容，导入一个大列表中
while($res=mysqli_fetch_assoc($query)){
	$lineup[$res['Abbr']]=explode(" ",str_replace("\"","",str_replace("/", " ", $res['Lineup'])));
}
//对每个球员，判断其临时进球是一线队还是预备队的，并更新进球
while($res=mysqli_fetch_assoc($tmpscoredplayer)){
	if(in_array($res['Name'], $lineup[$res['Team']])){
		mysqli_query($conn,"UPDATE current SET Goal=".$res['Goal']."+".$res['tmpGoal']." WHERE Name='".$res['Name']."'");
		mysqli_query($conn,"UPDATE current SET tmpGoal=0 WHERE Name='".$res['Name']."'");
	}
	else{
		mysqli_query($conn,"UPDATE current SET resGoal=".$res['resGoal']."+".$res['tmpresGoal']." WHERE Name='".$res['Name']."'");
		mysqli_query($conn,"UPDATE current SET tmpresGoal=0 WHERE Name='".$res['Name']."'");
	}
}
//导出射手榜
printFile("printTopGoalscorers",$conn,"第".$round."轮射手榜","/home/FML-server/History/top_goalscorers_".$round.".html");
//设置status数据库的状态，表示比赛结束
mysqli_query($conn,"UPDATE status SET MATCH_ON=0,LAST_SCORED_PLAYER=NULL WHERE Activity='FML'");
mysqli_close($conn);
writeLog("Submit round");
echo("<script> alert('已完成！'); </script>");
//输出一个很简单的网页，供查看结果
echo("
<!DOCTYPE html>
<html>
<head>
	<title>已完成导入</title>
</head>
<body>
	<p>直播帖已保存在<a href='History/FMLlive_".$round.".html'>链接</a></p>
	<p>积分榜已保存在<a href='History/league_table_".$round.".html'>链接</a></p>
	<p>射手榜已保存在<a href='History/top_goalscorers_".$round.".html'>链接</a></p>
	<a href='index.php'>回到首页</a>
</body>
</html>");
}
else{
	echo("<script>alert('没有权限！');window.close();</script>");
}
?>
