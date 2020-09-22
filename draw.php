<?php
$teams=array("CPU","ESP","HSV","IMA","JIU","JUV","LEI","LIN","LYF","MCG","NLP","TFE","TMM","VIK","WTF","XER");
for($i=15;$i>=0;$i--){
    $randnum=rand(0,$i);
    $tmp=$teams[$randnum];
    $teams[$randnum]=$teams[$i];
    $teams[$i]=$tmp;
}
for($i=0;$i<16;$i++){
    echo($teams[$i]." ".$i."<br>");
}
?>