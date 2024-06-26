<?php
require("scripts/addtoquery.php");
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
// This variable should be toggled if user is in search mode.
$issearch = false;
// This variable should be toggled if user is browsing a single post.
// Should also be set for text posts.
$ispost = false;
// This variable should be toggled if user is browsing a text post.
$textpost = false;
// This variable is used for fancy displaying of "too many tags in search" notice.
$searchfailed = false;
// This variable is enabled if user is searching for paid posts only.
// Paired with issearch which is either set to false if user only
// wants paid posts, or with issearch on if user wants paid posts
// that match the search criteria.
$paidpostssearch = false;
// This is an incrementing int used for ANDing to get what search types
// are enabled, so we can print an appropriate notice on top.
// 1 - title
// 2 - tags
// 4 - date
$searchtype = 0;
// Similar to searchtype but gets set depending on what errors occured
// like too many tags or search title too long.
// 1 - title error
// 2 - tags error
// 4 - date error
$searcherror = 0;

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

// Is user trying to get a random post?
else if(isset($_POST["random"]))
{
	$ispost = true;
	$query = "SELECT * FROM posts WHERE ispaid = 0 ORDER BY RAND() LIMIT 1";
	if($isauth)
	{
		$query = "SELECT * FROM posts ORDER BY RAND() LIMIT 1";
	}
}

// User doesn't want a random post, check if user is trying to perform a search action of any type
else if(isset($_GET["searchmode"]))
{
	$queryargs = array();
	$queryarray[0] = "SELECT * FROM posts WHERE 1=1";
	$queryarray[1] = "SELECT COUNT(id) FROM posts WHERE 1=1";
	if(!$isauth)
	{
		$queryarray = addtoquery($queryarray, " AND ispaid = 0");
	}
	if(!empty($_GET["searchquery_title"]))
	{
		$continue = true;
		if(strlen($_GET["searchquery_title"]) > 40)
		{
			$searcherror = $searcherror | 1;
			$continue = false;
		}
		if($continue)
		{
			$queryarray = addtoquery($queryarray, " AND title");
			if(isset($_GET["searchquery_title_not"]))
			{
				$queryarray = addtoquery($queryarray, " NOT");
			}
			$queryarray = addtoquery($queryarray, " LIKE ?");
			$queryargs[] = "%" . $_GET["searchquery_title"] . "%";
			$issearch = true;
			$searchtype = $searchtype | 1;
		}
	}
	if(!empty($_GET["searchquery_tags"]))
	{
		// Split user string into tags that can be queried
		$querytags = explode(" ", $_GET["searchquery_tags"]);
		// Get count of tags
		$querytagscount = sizeof($querytags);
		$continue = true;
		if($querytagscount > 5 || $querytagscount < 1)
		{
			$continue = false;
			$searcherror = $searcherror | 2;
		}
		if($continue)
		{
			for($i=0; $i<$querytagscount; $i++)
			{
				$queryarray = addtoquery($queryarray, " AND tags");
				if(isset($_GET["searchquery_tags_not"]))
				{
					$queryarray = addtoquery($queryarray, " NOT");
				}
				$queryarray = addtoquery($queryarray, " LIKE ?");
				$queryargs[] = "%" . $querytags[$i] . "%";
				
			}
			$issearch = true;
			$searchtype = $searchtype | 2;
		}
	}
	if(!empty($_GET["searchquery_fromdate"]) && !empty($_GET["searchquery_todate"]))
	{
		$continue = false;
		if(strtotime($_GET["searchquery_fromdate"]) && strtotime($_GET["searchquery_todate"]))
		{
			// Make sure dates follow the format of 0000-00-00
			$regex = '/^\d{4}-\d{2}-\d{2}$/';
			if(preg_match($regex, $_GET["searchquery_fromdate"]) && preg_match($regex, $_GET["searchquery_todate"]))
			{
				// Both dates are fully valid, continue
				$continue = true;
			}
		}
		if($continue)
		{
			$datefrom = $_GET["searchquery_fromdate"] . " 00:00:00";
			$dateto = $_GET["searchquery_todate"] . " 23:59:59";
			$queryarray = addtoquery($queryarray, " AND (date");
			if(isset($_GET["searchquery_date_not"]))
			{
				$queryarray = addtoquery($queryarray, " NOT");
			}
			$queryarray = addtoquery($queryarray, " BETWEEN ? AND ?)");
			array_push($queryargs, $datefrom, $dateto);
			$issearch = true;
			$searchtype = $searchtype | 4;
		}
		else
		{
			$searcherror = $searcherror | 4;
		}
	}
	if(isset($_GET["paidpostsonly"]))
	{
		$queryarray = addtoquery($queryarray, " AND ispaid = 1");
		$paidpostssearch = true;
		$issearch = true;
	}
	if($issearch)
	{
		$queryarray[0] = $queryarray[0] . " ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
	}
}

