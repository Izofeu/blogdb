<?php
	$domain = "nutsuki.fun";
	
	# Authentication for paid posts
	$isauth = false;
	$auth = "123";
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
?>