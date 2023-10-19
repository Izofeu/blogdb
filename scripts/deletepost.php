<?php
	if(isadmin())
	{
		$db = db_open();
		$query = "DELETE FROM posts WHERE id = ?";
		mysqli_execute_query($db, $query, [$_POST["postdelete_id"]]);
		mysqli_close($db);
		$postdeletesuccess = true;
	}
?>