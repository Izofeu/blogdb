<?php
function addtoquery($queryarr, $toadd)
{
	$queryarr[0] = $queryarr[0] . $toadd;
	$queryarr[1] = $queryarr[1] . $toadd;
	return $queryarr;
}
?>