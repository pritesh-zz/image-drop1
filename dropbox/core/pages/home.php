<?php

// If there are tags
if ( $tags ) {
        $taglist = split( ' ', $tags );
        foreach ( $taglist as $k => $value ) {
                $tag = $db->real_escape_string( strval( $value ) );
                switch ( $value{0} ) {
                        case '-': # don't show entries with these tags
                                $tag = substr( $tag, 1 );
                                $joins .= " left outer join tagmap t{$k} on t{$k}.entry=entries.id && t{$k}.tag=(SELECT id FROM tags WHERE name='$tag') ";
                                $where[] = " t{$k}.tag is null ";
                                break;
                        case '~':
                                # not implemented yet
                                break;
						case 's': # sorting
							if ( ( strlen( $value ) > 2 ) && $value{1} == ':' ) {
								switch ( substr( $value, 2 ) ) {
									case 's':
									case 'size':
										$sort = 'size';
										break;
									case 'v':
									case 'views':
										$sort = 'views';
										break;
									case 'd':
									case 'date':
									default:
										$sort = 'date';
										break;
								}
								break;
							}
                        default: # show entries with these tags
							//if ( substr( $value, -1, 1 ) == '*' )
							//	$joins .= " inner join tagmap t{$k} on t{$k}.entry=entries.id && t{$k}.tag=(SELECT id FROM tags WHERE name like '%" .
							//			substr($tag, 0, -1) . "%') ";
							//else
                                $joins .= " inner join tagmap t{$k} on t{$k}.entry=entries.id && t{$k}.tag=(SELECT id FROM tags WHERE name='$tag') ";
                            break;
                }
        }
        if ( $where ) 
                $where = implode( ' && ', $where );
}

if ( isset( $_SESSION['hide'] ) )
	$where .= ' && safe=1 ';

$page = ($entry) ? $entry : 1;

if ( !$sort )
	$sort = 'date';
$direction = 'desc';
$count = 50;
$offset = $count * ($page - 1);

$sql = sprintf( "select SQL_CALC_FOUND_ROWS id,title from entries %s where parent is null %s order by %s %s limit %d offset %d",
                        $joins, $where, $sort, $direction, $count, $offset );
#print $sql;
$result = $db->query( $sql );
$num = array_pop( $db->query( "SELECT FOUND_ROWS()" )->fetch_row() );
if ( ! $result ) die("Failed Query");

?>
	<?php tagField( $db, 50 ); ?>
	<div id="search">
	<form action="/search/" method="get">
	search <input type="text" name="search" value="<?=$tags;?>" />
	</form>
	</div>
	<div id="images">
	<? for($i = 1; $row = $result->fetch_assoc(); ++$i ) { ?>	
		<a title="<?=$row['title'];?>" href="<?=$loc;?>/view/<?=$row['id'];?>/">
			<img src="<?=$loc;?>/thumb/<?=$row['id'];?>/" alt="<?=$row['title'];?>" />
		</a>
	<? } ?>
	</div>
<?
$pages = ceil( $num / $count );
$tagurl = ($tags) ? "/tags/$tags" : '';
if ( $pages > 1 && $page > 1 )
	echo "<a href=\"$tagurl/page/" . ($page-1) . "/\">&lt;prev</a>";
if ( $pages > 1 ) {
	for ( $i = 1; $i <= $pages; ++$i ) {
		if ( $i != $page )
			echo " <a href=\"$tagurl/page/$i/\">$i</a> ";
		else
			echo " $i ";
	}
	if ( $page < $pages )
		echo "<a href=\"$tagurl/page/" . ($page+1) . "/\">next&gt;</a>";
}
?>
