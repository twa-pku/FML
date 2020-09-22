<?php
include "FML.php";
include "/home/FML-server/Classes/PHPExcel/IOFactory.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$working_folder="/home/FML-server/anbiao/";
$working_array=array();
//l1顺序 $teams=array("VIK","XER","LYF","LEI","JUV","NLP","ESP","CPU","TMM","HSV","JIU","MCG","IMA","TFE","LIN","WTF");
//l2顺序 $teams=array("XER","LIN","JIU","NLP","LYF","CPU","MCG","JUV","ESP","LEI","VIK","TFE","HSV","IMA","WTF","TMM");
$position=array("G","D","M","F");
$suffix=$_GET['suffix'];
$teams=explode(' ',$_GET['turns']);

function my_array_search($str,$array){
    for($i=0;$i<count($array);$i++){
        if($array[$i]==$str)
        return $i;
    }
    return -1;
}

function print_team_array($array){
    echo("<div>");
    printWithFormat($array[7],4,0);
    printWithFormat($array[3],2,0);
    printWithFormat($array[0],3,0);
    printWithFormat($array[6],5,0);
    printWithFormat($array[2],19,0);    
    printWithFormat($array[4],20,0);
    printWithFormat($array[1],3,0);
    echo("</div>");
}

//根据球员价格-位置-顺位排序
function sortbyprice($array1,$array2){
    global $position;
    if($array1[1]<$array2[1])
    return -1;
    elseif($array1[1]>$array2[1])
    return 1;
    else{
        if(my_array_search($array1[3],$position)<my_array_search($array2[3],$position))
        return -1;
        elseif(my_array_search($array1[3],$position)>my_array_search($array2[3],$position))
        return 1;
        else{
            if($array1[0]<$array2[0])
            return 1;
            elseif($array1[0]>$array2[0])
            return -1;
        }
    }
}

function checkanbiao($array){
    global $conn;
    $r=array();
    if(count($array)==0)
    return $r;
    $team=$array[0][7];
    $gksign=10;
    if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Pos='G' AND Team='".$team."'"))==0)
    $gksign=0;
    $pricesum=0;
    $money=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Money FROM teams WHERE Abbr='".$team."'"))['Money'];
    $playernum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Team='".$team."'"));
    $p=0;
    //需要检查的情况：球员名被改了，球队已经签约过该球员，该球员已经被签约过三次，没有足够的钱签门将/凑足人数下限，钱爆了，人爆了
    for(;$p<count($array);$p++){
        if($array[$p][3]=="G")
        $gksign=10;
        if(count($r)+$playernum>=22 || $pricesum+$array[$p][1]>$money-10+$gksign || $pricesum+$array[$p][1]+(8-(count($r)+$playernum))*10>$money)
    break;
    //情况：这个编号不存在或已经有主；这个球员已经效力过3个球队；这个球员被这个球队买过；
    if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE KeyinFML='".$array[$p][6]."' AND Team=''"))==0 || mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE KeyinFML='".$array[$p][6]."' AND OwnerNum<3"))==0 || mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE KeyinFML='".$array[$p][6]."' AND (Owner1='".$team."' OR Owner2='".$team."' OR Owner3='".$team."')"))>0){
        echo("entr");
        continue;
    }
        $pricesum+=$array[$p][1];
        array_push($r,$array[$p]);
    }
    return $r;
}

