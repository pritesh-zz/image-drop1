<?php
if ( $entry )
	$id = intval( $entry );
?>

<h3>Updates:</h3>
<?php
if ( $id )
	$sql = sprintf( "select * from updates where entry=%d order by date desc", $id );
else
	$sql = sprintf( "select e.title,u.* from entries e, updates u where e.id=u.entry order by u.date desc" );
$result = $db->query( $sql );
while ( $row = $result->fetch_assoc() ) {
if ( $id ) {
?>
<strong><?=$row['ip'];?></strong> updated (<?=$row['field'];?>) from '<?=$row['from'];?>' to '<?=$row['to'];?>' on <?=date('Y-m-d @ H:i:s', $row['date']);?><br/>
<?php
} else {
?>
<p>
<strong><a href="/view/<?=$row['entry'];?>/"><?=$row['title'];?></a></strong>
<br/>
<?=$row['ip'];?> updated (<?=$row['field'];?>) from '<?=$row['from'];?>' to '<?=$row['to'];?>' on <?=date('Y-m-d @ H:i:s', $row['date']);?>
<br/>
</p>
<?php
}
}
?>
