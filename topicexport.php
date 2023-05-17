<?php

$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";
require_once $docroot."/includes/functions_phpdocx.php";
require_once $docroot."/phpdocx/classes/CreateDocx.inc";

// necessary! used in phpdocx
global $thefont, $expfontsize;
$parachoice = 0; // no indent, no justify
$ucaseot = 0;
$thefont='arial';
//$thefont = 'times new roman';
$expfontsize = (($thefont=='arial')?24:28);

$workdir = createworkdir();
$docx = new CreateDocx($docroot.'/includes/REV_styles.docx');
$docx->modifyPageLayout('letter',
                        array('marginTop' => 1080,
                              'marginBottom' => 1080,
                              'marginLeft' => 900,
                              'marginRight' => 900,
                              'numberCols' => 3
                              ));
$numbering = new WordFragment($docx, 'defaultFooter');
$numbering->addPageNumber('page_NumberOfX', $options);
$docx->addFooter(array('default' => $numbering));

$ftitle = 'REV Topic List';

$htm = '<h2 style="margin-top:0;">REV Topic List</h2>';
$sql = 'select topic from topic order by topic ';
$tpx = dbquery($sql);
$lastletter='-';
while($row = mysqli_fetch_array($tpx)){
  $chr = strtoupper(substr($row['topic'], 0, 1));
  if($chr!=$lastletter){
    $htm.= (($chr=='A')?'':'<br />').'<b>'.$chr.'</b><br />';
    $lastletter = $chr;
  }
  $htm.= $row[0].'<br />';
}


loadhtm2($docx, tidify($htm));
$docx->createDocx($docroot.'/export/'.$workdir.$ftitle);
header('Location: /export/'.$workdir.$ftitle.'.docx');


//
//
//
function loadhtm2($docx, $html){
  $docx->embedHTML($html, array('strictWordStyles' => false,
                                'downloadImages' => true)); //,
}

