<?php
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
$sign=mysqli_fetch_assoc(mysqli_query($conn,"SELECT MATCH_ON FROM status WHERE Activity='FML'"))['MATCH_ON'];
if($sign==1){
	echo("<script>alert('比赛正在进行！');history.back();</script>");
	return;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>直播贴生成器</title>
</head>
<body>
<h1>输入以下信息</h1>
<form action="" method="POST">
<p>球队1：  <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team1">    阵容1： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z- \/.\']/g,'');" name="squad1">    球队2： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team2">    阵容2： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad2"></p>
<p>球队3：  <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team3">    阵容3： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad3">    球队4： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team4">    阵容4： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad4"></p>
<p>球队5：  <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team5">    阵容5： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad5">    球队6： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team6">    阵容6： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad6"></p>
<p>球队7：  <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team7">    阵容7： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad7">    球队8： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team8">    阵容8： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad8"></p>
<p>球队9：  <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team9">    阵容9： <input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad9">    球队10：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team10">    阵容10：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad10"></p>
<p>球队11：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team11">    阵容11：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad11">    球队12：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team12">    阵容12：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad12"></p>
<p>球队13：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team13">    阵容13：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad13">    球队14：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team14">    阵容14：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad14"></p>
<p>球队15：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team15">    阵容15：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad15">    球队16：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" name="team16">    阵容16：<input type="text" oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" name="squad16"></p>

<input type="submit" name="check" value="检查拼写" formaction="Name_checker.php"><input type="submit" name="submit" value="提交直播帖" formaction="broadcast_submit.php" >
</form>
</body>
</html>
