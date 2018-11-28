<?php if (php_sapi_name() != 'cli') { die('Unauthorized Access'); } ?>
<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
// DB details
$host = '127.0.0.1';	// normally localhost, but not necessarily.
$data = 'wheatridge_mage';	// your database
$user = 'root';	// your db userid
$pass = 'Unl3aded';	// your db password
$char = '';	// your db charset you can leave this blank
// Search replace details
$srch = 'wheatridgecyclery.uldev.co/store';
$rplc = 'local.wheatridgecyclery.com/store';
// Tables to scanned

$tables = array();

$exclude_cols = array( ); // Add columns to be excluded from changes to this array. Exclude guid column for Wordpress for integrety
/**
 *
 * Safe Search and Replace on Database with Serialized Data v2.0.1
 *
 * This script is to solve the problem of doing database search and replace when
 * developers have only gone and used the non-relational concept of serializing
 * PHP arrays into single database columns.  It will search for all matching
 * data on the database and change it, even if it's within a serialized PHP
 * array.
 *
 * The big problem with serialised arrays is that if you do a normal DB style
 * search and replace the lengths get mucked up.  This search deals with the
 * problem by unserializing and reserializing the entire contents of the
 * database you're working on.  It then carries out a search and replace on the
 * data it finds, and dumps it back to the database.  So far it appears to work
 * very well.  It was coded for our WordPress work where we often have to move
 * large databases across servers, but I designed it to work with any database.
 * Biggest worry for you is that you may not want to do a search and replace on
 * every damn table - well, if you want, simply add some exclusions in the table
 * loop and you'll be fine.  If you don't know how, you possibly shouldn't be
 * using this script anyway.
 *
 * To use, simply configure the settings below and off you go.  I wouldn't
 * expect the script to take more than a few seconds on most machines.
 *
 * BIG WARNING!  Take a backup first, and carefully test the results of this
 * code. If you don't, and you vape your data then you only have yourself to
 * blame. Seriously.  And if you're English is bad and you don't fully
 * understand the instructions then STOP. Right there. Yes. Before you do any
 * damage.
 *
 * USE OF THIS SCRIPT IS ENTIRELY AT YOUR OWN RISK. I/We accept no liability
 * from its use.
 *
 * First Written 2009-05-25 by David Coveney of Interconnect IT Ltd (UK)
 * http://www.davidcoveney.com or http://www.interconnectit.com
 * and released under the WTFPL
 * ie, do what ever you want with the code, and we take no responsibility for it
 * OK? If you don't wish to take responsibility, hire us at Interconnect IT Ltd
 * on +44 (0)151 331 5140 and we will do the work for you, but at a cost,
 * minimum 1hr
 *
 * To view the WTFPL go to http://sam.zoy.org/wtfpl/ (WARNING: it's a little
 * rude, if you're sensitive);
 *
 *
 * Version 2.1.0:
 *              - Changed to version 2.1.0 
 *		* Following change by Sergei Biryukov - merged in and tested by Dave Coveney
 *              - Added Charset Support (tested with UTF-8, not tested on other charsets)
 *		* Following changes implemented by James Whitehead with thanks to all the commenters and feedback given!
 * 		- Removed PHP warnings if you go to step 3+ without DB details.
 * 		- Added options to skip changing the guid column. If there are other
 * 		columns that need excluding you can add them to the $exclude_cols global
 * 		array. May choose to add another option to the table select page to let
 * 		you add to this array from the front end.
 * 		- Minor tweak to label styling.
 * 		- Added comments to each of the functions.
 * 		- Removed a dead param from icit_srdb_replacer
 * Version 2.0.0:
 * 		- returned to using unserialize function to check if string is
 * 		serialized or not
 * 		- marked is_serialized_string function as deprecated
 * 		- changed form order to improve usability and make use on multisites a
 * 		bit less scary
 * 		- changed to version 2, as really should have done when the UI was
 * 		introduced
 * 		- added a recursive array walker to deal with serialized strings being
 * 		stored in serialized strings. Yes, really.
 * 		- changes by James R Whitehead (kudos for recursive walker) and David
 * 		Coveney 2011-08-26
 *  Version 1.0.2:
 *  	- typos corrected, button text tweak - David Coveney / Robert O'Rourke
 *  Version 1.0.1
 *  	- styling and form added by James R Whitehead.
 *
 *  Credits:  moz667 at gmail dot com for his recursive_array_replace posted at
 *            uk.php.net which saved me a little time - a perfect sample for me
 *            and seems to work in all cases.
 *
 */

