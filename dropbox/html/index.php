<?php
// Start debug timming
$ts = microtime(true);

// Bring in the needed stuff and setup the connection to the database
require '../core/conf.php';
require $path . "/core/func.php";

// Process arguments
if ( $_GET['args'] ) {
	// Args are seperated by '/' to make purdy urls
	$args = explode('/', $_GET['args'] );
	
	// Loop through the incoming arguments and setup the section/entry/tags variables
	for ( $i = 0, $c = count( $args ); $i < $c; ++$i ) {
		// Hackish switch statement with lots of confusing drop throughs
		switch ( strval( $args[$i] ) ) {
			// Sections that tage an entry as an argument
			case 'page':
				// the page psudo-section uses the entry arg to as it's current page argument
				$section = 'home';
			case 'view':
			case 'edit':
			case 'update':
			case 'comment':
			case 'track':
			case 'delete':
				// don't want to overwrite section if it has already been set
				if ( !$section ) 
					$section = $args[$i];
				// make sure there is an argument before setting it
				if ( ($i+1) < $c )
					$entry = $args[++$i];
				else
					die('problem');
				break;

			// Sections that do not have any arguments
			case 'stats':
			case 'about':
			case 'help':
			case 'tagfield':
			case 'upload':
			case 'submit':
			case 'hide':
			case 'search':
				if ( !$section )
					$section = $args[$i];
				break;
			
			// Sections that use tags
			case 'tags':
				if ( ($i+1) < $c ) 
					$tags = $args[++$i];
				else
					die('problem');

			// Currently setting the default section outside the switch
			default:
				break;
		}
	}
}

// Since the default section isn't set in the switch make sure we have a section
if ( !$section ) $section = 'home';

ob_start(); // temp hack to get redirections on outputless pages to work.

// Output our shit, header followed by section content and lastly the footer. Oh and the debugging poop.
require $path . "/core/header.php";
require $path . "/core/pages/" . $section . ".php";

// Debug script timing
$te = microtime(true);
$t = round( $te - $ts, 4 );
echo "<br/>script executed in $t seconds<br/>";

require $path . "/core/footer.php";

ob_flush(); // temp hack to get redirections on outputless pages to work.

?>
