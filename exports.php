<?php
if(empty($userid) || $userid==0) die('unauthorized access');

ini_set('memory_limit','768M');     //
ini_set('max_execution_time', 480); //480 seconds = 8 minutes
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/functions_phpdocx.php";
require_once $docroot."/phpdocx/classes/CreateDocx.inc";

global $thefont, $expfontsize;
$stitle = 'Exports to MS Word';
$processed = 0;
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=="export"){
  $pagebreak = ((isset($_POST['pagebreak']))?1:0);
  $otbkcomm  = ((isset($_POST['otbkcomm']))?$_POST['otbkcomm']:0);
  $ntbkcomm  = ((isset($_POST['ntbkcomm']))?$_POST['ntbkcomm']:0);
  $unique = getuserfiletimestamp($timezone, 0);
  $workdir = createworkdir();

  $thefont='arial';
  if($fontfamily=='merriweather' || $fontfamily=='times new roman' || $fontfamily=='caladea' || $fontfamily=='ibm plex serif')
    $thefont = 'times new roman';
  // export prefs are in their own cookie
  $expprefs   = explode(';', (isset($_COOKIE['rev_expprefs']))?$_COOKIE['rev_expprefs']:'2;1'); // medium font, no gutter
  $expfontsize   = $expprefs[0];
  $expmargintype = $expprefs[1]; // no need to handle, do it from within MSW

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

  foreach($_POST as $key => $value){
    $prependidx=1;
    if (strstr($key, 'doc') && $value==1){
      $doc = str_replace('doc','',$key);
      $tmp = explode('_', $doc);
      $test = $tmp[0];
      $book = $tmp[1];
      $docx = new CreateDocx($docroot.'/includes/REV_styles.docx');
      $docx->modifyPageLayout('letter',
                              array('marginTop' => 1080,
                                    'marginBottom' => 1080,
                                    'marginLeft' => 900,
                                    'marginRight' => 900,
                                    'numberCols' => (($test==0 || $test==1)?(($viewcols>1)?2:1):1)
                                    ));
      $options = array(
          'textAlign' => 'center',
          'pStyle' => 'rFooter'
      );
      $numbering = new WordFragment($docx, 'defaultFooter');
      //$numbering->addPageNumber('page_Number', $options);
      $numbering->addPageNumber('page_NumberOfX', $options);
      $docx->addFooter(array('default' => $numbering));

      $html = '';

      switch ($test) {
      case 2: // intro
      case 3: // appx
        $folder = (($test==2)?'Information':'Appendices');
        if($book < 95){
          $ftitle = 'REV_'.(($test==2)?getbooktitle($test,$book, 0):getbooktagline($test,$book));
          $html = getappxintro();
        }else{
          switch($book){
          case 98:
            $ftitle = 'REV_Abbreviations';
            $html = getbibcontent(0);
            break;
          case 99:
            $ftitle = 'REV_Bibliography';
            $html = getbibcontent(1);
            break;
          default:
            $ftitle = 'UNKNOWN!';
            $html = 'No content!';
          break;
          }
        }
        loadhtm($docx, tidify($html));
        break;
      case 8: // word studies
        $folder = 'Word Studies';
        $test = 4;
        $prependidx=0;
        $ftitle = getbooktitle($test,$book, 0);
        $html = getappxintro();
        loadhtm($docx, tidify($html));
        break;
      case 0: // OT text
        $folder = 'Old Testament';
        $ftitle = 'REV_'.getbooktitle($test,$book, 1);
        $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
        $chaps = $row[0];
        for($ni=1;$ni<=$chaps;$ni++){
          $chap = $ni;
          $html = getbible(0);
          loadhtm($docx, tidify($html));
        }
        break;
      case 4: // OT comm
        $folder = 'OT Commentary';
        $test = 0;
        $html = '';
        $ftitle = 'REV_Commentary_'.getbooktitle($test,$book, 1);
        if($otbkcomm > 0){
          $html = getbookcomm();
          loadhtm($docx, tidify($html));
          if($otbkcomm==1){
            if($pagebreak==1){
              $docx->addBreak(array('type' => 'page'));
              $html='';
            }else{
              $html = '<br /><br /><br />';
            }
          }
        }
        if($otbkcomm < 2){
          $html.='<h2>'.getbooktitle($test,$book, 0).' Commentary</h2>';
          $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
          $chaps = $row[0];
          for($ni=1;$ni<=$chaps;$ni++){
            $chap = $ni;
            $html .= getcommentary();
            loadhtm($docx, tidify($html));
            $html = '<p>&nbsp;</p>';
          }
        }
        break;
      case 1: // NT text
        $folder = 'New Testament';
        $ftitle = 'REV_'.getbooktitle($test,$book, 1);
        $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
        $chaps = $row[0];
        for($ni=1;$ni<=$chaps;$ni++){
          $chap = $ni;
          $html = getbible(0);
          loadhtm($docx, tidify($html));
        }
        break;
      case 5: // NT comm
        $folder = 'NT Commentary';
        $test = 1;
        $html = '';
        $ftitle = 'REV_Commentary_'.getbooktitle($test,$book, 1);
        if($ntbkcomm > 0){
          $html = getbookcomm();
          loadhtm($docx, tidify($html));
          if($ntbkcomm==1){
            if($pagebreak==1){
              $docx->addBreak(array('type' => 'page'));
              $html='';
            }else{
              $html = '<br /><br /><br />';
            }
          }
        }
        if($ntbkcomm<2){
          $html.='<h2>'.getbooktitle($test,$book, 0).' Commentary</h2>';
          $row = rs('select chapters from book where testament = '.$test.' and book = '.$book.' ');
          $chaps = $row[0];
          for($ni=1;$ni<=$chaps;$ni++){
            $chap = $ni;
            $html .= getcommentary();
            loadhtm($docx, tidify($html));
            $html = '<p>&nbsp;</p>';
          }
        }
        break;
      }
      $properties = array(
          'title' => fixforword($ftitle),
          'creator' => 'John Schoenheit',
          'dateCreated' => '12/12/2015',
          'date' => '12/12/2015',
          'description' => 'Revised English Version',
      );
      $docx->addProperties($properties);

      if($prependidx==1) $ftitle = $book.'_'.$ftitle;
      $ftitle = fixtxt($ftitle);
      $ftitle = str_replace(': ','_',$ftitle);
      $ftitle = str_replace(' ','_',$ftitle);
      $ftitle = str_replace(':','',$ftitle);
      $ftitle = str_replace('/','_',$ftitle);
      $ftitle = str_replace('?','',$ftitle);
      $ftitle = str_replace(';','_',$ftitle);
      $filenames[] = $ftitle.'.docx';
      $folders[] = $folder;
      if($pagebreak==1) $docx->addBreak(array('type' => 'page'));
      $docx->createDocx($docroot.'/export/'.$workdir.$ftitle);
      unset($docx);
    }
  }
  $zipfilename = "REV_MSWord".$unique.".zip";
  $zip = new ZipArchive();
  $zipfile = $docroot."/export/".$workdir.$zipfilename;

  if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {
      exit("cannot open <$zipfile>\n");
  }
  $cnt=0;
  foreach ($filenames as $filename) {
    $zip->addFile('./export/'.$workdir.$filename, $folders[$cnt].'/'.$filename);
    $cnt++;
  }
  $zip->close();
  foreach ($filenames as $filename) {
    unlink($docroot.'/export/'.$workdir.$filename);
  }
  $processed = 1;
}

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
<form name="frm" action="/" method="post">
  <table class="gridtable" style="font-size:80%">
