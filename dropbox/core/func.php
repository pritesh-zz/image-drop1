<?php

function fail()
{
	die("oops");
}

function tagField($db,$limit=null)
{
	global $loc;

	$sql = "select t.name, m.tag, count(m.tag) as num from tagmap as m, tags as t where t.id=m.tag group by tag order by date desc";
	$sql .= (!is_null($limit)) ? ' LIMIT ' . $limit : null;
	$max_size = 250;
	$min_size = 100;

	if ( ! $result = $db->query( $sql ) ) die("Failed Query");

	while( $row = $result->fetch_assoc() ) {
		$tags[$row['name']] = $row['num'];
	}

	$max_value = max(array_values($tags));
	$min_value = min(array_values($tags));
	$spread = $max_value - $min_value;
	if ( $spread == 0 ) $spread = 1;
	$step = ($max_size - $min_size) / $spread;

	?>
	<div id="tags">
	<?php 
	foreach ( $tags as $key => $value ) {
		$size = ceil( $min_size + (($value - $min_value) * $step) );
	?>
		<a style="font-size: <?=$size;?>%" href="<?=$loc;?>/tags/<?=urlencode($key);?>/"><?=str_replace('_',' ',$key);?></a>(<?=$value;?>), <? } ?> <a href="<?=$loc;?>/">all</a>
	</div>
	<?php
}

?>
