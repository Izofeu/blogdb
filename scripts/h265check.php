<?php
	if(!isset($_COOKIE["h265"]))
	{
		echo "<video controls preload='metadata' id='testvideo' style='display:none;'>
		<source src='https://cdn.discordapp.com/attachments/1091811006788935751/1097564060230369280/Hibana.mp4'>
		</source></video>";
		echo "<script>"
		. "let vid = document.getElementById('testvideo');"
		. "vid.addEventListener('loadedmetadata', (event) => {"
		. "if(event.target.videoHeight === 0) {"
		. "alert('Your browser/device does not support h.265 video playback. Videos will NOT load properly. See the About section for more details.');"
		. "document.cookie = 'h265=1; path=/';}});"
		. "</script>";
	}
	
	
?>