<?
  if($processed==1){
    print('<tr><td colspan="6">');
    print('<span style="color:red"><b>Click <a href="/export/'.$workdir.$zipfilename.'">here</a> to download the file.</b></span> <small>Please download it now.  It will be removed from the server in 7 minutes.</small>');
    print('</td></tr>');
?>
    <tr>
      <td colspan="6">
      <h4>Instructions for combining MSWord documents</h4>
      <ol>
        <li>Download the zip file by clicking the link above.</li>
        <li>Extract the downloaded file, locate and open the first document you want to combine in MSWord.  For example, if you want to combine the books of the NT, open Matthew.</li>
        <li>Position the cursor at the very end of the file.  If you checked the box to add a page break, the cursor will be on a blank page.  If you did not check the box, hit [ENTER] a time or two.</li>
        <li>On the MSWord main menu, click "Insert".</li>
        <li>Over towards the right, locate and click the little down-arrow to the right of "Object"<br />
            <img src="/i/insertobject.jpg" alt="insert object" border="0" /></li>
        <li>Click on "Text from file..."</li>
        <li>In the window that opens, navigate to and select the rest of the Word docs you want to combine.  For example, if you are combining the books of the NT, select Mark through Revelation.</li>
        <li>Click "Insert", and Word will append those docs to the end of the first doc you opened.</li>
        <li>Save the combined file with an appropriate file name, such as "REV_NT.docx" (or whatever)</li>
      </ol>
      </td>
    </tr>
<?
  }else{
?>
    <tr>
      <td align="center" colspan="7">
      NOTICE: depending on how many things are checked,<br />this may take a few minutes to complete.  Please be patient.
      </td>
    </tr>
    <tr>
      <td align="center" colspan="7">
      Choose what you want to export and click <input type="submit" name="btn" value="here." onclick="return validate(document.frm);">
      &nbsp;&nbsp;
      <small>Add page break at end for combining MSWord docs</small><input type="checkbox" name="pagebreak" value="1" checked="checked">
      </td>
    </tr>
    <tr>
      <td style="text-align:right;vertical-align:top;">Information <input type="checkbox" name="larry2" value="1" onclick="chkall(0);"></td>
      <td style="text-align:right;vertical-align:top;">Old Test <input type="checkbox" name="larry0" value="1" onclick="chkall(1);"></td>
      <td style="text-align:right;vertical-align:top;">OT Comm <input type="checkbox" name="larry4" value="1" onclick="chkall(2);"><br />Book Comtry<br />
      <select name="otbkcomm">
        <option value="0" selected>exclude</option>
        <option value="1">prepend</option>
        <option value="2">only</option>
      </select></td>
      <td style="text-align:right;vertical-align:top;">New Test <input type="checkbox" name="larry1" value="1" onclick="chkall(3);"></td>
      <td style="text-align:right;vertical-align:top;">NT Comm <input type="checkbox" name="larry5" value="1" onclick="chkall(4);"><br />Book Comtry<br />
      <select name="ntbkcomm">
        <option value="0" selected>exclude</option>
        <option value="1">prepend</option>
        <option value="2">only</option>
      </select></td>
      <td style="text-align:right;vertical-align:top;">Appendices <input type="checkbox" name="chkall5" id="chkall5" value="1" onclick="chkall(5);"></td>
      <?if($revws==1 || $showdevitems==1){?>
      <td style="text-align:right;vertical-align:top;">Word Studies <input type="checkbox" name="chkall6" id="chkall6" value="1" onclick="chkall(6);"></td>
      <?}?>
    </tr>
    <tr>
  <?
  if($revws==1 || $showdevitems==1)
    $tests = array(2,0,4,1,5,3,8);
  else
    $tests = array(2,0,4,1,5,3);
  $js = 'var testbooks = [';
  for($ni=0;$ni<sizeof($tests);$ni++){
    $js.='['.$tests[$ni].',';
    $tst = (($tests[$ni]<4)?$tests[$ni]:$tests[$ni]-4);
    $sql = 'select book, abbr, tagline, title from book where testament = '.$tst.' and active = 1 order by '.(($tst==4)?'title':'sqn').' ';
    $dat = dbquery($sql);
    print('<td style="text-align:right;vertical-align:top;white-space:nowrap;">');
    while($row = mysqli_fetch_array($dat)){
      $nam = fixname($tst, $row[1], $row[2], $row[3]);
      print($nam.' <input type="checkbox" name="doc'.$tests[$ni].'_'.$row[0].'" value="1"><br />');
      $js.=$row[0].',';
    }
    if($tests[$ni]==2){
      // add abbrevs and bib to Info
      print('Abbreviations <input type="checkbox" name="doc'.$tests[$ni].'_98" value="1"><br />');
      $js.='98,';
      print('Bibliography <input type="checkbox" name="doc'.$tests[$ni].'_99" value="1"><br />');
      $js.='99,';
    }
    $js.='0],';
    print('</td>'.crlf);
  }
  print('</tr>');
}
print('</table>');

