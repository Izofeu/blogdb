<?php
	$domain = "nutsuki.fun";
	
	# Cookie notice cookie
	if(isset($_COOKIE["cookienoticeviewed"]))
	{
		setcookie("cookienoticeviewed", 1, time() + (86400 * 7), "/", $domain, false, false);
	}
	# Authentication for paid posts
	$isauth = false;
	$authfile = fopen("scripts/auth.pwd", "r") or die ("Cannot open password file.");
	$auth = fread($authfile, filesize("scripts/auth.pwd"));
	fclose($authfile);
	
	if(isset($_POST["auth"]))
	{
		$authsuccessful = false;
		if($_POST["auth"] == $auth)
		{
			$isauth = true;
			$authsuccessful = true;
			setcookie("auth", $auth, time() + (86400 * 7), "/", $domain, false, true);
		}
	}
	else if(isset($_COOKIE["auth"]))
	{
		if($_COOKIE["auth"] == $auth)
		{
			$isauth = true;
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
		$db = db_open();
		$query = "SELECT * FROM admins WHERE username = ?";
		$res = mysqli_execute_query($db, $query, [$_POST["user"]]);
		$res = mysqli_fetch_array($res);
		$loginsuccessful = false;
		if($res)
		{
			$pres = password_verify($_POST["password"], $res[1]);
			if($pres)
			{
				setcookie("user", $_POST["user"], time() + (86400 * 7), "/", $domain, true, true);
				setcookie("password", $res[1], time() + (86400 * 7), "/", $domain, true, true);
				setcookie("auth", $auth, time() + (86400 * 7), "/", $domain, false, true);
				$adminusername = $_POST["user"];
				$showadminui = true;
				$isauth = true;
				$loginsuccessful = true;
				header("refresh: 2;");
			}
		}
		mysqli_close($db);
	}
	$analyticsenabledcookie = true;
	if(isset($_POST["toggleanalytics"]))
	{
		if(isset($_COOKIE["googleanalyticsdisabled"]))
		{
			$analyticsenabledcookie = true;
			setcookie("googleanalyticsdisabled", null, 1, "/", $domain, false, false);
		}
		else
		{
			$analyticsenabledcookie = false;
			setcookie("googleanalyticsdisabled", 1, time() + (86400 * 7), "/", $domain, false, false);
		}
	}
	else if(isset($_COOKIE["googleanalyticsdisabled"]))
	{
		setcookie("googleanalyticsdisabled", 1, time() + (86400 * 7), "/", $domain, false, false);
		$analyticsenabledcookie = false;
	}
?>