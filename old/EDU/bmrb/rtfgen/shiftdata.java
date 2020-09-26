package EDU.bmrb.rtfgen;
/**
 * Class that contains raw chemical shift data. 
 * rtfgen.getChemLoop returns a vector of these.
 * 
 * @author dmaziuk@bmrb.wisc.edu
 * @version 1
 ************/


public class shiftdata
{
    private String seq_code;
    private String res_label;
    private String atom_name;
    private String chem_shift;
    /** creates new shiftdata */
    public shiftdata()
    {
	seq_code = new String();
	res_label = new String();
	atom_name = new String();
	chem_shift = new String();
    }
    /** changes sequence code.
     * @param code sequence code
     */
    public void setSeqCode( String code ) { seq_code = code; }
    /** changes residue label.
     * @param label residue label
     */
    public void setResLabel( String label ) { res_label = label; }
    /** changes atom name.
     * @param name atom name
     */
    public void setAtomName( String name ) { atom_name = name; }
    /** changes chemical shift value.
     * @param shift chemical shift value
     */
    public void setChemShift( String shift ) { chem_shift = shift; }
    /** returns sequence code
     * @return sequence code
     */
    public String getSeqCode() { return seq_code; }
    /** returns residue label.
     * @return label residue label
     */
    public String getResLabel() { return res_label; }
    /** returns atom name.
     * @return name atom name
     */
    public String getAtomName() { return atom_name; }
    /** returns chemical shift value.
     * @return shift chemical shift value
     */
    public String getChemShift() { return chem_shift; }
    /** returns this object as string
     * @return "sequence_code residue_label atom_name chemical_shift" string
     */
    public String toString()
    {
	return( seq_code + " " + res_label + " " + atom_name + " " + chem_shift );
    }

}
