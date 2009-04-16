<?php

require '../core/conf.php';
require $path . "/core/func.php";

$sql = "select id,hash from entries where hash is null";
$result = $db->query( $sql );

// get all images without a hash
while( $entry = $result->fetch_assoc() ) {

	$sql = sprintf("select * from data where entryid=%d", $entry['id'] );
	$r2 = $db->query( $sql );

	while( $row = $r2->fetch_assoc() ) {
		$data .= $row['filedata'];
	}

	$sql = sprintf("update entries set hash='%s' where id=%d",
					sha1($data), $entry['id'] );
	$data = '';
	print $sql . "<br/>";
	$db->query( $sql );
}

?>
