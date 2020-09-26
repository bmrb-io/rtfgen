#!PERL_PATH
#
#    This is a sampe perl program to show a simple web
#    interface.
#
#

# All web CGI (CGI = Common gateway interface) programs have certain
# similar features:
#
# 1 - Usually they are two-pass.  On the first run, they output
#     the web form, and on the second they accept the submitted
#     data.
#
# 2 - The stdout of the CGI program is redirected by the server to
#     become the text of the web page, for example:
#           printf( "<HEAD><TITLE>foo</TITLE><HEAD>\n" );
#           printf( "<BODY><H2>This is an example</H2></BODY>\n" );
#
# 3 - The stderr of the CGI program is redirected to a log file by
#     the web server.  So anything going to stderr ends up in the
#     web site's error log.
#
# 4 - The web server executes the CGI program.  When it does this
#     it first sets up some environment variables.  These environment
#     variables are the arguments in the form.
#

$| = 1;

require "cgi-lib2.pl";

# Truncate any file over 3 Mb in size:
$cgi_lib'maxdata = 1024*1024*3;

$parse_return = &ReadParse( %in );

if( !defined $parse_return )  # If there was an error in parsing the parameters.
{
    &PrintError( "CGI parameters invalid.");
    exit(1);
}

$ENV{CLASSPATH} = "JAVA_CLASS_FILES";
$NMRSTAR_file_repository = "DATA_LIBRARY_FILES";

# chdir into the directory in which this CGI program is running:
$working_dir = $ENV{"SCRIPT_FILENAME"};
$working_dir =~ s/\/[^\/]+$//g;
chdir "$working_dir";


# TODO - put some intro here.

if( $in{"sort_order"} eq "" )
{
    print "Content-type: text/html\n";
    print "\n";

    print "<HEAD>\n";
    print "    <TITLE>RTF-gen software page</TITLE>\n";
    print "</HEAD>\n";
    print "<BODY>\n";

    &prog_des;
    &show_form;

    print "</BODY>\n";
}
else
{
    print "Title: $in{'input'}.rtf\n";
    print "Content-disposition: filename=\"$in{'input'}.rtf\"\n";
    print "Content-Type: application/rtf\n";
    print "\n";
    &run_program;
}

exit(1);

#print the description of the software.
sub prog_des
{  
    print "<H3><CENTER>RTF-gen description</CENTER></H3>\n";
    print <<ENDQUOTE;
	<HR>
	RTF-gen is a program to generate a word processor table of
	amino acid chemical shifts from an NMR-STAR file.
	
	<P>
	The file produced is in RTF format (MS Word 2.0), and it can be
	imported into most of the popular word processors.  It is a table
	of amino acid chemical shift data in publication format.
	To use this software, specify a NMR-STAR format file with the
	form below and hit the submit button.  Note that the NMR-STAR file
	does not need to be complete.  As long as there is a chemical
	shift section in the file, it will work.  (Even if the rest of the
	NMRSTAR information is not in the file.) Files produced by the 
	<a href ="http://www.bmrb.wisc.edu/elec_dep/gen_aa.html">BMRB 
	amino acid atom table generator</a> can be easily uploaded.
	<P>
	The data in the RTF file can be organized in one of two ways, 
	<a href ="http://www.bmrb.wisc.edu/help_files/seqcode.html">sequence code ordering </a>
	and 
	<a href ="http://www.bmrb.wisc.edu/help_files/aaorder.html">amino acid ordering</a>.
        <P> 
	For assistance contact 
	<A href="mailto:bmrbhelp\@bmrb.wisc.edu">bmrbhelp\@bmrb.wisc.edu</A>
	
	<HR>
ENDQUOTE

}
sub show_form
{
    print <<ENDQUOTE;

    <FORM ENCTYPE=\"multipart/form-data\" METHOD=POST
          ACTION=\"rtfgen_web.cgi\">
        <P>
	<CENTER>
        <TABLE WIDTH=75% BORDER=8 >

	<FONT COLOR=black> <!-- Should be the default - but just being sure -->

        <TR>
            <TH COLSPAN=2 BGCOLOR="#90A0C0">
                <P ALIGN=left>
                <EM>Either</EM> enter a BMRB accession 
                number in the left box below, <EM>or</EM> 
                give a directory and filename of a local file 
                with chemical shifts in NMR-STAR format on your 
                computer.<BR>
                (If you fill in both blanks, the accession number 
                will be used, and the upload filename will be ignored.)
                </P>
        <TR>
            <TD BGCOLOR="#70D0B0">
                Accession number of a file in BMRB's public archive:
                <INPUT TYPE=text NAME=input VALUE=\"\" SIZE=5>
            <TD BGCOLOR="#70D0B0">
                Local file to use as input:<BR>
                <INPUT TYPE=file NAME=upload SIZE=35>
        <TR>
	    <TD BGCOLOR="#D0FF60">
		<CENTER>
		Sort the table by:
		<SELECT NAME=\"sort_order\">
		    <OPTION SELECTED>sequence code
		    <OPTION>amino acid
		</SELECT>
		</CENTER>
	    <TH>
		<FONT COLOR=white>
		    <INPUT TYPE=submit VALUE=\"Submit (Generates RTF)\">
		    <INPUT TYPE=reset VALUE=\"Reset this form\">
		</FONT>

	</FONT> <!-- turn off the black, just in case the user had a different
	             default color set -->

        </TABLE>
	</CENTER>
    </FORM>
    
ENDQUOTE
}

