<?php
	// returns 0 on success, 1 if user can only delete own posts,
	// 2 if user cannot delete posts at all and 3 if post doesn't exist
	if(!isadmin(4) && !isadmin(16))
	{
		$postdeletesuccess = 2;
		return;
	}
	$db = db_open();
	if(!isadmin(16))
	{
		$query = "SELECT uploader FROM posts WHERE id = ?";
		$res = mysqli_execute_query($db, $query, [$_POST["postdelete_id"]]);
		if($res)
		{
			$res = mysqli_fetch_array($res);
			$res = $res[0];
			if($res != $_COOKIE["user"])
			{
				$postdeletesuccess = 1;
				mysqli_close($db);
				return;
			}
		}
		else
		{
			$postdeletesuccess = 3;
			mysqli_close($db);
			return;
		}
	}
	$query = "DELETE FROM posts WHERE id = ?";
	mysqli_execute_query($db, $query, [$_POST["postdelete_id"]]);
	mysqli_close($db);
	$postdeletesuccess = 0;
?>