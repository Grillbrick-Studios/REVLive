<?php
header('Content-Type:text/html; charset=utf-8');
ini_set('memory_limit','768M');     // required for Psalms
ini_set('max_execution_time', 480); //480 seconds = 8 minutes

$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";
require_once $docroot."/includes/functions_phpdocx.php";
require_once $docroot."/phpdocx/classes/CreateDocx.inc";

$what = ((isset($_GET['what']))?$_GET['what']:'bible');

$test = ((isset($_GET['test']))?$_GET['test']:1);
$book = ((isset($_GET['book']))?$_GET['book']:40);
$chap = ((isset($_GET['chap']))?$_GET['chap']:1);
$vers = ((isset($_GET['vers']))?$_GET['vers']:0);
$pdf =  ((isset($_GET['pdf']))?$_GET['pdf']:0);

//$vers = '1 unhex';

$injection = 0;
if($what!='bible' && $what!='bcom' && $what!='vers' && $what!='appx' && $what!='info' && $what!='word' && $what!='comm' && $what!='myrev' && $what!='outl' && $what!='bib') $injection=1;
if($what=='myrev'){
  $dat = ((isset($_GET['dat']))?$_GET['dat']:'none');
  if($dat=='none') exit('no data');
  $ardat = explode('~', $dat);
  // doing this just to pass the test
  $ardat2= explode('|', $ardat[0]);
  $test = ((isset($ardat2[1]))?$ardat2[1]:'x');
  $book = ((isset($ardat2[2]))?$ardat2[2]:'x');
  $chap = ((isset($ardat2[3]))?$ardat2[3]:'x');
  $vers = ((isset($ardat2[4]))?$ardat2[4]:'x');
}
if(preg_match('#\D#', $test) || preg_match('#\D#', $book) || preg_match('#\D#', $chap) || preg_match('#\D#', $vers) || preg_match('#\D#', $pdf)) $injection=1;

$pdf =  (int) preg_replace('/\D/', '', $pdf);

if($injection==1){
  logview(452,0,0,0,0,'<span style="color:red">MSW injection attempt</span>');
  if($autoblock==1) $block = dbquery('insert into blockedips (ipaddress, hitcount, lasthit, reason, comment) values (\''.$ip.'\', 1, UTC_TIMESTAMP(), 1, \'autoblocked (MSW)\')');
  exit(0);
}

$test = (int) preg_replace('/\D/', '', $test);
$book = (int) preg_replace('/\D/', '', $book);
$chap = (int) preg_replace('/\D/', '', $chap);
$vers = (int) preg_replace('/\D/', '', $vers);

global $thefont, $expfontsize;

$thefont='arial';
if($fontfamily=='merriweather' || $fontfamily=='times new roman' || $fontfamily=='caladea' || $fontfamily=='ibm plex serif')
  $thefont = 'times new roman';

// export prefs are in their own cookie
$expprefs   = explode(';', (isset($_COOKIE['rev_expprefs']))?$_COOKIE['rev_expprefs']:'2;1'); // medium font, no gutter
$expfontsize   = $expprefs[0];
$expmargintype = $expprefs[1];  // no need to handle, do it from within MSW

switch($expfontsize){
  case 1: // small
    $expfontsize = (($thefont=='arial')?20:24);
    break;
  case 3: // large
    $expfontsize = (($thefont=='arial')?28:32);
    break;
  default: // medium
    $expfontsize = (($thefont=='arial')?24:28);
    break;
}

$docx = new CreateDocx($docroot.'/includes/REV_styles.docx');

// this is for transforming a docx to pdf using phpdocx
if($pdf==1){
  $docx->enableCompatibilityMode();
}

$docx->modifyPageLayout('letter',
                        array('marginTop' => 1080,
                              'marginBottom' => 1080,
                              'marginLeft' => 900,
                              'marginRight' => 900,
                              'numberCols' => (($what=='bible')?(($viewcols>1)?2:1):1)
                              ));
// messing with page numbers
$options = array(
    'textAlign' => 'center',
    'pStyle' => 'rFooter'
);
$numbering = new WordFragment($docx, 'defaultFooter');
//$numbering->addPageNumber('page_Number', $options);
$numbering->addPageNumber('page_NumberOfX', $options);
$docx->addFooter(array('default' => $numbering));

$html = '';
$htmlprinted = 0;

if($what=='bib'){
  $bibtype = $test;
  $ftitle = 'REV_'.(($bibtype==0)?'Abbreviations':'Bibliography');
  $html = getbibcontent($bibtype);
  //logview((($pdf==1)?99:94),$test,$book,$chap,$vers,$ftitle);
}