sub run_program
{   
    local( $sort_num );
    local( $run_type );
    local( $remote_name );
    local( $log_date );

    $log_date = `date`;
    if( $log_date =~ /\n/ )
    {   chop( $log_date );
    }
    if( "$ENV{'REMOTE_HOST'}" eq "" )
    {   $remote_name = $ENV{'REMOTE_ADDR'} ;
    }
    else
    {   $remote_name = $ENV{'REMOTE_HOST'} ;
    }

    $sort_num = ( $in{'sort_order'} eq 'sequence code' ) ? 1 : 2 ;

    # If the file was uploaded instead, then dump it's contents to
    # a temp file first and run that temp file through the
    # program:
    # - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    if( $in{'input'} eq "" )
    {
	# (Note: in Perl, $$ is a special variable that means
	# the current process ID number (pid):)
	$nmrstar_file = "/tmp/$$.upload.str";
	if( ! open( TMPFILE, ">$nmrstar_file" ) )
	{   &PrintError( "Can't server's spool file: $!" );
	    exit(1);
	}
	# The variable $in{'upload'} contains the entire body of the
	# uploaded file in one really big string in memory.
	# ---------------------------------------------------------
	print TMPFILE $in{'upload'};
	close( TMPFILE );
     
	# Now run the program:
        &shell_cmd( "java EDU.bmrb.rtfgen.rtfgen", $nmrstar_file, $sort_num, "-" );

	# Delete the now useless spool file:
	unlink $nmrstar_file;

	$run_type = "uploaded file";
    }
    else  # User entered an accession number, so try that:
    {
	$nmrstar_file = "$NMRSTAR_file_repository/bmr$in{'input'}.str";
        &shell_cmd( "java EDU.bmrb.rtfgen.rtfgen", $nmrstar_file, $sort_num, "-" );

	$run_type = "accession number";

    }


   
   # open the file for writing the basic statistics table
   open( LOGOUT, ">>rtfgen.logfile" ) or die "failed to open file rtfgen.logfile"; 
   flock( LOGOUT, $LOCK_EX=2 );
   # write into the log file.
   print LOGOUT $log_date, "\t", $remote_name, "\t", $run_type, "\n";
   # close log file.
   flock( LOGOUT, $LOCK_UN=8 );
   close(LOGOUT);
   
}


sub shell_cmd
{
    local( $cmdline ) = "";

    for( $idx = 0 ; $idx <= $#_ ; $idx++ )
    {   if( $_[$idx] )
	{   $cmdline = $cmdline . " " . $_[$idx];
	}
    }

    # Run the $cmdline, and echo the output of it.
    if( ! open( CMD_PIPE, "$cmdline |" ) )
    {
        print STDERR "error running $cmdline: error was: $!\n";
    }
    while( $lineoftext = <CMD_PIPE> )
    {
	print $lineoftext;
    }
    close( CMD_PIPE );
}

# PrintError
# ----------
#    Print the string given as an error message in HTML.

sub PrintError
{
    print STDERR "$0 error: Cannot parse CGI.\n";
    print <<ENDQUOTE;
Content-type: text/html

<HEAD><TITLE>$0 Error</TITLE></HEAD>
<BODY>
    <H2>Error in CGI Perl script</H2>
    <HR>
    <PRE>
	$_[0]
    </PRE>
    <HR>
</BODY>

ENDQUOTE
}
