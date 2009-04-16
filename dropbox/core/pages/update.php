<?php

// Got to be sure that we have some post data to work with
if ( $_POST ) {
	$id = intval( $entry );
	$title = strval( $_POST['title'] );
	$safe = intval( $_POST['rating'] );
	$host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) . ' (' . $_SERVER['REMOTE_ADDR'] . ')';
	
	// get some of the existing info so we can compare it later
	$sql = sprintf("select password,title,safe from entries where id=%d", $id );
	$result = $db->query( $sql );
	$entry = $result->fetch_assoc();

	// passwords match? rather important
	if ( $entry['password'] == $db->real_escape_string( strval( $_POST['password'] ) ) ) {

	// some tags? if not default to 'none'
	if ( $_POST['tags'] ) {
		$tags = explode( ',', str_replace( ' ', '_', trim( strval( $_POST['tags'] ) ) ) );
		sort( $tags, SORT_STRING );
		$tag_count = count( $tags );
	} else {
		$tags[] = 'none';
		$tag_count = 1;
	}

	// make any changes to title or worksafe status
	// FIXME
	// only want to make this query if a change has actually happened
	$sql = sprintf( "update entries set title='%s',safe=%d where id=%d", $db->real_escape_string( $title ), $safe, $id );
	if ( !$db->query( $sql ) )
		die('error in query');

	// add update record for a changed title
	if ( $title != $entry['title'] ) {
		$sql = sprintf( "insert into updates (entry,ip,date,field,`from`,`to`) values (%d,'%s',%d,'%s','%s','%s')",
						$id,$host,time(),'title',$entry['title'],$title );
		if ( !$db->query( $sql ) )
			die('error in query');
	}

	// add update record for a changed worksafe status
	if ( $safe != $entry['safe'] ) {
		$oldrating = ($entry['safe'] == 1) ? 'Yes' : 'No';
		$newrating = ($safe == 1) ? 'Yes' : 'No';
		$sql = sprintf( "insert into updates (entry,ip,date,field,`from`,`to`) values (%d,'%s',%d,'%s','%s','%s')",
						$id,$host,time(),'worksafe',$oldrating,$newrating );
		if ( !$db->query( $sql ) )
			die('error in query');
	}

	// get the existing tags to compare to the ones in the post data
	$sql = sprintf( "select t.name from tags t, tagmap m where m.entry=%d && t.id=m.tag order by t.name", $id );
	$result = $db->query( $sql );
	for($i = 0; $row = $result->fetch_assoc(); ++$i ) {
		$existingtags[] = $row['name'];
		if ( $i > 0 ) $oldtags .=',';
		$oldtags .= $row['name'];
	}
	$newtags = implode( ',', $tags );

	// have the tags been updated?
	if ( $newtags != $oldtags ) {

		// clear out existing entry => tag relationships
		// FIXME
		// plan on only removing entries that have changed in the future
		$sql = sprintf( "delete from tagmap where entry=%d", $id );
		if ( !$db->query( $sql ) )
			die('error in query');

		// loop over tags in post
		for ( $i = 0; $i < $tag_count; ++$i ) {
			$cur = trim( str_replace( ' ', '_', strtolower( $tags[$i] ) ) );
			$sql = sprintf( "select id from tags where name='%s'", $cur );
			$result = $db->query( $sql );
			// should this be an update or insert?
			if ( $result->num_rows < 1 ) {
				$sql = sprintf( "insert into tags (name,date) values ('%s',UNIX_TIMESTAMP())", $cur );
				$db->query( $sql );
				$tag_id = $db->insert_id;
			} else {
				$row = $result->fetch_array();
				$tag_id = $row[0];
				// If tag wasn't already assosiated with this image update the tag timestamp
				if ( !in_array( $cur, $existingtags ) ) {
					$sql = sprintf( "update tags set date=UNIX_TIMESTAMP() where id=%d", $tag_id );
					$db->query( $sql );
				}
			}
			// insert entry => tag records to tagmap
			$sql = sprintf( "insert into tagmap (tag,entry) values (%d,%d)", $tag_id, $id );
			$db->query( $sql );
		}

		// add update record for changes to tags
		$sql = sprintf( "insert into updates (entry,ip,date,field,`from`,`to`) values (%d,'%s',%d,'%s','%s','%s')",
						$id,$host,time(),'tags',$oldtags,$newtags );
		if ( !$db->query( $sql ) )
			die('error in query');

	}

	} else {
		header("location: http://" . $url . $loc . "/edit/" . $id . "/");
	}
}

header("location: http://" . $url . $loc . "/view/" . $id . "/");

$db->close();

?>
