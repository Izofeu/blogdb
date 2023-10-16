<?php
$pwdfile = fopen("scripts/password.pwd", "r") or die ("Cannot open password file.");
$pwd = fread($pwdfile, filesize("scripts/password.pwd"));
fclose($pwdfile);
$db = mysqli_connect("localhost", "pyth", $pwd, "website");
if (mysqli_connect_errno())
{
	die("Cannot connect to the database.");
}
$offset = 0;
$page = 1;
if(isset($_GET["page"]))
{
	if(is_numeric($_GET["page"]))
	{
		$get_page = intval($_GET["page"]);
		if($get_page > 0)
		{
			$page = $get_page;
			$offset = ($get_page * 20) - 20;
			if($offset < 0)
			{
				$offset = 0;
			}
		}
	}
}

$postcount = 20;

$ispost = false;
$query = "SELECT * FROM posts ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
if(isset($_GET["id"]))
{
	if(is_numeric($_GET["id"]))
	{
		$get_id = intval($_GET["id"]);
		if($get_id > 0)
		{
			$ispost = true;
			$query = "SELECT * FROM posts WHERE id = " . $get_id;
		}
	}
}

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
	echo "<h3><a href='?id=" . $post[0] . "'>" . $post[1] . "</a></h3>";
	echo "<video controls name='media' preload='none' class='lazy videostags' data-poster='" . $post[6] . "'>";
	echo "<source src='" . $post[5] . "' type='video/mp4'>";
	echo "</video>";
	echo "<div class='hfiller20px'>";
	echo "</div>";
	echo "<div class='horizontal_line'>";
	echo "</div>";
	echo "<div class='subtext'>";
	echo "<div>Posted by " . $post[3] . " at " . $post[4] . ".</div>";
	echo "<div>Tags: " . $post[2] . "</div>";
	echo "</div>";
	echo "</div>";
}


if(!$ispost)
{
	$query = "SELECT COUNT(id) FROM posts";
	$res = mysqli_query($db, $query);
	$res = mysqli_fetch_array($res);
	$res = $res[0];
	$res = intdiv($res, $postcount) + 1;
	echo "<div class='pagecount'>";
	if($page > 1)
	{
		echo "<a href='?page=" . ($page - 1) . "'><< </a>";
	}
	echo "Page " . $page . " out of " . $res;
	if($page < $res)
	{
		echo "<a href='?page=" . ($page + 1) . "'> >></a>";
	}
	echo "</div>";
}


mysqli_close($db);
echo "<script src='lazyload.js'></script>";
?>
