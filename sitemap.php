<?php
require("scripts/config.php");
require("scripts/db_open.php");
$db = db_open();
$query = "SELECT id FROM posts WHERE ispaid = 0";
$res = mysqli_query($db, $query);
while($id = mysqli_fetch_array($res))
{
	echo "https://" . $domain . "/index.php?id=" . $id[0] . "\n";
}
mysqli_close($db);

?>