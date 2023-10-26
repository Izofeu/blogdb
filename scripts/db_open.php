<?php
function db_open()
{
	global $db_username, $db_url, $db_name, $db_password_file_path;
	$pwdfile = fopen($db_password_file_path, "r") or die ("Cannot open password file.");
	$pwd = fread($pwdfile, filesize($db_password_file_path));
	fclose($pwdfile);
	$db = mysqli_connect($db_url, $db_username, $pwd, $db_name);
	if (mysqli_connect_errno())
	{
		die("Cannot connect to the database.");
	}
	return $db;
}
?>