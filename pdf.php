<?php
//header("Content-type:application/pdf");
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";
require_once $docroot."/mpdf/vendor/autoload.php";
ini_set('max_execution_time', 480); //480 seconds = 8 minutes

//$fontfam='arial';
$fontfam='FreeSans';
if($fontfamily=='merriweather' || $fontfamily=='times new roman' || $fontfamily=='caladea' || $fontfamily=='ibm plex serif')
  $fontfam = 'FreeSerif';

$fontsize = (($fontfam==='FreeSerif')?14:13); // arial prints too big...

$arfn = array();
$arcomfn = array();

$what = ((isset($_GET['what']))?$_GET['what']:'bible');

$test = ((isset($_GET['test']))?$_GET['test']:1);
$book = ((isset($_GET['book']))?$_GET['book']:40);
$chap = ((isset($_GET['chap']))?$_GET['chap']:1);
$vers = ((isset($_GET['vers']))?$_GET['vers']:0);

//$vers = '1 \'unhex';

$injection = 0;
if($what!='bible' && $what!='bcom' && $what!='vers' && $what!='appx' && $what!='info' && $what!='word' && $what!='comm' && $what!='notes' && $what!='myrev' && $what!='outl' && $what!='bib') $injection=1;
if($what=='myrev'){
  $dat = ((isset($_GET['dat']))?$_GET['dat']:'none');
  if($dat=='none') exit('no data');
  $ardat = explode('~', $dat);
  $ardat2= explode('|', $ardat[0]);
  $test = ((isset($ardat2[1]))?$ardat2[1]:'x');
  $book = ((isset($ardat2[2]))?$ardat2[2]:'x');
  $chap = ((isset($ardat2[3]))?$ardat2[3]:'x');
  $vers = ((isset($ardat2[4]))?$ardat2[4]:'x');
}
//print('test: '.$test.'<br />');
//print('book: '.$book.'<br />');
//print('chap: '.$chap.'<br />');
//print('vers: '.$vers.'<br />');
//die();
if(preg_match('#\D#', $test) || preg_match('#\D#', $book) || preg_match('#\D#', $chap) || preg_match('#\D#', $vers)) $injection=1;

if($injection==1){
  logview(452,0,0,0,0,'<span style="color:red">PDF injection attempt</span>');
  if($autoblock==1) $block = dbquery('insert into blockedips (ipaddress, hitcount, lasthit, reason, comment) values (\''.$ip.'\', 1, UTC_TIMESTAMP(), 1, \'autoblocked (PDF)\')');
  exit(0);
}

$test = (int) preg_replace('/\D/', '', $test);
$book = (int) preg_replace('/\D/', '', $book);
$chap = (int) preg_replace('/\D/', '', $chap);
$vers = (int) preg_replace('/\D/', '', $vers);

// export prefs are in their own cookie
$expprefs   = explode(';', (isset($_COOKIE['rev_expprefs']))?$_COOKIE['rev_expprefs']:'2;1'); // medium font, no gutter
$pdffontsize   = $expprefs[0];
$pdfmargintype = $expprefs[1];
$pdfalignment  = (($parachoice==2 || $parachoice==4)?2:1);  // 2=justify, 1=left
$pdftextindent = (($parachoice==3 || $parachoice==4)?1:0);

switch($pdffontsize){
  case 1:
    $fontsize = $fontsize*.8;
    break;
  case 2:
    break;
  case 3:
    $fontsize = $fontsize*1.2;
    break;
}

switch($pdfmargintype){
  case 1: // even
    $leftmargin = 15;
    $rightmargin = 15;
    $mirrormargins = false;
    break;
  case 2: // gutter, single
    $leftmargin = 35;
    $rightmargin = 15;
    $mirrormargins = false;
    break;
  case 3: // gutter, double
    $leftmargin = 35;
    $rightmargin = 15;
    $mirrormargins = true;
    break;
}
$topmargin = 20;
$bottommargin = 20;

//echo exec('whoami');

// create new PDF document
if($site=='www.revisedenglishversion.com' || $site=='www.revdevbible.com' || $site=='www.revdevbible2.com')
$pdf = new \Mpdf\Mpdf([
                      'mode'=>'utf-8',
                      'format' => 'Letter',
                      'margin_top' => $topmargin,
                      'margin_left' => $leftmargin,
                      'margin_right' => $rightmargin,
                      'margin_bottom' => $bottommargin
                      ]);
else
// someting is wrong...
// for some reason the default tmp dir is not writeable on the dev site.
$pdf = new \Mpdf\Mpdf([
                      'mode'=>'utf-8',
                      'format' => 'Letter',
                      'margin_top' => $topmargin,
                      'margin_left' => $leftmargin,
                      'margin_right' => $rightmargin,
                      'margin_bottom' => $bottommargin,
                      'tempDir' => $docroot.'/temp'
                      ]);

$pdf->useKerning = false;
$pdf->mirrorMargins = $mirrormargins;
$pdf->SHYleftmin = 2;    // for auto-hyphen
$pdf->SHYrightmin = 2;
$pdf->ignore_invalid_utf8 = true;
$pdf->use_kwt = true;
$pdf->SetHTMLFooter('<div style="text-align:center;margin:0;padding:0;"><span style="color:#aaaaaa;font-size:12px;font-style:italic;">Page {PAGENO} of {nb}</span></div>');

