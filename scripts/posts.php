<?php
// Read the password to the database
$pwdfile = fopen("scripts/password.pwd", "r") or die ("Cannot open password file.");
$pwd = fread($pwdfile, filesize("scripts/password.pwd"));
fclose($pwdfile);
$db = mysqli_connect("localhost", "pyth", $pwd, "website");
if (mysqli_connect_errno())
{
	die("Cannot connect to the database.");
}
// This variable is a default offset in case it doesn't get set by the code
$offset = 0;
// This variable is a default page number
$page = 1;
// Check if user changed browsing page
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

// Default max post count per page
$postcount = 20;

// Setting default variables, do NOT edit these
$issearch = false;
$ispost = false;
$searchbytags = false;
// Prepare default query to get posts
$query = "SELECT * FROM posts WHERE ispaid = 0 ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
// Default query if user is a paid user
if($isauth)
{
	$query = "SELECT * FROM posts ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
}

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
			$query = "SELECT * FROM posts WHERE ispaid = 0 AND id = " . $get_id;
			if($isauth)
			{
				$query = "SELECT * FROM posts WHERE id = " . $get_id;
			}
		}
	}
}

// Check if user is trying to search posts
else if(isset($_GET["searchquery"]))
{
	if($_GET["searchquery"] != "")
	{
		// Default search query
		$query = "SELECT * FROM posts WHERE ispaid = 0 AND title LIKE ? ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
		if($isauth)
		{
			$query = "SELECT * FROM posts WHERE title LIKE ? ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
		}
		// Check if user is trying to search by tags instead
		if(isset($_GET["searchtype"]))
		{
			if($_GET["searchtype"] == 1)
			{
				// User is trying to search by tags
				$searchbytags = true;
				// Split user string into tags that can be queried
				$querytags = explode(" ", $_GET["searchquery"]);
				// Get count of tags
				$querytagscount = sizeof($querytags);
				// Add a percent sign before and after the query to get all SQL matches and prevent SQL injection
				for($i=0; $i<$querytagscount; $i=$i+1)
				{
					$querytags[$i] = "%" . $querytags[$i] . "%";
				}
				// Kill the script if user tries to search too many tags
				if($querytagscount > 5)
				{
					die("Too many tags.");
				}
				// Default query
				$query = "SELECT * FROM posts WHERE ispaid = 0 AND tags LIKE ?";
				if($isauth)
				{
					$query = "SELECT * FROM posts WHERE tags LIKE ?";
				}
				// Alternate query, this is used to get count of posts for Page x out of y
				$querycount = "SELECT COUNT(id) FROM posts WHERE ispaid = 0 AND tags LIKE ?";
				if($isauth)
				{
					$querycount = "SELECT COUNT(id) FROM posts WHERE tags LIKE ?";
				}
				// Depending on user argument count, add enough 'AND tags' clauses
				for($i=2; $i<=$querytagscount; $i=$i+1)
				{
					$query = $query . " AND tags LIKE ?";
					$querycount = $querycount . " AND tags LIKE ?";
				}
				// Get count of elements with tags, sanitized
				$tagpostcount = mysqli_execute_query($db, $querycount, $querytags);
				// Prepare actual query
				$query = $query . " ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
			}
		}
		// User is in search mode, set flag to true
		$issearch = true;
	}
}

// Execute this query if user is searching by title
if($issearch && !$searchbytags)
{
	$res = mysqli_execute_query($db, $query, ["%" . $_GET["searchquery"] . "%"]);
}
// Execute this query if user is searching by tags
else if($searchbytags)
{
	$res = mysqli_execute_query($db, $query, $querytags);
}
// Execute this query if user is not in search mode
else
{
	$res = mysqli_query($db, $query);
}
// Structure of the database's table
# 0 - id
# 1 - title
# 2 - tags
# 3 - uploader
# 4 - date (YYYY-MM-DD HH:mm:ss)
# 5 - videourl
# 6 - imageurl
# 7 - ispaid

// Print retrieved posts
if($issearch)
{
	echo "<div class='searchquerytext'>";
	echo "Searching: \"";
	echo htmlspecialchars($_GET["searchquery"]);
	echo "\" by ";
	if($searchbytags)
	{
		echo "tags";
	}
	else
	{
		echo "title";
	}
	echo ".";
	echo "</div>";
}
while ($post = mysqli_fetch_array($res))
{
	echo "<div class='post'>";
	echo "<h3><a ";
	if($post[7])
	{
		echo "class='paidpost' ";
	}
	echo "href='?id=" . $post[0] . "'>";
	if($post[7])
	{
		echo "[PAID] ";
	}
	echo $post[1] . "</a></h3>";
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

// Count results and display Page x of y if user isn't browsing a single post
if(!$ispost)
{
	// Count query for proper displaying of Page x of y
	$query = "SELECT COUNT(id) FROM posts WHERE ispaid = 0";
	if($isauth)
	{
		$query = "SELECT COUNT(id) FROM posts";
	}
	if($issearch)
	{
		// Count query for proper displaying of Page x of y, if user is in search by title mode
		$query = "SELECT COUNT(id) FROM posts WHERE ispaid = 0 AND title LIKE ?";
		if($isauth)
		{
			$query = "SELECT COUNT(id) FROM posts WHERE title LIKE ?";
		}
		if($searchbytags)
		{
			// If user is in search by tags mode, we already have a mysqli object so we do not need to query the database
			$res = $tagpostcount;
		}
		else
		{
			// Executing query in search by title mode
			$res = mysqli_execute_query($db, $query, ["%" . $_GET["searchquery"] . "%"]);
		}
	}
	else
	{
		// This count query runs if user isn't in search mode
		$res = mysqli_query($db, $query);
	}
	// Getting post count
	$res = mysqli_fetch_array($res);
	$res = $res[0];
	// Get pages count
	$res = intdiv($res, $postcount) + 1;
	// Printing of Page x of y
	echo "<div class='pagecount'>";
	// If not the first page, print previous page button
	if($page > 1)
	{
		echo "<a href='?page=" . ($page - 1);
		// Add search query if searching
		if($issearch)
		{
			echo "&searchquery=" . $_GET["searchquery"];
			if($searchbytags)
			{
				// Add search by tags if searching by tags
				echo "&searchtype=1";
			}
		}
		echo "'><< </a>";
	}
	// Print Page x of y
	echo "Page " . $page . " out of " . $res;
	// If not the last page, print next page button
	if($page < $res)
	{
		echo "<a href='?page=" . ($page + 1);
		// Add search query if searching
		if($issearch)
		{
			echo "&searchquery=" . $_GET["searchquery"];
			if($searchbytags)
			{
				// Add search by tags if searching by tags
				echo "&searchtype=1";
			}
		}
		echo "'> >></a>";
	}
	echo "</div>";
}

// Close database
mysqli_close($db);
// Lazyload video poster script
echo "<script src='lazyload.js'></script>";
?>
