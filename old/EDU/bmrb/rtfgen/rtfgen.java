package EDU.bmrb.rtfgen;
/**
 * rtfgen generates table of chemical shifts in RTF format.
 * Usage: <code>rtfgen <em><input file> <sort order> <output file> </em></code>
 * Chemical shift data is read from <em>input file</em> in NMR-STAR format.
 * The table is ordered by shift sequence number (<em>1</em>) or by amino acid (<em>2</em>).
 * Output is written to <em>output file</em>; if output file is "-", it is written to stdout.
 * 
 * @author dmaziuk@bmrb.wisc.edu
 * @version 1
 ************/

import java.io.*;
import java.util.*;
import EDU.bmrb.starlibj.*;
/** 
 * Converts chemical shift table from NMR-STAR loop to RTF.
 */
public class rtfgen
{
    private static String infname;
    private static int option;
    private static String outfname;
    private static boolean deleteInfile = false;
    /**
     * default constructor.
     */
    public rtfgen() 
    {
    } //------------------------------------------------------------------------------------------------
    /**
     * program entry point.
     * Command-line arguments: input file, sort order, output file (order is
     * important). Sort order can be "1" to sort by shift sequence number or "2"
     * to sort by amino acid. If output file is "-", result is written to stdout.
     * @param args command-line arguments.
     */
    public static void main( String[] args )
    {
	try
	{
	    infname = args[0];
	    Integer i = new Integer( args[1] );
	    option = i.intValue();
	    outfname = args[2];
	    if( (option != 1) && (option != 2) ) 
	    {
		usage();
		return;
	    }	    
	}
	catch( Exception e )
	{
//    	    e.printStackTrace();
	    usage();
	    return;
	}	
	rtfgen gen = new rtfgen();
	gen.run();
	if( deleteInfile )
	{
	    File f = new File( infname );
	    f.delete();
	}
    } //------------------------------------------------------------------------------------------------
    /**
     * print usage information.
     */
    private static void usage()
    {
	System.out.println( "Usage: rtfgen <input file> <1|2> <output file>" );
	System.out.println( "       1 : table ordered by chemical shift sequence number" );
	System.out.println( "       2 : table ordered by amino acid" );
	System.out.println( "       Use dash (-) for output file name to print to stdout" );
    } //------------------------------------------------------------------------------------------------
    /**
     * Main function.
     */
    public void run()
    {
	if( !isNMR( infname ) ) return;

	StarFileNode root = null; // grr... this is to shut up javac warning.
        root = readFile( infname );
	if( root == null ) return;
	
	String mol_name = getMolName( root );
	VectorCheckType frames = getShiftFrames( root );
	if( (frames == null) || (frames.size() < 1) ) return;
// get chem shift data
	Vector data = new Vector();      // vector of shiftdata
	Vector ranges = new Vector();    // vector of Integer
// Note that we process only the first saveframe if there's > 1
	if( !getChemLoop( (SaveFrameNode) frames.elementAt( 0 ), data, ranges ) )
	{
	    System.err.println( "Chemical shift loop not found in " + infname );
	    return;
	}
// update detail list
	Vector details = new Vector();
	if( !getDetailList( (SaveFrameNode) frames.elementAt( 0 ), details ) )
	{
	    System.err.println( "Error getting detail list" );
	    return;
	}
// get component names
	Vector names = new Vector();
	if( !getComponentList( (SaveFrameNode) frames.elementAt( 0 ), names ) )
	{
	    System.err.println( "Error getting molecular system component name list" );
	    return;
	}
// get conditions
	Vector conditions = new Vector();
	if( !getConditionList( root, (SaveFrameNode) frames.elementAt( 0 ), conditions ) )
	{
	    System.err.println( "Error getting molecular system component name list" );
	    return;
	}
	for( int i = (frames.size() - 1); i >= 0; i-- )
	    frames.removeElementAt( i );
// redirect stdout to file, if specified
	if( !outfname.equals( "-" ) ) 
	{
	    try 
	    {
// trust Sun to deprecate PrintStream without fixing System.setOut()
		System.setOut( new PrintStream( new FileOutputStream( outfname ) ) );
	    }
	    catch( Exception e )
	    {
		e.printStackTrace();
		return;
	    }
	}
// generate rtf file
	int table_count = 1;
	int row_count = 0;
	boolean makerow_flag = false;
	System.out.println( rtf.fileheader );         // file header
//	System.out.println();
// for each amino-acid
	for( int i = 0; i < ranges.size(); i++ )
	{
	    System.out.print( rtf.tabletitle );       // table title
	    System.out.print( table_count + " : " );
// print molecular system name
	    if( !mol_name.equals( "$" ) ) System.out.print( mol_name + " " );
// print component name if different from the above
	    if( !(((String)names.elementAt( i )).equals( "$" ) || ((String)names.elementAt( i )).equals( mol_name )) )
		System.out.print( (String)names.elementAt( i ) );
	    System.out.print( " resonance assignments; " );
// print conditions
	    System.out.print( (String)conditions.elementAt( i ) );
	    System.out.print( rtf.tabletitletail );   // end table title
	    System.out.println( rtf.tableheader ) ;   // table header
//	    System.out.println();
	
	    String symbol;
	    Vector rows = new Vector();               // vector of rows of rtf table
	    boolean foot_note = false;                // is there a footnote for this table
	    int fn_count = 0;                         // footnote count
	    Vector fn_symbols = new Vector();         // 1-letter AA symbols for footnote
	    Vector fn_numbers = new Vector();         // AA sequence codes for footnote
	    Vector fn_values = new Vector();          // 3-letter AA labels for footnote
	    boolean is_special = false;               // is a special amino-acid?
	    while( row_count < ((Integer) ranges.elementAt( 0 )).intValue() )
	    {
// findAA()
		pair aa = new pair();
		Vector alist = new Vector();
		if( !findAA( data, alist, aa ) ) 
		{
		    System.err.println( "Error getting atom/shift value list" );
		    return;
		}
		symbol = rtf.matchAA( aa.getLabel() );
// special amino-acid
		if( symbol.equals( "X" ) )
		{
		    fn_symbols.addElement( symbol );
		    fn_numbers.addElement( aa.getSeqCode() );
		    fn_values.addElement( aa.getLabel() );
		    foot_note = true;
		    is_special = true;
		    fn_count++;
		}
		else is_special = false;

		row_count += alist.size();
// make a row of rtf table
		makeRow( rows, alist, symbol, aa, is_special, fn_count );		
	    } // endwhile
	    data.removeAllElements();
// print the rows
	    if( option == 1 )
		for( int j = 0; j < rows.size(); j++ )
		    System.out.println( ((triple)rows.elementAt( j )).getRtfRow() );
	    else
	    {
		VectorSorter.sort( rows, (triple) rows.elementAt( 0 ) );
		for( int j = 0; j < rows.size(); j++ )
		    System.out.println( ((triple)rows.elementAt( j )).getRtfRow() );
	    }
	    int detail_count = 0;
// print the foot note if necessary.
	    if( foot_note )
	    {
		System.out.print( "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par Footnotes:}" );
		System.out.print( "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par (1) " );
		for( int j = 0; j < fn_symbols.size(); j++ )
		{
		    System.out.print( (String) fn_symbols.elementAt( j ) );
		    System.out.print( (String) fn_numbers.elementAt( j ) );
		    System.out.print( " = " );
		    System.out.print( (String) fn_values.elementAt( j ) );
		}
		if( ((String) details.elementAt( detail_count )).equals( "nodetail" ) )
		    System.out.print( "\\par\\par}" );
		else System.out.print( "}" );
	    }
// print details
	    if( !((String) details.elementAt( detail_count )).equals( "nodetail" ) )
	    {
		if( !foot_note )
		    System.out.print( "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par Footnotes:}" );
		System.out.print( "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par (" );
		if( foot_note ) System.out.print( "2" );
		else System.out.print( "1" );
		System.out.print( (String) details.elementAt( detail_count ) );
		System.out.print( "\\par\\par}" );
		detail_count++;
	    }
	    table_count++;
	} // endfor i = 0 to ranges.size()
// end of RTF file
	System.out.print( rtf.filetail );
	ranges.removeAllElements();
	details.removeAllElements();
	names.removeAllElements();
	conditions.removeAllElements();
    } //------------------------------------------------------------------------------------------------
    /**
     * Check if input file is in NMR-STAR format. If not (eg. just the chemical shifts part),
     * try adding required tags to make it a STAR file. <strong>Note</strong> that the test 
     * is pretty dumb: we check that first line of file has "data" in it.
     * 
     * @param file name
     * @return true if it's an NMR-STAR file, false otherwise
     */
    private static boolean isNMR( String fname )
    {
	BufferedReader fin;
	try { fin = new BufferedReader( new FileReader( fname ) ); }
	catch( FileNotFoundException f )
	{
	    System.err.print( "File not found: " );
	    System.err.println( fname );
	    return false;
	}
	String s = new String();
	try { s = fin.readLine(); }
	catch( IOException e )
	{
	    System.err.print( "Error reading from " );
	    System.err.println( fname );
	    return false;
	}
	if( s.indexOf( "data" ) >= 0 ) return true;
	
	Long now = new Long( System.currentTimeMillis() );
	String tmpfname = new String( "/tmp/" );
	tmpfname = tmpfname.concat( now.toString() );
	tmpfname = tmpfname.concat( ".nmrout" );
	PrintWriter fout;
	try 
	{
	    fout = new PrintWriter( new BufferedWriter( new FileWriter( tmpfname ) ) );
	}
	catch( IOException e )
	{
	    System.err.print( "Error writing to " );
	    System.err.println( tmpfname );
	    return false;
	}
	try
	{
	    fout.println( "data_0000" );
	    fout.println( "save_chemical_shift_assignment_tmp" );
	    fout.println( "_Saveframe_category   assigned_chemical_shifts" );
	    fout.println( s );
	    while( (s = fin.readLine()) != null )
		fout.println( s );
    	    fout.println( "save_" );
	    fout.close();
	    fin.close();
	}
	catch( IOException e )
	{
	    System.err.print( "Error copying " );
	    System.err.println( fname );
	    return false;
	}
	infname = tmpfname;
	deleteInfile = true;
	return true;
    } //------------------------------------------------------------------------------------------------
    /**
     * read and parse input NMR-STAR file.
     * 
     * @param file name
     * @return NMR-STAR tree (vector of StarFile nodes)
     */
    private StarFileNode readFile( String filename )
    {
	BufferedReader fin;
	StarParser parser = null;
	StarFileNode startree = null;
	try { fin = new BufferedReader( new FileReader( filename ) ); }
	catch( FileNotFoundException f )
	{
	    System.err.print( "File not found: " );
	    System.err.println( filename );
	    return null;
	}
	try
	{
	    parser = new StarParser( fin );
	    parser.StarFileNodeParse( parser );
	    startree = (StarFileNode)parser.endResult();
	}
	catch( Exception e )
	{
	    System.err.println( "Starlib error" );
	    e.printStackTrace();
	    return null;
	}
	return startree;
    } //------------------------------------------------------------------------------------------------
    /**
     * extract molecular system name from the NMR-STAR tree.
     * If there are multiple molecular systems, extract the first one only.
     * If there is none, return String( "$" ).
     * 
     * @param NMR-STAR tree
     * @return molecular system name
     */
    private String getMolName( StarFileNode startree )
    {
	String mol_name = new String( "$" );
	VectorCheckType node_list;
	node_list = startree.searchByName( "_Mol_system_name" );
	int numhits = node_list.size();
	if( numhits > 0 )
	{
	    if( numhits > 1 ) System.err.println( "too many _Mol_system_name tags in input file" );
	    mol_name = ((DataItemNode)node_list.elementAt( 0 )).getValue();
	}
	return mol_name;
    } //------------------------------------------------------------------------------------------------
    /**
     * return a VectorCheckType list of all "assigned_chemical_shifts" saveframes.
     * <strong>Note</strong> that the program can handle only one saveframe; if there's
     * more than one, we print an error message and process only the first saveframe.
     * 
     * @param NMR-STAR tree
     * @return vector of saveframes
     */
    private VectorCheckType getShiftFrames( StarFileNode startree )
    {
	VectorCheckType node_list;
	try
	{
	    node_list = startree.searchForTypeByTagValue( Class.forName( StarValidity.pkgName() + ".SaveFrameNode" ),
							 "_Saveframe_category", "assigned_chemical_shifts" );
	}
	catch( Exception e )
	{
	    e.printStackTrace();
	    return null;
	}
	
	if( node_list.size() < 1 ) 
	{
	    System.err.println( "no \"assigned_chemical_shifts\" saveframe(s) found." );
	    return null;
	}
	
	if( node_list.size() > 1 )
	    System.err.println( "too many saveframes, processing the first one only." );
	
	return node_list;
    } //------------------------------------------------------------------------------------------------
    /**
     * extract chemical shift loop from the saveframe.
     * 
     * @param frame: saveframe containing chemical shift loop.
     * @param data: (return) vector of shiftdata.
     * @return false on error, true otherwise.
     * @see shiftdata
     */
    private boolean getChemLoop( SaveFrameNode frame, Vector data, Vector range )
    {
	DataLoopNameListNode names;
	LoopTableNode values;
	VectorCheckType nodes;

//	StarUnparser unparser = new StarUnparser( System.err );
//	System.err.print( "*** frame " + frame.size() );
//	unparser.writeOut( frame, 4 );
//	System.err.println();

	try
	{
	    nodes = frame.searchForTypeByName( Class.forName( StarValidity.pkgName() + ".DataLoopNode" ),
					      "_Atom_shift_assign_ID" );
	}
	catch( Exception e )
	{
	    e.printStackTrace();
	    return false;
	}
	
//	System.err.println( "*** found " + nodes.size() + " loop nodes" );
	
	names = ((DataLoopNode) nodes.elementAt( 0 )).getNames();
	values = ((DataLoopNode) nodes.elementAt( 0 )).getVals();

//	System.err.println( "** names.size = " + names.size() + ", values.size = " + values.size() );
//	unparser.writeOut( (LoopNameListNode)names.elementAt( 0 ), 6 );
//	unparser.writeOut( values, 6 );

	LoopNameListNode n;
	int seq_pos = -1, lab_pos = -1, atom_pos = -1, shift_pos = -1;
	    
	for( int i = 0; i < names.size(); i++ )
	{
// check validity of the loop
	    n = names.elementAt( i );
	    for( int j = 0; j < n.size(); j++ )
	    {
		if( n.elementAt( j ).getValue().equals( "_Residue_seq_code" ) ) seq_pos = j;
		else if( n.elementAt( j ).getValue().equals( "_Residue_label" ) ) lab_pos = j;
		else if( n.elementAt( j ).getValue().equals( "_Atom_name" ) ) atom_pos = j;
		else if( n.elementAt( j ).getValue().equals( "_Chem_shift_value" ) ) shift_pos = j;
	    }
	}
	
	    if( seq_pos < 0 ) 
	    {
		System.err.println( "Missing _Residue_seq_code tag" );
		return false;
	    }
	    if( lab_pos < 0 ) 
	    {
		System.err.println( "Missing _Residue_label tag" );
		return false;
	    }
	    if( atom_pos < 0 ) 
	    {
		System.err.println( "Missing _Atom_name tag" );
		return false;
	    }
	    if( shift_pos < 0 ) 
	    {
		System.err.println( "Missing _Chem_shift_value tag" );
		return false;
	    }
//	}

	LoopRowNode row;
	for( int i = 0; i < values.size(); i++ )
	{
	    row = values.elementAt( i );
	    shiftdata d = new shiftdata();
	    d.setSeqCode(  row.elementAt( seq_pos ).getValue() );
	    d.setResLabel(  row.elementAt( lab_pos ).getValue() );
	    d.setAtomName(  row.elementAt( atom_pos ).getValue() );
	    d.setChemShift(  row.elementAt( shift_pos ).getValue() );
	    data.addElement( d );
	}
// update ranges list
//FIXME: check if this makes sense -- if values.size() is what we should use here
	range.addElement( new Integer( values.size() ) );

	return true;
    } //------------------------------------------------------------------------------------------------
    /**
     * extract details from saveframe.
     * 
     * @param frame: saveframe containing chemical shift loop.
     * @param details: (return) vector of String.
     * @return false on error, true otherwise.
     */
    private boolean getDetailList( SaveFrameNode frame, Vector details )
    {
	VectorCheckType nodes;
	String tmp = new String( "nodetail" );
	try
	{
	    nodes = frame.searchForTypeByName( Class.forName( StarValidity.pkgName() + ".DataItemNode" ),
					      "_Details" );
	}
	catch( Exception e )
	{
	    e.printStackTrace();
	    return false;
	}
	
	if( nodes.size() > 1 ) System.err.println( "too many _Details tags in saveframe" );
        if( nodes.size() > 0 ) tmp = ((DataItemNode)nodes.elementAt( 0 )).getValue();
	details.addElement( tmp );
	return true;
    } //------------------------------------------------------------------------------------------------
    /**
     * extract molecular system component name from saveframe.
     * 
     * @param frame: saveframe containing chemical shift loop.
     * @param names: (return) vector of String.
     * @return false on error, true otherwise.
     */
    private boolean getComponentList( SaveFrameNode frame, Vector names )
    {
	VectorCheckType nodes;
	String tmp = new String( "$" );
	try
	{
	    nodes = frame.searchForTypeByName( Class.forName( StarValidity.pkgName() + ".DataItemNode" ),
					      "_Mol_system_component_name" );
	}
	catch( Exception e )
	{
	    e.printStackTrace();
	    names.addElement( tmp );
	    return false;
	}
	if( nodes.size() > 1 ) System.err.println( "too many _Mol_system_component_name tags in saveframe" );
        if( nodes.size() > 0 ) tmp = ((DataItemNode)nodes.elementAt( 0 )).getValue();
	names.addElement( tmp );
	return true;
    } //------------------------------------------------------------------------------------------------
    /**
     * extract sample condition loop from saveframe.
     * 
     * @param root: NMR-STAR tree.
     * @param frame: saveframe containing chemical shift loop.
     * @param cons: (return) vector of String, sample conditions.
     * @return false on error, true otherwise.
     */
    private boolean getConditionList( StarFileNode root, SaveFrameNode frame, Vector cons )
    { 
	DataLoopNameListNode names;
	LoopTableNode values;
	VectorCheckType nodes;
	String tmp = new String( "save_" );
	String cond = new String( "$" );     // return value
	String ph = new String();            // pH
	String t = new String();             // temperature
	String other = new String();         // ionic strength, pressure
	boolean has_ph = false;

	StarUnparser unparser = new StarUnparser( System.err );

	try
	{
	    nodes = frame.searchForTypeByName( Class.forName( StarValidity.pkgName() + ".DataItemNode" ),
					      "_Sample_conditions_label" );
	}
	catch( Exception e )
	{
	    e.printStackTrace();
	    cons.addElement( cond );
	    return false;
	}
	if( nodes.size() > 1 ) System.err.println( "too many _Sample_conditions_label tags in saveframe" );
        if( nodes.size() > 0 ) tmp += ((DataItemNode)nodes.elementAt( 0 )).getValue();
	VectorCheckType frames = root.searchByName( tmp );
	if( frames.size() > 1 ) System.err.println( "too many " + tmp + " saveframes" );
	if( frames.size() > 0 ) 
	{
	    try 
	    {
		nodes = ((StarNode)frames.elementAt( 0 )).searchForTypeByName( 
		    Class.forName( StarValidity.pkgName() + ".DataLoopNode" ), "_Variable_type" );
		if( nodes.size() > 0 )
		{
		    names = ((DataLoopNode) nodes.elementAt( 0 )).getNames();
		    values = ((DataLoopNode) nodes.elementAt( 0 )).getVals();

//		    System.err.println( "** names.size = " + names.size() + ", values.size = " + values.size() );
//		    unparser.writeOut( (LoopNameListNode)names.elementAt( 0 ), 6 );
//		    unparser.writeOut( values, 6 );

		    LoopNameListNode n;
		    int vartype = -1, varvalue = -1, varunits = -1;
		    for( int i = 0; i < names.size(); i++ )
		    {
			n = names.elementAt( i );
			for( int j = 0; j < n.size(); j++ )
			{
			    if( n.elementAt( j ).getValue().equals( "_Variable_type" ) ) vartype = j;
			    else if( n.elementAt( j ).getValue().equals( "_Variable_value" ) ) varvalue = j;
			    else if( n.elementAt( j ).getValue().equals( "_Variable_value_units" ) ) varunits = j;
			}
		    }

		    LoopRowNode row;
		    if( (vartype != -1) && (varvalue != -1 ) )
		    {
			for( int i = 0; i < values.size(); i++ )
			{
			    row = values.elementAt( i );
			    if( row.elementAt( vartype ).getValue().equals( "pH" ) )
			    {
				ph = row.elementAt( vartype ).getValue().trim() + " ";
				ph += row.elementAt( varvalue ).getValue().trim();
				has_ph = true;
			    }
			    else if( row.elementAt( vartype ).getValue().equals( "temperature" ) )
			    {
				t = ", " + row.elementAt( varvalue ).getValue();
// this magic writes "degrees" character in RTF
				t += " {\\fs16{\\field{\\*\\fldinst SYMBOL 176 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}} ";
				if( varunits != -1 ) t += row.elementAt( varunits ).getValue();
			    }
			    else
			    {
				other += (", " + row.elementAt( vartype ).getValue().trim());
				other += (" " + row.elementAt( varvalue ).getValue().trim());
				if( varunits != -1 ) other += (" " + row.elementAt( varunits ).getValue().trim());
			    }
			} // endfor
			cond = ph + t + other;

//			System.err.println( "**** condition: " + cond );

		    } // endif vartype != -1

		} // endif nodes.size() > 0
	    } // try
	    catch( Exception e ) 
	    { 
		e.printStackTrace(); 
		cons.addElement( cond );
		return false;
	    }
	    
	} // endif frames.size() > 0

	cons.addElement( cond );
	return true;
    } //------------------------------------------------------------------------------------------------2
    /**
     * convert vector of shiftdata into a pair of strings: label, sequence code
     * and a vector of pairs: atomname, chemical shift value
     * 
     * @param shifts: source vector
     * @param alist: target (vector of pair), atom names + chemical shift values
     * @param aa: target (pair), amino acid label + sequence code
     * @return false on error, true otherwise
     */
    public boolean findAA( Vector shifts, Vector alist, pair aa )
    {
	int count = 0;
// extract aminoacid label and code
	String aa_label = new String( ((shiftdata) shifts.elementAt( 0 )).getResLabel() );
	String aa_seqcode = new String ( ((shiftdata) shifts.elementAt( 0 )).getSeqCode() );
	aa.setLabel( aa_label );
	aa.setSeqCode( aa_seqcode );
// ... and shift value(s)
	for( int i = 0; i < shifts.size(); i++ )
	{
	    if( (((shiftdata) shifts.elementAt( i )).getResLabel().equals( aa_label ))
	       && (((shiftdata) shifts.elementAt( i )).getSeqCode().equals( aa_seqcode )) )
	    {
		pair tmp = new pair( ((shiftdata) shifts.elementAt( i )).getAtomName(),
				    ((shiftdata) shifts.elementAt( i )).getChemShift() );
		alist.addElement( tmp );
		count++;
	    }
	}
// delete the rows
// can't do this in the previous loop as it screws up the .size() and loop counter
	try
	{
	    for( int i = (shifts.size() - 1); i >= 0; i-- )
		if( (((shiftdata) shifts.elementAt( i )).getResLabel().equals( aa_label ))
		   && (((shiftdata) shifts.elementAt( i )).getSeqCode().equals( aa_seqcode )) )
		    shifts.removeElementAt( i );
	}
	catch( Exception e )
	{
	    e.printStackTrace();
	    return false;
	}
	if( count > 0 ) return true;
	else return false;
    } //------------------------------------------------------------------------------------------------
    /**
     * find <code>what</code> in <code>alist</code>, return the match and remove
     * the entry from <code>alist</code>.
     * 
     * @param alist: list of AA label/sequence code pairs
     * @param what: AA label to search for
     * @return: sequence code or null
     */
    public String findItem( Vector alist, String what )
    {
	for( int i = 0; i < alist.size(); i++ )
	    if( ((pair) alist.elementAt( i )).getLabel().equals( what ) )
	    {
		String retval = new String( ((pair) alist.elementAt( i )).getSeqCode() );
		alist.removeElementAt( i );
		return retval;
	    }
	return null;
    } //------------------------------------------------------------------------------------------------
    /**
     * make a row in rtf table.
     * (don't be scared: this code was copy-pasted from original rtfgen.c++. NMF)
     * 
     * @param alist: source vector
     * @param symbol: 1-char AA code
     * @return true, false on error (? -- there are no error conditions)
     */
    public boolean makeRow( Vector dest, Vector alist, String symbol, pair aa, boolean is_spec, int fn_count )
    {
	String value1 = new String();
	String value2 = new String();
	String value3 = new String();
	int count = alist.size();

	triple retval = new triple( symbol, Integer.valueOf( aa.getSeqCode() ).intValue() );
	StringBuffer sb = new StringBuffer();
// row header
	sb.append( rtf.rowheader );
	sb.append( "{\\fs16 " );
	sb.append( symbol );
	sb.append( aa.getSeqCode() );
	if( is_spec ) sb.append( "{\\fs16\\super " + fn_count + "}" );
// find N
	value1 = findItem( alist, "N" );
	if( value1 != null )
	{
	    sb.append( "\\cell " + value1 );
	    count--;
	}
	else sb.append( "\\cell  " );
// find H
	value1 = findItem( alist, "H" );
	if( value1 != null )
	{
	    sb.append( " (" + value1 + ")" );
	    count--;
	}
// find C
	value1 = findItem( alist, "C" );
	if( value1 != null )
	{
	    sb.append( "\\cell " + value1 );
	    count--;
	}
	else sb.append( "\\cell " );
// find CA
	value1 = findItem( alist, "CA" );
	if( value1 != null )
	{
	    sb.append( "\\cell " + value1 );
	    count--;
	}
	else sb.append( "\\cell " );
// find HA or HA2 and HA3
	if( symbol.equals( "G" ) )
	{
	    value1 = findItem( alist, "HA2" );
	    value2 = findItem( alist, "HA3" );
	    if( (value1 != null ) && (value2 != null) )
	    {
		sb.append( " (" + value1 + ", " + value2 + ")" );
		count -= 2;
	    }
	    else if( (value1 != null ) && (value2 == null) )
	    {
		sb.append( " (" + value1 + ", *)" );
		count--;
	    }
	    else if( (value1 == null ) && (value2 != null) )
	    {
		sb.append( " (*, " + value2 + ")" );
		count--;
	    }
	} // endif symbol == "G"
	else
	{
	    value1 = findItem( alist, "HA" );
	    if( value1 != null )
	    {
		sb.append( " (" + value1 + ")" );
		count--;
	    }
	} // endif symbol != "G"
// find CB
	value1 = findItem( alist, "CB" );
	if( value1 != null )
	{
	    sb.append( "\\cell " + value1 );
	    count--;
	}
	else sb.append( "\\cell " );
	if( (symbol.equals( "A" )) || (symbol.equals( "I" ))
	   || (symbol.equals( "T" )) || (symbol.equals( "V" )) )
	{
	    value1 = findItem( alist, "HB" );
	    if( value1 != null )
	    {
		sb.append( " (" + value1 + ")" );
		count--;
	    }
	} // endif symbol in "ATIV"
	else
	{
	    value1 = findItem( alist, "HB2" );
	    value2 = findItem( alist, "HB3" );
	    if( (value1 != null) && (value2 != null) )
	    {
		sb.append( " (" +  value1 +  ", " + value2 + ")" );
		count -= 2;
	    }
	    else if( (value1 != null) && (value2 == null) )
	    {
		sb.append( " (" +  value1 +  ", *)" );
		count--;
	    }
	    else if( (value1 == null) && (value2 != null) )
	    {
		sb.append( " (*, " + value2 + ")" );
		count--;
	    }
	} // endif symbol not in "ATIV"

	sb.append( "\\cell" );

// find CG, CG1 and CG2
// CG
	if( (symbol.equals( "P" )) || (symbol.equals( "Q" )) || (symbol.equals( "R" ))
	    || (symbol.equals( "E" )) || (symbol.equals( "K" )) || (symbol.equals( "M" )) )
	{
	    value1 = findItem( alist, "CG" );
	    value2 = findItem( alist, "HG2" );
	    value3 = findItem( alist, "HG3" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.cg );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
	} // endif symbol in "PQREKM"
	if( symbol.equals( "L" ) )
	{
	    value1 = findItem( alist, "CG" );
	    value2 = findItem( alist, "HG" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cg );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	} // endif symbol == "L"
	if( symbol.equals( "V" ) )
	{
	    value1 = findItem( alist, "CG1" );
	    value2 = findItem( alist, "HG1" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cg1 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	} // endif symbol == "V"
	if( symbol.equals( "I" ) ) 
	{
	    value1 = findItem( alist, "CG1" );
	    value2 = findItem( alist, "HG12" );
	    value3 = findItem( alist, "HG13" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.cg1 );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2  );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
	} // endif symbol == "I"
	if( (symbol.equals( "I" )) || (symbol.equals( "T" )) || (symbol.equals( "V" )) )
	{
	    value1 = findItem( alist, "CG2" );
	    value2 = findItem( alist, "HG2" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cg2 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	} // endif symbol in "ITV"
// find CD and CD1 and CD2.
// CD
	if( (symbol.equals( "P" )) || (symbol.equals( "R" )) || (symbol.equals( "K" )) )
	{
	    value1 = findItem( alist, "CD" );
	    value2 = findItem( alist, "HD2" );
	    value3 = findItem( alist, "HD3" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.cd );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
	} // endif symbol in "PRK"
	if( (symbol.equals( "Q" )) || (symbol.equals( "E" )) )
	{
	    value1 = findItem( alist, "CD" );
	    if( value1 != null )
	    {
		sb.append( rtf.cd );
		sb.append( "{\\fs16, " + value1 );
		count--;
	    }
	} // endif symbol in "QE"
// CD1
	if( (symbol.equals( "I" )) || (symbol.equals( "L" )) || (symbol.equals( "F" ))
	   || (symbol.equals( "W" )) || (symbol.equals( "Y" )) )
	{
	    value1 = findItem( alist, "CD1" );
	    value2 = findItem( alist, "HD1" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cd1 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	} // endif symbol in "ILFWY"
// CD2
	if( (symbol.equals( "H" )) || (symbol.equals( "L" )) || (symbol.equals( "F" ))
	   || (symbol.equals( "Y" )) )
	{
	    value1 = findItem( alist, "CD2" );
	    value2 = findItem( alist, "HD2" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cd2 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // if symbol in "HLFY"
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "CD2" );
	    if( value1 != null )
	    {
		sb.append( rtf.cd2 );
		sb.append( "{\\fs16, " + value1 );
		count--;
	    }
	} // endif symbol == "W"
// find CE and CE1 and CE2 and CE3.
// CE
	if( symbol.equals( "M" ) )
	{
	    value1 = findItem( alist, "CE" );
	    value2 = findItem( alist, "HE" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ce );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "M"
	if( symbol.equals( "K" ) )
	{
	    value1 = findItem( alist, "CE" );
	    value2 = findItem( alist, "HE2" );
	    value3 = findItem( alist, "HE3" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.ce );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
	} // endif symbol == "K"
// CE1
	if( (symbol.equals( "H" )) || (symbol.equals( "F" )) || (symbol.equals( "Y" )) )
	{
	    value1 = findItem( alist, "CE1" );
	    value2 = findItem( alist, "HE1" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ce1 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol in "HFY"
// CE2
	if( (symbol.equals( "F" )) || (symbol.equals( "Y" )) )
	{
	    value1 = findItem( alist, "CE2" );
	    value2 = findItem( alist, "HE2" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ce2 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol in "FY"
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "CE2" );
	    if( value1 != null )
	    {
		sb.append( rtf.ce2 );
		sb.append( "{\\fs16, " + value1 );
		count--;
	    }
	} // endif symbol == "W"
// CE3
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "CE3" );
	    value2 = findItem( alist, "HE3" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ce3 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "W"
// CH2
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "CH2" );
	    value2 = findItem( alist, "HH2" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ch2 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "W"
// find CZ and CZ2 and CZ3
// CZ
	if( symbol.equals( "F" ) )
	{
	    value1 = findItem( alist, "CZ" );
	    value2 = findItem( alist, "HZ" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cz );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "F"
	if( (symbol.equals( "R" )) || (symbol.equals( "Y" )) )
	{
	    value1 = findItem( alist, "CZ" );
	    if( value1 != null )
	    {
		sb.append( rtf.cz );
// printAfterC( flag1, out_stream, value1, count )
//          if( value1 != null ) {
		sb.append( "{\\fs16, " + value1 );
		count--;
	    }
// end printAfterC()
	} // endif symbol in "RY"
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "CZ2" );
	    value2 = findItem( alist, "HZ2" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cz2 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "W"
// CZ3
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "CZ3" );
	    value2 = findItem( alist, "HZ3" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.cz3 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "W"
// find ND1 and ND2
// ND1
	if( symbol.equals( "H" ) )
	{
	    value1 = findItem( alist, "ND1" );
	    value2 = findItem( alist, "HD1" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.nd1 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	} // endif symbol == "H"
// ND2
	if( symbol.equals( "N" ) )
	{
	    value1 = findItem( alist, "ND2" );
	    value2 = findItem( alist, "HD21" );
	    value3 = findItem( alist, "HD22" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.nd2 );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
	} // endif symbol == "N"
// find NE and NE1 and NE2
// NE
	if( symbol.equals( "R" ) )
	{
	    value1 = findItem( alist, "NE" );
	    value2 = findItem( alist, "HE" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ne );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "R"
// NE1
	if( symbol.equals( "W" ) )
	{
	    value1 = findItem( alist, "NE1" );
	    value2 = findItem( alist, "HE1" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ne1 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	    
	} // endif symbol == "W"
// NE2
	if( symbol.equals( "H" ) )
	{
	    value1 = findItem( alist, "NE2" );
	    value2 = findItem( alist, "HE2" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.ne2 );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
	} // endif symbol == "H"
	if( symbol.equals( "Q" ) )
	{
	    value1 = findItem( alist, "NE2" );
	    value2 = findItem( alist, "HE21" );
	    value3 = findItem( alist, "HE22" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.ne2 );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
	} // endif symbol == "Q"
// find NH1 and NH2
	if( symbol.equals( "R" ) )
	{
// NH1
	    value1 = findItem( alist, "NH1" );
	    value2 = findItem( alist, "NH11" );
	    value3 = findItem( alist, "NH12" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.nh1 );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
// NH2
// findthree( "NH2", "HE21", "HE22", NE2, alist, out_stream, count )
	    value1 = findItem( alist, "NH2" );
	    value2 = findItem( alist, "NH21" );
	    value3 = findItem( alist, "NH22" );
	    if( (value1 != null) || (value2 != null) || (value3 != null) )
	    {
		sb.append( rtf.nh2 );
		if( value1 == null ) sb.append( "{\\fs16, * " );
		else
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		if( value2 == null ) sb.append( "(*" );
		else
		{
		    sb.append( " (" + value2 );
		    count--;
		}
		if( value3 == null ) sb.append( ", *)" );
		else 
		{
		    sb.append( ", " + value3 + ")" );
		    count--;
		}
		if( count != 0 ) sb.append( ";" );
	    } // endif value 1, 2 & 3 != null
// end findthree()
	} // endif symbol == "R"
// find NZ
	if( symbol.equals( "K" ) )
	{
// findtwo( "NZ", "HZ", NZ, alist, out_stream,cnt );
	    value1 = findItem( alist, "NZ" );
	    value2 = findItem( alist, "HZ" );
	    if( (value1 != null) || (value2 != null) )
	    {
		sb.append( rtf.nz );
		if( value1 != null )
		{
		    sb.append( "{\\fs16, " + value1 );
		    count--;
		}
		else sb.append( "{\\fs16, * " );
		if( value2 != null )
		{
		    sb.append( " (" + value2 + ")" );
		    count--;
		}
		else sb.append( "(*)" );
		if( count != 0 ) sb.append( ";" );
	    }
// end findtwo()
	} // endif symbol == "K"
// special amino acid
	if( symbol.equals( "X" ) )
	{
	    sb.append( "} {\\fs16 " );
	    int j = 0;
	    for( int i = 0; i < alist.size(); i++ )
	    {
		if( j == (alist.size() - 1) )
		{
		    sb.append( ((pair) alist.elementAt( i )).getLabel() );
		    sb.append( "," );
		    sb.append( ((pair) alist.elementAt( i )).getSeqCode() );
		}
		else
		{
		    sb.append( ((pair) alist.elementAt( i )).getLabel() );
		    sb.append( "," );
		    sb.append( ((pair) alist.elementAt( i )).getSeqCode() );
		    sb.append( "; " );
		}
		j++;
	    }
	} // endif symbol == "X"
// end of the row
	sb.append( "\\cell } " );
// row tail
	sb.append( rtf.rowtail );
	retval.setRtfRow( sb.toString() );
	dest.addElement( retval );
	return true;
    } //------------------------------------------------------------------------------------------------
    
}
