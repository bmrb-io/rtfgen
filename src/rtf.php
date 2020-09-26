<?php
// file header
    define( "RTF_FILEHEADER", "{\\rtf1\\ansi\\ansicpg1252\\uc1 \\deff0\\deflang1033\\deflangfe1033{\\fonttbl{\\f0\\froman\\fcharset0\\fprq2
{\\*\\panose 02020603050405020304}Times New Roman;}{\\f3\\froman\\fcharset2\\fprq2{\\*\\panose 05050102010706020507}Symbol;}}
{\\colortbl;\\red0\\green0\\blue0;\\red0\\green0\\blue255;\\red0\\green255\\blue255;\\red0\\green255\\blue0;
\\red255\\green0\\blue255;\\red255\\green0\\blue0;\\red255\\green255\\blue0;\\red255\\green255\\blue255;\\red0\\green0\\blue128;
\\red0\\green128\\blue128;\\red0\\green128\\blue0;\\red128\\green0\\blue128;\\red128\\green0\\blue0;\\red128\\green128\\blue0;
\\red128\\green128\\blue128;\\red192\\green192\\blue192;}{\\stylesheet{\\widctlpar\\adjustright \\fs20\\cgrid \\snext0 Normal;}
{\\*\\cs10 \\additive Default Paragraph Font;}}{\\info{\\title Table : Resonance Assignments;}{\\author 4th1}{\\operator 4th1}
{\\creatim\\yr1998\\mo10\\dy24\\hr13\\min18}{\\revtim\\yr1998\\mo10\\dy24\\hr13\\min18}{\\version2}{\\edmins0}{\\nofpages2}{\\nofwords797}
 {\\nofchars4545}{\\*\\company BioMagResBank,UW-Madison}{\\nofcharsws5581}{\\vern89}}\\widowctrl\\ftnbj\\aenddoc\\hyphcaps0\\viewkind1\\viewscale75