$style = 'body, td, .bq{font-family:"'.$fontfam.'"; font-size: '.$fontsize.'pt;font-kerning:none;} /*hyphens:auto;}*/
          .rtl {direction: rtl;text-align: right;}
          div.bq{display:block;margin:0 .5em 0 2em;padding:0;text-align:'.(($pdfalignment==1)?'left':'justify').'}
          .vh, .vhmicro{font-weight:bold;font-style:italic;margin-bottom:0;text-indent:0}
          .vhmicro{font-weight:normal;font-size:70%;color:#999999}
          strong{font-size:94%;}
          .sup{color:#999999;}
          '.(($versebreak==0 || $page>0)
          ?'p{margin-top:1em;margin-bottom:1em;text-align:'.(($pdfalignment==1)?'left':'justify').';text-indent:'.(($pdftextindent==1)?'1.3em':'0').';font-kerning:none;}'
          :'p{margin-bottom:5px;text-align:'.(($pdfalignment==1)?'left':'justify').';text-indent:0;font-kerning:none;}').'
          .rNotInText{color:#aaaaaa;}
          p.hp{text-indent:-1.8em;margin:0 0 0 1.8em;padding:0;}
          .tdvnum{width:17px;vertical-align:top;text-align:right;}
          .tdv{text-indent:-2.8em;margin:0 0 0 2.8em;padding:0;}
          .tdvl{text-indent:0;margin:0 0 0 0;padding:0;}
          table, tr, td{page-break-inside:auto;}
          h5{font-size:1em;line-height:inherit;margin-bottom:3px;padding-bottom:0;}
          ';

$pdf->WriteHTML($style, 1);

$html = '';
$htmlwritten = 0;

if($what=='myrev'){
  $ftitle = 'REV_Notes';
  $arnotes = explode('~', $dat);
  switch(count($arnotes)){
  case 1: // general notes
    $html = getmyrevnote($arnotes[0]);
    break;
  default:
    $sort=(int) ((isset($_REQUEST['sort']))?$_REQUEST['sort']:0);
    $sort=(($sort==1)?'highlight, ':(($sort==2)?'sqn, ':(($sort==3)?'lastupdate desc, ':'')));
    $html = '<h3>My REV Notes</h3>';
    $pdf->WriteHTML(tidy($html));
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
        $pdf->WriteHTML(tidy($html));
      }

    }else{
      for($ni=1;$ni<count($arnotes);$ni++){
        $html = getmyrevnote($arnotes[$ni]);
        $pdf->WriteHTML(tidy($html));
      }
    }
    //die();
    $htmlwritten = 1;
    break;
  }
}

if($what=='bible'){
  $ftitle = 'REV_'.getbooktitle($test,$book, 1).(($chap==0)?'':'_ch'.$chap);
  //$pdf->SetColumns(2, 0, 12);
  if($viewcols>1) $pdf->SetColumns(2, 0, 12);
  $pdf->keepColumns = true;
  $pdf->simpleTables = true;
  //$pdf->tableMinSizePriority = true;
  if($chap==0){
    $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
    $chaps = $row[0];
    for($ni=1;$ni<=$chaps;$ni++){
      $chap = $ni;
      $html = getbible(0);//.'&nbsp;<br />';
      //if($ni<$chaps) $html.='<hr />';
      $pdf->WriteHTML(tidy($html));
    }
    $htmlwritten = 1;
    logview(97,$test,$book,0,$vers,$ftitle);
  }else{
    $html = getbible(1);
    logview(97,$test,$book,$chap,$vers,$ftitle);
  }
}

if($what=='bib'){
  $bibtype = $test;
  $ftitle = 'REV_'.(($bibtype==0)?'Abbreviations':'Bibliography');
  $html = getbibcontent($bibtype);
  //logview((($pdf==1)?99:94),$test,$book,$chap,$vers,$ftitle);
}

if($what=='vers'){
  $ftitle = 'REV_Commentary_'.getbooktitle($test,$book, 1).'_ch'.$chap.'_v'.$vers;
  $html = getverscomm();
  logview(99,$test,$book,$chap,$vers,$ftitle);
}

if($what=='appx' || $what=='info' || $what=='word'){
  $sql = 'select ifnull(tagline,title) from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $ftitle = 'REV_'.$row[0];
  $html = getappxintro();
  logview(96,$test,$book,$chap,$vers,$ftitle);
}

if($what=='outl'){
  $sql = 'select ifnull(tagline,title) from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $ftitle = 'REV_Outline_'.cleanquotes($row[0]);
  $html = getoutline();
  logview(96,$test,$book,$chap,$vers,$ftitle);
}

if($what=='comm'){
  $headingprinted = 0;
  $ftitle = 'REV_Commentary_'.getbooktitle($test,$book, 1).(($chap==0)?'':'_ch'.$chap);
  if($chap==0){
    $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
    $chaps = $row[0];
    for($ni=1;$ni<=$chaps;$ni++){
      $chap = $ni;
      $html = getcommentary();
      $pdf->writeHTML(tidy($html));
    }
    $htmlwritten = 1;
    logview(98,$test,$book,0,$vers,$ftitle);
  }else{
    $html = getcommentary();
    logview(98,$test,$book,$chap,$vers,$ftitle);
  }
}

if($what=='bcom'){
  $ftitle = 'REV_Book_Commentary_'.getbooktitle($test,$book, 1);
  $html = getbookcomm();
  logview(95,$test,$book,$chap,$vers,$ftitle);
}

if($html=='' && (!$htmlwritten)){
  $ftitle = 'error';
  $html = 'ERROR: no content';
}

if($htmlwritten==0) $pdf->WriteHTML(tidy($html));

/* test
print('<html><head><title>test</title><style>'.$style.'</style></head><body>'.tidy($html).'</body></html>');
die();
//*/

