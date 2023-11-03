<?php
	// permlevels:
	// 1 - insert posts
	// 2 - edit own posts
	// 4 - delete own posts
	// 8 - edit all posts
	// 16 - delete all posts
	function isadmin($permlevel)
	{
		if(isset($_COOKIE["user"]) && isset($_COOKIE["password"]))
		{
			$db = db_open();
			$query = "SELECT * FROM admins WHERE username = ?";
			$res = mysqli_execute_query($db, $query, [$_COOKIE["user"]]);
			$res = mysqli_fetch_array($res);
			mysqli_close($db);
			if($res)
			{
				$pres = $_COOKIE["password"] == $res[1];
				if($pres)
				{
					return $permlevel & $res[2];
				}
			}
		}
		return false;
	}
?>