// Execute this query if user is in search mode
if($issearch)
{
	$res = mysqli_execute_query($db, $queryarray[0], $queryargs);
	$res_count = mysqli_execute_query($db, $queryarray[1], $queryargs);
}
// Execute this query if user is not in search mode
// or if user wants paid posts only.
else
{
	$res = mysqli_query($db, $query);
	$res_count = mysqli_query($db, "SELECT COUNT(id) FROM posts WHERE ispaid = 0");
	if($isauth)
	{
		$res_count = mysqli_query($db, "SELECT COUNT(id) FROM posts");
	}
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

// Print adequate search query text and mode as well as negation
if($issearch)
{
	echo "<div class='searchquerytext'>";
	echo "<span class='bold'>Search parameters:</span><br>";
	if($searchtype & 1)
	{
		echo "Title: " . htmlspecialchars($_GET["searchquery_title"]);
		if(isset($_GET["searchquery_title_not"]))
		{
			echo " (negated)";
		}
		echo "<br>";
	}
	if($searchtype & 2)
	{
		echo "Tags: " . htmlspecialchars($_GET["searchquery_tags"]);
		if(isset($_GET["searchquery_tags_not"]))
		{
			echo " (negated)";
		}
		echo "<br>";
	}
	if($searchtype & 4)
	{
		echo "Date: between " . htmlspecialchars($_GET["searchquery_fromdate"]);
		echo " and " . htmlspecialchars($_GET["searchquery_todate"]);
		if(isset($_GET["searchquery_date_not"]))
		{
			echo " (negated)";
		}
		echo "<br>";
	}
	if($paidpostssearch)
	{
		echo "Paid posts only.<br>";
	}
	echo "</div>";
}
if($searcherror)
{
	if($issearch)
	{
		echo "<br>";
	}
	echo "<div class='searchquerytext'>";
	echo "<span class='bold'>Some of your query parameters resulted in an error:</span><br>";
	if($searcherror & 1)
	{
		echo "Title must be no longer than 60 characters long.<br>";
	}
	if($searcherror & 2)
	{
		echo "There can be a maximum of 5 tags per search.<br>";
	}
	if($searcherror & 4)
	{
		echo "The inputted date range is not valid.";
	}
	echo "</div>";
}

// If user tried to edit / delete / insert a post, include a notice script to print appropriate message
if(isset($postdeletesuccess) || isset($posteditsuccess) || isset($postinsertsuccess))
{
	require("scripts/adminactionnotice.php");
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
				echo "<div class='post_nocss'>";
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
	$ispost = true;
	if($loginsuccessful)
	{
		echo "Welcome back, " . htmlspecialchars($_POST["user"]) . ". Refreshing in 2 seconds..";
	}
	else
	{
		echo "Invalid credentials.";
	}
	echo "</div>";
}
// If the user hasn't tried to log in, are they trying to insert a post?
else if(isset($_POST["insertpost"]))
{
	$ispost = true;
	$nopost = false;
	if(!isadmin(1))
	{
		echo "Your account doesn't have permission to insert posts.";
	}
	else
	{
		echo "<div class='post_nocss'>";
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
		echo "<div class='post";
		if($ispost)
		{
			echo "_nocss";
		}
		echo "'>";
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
		echo "<div class='post_nocss'>";
		echo "<h3>" . $post[2] . "</h3>";
		echo $post[1];
		echo "</div>";
	}
}
// Display this notice if no valid posts were found
if($nopost)
{
	echo "<div class='post_nocss'>";
	echo "<h3>No results</h3>";
	echo "<div class=''>";
	echo "No content was found that matches the criteria. The post(s) either don't exist, or you don't have permission to view the content.";
	echo "</div>";
	echo "</div>";
}

// Count results and display Page x of y if user isn't browsing a single post and isn't trying to insert a post
else if(!$ispost)
{
	require("scripts/search_echo.php");
	$res_count = mysqli_fetch_array($res_count);
	$res_count = $res_count[0];
	// Get pages count
	$res = intdiv($res_count, $postcount) + 1;
	// Printing of Page x of y
	echo "<div class='pagecount'>";
	// If not the first page, print previous page button
	if($page > 1)
	{
		echo "<a href='?page=1";
		// Add search query if searching
		if($issearch)
		{
			search_echo($searchtype, $paidpostssearch);
		}
		echo "'><<< </a>";
		
		echo "<a href='?page=" . ($page - 1);
		// Add search query if searching
		if($issearch)
		{
			search_echo($searchtype, $paidpostssearch);
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
			search_echo($searchtype, $paidpostssearch);
		}
		echo "'> >></a>";
		
		echo "<a href='?page=" . $res;
		// Add search query if searching
		if($issearch)
		{
			search_echo($searchtype, $paidpostssearch);
		}
		echo "'> >>></a>";
	}
	echo "</div>";
}

// Close database
mysqli_close($db);
// Lazyload video poster script
echo "<script src='lazyload.js'></script>";
?>
