<?php
	// permlevels:
	// 1 - insert posts
	// 2 - edit own posts
	// 4 - delete own posts
	// 8 - edit all posts
	// 16 - delete all posts
	
	// Users MUST have "own" permissions in order for "all" permissions to work properly!
	
	// isadmin is a safe function, meaning you can rely on its return value that
	// the user has permissions
	
	$db = db_open();
	$query = "SELECT * FROM admins";
	$res = mysqli_query($db, $query);
	$rowres = mysqli_fetch_all($res);
	mysqli_close($db);
	
	function isadmin($permlevel)
	{
		global $rowres;
		$res = $rowres;
		if(isset($_COOKIE["user"]) && isset($_COOKIE["password"]))
		{
			//mysqli_data_seek($res, 0);
			foreach($res as $resarr)
			{
				if($_COOKIE["user"] == $resarr[0])
				{
					$pres = $_COOKIE["password"] == $resarr[1];
					if($pres)
					{
						return $permlevel & $resarr[2];
					}
					return false;
				}
			}
		}
		return false;
	}
?>