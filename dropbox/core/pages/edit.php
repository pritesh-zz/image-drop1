<?php

$id = intval( $entry );

$sql = sprintf( "select title,safe from entries where id=%d", $id );

if ( ! $result = $db->query( $sql ) ) {
	die("Query Error");
}

$entry = $result->fetch_assoc();
$title = $entry['title'];
$rating = $entry['safe'];

$sql = sprintf( "select t.name from tags t, tagmap m where m.entry=%d && t.id=m.tag", $id );

$info = $result->fetch_assoc();

if ( ! $result = $db->query( $sql ) ) {
	die( "Query Error" );
}

$tags = '';
for($i = 0; $row = $result->fetch_assoc(); ++$i ) {
	$tags .= ( $i > 0 ) ? ',' . str_replace('_',' ',$row['name']) : str_replace('_',' ',$row['name']);
}
?>
	<div id="form">
		<h2>Edit</h3>
		<form action="http<?=($secure) ? 's' : '';?>://<?=$url;?><?=$loc;?>/update/<?=$id;?>/" method="post">
		<table id="form_table">
			<tr>
				<td>title</td>
				<td><input type="text" name="title" value="<?=stripslashes( $title ); ?>" /></td>
			</tr>
			<tr>
				<td>tags</td>
				<td><input type="text" name="tags" value="<?=stripslashes( $tags ); ?>" /></td>
			</tr>
			<tr>
				<td>worksafe</td>
				<td>
					<input type="radio" <? if ($rating == 1) echo 'checked="checked"'; ?> name="rating" value="1" /> Yes 
					<input type="radio" <? if ($rating == 0) echo 'checked="checked"'; ?> name="rating" value="0" /> No</td>
			<tr>
				<td>password</td>
				<td><input type="password" name="password" value="" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" value="Update" /></td>
			</tr>
		</table>	
		</form>
	</div>
