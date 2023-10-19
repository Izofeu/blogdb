<?php
	$domain = "nutsuki.fun";
	
	# Authentication for paid posts
	$isauth = false;
	$authfile = fopen("scripts/auth.pwd", "r") or die ("Cannot open password file.");
	$auth = fread($authfile, filesize("scripts/auth.pwd"));
	fclose($authfile);
	if(isset($_COOKIE["auth"]))
	{
		if($_COOKIE["auth"] == $auth)
		{
			$isauth = true;
			setcookie("auth", $auth, time() + (86400 * 7), "/", $domain, false, true);
		}
	}
	else if(isset($_POST["auth"]))
	{
		$authsuccessful = false;
		if($_POST["auth"] == $auth)
		{
			$isauth = true;
			$authsuccessful = true;
			setcookie("auth", $auth, time() + (86400 * 7), "/", $domain, false, true);
		}
	}
	
	# Admin login
	$showadminui = false;
	if(isset($_POST["logout"]))
	{
		setcookie("user", null, 1, "/", $domain, true, true);
		setcookie("password", null, 1, "/", $domain, true, true);
	}
	else if(isset($_COOKIE["user"]))
	{
		setcookie("user", $_COOKIE["user"], time() + (86400 * 7), "/", $domain, true, true);
		if(isset($_COOKIE["password"]))
		{
			setcookie("password", $_COOKIE["password"], time() + (86400 * 7), "/", $domain, true, true);
			$adminusername = $_COOKIE["user"];
			$showadminui = true;
		}
	}
	else if(isset($_POST["user"]) && isset($_POST["password"]))
	{
		$pwdfile = fopen("scripts/password.pwd", "r") or die ("Cannot open password file.");
		$pwd = fread($pwdfile, filesize("scripts/password.pwd"));
		fclose($pwdfile);
		$db = mysqli_connect("localhost", "pyth", $pwd, "website");
		if (mysqli_connect_errno())
		{
			die("Cannot connect to the database.");
		}
		$query = "SELECT * FROM admins WHERE username = ?";
		$res = mysqli_execute_query($db, $query, [$_POST["user"]]);
		$res = mysqli_fetch_array($res);
		if($res)
		{
			$pres = password_verify($_POST["password"], $res[1]);
			if($pres)
			{
				setcookie("user", $_POST["user"], time() + (86400 * 7), "/", $domain, true, true);
				setcookie("password", $res[1], time() + (86400 * 7), "/", $domain, true, true);
				$adminusername = $_POST["user"];
				$showadminui = true;
			}
		}
		mysqli_close($db);
	}
?>