?>
<input type="hidden" name="mitm" value="<?=$mitm?>" />
<input type="hidden" name="page" value="<?=$page?>" />
<input type="hidden" name="test" value="<?=$test?>" />
<input type="hidden" name="book" value="<?=$book?>" />
<input type="hidden" name="chap" value="<?=$chap?>" />
<input type="hidden" name="vers" value="<?=$vers?>" />
<input type="hidden" name="oper" value="" />
</form>
<script>
  <?if($processed==0) print($js.'0];');?>

  function chkall(idx){
    var ar = testbooks[idx];
    var tst = ar[0];
    var chk = document.frm['doc'+tst+'_'+ar[1]].checked;
    for(i=1;i<ar.length-1;i++){
      document.frm['doc'+tst+'_'+ar[i]].checked=!chk;
    }
    return !chk;
  }

  function validate(f){
    var havebook = false;
    for(i=0;i<testbooks.length;i++){
      ar = testbooks[i];
      tst = ar[0];
      for(j=1;j<ar.length-1;j++){
        if(document.frm['doc'+tst+'_'+ar[j]].checked){
          havebook = true;
          break;
        }
      }
    }
    if(havebook==false){
      alert('Nothing is checked');
      return false;
    }
    f.btn.value = 'please wait..';
    f.oper.value = 'export';
    return true;
  }
</script>
<?
  function fixname($t, $abbr, $tag, $title){
    $title = fixtxt($title);
    $tag = fixtxt($tag);
    switch ($t) {
    case 3: //appx
      $ret = str_replace('Appendix ', 'Appx ', $tag);
      if(strlen($ret) > 22)
        $ret = substr($ret, 0, 19).'...';
      break;
    case 0:
    case 1:
      $ret = (($abbr=='-')?(($tag==null)?$title:$tag):$abbr);
      break;
    case 2: // intro
      $ret = $title;
      if(strlen($ret) > 18)
        $ret = substr($ret, 0, 17).'...';
      break;
    case 4: // word study
      $ret = $title;
      if(strlen($ret) > 18)
        $ret = substr($ret, 0, 17).'...';
      break;
    }
    return $ret;
  }

  function fixtxt($t){
    $t = cleanquotes($t);
    //$t = str_replace('“','',$t);
    //$t = str_replace('”','',$t);
    $t = replacediacritics($t);
    return $t;
  }

  function fixforword($title){
    $title = fixtxt($title);
    return $title;
  }
?>
