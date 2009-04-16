<?php

if ( $_GET['search'] ) {
	$loc = sprintf('/tags/%s/', strval($_GET['search']));
} else {
	$loc = '/';
}

header("Location: $loc");

?>
