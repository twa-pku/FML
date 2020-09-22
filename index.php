<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>FML site</title>
	<script>var flag=0;</script>
</head>
<body>
	<!--a href="readme.html">页面使用帮助</a-->	
	<span id="welcome" style='float:right'>欢迎，admin	<input type="button" value="退出" onclick="logout()"></span>
	<span id="login" style='float:right'><a href='login.php'>登录</a></span>
	<?php
	include "FML.php";
	$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
	if(checkCookie()){
		echo("<script> flag=1;</script>");
	}
	$result=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='FML'"));
	if($result['MATCH_ON']==1){
		$stamp=$result['LAST_MODIFIED'];
		$player=$result['LAST_SCORED_PLAYER'];
		$time=date('Y-m-d H:i:s',$stamp+8*3600);
		echo("<span style='float:middle;'>比赛正在进行，最后更新于");
		echo($time);
		echo("，进球者");
		echo($player);
		echo("</span>");
	}
	else{
		echo("<span text-align=center;>比赛已结束</span>");
	}
	mysqli_close($conn);
	?>
	<span>查看第<input type="text" oninput="this.value=this.value.replace(/[^0-9]/g,'');" id="round">轮<select id="historyType"><option value="FMLlive_">直播帖</option><option value="league_table_">积分榜</option><option value="top_goalscorers_">射手榜</option></select><input type="button" value="查看" onclick="showHistory(document.getElementById('round').value,document.getElementById('historyType').options[document.getElementById('historyType').selectedIndex].value)"></span>（由于网站从第16轮开始搭建，本赛季只提供16轮以后的数据）
	<div id="admin">
	<h2>暗标管理</h2>
	<span><a href="#" onclick="anbiaohandler(document.getElementById('anbiaotime').value,document.getElementById('turns').value)">开标</a><input type='text' placeholder='暗标轮次，如l1,c1' id='anbiaotime'>，<input type='text' size='100' placeholder='暗标顺序' id='turns'></span></p>
	<h2>转会窗管理</h2>
	<p>转会请在此进行：<input type='text' id='Team1' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');" placeholder='请输入第一支球队'><input type='text' size='30' id='Player1' oninput="this.value=this.value.replace(/[^a-zA-Z,-.\']/g,'');" placeholder='请输入相应球员名，用“,”隔开'><input type='text' id='Team2' oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" placeholder='请输入第二支球队'><input type='text' id='Player2' size='30' oninput="this.value=this.value.replace(/[^a-zA-Z,-.\']/g,'');" placeholder='请输入相应球员名，用“,”隔开'><input type='text' id='transfermoney' size='25' oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder='请输入金额,无资金交换填0'><input type='button' value='提交' onclick="Transfer(document.getElementById('Player1').value,document.getElementById('Team1').value,document.getElementById('Player2').value,document.getElementById('Team2').value,document.getElementById('transfermoney').value)">（其中第一支球队付出资金）</p>
	<p>自由签请在此进行：<input type='text' id='Teamfreesign' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');"  placeholder='请输入球队'><input type='text' id='Playerfreesign' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" placeholder='请输入相应球员名'><input type='text' id='PlayerMoney' oninput="this.value=this.value.replace(/[^0-9]/g,'');" value='10'><input type='button' value='提交' onclick="Playerfreesign(document.getElementById('Playerfreesign').value,document.getElementById('Teamfreesign').value,document.getElementById('PlayerMoney').value)"></p>
	<p>解约请在此进行：<input type='text' id='Teamrelease' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');" placeholder='请输入球队'><input type='text' id='Playerrelease' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" placeholder='请输入相应球员名'><input type='button' value='提交' onclick="Playerrelease(document.getElementById('Playerrelease').value,document.getElementById('Teamrelease').value)"></p>
	<h2>比赛管理</h2>
	<p><span><input type='button' value='生成直播帖' onclick="(function(){if(confirm('确认？')) window.open('broadcast_creator.php');})()">		撤销进球球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undoscoredPlayer'><input type='button' value='撤销' onclick="undoscoredPlayer(document.getElementById('undoscoredPlayer').value)"></span></p>
	<p><b>请在本轮所有比赛结束后执行！执行之前请确认已录入所有进球球员！此操作无法撤回！</b><span><input type='button' value='结束当前比赛' onclick="(function(){if(confirm('确认？')) window.open('submit_round.php');})()"></span></p>
	<h2>导出数据库</h2>
	<input type='button' value='导出球员数据库' onclick="window.open('export_player_database.php')"><input type='button' value='导出球队数据库' onclick="window.open('export_team_database.php')">