/**
 * Walk and array replacing one element for another. ( NOT USED ANY MORE )
 *
 * @param string $find    The string we want to replace.
 * @param string $replace What we'll be replacing it with.
 * @param array $data    Used to pass any subordinate arrays back to the
 * function for searching.
 *
 * @return array    The original array with the replacements made.
 */
function recursive_array_replace( $find, $replace, $data ) {
    if ( is_array( $data ) ) {
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                recursive_array_replace( $find, $replace, $data[ $key ] );
            } else {
                // have to check if it's string to ensure no switching to string for booleans/numbers/nulls - don't need any nasty conversions
                if ( is_string( $value ) )
					$data[ $key ] = str_replace( $find, $replace, $value );
            }
        }
    } else {
        if ( is_string( $data ) )
			$data = str_replace( $find, $replace, $data );
    }
}


/**
 * Take a serialised array and unserialise it replacing elements as needed and
 * unserialising any subordinate arrays and performing the replace on those too.
 *
 * @param string $from       String we're looking to replace.
 * @param string $to         What we want it to be replaced with
 * @param array  $data       Used to pass any subordinate arrays back to in.
 * @param bool   $serialised Does the array passed via $data need serialising.
 *
 * @return array	The original array with all elements replaced as needed.
 */
function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {

	// some unseriliased data cannot be re-serialised eg. SimpleXMLElements
	try {

		if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
			$data = recursive_unserialize_replace( $from, $to, $unserialized, true );
		}

		elseif ( is_array( $data ) ) {
			$_tmp = array( );
			foreach ( $data as $key => $value ) {
				$_tmp[ $key ] = recursive_unserialize_replace( $from, $to, $value, false );
			}

			$data = $_tmp;
			unset( $_tmp );
		}

		else {
			if ( is_string( $data ) )
				$data = str_replace( $from, $to, $data );
		}

		if ( $serialised )
			return serialize( $data );

	} catch( Exception $error ) {

	}

	return $data;
}


/**
 * Is the string we're dealing with a serialised string? ( NOT USED ANY MORE )
 *
 * @param string $data The string we want to check
 *
 * @return bool    true if serialised.
 */
function is_serialized_string( $data ) {
	// if it isn't a string, it isn't a serialized string
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( preg_match( '/^s:[0-9]+:.*;$/s', $data ) ) // this should fetch all serialized strings
		return true;
	return false;
}



/**
 * Take an array and turn it into an English formatted list. Like so:
 * array( 'a', 'b', 'c', 'd' ); = a, b, c, or d.
 *
 * @param array $input_arr The source array
 *
 * @return string    English formatted string
 */
function eng_list( $input_arr = array( ), $sep = ', ', $before = '"', $after = '"' ) {
	if ( ! is_array( $input_arr ) )
		return false;

	$_tmp = $input_arr;

	if ( count( $_tmp ) >= 2 ) {
		$end2 = array_pop( $_tmp );
		$end1 = array_pop( $_tmp );
		array_push( $_tmp, $end1 . $after . ' or ' . $before . $end2 );
	}

	return $before . implode( $before . $sep . $after, $_tmp ) . $after;
}


/**
 * Search through the file name passed for a set of defines used to set up
 * WordPress db access.
 *
 * @param string $filename The file name we need to scan for the defines.
 *
 * @return array    List of db connection details.
 */
