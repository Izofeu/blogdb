function removecookie()
{
	document.cookie = 'cookienoticeviewed=1; max-age=31536000; path=/';
	let cookienotice = document.getElementById("cookienotice");
	cookienotice.style.display = "none";
}

function settings_toggle()
{
	let settings_button = document.getElementById("settings_toggle");
	let settings = document.getElementById("settings");
	if(settings_button.dataset.settingsopen == 1)
	{
		settings.style.display = "none";
		settings_button.dataset.settingsopen = 0;
		settings_button.innerHTML = "Open settings";
	}
	else
	{
		settings.style.display = "block";
		settings_button.dataset.settingsopen = 1;
		settings_button.innerHTML = "Close settings";
	}
}

function confirm_analytics()
{
	let analyticsstatus = document.getElementById("googleanalyticsstatus").dataset.googleanalyticsstatus;
	if(analyticsstatus == 0)
	{
		return true;
	}
	else
	{
		return confirm("Are you sure you want to disable Google Analytics? It will prevent me from accessing view count of specific "
					+ "links and posts via Google. It will not stop logging of your activity on the webserver. Do you want to continue?");
	}
}

function search_options_toggle(what)
{
	let search_bar = document.getElementById("searchquery");
	let hidden_search = document.getElementById("searchquery_disabled");
	let search_dates = document.getElementsByClassName("dateinput");
	if(what == "lock_bar")
	{
		search_bar.setAttribute("disabled", "disabled");
		for(let obj of search_dates)
		{
			obj.removeAttribute("disabled");
		}
		hidden_search.removeAttribute("disabled");
	}
	else if(what == "lock_date")
	{
		search_bar.removeAttribute("disabled");
		for(let obj of search_dates)
		{
			obj.setAttribute("disabled", "disabled");
		}
		hidden_search.setAttribute("disabled", "disabled");
	}
}