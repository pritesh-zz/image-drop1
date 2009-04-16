<?php

$stats_file = $path . "/core/stats.php";
$stats_date = filemtime( $stats_file );

// GENERATE STATISTICS (once an hour)
if ( ( time() - $stats_date >= 3600 ) ) {

$sql = "SELECT count(*) count FROM entries";
$result = $db->query( $sql );
$row = $result->fetch_array();
$num_images = $row[0];
$result->close();

$sql = "SELECT count(*) count FROM data";
$result = $db->query( $sql );
$row = $result->fetch_array();
$num_chunks = $row[0];
$result->close();

$sql = "SELECT SUM(size) size FROM entries";
$result = $db->query( $sql );
$row = $result->fetch_array();
$db_size = round( $row[0] / 1024 / 1024, 2 ); 
$result->close();

$sql = "SELECT DISTINCT ip FROM entries";
$result = $db->query( $sql );
$ips = $result->num_rows;
$result->close();

$sql = "SELECT count(*) count FROM tags";
$result = $db->query( $sql );
$row = $result->fetch_array();
$num_tags = $row[0];
$result->close();

$sql = "SELECT tag, count(entry) num from tagmap group by tag";
$result = $db->query( $sql );
while ( $row = $result->fetch_assoc() ) {
	if ( isset( $min ) ) {
		if ( $row['num'] < $min['num'] ) {
			$min = $row;
		}
	} else {
		$min = $row;
	}
	if ( isset( $max ) ) {
		if ( $row['num'] > $max['num'] ) {
			$max = $row;
		}
	} else {
 		$max = $row;
	}
}
$result->close();

$sql = "SELECT name from tags where id=" . $min['tag'];
$result = $db->query( $sql );
$row = $result->fetch_array();
$tag_min = $row[0];
$result->close();

$sql = "SELECT name from tags where id=" . $max['tag'];
$result = $db->query( $sql );
$row = $result->fetch_array();
$tag_max = $row[0];
$result->close();

$sql = "SELECT SUM(views) sum FROM entries";
$result = $db->query( $sql );
$row = $result->fetch_array();
$num_views = $row[0];
$result->close();

$sql = "SELECT id,title,views FROM entries ORDER BY views DESC LIMIT 1";
$result = $db->query( $sql );
$row = $result->fetch_array();
$most_views = $row;
$result->close();

// Date of oldest image
$sql = "SELECT date,id FROM entries order by date limit 1";
$result = $db->query( $sql );
$row = $result->fetch_array();
$oldest_image = $row;
$result->close();

// Date of newest image
$sql = "SELECT date,id FROM entries order by date desc limit 1";
$result = $db->query( $sql );
$row = $result->fetch_array();
$newest_image = $row;
$result->close();

$stats_data = '<?php ';
$stats_data .= sprintf( '%s = "%s";', '$num_images', $num_images );
$stats_data .= sprintf( '%s = "%s";', '$num_chunks', $num_chunks );
$stats_data .= sprintf( '%s = "%s";', '$db_size', $db_size );
$stats_data .= sprintf( '%s = "%s";', '$num_views', $num_views );
$stats_data .= sprintf( '%s = "%s";', '$ips', $ips );
$stats_data .= sprintf( '%s = "%s";', '$num_tags', $num_tags );
$stats_data .= sprintf( '%s = "%s";', '$tag_min', $tag_min );
$stats_data .= sprintf( '%s = "%s";', '$tag_max', $tag_max );
$stats_data .= sprintf( '%s = %s;', '$oldest_image', var_export( $oldest_image, true ) );
$stats_data .= sprintf( '%s = %s;', '$newest_image', var_export( $newest_image, true ) );
$stats_data .= sprintf( '%s = %s;', '$most_views', var_export( $most_views, true ) );
$stats_data .= '?>';

file_put_contents( $stats_file, $stats_data );

} else {
	include $stats_file;
}
?>

<div id="statistics">
<h2>Statistics</h2>
<p>Stats are generated once an hour</p>
<p>stats last generated: <?=date( 'Y-m-d @ H:i:s', $stats_date );?> UTC</p>
<table>
	<tr>
		<td>Number of uploaded images:</td>
		<td><?=$num_images;?></td>
	</tr>
	<tr>
		<td>Total size of database:</td>
		<td><?=$db_size;?>mb</tb>
	</tr>
	<tr>
		<td>Number of image chunks:</td>
		<td><?=$num_chunks;?></td>
	</tr>
	<tr>
		<td>Number of image views:</td>
		<td><?=$num_views;?></td>
	</tr>
	<tr>
		<td>Image with most views:</td>
		<td><a href="<?=$loc;?>/view/<?=$most_views[0];?>/"><?=$most_views[2];?> - <?=$most_views[1];?></a></td>
	</tr>
	<tr>
		<td>Oldest image uploaded on:</td>
		<td><a href="<?=$loc;?>/view/<?=$oldest_image[1];?>/"><?=date('Y-m-d @ H:i:s', $oldest_image[0] ); ?> UTC</a></td>
	</tr>
	<tr>
		<td>Newest image uploaded on:</td>
		<td><a href="<?=$loc;?>/view/<?=$newest_image[1];?>/"><?=date('Y-m-d @ H:i:s', $newest_image[0] ); ?> UTC</a></td>
	</tr>
	<tr>
		<td>Average uploads per day:</td>
		<td><?=round( $num_images / ( ceil( ( $newest_image[0] - $oldest_image[0] ) / 82400 ) + 1 ), 2 );?></td>
	</tr>
	<tr>
		<td>Distinct uploaders:</td>
		<td><?=$ips;?></td>
	</tr>
	<tr>
		<td>Number of tags:</td>
		<td><?=$num_tags;?></td>
	</tr>
	<tr>
		<td>Tag with most images:</td>
		<td><a href="<?=$loc;?>/tags/<?=urlencode($tag_max);?>/"><?=$tag_max;?></a></td>
	</tr>
	<tr>
		<td>Tag with least images:</td>
		<td><a href="<?=$loc;?>/tags/<?=urlencode($tag_min);?>/"><?=$tag_min;?></a></td>
	</tr>
</table>

</div>

