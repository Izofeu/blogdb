<?php
$db = db_open();

$ispost = false;
$textpost = false;
// Check if user is attempting to access a single post page
if(isset($_GET["id"]))
{
	if(is_numeric($_GET["id"]))
	{
		$get_id = intval($_GET["id"]);
		if($get_id > 0)
		{
			// Single post mode
			$ispost = true;
			$query = "SELECT * FROM posts WHERE id = " . $get_id;
			if(isset($_GET["textpost"]))
			{
				$textpost = true;
				$query = "SELECT * FROM textposts WHERE id = " . $get_id;
			}
		}
	}
}
if($ispost && !$textpost)
{
	$res = mysqli_query($db, $query);
	$res = mysqli_fetch_array($res);
	if($res)
	{
		// Structure of the database's table
		# 0 - id
		# 1 - title
		# 2 - tags
		# 3 - uploader
		# 4 - date (YYYY-MM-DD HH:mm:ss)
		# 5 - videourl
		# 6 - imageurl
		# 7 - ispaid
		echo "<meta property='og:title' content='";
		if($res[7])
		{
			echo "[PAID] ";
		}
		echo htmlspecialchars($res[1]) . "'>";
		echo "<meta property='og:image' content='" . htmlspecialchars($res[6]) . "'>";
		echo "<meta property='og:url' content='https://" . $domain . "/index.php?id=" . $res[0] . "'>";
		echo "<meta property='og:description' content='A frontend for watching Nutsuki&#39;s videos. This video has the following tags: " . htmlspecialchars($res[2]) . ".'>";
		echo "<meta property='description' content='A frontend for watching Nutsuki&#39;s videos. This video has the following tags: " . htmlspecialchars($res[2]) . ".'>";
		echo "<title>" . htmlspecialchars($res[1]) . " (" . $res[0] . ") - " . htmlspecialchars($pagename) . "</title>";
	}
}
else if($textpost)
{
	$res = mysqli_query($db, $query);
	$res = mysqli_fetch_array($res);
	if($res)
	{
		echo "<meta property='og:title' content='" . htmlspecialchars($res[2]) . "'>";
		echo "<meta property='og:url' content='https://" . $domain . "/index.php?textpost&id=" . $res[0] . "'>";
		echo "<meta property='og:description' content='A frontend for watching Nutsuki&#39;s videos.'>";
		echo "<meta property='description' content='A frontend for watching Nutsuki&#39;s videos.'>";
		echo "<title>" . htmlspecialchars($res[2]) . " - " . htmlspecialchars($pagename) . "</title>";
	}
}
else
{
	echo "<meta property='og:title' content='Nutsuki Suu&#39;s archive'>";
	echo "<meta property='og:url' content='https://" . $domain . "'>";
	echo "<meta property='og:description' content='A frontend for watching Nutsuki&#39;s videos.'>";
	echo "<meta property='description' content='A frontend for watching Nutsuki&#39;s videos.'>";
	echo "<title>" . htmlspecialchars($pagename) . "</title>";
}

mysqli_close($db)
?>