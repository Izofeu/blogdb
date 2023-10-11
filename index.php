<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="utf-8">
	<meta content="adult" name="rating">
	<link rel="stylesheet" href="styles.css" type="text/css">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<title>Nutsuki suu's videos</title>
	
	<!-- Embeds -->
	<meta content="https://nutsuki.fun" property='og:url'/>
</head>
<body class="bg_dark font">
	<div class="root_container">
		<div class="top_container">
			<div class="top_container_margin">
				<div class="top_header">
					<h1>
						Nutsuki Suu's videos
					</h1>
					<h2>
						A list of all Nutsuki's Discord videos.
					</h2>
				</div>
				
				<div class="horizontal_line">
				</div>
				
				<div class="main_container">
					<div class="margin30">
					</div>
					
					<div class="containers container1">
						<?php
							error_reporting(E_ALL);
							require("scripts/posts.php");
						?>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</body>
</html>