$ftitle = cleanquotes($ftitle);
$ftitle = replacediacritics($ftitle);
$ftitle = str_replace(': ','_',$ftitle);
$ftitle = str_replace('; ','_',$ftitle);
$ftitle = str_replace(':','',str_replace(' ','_',$ftitle));
$ftitle = str_replace('?', '', $ftitle);
$ftitle = str_replace('/', '_', $ftitle);
$workdir = createworkdir();

$filespec = $docroot.'/export/'.$workdir.$ftitle.'.pdf';
$pdf->Output($filespec, 'F');
header('Location: /export/'.$workdir.$ftitle.'.pdf');

mysqli_close($db);

//
//
//
function getbibcontent($bibtype){
  $ret='<h2 style="text-align:center;">'.(($bibtype==0)?'Abbreviations':'Bibliography').'</h2><ul>';
  $sql = 'select bibentry from bibliography where bibtype = '.$bibtype.' order by bibauthor ';
  $bib = dbquery($sql);
  while($row = mysqli_fetch_array($bib)){
    $ret.= '<li>'.str_replace('[longdash]','&mdash;&mdash;&mdash;', $row[0]).'</li>';
  }
  $ret.= '</ul>';
  return $ret;
}

function getmyrevnote($dat){
  $ret='';
  $dat = ((isset($dat))?$dat:'-1|0|0|0|-1');
  $ardat = explode('|', $dat);
  $redr = ((isset($ardat[0]))?$ardat[0]:-1);
  $test = ((isset($ardat[1]))?$ardat[1]:-1);
  $book = ((isset($ardat[2]))?$ardat[2]:-1);
  $chap = ((isset($ardat[3]))?$ardat[3]:-1);
  $vers = ((isset($ardat[4]))?$ardat[4]:-1);
  if($redr==-1 || $test==-1 || $book==-1 || $chap==-1 || $vers==-1) die('bad data');
  $row = rs('select myrevname from myrevusers where myrevid = '.$redr.' ');
  $redrnam = $row[0];
  if($chap==0 && $vers==0){
    $stitle = $redrnam.'&rsquo;s General Notes';
    $sql = 'select notes from myrevusers where myrevid = '.$redr.' ';
    $row = rs($sql);
    $mynotes = $row[0];
    $hlite = 0;
    $verse = '';
  }else{
    $btitle = getbooktitle($test,$book, (($test<2)?1:0));
    $stitle = $redrnam.'&rsquo;s Notes on '.$btitle.(($test<2)?' '.$chap.':'.$vers:'');
    $sql = 'select ifnull(rd.myrevnotes, \'\'), ifnull(rd.highlight, 0) highlight, ifnull(rd.marginnote, \'\') marginnote, if(v.versetext=\'-\', v.commentary, v.versetext) versetext
            from verse v
            left join myrevdata rd on rd.myrevid = '.$redr.' and v.testament = rd.testament and v.book = rd.book and v.chapter = rd.chapter and v.verse = rd.verse
            where v.testament = '.$test.'
            and v.book = '.$book.'
            and v.chapter = '.$chap.'
            and v.verse = '.$vers.' ';
    $row = rs($sql);
    $mynotes = $row[0];
    $hlite = $row[1];
    $mnote = $row[2];
    $verse = $row[3];
    $verse = str_replace('[pg]', ' ', $verse);
    $verse = str_replace('[hp]', ' ', $verse);
    $verse = str_replace('[hpbegin]', ' ', $verse);
    $verse = str_replace('[hpend]', ' ', $verse);
    $verse = str_replace('[lb]', ' ', $verse);
    $verse = str_replace('[listbegin]', ' ', $verse);
    $verse = str_replace('[listend]', ' ', $verse);
    $verse = str_replace('[bq]', ' ', $verse);
    $verse = str_replace('[/bq]', ' ', $verse);
    $verse = str_replace('[br]', ' ', $verse);
    $verse = str_replace('[fn]', ' ', $verse);
    $verse = str_replace('[mvh]', ' ', $verse);
    $verse = str_replace('[mvs]', ' ', $verse);
    if($test>1 && strlen($verse) > 500) $verse = truncateHtml($verse, 500);
  }
  //$ret.= '<h2>'.$stitle.'</h2>';
  if($verse!=''){
    //$ret.= '<hr size="1"><p>'.$btitle.' '.$chap.':'.$vers.') '.$verse.'</p>';
    $ret.= '<p><strong>'.$btitle.(($test<2)?' '.$chap.':'.$vers.')':'').'</strong> '.$verse.'</p>';
  }
  if($mnote!=''){
    $ret.= '<p style="margin-left:80px;font-size:90%;"><strong><em>Margin note:</em> </strong>'.$mnote.'</p>';
  }
  if($mynotes!=''){
    //$ret.= 'My notes: '.$mynotes;
    $ret.= '<hr><blockquote>'.$mynotes.'</blockquote><hr>';
  }

  return $ret;
}

function getbookcomm(){
  global $test, $book;
  $btitle = getbooktitle($test,$book,0);
  $sql = 'select ifnull(tagline, title), comfootnotes, commentary from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $ret = '<h3 style="display:inline">Introduction to '.$row[0].'</h3>';
  if(!$row){
    $ret.='NO DATA';
  }elseif($row[2]==null){
    $ret.='<p>Sorry, there is no commentary for '.$row[0].'.</p>';
  }else{
    $comfootnotes = $row['comfootnotes'];
    // handle new footnotes
    $comfootnotes = getfootnotes($test, $book, 0, 0, 'com');
    //
    $commentary = $row['commentary'];
    $commentary = nvl($commentary, "-");
    $commentary = processcommforPDFdisplay($commentary);
    $commentary = processcomfootnotes_pdf($commentary, $comfootnotes);
    $ret.=$commentary;
    $ret.=printcomfootnotes_pdf();
  }
  return $ret;
}