//
    ob_start();
    echo("
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>球员暗标结果</title>
    </head>
    <body>");
for($i=0;$i<16;$i++){
    $file=$working_folder.$teams[$i].$suffix.".xlsx";
    if(!file_exists($file)){
        continue;
    }
    $filetype=PHPExcel_IOFactory::identify($file);
    $reader=PHPExcel_IOFactory::createReader($filetype);
    $excel=$reader->load($file);
    $sheet=$excel->getSheet(0);
    $maxcol='G';
    $maxrow=$sheet->getHighestRow();

    $teamanbiao=array();

    for($row=2;$row<=$maxrow;$row++){
        $tmp=array();
        for($col='A';$col<=$maxcol;$col++){
            $cell=$col.$row;
            $value=$sheet->getCell($cell)->getValue();
            if($value=="0"){
            break;
            }
            array_push($tmp,$value);
        }
        if(count($tmp)>0){
            array_push($tmp,$teams[$i]);
            array_push($teamanbiao,$tmp);
        }
    }

    //检查人员，钱数
    usort($teamanbiao,"sortbyprice");
    $teamanbiao=checkanbiao($teamanbiao);

    //输出每个队的暗标
    usort($teamanbiao,"sortbyteam");
for($j=0;$j<count($teamanbiao);$j++){
    print_team_array($teamanbiao[$j]);
}
echo("<p></p>");

    $working_array=array_merge($working_array,$teamanbiao);
}
echo("</body></html>");
$handle=fopen("History/team_anbiao_".$suffix.".html","w");
$ob=ob_get_contents();
fwrite($handle, $ob);
fclose($handle);
ob_end_clean();

//排序函数
function sortbyname($array1,$array2){
    global $teams;
    if($array1[2]<$array2[2])
    return -1;
    elseif($array1[2]>$array2[2])
    return 1;
    else{
        if($array1[1]<$array2[1])
        return 1;
        elseif($array1[1]>$array2[1])
        return -1;
        else{
            if($array1[0]<$array2[0])
            return -1;
            elseif($array1[0]>$array2[0])
            return 1;
            else{
                if(my_array_search($array1[7],$teams)<my_array_search($array2[7],$teams))
                return -1;
                else
                return 1;
            }
        }
    }
}

function sortbyteam($array1,$array2){
    global $position;
    if($array1[7]<$array2[7])
    return -1;
    elseif($array1[7]>$array2[7])
    return 1;
    else{
        if(my_array_search($array1[3],$position)<my_array_search($array2[3],$position))
        return -1;
        elseif(my_array_search($array1[3],$position)>my_array_search($array2[3],$position))
        return 1;
        else{
            if($array1[0]<$array2[0])
            return -1;
            elseif($array1[0]>$array2[0])
            return 1;
        }
    }
}

//根据球员姓名-价格-编号-发帖时间排序暗标
usort($working_array,"sortbyname");

function print_working_array($array){
    echo("<div>");
    printWithFormat($array[0],3,0);
    printWithFormat($array[1],4,0);
    printWithFormat($array[2],19,0);
    printWithFormat($array[3],2,0);    
    printWithFormat($array[4],20,0);
    printWithFormat($array[5],3,0);
    printWithFormat($array[6],5,0);
    printWithFormat($array[7],3,0);
    echo("</div>");
}

//输出每个球员的暗标结果，并去除重复的球员
ob_start();
$result_array=array();
$name="";
echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>球员暗标结果</title>
</head>
<body>");
for($i=0;$i<count($working_array);$i++){
    if($name!=$working_array[$i][2]){
        echo("<p></p>");
        $name=$working_array[$i][2];
        echo("<div>".$name."</div>");
        array_push($result_array,$working_array[$i]);
    }
    print_working_array($working_array[$i]);
}
echo("</body></html>");
$handle=fopen("History/player_result_".$suffix.".html","w");
$ob=ob_get_contents();
fwrite($handle, $ob);
fclose($handle);
ob_end_clean();

//根据球队-位置-编号排序暗标
usort($result_array,"sortbyteam");

//写入数据库
for($i=0;$i<count($result_array);$i++){
    mysqli_query($conn,"UPDATE current SET Team='".$result_array[$i][7]."',Price=".$result_array[$i][1].",OwnerNum=(SELECT OwnerNum FROM current WHERE KeyinFML='".$result_array[$i][6]."')+1 WHERE KeyinFML='".$result_array[$i][6]."'");
	mysqli_query($conn,"UPDATE teams SET Money=(SELECT Money FROM teams WHERE Abbr='".$result_array[$i][7]."')-".$result_array[$i][1]." WHERE Abbr='".$result_array[$i][7]."'");//调整money
	$res=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owner1,Owner2,Owner3 FROM current WHERE KeyinFML='".$result_array[$i][6]."'"));
	if($res['Owner1']==""){
		mysqli_query($conn,"UPDATE current SET Owner1='".$result_array[$i][7]."' WHERE KeyinFML='".$result_array[$i][6]."'");
	}
	elseif($res['Owner2']==""){
		mysqli_query($conn,"UPDATE current SET Owner2='".$result_array[$i][7]."' WHERE KeyinFML='".$result_array[$i][6]."'");
	}
	elseif($res['Owner3']==""){
		mysqli_query($conn,"UPDATE current SET Owner3='".$result_array[$i][7]."' WHERE KeyinFML='".$result_array[$i][6]."'");
    }
    $player=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Name FROM current WHERE KeyinFML='".$result_array[$i][6]."'"))['Name'];
	//在日志中记录签约
	writeLog($result_array[$i][7]." sign ".$player);
}

function print_result_array($array){
    echo("<div>");
    printWithFormat($array[7],4,0);
    printWithFormat($array[3],2,0);
    printWithFormat($array[6],5,0);
    printWithFormat($array[2],19,0);    
    printWithFormat($array[4],20,0);
    printWithFormat($array[1],4,0);
    echo("</div>");
}

//打印暗标结果
ob_start();
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
$handle=fopen("History/team_result_".$suffix.".html","w");
$ob=ob_get_contents();
fwrite($handle, $ob);
fclose($handle);
ob_end_clean();

mysqli_close($conn);

echo("
<!DOCTYPE html>
<html>
<head>
	<title>已完成导入</title>
</head>
<body>
	<p>球员投标情况已保存在<a href='History/player_result_".$suffix.".html'>链接</a></p>
	<p>球队暗标已保存在<a href='History/team_anbiao_".$suffix.".html'>链接</a></p>
	<p>暗标结果已保存在<a href='History/team_result_".$suffix.".html'>链接</a></p>
	<a href='index.php'>回到首页</a>
</body>
</html>");
?>