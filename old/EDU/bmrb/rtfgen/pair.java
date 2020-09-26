package EDU.bmrb.rtfgen;
/**
 * Pair of strings class
 */

public class pair
{
    private String first;
    private String second;
    /** creates new pair. */
    public pair() 
    {
	first = new String();
	second = new String();
    }
    /** creates new pair.
     * @param label first string
     * @param code second string
     */
    public pair( String label, String code )
    {
	first = new String( label );
	second = new String( code );
    }
    /** returns first string
     * @return first string
     */
    public String getLabel() { return first; }
    /** returns second string
     * @return second string
     */
    public String getSeqCode() { return second; }
    /** Changes first string
     * @param s first string
     */
    public void setLabel( String s ) { first = s; }
    /** Changes second string
     * @param s second string
     */
    public void setSeqCode( String s ) { second = s; }
    /** returns this object as string.
     * @return "first_string second_string"
     */ 
    public String toString() { return( first + " " + second );  }
}