function icit_srdb_define_find( $filename = 'wp-config.php' ) {

	$filename = dirname( __FILE__ ) . '/' . basename( $filename );

	if ( file_exists( $filename ) && is_file( $filename ) && is_readable( $filename ) ) {
		$file = @fopen( $filename, 'r' );
		$file_content = fread( $file, filesize( $filename ) );
		@fclose( $file );
	}

	preg_match_all( '/define\s*?\(\s*?([\'"])(DB_NAME|DB_USER|DB_PASSWORD|DB_HOST|DB_CHARSET)\1\s*?,\s*?([\'"])([^\3]*?)\3\s*?\)\s*?;/si', $file_content, $defines );

	if ( ( isset( $defines[ 2 ] ) && ! empty( $defines[ 2 ] ) ) && ( isset( $defines[ 4 ] ) && ! empty( $defines[ 4 ] ) ) ) {
		foreach( $defines[ 2 ] as $key => $define ) {

			switch( $define ) {
				case 'DB_NAME':
					$name = $defines[ 4 ][ $key ];
					break;
				case 'DB_USER':
					$user = $defines[ 4 ][ $key ];
					break;
				case 'DB_PASSWORD':
					$pass = $defines[ 4 ][ $key ];
					break;
				case 'DB_HOST':
					$host = $defines[ 4 ][ $key ];
					break;
				case 'DB_CHARSET':
					$char = $defines[ 4 ][ $key ];
					break;
			}
		}
	}

	return array( $host, $name, $user, $pass, $char );
}

/**
 * Used to check the $_post tables array and remove any that don't exist.
 *
 * @param array $table The list of tables from the $_post var to be checked.
 *
 * @return array	Same array as passed in but with any tables that don'e exist removed.
 */
function check_table_array( $table = '' ){
	global $all_tables;
	return in_array( $table, $all_tables );
}

/**
 * The main loop triggered in step 5. Up here to keep it out of the way of the
 * HTML. This walks every table in the db that was selected in step 3 and then
 * walks every row and column replacing all occurences of a string with another.
 * We split large tables into 50,000 row blocks when dealing with them to save
 * on memmory consumption.
 *
 * @param mysql  $connection The db connection object
 * @param string $search     What we want to replace
 * @param string $replace    What we want to replace it with.
 * @param array  $tables     The tables we want to look at.
 *
 * @return array    Collection of information gathered during the run.
 */
function icit_srdb_replacer( $connection, $search = '', $replace = '', $tables = array( ) ) {
	global $guid, $exclude_cols;

	$report = array( 'tables' => 0,
					 'rows' => 0,
					 'change' => 0,
					 'updates' => 0,
					 'start' => microtime( ),
					 'end' => microtime( ),
					 'errors' => array( ),
					 );

	if ( is_array( $tables ) && ! empty( $tables ) ) {
		foreach( $tables as $table ) {
			$report[ 'tables' ]++;

			$columns = array( );

			// Get a list of columns in this table
		    $fields = mysql_query( 'DESCRIBE ' . $table, $connection );
			while( $column = mysql_fetch_array( $fields ) )
				$columns[ $column[ 'Field' ] ] = $column[ 'Key' ] == 'PRI' ? true : false;

			// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
			$row_count = mysql_query( 'SELECT COUNT(*) FROM ' . $table, $connection );
			$rows_result = mysql_fetch_array( $row_count );
			$row_count = $rows_result[ 0 ];
			if ( $row_count == 0 )
				continue;

			$page_size = 50000;
			$pages = ceil( $row_count / $page_size );

			for( $page = 0; $page < $pages; $page++ ) {

				$current_row = 0;
				$start = $page * $page_size;
				$end = $start + $page_size;
				// Grab the content of the table
				$data = mysql_query( sprintf( 'SELECT * FROM %s LIMIT %d, %d', $table, $start, $end ), $connection );

				if ( ! $data )
					$report[ 'errors' ][] = mysql_error( );

				while ( $row = mysql_fetch_array( $data ) ) {

					$report[ 'rows' ]++; // Increment the row counter
					$current_row++;

					$update_sql = array( );
					$where_sql = array( );
					$upd = false;

					foreach( $columns as $column => $primary_key ) {
						if ( $guid == 1 && in_array( $column, $exclude_cols ) )
							continue;

						$edited_data = $data_to_fix = $row[ $column ];

						// Run a search replace on the data that'll respect the serialisation.
						$edited_data = recursive_unserialize_replace( $search, $replace, $data_to_fix );

						// Something was changed
						if ( $edited_data != $data_to_fix ) {
							$report[ 'change' ]++;
							$update_sql[] = $column . ' = "' . mysql_real_escape_string( $edited_data ) . '"';
							$upd = true;
						}

						if ( $primary_key )
							$where_sql[] = $column . ' = "' . mysql_real_escape_string( $data_to_fix ) . '"';
					}

					if ( $upd && ! empty( $where_sql ) ) {
						$sql = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
						$result = mysql_query( $sql, $connection );
						if ( ! $result )
							$report[ 'errors' ][] = mysql_error( );
						else
							$report[ 'updates' ]++;

					} elseif ( $upd ) {
						$report[ 'errors' ][] = sprintf( '"%s" has no primary key, manual change needed on row %s.', $table, $current_row );
					}

				}
			}
		}

	}
	$report[ 'end' ] = microtime( );

	return $report;
}

