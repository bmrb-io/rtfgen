<?php
include_once '../../php_includes/Globals.inc';
global $SWLIBDIR;
require_once $SWLIBDIR . "/sans.php";
//require_once 'sans.php';

class StarParser {

    private $ch;
    private $eh;
    private $lex;
    private $blockId;

    function __construct(  STARLexer $lex, ContentHandler $ch, ErrorHandler $eh ) {
	$this->ch = $ch;
	$this->eh = $eh;
	$this->lex = $lex;
    }
    function getContentHandler() {
	return $this->ch;
    }
    function setContentHandler( ContentHandler $ch ) {
	$this->ch = $ch;
    }
    function getErrorHandler() {
	return $this->eh;
    }
    function setErrorHandler( ErrorHandler $eh ) {
	$this->eh = $eh;
    }
    function getScanner() {
	return $this->lex;
    }
    function setScanner( STARLexer $lex ) {
	$this->lex = $lex;
    }
    function parse() {
	if( ! isset( $this ) ) die( "Lexer not initialized\n" );
	if( ! isset( $this->ch ) ) die( "content handler not initialized\n" );
	if( ! isset( $this->eh ) ) die( "error handler not initialized\n" );
	do {
	    $tok = $this->lex->yylex();
	    switch( $tok ) {
		case STARLexer::ERROR :
//echo "parser: crit. error on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), $this->lex->getText() );
		    return;
		case STARLexer::WARNING :
//echo "parser: warning on " . $this->lex->getText() . "\n";
		    if( $this->eh->warning( $this->lex->getLine(), $this->lex->getText() ) )
			return;
		    break;
		case STARLexer::FILEEND :
//echo "parser: eof on " . $this->lex->getText() . "\n";
		    $this->ch->endData( $this->lex->getLine(), $this->blockId );
		    return;
		case STARLexer::COMMENT :
//echo "parser: comment " . $this->lex->getText() . "\n";
		    if( $this->ch->comment( $this->lex->getLine(), $this->lex->getText() ) )
			return;
		    break;
		case STARLexer::DATASTART :
		    $this->blockId = $this->lex->getText();
//echo "parser: start data block " . $this->blockId . "\n";
		    if( $this->ch->startData( $this->lex->getLine(), $this->blockId ) )
			return;
		    if( $this->parseDataBlock() ) return;
		    break;
		case STARLexer::SAVESTART :
		    if( $this->eh->warning( $this->lex->getLine(), "Missing data_ block" ) )
			return;
		    $savename = $this->lex->getText();
//echo "parser(2): start saveframe " . $savename . "\n";
		    if( $this->ch->startSaveframe( $this->lex->getLine(), $savename ) )
			return true;
		    if( $this->parseSaveFrame( $savename ) ) return true;
		    break;
		case STARLexer::LOOPSTART :
		    $this->eh->criticalError( $this->lex->getLine(), "Unexpected \"loop_\" (check \"data loop only\" option on previous page)" );
		    return true;
		default :
//echo "parser: unknown token " . $tok . " on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), "Invalid token: " . $this->lex->getText() );
		    return;
	    }
	} while( $tok != STARLexer::FILEEND );
    }
    function parseDataBlock() {
	if( ! isset( $this ) ) die( "Lexer not initialized\n" );
	if( ! isset( $this->ch ) ) die( "content handler not initialized\n" );
	if( ! isset( $this->eh ) ) die( "error handler not initialized\n" );
	do {
	    $tok = $this->lex->yylex();
//echo "parser(2): token " . $tok . " text " . $this->lex->getText() . "\n";
	    switch( $tok ) {
		case STARLexer::ERROR :
//echo "parser(2): crit. error on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), $this->lex->getText() );
		    return true;
		case STARLexer::WARNING :
//echo "parser(2): warning on " . $this->lex->getText() . "\n";
		    if( $this->eh->warning( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
		case STARLexer::FILEEND :
//echo "parser(2): eof on " . $this->lex->getText() . "\n";
		    $this->ch->endData( $this->lex->getLine(), $this->blockId );
		    return true;
		case STARLexer::COMMENT :
//echo "parser(2): comment " . $this->lex->getText() . "\n";
		    if( $this->ch->comment( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
		case STARLexer::SAVESTART :
		    $savename = $this->lex->getText();
//echo "parser(2): start saveframe " . $savename . "\n";
		    if( $this->ch->startSaveframe( $this->lex->getLine(), $savename ) )
			return true;
		    if( $this->parseSaveFrame( $savename ) ) return true;
		    break;
		default :
//echo "parser(2): unknown token " . $tok . " on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), "Invalid token: " . $this->lex->getText() );
		    return true;
	    }
	} while( $tok != STARLexer::FILEEND );
//	$this->ch->endData( $this->lex->getLine(), $this->blockId );
//	return true;
    }
    function parseSaveFrame( $name ) {
	if( ! isset( $this ) ) die( "Lexer not initialized\n" );
	if( ! isset( $this->ch ) ) die( "content handler not initialized\n" );
	if( ! isset( $this->eh ) ) die( "error handler not initialized\n" );
	$tag = "";
	$tagline = -1;
	$val = "";
	$needvalue = false;
	do {
	    $tok = $this->lex->yylex();
	    switch( $tok ) {
		case STARLexer::ERROR :
//echo "parser(3): crit. error on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), $this->lex->getText() );
		    return true;
		case STARLexer::WARNING :
//echo "parser(3): warning on " . $this->lex->getText() . "\n";
		    if( $this->eh->warning( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
		case STARLexer::FILEEND :
//echo "parser(3): eof on " . $this->lex->getText() . "\n";
		    $this->eh->error( $this->lex->getLine(), "Premature end of file (no closing save_)" );
		    $this->ch->endData( $this->lex->getLine(), $this->blockId );
		    return true;
		case STARLexer::COMMENT :
//echo "parser(3): comment " . $this->lex->getText() . "\n";
		    if( $this->ch->comment( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
// exit point
		case STARLexer::SAVEEND :
//echo "parser(3): end saveframe " . $name . "\n";
		    if( $needvalue ) {
			if( $this->eh->error( $this->lex->getLine(), "Value expected, found save_" ) )
			    return true;
		    }
		    return $this->ch->endSaveframe( $this->lex->getLine(), $name );
		case STARLexer::LOOPSTART :
//echo "parser(3): start loop\n";
		    if( $needvalue ) {
			if( $this->eh->error( $this->lex->getLine(), "Value expected, found loop_" ) )
			    return true;
		    }
		    if( $this->ch->startLoop( $this->lex->getLine() ) )
			return true;
		    if( $this->parseLoop() ) return true;
		    break;
		case STARLexer::TAGNAME :
//echo "parser(3): tag " . $this->lex->getText() . "\n";
		    if( $needvalue ) {
			if( $this->eh->error( $this->lex->getLine(), "Value expected, found " . $this->lex->getText() ) )
			    return true;
		    }
		    $tag = $this->lex->getText();
		    $tagline = $this->lex->getLine();
		    $needvalue = true;
		    break;
		case STARLexer::DVNSINGLE :
		case STARLexer::DVNDOUBLE :
		case STARLexer::DVNSEMICOLON :
		case STARLexer::DVNFRAMECODE :
		case STARLexer::DVNNON :		
//echo "parser(3): value " . $this->lex->getText() . "\n";
		    if( ! $needvalue ) {
			if( $this->eh->error( $this->lex->getLine(), "Value not expected: " . $this->lex->getText() ) )
			    return true;
		    }
		    $needvalue = false;
		    $val = $this->lex->getText();
		    if( $tok == STARLexer::DVNSEMICOLON ) {
			if( preg_match( "/^\n/", $val ) ) {
//echo "PARSER: replacing |" . $val . "|\n";
			    $val = preg_replace( "/^\n/", "", $val );
//echo "PARSER: NOW |" . $val . "|\n";
			}
		    }
		    if( $this->ch->data( $tag, $tagline, $val, $this->lex->getLine(), $tok, false ) )
			return true;
		    $tag = "";
		    $tagline = -1;
		    $val = "";
		    break;
		default :
//echo "parser(3): unknown token " . $tok . " on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), "Invalid token: " . $this->lex->getText() );
		    return true;
	    }
	} while( $tok != STARLexer::FILEEND );
    }
    function parseLoop() {
	if( ! isset( $this ) ) die( "Lexer not initialized\n" );
	if( ! isset( $this->ch ) ) die( "content handler not initialized\n" );
	if( ! isset( $this->eh ) ) die( "error handler not initialized\n" );
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
		    $this->eh->criticalError( $this->lex->getLine(), $this->lex->getText() );
		    return true;
		case STARLexer::WARNING :
//echo "parser(4): warning on " . $this->lex->getText() . "\n";
		    if( $this->eh->warning( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
		case STARLexer::FILEEND :
//echo "parser(4): eof on " . $this->lex->getText() . "\n";
		    $this->eh->error( $this->lex->getLine(), "Premature end of file (no closing stop_)" );
		    $this->ch->endData( $this->lex->getLine(), $this->blockId );
		    return true;
		case STARLexer::COMMENT :
//echo "parser(4): comment " . $this->lex->getText() . "\n";
		    if( $this->ch->comment( $this->lex->getLine(), $this->lex->getText() ) )
			return true;
		    break;
// exit point
		case STARLexer::STOP :
//echo "parser(4): end loop\n";
		    if( count( $tags ) < 1 ) {
			if( $this->eh->error( $this->lex->getLine(), "Loop with no tags" ) )
			    return true;
		    }
		    if( $numvals < 1 ) {
			if( $this->eh->error( $this->lex->getLine(), "Loop with no values" ) )
			    return true;
		    }
		    $rc = false;
		    if( ($numvals % count( $tags )) != 0 ) {
			if( $wrongline < 0 ) $wrongline = $this->lex->getLine();
			$rc = $this->eh->warning( $wrongline, "Loop count error" );
		    }
		    $rc = $rc || $this->ch->endLoop( $this->lex->getLine() );
		    return $rc;
		case STARLexer::TAGNAME :
//echo "parser(4): tag " . $this->lex->getText() . "\n";
		    if( ! $parsingtags ) {
			if( $this->eh->error( $this->lex->getLine(), "Value expected, found " . $this->lex->getText() ) )
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
		    if( $this->ch->data( $tag, $tagline, $val, $this->lex->getLine(), $tok, true ) )
			return true;
		    $val = "";
		    $tag = "";
		    $tagline = -1;
		    break;
		default :
//echo "parser(4): unknown token " . $tok . " on " . $this->lex->getText() . "\n";
		    $this->eh->criticalError( $this->lex->getLine(), "Invalid token: " . $this->lex->getText() );
		    return true;
	    }
	} while( $tok != STARLexer::FILEEND );
    }
}

?>