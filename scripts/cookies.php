<?php
	# Cookie notice cookie
	if(isset($_COOKIE["cookienoticeviewed"]))
	{
		setcookie("cookienoticeviewed", 1, time() + (86400 * 7), "/", $domain, false, false);
	}
	# Authentication for paid posts
	$isauth = false;
	// Two ifs above check if user has either a cookie set or a post value set
	// This value is used in a query to determine if the cookie exists and hasn't expired yet
	if(isset($_COOKIE["auth"]))
	{
		$toquery = $_COOKIE["auth"];
	}
	else if(isset($_POST["auth"]))
	{
		$toquery = $_POST["auth"];
		$authsuccessful = false;
	}
	// The user is trying to authenticate to view paid posts
	if(isset($toquery))
	{
		$db = db_open();
		$query = "SELECT * FROM cookies WHERE value = ?";
		// Find the user inputted value
		$res = mysqli_execute_query($db, $query, [$toquery]);
		$res = mysqli_fetch_array($res);
		// Does the user inputted value exist?
		if($res)
		{
			// Convert mysql datetime to php unix time
			$date_cookie = strtotime($res[2]);
			// Get unix time of php, respecting the timezone from config
			$date_now = time();
			// Has the cookie expired yet?
			if(($date_cookie - $date_now) > 0)
			{
				// This variable shows the expiration date in the sidebar
				$cookieexpires = $res[2];
				// Set / renew the cookie, doesn't need to be set to expiry date of database value
				setcookie("auth", $toquery, time() + (86400 * 7), "/", $domain, false, true);
				// Mark user as authenticated to view paid posts
				$isauth = true;
				if(isset($authsuccessful))
				{
					$authsuccessful = true;
				}
			}
		}
		// User inputted default value does not exist, clear the cookie if exists
		// This is required because if the user has an old cookie saved,
		// they won't be able to reauthenticate with new credentials.
		else if(isset($_COOKIE["auth"]))
		{
			setcookie("auth", null, 1, "/", $domain, false, false);
		}
		mysqli_close($db);
	}

	# Admin login
	// showadminui variable is used only for displaying non-critical admin UI elements
	// like edit / delete buttons. It is NOT a safe variable and should not be
	// relied on for any real functionality. Use isadmin(int) for that purpose as that function
	// guarantees safety.
	$showadminui = false;
	// Is the user logging out?
	if(isset($_POST["logout"]))
	{
		setcookie("user", null, 1, "/", $domain, true, true);
		setcookie("password", null, 1, "/", $domain, true, true);
	}
	// User isn't logging out, does the user have credentials stored?
	// If credentials exist but are invalid, they get treated like correct credentials
	// however the user gets no extra permissions - if credentials are invalidated
	// or user has set user and password cookies to random values, that's on them
	// to "log out".
	else if(isset($_COOKIE["user"]))
	{
		setcookie("user", $_COOKIE["user"], time() + (86400 * 7), "/", $domain, true, true);
		if(isset($_COOKIE["password"]))
		{
			setcookie("password", $_COOKIE["password"], time() + (86400 * 7), "/", $domain, true, true);
			$adminusername = $_COOKIE["user"];
			$showadminui = true;
			if(isadmin(0))
			{
				$isauth = true;
			}
		}
	}
	// Not logging out and no credentials stored, perhaps the user is trying to log in?
	else if(isset($_POST["user"]) && isset($_POST["password"]))
	{
		$db = db_open();
		$query = "SELECT * FROM admins WHERE username = ?";
		$res = mysqli_execute_query($db, $query, [$_POST["user"]]);
		$res = mysqli_fetch_array($res);
		$loginsuccessful = false;
		if($res)
		{
			// User provided username exists, verify the password with the hashed copy inside the database
			$pres = password_verify($_POST["password"], $res[1]);
			if($pres)
			{
				// User credentials are valid, set them as cookies, enable login notification,
				// enable viewing of paid posts without a need for paid posts authentication
				// and reload the page so all UI elements display correctly
				setcookie("user", $_POST["user"], time() + (86400 * 7), "/", $domain, true, true);
				setcookie("password", $res[1], time() + (86400 * 7), "/", $domain, true, true);
				$adminusername = $_POST["user"];
				$showadminui = true;
				$isauth = true;
				$loginsuccessful = true;
				header("refresh: 2;");
			}
		}
		mysqli_close($db);
	}
	$analyticsenabledcookie = true;
	if(isset($_POST["toggleanalytics"]))
	{
		if(isset($_COOKIE["googleanalyticsdisabled"]))
		{
			$analyticsenabledcookie = true;
			setcookie("googleanalyticsdisabled", null, 1, "/", $domain, false, false);
		}
		else
		{
			$analyticsenabledcookie = false;
			setcookie("googleanalyticsdisabled", 1, time() + (86400 * 7), "/", $domain, false, false);
		}
	}
	else if(isset($_COOKIE["googleanalyticsdisabled"]))
	{
		setcookie("googleanalyticsdisabled", 1, time() + (86400 * 7), "/", $domain, false, false);
		$analyticsenabledcookie = false;
	}
?>