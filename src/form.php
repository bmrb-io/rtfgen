<?php
require_once "rtfgen.php";

//foreach( array_keys( $_POST ) as $param ) echo "$param => $_POST[$param]<br>";
//foreach( array_keys( $_FILES ) as $param ) { echo "$param => "; print_r( $_FILES[$param] ); echo "<br>"; }

//
// delete files older than 1 day from $dir.
//
function delete_old_files( $dir ) {
#echo "<STRONG>delete_old_files: $dir</STRONG><BR>";
    $OLD = time() - 86400; // 24 hours
//    $OLD = time() - 60; // 1 minute - testing
    if( ! file_exists( $dir ) ) {
	mkdir( $dir );
	return;
    }
    if( is_dir( $dir ) ) {
        if( $dh = opendir( $dir ) ) {
            while( ($file = readdir( $dh )) !== false ) {
                if( ($file == ".") || ($file == "..") ) continue;
#echo "<STRONG>delete_old_files: $dir$file</STRONG><BR>";
                if( fileatime( $dir . "/" . $file ) < $OLD ) {
#echo "<STRONG>delete_old_files: deleting $dir$file</STRONG><BR>";
                    unlink( $dir . $file );
                }
            }
            closedir( $dh );
        }
    }
}

if( ! isset( $_POST['type'] ) ) {
    echo "<CENTER><STRONG>Select \"Complete BMRB entry\" or \"Chemical shifts loop only\"";
    echo " on the previous page</STRONG>";
    die( "<P>Use your browser's Back button to return to previous page</CENTER>" );
}

if( ($_FILES['uplfile']['error'] != 0) || ($_FILES['uplfile']['size'] <= 0) ) {
    echo "<CENTER><STRONG>There was an error uploading file, please try again!</STRONG>";
    echo "<P>Use your browser's Back button to return to previous page</CENTER>";
    die;
}
/*
global $LOCALTMPDIR;
delete_old_files( $LOCALTMPDIR );
$uplfile = tempnam( $LOCALTMPDIR, "upl" );
if( ! move_uploaded_file( $_FILES['uplfile']['tmp_name'], $uplfile ) ) {
    echo "<CENTER><STRONG>There was an error saving file, please try again!</STRONG>";
    echo "<P>Use your browser's Back button to return to previous page</CENTER>";
    die;
}
*/
header( "Content-Type: text/rtf" );
header( "Content-Disposition: inline; filename=" . $_FILES['uplfile']['name'] . ".rtf" );

if( $_POST['type'] == 1 ) $complete_entry = true;
else $complete_entry = false;
//$rc = make_rtf( $uplfile, $_POST['sort'], $complete_entry );
$rc = make_rtf( $_FILES['uplfile']['tmp_name'], $_POST['sort'], $complete_entry );
if( $rc !== TRUE ) die( "Error creating RTF: " . $rc . "\n" );
?>