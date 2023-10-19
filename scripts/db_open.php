<?php
function db_open()
{
	$pwdfile = fopen("scripts/password.pwd", "r") or die ("Cannot open password file.");
	$pwd = fread($pwdfile, filesize("scripts/password.pwd"));
	fclose($pwdfile);
	$db = mysqli_connect("localhost", "pyth", $pwd, "website");
	if (mysqli_connect_errno())
	{
		die("Cannot connect to the database.");
	}
	return $db;
}
?>