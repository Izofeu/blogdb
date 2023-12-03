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
// This variable should be toggled if user is in search mode.
$issearch = false;
// This variable should be toggled if user is browsing a single post.
// Should also be set for text posts.
$ispost = false;
// This variable should be toggled if user is searching by valid tags.
$searchbytags = false;
// This variable should be toggled if user is searching by valid date.
$searchbydate = false;
// This variable should be toggled if user is browsing a text post.
$textpost = false;
// This variable should be toggled if user has negated the search.
// It doesn't guarantee that the search is valid or even executed.
$negatesearch = false;
// This variable is used for fancy displaying of "too many tags in search" notice.
$searchfailed = false;
// This variable is enabled if user is searching for paid posts only.
// Paired with issearch which is either set to false if user only
// wants paid posts, or with issearch on if user wants paid posts
// that match the search criteria.
$paidpostssearch = false;

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
else if(isset($_GET["searchquery"]))
{
	// Is the search negated?
	if(isset($_GET["negatesearch"]))
	{
		$negatesearch = true;
	}
	// Is the search action valid?
	// A valid search action must contain a search type
	// and either a non-empty search query or a date parameter
	if(($_GET["searchquery"] != "" || isset($_GET["search_fromdate"])) && isset($_GET["searchtype"]))
	{
		// User is in general search mode, set flag to true
		$issearch = true;
		// Is user trying to search by tags?
		if($_GET["searchtype"] == 1)
		{
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
			$continue = true;
			if($querytagscount > 5)
			{
				// Disable search mode if user is trying to search too many tags at once.
				// We don't want malicious users to potentially overload the database with too many
				// AND queries.
				$continue = false;
				$issearch = false;
				$searchfailed = true;
				$searchbytags = false;
			}
			// Tags are validated, continue execution
			if($continue)
			{
				// Default query
				$query = "SELECT * FROM posts WHERE ispaid = 0 AND tags";
				if($negatesearch)
				{
					$query = $query . " NOT LIKE ?";
				}
				else
				{
					$query = $query . " LIKE ?";
				}
				if($isauth)
				{
					$query = "SELECT * FROM posts WHERE tags";
					if($negatesearch)
					{
						$query = $query . " NOT LIKE ?";
					}
					else
					{
						$query = $query . " LIKE ?";
					}
					// Does the user only want paid posts?
					if(isset($_GET["paidpostsonly"]))
					{
						$query = $query . " AND ispaid = 1";
					}
				}
				// Alternate query, this is used to get count of posts for Page x out of y
				$querycount = "SELECT COUNT(id) FROM posts WHERE ispaid = 0 AND tags";
				if($negatesearch)
				{
					$querycount = $querycount . " NOT LIKE ?";
				}
				else
				{
					$querycount = $querycount . " LIKE ?";
				}
				if($isauth)
				{
					$querycount = "SELECT COUNT(id) FROM posts WHERE tags";
					if($negatesearch)
					{
						$querycount = $querycount . " NOT LIKE ?";
					}
					else
					{
						$querycount = $querycount . " LIKE ?";
					}
					if(isset($_GET["paidpostsonly"]))
					{
						$paidpostssearch = true;
						$querycount = $querycount . " AND ispaid = 1";
					}
				}
				// Depending on user argument count, add enough 'AND tags' clauses
				for($i=2; $i<=$querytagscount; $i=$i+1)
				{
					if($negatesearch)
					{
						$query = $query . " AND tags NOT LIKE ?";
						$querycount = $querycount . " AND tags NOT LIKE ?";
					}
					else
					{
						$query = $query . " AND tags LIKE ?";
						$querycount = $querycount . " AND tags LIKE ?";
					}
				}
				// Execute a query with count of found posts, sanitized
				$searchpostcount = mysqli_execute_query($db, $querycount, $querytags);
				// Prepare actual query
				$query = $query . " ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
			}
		}
		// Is user trying to search by date?
		else if($_GET["searchtype"] == 2 && isset($_GET["search_fromdate"]) && isset($_GET["search_todate"]))
		{
			$continue = false;
			// Make sure dates are semi-valid
			if(strtotime($_GET["search_fromdate"]) && strtotime($_GET["search_todate"]))
			{
				// Make sure dates follow the format of 0000-00-00
				$regex = '/^\d{4}-\d{2}-\d{2}$/';
				if(preg_match($regex, $_GET["search_fromdate"]) && preg_match($regex, $_GET["search_todate"]))
				{
					// Both dates are fully valid, continue
					$continue = true;
				}
			}
			if($continue)
			{
				// Set search by date mode
				$searchbydate = true;
				// Add time to the query as it's stored as datetime in the table
				$datefrom = $_GET["search_fromdate"] . " 00:00:00";
				$dateto = $_GET["search_todate"] . " 23:59:59";
				// Prepare an array for mysqli_execute_query function
				$querydate = [$datefrom, $dateto];
				// Default query
				$query = "SELECT * FROM posts WHERE ispaid = 0 AND (date BETWEEN ? AND ?) ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
				if($negatesearch)
				{
					$query = "SELECT * FROM posts WHERE ispaid = 0 AND (date NOT BETWEEN ? AND ?) ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
				}
				// Alternate query, this is used to get count of posts for Page x out of y
				$querycount = "SELECT COUNT(id) FROM posts WHERE ispaid = 0 AND (date BETWEEN ? AND ?)";
				if($negatesearch)
				{
					$querycount = "SELECT COUNT(id) FROM posts WHERE ispaid = 0 AND (date NOT BETWEEN ? AND ?)";
				}
				if($isauth)
				{
					$query = "SELECT * FROM posts WHERE (date BETWEEN ? AND ?)";
					$querycount = "SELECT COUNT(id) FROM posts WHERE (date BETWEEN ? AND ?)";
					if($negatesearch)
					{
						$query = "SELECT * FROM posts WHERE (date NOT BETWEEN ? AND ?)";
						$querycount = "SELECT COUNT(id) FROM posts WHERE (date NOT BETWEEN ? AND ?)";
					}
					if(isset($_GET["paidpostsonly"]))
					{
						$paidpostssearch = true;
						$query = $query . " AND ispaid = 1";
						$querycount = $querycount . " AND ispaid = 1";
					}
					$query = $query . " ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
				}
				// Execute a query with count of found posts, sanitized
				$searchpostcount = mysqli_execute_query($db, $querycount, $querydate);
			}
			// User inputted date is invalid, disable search mode and proceed with default query
			else
			{
				$issearch = false;
			}
		}
		// User is trying to search by title, prepare an appropriate query
		else
		{
			$query = "SELECT * FROM posts WHERE ispaid = 0 AND title LIKE ? ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
			if($negatesearch)
			{
				$query = "SELECT * FROM posts WHERE ispaid = 0 AND title NOT LIKE ? ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
			}
			if($isauth)
			{
				$query = "SELECT * FROM posts WHERE title LIKE ?";
				$querycount = "SELECT COUNT(id) FROM posts WHERE title LIKE ?";
				if($negatesearch)
				{
					$query = "SELECT * FROM posts WHERE title NOT LIKE ?";
					$querycount = "SELECT COUNT(id) FROM posts WHERE title NOT LIKE ?";
				}
				if(isset($_GET["paidpostsonly"]))
				{
					$paidpostssearch = true;
					$query = $query . " AND ispaid = 1";
					$querycount = $querycount . " AND ispaid = 1";
					$searchpostcount = mysqli_execute_query($db, $querycount, [$_GET["searchquery"]]);
				}
				$query = $query . " ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
			}
		}
	}
	// Run this query if user wants paid posts
	// without search parameters.
	else if(isset($_GET["paidpostsonly"]) && $isauth)
	{
		$paidpostssearch = true;
		// We do not set issearch flag.
		$query = "SELECT * FROM posts WHERE ispaid = 1 ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
		$querycount = "SELECT COUNT(id) FROM posts WHERE ispaid = 1 ORDER BY date DESC LIMIT " . $postcount . " OFFSET " . $offset;
		$searchpostcount = mysqli_query($db, $querycount);
	}
}

