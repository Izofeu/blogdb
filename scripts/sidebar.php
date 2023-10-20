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
	echo "<input type='text' name='searchquery' class='searchbox textfield'>";
	echo "<input type='submit' class='button button_searchbox'><br>";
	echo "<input type='radio' class='radiobutton' id='searchbytitle' name='searchtype' value='0' checked>";
	echo "<label class='searchlabel' for='searchbytitle'>By title</label><br>";
	echo "<input type='radio' class='radiobutton' id='searchbytags' name='searchtype' value='1'>";
	echo "<label class='searchlabel' for='searchbytags'>By tags (separated by space)</label>";
	echo "</form>";
	echo "</div>";
	
	# Authentication
	if($isauth)
	{
		$authtext = "shown";
	}
	else
	{
		$authtext = "hidden";
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
	if(!$isauth)
	{
		echo "<div>";
		echo "<form action='index.php' method='post'>";
		echo "<input class='textfield' type='text' name='auth'>";
		echo "<input class='button' type='submit' value='Authenticate'>";
		echo "</form>";
		echo "</div>";
	}
	if($showadminui)
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
		<li><a href='https://mikuboard.blogspot.com/p/about_2.html'>About</a></li>
		<li><a href='https://mikuboard.blogspot.com/p/comment-rules.html'>Comment rules</a></li>
		<li><a href='https://mikuboard.blogspot.com/p/contact-details.html'>Contact details</a></li>
		<!--<li><a rel='nofollow' href='https://mikuboard.blogspot.com/p/disable-ads.html' class='redlink'>Ads removal statement</a></li>-->
		<li><a href='https://mikuboard.blogspot.com/p/settings.html'>Settings</a></li>
		</ul>";
	echo "</div>";
	
	# Filter by tags
	echo "<div class='filter_by gadgets'>";
	echo "<h3 class='gadgets_top_text'>Filter by</h3>";
	echo "<ul class='url_list'>
		<li><a class='mikulink' href='https://mikuboard.blogspot.com/search/label/miku'>Miku</a></li>
		<li><a class='lukalink' href='https://mikuboard.blogspot.com/search/label/luka'>Luka</a></li>
		<li><a class='lenlink' href='https://mikuboard.blogspot.com/search/label/rin'>Rin</a></li>
		<li><a class='hakulink' href='https://mikuboard.blogspot.com/search/label/haku'>Haku</a></li>
		<li><a class='meikolink' href='https://mikuboard.blogspot.com/search/label/meiko'>Meiko</a></li>
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
		echo "<div>Authenticated as " . htmlspecialchars($adminusername) . ".</div>";
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