<?php
$pwdfile = fopen("scripts/password.pwd", "r") or die ("Cannot open password file.");
$pwd = fread($pwdfile, filesize("scripts/password.pwd"));
fclose($pwdfile);
$db = mysqli_connect("localhost", "pyth", $pwd, "website");
if (mysqli_connect_errno())
{
	die("Cannot connect to the database.");
}
$query = "SELECT * FROM posts ORDER BY id DESC LIMIT 2";
$res = mysqli_query($db, $query);
# 0 - id
# 1 - title
# 2 - tags
# 3 - uploader
# 4 - date (YYYY-MM-DD HH:mm:ss)
# 5 - videourl
# 6 - imageurl
# 7 - ispaid
while ($post = mysqli_fetch_array($res))
{
	echo "<div class='post'>";
	echo "<h3><a href='https://www.nutsuki.fun/post.php?id=" . $post[0] . "'>" . $post[1] . "</a></h3>";
	echo "<video controls name='media' preload='none' class='lazy videostags' data-poster='" . $post[6] . "'>";
	echo "<source src='" . $post[5] . "' type='video/mp4'>";
	echo "</video>";
	echo "<div class='horizontal_line'>";
	echo "</div>";
	echo "<div class='subtext'>";
	echo "<div>Posted by " . $post[3] . " at " . $post[4] . ".</div>";
	echo "<div>Tags: " . $post[2] . "</div>";
	echo "</div>";
	echo "</div>";
}

echo "<script src='lazyload.js'></script>";
?>
