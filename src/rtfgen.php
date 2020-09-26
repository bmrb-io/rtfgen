<?php
include_once '../../php_includes/Globals.inc';
require_once 'RTFGenParser.php';
require_once 'LoopParser.php';
require_once "SampleCond.php";
require_once "ShiftRow.php";
require_once "rtf.php";

function find_table_num( $tables, $cond, $molname ) {
    $exp = ": ";
    $exp .= $molname;
    $exp .= " resonance assignments ";
    $exp .= $cond;
    $exp .= RTF_TBLTITLE_END;
    $exp .= RTF_TBLHEADER;
    for( $i = 0; $i < count( $tables ); $i++ ) {
//echo "+++ " . $tables[$i] . "\n";
//echo "+++ " . $exp . "\n";
	if( strpos( $tables[$i], $exp ) !== FALSE ) {
	    return $i;
	}
    }
    return -1;
}

function make_rtf_row( $seq, $code, $atoms, $special, $footnotes, $label ) {
    $rc = RTF_ROWHEADER;
    $rc .= "{\\fs16 ";
    $rc .= $code;
    $rc .= $seq;
    if( $special ) {
	$rc .= "{\\fs16\\super ";
	$rc .= count( $footnotes );
	$rc .= "}";
    }
// add atoms
// N
    $rc .= "\\cell ";
    if( array_key_exists( "N", $atoms ) ) {
	$rc .= $atoms["N"];
    }
// NH
    if( array_key_exists( "H", $atoms ) ) {
	$rc .= " (";
	$rc .= $atoms["H"];
	$rc .= ")";
    }
// C
    $rc .= "\\cell ";
    if( array_key_exists( "C", $atoms ) ) {
	$rc .= $atoms["C"];
    }
// CA
    $rc .= "\\cell ";
    if( array_key_exists( "CA", $atoms ) ) {
	$rc .= $atoms["CA"];
    }
// HA or HA2, HA3
    if( array_key_exists( "HA", $atoms ) ) {
	$rc .= " (";
	$rc .= $atoms["HA"];
	$rc .= ")";
    }
    else if( array_key_exists( "QA", $atoms ) ) {
	$rc .= " (";
	$rc .= $atoms["QA"];
	$rc .= ")";
    }
    else if( array_key_exists( "HA2", $atoms )
          || array_key_exists( "HA3", $atoms ) ) {
	$rc .= " (";
	if( array_key_exists( "HA2", $atoms ) ) {
	    $rc .= $atoms["HA2"];
        }
        else $rc .= "*";
        $rc .= ", ";
        if( array_key_exists( "HA3", $atoms ) ) {
	    $rc .= $atoms["HA3"];
	}
	else $rc .= "*";
	$rc .= ")";
    }
// CB
    $rc .= "\\cell ";
    if( array_key_exists( "CB", $atoms ) ) {
	$rc .= $atoms["CB"];
    }
// HB or HB2, HB3 or HB1, HB2, HB3
    if( array_key_exists( "HB", $atoms ) ) {
        $rc .= " (";
	$rc .= $atoms["HB"];
	$rc .= ")";
    }
    else if( array_key_exists( "MB", $atoms ) ) {
        $rc .= " (";
	$rc .= $atoms["MB"];
	$rc .= ")";
    }
    else if( array_key_exists( "QB", $atoms ) ) {
        $rc .= " (";
	$rc .= $atoms["QB"];
	$rc .= ")";
    }
    else if( array_key_exists( "HB1", $atoms )
          || array_key_exists( "HB2", $atoms )
          || array_key_exists( "HB3", $atoms ) ) {
        if( strcmp( "A", $code ) == 0 ) { // ALA: collapse methyl into one
            if( array_key_exists( "HB1", $atoms ) ) $rc .= " (" . $atoms["HB1"] . ")";
            else if( array_key_exists( "HB2", $atoms ) ) $rc .= " (" . $atoms["HB2"] . ")";
            else if( array_key_exists( "HB3", $atoms ) ) $rc .= " (" . $atoms["HB3"] . ")";
        }
/*
        $rc .= " (";
        if( strcmp( "A", $code ) == 0 ) { // ALA: collapse methyl into one
    	    if( array_key_exists( "HB1", $atoms ) ) $rc .= $atoms["HB1"];
    	    else if( array_key_exists( "HB2", $atoms ) ) $rc .= $atoms["HB2"];
    	    else if( array_key_exists( "HB3", $atoms ) ) $rc .= $atoms["HB3"];
*/
/*
            if( array_key_exists( "HB1", $atoms ) ) {
                $rc .= $atoms["HB1"];
                $rc .= ", ";
            }
            if( array_key_exists( "HB2", $atoms ) ) $rc .= $atoms["HB2"];
            else $rc .= "*";
            $rc .= ", ";
            if( array_key_exists( "HB3", $atoms ) ) $rc .= $atoms["HB3"];
            else $rc .= "*";

        }
*/
/* -- this was broken
	else if( strpos( "ITV", $code ) !== FALSE ) { // ILE, THR, VAL: single proton
            if( array_key_exists( "HB", $atoms ) ) $rc .= $atoms["HB"];
            else $rc .= "*";
        }
*/
        else { // others: methylene
            $rc .= " (";
            if( array_key_exists( "HB2", $atoms ) ) $rc .= $atoms["HB2"];
            else $rc .= "*";
            $rc .= ", ";
            if( array_key_exists( "HB3", $atoms ) ) $rc .= $atoms["HB3"];
            else $rc .= "*";
            $rc .= ")";
        }
    }
// "other"
// CG
//echo "+++ $code\n";
    $other = "";
    if( strpos( "DFHNWY", $code ) !== FALSE ) { // GC only
//echo "+++ in 'DFHNWY'\n";
	if( array_key_exists( "CG", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG;
	    $other .= "{\\fs16, ";
	    $other .= $atoms["CG"];
	}
    } // endif DFHNWY
    if( strpos( "EKMPQR", $code ) !== FALSE ) {
// GLU, LYS, MET, PRO, GLN, ARG
	if( array_key_exists( "CG", $atoms )
	 || array_key_exists( "HG2", $atoms )
	 || array_key_exists( "HG3", $atoms )
	 || array_key_exists( "QG", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CG", $atoms ) ) $other .= $atoms["CG"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "QG", $atoms ) ) $other .= $atoms["QG"];
	    else {
	        if( array_key_exists( "HG2", $atoms ) ) $other .= $atoms["HG2"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HG3", $atoms ) ) $other .= $atoms["HG3"];
	        else $other .= "*";
            }
	    $other .= ")";
	}
    } // endif EKMPQR
    if( strpos( "L", $code ) !== FALSE ) {
// LEU
	if( array_key_exists( "CG", $atoms )
	 || array_key_exists( "HG", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CG", $atoms ) ) {
		$other .= $atoms["CG"];
	    }
	    else $other .= "*";
	    if( array_key_exists( "HG", $atoms ) ) { // methyl or methylene group
		$other .= " (";
		$other .= $atoms["HG"];
		$other .= ")";
	    }
	    else $other .= "(*)";
	}
    } // endif L
// CIST - no CG
// CG1
    if( strpos( "I", $code ) !== FALSE ) {
//echo "+++ in 'I'(CG1)\n";
	if( array_key_exists( "CG1", $atoms )
	 || array_key_exists( "HG12", $atoms )
	 || array_key_exists( "HG13", $atoms )
	 || array_key_exists( "QG", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CG1", $atoms ) ) {
	    	$other .= $atoms["CG1"];
	    }
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "QG", $atoms ) ) $other .= $atoms["QG"];
            else {
                if( array_key_exists( "HG12", $atoms ) ) $other .= $atoms["HG12"];
                $other .= ", ";
                if( array_key_exists( "HG13", $atoms ) ) $other .= $atoms["HG13"];
                else $other .= "*";
            }
            $other .= ")";
        }
//echo "++++ $other\n";
    } // endif I
    if( strpos( "V", $code ) !== FALSE ) {
	if( array_key_exists( "CG1", $atoms )
	 || array_key_exists( "HG11", $atoms )
	 || array_key_exists( "HG12", $atoms )
         || array_key_exists( "HG13", $atoms )
	 || array_key_exists( "MG1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CG1", $atoms ) ) {
		$other .= $atoms["CG1"];
	    }
	    else $other .= "*";
            if( array_key_exists( "MG1", $atoms ) ) $other .= " (" . $atoms["MG1"] . ")";
            else if( array_key_exists( "HG11", $atoms ) ) $other .= " (" . $atoms["HG11"] . ")";
            else if( array_key_exists( "HG12", $atoms ) ) $other .= " (" . $atoms["HG12"] . ")";
            else if( array_key_exists( "HG13", $atoms ) ) $other .= " (" . $atoms["HG13"] . ")";
/*
            $other .= " (";
            if( array_key_exists( "MG1", $atoms ) ) $other .= $atoms["MG1"];
            else {
                if( array_key_exists( "HG11", $atoms ) ) $other .= $atoms["HG11"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HG12", $atoms ) ) $other .= $atoms["HG12"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HG13", $atoms ) ) $other .= $atoms["HG13"];
                else $other .= "*";
            }
            $other .= ")";
*/
        }
    } // endif V
// CG2
    if( strpos( "V", $code ) !== FALSE ) {
	if( array_key_exists( "CG2", $atoms )
	 || array_key_exists( "HG21", $atoms )
	 || array_key_exists( "HG22", $atoms )
         || array_key_exists( "HG23", $atoms )
	 || array_key_exists( "MG2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CG2", $atoms ) ) {
		$other .= $atoms["CG2"];
	    }
	    else $other .= "*";

            if( array_key_exists( "MG2", $atoms ) ) $other .= " (" . $atoms["MG2"] . ")";
            else if( array_key_exists( "HG21", $atoms ) ) $other .= " (" . $atoms["HG21"] . ")";
            else if( array_key_exists( "HG22", $atoms ) ) $other .= " (" . $atoms["HG22"] . ")";
            else if( array_key_exists( "HG23", $atoms ) ) $other .= " (" . $atoms["HG23"] . ")";
/*
            $other .= " (";
            if( array_key_exists( "MG2", $atoms ) ) $other .= $atoms["MG2"];
            else {
                if( array_key_exists( "HG21", $atoms ) ) $other .= $atoms["HG21"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HG22", $atoms ) ) $other .= $atoms["HG22"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HG23", $atoms ) ) $other .= $atoms["HG23"];
                else $other .= "*";
            }
            $other .= ")";
*/
        }
    } // endif V
    if( strpos( "IT", $code ) !== FALSE ) {
// ILE, THR
//echo "+++ in 'IT'(CG2)\n";
//print_r( $atoms );
	if( array_key_exists( "CG2", $atoms )
	 || array_key_exists( "HG21", $atoms )
	 || array_key_exists( "HG22", $atoms )
	 || array_key_exists( "HG23", $atoms )
	 || array_key_exists( "MG", $atoms ) ) {
//echo "+++ key exists (CG2)\n";
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CG2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CG2", $atoms ) ) {
		$other .= $atoms["CG2"];
	    }
	    else $other .= "*";
            if( array_key_exists( "MG", $atoms ) ) $other .= " (" . $atoms["MG"] . ")";
            else if( array_key_exists( "HG21", $atoms ) ) $other .= " (" . $atoms["HG21"] . ")";
            else if( array_key_exists( "HG22", $atoms ) ) $other .= " (" . $atoms["HG22"] . ")";
            else if( array_key_exists( "HG23", $atoms ) ) $other .= " (" . $atoms["HG23"] . ")";
/*
	    $other .= " (";
            if( array_key_exists( "MG", $atoms ) ) $other .= $atoms["MG"];
            else {
                if( array_key_exists( "HG21", $atoms ) ) $other .= $atoms["HG21"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HG22", $atoms ) ) $other .= $atoms["HG22"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HG23", $atoms ) ) $other .= $atoms["HG23"];
                else $other .= "*";
            }
            $other .= ")";
*/
    }