function getcommentary(){
  global $test, $book, $chap, $headingprinted;
  $btitle = getbooktitle($test,$book,0);
  $babbr = getshortbookabbr($test,$book);

  $ret=''; $havecontent=0;
  if($headingprinted==0){
    $ret='<h2>REV Commentary</h2>';
    $headingprinted=1;
  }
  $sql = 'select verse, comfootnotes, commentary from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and commentary is not null order by chapter, verse ';
  $com = dbquery($sql);
  if(mysqli_num_rows($com) > 0){
    $ret.='<h3>'.$btitle.' Chapter '.$chap.'</h3>';
    $havecontent=1;
  }
  while($row = mysqli_fetch_array($com)){
    $commentary = $row['commentary'];
    $commentary = (($commentary)?$commentary:'-');
    $comfootnotes = $row['comfootnotes'];
    // handle new commentary footnotes
    $comfootnotes = getfootnotes($test, $book, $chap, $row['verse'], 'com');
    //
    if($commentary!='-'){
      $commentary = processcommforPDFdisplay($commentary);
      if(substr($commentary, 0, 3) == '<p>'){
        $commentary = '<p style="margin-top:0;">'.trim(substr($commentary, 3));
      }
      $commentary = processcomfootnotes_pdf($commentary, $comfootnotes);
      $ret.='<span style="text-align:left;font-size:95%;">'.$babbr.' '.$chap.':'.$row['verse'].'</span><br />';
      $ret.='<div style="margin-left:20px">'.$commentary.printcomfootnotes_pdf(1).'</div>';
    }
  }
  if($havecontent==1) $ret.='<hr />';
  //die($ret);
  return $ret;
}

function getappxintro(){
  global $test, $book;
  $sql = 'select comfootnotes, commentary from verse where testament = '.$test.' and book = '.$book.' and chapter = 1 and verse = 1 ';
  $row = rs($sql);
  $comfootnotes = $row['comfootnotes'];
  // handle new commentary footnotes
  $comfootnotes = getfootnotes($test, $book, 1, 1, 'com');
  //
  $ret = $row['commentary'];
  $ret = (($ret)?$ret:'No Content!');
  $ret = str_replace('[longdash]','&mdash;&mdash;&mdash;', $ret);
  $ret = processcommforPDFdisplay($ret);
  $ret = processcomfootnotes_pdf($ret, $comfootnotes);
  $ret.=printcomfootnotes_pdf();
  //die($ret);
  return $ret;
}

function getoutline(){
  global $test, $book;
  $ret = '';
  $sql = 'select ifnull(tagline,concat(\'The Book of \', title)) tagline from book where testament = '.$test.' and book = '.$book.' ';
  $row = rs($sql);
  $btitle = $row[0];
  $ret.= '<h3 style="text-align:center;">Outline for '.$btitle.'</h3>';
  $sql = 'select chapter, verse, level, heading, reference, link from outline
          where testament = '.$test.' and book = '.$book.' and inoutline=1
          order by chapter, verse, level ';
  $lastlvl=0;
  $ni=0;
  $qry = dbquery($sql);
  $ret.= '<ol>';
  while($row = mysqli_fetch_array($qry)){
    $lvl = $row['level'];
    if($lastlvl==1 && $lvl==0) $ret.= '</ol></li>';
    if($lastlvl==0 && $lvl==1) $ret.= '<ol type="A">';
    $heading = str_replace('~','',$row['heading']);
    $heading = str_replace('[br]',' ',$heading);
    $ret.= '<li>'.$heading.' <small>('.$row['reference'].')</small>';
    $lastlvl = $lvl;
    $ni++;
  }
  $ret.= '</li></ol>';
  if($ni==0) $ret = '<h3 style="text-align:center;">Outline for '.$btitle.'</h3><p>Sorry, there is no outline data for this book yet.</p>';
  //die($ret);
  return $ret;
}

function getverscomm(){
  global $test, $book, $chap, $vers, $vsfncnt;
  $btitle = getbooktitle($test,$book, 0);
  $babbr  = getbooktitle($test,$book, 1);
  $stitle = 'REV Commentary for: '.$btitle.' '.$chap.':'.$vers;

  $sql = 'select versetext, heading, footnotes, comfootnotes, commentary from verse where book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  $theverse  = $row['versetext'];
  $footnotes = $row['footnotes'];
  $comfootnotes = $row['comfootnotes'];
  // handle new footnotes
  //$footnotes = getfootnotes($test, $book, $chap, $vers, 'vrs');
  $comfootnotes = getfootnotes($test, $book, $chap, $vers, 'com');
  //
  $commentary= $row['commentary'];
  $vsfncnt     = substr_count($row['heading'].'', '[fn]');
  $theverse = processfootnotes_pdf($theverse, $footnotes, $vers);
  $theverse = str_replace('[mvh]','[pg]', $theverse);
  $theverse = str_replace('[pg][hp]','<br /><br />', $theverse);
  $theverse = str_replace('[pg]','<br /><br />', $theverse);
  $theverse = str_replace('[bq]','<br /><br />', $theverse);
  $theverse = str_replace('[/bq]',' ', $theverse);
  $theverse = str_replace('[br]','<br />', $theverse);
  $theverse = str_replace('[hp]','<br />', $theverse);
  $theverse = str_replace('[lb]','<br />', $theverse);
  $theverse = str_replace('[hpbegin]','<br />', $theverse);
  $theverse = str_replace('[hpend]','<br />', $theverse);
  $theverse = str_replace('[listbegin]','<br />', $theverse);
  $theverse = str_replace('[listend]','<br />', $theverse);
  if(substr($theverse, 0, 1)=='~'){
      //$theverse = '~[['.substr($theverse,1).']]';
      $theverse = '[['.substr($theverse,1).']]';
      $theverse = str_replace(']]]]', ']]', $theverse);
      $theverse = str_replace('[[[[', '[[', $theverse);
  }

  $commentary = nvl($commentary, 'No Commentary...yet');
  $commentary = processcomfootnotes_pdf($commentary, $comfootnotes);
  $commentary = processcommforPDFdisplay($commentary);

  $ret='<h2>'.$stitle.'</h2>';
  $ret.='<b>'.$babbr.' '.$chap.':'.$vers.')</b> '.fixverse($theverse);
  $ret.=printfootnotes_pdf();
  $ret.='<hr />';
  $ret.=$commentary;
  $ret.=printcomfootnotes_pdf();
  //die($ret);
  return $ret;
}

