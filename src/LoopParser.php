<?php
include_once '../../php_includes/Globals.inc';
global $SWLIBDIR;
require_once $SWLIBDIR . "/sans.php";
require_once 'ShiftRow.php';

class LoopParser implements ErrorHandler, ContentHandler {

    public $data;
    private $row_num;
    private $lex;

    function __construct(  STARLexer $lex ) {
	$this->lex = $lex;
	$this->data = array();
	$this->row_num = -1;
    }
    function getContentHandler() {
	return $this;
    }
    function getErrorHandler() {
	return $this;
    }
    function getScanner() {
	return $this->lex;
    }
    function setScanner( STARLexer $lex ) {
	$this->lex = $lex;
    }
    function parse() {
	if( ! isset( $this ) ) die( "Lexer not initialized\n" );
	do {
	    $tok = $this->lex->yylex();
	    switch( $tok ) {
		case STARLexer::ERROR :
//echo "parser: crit. error on " . $this->lex->getText() . "\n";
		    $this->criticalError( $this->lex->getLine(), $this->lex->getText() );
		    return;
		case STARLexer::WARNING :
//echo "parser: warning on " . $this->lex->getText() . "\n";
		    if( $this->warning( $this->lex->getLine(), $this->lex->getText() ) )
			return;
		    break;
		case STARLexer::FILEEND :
//echo "parser: eof on " . $this->lex->getText() . "\n";
		    $this->endData( $this->lex->getLine(), "0000" );
		    return;
		case STARLexer::COMMENT :
//echo "parser: comment " . $this->lex->getText() . "\n";
		    if( $this->comment( $this->lex->getLine(), $this->lex->getText() ) )
			return;
		    break;
		case STARLexer::LOOPSTART :
//echo "parser: start loop\n";
		    if( $this->startLoop( $this->lex->getLine() ) )
			return true;
		    if( $this->parseLoop() ) return true;
		    break;
		case STARLexer::GLOBALSTART : // ignore
		case STARLexer::GLOBALEND :
		case STARLexer::DATASTART :
		case STARLexer::DATAEND :
		case STARLexer::SAVESTART :
		case STARLexer::SAVEEND :
		case STARLexer::TAGNAME :
		case STARLexer::DVNSINGLE :
		case STARLexer::DVNDOUBLE :
		case STARLexer::DVNFRAMECODE :
		case STARLexer::DVNSEMICOLON :
		case STARLexer::DVNNON :
		    break;
		default :
//echo "parser: unknown token " . $tok . " on " . $this->lex->getText() . "\n";
		    $this->criticalError( $this->lex->getLine(), "Invalid token: " . $this->lex->getText() );
		    return;
	    }
	} while( $tok != STARLexer::FILEEND );
    }
    function parseLoop() {
	if( ! isset( $this ) ) die( "Lexer not initialized\n" );
	$tags = array();
	$taglines = array();
	$numvals = 0;
	$loopcol = 0;
	$lastline = -1;
	$wrongline = -1;
	$wrongcol = -1;
	$val = "";
	$tag = "";
	$tagline = -1;
	$parsingtags = true;
	$rc;
	do {
	    $tok = $this->lex->yylex();
	    switch( $tok ) {
		case STARLexer::ERROR :
//echo "parser(4): crit. error on " . $this->lex->getText() . "\n";
		    $this->criticalError( $this->lex->getLine(), $this->lex->getText() );
		    return true;
		case STARLexer::WARNING :
//echo "parser(4): warning on " . $this->lex->getText() . "\n";
		    if( $this->warning( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
		case STARLexer::FILEEND :
//echo "parser(4): eof on " . $this->lex->getText() . "\n";
		    $this->error( $this->lex->getLine(), "Premature end of file (no closing stop_)" );
		    return true;
		case STARLexer::COMMENT :
//echo "parser(4): comment " . $this->lex->getText() . "\n";
		    if( $this->comment( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
// exit point
		case STARLexer::STOP :
//echo "parser(4): end loop\n";
		    if( count( $tags ) < 1 ) {
			if( $this->error( $this->lex->getLine(), "Loop with no tags" ) )
			    return true;
		    }
		    if( $numvals < 1 ) {
			if( $this->error( $this->lex->getLine(), "Loop with no values" ) )
			    return true;
		    }
		    $rc = false;
		    if( ($numvals % count( $tags )) != 0 ) {
			if( $wrongline < 0 ) $wrongline = $this->lex->getLine();
			$rc = $this->warning( $wrongline, "Loop count error" );
		    }
		    $rc = $rc || $this->endLoop( $this->lex->getLine() );
		    return $rc;
		case STARLexer::TAGNAME :
//echo "parser(4): tag " . $this->lex->getText() . "\n";
		    if( ! $parsingtags ) {
			if( $this->error( $this->lex->getLine(), "Value expected, found " . $this->lex->getText() ) )
			    return true;
		    }
		    $tags[] = $this->lex->getText();
		    $taglines[] = $this->lex->getLine();
		    break;
		case STARLexer::DVNSINGLE :
		case STARLexer::DVNDOUBLE :
		case STARLexer::DVNSEMICOLON :
		case STARLexer::DVNFRAMECODE :
		case STARLexer::DVNNON :		
//echo "parser(4): value " . $this->lex->getText() . "\n";
		    if( $parsingtags ) {
			$parsingtags = false;
//echo "tags:\n";
//for( $i = 0; $i < count($tags); $i++ ) echo "$i: $tags[$i]\n";
//echo "\n";
		    }
		    $val = $this->lex->getText();
		    if( $tok == STARLexer::DVNSEMICOLON ) {
			if( preg_match( "/^\n/", $val ) )
			    $val = preg_replace( "/^\n/", "", $val );
		    }
		    $numvals++;
		    $tag = $tags[$loopcol];
		    $tagline = $taglines[$loopcol];
		    $loopcol++;
		    if( $loopcol == count( $tags ) ) {
			if( $lastline != $this->lex->getLine() ) {
			    if( $wrongline < 0 ) $wrongline = $this->lex->getLine();
			    $lastline = $this->lex->getLine();
			}
			$loopcol = 0;
		    }
		    if( $this->data( $tag, $tagline, $val, $this->lex->getLine(), $tok, true ) )
			return true;
		    $val = "";
		    $tag = "";
		    $tagline = -1;
		    break;
		default :
//echo "parser(4): unknown token " . $tok . " on " . $this->lex->getText() . "\n";
		    $this->criticalError( $this->lex->getLine(), "Invalid token: " . $this->lex->getText() );
		    return true;
	    }
	} while( $tok != STARLexer::FILEEND );
    }
    function criticalError( $line, $msg ) {
	echo "Critical error in line $line: $msg\n";
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
	$this->row_num = -1;
        return false;
    }
    function startLoop( $line ) {
        return false;
    }
    function endLoop( $line ) {
        return false;
    }
    function comment( $line, $text ) {
        return false;
    }
    function data( $tag, $tagline, $val, $valline, $delim, $loop ) {
// chemical shifts loop
	if( strcmp( $tag, "_Atom_chem_shift.Comp_index_ID" ) == 0 ) {
	    $this->row_num++;
	    $this->data[$this->row_num] = new ShiftRow( NULL, 1 );
	    $this->data[$this->row_num]->seqid = $val;
	}
	if( strcmp( $tag, "_Atom_chem_shift.Comp_ID" ) == 0 ) {
	    $this->data[$this->row_num]->compid = $val;
	    $this->data[$this->row_num]->code = get_code( $val );
	}
	if( strcmp( $tag, "_Atom_chem_shift.Atom_ID" ) == 0 ) {
	    $this->data[$this->row_num]->atomid = $val;
	}
	if( (strcmp( $tag, "_Atom_chem_shift.Val" ) == 0) 
	 || (strcmp( $tag, "_Atom_chem_shift.Chem_shift_val" ) == 0) ) {
	    $this->data[$this->row_num]->val = $val;
	}
        return false;
    }
}


?>
