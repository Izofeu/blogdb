<?php
	// ob_start(null,);
	require("scripts/config.php");
	require("scripts/db_open.php");
	require("scripts/isadmin.php");
	require("scripts/cookies.php");
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<?php
		if($analyticsenabledcookie)
		{
			require("scripts/tracking.php");
		}
	?>
	<meta charset="utf-8">
	<meta name="author" content="Izofeu">
	<meta name="publisher" content="Nutsuki suu">
	<meta content="adult" name="rating">
	<meta content='website' property='og:type'>
	<meta content='en_US' property='og:locale'>
	<link rel='stylesheet' href='styles.css' type='text/css'>
	<?php
		require("scripts/opengraph.php");/*
		if(isset($_GET["id"]) || isset($_POST["insertpost"]))
		{
			echo "<link rel='stylesheet' href='styles_noexpand.css' type='text/css'>";
		}
		else
		{
			echo "<link rel='stylesheet' href='styles.css' type='text/css'>";
		}*/
		if(isset($_POST["postdelete_id"]))
		{
			require("scripts/deletepost.php");
		}
		if(isset($_POST["edit_id"]))
		{
			require("scripts/editpost.php");
		}
		if(isset($_POST["insert_title"]))
		{
			require("scripts/insertpost.php");
		}
	?>
	
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
</head>
<body class="bg_dark font">
	<script src="scripts.js">
	</script>
	<?php
		require("scripts/cookienotice.php");
	?>
	<div class="root_container">
		<div class="top_container">
			<div class="top_container_margin">
				<div class="top_header">
					<h1>
						<a class='h1link' href='./index.php'>Nutsuki Suu's videos</a>
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
							if($include_footer)
							{
								require("scripts/footer.php");
							}
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