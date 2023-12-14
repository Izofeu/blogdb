<?php
function search_echo($searchtype, $paidpostssearch)
{
	echo "&searchmode=on";
	if($searchtype & 1)
	{
		echo "&searchquery_title=" . htmlspecialchars($_GET["searchquery_title"]);
	}
	if($searchtype & 2)
	{
		echo "&searchquery_tags=" . htmlspecialchars($_GET["searchquery_tags"]);
	}
	if($searchtype & 4)
	{
		echo "&searchquery_fromdate=" . htmlspecialchars($_GET["searchquery_fromdate"]);
		echo "&searchquery_todate=" . htmlspecialchars($_GET["searchquery_todate"]);
	}
	if($paidpostssearch)
	{
		echo "&paidpostsonly=on";
	}
	// Negations
	if(isset($_GET["searchquery_title_not"]))
	{
		echo "&searchquery_title_not=on";
	}
	if(isset($_GET["searchquery_tags_not"]))
	{
		echo "&searchquery_tags_not=on";
	}
	if(isset($_GET["searchquery_date_not"]))
	{
		echo "&searchquery_date_not=on";
	}
	return;
}
?>