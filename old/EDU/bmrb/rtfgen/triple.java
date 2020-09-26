package EDU.bmrb.rtfgen;
/**
 * Structure that holds amino-acid symbol, sequence code and
 * an RTF formatted table row.
 * 
 * @author dmaziuk@bmrb.wisc.edu
 * @version 1
 ************/

public class triple implements VectorSorter.Comparer
{
    private String sym;
    private int seq;
    private String rtf;
    /** creates new triple.
     * @param symbol residue label
     * @param code sequence code
     * @param row RTF table row
     */
    public triple( String symbol, int code, String row )
    {
	sym = new String( symbol );
	seq = code;
	rtf = new String( row );
    }
    /** creates new triple.
     * @param symbol residue label
     * @param code sequence code
     */
    public triple( String symbol, int code )
    {
	sym = new String( symbol );
	seq = code;
	rtf = new String();
    }
    /** creates new triple. */
    public triple() 
    {
	sym = new String();
	seq = -1;
	rtf = new String();
    }
    /** changes residue label.
     * @param symbol residue label
     */
    public void setAAsymbol( String symbol ) { sym = symbol; }
    /** changes sequence code.
     * @param code residue sequence code
     */
    public void setSeqCode( int code ) { seq = code; }
    /** changes RTF table row.
     * @param row RTF table row
     */
    public void setRtfRow( String row ) { rtf = row; }
    /** return residue label.
     * @return residue label
     */    
    public String getAAsymbol() { return sym; }
    /** returns sequence code.
     * @return residue sequence code
     */
    public int getSeqCode() { return seq; }
    /** returns RTF table row.
     * @return RTF table row
     */
    public String getRtfRow() { return rtf; }
    /** comparator function for sorting
     * @param a object to compare
     * @param b object to compare
     * @return -1 if a < b, 0 if a == b, or 1 if a > b
     */
    public int compare(Object a, Object b)
    {
	triple ta = (triple) a;
	triple tb = (triple) b;
	if( (ta.getAAsymbol().equals( "X" )) && (tb.getAAsymbol().equals( "X" )) )
	{
	    if( ta.getSeqCode() < tb.getSeqCode() ) return -1;
	    else if( ta.getSeqCode() == tb.getSeqCode() ) return 0;
	    else if( ta.getSeqCode() > tb.getSeqCode() ) return 1;
	}
	else if( (ta.getAAsymbol().equals( "X" )) && (!tb.getAAsymbol().equals( "X" )) )
	    return 1;
	else if( (!ta.getAAsymbol().equals( "X" )) && (tb.getAAsymbol().equals( "X" )) )
	    return -1;
	else
	{
	    int res = ta.getAAsymbol().compareTo( tb.getAAsymbol() );
	    if( res == 0 )
	    {
		if( ta.getSeqCode() < tb.getSeqCode() ) return -1;
		else if( ta.getSeqCode() == tb.getSeqCode() ) return 0;
		else if( ta.getSeqCode() > tb.getSeqCode() ) return 1;
	    }
	    else return res;
	}
	return 0;
    }
}
