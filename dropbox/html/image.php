<?php

require '../core/conf.php';

$mode = ( $_GET['mode'] == 'thumb' ) ? 'thumb' : 'image';
$id = intval( $_GET['id'] );
$sql = 'SELECT date,size,parent FROM entries WHERE id=' . $id;
$result = $db->query( $sql );
$row = $result->fetch_assoc();
$date = $row['date'];
$size = $row['size'];
$parent_id = ($row['parent']) ? $row['parent'] : $id;

header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $date ) . ' GMT', true, 200 );
header('Expires: ' . gmdate( 'D, d M Y H:i:s',  $date + 86400 ) . ' GMT', true, 200 );

$ar = apache_request_headers();

if ( isset( $ar['If-Modified-Since'] ) && 
	( $ar['If-Modified-Since'] != '' ) &&
	( strtotime( $ar['If-Modified-Since']) >= $date ) ) {
	header( 'Last-Modified: ' . gmdate('D, d M Y H:i:s' ) . ' GMT', true, 304 );
	exit();
}


if ( $mode == 'thumb' )
{
	$sql = 'SELECT data,size FROM thumbs WHERE entry=' . $id . ' && custom=' .
		( ( $_GET['args'] && substr( $_GET['args'], 0, 6 ) == 'custom' ) ? 1 : 0 );

	if ( $result = $db->query( $sql ) ) {
		$row = $result->fetch_assoc();
		header( 'Content-Length: ' . $row['size'] );
		header( "content-type: image/jpeg" );
		echo $row['data'];
	}

} else {
	$sql = sprintf( "UPDATE entries SET views=views+1 WHERE id=%d", $parent_id );
	if ( !$db->query( $sql ) ) die( "Query Error" );
	$sql = sprintf( "SELECT id FROM data WHERE entryid=%d order by id", $id );
	$result = $db->query( $sql );
	header('Content-Length: ' . $size );
	header("content-type: image/jpeg");
	while ( $row = $result->fetch_array() )
		$chunks[] = $row[0];
	$size = count( $chunks );
	for( $i = 0; $i < $size; ++$i ) {
		$sql = sprintf( "select filedata from data where id=%d", $chunks[$i] );
		$result = $db->query( $sql );
		$row = $result->fetch_array();
		echo $row[0];
	}
}


$db->close();

?>