</p></div>
	<h1>最新放送</h1>
		<p><a href='draw.php'>抽签测试</a></p>
	<h2>实时赛况</h2>
	<p>	提交进球球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='scoredPlayer' placeholder='输入格式：球员+当前进球数，如Messi2' size='40'><input type='button' value='提交' onclick="submitscoredPlayer(document.getElementById('scoredPlayer').value)">	<span><a href="broadcast_real_time.php">查看实时直播帖</a></span>		<span><a href="league_table_real_time.php">查看实时积分榜</a></span>		<span><a href="#" onclick="gettopGoalscorers(document.getElementById('firstteamgoalnum').value,document.getElementById('subgoalnum').value)">查看实时射手榜</a>（显示一线队进球超过<input type='number' value='0' id='firstteamgoalnum'>个，预备队进球超过<input type='number' value='0' id='subgoalnum'>个的球员）</span></p>
	<h2>查询</h2>
	<div>按<select id="searchType"><option value="Name">球员名</option><option value="KeyinFML">球员编号</option><option value="Club">球队名</option><option value="Team">FML球队名</option></select>查询：			<input size="100" type="text" id="searchName" oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" placeholder="请输入球员名、球队名、编号等..."> <input type="button" onclick="getName(document.getElementById('searchType').options[document.getElementById('searchType').selectedIndex].value,document.getElementById('searchName').value)" value="查询"></div>
	<div id="showresult"></div>
	<div id="testresult"></div>
	<!--h2>精华文章</h2>
	<p>待添加</p-->
	<!--h2>玩家介绍</h2>
	<p>看大家有没有兴趣往这里面加内容吧……</p-->
	<h2>导出文件</h2>
	<input type="button" value="导出球员名单" onclick="window.open('export_current.php')"><input type="button" value="查看玩家阵容" onclick="window.open('export_the_list.php')">
	<h2>常用链接</h2>
	<a href="http://www.footballsquads.co.uk/squads.htm">Footballsquads</a>				<a href="https://www.whoscored.com/">Whoscored</a>				<a href="https://www.transfermarkt.co.uk/">Transfermarkt</a>				<a href="https://www.betinf.com">Sports Betting Information</a>				<a href="https://fantasy.premierleague.com/">英超Fantasy</a>
<script type="text/javascript">
	function showHistory(round,type){
		if(round=="")
			alert("请输入轮次");
		else
		window.open("History/"+type+round+".html");
	}
</script>
<script type="text/javascript">
        function gettopGoalscorers(num1,num2){
                if(num1=="" || num2=="")
                        alert("请输入数字");
                else
                window.open("shooters.php?num1="+num1+"&num2="+num2);
        }
</script>
<script type="text/javascript">
        function anbiaohandler(str,turns){
                window.open("anbiaohandler.php?suffix="+str+"&turns="+turns);
        }
</script>
<script type="text/javascript">
	function logout(){
		document.cookie="us_ern-ame=";
		window.location.href="index.php";
	}
</script>
<script type="text/javascript">/*
		var mycookie="";
		var strcookie=document.cookie;
		if(strcookie.length>0){
		var arrcookie=strcookie.split(";");
		for(var i=0;i<arrcookie.length;i++){
			var arr=arrcookie[i].split("=");
			if(arr[0]=="us_ern-ame")
				mycookie=arr[1];
		}*/
		if(flag==1){
			document.getElementById("login").style.display="none";
			document.getElementById("guest").style.display="none";
		}
		else{
			document.getElementById("welcome").style.display="none";
			document.getElementById("admin").style.display="none";
		}
	/*}
	else{
			document.getElementById("welcome").style.display="none";
			document.getElementById("admin").style.display="none";
		}*/
	</script>
<script type="text/javascript">
	function undoscoredPlayer(str){
		if(str==""){
				alert("输入为空。");
			}
		else if(confirm("确定撤销"+str+"的进球吗？")){
			var http;
			if(window.XMLHttpRequest){
				http=new XMLHttpRequest();
			}
			else{
				http=new ActiveXObject("Microsoft.XMLHTTP");
			}
			http.onreadystatechange=function(){
				if(http.readyState==4 && http.status==200){
					alert(http.responseText);
				}
			}
			http.open("GET","undoscoredPlayer.php?&str="+str,true);
			http.send();
			}
	}
</script>
<script type="text/javascript">
	function submitscoredPlayer(str){
		if(str==""){
				alert("输入为空。");
			}
		else if(confirm("确定添加"+str+"的进球吗？")){
			var http;
			if(window.XMLHttpRequest){
				http=new XMLHttpRequest();
			}
			else{
				http=new ActiveXObject("Microsoft.XMLHTTP");
			}
			http.onreadystatechange=function(){
				if(http.readyState==4 && http.status==200){
					alert(http.responseText);
					//location.reload();
				}
			}
			http.open("GET","submitscoredPlayer.php?&str="+str,true);
			http.send();
			}
	}
</script>
<script>
	function getName(type,str){
		var http;
		if(str==""){
			document.getElementById("showresult").innerHTML="输入为空。";
			return;
		}
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				document.getElementById("showresult").innerHTML=http.responseText;
			}
		}
		http.open("GET","getName.php?type="+type+"&str="+str,true);
		http.send();
	}
</script>
<script type="text/javascript">
	function Playerrelease(player,team){
		if(confirm("确认 "+team+" 解约 "+player+" 吗？")){
		var http;
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				alert(http.responseText);
			}
		}
		http.open("GET","release.php?player="+player+"&team="+team,true);
		http.send();
	}
	}
</script>
<script type="text/javascript">
	function Playerfreesign(player,team,money){
		if(confirm("确认 "+team+" 自由签入 "+player+" 吗？")){
		var http;
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				alert(http.responseText);
			}
		}
		http.open("GET","freesign.php?player="+player+"&team="+team+"&money="+money,true);
		http.send();
	}
	}
</script>
<script type="text/javascript">
	function Transfer(player1,team1,player2,team2,money){
		if(confirm("确认要转会吗？")){
		var http;
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				alert(http.responseText);
			}
		}
		http.open("GET","transfer.php?player1="+player1+"&team1="+team1+"&player2="+player2+"&team2="+team2+"&money="+money,true);
		http.send();
	}
	}
</script>
</body>
</html>
