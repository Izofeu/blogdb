<?php
if(!isset($_COOKIE["cookienoticeviewed"]))
{
	echo "<div class='cookienotice' id='cookienotice'>";
	echo "<div class='cookienoticetext'>";
	echo "This website uses cookies to store user preferences. Site created cookies are stored locally, they are not used for advertising or for selling your data. The site contains"
		. " third party iframes which may also set cookies on your device. Does anybody even read cookie notices? I'm surprised you got to this point and haven't dismissed it yet lol get a life."
		. " Jokes aside I'm impressed you made it here."
		. " More information about cookies can be accessed in the About page. Cookies can be"
		. " blocked in your browser settings.";
	echo "</div>";
	echo "<script src='removecookies.js'></script>";
	echo "<button onclick='removecookie()' class='button cookienoticebutton'>DISMISS</button>";
	echo "</div>";
}
?>