#!PERL_PATH
#
#    Web interface for rtfgen.
#
#

use CGI qw/:standard -no_xhtml/;
use strict;
use English;

my $JAVA="JAVAPATH_REPLACEME";
$ENV{CLASSPATH} = "JAVA_CLASS_FILES";
my $STARDIR = "DATA_LIBRARY_FILES";

#
#
my $LOGDIR = "LOGDIR_REPLACEME";
my $TMPDIR = "TMPDIR_REPLACEME";

#
# program
#
my $CMD = "$JAVA EDU.bmrb.rtfgen.rtfgen";

#
# Limit uploads to 3 MB
#
$CGI::POST_MAX = 1024 * 1024 * 3;

my $WORKDIR = $ENV{"SCRIPT_FILENAME"};
$WORKDIR =~ s/\/[^\/]+$//g;

chdir $WORKDIR;

# params
#
if( (param( 'input' ) eq "") && (param( 'upload' ) eq "") ) {
    print header,
          start_html( -lang=>"en_US", -title=>"Error" ),
          "\n";
    print p,
          strong( "Required parameter missing. Use your browser's Back button to return to RTFgen page" ),
          "\n";
    print end_html;
    print "\n";
}
else {
    my $log_date = `date`;
    chomp $log_date;
    my $remote_host = remote_host();
    my $sort = (param( "sort_order" ) eq "sequence number") ? 1 : 2;
    my $run_type;
    my $run = 1;
#
# uploaded file
    my $starfile;
    if( param( "input" ) eq "" ) {
	$starfile =  "$TMPDIR/$PID.upload.str";
	{
	    no strict;
	    open( OUTFILE, ">$starfile" ) or die "Cannot open $starfile: $OS_ERROR\n";
	    my $fh = param( "upload" );
	    while( <$fh> ) { print OUTFILE; }
	    close( OUTFILE );
	}
        $run_type = "Uploaded file: $starfile";
        if( (! -e $starfile) || (-z $starfile) ) {
            print header,
                  start_html( -lang=>"en_US", -title=>"Error" ),
                  "\n";
            print p,
                  strong( "Upload failed. Use your browser's Back button to return to RTFgen page" ),
                  "\n";
            print end_html;
            print "\n";
	    $run_type = "Failed upload: $starfile";
	    $run = 0;
        }
    }
    else {
	$starfile = $STARDIR . "/bmr" . param( "input" ) . ".str";
	$run_type = "Accession number " . param( "input" ) . "($starfile)";
        if( (! -e $starfile) || (-z $starfile) ) {
            print header,
                  start_html( -lang=>"en_US", -title=>"Error" ),
                  "\n";
            print p,
                  strong( "Invalid accession number. Use your browser's Back button to return to RTFgen page" ),
                  "\n";
            print end_html;
            print "\n";
	    $run_type = "Accession number invalid: ". param( "input" ) . "($starfile not found)";
	    $run = 0;
        }
    }
#
# run program
# NOTE: rtfgen doesn't return error codes so there's no way to tell user
# that conversion failed (e.g. input file is not STAR). 
# TODO: change rtfgen to use System.exit( code ) instead of return. Then
# deal with exit code of $command here.
#
    if( $run ) {
        print header( "text/rtf" );  #, "\n";
        my $command = "$CMD $starfile $sort -";
        open( CMDPIPE, "$command |" ) or die "Cannot run command $command: $OS_ERROR\n";
        while( <CMDPIPE> ) { print; }
        close( CMDPIPE );
        if( $run_type !~ /Accession number/ ) { unlink $starfile; }
    }
#
# log
   open( LOGOUT, ">>$LOGDIR/rtfgen.log" ) or die "failed to open $LOGDIR/rtfgen.log: $OS_ERROR\n"; 
   flock( LOGOUT, 2 );
   # write into the log file.
   print LOGOUT $log_date, "\t", $remote_host, "\t", $run_type, "\n";
   # close log file.
   flock( LOGOUT, 8 );
   close( LOGOUT );   
}

exit( 0 );
#
#
#