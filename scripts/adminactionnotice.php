<?php
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
?>