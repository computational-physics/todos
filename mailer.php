<?php 
session_start();
$t_user = $_SESSION['app_user_name'];
if ($t_user == 'Junfan' && isset($_POST['user'])) {
	$t_user = $_POST['user'];
} 
$jsonStr = $_POST['todo'];
$username = $t_user;
$filepath = 'tmp/' . md5($jsonStr) . '.json';
file_put_contents($filepath, $jsonStr);
$command = "/usr/bin/python /var/www/html/apps/todos/sendmail.py -u $username -f $filepath";
$result = shell_exec($command);
file_put_contents('mail.log', $command . '
', FILE_APPEND);
echo $result;
// unlink($filepath);
?>