<?php

class SampleCond {
    public $ph = "";
    public $punit = "";
    public $temp = "";
    public $tunit = "";
    public $other = "";
    public function toString() { 
	return $this->punit . " " . $this->ph 
	     . ", temperature " . $this->temp . " " . $this->tunit 
	     . ", other: " . $this->other; 
    }
    public function toRTFString() { 
        $rc = "";
	if( strlen( $this->ph ) > 0 ) {
	    if( strlen( $this->punit ) > 0 ) {
		$rc .= $this->punit;
		$rc .= " ";
	    }
	    else $rc .= "pH ";
	    $rc .= $this->ph;
	}
	if( strlen( $this->temp ) > 0 ) {
	    if( strlen( $rc ) > 0 ) $rc .= ", ";
	    $rc .= "temperature ";
	    $rc .= $this->temp;
	    $rc .= "{\\fs16{\\field{\\*\\fldinst SYMBOL 176 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}} ";
	    if( strlen( $this->tunit ) > 0 ) $rc .= $this->tunit;
	    else $rc .= "K";
	}
	if( strlen( $this->other ) > 0 ) {
	    if( strlen( $rc ) > 0 ) $rc .= ", ";
	    $rc .= $this->other;
	}
	return $rc;
    }
}

?>