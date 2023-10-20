function removecookie()
{
	document.cookie = 'cookienoticeviewed=1; max-age=31536000; path=/';
	let cookienotice = document.getElementById("cookienotice");
	cookienotice.style.display = "none";
}