\\fet0\\sectd \\linex0\\endnhere\\sectdefaultcl {\\*\\pnseclvl1\\pnucrm\\pnstart1\\pnindent720\\pnhang{\\pntxta .}}
{\\*\\pnseclvl2\\pnucltr\\pnstart1\\pnindent720\\pnhang{\\pntxta.}}{\\*\\pnseclvl3\\pndec\\pnstart1\\pnindent720\\pnhang{\\pntxta .}}
{\\*\\pnseclvl4\\pnlcltr\\pnstart1\\pnindent720\\pnhang{\\pntxta )}}{\\*\\pnseclvl5\\pndec\\pnstart1\\pnindent720\\pnhang{\\pntxtb (}
{\\pntxta )}}{\\*\\pnseclvl6\\pnlcltr\\pnstart1\\pnindent720\\pnhang{\\pntxtb (}{\\pntxta )}}{\\*\\pnseclvl7\\pnlcrm\\pnstart1\\pnindent720
\\pnhang{\\pntxtb (}{\\pntxta )}}{\\*\\pnseclvl8\\pnlcltr\\pnstart1\\pnindent720\\pnhang{\\pntxtb (}{\\pntxta )}}
{\\*\\pnseclvl9\\pnlcrm\\pnstart1\\pnindent720\\pnhang{\\pntxtb (}{\\pntxta )}}" );
// table header
    define( "RTF_TBLTITLE_START", "\\pard\\plain\\nowidctlpar\\widctlpar\\adjustright\\fs20\\cgrid{\\fs16 Table " );
    define( "RTF_TBLTITLE_END", " (proton chemical shifts are given in parentheses). \\par}" );
    define( "RTF_TBLHEADER", "\\trowd \\trgaph108\\trleft-108\\trbrdrt\\brdrs\\brdrw30\\brdrcf11 \\trbrdrb\\brdrs\\brdrw30\\brdrcf11
\\clvertalt\\clbrdrt\\brdrs\\brdrw30\\brdrcf11 \\clbrdrb\\brdrs\\brdrw15\\brdrcf11 \\cltxlrtb \\cellx572
\\clvertalt\\clbrdrt\\brdrs\\brdrw30\\brdrcf11 \\clbrdrb\\brdrs\\brdrw15\\brdrcf11 \\cltxlrtb \\cellx1890\\clvertalt\\clbrdrt\\brdrs\\brdrw30\\brdrcf11
\\clbrdrb\\brdrs\\brdrw15\\brdrcf11 \\cltxlrtb \\cellx2610 \\clvertalt\\clbrdrt\\brdrs\\brdrw30\\brdrcf11 \\clbrdrb\\brdrs\\brdrw15\\brdrcf11
\\cltxlrtb \\cellx4410 \\clvertalt\\clbrdrt\\brdrs\\brdrw30\\brdrcf11 \\clbrdrb\\brdrs\\brdrw15\\brdrcf11 \\cltxlrtb \\cellx6210
\\clvertalt\\clbrdrt\\brdrs\\brdrw30\\brdrcf11 \\clbrdrb\\brdrs\\brdrw15\\brdrcf11 \\cltxlrtb \\cellx10080\\pard
\\nowidctlpar\\widctlpar\\intbl\\adjustright {\\fs16 residue\\cell N\\cell C\\cell C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 97
\\\\f \"Symbol\" \\\\s 8} {\\fldrslt\\f3\\fs16}}}{\\fs16 \\cell C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 98 \\\\f \"Symbol\"
\\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16 \\cell other\\cell }\\pard \\nowidctlpar\\widctlpar\\intbl\\adjustright {\\fs16 \\row }" );
// row header
/*    define( "RTF_ROWHEADER", "\\trowd \\trgaph108\\trleft-108\\trbrdrt\\brdrs\\brdrw30\\brdrcf11 
\\trbrdrb\\brdrs\\brdrw30\\brdrcf11\\clvertalt\\cltxlrtb \\cellx572\\clvertalt\\cltxlrtb 
\\cellx1890\\clvertalt\\cltxlrtb \\cellx2610\\clvertalt\\cltxlrtb \\cellx4410\\clvertalt\\cltxlrtb 
\\cellx6210\\clvertalt\\cltxlrtb \\cellx10080\\pard \\nowidctlpar\\widctlpar\\intbl\\adjustright " );
*/
    define( "RTF_ROWHEADER", "\\trowd\\trautofit1 \\intbl\\adjustright
\\cellx572\\clvertalt\\cltxlrtb 
\\cellx1890\\clvertalt\\cltxlrtb
\\cellx2610\\clvertalt\\cltxlrtb
\\cellx4410\\clvertalt\\cltxlrtb 
\\cellx6210\\clvertalt\\cltxlrtb
\\cellx10080\\pard " );
// row end
    define( "RTF_ROWTAIL", "\\pard \\nowidctlpar\\widctlpar\\intbl\\adjustright {\\fs16 \\row }" );
   /** empty row */
    define( "RTF_EMPTYROW", "\\trowd \\trgaph108\\trleft-108\\trbrdrt\\brdrs\\brdrw30\\brdrcf11
 \\trbrdrb\\brdrs\\brdrw30\\brdrcf11\\clvertalt\\cltxlrtb \\cellx572\\clvertalt\\cltxlrtb \\cellx1890\\clvertalt\\cltxlrtb
 \\cellx2610\\clvertalt\\cltxlrtb \\cellx4410\\clvertalt\\cltxlrtb \\cellx6210\\clvertalt\\cltxlrtb \\cellx10080\\pard
 \\nowidctlpar\\widctlpar\\intbl\\adjustright {\\fs16 \\cell   \\cell \\cell  \\cell  \\cell\\cell } \\pard
 \\nowidctlpar\\widctlpar\\intbl\\adjustright {\\fs16 \\row }" );
            

// atoms
    define( "RTF_CG", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 103 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_CG1", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 103 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_CG2", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 103 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_CD", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 100 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_CD1", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 100 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_CD2", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 100 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_CE", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_CE1", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_CE2", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_CE3", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 3}" );
    define( "RTF_CH2", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 104 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_CZ", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 122 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_CZ2", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 122 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_CZ3", " C}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 122 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 3}" );
    define( "RTF_ND1", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 100 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_ND2", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 100 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_NE", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_NE1", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_NE2", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_NH1", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 104 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_NH2", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 104 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_NZ", " N}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 122 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_OD2", " O}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 100 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_OE2", " O}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 101 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 2}" );
    define( "RTF_OG1", " O}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 103 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}{\\fs16\\super 1}" );
    define( "RTF_OH", " O}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 104 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );
    define( "RTF_SG", " S}{\\fs16\\super {\\field{\\*\\fldinst SYMBOL 103 \\\\f \"Symbol\" \\\\s 8}{\\fldrslt\\f3\\fs16}}}" );

?>