// Errors array 
$errors = array( );

// Check the db connection 
$connection = @mysql_connect( $host, $user, $pass );
if ( ! $connection ) {
	$errors[] = mysql_error( );
}

if ( ! empty( $char ) ) {
	if ( function_exists( 'mysql_set_charset' ) )
		mysql_set_charset( $char, $connection );
	else
		mysql_query( 'SET NAMES ' . $char, $connection );  // Shouldn't really use this, but there for backwards compatibility	
}

// Do we have any tables and if so build the all tables array
$all_tables = array( );
@mysql_select_db( $data, $connection );
$all_tables_mysql = @mysql_query( 'SHOW TABLES', $connection );

if ( ! $all_tables_mysql ) {
	$errors[] = mysql_error( );
} else {
	while ( $table = mysql_fetch_array( $all_tables_mysql ) ) {
		$all_tables[] = $table[ 0 ];
	}
}

// Check and clean the tables array
if ($tables) {
	$tables = array_filter( $tables, 'check_table_array' );
}
else {
	$tables = $all_tables;
}

if ( ! empty( $errors ) && is_array( $errors ) ) {
	echo "\r\nERRORS:\r\n";
	foreach( $errors as $error )
		echo  "\r\n" . $error . "\r\n";
}
// Set timeout to infinite
@ set_time_limit( 0 );
// Try to push the allowed memory up, while we're at it
@ ini_set( 'memory_limit', '1024M' );

// Process the tables
if ( isset( $connection ) )
	$report = icit_srdb_replacer( $connection, $srch, $rplc, $tables );

// Output any errors encountered during the db work.
if ( ! empty( $report[ 'errors' ] ) && is_array( $report[ 'errors' ] ) ) {
	echo "\r\nERRORS:\r\n";
	foreach( $report[ 'errors' ] as $error )
		echo "\r\n" . $error . "\r\n";
}

// Calc the time taken.
$time = array_sum( explode( ' ', $report[ 'end' ] ) ) - array_sum( explode( ' ', $report[ 'start' ] ) );
echo "\r\n/*************************/";
echo "\r\n/*********SUCCESS*********/";
echo "\r\n/*************************/\r\n"; 
echo "Searched for: " . $srch . "\r\n";
echo "Replaced with: " . $rplc . "\r\n";
echo "Tables Scanned: " . $report['tables'] . "\r\n";
echo "Rows Scanned: " . $report['rows'] . "\r\n";
echo "Cells Changed: " . $report['change'] . "\r\n";
echo "DB Updated: " . $report['updates'] . "\r\n";
echo "Time Elapsed: " . $time . "\r\n";

// $srch, $rplc, $report[ 'tables' ], $report[ 'rows' ], $report[ 'change' ], $report[ 'updates' ], $time
printf( 'In the process of replacing "%s" with "%s" we scanned %d tables with a total of %d rows, %d cells were changed and %d db update performed and it all took %f seconds.', $srch, $rplc, $report[ 'tables' ], $report[ 'rows' ], $report[ 'change' ], $report[ 'updates' ], $time );

// Close the connection
if ( isset( $connection ) && $connection )
	mysql_close( $connection );
?>
