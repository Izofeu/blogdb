<?php
if(!isadmin())
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
$query = "UPDATE posts SET title = ?, tags = ?, videourl = ?, imageurl = ?, ispaid = ? WHERE id = ?";
mysqli_execute_query($db, $query, [$_POST["edit_title"], $_POST["edit_tags"], $_POST["edit_videourl"], $_POST["edit_imageurl"], $paidpost, $_POST["edit_id"]]);
mysqli_close($db);
$posteditsuccess = true;
?>