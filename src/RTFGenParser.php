<?php
require_once 'StarParser.php';
include_once "./SampleCond.php";
include_once "./ShiftRow.php";

class RTFGenParser implements ErrorHandler, ContentHandler {

    public $molnames;
    public $conditions;
    public $data;
    public $details;

    private $aname;
    private $eid;
    private $aeid;
    private $condlabel;
    private $ph;
    private $temp;
    private $hdr_printed;
    private $last_eid;
    private $last_conds;
    private $table_num;
    private $row_num;
    private $sfname;
    
    function __construct() {
	$this->aname = NULL;
	$this->aeid = NULL;
	$this->eid = NULL;
	$this->molnames = array();
	$this->condlabel = NULL;
	$this->conditions = array();
	$this->details = array();
	$this->ph = false;
	$this->temp = false;
	$this->hdr_printed = false;
	$this->last_eid = NULL;
	$this->last_conds = NULL;
	$this->table_num = 0;
	$this->data = array();
	$this->row_num = -1;
	$this->sfname = NULL;
    }
    
    function criticalError( $line, $msg ) {
	die( "Critical error in line $line: $msg" );
        return true;
    }
    function error( $line, $msg ) {
	echo "Error in line $line: $msg\n";
        return true;
    }
    function warning( $line, $msg ) {
	echo "Warning in line $line: $msg\n";
        return false;
    }
    function startData( $line, $id ) {
        return false;
    }
    function endData( $line, $id ) {
    }
    function startSaveframe( $line, $id ) {
        return false;
    }
    function endSaveframe( $line, $id ) {
	$this->aeid = NULL;
	$this->eid = NULL;
	$this->condlabel = NULL;
	$this->last_eid = NULL;
	$this->last_conds = NULL;
	$this->row_num = -1;
	$this->sfname = NULL;
        return false;
    }
    function startLoop( $line ) {
        return false;
    }
    function endLoop( $line ) {
	$this->ph = false;
	$this->temp = false;
        return false;
    }
    function comment( $line, $text ) {
        return false;
    }
    function data( $tag, $tagline, $val, $valline, $delim, $loop ) {
	if( strcmp( $tag, "_Assembly.Name" ) == 0 ) {
	    $this->aname = $val;
	}
	if( strcmp( $tag, "_Entity_assembly.ID" ) == 0 ) {
	    $this->aeid = $val;
	}
	if( strcmp( $tag, "_Entity_assembly.Entity_assembly_name" ) == 0 ) {
	    $this->molnames[$this->aeid] = $this->aname . " " . $val;
	}
/*
	if( strcmp( $tag, "_Entity.ID" ) == 0 ) {
	    $this->eid = $val;
	}
//
// Molecule names
// NOTE: 2.1 prints "_Mol_system_name _Mol_system_component_name"
// in 3.1 that will be "_Assembly.Name _Entity_assembly.Entity_assembly_name"
//
	if( ($this->eid != NULL) && (strcmp( $tag, "_Entity.Name" ) == 0) ) {
	    $this->molnames[$this->eid] = $val;
	}
*/
//
// Sample conditions
//
	if( strcmp( $tag, "_Sample_condition_list.Sf_framecode" ) == 0 ) {
	    $this->condlabel = $val;
	    $this->conditions[$this->condlabel] = new SampleCond;
	}
	if( strcmp( $tag, "_Sample_condition_variable.Type" ) == 0 ) {
	    if( (preg_match( "/^pH(\*)*/", $val ) == 1) || (preg_match( "/^pD/", $val ) == 1) ) {
		$this->temp = false;
		$this->ph = true;
		$this->conditions[$this->condlabel]->punit = $val;
	    }
	    else if( preg_match( "/^temperature/", $val ) == 1 ) {
		$this->temp = true;
		$this->ph = false;
	    }
	    else {
		$this->temp = false;
		$this->ph = false;
		if( strlen( $this->conditions[$this->condlabel]->other ) < 1 )
		    $this->conditions[$this->condlabel]->other = $val;
		else {
		    $this->conditions[$this->condlabel]->other .= ", ";
		    $this->conditions[$this->condlabel]->other .= $val;
		}
	    }
	}
	if( strcmp( $tag, "_Sample_condition_variable.Val" ) == 0 ) {
	    if( $this->ph ) {
		$this->conditions[$this->condlabel]->ph = $val;
	    }
	    else if( $this->temp ) {
		$this->conditions[$this->condlabel]->temp = $val;
	    }
	    else {
		$this->conditions[$this->condlabel]->other .= " ";
		$this->conditions[$this->condlabel]->other .= $val;
	    }
	}
	if( strcmp( $tag, "_Sample_condition_variable.Val_units" ) == 0 ) {
	    if( $this->ph ) {
		// do nothing
		$this->ph = false;
	    }
	    else if( $this->temp ) {
		$this->conditions[$this->condlabel]->tunit = $val;
		$this->temp = false;
	    }
	    else {
		$this->conditions[$this->condlabel]->other .= " ";
		$this->conditions[$this->condlabel]->other .= $val;
	    }
	}
// chemical shifts
	if( strcmp( $tag, "_Assigned_chem_shift_list.Sf_framecode" ) == 0 ) {
	    $this->sfname = $val;
	    $this->data[$this->sfname] = array();
	}
	if( strcmp( $tag, "_Assigned_chem_shift_list.Sample_condition_list_label" ) == 0 ) {
	    $this->last_conds = $val;
	}
	if( strcmp( $tag, "_Assigned_chem_shift_list.Details" ) == 0 ) {
	    $this->details[$this->sfname] = $val;
	}
// loop
//	if( strcmp( $tag, "_Atom_chem_shift.Entity_ID" ) == 0 ) {
//	    $this->row_num++;
//	    $this->data[] = new ShiftRow( $this->last_conds, $val );
//	}
	if( strcmp( $tag, "_Atom_chem_shift.Entity_assembly_ID" ) == 0 ) {
	    $this->row_num++;
	    $this->data[$this->sfname][] = new ShiftRow( $this->last_conds, $val );
	}
	if( strcmp( $tag, "_Atom_chem_shift.Comp_index_ID" ) == 0 ) {
	    $this->data[$this->sfname][$this->row_num]->seqid = $val;
	}
	if( strcmp( $tag, "_Atom_chem_shift.Comp_ID" ) == 0 ) {
	    $this->data[$this->sfname][$this->row_num]->compid = $val;
	    $this->data[$this->sfname][$this->row_num]->code = get_code( $val );
	}
	if( strcmp( $tag, "_Atom_chem_shift.Atom_ID" ) == 0 ) {
	    $this->data[$this->sfname][$this->row_num]->atomid = $val;
	}
	if( (strcmp( $tag, "_Atom_chem_shift.Val" ) == 0) 
	 || (strcmp( $tag, "_Atom_chem_shift.Chem_shift_val" ) == 0) ) {
	    $this->data[$this->sfname][$this->row_num]->val = $val;
	}
        return false;
    }
}

?>