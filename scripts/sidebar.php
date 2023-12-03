<?php
	# Patreon button
	echo "<div class='patreonbutton gadgets'>";
	echo "<a href='https://www.patreon.com/Nutsukisuu' style='all:unset; cursor:pointer;' target='_blank'>
		<div style='width:295px; height:40px; border:0px solid #F96854; background-color: #F96854; border-radius:10px; color:white; text-align: center; line-height:40px; vertical-align: middle; font-size:20px;'>
		<div style='float:left; width:20%;'>
		<img src='https://cdn.icon-icons.com/icons2/2429/PNG/512/patreon_logo_icon_147253.png' style='height:40px; width:auto;' />
		</div>
		<div style='float:left; width:65%;'>
		BECOME A MEMBER
		</div>
		<div style='float:left; width:15%;'>
		</div>
		</div>
		</a>";
	echo "</div>";
	
	# Youtube button
	echo "<div class='youtubebutton gadgets'>";
	echo "<a href='https://www.youtube.com/@NutsukiSuuK' style='all:unset; cursor:pointer;' target='_blank'>
		<div style='width:295px; height:40px; border:0px solid #FF0000; background-color: #FF0000; border-radius:10px; color:white; text-align: center; line-height:40px; vertical-align: middle; font-size:20px;'>
		<div style='float:left; width:20%;'>
		<img src='https://1000logos.net/wp-content/uploads/2017/05/youtube-symbol.jpg' style='height:30px; padding-top:5px; width:auto;' />
		</div>
		<div style='float:left; width:65%;'>
		YouTube
		</div>
		<div style='float:left; width:15%;'>
		</div>
		</div>
		</a>";
	echo "</div>";
	
	# Search bar
	echo "<div class='searchbar gadgets'>";
	echo "<h3 class='gadgets_top_text'>Search for...</h3>";
	echo "<form action='index.php' method='get'>";
	echo "<input type='text' id='searchquery' name='searchquery' class='searchbox textfield'>";
	echo "<input type='hidden' name='searchquery' id='searchquery_disabled' disabled='disabled'>";
	echo "<input type='submit' class='button button_searchbox'><br>";
	echo "<input type='radio' class='radiobutton' id='searchbytitle' name='searchtype' onclick='search_options_toggle(\"lock_date\")' value='0' checked>";
	echo "<label class='searchlabel' for='searchbytitle'>By title</label><br>";
	echo "<input type='radio' class='radiobutton' id='searchbytags' name='searchtype' onclick='search_options_toggle(\"lock_date\")' value='1'>";
	echo "<label class='searchlabel' for='searchbytags'>By tags (separated by space)</label><br>";
	echo "<input type='radio' class='radiobutton' id='searchbydate' name='searchtype' onclick='search_options_toggle(\"lock_bar\")' value='2'>";
	echo "<label class='searchlabel' for='searchbydate'>By date</label><br>";
	echo "<input type='checkbox' class='radiobutton' id='paidpostsonly' name='paidpostsonly'";
	if(!$isauth)
	{
		echo " disabled='disabled'";
	}
	echo ">";
	echo "<label class='searchlabel' for='paidpostsonly'>Paid only</label><br>";
	echo "<input type='checkbox' class='radiobutton' id='negatesearch' name='negatesearch'>";
	echo "<label class='searchlabel' for='negatesearch'>Not</label><br>";
	echo "From <input type='date' class='dateinput textfield' name='search_fromdate' disabled='disabled'><br>";
	echo "To <input type='date' class='dateinput textfield' name='search_todate' disabled='disabled'>";
	echo "</form>";
	echo "<form action='index.php' method='post'>";
	echo "<input type='submit' class='button' value='Random post'>";
	echo "<input type='hidden' name='random'>";
	echo "</form>";
	echo "</div>";
	
	# Authentication
	if($isauth)
	{
		$authtext = "shown";
		if(!$showadminui)
		{
			$authtext = $authtext . ". Permission expires at " . $cookieexpires . " " . date('T');
		}
	}
	else
	{
		$authtext = "hidden. Subscribe to Patreon, boost our Discord server or donate individually via PayPal to unlock exclusive content";
	}
	echo "<div class='authentication gadgets'>";
	echo "<h3 class='gadgets_top_text'>Authentication</h3>";
	echo "<div class='sidetext'>";
	if(isset($authsuccessful))
	{
		if($authsuccessful)
		{
			echo "Authentication successful. ";
		}
		else
		{
			echo "Authentication unsuccessful. ";
		}
	}
	echo "Paid content " . $authtext . ".</div>";
	if(!$showadminui)
	{
		echo "<div>";
		echo "<form action='index.php' method='post'>";
		echo "<input class='textfield' type='text' name='auth'>";
		echo "<input class='button' type='submit' value='Authenticate'>";
		echo "</form>";
		echo "</div>";
	}
	if(isadmin(1))
	{
		echo "<form action='index.php' method='post'>";
		echo "<input type='submit' name='insertpost' class='button' value='Add post'>";
		echo "</form>";
	}
	echo "</div>";
	
	
	# Information
	echo "<div class='information gadgets'>";
	echo "<h3 class='gadgets_top_text'>Information</h3>";
	echo "<ul class='url_list'>
		<li><a href='./index.php?textpost&id=1'>About</a></li>
		<li style='text-decoration: line-through;'><a href='https://mikuboard.blogspot.com/p/comment-rules.html'>Comment rules</a></li>
		<li><a href='./index.php?textpost&id=2'>Contact details</a></li>
		<li style='text-decoration: line-through;'><a href='https://mikuboard.blogspot.com/p/settings.html'>Settings</a></li>
		</ul>";
	echo "</div>";
	
	# Settings
	echo "<div class='settingsgadget gadgets'>";
	echo "<h3 class='gadgets_top_text'>Settings</h3>";
	echo "<button class='button' id='settings_toggle' data-settingsopen='0' onclick='settings_toggle()'>Open settings</button>";
	echo "<div class='settings' id='settings'>";
	echo "<div title='hover test'>";
	echo "Google analytics - ";
	echo "<span class='googleanalyticsstatus' id='googleanalyticsstatus' data-googleanalyticsstatus='";
	if(!$analyticsenabledcookie)
	{
		echo "0'>Disabled";
	}
	else
	{
		echo "1'>Enabled";
	}
	echo "</span>";
	echo "</div>";
	echo "<div>";
	echo "<form action='index.php' method='post' onsubmit='return confirm_analytics()'>";
	echo "<input type='submit' name='toggleanalytics' class='button' value='Toggle'>";
	echo "</form>";
	echo "</div>";
	echo "</div>";
	echo "</div>";
	
	# Filter by tags
	echo "<div class='filter_by gadgets'>";
	echo "<h3 class='gadgets_top_text'>Filter by</h3>";
	echo "<ul class='url_list'>
		<li><a class='mikulink' href='./index.php?searchquery=miku&searchtype=1'>Miku</a></li>
		<li><a class='lukalink' href='./index.php?searchquery=luka&searchtype=1'>Luka</a></li>
		<li><a class='rinlink' href='./index.php?searchquery=rin&searchtype=1'>Rin</a></li>
		<li><a class='hakulink' href='./index.php?searchquery=haku&searchtype=1'>Haku</a></li>
		<li><a class='meikolink' href='./index.php?searchquery=meiko&searchtype=1'>Meiko</a></li>
		</ul>";
	echo "</div>";
	
	# Discord embed
	echo "<div class='discord gadgets'>";
	echo "<h3 class='gadgets_top_text'>Discord server</h3>";
	echo "<iframe src='https://discord.com/widget?id=1044937920898936905&theme=dark' width='295' height='390' allowtransparency='true' frameborder='0' sandbox='allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts'></iframe>";
	echo "</div>";
	
	# Admin login
	echo "<div class='adminlogin gadgets'>";
	echo "<h3 class='gadgets_top_text'>Admin login</h3>";
	if($showadminui)
	{
		echo "<div>Authenticated as " . htmlspecialchars($adminusername) . ". Your permissions:</div>";
		// List permissions of a user
		echo "<div><ul>";
		if(isadmin(1))
			echo "<li>Insert posts</li>";
		if(isadmin(2))
			echo "<li>Edit own posts</li>";
		if(isadmin(4))
			echo "<li>Delete own posts</li>";
		if(isadmin(8))
			echo "<li>Edit any post</li>";
		if(isadmin(16))
			echo "<li>Delete any post</li>";
		echo "</ul></div>";
		echo "<form action='index.php' method='post'>";
		echo "<input type='hidden' name='logout'>";
		echo "<input type='submit' class='button' value='Log out'>";
		echo "</form>";
	}
	else
	{
		echo "<form action='index.php' method='post'>";
		echo "<input type='text' class='loginfield' placeholder='User name' name='user'>";
		echo "<input type='password' class='loginfield' placeholder='Password' name='password'>";
		echo "<input type='submit' class='button' value='Log in'>";
		echo "</form>";
	}
	echo "</div>";
?>