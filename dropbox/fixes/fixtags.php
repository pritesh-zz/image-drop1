<?php

require '../core/conf.php';
require $path . "/core/func.php";

//select all tags
$sql = "select * from tags";
$result = $db->query( $sql );

// find ones that have extra whitespace
while( $row = $result->fetch_assoc() ) {
	$tags[$row['id']] = $row['name'];
	print "row[name] == " . $row['name'] . " " . $row['id'] . "<br/>";
	if ( $row['name']{0} == '_' ) {
		$ws_tags[] = $row;
		print "row[name] == substr(" . substr($row['name'], 1) . ",1)<br/>";
	}
}
// look for another tag with the same name without the whitespace
foreach( $ws_tags as $ws ) {
	$found = 0;
	print "ws['name'] == " . $ws['name'] . "<br/>";
	foreach( $tags as $id => $name ) {
		if ( substr( $ws['name'], 1 ) == $name ) {
			$found = $id;
			break;
		}
	}
	if ( $found ) {
		$sql = sprintf( "update tagmap set tag=%d where tag=%d", $found, $ws['id'] );
		$db->query( $sql );
		print $sql."<br/>";
		$sql = sprintf( "delete from tags where id=%d", $ws['id'] );
		$db->query( $sql );
	} else {
		$sql = sprintf( "update tags set name='%s' where id=%d", substr($ws['name'],1), $ws['id'] );
		$db->query( $sql );
		print $sql."<br/>";
	}
}

?>
