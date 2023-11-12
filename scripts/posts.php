<?php
$db = db_open();
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

// This variable is responsible for displaying No posts found notice.
$nopost = true;
$issearch = false;
$ispost = false;
$searchbytags = false;
$textpost = false;
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
			// Does the user request a text post rather than a video post?
			if(isset($_GET["textpost"]))
			{
				$textpost = true;
				$query = "SELECT * FROM textposts WHERE id = " . $get_id;
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

// Print retrieved posts query text
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

// Did the user try to delete a post?
if(isset($postdeletesuccess))
{
	echo "<div class='searchquerytext'>";
	switch($postdeletesuccess)
	{
		// 0 - Successful deletion (doesn't guarantee the database has been altered, only guarantees user had permission to attempt it)
		case 0:
		{
			echo "Post deleted successfully.";
			break;
		}
		// 1 - Account can only delete its own posts
		case 1:
		{
			echo "Failure deleting post. Your account can only delete your own posts.";
			break;
		}
		// 2 - Account cannot delete posts at all
		case 2:
		{
			echo "Your account doesn't have permission to delete posts.";
			break;
		}
		case 3:
		{
			echo "Post does not exist.";
			break;
		}
	}
	echo "</div>";
	
}

// Did the user try to edit a post?
if(isset($posteditsuccess))
{
	echo "<div class='searchquerytext'>";
	if($posteditsuccess)
	{
		echo "Post edited successfully.";
	}
	else
	{
		echo "Failure editing post. Your account can only edit your own posts.";
	}
	echo "</div>";
}

// Has the user successfully inserted a post?
if(isset($postinsertsuccess))
{
	echo "<div class='searchquerytext'>";
	echo "Post added successfully.";
	echo "</div>";
}

// Is user trying to edit a valid post?
if($ispost && isset($_GET["postedit"]) && !$textpost)
{
	// Mark the page as containing a post
	$nopost = false;
	// Check if user can edit posts at all
	if(!isadmin(2) && !isadmin(8))
	{
		echo "Your account does not have permission to edit posts.";
	}
	else
	{
		$res = mysqli_fetch_array($res);
		if($res)
		{
			// Continue flag
			$continue = true;
			if(!isadmin(8))
			{
				if($res[3] != $_COOKIE["user"])
				{
					// Refuse continuation of this if, if the user doesn't have the required permission
					$continue = false;
					echo "Your account can only edit your own posts.";
				}
			}
			// Only continue if user has the required permission
			if($continue)
			{
				echo "<div class='post'>";
				echo "<div class='subtext'>";
				echo "Editing post " . htmlspecialchars($res[1]) . ":";
				echo "</div>";
				echo "<form action='index.php' method='post'>";
				echo "<input type='hidden' name='edit_id' value='" . htmlspecialchars($res[0]) . "'>";
				echo "<table class='tableedit'>";
				echo "<tr>";
					echo "<th class='rowedit headerrowtable'>Parameter</th>";
					echo "<th class='rowedit'>Value</th>";
				echo "</tr>";
				echo "<tr>";
					echo "<td class='rowedit parameter'>Title</td>";
					echo "<td class='rowedit'><textarea maxlength='80' class='textfield editfield' name='edit_title'>" . htmlspecialchars($res[1]) . "</textarea></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td class='rowedit parameter'>Tags</td>";
					echo "<td class='rowedit'><textarea maxlength='80' class='textfield editfield' name='edit_tags'>" . htmlspecialchars($res[2]) . "</textarea></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td class='rowedit parameter'>Video url</td>";
					echo "<td class='rowedit'><textarea maxlength='256' class='textfield editfield' name='edit_videourl'>" . htmlspecialchars($res[5]) . "</textarea></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td class='rowedit parameter'>Thumbnail url</td>";
					echo "<td class='rowedit'><textarea maxlength='256' class='textfield editfield' name='edit_imageurl'>" . htmlspecialchars($res[6]) . "</textarea></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td class='rowedit parameter'>Is paid</td>";
					echo "<td class='rowedit'><input type='checkbox' class='checkbox' name='edit_ispaid' ";
					if($res[7])
					{
						echo "checked='checked'";
					}
					echo "'></td>";
				echo "</tr>";
				echo "</table>";
				echo "<input type='submit' class='button' value='Save changes'>";
				echo "</form>";
				echo "</div>";
			}
		}
	}
}
// If the user is not trying to edit a valid post, did they log in?
else if(isset($loginsuccessful))
{
	echo "<div class='searchquerytext'>";
	// User tried to log in, disable the no posts found notice
	$nopost = false;
	if($loginsuccessful)
	{
		echo "Welcome back, " . htmlspecialchars($_POST["user"]) . ". Refreshing in 2 seconds..";
	}
	else
	{
		echo "Invalid credentials.";
	}
	echo "</div>";
}// If the user hasn't tried to log in, are they trying to insert a post?
else if(isset($_POST["insertpost"]))
{
	$nopost = false;
	if(!isadmin(1))
	{
		echo "Your account doesn't have permission to insert posts.";
	}
	else
	{
		echo "<div class='post'>";
		echo "<div class='subtext'>";
		echo "Adding a post:";
		echo "</div>";
		echo "<form action='index.php' method='post'>";
		echo "<table class='tableedit'>";
		echo "<tr>";
			echo "<th class='rowedit headerrowtable'>Parameter</th>";
			echo "<th class='rowedit'>Value</th>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='rowedit parameter'>Title</td>";
			echo "<td class='rowedit'><textarea maxlength='80' class='textfield editfield' name='insert_title'></textarea></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='rowedit parameter'>Tags</td>";
			echo "<td class='rowedit'><textarea maxlength='80' class='textfield editfield' name='insert_tags'></textarea></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='rowedit parameter'>Video url</td>";
			echo "<td class='rowedit'><textarea maxlength='256' class='textfield editfield' name='insert_videourl'></textarea></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='rowedit parameter'>Thumbnail url</td>";
			echo "<td class='rowedit'><textarea maxlength='256' class='textfield editfield' name='insert_imageurl'></textarea></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td class='rowedit parameter'>Is paid</td>";
			echo "<td class='rowedit'><input type='checkbox' class='checkbox' name='insert_ispaid'></td>";
		echo "</tr>";
		echo "</table>";
		echo "<input type='submit' class='button' value='Submit post'>";
		echo "</form>";
		echo "</div>";
	}
}
// If the user isn't trying to insert a post, are they trying to display a text post?
else if(!$textpost)
{
	// User isn't trying to display a text post, display all posts that match the user's search criteria and/or page (if provided).
	while($post = mysqli_fetch_array($res))
	{
		// At least one post was found, disable the no posts found notice
		$nopost = false;
		// Display the posts
		echo "<div class='post'>";
		echo "<h3><a ";
		if($post[7])
		{
			echo "class='paidpost' ";
		}
		echo "href='?id=" . htmlspecialchars($post[0]) . "'>";
		if($post[7])
		{
			echo "[PAID] ";
		}
		echo htmlspecialchars($post[1]) . "</a></h3>";
		echo "<video controls name='media' preload='none' class='lazy videostags' data-poster='" . htmlspecialchars($post[6]) . "'>";
		echo "<source src='" . htmlspecialchars($post[5]) . "' type='video/mp4'>";
		echo "</video>";
		echo "<div class='hfiller20px'>";
		echo "</div>";
		echo "<div class='horizontal_line'>";
		echo "</div>";
		echo "<div class='subtext'>";
		echo "<div>Posted by " . htmlspecialchars($post[3]) . " at " . htmlspecialchars($post[4]) . ".</div>";
		echo "<div>Tags: " . htmlspecialchars($post[2]) . "</div>";
		echo "</div>";
		if($showadminui)
		{
			echo "<div style='display:flex;'>";
			// Continue flag for an incoming if
			$continue = false;
			// Check if user has permission to edit own posts
			if(isadmin(2))
			{
				// User has permission to edit own posts, allow continuing
				$continue = true;
				// Can user edit all posts? If yes, ignore the name check, if no, check if the uploader's name matches the account name
				if(!isadmin(8))
				{
					if($post[3] != $_COOKIE["user"])
					{
						// Uploader name does not match the account name, disallow continuing
						$continue = false;
					}
				}
			}
			// Is the user allowed to edit this post, following the previous permission checks?
			// If so, print the edit button under the post.
			if($continue)
			{
				echo "<form action='index.php' method='get'>";
				echo "<input type='hidden' name='id' value='" . htmlspecialchars($post[0]) . "'>";
				echo "<input type='submit' class='button' name='postedit' value='Edit'>";
				echo "</form>";
			}
			// Continue flag for an incoming if
			$continue = false;
			// Check if user has permission to delete own posts
			if(isadmin(4))
			{
				// User has permission to delete own posts, allow continuing
				$continue = true;
				// Can user delete all posts? If yes, ignore the name check, if no, check if the uploader's name matches the account name
				if(!isadmin(16))
				{
					if($post[3] != $_COOKIE["user"])
					{
						// Uploader name does not match the account name, disallow continuing
						$continue = false;
					}
				}
			}
			// Is the user allowed to delete this post, following the previous permission checks?
			// If so, print the delete button under the post.
			if($continue)
			{
				echo "<form action='index.php' method='post' onsubmit=\"return confirm('Are you sure you want to delete this post?')\">";
				echo "<input type='hidden' name='postdelete_id' value='" . htmlspecialchars($post[0]) . "'>";
				echo "<input type='submit' class='button marginleft' name='postdelete' value='Delete'>";
				echo "</form>";
			}
			echo "</div>";
		}
		echo "</div>";
	}
}
// This code gets executed if user is trying to view a text post
else
{
	$post = mysqli_fetch_array($res);
	if($post)
	{
		// The text post the user is trying to view exists, disable the no posts found notice
		$nopost = false;
		echo "<div class='post'>";
		echo "<h3>" . $post[2] . "</h3>";
		echo $post[1];
		echo "</div>";
	}
}
// Display this notice if no valid posts were found
if($nopost)
{
	echo "<div class='post'>";
	echo "<h3>No results</h3>";
	echo "<div class=''>";
	echo "No content was found that matches the criteria. The post(s) either don't exist, or you don't have permission to view the content.";
	echo "</div>";
	echo "</div>";
}

// Count results and display Page x of y if user isn't browsing a single post and isn't trying to insert a post
if(!$ispost && !isset($_POST["insertpost"]) && !$nopost)
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
			echo "&searchquery=" . htmlspecialchars($_GET["searchquery"]);
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
			echo "&searchquery=" . htmlspecialchars($_GET["searchquery"]);
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