//echo "++++ $other\n";
    } // endif IT
// CD	
    if( strpos( "EQ", $code ) !== FALSE ) { // CD only
// GLU, GLN
	if( array_key_exists( "CD", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD;
	    $other .= "{\\fs16, ";
	    $other .= $atoms["CD"];
	}
    } // endif EQ
    if( strpos( "KPR", $code ) !== FALSE ) { // CD, HD2, HD3 
// LYS, PRO, ARG
	if( array_key_exists( "CD", $atoms )
	 || array_key_exists( "HD2", $atoms )
	 || array_key_exists( "HD3", $atoms )
	 || array_key_exists( "QD", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CD", $atoms ) ) $other .= $atoms["CD"];
	    else $other .= "*";
	    $other .= " (";
            if( array_key_exists( "QD", $atoms ) ) $other .= $atoms["QD"];
            else {
                if( array_key_exists( "HD2", $atoms ) ) $other .= $atoms["HD2"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HD3", $atoms ) ) $other .= $atoms["HD3"];
                else $other .= "*";
            }
            $other .= ")";
        }
    } // endif KPR
// CD1
    if( strpos( "FWY", $code ) !== FALSE ) { // CD1, HD1 
// PHE, TRP, TYR
	if( array_key_exists( "CD1", $atoms )
	 || array_key_exists( "HD1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CD1", $atoms ) ) $other .= $atoms["CD1"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HD1", $atoms ) ) $other .= $atoms["HD1"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif FWY
    if( strpos( "I", $code ) !== FALSE ) { // 
	if( array_key_exists( "CD1", $atoms )
	 || array_key_exists( "HD11", $atoms )
	 || array_key_exists( "HD12", $atoms )
	 || array_key_exists( "HD13", $atoms )
	 || array_key_exists( "MD", $atoms ) ) {
//echo "+++ key exists ILE(CD1)\n";
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CD1", $atoms ) ) $other .= $atoms["CD1"];
	    else $other .= "*";
            if( array_key_exists( "MD", $atoms ) ) $other .= " (" . $atoms["MD"] . ")";
            else if( array_key_exists( "HG11", $atoms ) ) $other .= " (" . $atoms["HG11"] . ")";
            else if( array_key_exists( "HG12", $atoms ) ) $other .= " (" . $atoms["HG12"] . ")";
            else if( array_key_exists( "HG13", $atoms ) ) $other .= " (" . $atoms["HG13"] . ")";
/*
	    $other .= " (";
            if( array_key_exists( "MD", $atoms ) ) $other .= $atoms["MD"];
            else {
                if( array_key_exists( "HD11", $atoms ) ) $other .= $atoms["HD11"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HD12", $atoms ) ) $other .= $atoms["HD12"];
                else $other .= "*";
                $other .= ", ";
                if( array_key_exists( "HD13", $atoms ) ) $other .= $atoms["HD13"];
                else $other .= "*";
            }
            $other .= ")";
*/
        }
//echo "++++ $other\n";
    } // endif I
    if( strpos( "L", $code ) !== FALSE ) { // CD1, HD11, HD12, HD13 
	if( array_key_exists( "CD1", $atoms )
	 || array_key_exists( "HD11", $atoms )
	 || array_key_exists( "HD12", $atoms )
	 || array_key_exists( "HD13", $atoms )
	 || array_key_exists( "MD1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CD1", $atoms ) ) $other .= $atoms["CD1"];
	    else $other .= "*";
            if( array_key_exists( "MD1", $atoms ) ) $other .= " (" . $atoms["MD1"] . ")";
            else if( array_key_exists( "HG11", $atoms ) ) $other .= " (" . $atoms["HG11"] . ")";
            else if( array_key_exists( "HG12", $atoms ) ) $other .= " (" . $atoms["HG12"] . ")";
            else if( array_key_exists( "HG13", $atoms ) ) $other .= " (" . $atoms["HG13"] . ")";
/*
	    $other .= " (";
	    if( array_key_exists( "MD1", $atoms ) ) $other .= $atoms["MD1"];
	    else {
	        if( array_key_exists( "HD11", $atoms ) ) $other .= $atoms["HD11"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HD12", $atoms ) ) $other .= $atoms["HD12"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HD13", $atoms ) ) $other .= $atoms["HD13"];
	        else $other .= "*";
            }
	    $other .= ")";
*/
	}
    } // endif L
// CD2
    if( strpos( "W", $code ) !== FALSE ) { // CD2 only
	if( array_key_exists( "CD2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD2;
	    $other .= "{\\fs16, ";
	    $other .= $atoms["CD2"];
	}
    } // endif W
    if( strpos( "FHY", $code ) !== FALSE ) { // CD2, HD2 
	if( array_key_exists( "CD2", $atoms )
	 || array_key_exists( "HD2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CD2", $atoms ) ) $other .= $atoms["CD2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HD2", $atoms ) ) $other .= $atoms["HD2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif FHY
    if( strpos( "L", $code ) !== FALSE ) { // CD2, HD21, HD22, HD23 
	if( array_key_exists( "CD2", $atoms )
	 || array_key_exists( "HD21", $atoms )
	 || array_key_exists( "HD22", $atoms )
	 || array_key_exists( "HD23", $atoms )
	 || array_key_exists( "MD2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CD2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CD2", $atoms ) ) $other .= $atoms["CD2"];
	    else $other .= "*";
            if( array_key_exists( "MD2", $atoms ) ) $other .= " (" . $atoms["MD2"] . ")";
            else if( array_key_exists( "HG21", $atoms ) ) $other .= " (" . $atoms["HG21"] . ")";
            else if( array_key_exists( "HG22", $atoms ) ) $other .= " (" . $atoms["HG22"] . ")";
            else if( array_key_exists( "HG23", $atoms ) ) $other .= " (" . $atoms["HG23"] . ")";
/*
	    $other .= " (";
	    if( array_key_exists( "MD2", $atoms ) ) $other .= $atoms["MD2"];
	    else {
	        if( array_key_exists( "HD21", $atoms ) ) $other .= $atoms["HD21"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HD22", $atoms ) ) $other .= $atoms["HD22"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HD23", $atoms ) ) $other .= $atoms["HD23"];
	        else $other .= "*";
            }
            $other .= ")";
*/
	}
    } // endif L
// CE
    if( strpos( "K", $code ) !== FALSE ) { // CE, HE2, HE3 
	if( array_key_exists( "CE", $atoms )
	 || array_key_exists( "HE2", $atoms )
	 || array_key_exists( "HE3", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CE;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CE", $atoms ) ) $other .= $atoms["CE"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE2", $atoms ) ) $other .= $atoms["HE2"];
	    else $other .= "*";
	    $other .= ", ";
	    if( array_key_exists( "HE3", $atoms ) ) $other .= $atoms["HE3"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif K
    if( strpos( "M", $code ) !== FALSE ) { // CE, HE11, HE12, HE13 
	if( array_key_exists( "CE", $atoms )
	 || array_key_exists( "HE1", $atoms )
	 || array_key_exists( "HE2", $atoms )
	 || array_key_exists( "HE3", $atoms )
	 || array_key_exists( "ME", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CE;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CE", $atoms ) ) $other .= $atoms["CE"];
	    else $other .= "*";
            if( array_key_exists( "ME", $atoms ) ) $other .= " (" . $atoms["ME"] . ")";
            else if( array_key_exists( "HE1", $atoms ) ) $other .= " (" . $atoms["HE1"] . ")";
            else if( array_key_exists( "HE2", $atoms ) ) $other .= " (" . $atoms["HE2"] . ")";
            else if( array_key_exists( "HE3", $atoms ) ) $other .= " (" . $atoms["HE3"] . ")";
/*
	    $other .= " (";
	    if( array_key_exists( "ME", $atoms ) ) $other .= $atoms["ME"];
	    else {
	        if( array_key_exists( "HE1", $atoms ) ) $other .= $atoms["HE1"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HE2", $atoms ) ) $other .= $atoms["HE2"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HE3", $atoms ) ) $other .= $atoms["HE3"];
	        else $other .= "*";
            }
	    $other .= ")";
*/
	}
    } // endif M
// CE1
    if( strpos( "FHY", $code ) !== FALSE ) { // CE1, HE1 
	if( array_key_exists( "CE1", $atoms )
	 || array_key_exists( "HE1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CE1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CE1", $atoms ) ) $other .= $atoms["CE1"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE1", $atoms ) ) $other .= $atoms["HE1"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif FHY
// CE2
    if( strpos( "W", $code ) !== FALSE ) { // CE2 only
	if( array_key_exists( "CE2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CE2;
	    $other .= "{\\fs16, ";
	    $other .= $atoms["CE2"];
	}
    } // endif W
    if( strpos( "FY", $code ) !== FALSE ) { // CE2, HE2 
	if( array_key_exists( "CE2", $atoms )
	 || array_key_exists( "HE2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CE2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CE2", $atoms ) ) $other .= $atoms["CE2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE2", $atoms ) ) $other .= $atoms["HE2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif FY
// CE3
    if( strpos( "W", $code ) !== FALSE ) { // CE3 only
	if( array_key_exists( "CE3", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CE3;
	    $other .= "{\\fs16, ";
	    $other .= $atoms["CE3"];
	}
    } // endif W
// CH2
    if( strpos( "W", $code ) !== FALSE ) { // CH2, HH2 
	if( array_key_exists( "CH2", $atoms )
	 || array_key_exists( "HH2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CH2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CH2", $atoms ) ) $other .= $atoms["CH2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HH2", $atoms ) ) $other .= $atoms["HH2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif W
// CZ
    if( strpos( "RY", $code ) !== FALSE ) { // CZ only
	if( array_key_exists( "CZ", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CZ;
	    $other .= "{\\fs16, ";
	    $other .= $atoms["CZ"];
	}
    } // endif RY
    if( strpos( "F", $code ) !== FALSE ) { // CZ, HZ 
	if( array_key_exists( "CZ", $atoms )
	 || array_key_exists( "HZ", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CZ;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CZ", $atoms ) ) $other .= $atoms["CZ"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HZ", $atoms ) ) $other .= $atoms["HZ"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif F
// CZ2
    if( strpos( "W", $code ) !== FALSE ) { // CZ2, HZ2 
	if( array_key_exists( "CZ2", $atoms )
	 || array_key_exists( "HZ2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CZ2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CZ2", $atoms ) ) $other .= $atoms["CZ2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HZ2", $atoms ) ) $other .= $atoms["HZ2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif W
// CZ3
    if( strpos( "W", $code ) !== FALSE ) { // CZ3, HZ3
	if( array_key_exists( "CZ3", $atoms )
	 || array_key_exists( "HZ3", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_CZ3;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "CZ3", $atoms ) ) $other .= $atoms["CZ3"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HZ3", $atoms ) ) $other .= $atoms["HZ3"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif W
// ND1
    if( strpos( "H", $code ) !== FALSE ) { // ND1, HD1
	if( array_key_exists( "ND1", $atoms )
	 || array_key_exists( "HD1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_ND1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "ND1", $atoms ) ) $other .= $atoms["ND1"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HD1", $atoms ) ) $other .= $atoms["HD1"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif H
// ND2
    if( strpos( "N", $code ) !== FALSE ) { // ND2, HD21, HD22
	if( array_key_exists( "ND2", $atoms )
	 || array_key_exists( "HD21", $atoms )
	 || array_key_exists( "HD22", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_ND2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "ND2", $atoms ) ) $other .= $atoms["ND2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HD21", $atoms ) ) $other .= $atoms["HD21"];
	    else $other .= "*";
	    $other .= ", ";
	    if( array_key_exists( "HD22", $atoms ) ) $other .= $atoms["HD22"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif N
// NE
    if( strpos( "R", $code ) !== FALSE ) { // NE, HE
	if( array_key_exists( "NE", $atoms )
	 || array_key_exists( "HE", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NE;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NE", $atoms ) ) $other .= $atoms["NE"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE", $atoms ) ) $other .= $atoms["HE"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif R
// NE1
    if( strpos( "W", $code ) !== FALSE ) { // NE1, HE1
	if( array_key_exists( "NE1", $atoms )
	 || array_key_exists( "HE1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NE1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NE1", $atoms ) ) $other .= $atoms["NE1"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE1", $atoms ) ) $other .= $atoms["HE1"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif W
// NE2
    if( strpos( "H", $code ) !== FALSE ) { // NE2, HE2
	if( array_key_exists( "NE2", $atoms )
	 || array_key_exists( "HE2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NE2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NE2", $atoms ) ) $other .= $atoms["NE2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE2", $atoms ) ) $other .= $atoms["HE2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif H
    if( strpos( "Q", $code ) !== FALSE ) { // NE2, HE21, HE22 
	if( array_key_exists( "NE2", $atoms )
	 || array_key_exists( "HE21", $atoms )
	 || array_key_exists( "HE22", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NE2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NE2", $atoms ) ) $other .= $atoms["NE2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE21", $atoms ) ) $other .= $atoms["HE21"];
	    else $other .= "*";
	    $other .= ", ";
	    if( array_key_exists( "HE22", $atoms ) ) $other .= $atoms["HE22"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif Q
// NH1 & 2
    if( strpos( "R", $code ) !== FALSE ) {
	if( array_key_exists( "NH1", $atoms )
	 || array_key_exists( "HH11", $atoms )
	 || array_key_exists( "HH12", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NH1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NH1", $atoms ) ) $other .= $atoms["NH1"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HH11", $atoms ) ) $other .= $atoms["HH11"];
	    else $other .= "*";
	    $other .= ", ";
	    if( array_key_exists( "HH12", $atoms ) ) $other .= $atoms["HH12"];
	    else $other .= "*";
	    $other .= ")";
	}
	if( array_key_exists( "NH2", $atoms )
	 || array_key_exists( "HH21", $atoms )
	 || array_key_exists( "HH22", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NH2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NH2", $atoms ) ) $other .= $atoms["NH2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HH21", $atoms ) ) $other .= $atoms["HH21"];
	    else $other .= "*";
	    $other .= ", ";
	    if( array_key_exists( "HH22", $atoms ) ) $other .= $atoms["HH22"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif R
// NZ
    if( strpos( "K", $code ) !== FALSE ) { // NZ, HZ1, HZ2, HZ3 
	if( array_key_exists( "NZ", $atoms )
	 || array_key_exists( "HZ1", $atoms )
	 || array_key_exists( "HZ2", $atoms )
	 || array_key_exists( "HZ3", $atoms )
	 || array_key_exists( "QZ", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_NZ;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "NZ", $atoms ) ) $other .= $atoms["NZ"];
	    else $other .= "*";
            if( array_key_exists( "QZ", $atoms ) ) $other .= " (" . $atoms["QZ"] . ")";
            else if( array_key_exists( "HZ1", $atoms ) ) $other .= " (" . $atoms["HZ1"] . ")";
            else if( array_key_exists( "HZ2", $atoms ) ) $other .= " (" . $atoms["HZ2"] . ")";
            else if( array_key_exists( "HZ3", $atoms ) ) $other .= " (" . $atoms["HZ3"] . ")";
/*
	    $other .= " (";
	    if( array_key_exists( "QZ", $atoms ) ) $other .= $atoms["QZ"];
	    else {
	        if( array_key_exists( "HZ1", $atoms ) ) $other .= $atoms["HZ1"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HZ2", $atoms ) ) $other .= $atoms["HZ2"];
	        else $other .= "*";
	        $other .= ", ";
	        if( array_key_exists( "HZ3", $atoms ) ) $other .= $atoms["HZ3"];
	        else $other .= "*";
            }
	    $other .= ")";
*/
	}
    } // endif K
// O
    if( strpos( "D", $code ) !== FALSE ) { // OD2, HD2
	if( array_key_exists( "OD2", $atoms )
	 || array_key_exists( "HD2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_OD2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "OD2", $atoms ) ) $other .= $atoms["OD2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HD2", $atoms ) ) $other .= $atoms["HD2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif D
    if( strpos( "E", $code ) !== FALSE ) { // OE2, HE2
	if( array_key_exists( "OE2", $atoms )
	 || array_key_exists( "HE2", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_OE2;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "OE2", $atoms ) ) $other .= $atoms["OE2"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HE2", $atoms ) ) $other .= $atoms["HE2"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif E
    if( strpos( "T", $code ) !== FALSE ) { // OG1, HG1
	if( array_key_exists( "OG1", $atoms )
	 || array_key_exists( "HG1", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_OG1;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "OG1", $atoms ) ) $other .= $atoms["OG1"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HG1", $atoms ) ) $other .= $atoms["HG1"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif T
    if( strpos( "Y", $code ) !== FALSE ) { // OH, HH
	if( array_key_exists( "OH", $atoms )
	 || array_key_exists( "HH", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_OH;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "OH", $atoms ) ) $other .= $atoms["OH"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HH", $atoms ) ) $other .= $atoms["HH"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif Y
// SG
    if( strpos( "C", $code ) !== FALSE ) { // SG, HG
	if( array_key_exists( "SG", $atoms )
	 || array_key_exists( "HG", $atoms ) ) {
	    if( $other != "" ) $other .= "; ";
	    $other .= RTF_SG;
	    $other .= "{\\fs16, ";
	    if( array_key_exists( "SG", $atoms ) ) $other .= $atoms["SG"];
	    else $other .= "*";
	    $other .= " (";
	    if( array_key_exists( "HG", $atoms ) ) $other .= $atoms["HG"];
	    else $other .= "*";
	    $other .= ")";
	}
    } // endif C
// special
    if( strpos( "X", $code ) !== FALSE ) {
	if( $other != "" ) $other .= "; ";
	$other .= "} {\\fs16 ";
	$other .= $label;
	$other .= ", ";
	$other .= $seq;
    }

    if( $other != "" ) {
	$rc .= "\\cell ";
	$rc .= $other;
    }
    $rc .= "\\cell } ";
    $rc .= RTF_ROWTAIL;
    return $rc;
}

function make_rtf( $infile, $sort, $complete_entry ) {
    if( ! file_exists( $infile ) )
	return "No such file: $infile\n";

    if( $complete_entry ) {
	$t = new RTFGenParser();
	$in = fopen( $infile, "r" );
	$lex = new STARLexer( $in );
	$p = new StarParser( $lex, $t, $t );
	$p->parse();
	fclose( $in );
/*
foreach( array_keys( $t->molnames ) as $key )
    echo "entity $key : " . $t->molnames[$key] . "\n";
echo "------\n";
foreach( array_keys( $t->conditions ) as $key )
    echo "$key : " . $t->conditions[$key]->toRTFString() . "\n";
echo "------\n";
foreach( $t->data as $dat )
    foreach( $dat as $row )
	echo $row->toString() . "\n";
echo "------\n";
*/

// split array into tables
	$details = array();
	$table_headers = array();
	$table_num = 0;
	foreach( $t->data as $key => $dat ) {
// sort is always by sample conditions, then entity id
//echo  "-- BEFORE sort\n";
//print_r( $dat );
	    usort( $dat, (( $sort == 2 ) ? "by_label" : "by_seq") );
//echo  "-- AFTER sort\n";
//print_r( $dat );
//
	    $lastcond = "";
	    $lasteid = "";
	    $row = "";
	    foreach( $dat as $shiftrow ) {
		if( ($shiftrow->condlabel != $lastcond) || ($shiftrow->eid != $lasteid) ) {
		    $lastcond = $shiftrow->condlabel;
		    $lasteid = $shiftrow->eid;
		    $table_num++;
		    $row = RTF_TBLTITLE_START;
		    $row .= $table_num;
		    $row .= ": ";
		    $row .= $t->molnames[$lasteid];
		    $row .= " resonance assignments ";
		    $row .= $t->conditions[$lastcond]->toRTFString();
		    $row .= RTF_TBLTITLE_END;
		    $row .= RTF_TBLHEADER;
		    $table_headers[] = $row;
		    $details[] = $t->details[$key];
		}
	    }
	}
/*
print_r( $table_headers );
echo "------\n";
print_r( $details );
*/

	$lastcond = "";
	$lasteid = "";
	$lastseqid = "";
	$table_num = 0;
	$tables = array();
	$footnotes = array();
	$atoms = array();
	$lastlabel = "";
	foreach( $t->data as $dat ) {
	    usort( $dat, (( $sort == 2 ) ? "by_label" : "by_seq") );
	    foreach( $dat as $shiftrow ) {
// table number
		if( ($shiftrow->condlabel != $lastcond) || ($shiftrow->eid != $lasteid) ) {
		    if( $lastseqid != "" ) {
// last residue of the previous table
//echo "* For residue $lastseqid\n";
//print_r( $atoms );
			if( strcmp( $res, "X" ) == 0 ) {
			    $special = true;
        		    $footnotes[$table_num][] = "X" . $lastseqid . " = " . $lastlabel;
			}
			else $special = false;
			$row = make_rtf_row( $lastseqid, $res, $atoms, $special, $footnotes[$table_num], $lastlabel );
			$tables[$table_num][] = $row;
			unset( $atoms );
		    }
		    $lastcond = $shiftrow->condlabel;
		    $lasteid = $shiftrow->eid;
		    $lastseqid = $shiftrow->seqid;
		    $res = $shiftrow->code;
		    $lastlabel = $shiftrow->compid;
//		    $res = get_code( $lastlabel );
		    $table_num = find_table_num( $table_headers, $t->conditions[$lastcond]->toRTFString(), $t->molnames[$lasteid] );

		    if( $table_num < 0 ) 
			return "No table for $lasteid, $lastcond: $table_num\n";

		    $tables[$table_num] = array();
		    $footnotes[$table_num] = array();
		}
		if( $lastseqid == $shiftrow->seqid ) {
		    $res = $shiftrow->code;
		    $lastlabel = $shiftrow->compid;
//		    $res = get_code( $lastlabel );
		    $atoms[$shiftrow->atomid] = $shiftrow->val;
		}
		else {
//if( $lastseqid != "" ) {
//echo "* For residue $lastseqid\n";
//print_r( $atoms );
//}
		    if( strcmp( $res, "X" ) == 0 ) {
			$special = true;
    			$footnotes[$table_num][] = "X" . $lastseqid . " = " . $lastlabel;
		    }
		    else $special = false;
		    $row = make_rtf_row( $lastseqid, $res, $atoms, $special, $footnotes[$table_num], $lastlabel );
		    $tables[$table_num][] = $row;
		    unset( $atoms );
		    $atoms = array();
		    $lastseqid = $shiftrow->seqid;
		    $res = $shiftrow->code;
		    $lastlabel = $shiftrow->compid;
//		    $res = get_code( $lastlabel );
		    $atoms[$shiftrow->atomid] = $shiftrow->val;
		}
	    } // end foreach shiftrow
	} // end foreach $t->data
// last residue of last table
//echo "* For residue $lastseqid\n";
//print_r( $atoms );
	$res = $shiftrow->code;
	$lastlabel = $shiftrow->compid;
//	$res = get_code( $lastlabel );
	if( strcmp( $res, "X" ) == 0 ) {
	    $special = true;
	    $footnotes[$table_num][] = "X" . $lastseqid . " = " . $lastlabel;
	}
	else $special = false;
	$row = make_rtf_row( $lastseqid, $res, $atoms, $special, $footnotes[$table_num], $lastlabel );
	$tables[$table_num][] = $row;
	unset( $atoms );

	unset( $t ); // free up some ram
    } // endif $complete_entry
    else {
	$in = fopen( $infile, "r" );
	$lex = new STARLexer( $in );
	$t = new LoopParser( $lex );
	$t->parse();
	fclose( $in );
/*
print_r( $t->data );
echo "+++++\n";
*/
	$table_headers = array();
	$table_headers[0] = RTF_TBLTITLE_START . "1 resonance assignments "
	                  . RTF_TBLTITLE_END . RTF_TBLHEADER;
	$details = array();
	$details[0] = NULL;
	$tables = array();
	$tables[0] = array();
	$footnotes = array();
	$footnotes[0] = array();
	$atoms = array();
	$lastlabel = "";
	$lastseqid = "";
/*
echo "+++ BEFORE sort\n";
print_r( $t->data );
echo "+++++\n";
*/
	usort( $t->data, (( $sort == 2 ) ? "by_label" : "by_seq") );
/*
echo "+++ AFTER sort\n";
print_r( $t->data );
echo "+++++\n";
*/
	foreach( $t->data as $shiftrow ) {
	    if( $lastseqid == "" ) {
	        $lastseqid = $shiftrow->seqid;
		$res = $shiftrow->code;
		$lastlabel = $shiftrow->compid;
//		$res = get_code( $lastlabel );
	    }
	    if( $lastseqid == $shiftrow->seqid ) {
		$res = $shiftrow->code;
		$lastlabel = $shiftrow->compid;
//		$res = get_code( $lastlabel );
		$atoms[$shiftrow->atomid] = $shiftrow->val;
	    }
	    else {
		if( strcmp( $res, "X" ) == 0 ) {
		    $special = true;
    		    $footnotes[0][] = "X" . $lastseqid . " = " . $lastlabel;
		}
		else $special = false;
		$row = make_rtf_row( $lastseqid, $res, $atoms, $special, $footnotes[0], $lastlabel );
		$tables[0][] = $row;
		unset( $atoms );
		$atoms = array();
		$lastseqid = $shiftrow->seqid;
		$res = $shiftrow->code;
		$lastlabel = $shiftrow->compid;
//		$res = get_code( $lastlabel );
		$atoms[$shiftrow->atomid] = $shiftrow->val;
	    }
	} // end foreach shiftrow
	$res = $shiftrow->code;
	$lastlabel = $shiftrow->compid;
//	$res = get_code( $lastlabel );
	if( strcmp( $res, "X" ) == 0 ) {
	    $special = true;
	    $footnotes[0][] = "X" . $lastseqid . " = " . $lastlabel;
	}
	else $special = false;
	$row = make_rtf_row( $lastseqid, $res, $atoms, $special, $footnotes[0], $lastlabel );
	$tables[0][] = $row;
	unset( $atoms );

	unset( $t ); // free up some ram
    } // endif loop only
/*
print_r( $table_headers );
echo "------\n";
print_r( $tables );
echo "***\n";
*/
// printout
//echo "---- CUT HERE ----\n";
    echo RTF_FILEHEADER;
    for( $i = 0; $i < count( $table_headers ); $i++ ) {
	echo $table_headers[$i];
        foreach( $tables[$i] as $row )
    	    echo $row;
	if( count( $footnotes[$i] ) > 0 ) {
	    echo "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par Footnotes:}";
    	    echo "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par ";
    	    for( $j = 0; $j < count( $footnotes[$i] ); $j++ ) {
    		if( $j > 0 ) echo ", ";
    		echo "(" . ($j + 1) . ") ";
    		echo $footnotes[$i][$j];
    	    }
    	    if( $details[$i] == NULL ) echo "\\par\\par}";
    	    else echo "}";
	}
	if( $details[$i] != NULL ) {
	    if( count( $footnotes[$i] ) < 1 )
		echo "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par Footnotes:}";
	    echo "\\pard \\nowidctlpar\\widctlpar\\adjustright {\\fs16\\par (";
	    if( count( $footnotes[$i] ) > 0 ) echo (count( $footnotes[$i] ) + 1) .") ";
	    else echo "1) ";
	    echo $details[$i];
    	    echo "\\par\\par}";
	}
    }
    echo "}";
    return TRUE;
}

//$rc = make_rtf( "loop.str", 1, false );
//if( $rc !== TRUE ) die( "Error: " . $rc . "\n" );

?>
