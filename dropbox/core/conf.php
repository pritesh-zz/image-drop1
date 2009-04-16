<?php

session_start();

$loc = '/dropbox/html';
$path = '/var/www/dropbox';
$version = '0.1.0a';
$url = '192.168.4.223/dropbox/html';
$secure = false; // use https for things that require passwords, upload, edit, delete

$db = new mysqli("localhost", "dropbox", "dropbox", "dropbox");
if ( mysqli_connect_errno() ) {
	echo "Could not connect to database";
	exit();
}

?>
