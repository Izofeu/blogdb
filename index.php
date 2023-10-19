<?php
	// ob_start(null,);
	require("scripts/cookies.php");
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="utf-8">
	<meta name="author" content="Izofeu">
	<meta name="publisher" content="Nutsuki suu">
	<meta content="adult" name="rating">
	<meta content='website' property='og:type'>
	<meta content='en_US' property='og:locale'>
	<?php
		require("scripts/opengraph.php");
		if(isset($_GET["id"]))
		{
			echo "<link rel='stylesheet' href='styles_noexpand.css' type='text/css'>";
		}
		else
		{
			echo "<link rel='stylesheet' href='styles.css' type='text/css'>";
		}
	?>
	
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<title>Nutsuki suu's videos</title>
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
					
					<div class="containers container1">
						<?php
							error_reporting(E_ALL);
							require("scripts/posts.php");
						?>
					</div>
					
					
					
					<div class="containers container2">
						<div class="vertical_line" style="float: left;">
						</div>
						<div class="sidebar">
							<?php
								require("scripts/sidebar.php");
							?>
						</div>
					</div>
					
				</div>
			</div>
			<div class="top_container_margin bottom_container_margin">
			</div>
		</div>
	</div>
</body>
</html>
<?php
// ob_end_flush();
?>