function getbible($printbkhead){
  global $test, $book, $chap, $vers;
  global $useoefirst, $pdftextindent;
  global $versebreak, $arfn, $vsfncnt;

  // not handling reading mode for PDF exports
  if($versebreak>1) $versebreak=0;


  $ret = '';
  $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
  $numchaps = $row[0];
  $oldchap = -1;
  $btitle = getbooktitle($test,$book, 0);
  $sql = 'select v.chapter, v.verse, v.versetext,
          (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
          ifnull(v.heading,\'noscript\') superscript, v.paragraph, v.style, v.footnotes
          from verse v
          where v.testament = '.$test.'
          and v.book = '.$book.' ';
  if($chap>0) $sql .= 'and v.chapter = '.$chap.' ';
  $sql .= 'order by v.chapter, v.verse ';
  $verses = dbquery($sql);
  $paraboth = '</p><p>';
  $parabothhp = '<tr><td colspan="2" style="font-size:10px">&nbsp;</td></tr>'.crlf;
  $inhp = 0;
  $inlist = 0;
  $prevstyle = 0;

  while($row = mysqli_fetch_array($verses)){
    $chapter = $row['chapter'];
    $versnum = $row['verse'];
    $havepara = ($row['paragraph']==1);
    $footnotes = $row['footnotes'];
    // handle new footnotes
    //$footnotes = getfootnotes($test, $book, $chapter, $versnum, 'vrs');
    //

    if($chapter != $oldchap){
      if($chapter==1 || $printbkhead==1){
        $sql = 'select ifnull(tagline, concat(\'The Book of \', title)) tagline from book where testament = '.$test.' and book = '.$book.' ';
        $roww = rs($sql);
        $ret.='<h2>'.$roww['tagline'].'</h2>';
      }
      $ret.='<h3>'.(($book==19)?'Psalm '.$chapter:$btitle.(($numchaps>1)?' Chapter '.$chapter:'')).'</h3>';
      $oldchap = $chapter;
    }

    // initialize everything
    $pvhead='';$pvpara='';$pvvnum='';$pvvers='';$pvpost='';$havehead=0;
    $versbr='';$beginstub='';$endstub='';$havevnum=0;$needsep=0;$vsfncnt=0;

    $verse = trim($row['versetext']);
    $style = $row['style'];   // 1 = prose, 2> = poetry

    // handle heading
    if($row['headcount'] > 0){
      $havess = (($row['superscript'] != 'noscript')?1:0);
      $havehead = 1;
      $sql = 'select heading, level, reference from outline where testament = '.$test.' and book = '.$book.' and chapter = '.$chapter.' and verse = '.$versnum.' and link=1 order by level ';
      $heds = dbquery($sql);
      $hdcnt=0;$head='';
      while($rrow = mysqli_fetch_array($heds)){
        //if($hdcnt>0) $head.= '[br]&nbsp;&nbsp;&nbsp;';
        if($hdcnt>0) $head.= '[br]';
        $head.= $rrow[0];
        if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
        $hdcnt++;
      }
      $head = str_replace('[br]', '<br />', $head);
      //$head = str_replace('[separator]', '<img src="/i/pgdivider.png" style="border:0;width:70%;" alt="" />', $head);
      // try to handle multiple headings, mainly for Song of Songs 5
      $mvhcnt = substr_count($verse, '[mvh]');
      $arhead = explode('~~', $head);
      $idx = 0;
      if($mvhcnt < sizeof($arhead)){
        if($inhp==1 || $inlist==1){
          $pvhead.= '</table><p class="vh">'.fixverse($arhead[0]).'</p>'.(($style==1)?'<p style="margin-top:0">':'<table>');
        }else{
          $pvhead.= (($versnum==1)?'':'</p>').'<p class="vh">'.fixverse($arhead[0]).'</p>'.(($havess==0 && $style==1)?'<p style="margin-top:0'.(($pdftextindent==1)?';text-indent:1.3em;':'').'">':''.crlf);
        }
        $pvhead = processfootnotes_pdf($pvhead, $footnotes, $versnum);
        $havepara=false;
        $idx = 1;
      }
      while($idx < sizeof($arhead)){
        $pos = strpos($verse, '[mvh]');
        if($pos){
          if($inhp==1 || ($style>1&&$style<6)){
            $replace = '</td></tr></table><p class="vh">'.fixverse($arhead[$idx]).'</p><table><tr><td style="width:17px;"></td><td>';
          }else{
            $replace = '[hp]</p><p class="vh">'.fixverse($arhead[$idx]).'</p><p style="margin-top:0">';
          }
          $verse = substr_replace($verse, $replace, $pos, 5);
        }
        $idx++;
      }
    }

    // handle superscript
    if($row['superscript'] != 'noscript'){
      $sscript = str_replace('[br]', '<br />', $row['superscript']);
      // try to handle multiple headings, mainly for Song of Songs 5
      $mvscnt = substr_count($verse, '[mvs]');
      $arhead = explode('~~', $sscript);
      $idx = 0;
      if($mvhcnt < sizeof($arhead)){
        if($inhp==1 || $inlist==1){
          $pvhead.= '</table><p class="vhmicro">'.fixverse($arhead[0]).'</p>'.(($style==1)?'<p style="margin-top:0">':'<table>');
        }else{
          $pvhead.= (($versnum==1)?'':'</p>').'<p class="vhmicro"'.(($havehead==1)?' style="margin-top:0;"':'').'>'.fixverse($arhead[0]).'</p>'.(($style==1)?'<p style="margin-top:0'.(($pdftextindent==1)?';text-indent:1.3em;':'').'">':''.crlf);
        }
        $pvhead = processfootnotes_pdf($pvhead, $footnotes, $versnum);
        $havepara=false;
        $idx = 1;
      }
      while($idx < sizeof($arhead)){
        $pos = strpos($verse, '[mvs]');
        if($pos){
          if($inhp==1 || ($style>1&&$style<6)){
            $replace = '</td></tr></table><p class="vhmicro">'.fixverse($arhead[$idx]).'</p><table><tr><td style="width:17px;"></td><td>';
          }else{
            $replace = '[hp]</p><p class="vhmicro">'.fixverse($arhead[$idx]).'</p><p style="margin-top:0">';
          }
          $verse = substr_replace($verse, $replace, $pos, 5);
        }
        $idx++;
      }
    }

    //$head = str_replace('[separator]', '<img src="/i/pgdivider.png" style="border:0;width:70%;" alt="" />', $head);
    //if(strpos($verse, '[separator]')!==false){
    //  if(strpos($verse, '[separator]')===0){
    //    //if($pvhead=='') $pvhead = '<hr class="divider" style="text-align:left;" />';
    //    $pvhead = '<img src="/i/pgdivider.png" style="border:0;width:70%;" alt="" />'.$pvhead;
        $verse = str_replace('[separator]', '', $verse);
    //  }else{
    //    $verse = str_replace('[separator]', '<img src="/i/pgdivider.png" style="border:0;width:70%;" alt="" />', $verse);
    //  }
    //}

    // all one...  no image
    $pvvnum = '<sup class="sup">'.$versnum.'</sup>';

    $pmargin = (($havepara==1 && $pvhead=='')?' style="margin-top:20px;':' style="margin-top:0;').(($pdftextindent==1)?'text-indent:1.3em;':'').'"';
    $paraboth= '</p><p'.$pmargin.'>';

    switch($style){
    case 1: // prose
      //$pvpara.= (($havepara)?(($inlist==1 || $inhp==1)?'<p>':$paraboth):'');
      $pvpara.= (($versebreak==1)?'<p'.$pmargin.'>':(($havepara)?(($inlist==1 || $inhp==1)?'<p'.$pmargin.'>':$paraboth):''));
      if($inhp==1 || $inlist==1){
        $inhp = 0;
        $inlist=0;
        if($pvhead==''){
          $pvhead = '</table>';
          //$versbr = '<br />';
        }
      }
      if(left($verse, 4) == '[br]'){
        if(!$versebreak) $versbr = '<br />';
        $verse = substr($verse, 4);
      }
      if(left($verse, 4) == '[bq]'){
        $versbr = (($prevstyle==1)?'</p>':'').'<blockquote><p'.$pmargin.'>';
        $verse = substr($verse, 4);
      }
      if(left($verse, 5) == '[/bq]'){
        $versbr = '</p></blockquote><p'.$pmargin.'>';
        $verse = substr($verse, 5);
      }
      if(right($verse, 4) == '[br]') $verse = substr($verse, 0,-4);
      $verse = str_replace('[/bq][pg]', '[/bq]', $verse);
      $verse = str_replace('[pg]', $paraboth, $verse);
      $verse = str_replace('[bq]', (($prevstyle==1)?'</p>':'').'<blockquote><p'.$pmargin.'>', $verse);
      $verse = str_replace('[/bq]', '</p></blockquote><p>', $verse);
      $verse = str_replace('[br]','<br />', $verse);
      $pvvnum = $versbr.$pvvnum;
      $pvpost= (($versebreak==1)?'</p>':'');
      break;
    case 2:  // poetry
    case 3:  // poetry_NB
    case 4:  // BR_poetry
    case 5:  // BR_poetry_NB
      if(($style==4 || $style==5) && $inhp==1) {$needsep=1;} // BR_Poetry and BR_Poetry_NB
      if(false !== ($idx = strpos($verse, '[hpbegin]'))){
        if($inhp==1) {$beginstub = '</table><p>';}
        if($havepara){$beginstub .= $paraboth;}
        $beginstub.= $pvvnum.left($verse, $idx);
        // the str_replace([pg]) is for Obadiah 1:1
        $beginstub = str_replace('[pg]', $paraboth, $beginstub).'</p><table>';
        $havevnum = 1;
        $verse = substr($verse, $idx+9);
        if($versebreak==1 && $inhp==0) $beginstub.='<br />&nbsp;';
        $inhp = 1;
      }
      if(false !== ($idx = strpos($verse, '[hpend]'))){
        $endstub = $parabothhp.'</table>'.substr($verse, $idx+7);
        $verse = left($verse, $idx);
        $inhp = 1;
      }
      if($needsep==1 && $beginstub=='') $beginstub = $parabothhp;

      $ar = explode('[hp]', $verse);
      if($inhp==0){
        $inhp=1;
        $verse = '<table><tr>';
      }else $verse = '<tr>';
      $margintop = '';
      $marginbot = 'margin-bottom:'.(($style==3 || $style==5)?'1px;':'5px;');
      $verse.='<td class="tdvnum" style="'.$margintop.$marginbot.'">'.(($havevnum==0)?$pvvnum:'').'</td><td class="tdv" style="'.$margintop.$marginbot.'">';
      for($ni=0;$ni<sizeof($ar);$ni++){
        if(trim($ar[$ni]) != ''){
          $verse.= trim($ar[$ni]).'<br />';
        }
      }
      $verse = str_replace('[pg]', '<br />', $verse); // this is primarily for Matt 1:6
      $verse.='</td></tr>';
      $verse = $beginstub.$verse.$endstub;
      if($endstub != '') $inhp = 0;
      $pvvnum = '';
      break;
    case 6: // list
    case 7: // list_END
    case 8: // BR_list
    case 9: // BR_list_END
      $idx = strpos($verse, '[listbegin]');
      if($idx !== false){
        $beginstub = (($prevstyle>1)?'</table>':(($versebreak==0)?'':'</p>'));
        $beginstub .= (($havepara)?$paraboth:'').$pvvnum;
        $beginstub .= left($verse, $idx).(($versebreak==1 || $versnum==1)?'<br />&nbsp;':'');
        $beginstub.= '<table><tr><td class="tdvnum"></td><td class="tdvl">';
        $havevnum = 1;
        $verse = substr($verse, $idx+11, 2000);
        $inlist = 1;
      }
      $idx = strpos($verse, '[listend]');
      if($idx !== false){
        $endstub = '</td></tr>'.$parabothhp.'</table>';
        $endstub .= substr($verse, $idx+9, 2000);
        $verse = left($verse, $idx, 2000);
        $inlist = 1;
      }
      $tmpv = '';
      if($inlist==0){
        $inlist=1;
        $tmpv = (($prevstyle==1)?'</p>':'').'<table>';
      }
      if($style==8 || $style==9 || ($havevnum==0 && $versnum==1)){ // br
        $tmpv.= (($havepara && $prevstyle>1)?$parabothhp:'').'<tr><td class="tdvnum">'.$pvvnum.'</td><td class="tdvl">';
        $havevnum = 1;
      }
      if($havevnum==0) $tmpv.= $pvvnum;
      $tmpv.= $verse;
      $tmpv = str_replace('[lb]', '<br />', $tmpv);
      $verse = $tmpv;
      $verse = $beginstub.$verse.((($style==7 || $style == 9))?'</td></tr>':'').$endstub.crlf;
      if($endstub != '') $inlist = 0;
      $pvvnum = '';
      break;
    }
    $verse = processfootnotes_pdf($verse, $footnotes, $versnum);
    $pvvers = fixverse($verse);
    // 20200406 this might cause issues...
    if(strpos($pvvers, 'rNotInText')>0 && strpos($pvvers, '</span>')===false) $pvvers.='</span>';
    //$pvpost.= (($versebreak==1 && $style==1 && right($verse, 13) != '</blockquote>')?'<br />':'').' '; // (($style==1 && $versebreak==1)?'<p style="margin:0;padding:0;height:5px"></p>':'')
    $verse = $pvhead.$pvpara.$pvvnum.$pvvers.$pvpost;
    $prevstyle = $style;

    //if($versnum==28) die($verse);

    $ret.=$verse;
  }
  if($inhp==1 || $inlist==1) $ret.='</table>';
  $ret.=printfootnotes_pdf();
  $ret.='<br />';//.(($style==1)?'&nbsp;<br />':'');
  /*
  die($ret);
  //*/

  return $ret;
}

function processfootnotes_pdf($vers, $ftnotes, $v){
  global $arfn, $vsfncnt;
  $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
  $fword = "[fn]";
  $arfnotes = explode('~~', $ftnotes);
  $havefootnote = ((strpos($vers, $fword)>-1)?strpos($vers, $fword):-1);
  $nf = $vsfncnt;
  $fncnt = sizeof($arfn);
  while($havefootnote>-1){
    if($arfnotes[$nf] != ''){
      $arfn[$fncnt] = $v.'~~'.$arfnotes[$nf];
      $tmp = '<sup class="sup">'.substr($footnoteindicator, ($fncnt%26), 1).'</sup>';
      $fncnt++;
      $vsfncnt++;
    }else{
      $tmp = '';
    }
    $vers = substr($vers, 0, ($havefootnote)).$tmp.substr($vers, ($havefootnote+4));
    $nf++;
    $havefootnote = ((strpos($vers, $fword)>-1)?strpos($vers, $fword):-1);
  }
  return $vers;
}

function printfootnotes_pdf(){
  global $arfn;
  $ret='';
  $fncnt = sizeof($arfn);
  if($fncnt > 0){
    $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
    $ret.='<div style="display:block;border-top:1px solid black;font-size:80%;color:#666666;">';
    for($nf=0;$nf<($fncnt);$nf++){
      $artmp = explode('~~', $arfn[$nf]);
      $v = right(' '.substr($footnoteindicator, ($nf%26), 1), (($artmp[0]<10)?2:1)).'<font color="#aaaaaa">['.$artmp[0].']</font>';
      $ret.='<span style="font-family:courier;font-size:90%;">'.$v.'</span>';
      $ret.=$artmp[1].'<br />';
    }
    $ret =substr($ret, 0, -6).'</div>';
    $arfn = array();
  }
  return $ret;
}

function processcomfootnotes_pdf($comm, $ftnotes){
  global $arcomfn;
  $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
  $fword = "[fn]";
  $arfnotes = explode('~~', $ftnotes);
  $havefootnote = ((strpos($comm, $fword)>-1)?strpos($comm, $fword):-1);
  $nf = 0;
  $fncnt = 0; //sizeof($arcomfn);
  while(isset($arfnotes) && $havefootnote>-1){
    if($arfnotes[$nf] != ''){
      $fpreidx = trim(substr(' '.$footnoteindicator, (intval($fncnt/26)%26), 1));
      $fni = substr($footnoteindicator, ($fncnt%26), 1);
      $arcomfn[$fncnt] = $fpreidx.$fni.'~~'.$arfnotes[$nf];
      $tmp = '<sup class="sup">'.$fpreidx.$fni.'</sup>';
      $fncnt++;
    }else{
      $tmp = '';
    }
    $comm = substr($comm, 0, ($havefootnote)).$tmp.substr($comm, ($havefootnote+4));
    $nf++;
    $havefootnote = ((strpos($comm, $fword)>-1)?strpos($comm, $fword):-1);
  }
  return str_replace($fword, '', $comm);
}

function printcomfootnotes_pdf($addbr=0){
  global $arcomfn;
  $ret='';
  $fncnt = sizeof($arcomfn);
  if($fncnt > 0){
    $footnoteindicator = "abcdefghijklmnopqrstuvwxyz";
    $ret.='<div style="display:block;border-top:1px solid black;font-size:80%;color:#666666;">';
    for($nf=0;$nf<($fncnt);$nf++){
      $artmp = explode('~~', $arcomfn[$nf]);
      $v = '<font color="#aaaaaa">'.$artmp[0].')</font>';
      $ret.='<span style="font-family:courier;font-size:90%;">'.$v.'</span>';
      $ret.=$artmp[1].'<br />';
    }
    $ret =substr($ret, 0, -6).'</div>';
    $arcomfn = array();
  }
  return $ret.(($ret!='' && $addbr==1)?'<br />':'');
}

function processcommforPDFdisplay($com){
  global $parachoice;
  $indented = (($parachoice==3 || $parachoice==4)?1:0);

  // remove anchors
  $com = preg_replace('#<a id="toc(.*?)>(.*?)</a>#', '$2', $com); // remove TOC links
  $com = preg_replace('#<a name="marker(.*?)"><\/a>#', '<a id="marker$1"></a>', $com);
  $com = preg_replace('#<a id=(.*?)</a>#', '', $com); // remove whatsnew markers

  // misc
  $com = preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $com);
  $com = str_replace('[noparse]', '', $com);
  $com = str_replace('[/noparse]','', $com);
  //$com = str_replace('[smallcaps]', '<span style="font-variant: small-caps;">', $com);
  //$com = str_replace('[/smallcaps]', '</span>', $com);

  // tryng to handle formatting for paragraph headings
  // both of these next 2 statements are important
  //$com = preg_replace('#<p>\\s?<strong>(.*?)</strong><br />#', '<p style="text-indent:0;margin:0;"><strong>$1</strong></p><p style="margin-top:0;">', $com);
  $com = preg_replace('#<p>\\s?<strong>#', '<p style="text-indent:0;margin-top:0;"><strong>', $com);

  $com = preg_replace('#</h5>\\s?<p>#', '</h5><p style="margin-top:0;padding-top:0;'.(($indented==1)?'text-indent:1.4em;':'').'">', $com);

  return $com;
}

function tidy($htm){
  /* Output
  die($htm);
  //*/

  global $versebreak,$parachoice;
  $indented = (($parachoice==3 || $parachoice==4)?1:0);

  $htm = str_replace('<blockquote>', '<div class="bq">', $htm);
  $htm = str_replace('</blockquote>', '</div>', $htm);
  $htm = str_replace('dir="rtl"', 'class="rtl"', $htm);             // not sure this is necessary.
  $htm = str_replace('<br /> </li>', '<br />&nbsp;</li>', $htm);    // not sure why..
  $htm = str_replace('<br /><br />'.crlf.'</p>', '<br /></p>', $htm);

  //*
  $config = array(
                  'indent'           => false,
                  'output-xhtml'     => true,
                  'wrap'             => 99999,
                  'preserve-entities'=> 1,
                  'show-body-only'   => 1
                 );
  $tidy = new tidy;
  $tidy->parseString($htm, $config, 'utf8');
  $tidy->cleanRepair();
  $htm = $tidy;

  $htm = preg_replace('#<div class="bq">'.crlf.'<p(.*?)>#', '<div class="bq">'.crlf.'<p style="margin-top:'.(($versebreak==1)?'.5em':'0').';text-indent:0;">', (string) $htm);
  $htm = preg_replace('#</div>'.crlf.'<p class="(.*?)">#', '</div>'.crlf.'<p class="$1" style="margin-top:'.(($versebreak==1)?'.5em':'0').';">', $htm);
  $htm = preg_replace('#</div>'.crlf.'<p style="margin-top:20px;">#', '</div>'.crlf.'<p style="margin-top:'.(($versebreak==1)?'.5em':'0').';">', $htm);
  $htm = str_replace('</div></p>', '</div>', $htm);

  /* Output
  die($htm);
  //*/

  return $htm;
}

?>

