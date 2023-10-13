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
	echo "<form action='search.php' method='post'>";
	echo "<input type='text' name='searchquery' class='searchbox'>";
	echo "<input type='submit' class='button_searchbox'>";
	echo "</form>";
	echo "</div>";
	
	# Information
	echo "<div class='information gadgets'>";
	echo "<h3 class='gadgets_top_text'>Information</h3>";
	echo "<li><a href='https://mikuboard.blogspot.com/p/about_2.html'>About</a></li>
		<li><a href='https://mikuboard.blogspot.com/p/comment-rules.html'>Comment rules</a></li>
		<li><a href='https://mikuboard.blogspot.com/p/contact-details.html'>Contact details</a></li>
		<!--<li><a rel='nofollow' href='https://mikuboard.blogspot.com/p/disable-ads.html' class='redlink'>Ads removal statement</a></li>-->
		<li><a href='https://mikuboard.blogspot.com/p/settings.html'>Settings</a></li>
		</ul>";
	echo "</div>";
?>