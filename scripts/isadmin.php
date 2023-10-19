<?php
	function isadmin()
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
					return true;
				}
			}
		}
		return false;
	}
?>