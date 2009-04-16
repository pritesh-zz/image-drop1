<?php

$id = intval( $entry );

if ( $_POST ) {

	$name = ( $_POST['name'] ) ? strval( $_POST['name'] ) : 'annonymous';
	$content = strval( $_POST['content'] );
	$host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) . ' (' . $_SERVER['REMOTE_ADDR'] . ')';

	if ( $content ) {
		$sql = sprintf( "insert into comments (entry,name,content,ip,date) values (%d,'%s','%s','%s',%d)",
						$id, $name, $content, $host, time() );
		if ( !$db->query( $sql ) )
			die( "error in query" );
	}
} 

header("Location: $loc/view/$id/");

?>