if($what=='myrev'){
  $ftitle = 'REV_Notes';
  $arnotes = explode('~', $dat);
  switch(count($arnotes)){
  case 1: // general notes
    $html = getmyrevnote($arnotes[0]);
    break;
  default:
    $sort=(int) ((isset($_REQUEST['sort']))?$_REQUEST['sort']:0);
    //$sort=(($sort==1)?'highlight, ':(($sort==2)?'sqn, ':''));
    $sort=(($sort==1)?'highlight, ':(($sort==2)?'sqn, ':(($sort==3)?'lastupdate desc, ':'')));
    $html = '<h3>My REV Notes</h3>';
    loadhtm($docx, tidify($html));
    if($arnotes[1]=='all'){
      $getmyrevid = explode('|', $arnotes[0]);
      $myrevid = $getmyrevid[0];
      $dat = dbquery('select myrevid, testament, book, chapter, verse
                      from myrevdata
                      where myrevid = '.$myrevid.'
                      order by '.$sort.'testament, book, chapter, verse');
      while($row=mysqli_fetch_array($dat)) {
        $loc = $row[0].'|'.$row[1].'|'.$$row[2].'|'.$row[3].'|'.$row[4];
        $html = getmyrevnote($loc);
        loadhtm($docx, tidify($html));
      }

    }else{
      for($ni=1;$ni<count($arnotes);$ni++){
        $html = getmyrevnote($arnotes[$ni]);
        //print($arnotes[$ni].'<br />');
        loadhtm($docx, tidify($html));
      }
    }
    //die();
    $htmlprinted = 1;
    break;
  }
}

if($what=='bible'){
  $ftitle = 'REV_'.getbooktitle($test,$book, 1).(($chap==0)?'':'_ch'.$chap);
  if($chap==0){
    $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
    $chaps = $row[0];
    for($ni=1;$ni<=$chaps;$ni++){
      $chap = $ni;
      $html = getbible(0);
      loadhtm($docx, tidify($html));
    }
    $htmlprinted = 1;
    logview((($pdf==1)?97:92),$test,$book,0,$vers,$ftitle);
  }else{
    $html = getbible(1);
    logview((($pdf==1)?97:92),$test,$book,$chap,$vers,$ftitle);
  }
}

if($what=='vers'){
  $ftitle = 'REV_Commentary_'.getbooktitle($test,$book, 1).'_ch'.$chap.'_v'.$vers;
  $html = getverscomm();
  logview((($pdf==1)?99:94),$test,$book,$chap,$vers,$ftitle);
}

if($what=='appx' || $what=='info' || $what=='word'){
  $sql = 'select ifnull(tagline,title) from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $ftitle = 'REV_'.cleanquotes($row[0]);
  $html = getappxintro();
  logview((($pdf==1)?96:91),$test,$book,$chap,$vers,$ftitle);
}

if($what=='outl'){
  $sql = 'select ifnull(tagline,title) from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $ftitle = 'REV_Outline_'.cleanquotes($row[0]);
  $html = getoutline();
  logview((($pdf==1)?96:91),$test,$book,$chap,$vers,$ftitle);
}

if($what=='comm'){
  $ftitle = 'REV_Commentary_'.getbooktitle($test,$book, 1).(($chap==0)?'':'_ch'.$chap);
  $html = '<h2>REV Commentary</h2>';
  if($chap==0){
    $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
    $chaps = $row[0];
    for($ni=1;$ni<=$chaps;$ni++){
      $chap = $ni;
      $html .= getcommentary($pdf);
      loadhtm($docx, tidify($html));
      $html = '<p>&nbsp;</p>';
    }
    $htmlprinted = 1;
    logview((($pdf==1)?98:93),$test,$book,0,$vers,$ftitle);
  }else{
    $html .= getcommentary($pdf);
    logview((($pdf==1)?98:93),$test,$book,$chap,$vers,$ftitle);
  }
}

if($what=='bcom'){
  $ftitle = 'REV_Book_Commentary_'.getbooktitle($test,$book, 1);
  $html = getbookcomm();
  logview((($pdf==1)?95:90),$test,$book,$chap,$vers,$ftitle);
}

if($html=='' && $htmlprinted == 0){
  $ftitle = 'error';
  $html = 'ERROR: no content';
}
mysqli_close($db);

//die(tidify($html));
if($htmlprinted==0) loadhtm($docx, tidify($html));

$properties = array(
    'title' => $ftitle,
    //'subject' => 'My subject',
    'creator' => 'John Schoenheit',
    'dateCreated' => '12/12/2015',
    'date' => '12/12/2015',
    //'Author' => 'John Schoenheit',
    //'lastModifiedBy' => 'John Schoenheit',
    //'keywords' => 'keyword 1, keyword 2, keyword 3',
    'description' => 'Revised English Version',
    //'category' => 'My category',
    //'contentStatus' => 'Draft',
    //'Manager' => 'John Schoenheit',
    //'Company' => 'Spirit & Truth' //,
    //'custom' => array(
    //    'My custom text' => array('text' => 'This is a reasonably large text'),
    //    'My custom number' => array('number' => '4567'),
    //    'My custom date' => array('date' => '1962-01-27T23:00:00Z'),
    //    'My custom boolean' => array('boolean' => true)
    //    )
);
$docx->addProperties($properties);

$ftitle = cleanquotes($ftitle);
$ftitle = replacediacritics($ftitle);
$ftitle = str_replace(': ','_',$ftitle);
$ftitle = str_replace('; ','_', $ftitle);
$ftitle = str_replace(':','',str_replace(' ','_',$ftitle));
$ftitle = str_replace('?', '', $ftitle);
$ftitle = str_replace('/', '_', $ftitle);
$workdir = createworkdir();

// this does NOT work on the live site
//$docx->createDocxAndDownload($docroot.'/export/'.$ftitle);
$docx->createDocx($docroot.'/export/'.$workdir.$ftitle);

if($pdf==1){
  $docx->transformDocument($docroot.'/export/'.$workdir.$ftitle.'.docx', $docroot.'/export/'.$workdir.$ftitle.'.pdf');
  header('Location: /export/'.$workdir.$ftitle.'.pdf');
}else{
  // working, do not delete
  header('Location: /export/'.$workdir.$ftitle.'.docx');

  // trying to get Apple iOS to save the file and not open it
  // The file downloads, but the user cannot get back to the REV
  //$file = $docroot.'/export/'.$workdir.$ftitle.'.docx';
  //header("Content-Description: File Transfer");
  //header("Content-Type: application/octet-stream");
  //header("Content-Disposition: attachment; filename=\"". $ftitle.".docx" ."\"");
  //readfile ($file);
  //exit();
}
?>

