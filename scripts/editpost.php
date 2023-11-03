<?php
if(!isadmin(2) && !isadmin(8))
{
	die("Not an admin.");
}
if(!is_numeric($_POST["edit_id"]))
{
	die("Unknown error.");
}
$db = db_open();
$paidpost = 0;
if(isset($_POST["edit_ispaid"]))
{
	$paidpost = 1;
}
if(!isadmin(8))
{
	$query = "SELECT uploader FROM posts WHERE id = ?";
	$res = mysqli_execute_query($db, $query, [$_POST["edit_id"]]);
	if($res)
	{
		$res = mysqli_fetch_array($res);
		$res = $res[0];
		if($res != $_COOKIE["user"])
		{
			$posteditsuccess = false;
			mysqli_close($db);
			return;
		}
	}
}
$query = "UPDATE posts SET title = ?, tags = ?, videourl = ?, imageurl = ?, ispaid = ? WHERE id = ?";
mysqli_execute_query($db, $query, [$_POST["edit_title"], $_POST["edit_tags"], $_POST["edit_videourl"], $_POST["edit_imageurl"], $paidpost, $_POST["edit_id"]]);
mysqli_close($db);
$posteditsuccess = true;
?>