<?php


$id = intval( $entry );

$sql = sprintf( "select title,width,height,size,date,views,ip,safe,hash,child from entries where id=%d", $id );

if ( ! $result = $db->query( $sql ) ) {
	die("Query Error");
}


$entry = $result->fetch_assoc();

if ( $entry['width'] > 800 ) {
	$ratio = $entry['width'] / $entry['height'];
	$width = 800;
	$height = $width / $ratio;
} else {
	$width = $entry['width'];
	$height = $entry['height'];
}

$display_id = ($entry['child']) ? $entry['child'] : $id;

$sql = sprintf( "select t.name from tags t, tagmap m where m.entry=%d && t.id=m.tag", $id );

$info = $result->fetch_assoc();

if ( ! $result = $db->query( $sql ) ) {
	die( "Query Error" );
}

?>
	<div id="content">
	<h2>View</h2>
	URL: <?=$url;?><?=$loc;?>/view/<?=$id;?>/
	<br/>
	Direct: <?=$url;?><?=$loc;?>/image/<?=$id;?>/
	<br/>
	Title: <?=$entry['title'];?>
	<br/>
	Tags: 
	<?
	for($i = 0; $row = $result->fetch_assoc(); ++$i ) {
		if ( $i > 0 ) echo ', ';
		echo '<a href="' . $loc . '/tags/' . urlencode( $row['name'] ) . '/">' . str_replace('_',' ',$row['name']) . '</a>';
	}
	?>
	<br/>
	Worksafe: <?=($entry['safe'] == 1) ? 'Yes' : 'No'; ?>
	<br/>
	Uploaded: <?=date('Y-m-d @ H:i:s', $entry['date']);?> UTC
	<br/>
	Views: <?=$entry['views']; ?>
	<br/>
	Dimentions: <?=$entry['width']?>x<?=$entry['height']?>
	<br/>
	Size: <?=floor($entry['size']/1024)?>kb
	<br/>
	SHA-1 Hash: <?=$entry['hash'];?>
	<br/>
	Uploaded by: <?=$entry['ip'];?>
	<br/>
	<a href="<?=$loc;?>/track/<?=$id;?>/">Track Changes</a>&nbsp;
	<a href="<?=$loc;?>/edit/<?=$id;?>/">Edit Info</a>&nbsp;
	<a href="<?=$loc;?>/delete/<?=$id;?>/">Delete</a>&nbsp;
	<br/>
	<a href="<?=$loc;?>/image/<?=$id;?>/"><img alt="<?=$title;?>" width="<?=$width?>" height="<?=$height?>" src="<?=$loc;?>/image/<?=$display_id;?>/" /></a>
	<br/>
	<h3>Comments:</h3>
	<?php
	$sql = sprintf( "select * from comments where entry=%d order by date desc", $id );
	$result = $db->query( $sql );
	while( $row = $result->fetch_assoc() ) {
	?>
	<p>
	<strong><?=$row['name'];?> (<?=$row['ip'];?>)</strong> - <?=date('Y-m-d @ H:i:s', $row['date']);?><br/>
	<?=$row['content'];?>
	</p>
	<?
	}
	?>
	<form action="<?=$loc;?>/comment/<?=$id;?>/" method="post">
	name <input type="text" name="name" />
	<br/>
	<textarea cols="40" rows="7" name="content"></textarea>
	<br/>
	<input type="submit" value="Post" />
	</form>
	</div>
