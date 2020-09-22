<?php
include "FML.php";
//用RSA解密，再与数据库储存的md5形式的密码对比
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,file_get_contents("php://input"));
$arr=explode("&",$str);
$arr1=explode("=",$arr[0]);
$arr2=explode("=",$arr[1]);
$username=$arr1[1];
$password=$arr2[1];
//$username=$_POST["user"];
//$password=$_POST['password'];
$password=base64_decode(str_replace(" ", "+", $password));
$query=mysqli_query($conn,"SELECT password FROM users WHERE username='".$username."'");
if(mysqli_num_rows($query)!=1){
	echo("用户名不存在");
	return;
}
$password_sql=mysqli_fetch_assoc($query)['password'];
$privatekey='';
$privatekey=openssl_pkey_get_private($privatekey);
openssl_private_decrypt($password, $password_de, $privatekey);
if(md5($password_de)==$password_sql){
	setcookie('us_ern-ame',md5($username),time()+7200);
	echo("1");
}
else{
	echo("密码错误");
}
?>
