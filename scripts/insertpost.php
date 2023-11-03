<?php
	if(!isadmin(1))
	{
		$postinsertsuccess = false;
		return;
	}
	# 0 - id
	# 1 - title
	# 2 - tags
	# 3 - uploader
	# 4 - date (YYYY-MM-DD HH:mm:ss)
	# 5 - videourl
	# 6 - imageurl
	# 7 - ispaid
	$paidpost = 0;
	if(isset($_POST["insert_ispaid"]))
	{
		$paidpost = 1;
	}
	$db = db_open();
	$date = date("Y-m-d H-i-s");
	$query = "INSERT INTO posts (title, tags, uploader, date, videourl, imageurl, ispaid) VALUES ("
			. "?, ?, ?, ?, ?, ?, ?)";
	mysqli_execute_query($db, $query, [$_POST["insert_title"], $_POST["insert_tags"], $_COOKIE["user"], $date, $_POST["insert_videourl"], $_POST["insert_imageurl"], $paidpost]);
	mysqli_close($db);
	$postinsertsuccess = true;
?>