// Execute this query if user is searching by title
if($issearch && !$searchbytags && !$searchbydate)
{
	$res = mysqli_execute_query($db, $query, ["%" . $_GET["searchquery"] . "%"]);
}
// Execute this query if user is searching by tags
else if($searchbytags && $issearch)
{
	$res = mysqli_execute_query($db, $query, $querytags);
}
// Execute this query if user is searching by date
else if($searchbydate && $issearch)
{
	$res = mysqli_execute_query($db, $query, $querydate);
}
// Execute this query if user is not in search mode
// or if user wants paid posts only.
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

// Display a message if user inputted too many tags in search by tags mode
if($searchfailed)
{
	echo "<div class='searchquerytext'>";
	echo "Maximum of 5 tags allowed per search.";
	echo "</div>";
}

// Print adequate search query text and mode as well as negation
if($issearch || $paidpostssearch)
{
	echo "<div class='searchquerytext'>";
	if($paidpostssearch && !$issearch)
	{
		echo "Displaying paid posts only.";
		// Nice display of two different messages.
		$paidnoticedisplayed = true;
	}
	else if($searchbydate)
	{
		echo "Searching: dates ";
		if($negatesearch)
		{
			echo "not ";
		}
		echo "between " . htmlspecialchars($_GET["search_fromdate"]) . " and " . htmlspecialchars($_GET["search_todate"]) . ".";
	}
	else
	{
		echo "Searching: ";
		if($negatesearch)
		{
			echo "not ";
		}
		echo "\"";
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
	}
	if($paidpostssearch && !isset($paidnoticedisplayed))
	{
		echo " Paid posts only mode.";
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
		if($negatesearch)
		{
			$query = "SELECT COUNT(id) FROM posts WHERE ispaid = 0 AND title NOT LIKE ?";
		}
		if($isauth)
		{
			$query = "SELECT COUNT(id) FROM posts WHERE title LIKE ?";
			if($negatesearch)
			{
				$query = "SELECT COUNT(id) FROM posts WHERE title NOT LIKE ?";
			}
			if($paidpostssearch)
			{
				$query = $query . " AND ispaid = 1";
			}
		}
		if($searchbytags || $searchbydate)
		{
			// If user is in search by tags or by date mode, we already have a mysqli object so we do not need to query the database
			$res = $searchpostcount;
		}
		else
		{
			// Executing query in search by title mode
			$res = mysqli_execute_query($db, $query, ["%" . $_GET["searchquery"] . "%"]);
		}
	}
	// If user is not in search mode but wants paid posts only,
	// get the prepared mysqli object as it's easier
	// than writing a bunch of queries here.
	else if($paidpostssearch)
	{
		$res = $searchpostcount;
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
			else if($searchbydate)
			{
				echo "&searchtype=2&search_fromdate=" . htmlspecialchars($_GET["search_fromdate"])
			. "&search_todate=" . htmlspecialchars($_GET["search_todate"]);
			}
			else
			{
				// Default search type if user is searching by title, required
				echo "&searchtype=0";
			}
			if($negatesearch)
			{
				echo "&negatesearch=on";
			}
			if($paidpostssearch)
			{
				echo "&paidpostsonly=on";
			}
		}
		// Needs to be outside the if above in case user wants only paid posts
		// but didn't input any search parameters.
		else if($paidpostssearch)
		{
			echo "&searchquery=&searchtype=0&paidpostsonly=on";
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
			else if($searchbydate)
			{
				echo "&searchtype=2&search_fromdate=" . htmlspecialchars($_GET["search_fromdate"])
			. "&search_todate=" . htmlspecialchars($_GET["search_todate"]);
			}
			else
			{
				// Default search type if user is searching by title, required
				echo "&searchtype=0";
			}
			if($negatesearch)
			{
				echo "&negatesearch=on";
			}
			if($paidpostssearch)
			{
				echo "&paidpostsonly=on";
			}
		}
		else if($paidpostssearch)
		{
			echo "&searchquery=&searchtype=0&paidpostsonly=on";
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
