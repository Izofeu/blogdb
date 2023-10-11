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
while ($post = mysqli_fetch_array($res))
{
	echo "<div class='post'>";
	echo "<p>" . $post[1];
	echo "</div>";
}
?>