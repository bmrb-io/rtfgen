<?php
require_once 'rtfgen.php';

class ShiftRow {
    public $condlabel;
    public $eid;
    public $seqid;
    public $compid;
    public $atomid;
    public $val;
    public $code;
    function __construct( $condlabel, $eid ) {
	$this->condlabel = $condlabel;
	$this->eid = $eid;
    }
    public function toString() {
	return $this->condlabel . "," . $this->eid . "," 
	     . $this->seqid . "," . $this->compid . "," 
	     . $this->atomid . "," . $this->val;
    }
}

function by_seq( ShiftRow $a, ShiftRow $b ) {
    if( isset( $a->condlabel ) && isset( $b->condlabel ) ) {
	if( $a->condlabel != $b->condlabel ) 
	    return (($a->condlabel > $b->condlabel) ? 1 : -1);
    }
    if( $a->eid != $b->eid ) return (($a->eid > $b->eid) ? 1 : -1);
    if( $a->seqid != $b->seqid ) return (($a->seqid > $b->seqid) ? 1 : -1);
    if( $a->compid != $b->compid ) return (($a->compid > $b->compid) ? 1 : -1);
    if( $a->atomid != $b->atomid ) return (($a->atomid > $b->atomid) ? 1 : -1);
    return (($a->val > $b->val) ? 1 : -1);
//    return 0;
}

function by_label( ShiftRow $a, ShiftRow $b ) {
    if( isset( $a->condlabel ) && isset( $b->condlabel ) ) {
	if( $a->condlabel != $b->condlabel ) 
	    return (($a->condlabel > $b->condlabel) ? 1 : -1);
    }
    if( $a->eid != $b->eid ) return (($a->eid > $b->eid) ? 1 : -1);

//    if( $a->compid != $b->compid ) return (($a->compid > $b->compid) ? 1 : -1);
//    $acode = get_code( $a->compid );
//    $bcode = get_code( $b->compid );
    if( $a->code != $b->code ) return (($a->code > $b->code) ? 1 : -1);
    
    if( $a->seqid != $b->seqid ) return (($a->seqid > $b->seqid) ? 1 : -1);
    if( $a->atomid != $b->atomid ) return (($a->atomid > $b->atomid) ? 1 : -1);
    return (($a->val > $b->val) ? 1 : -1);
    return 0;
}

?>