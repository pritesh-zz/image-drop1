<?php
// FIXME - need to change all the "die" calls to some kind of real error handeling system to give
// the user a chance to correct the issue.

// Make sure we have some data to process
if ( $_POST ) {
	if ( is_uploaded_file( $_FILES['image']['tmp_name'] ) ) {
		if ( ! $img = getimagesize( $_FILES['image']['tmp_name'] ) ) // FIXME - kludge fix to check for an actual image
			die( "Not an image" );
		$title = strval( $_POST['title'] );
		$size = filesize( $_FILES['image']['tmp_name'] );
		$preview = 0; //( $size > 1048576 );
		$hash = sha1_file( $_FILES['image']['tmp_name'] );
		$width = 100;
		$height = 100;
		$width_orig = $img[0];
		$height_orig = $img[1];
		$password = strval( $_POST['password'] );
		$host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) . ' (' . $_SERVER['REMOTE_ADDR'] . ')';
		$safe = intval( $_POST['rating'] );

		// Check if a possible duplicate exists in the database already
		$sql = sprintf( "SELECT id FROM entries where hash='%s'", $hash );
		$result = $db->query( $sql );
		if ( $result->num_rows < 1 ) {

			// Setup tag array and how many tags are there.
			if ( $_POST['tags'] ) {
				$tags = explode( ',', strval( $_POST['tags'] ) ); // will be trimed for extranious whitespace later
				$tag_count = count( $tags );
			} else {
				$tags[] = 'none';
				$tag_count = 1;
			}

			$ratio_orig = $width_orig/$height_orig;

			if ( $width/$height > $ratio_orig) {
				$width = $height * $ratio_orig;
			} else {
				$height = $width / $ratio_orig;
			}

			$image_thumb = imagecreatetruecolor( floor( $width ), floor( $height ) );
			$image = imagecreatefromstring( file_get_contents( $_FILES['image']['tmp_name'] ) );

			if ( intval( $_POST['custom'] ) == 1 ) {
				$crop = true;

				if ( intval( $_POST['square'] ) == 0 ) {
					$crop = false;
					if ( $endwidth/$endheight > $ratio_orig ) {
						$endwidth = $endheight * $ratio_orig;
					} else {
						$endheight = $endwidth / $ratio_orig;
					}
				} else {
					$endwidth = intval( $_POST['size'] );
					$endheight = intval( $_POST['size'] );
				}

				if ( ( $endwidth / $endheight ) > ( $width_orig / $height_orig ) ) {
					$newwidth = $endwidth;
					$newheight = $height_orig * ( $endwidth / $width_orig );
				} else {
					$newwidth = $width_orig * ( $endheight / $height_orig );
					$newheight = $endheight;
				}

				$custom_thumb = imagecreatetruecolor( $newwidth, $newheight );
				imagecopyresampled( $custom_thumb, $image, 0, 0, 0, 0, $newwidth, $newheight, $width_orig, $height_orig );

				if ( $crop ) {
					switch( strval( $_POST['crop'] ) ) {
						case 'middle':
							$x = ($newwidth - $endwidth) / 2;
							$y = ($newheight - $endheight) / 2;
							break;
						case 'top':
							$x = 0;
							$y = 0;
							break;
						case 'bottom':
							$x = $newwidth - $endwidth;
							$y = $newheight - $endheight;
							break;
					}

					$temp_image = imagecreatetruecolor( $endwidth, $endheight );
					imagecopyresampled( $temp_image, $custom_thumb, 0, 0, $x, $y, $endwidth, $endheight, $endwidth, $endheight );
					imagedestroy( $custom_thumb );
					$custom_thumb = $temp_image;
				}

				ob_start();
					imagejpeg($custom_thumb, "", 100);
					imagedestroy( $custom_thumb );
					$custom_thumb = ob_get_contents();
				ob_end_clean();
			}


			// Create thumbnail and free fullsize image
			imagecopyresampled( $image_thumb, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig );
			if ( $preview ) {
				$image_preview = imagecreatetruecolor( floor( $width * 5 ), floor( $height * 5 ) );
				imagecopyresampled( $image_preview, $image, 0, 0, 0, 0, ($width*5), ($height*5), $width_orig, $height_orig );
			}
			imagedestroy( $image );

			// Catch the thumbnail image to a usable variable
			ob_start();
				imagejpeg($image_thumb, "", 100);
				imagedestroy( $image_thumb );
				$thumb = ob_get_contents();
			ob_end_clean();

			// if a preview image is needed to the same
			if ( $preview ) {
				ob_start();
					imagejpeg($image_preview, "", 100);
					imagedestroy( $image_preview );
					$preview_image = ob_get_contents();
				ob_end_clean();
			}

			$sql = sprintf( "insert into entries (title,type,size,width,height,ip,password,views,date,safe,hash) 
							values ('%s',%d, %d, %d, %d, '%s', '%s', 0, UNIX_TIMESTAMP(), %d,'%s')",
						$db->real_escape_string( $title ),
						$img[2],
						$size,
						$width_orig,
						$height_orig,
						$db->real_escape_string( $host ),
						$db->real_escape_string( $password ),
						$safe,
						$hash
						);

			if ( !$db->query( $sql ) )
				die('error in query1 : ' . $sql);
			$id = $db->insert_id;	

			$sql = sprintf( "insert into thumbs (data,entry,size) values ('%s',%d,%d)",
							$db->real_escape_string( $thumb ),
							$id, strlen( $thumb ) );
			if ( !$db->query( $sql ) )
				die('error in query2 : ' . $sql);

			if ( isset($custom_thumb) ) {
				$sql = sprintf( "insert into thumbs (data,entry,custom,size) values ('%s',%d,1,%d)",
								$db->real_escape_string( $custom_thumb ), $id, strlen( $custom_thumb ) );
				if ( !$db->query( $sql ) )
					die('error in query3 : ' . $sql );
			}

			// Write the image chunks out to the database
			$fp = fopen( $_FILES['image']['tmp_name'], 'rb' );
			while ( !feof( $fp ) ) {
				$sql = sprintf( "insert into data (entryid, filedata) values (%d, '%s')",
						$id,
						$db->real_escape_string( fread( $fp, 65535 ) ) );
				if ( !$db->query( $sql ) ) die("error!!!");
			}
			fclose( $fp );

			// If image dimentions are > some threshold I haven't decieded on create a child image
			// that is a redused size preview image for display on the "view" page.
			if ( $preview ) {
				$sql = sprintf( "insert into entries (parent,date,size) values (%d,UNIX_TIMESTAMP(),%d)", $id, $size );
				$db->query( $sql );
				$preview_id = $db->insert_id;
				$sql = sprintf( "update entries set child=%d where id=%d", $preview_id, $id );
				$db->query( $sql );

				// Write the preview chunks out to the database
			//	$fp = fopen( 'php://memory', 'r+' );
			//	fwrite( $fp, $preview_image );
			//	while( !feof( $fp ) ) {
			//		die( fread( $fp, 65535 ) );
					$sql = sprintf( "insert into data (entryid, filedata) values (%d, '%s')",
						$preview_id,
						$db->real_escape_string( $preview_image ) );
					if ( !$db->query( $sql ) ) die("error!");
				unset( $preview_image );
			//	}
				//fclose( $fp );
			}

			// Add/Update tags
			for ( $i = 0; $i < $tag_count; ++$i ) {
				$cur = str_replace( ' ', '_', strtolower( trim( $tags[$i] ) ) );
				$sql = sprintf( "select id from tags where name='%s'", $cur );
				$result = $db->query( $sql );

				// If the tag doesn't exist add it to the database, if it does update it's date field
				if ( $result->num_rows < 1 ) {
					$sql = sprintf( "insert into tags (name,date) values ('%s',UNIX_TIMESTAMP())", $cur );
					$db->query( $sql );
					$tag_id = $db->insert_id;
				} else {
					$row = $result->fetch_array();
					$tag_id = $row[0];
					$sql = sprintf( "update tags set date=UNIX_TIMESTAMP() where id=%d", $tag_id );
					$db->query( $sql );
				}

				// Add entry => tag relationship
				$sql = sprintf( "insert into tagmap (tag,entry) values (%d,%d)", $tag_id, $id );
				$db->query( $sql );
			}

		} else {
			die("Duplicate image in database");
		}

	} else {
		die("Internal Error while uploading image");
	}
} else {
	die("Internal Error while uploading image");
}


header("location: http://" . $url . $loc . "/view/" . $id . "/");

$db->close();

?>
