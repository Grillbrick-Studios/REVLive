<?php
$msg='';
$proceed = 0;
$whchprgm = ((isset($_POST['whchprgm']))?$_POST['whchprgm']:1); // which program: 1=eSword, 2=MySword, 3=BW, 4=theWord
$revinfo=0; // this is a switch for exporting 'information', probably will never use

//print('ws: '.$revws);

if(isset($_POST['oper']) && $_POST['oper'] == 'go'){
  $estimestamp = getsettingvalue('estimestamp', 'string');
  switch($whchprgm){
  case 1: // eSword
    $thepgm = 'e-Sword&reg;';
    $arfiles = array('REV_Bible'.$estimestamp.'.bbli', 'REV_Commentary'.$estimestamp.'.cmti', 'REV_Commentary_with_verses'.$estimestamp.'.cmti', 'REV_Appx'.$estimestamp.'.refi');
    $arfiles2 = array(46,47,48,49);
    $artitles= array('REV Bible', 'REV Commentary', 'REV Commentary w/verses', 'REV Appendices');
    if($revws) {array_push($arfiles, 'REV_WS'.$estimestamp.'.refi');array_push($arfiles2, 85);array_push($artitles, 'REV Word Studies');}
    if($revinfo) {array_push($arfiles, 'REV_Info'.$estimestamp.'.refi');array_push($artitles, 'REV Information');}
    logview(41,0,0,0,0,'eSword');
    break;
  case 2: // MySword
    $thepgm = 'MySword&reg;';
    $arfiles = array('REV_Bible.bbl.mybible', 'REV_Commentary.cmt.mybible', 'REV_Commentary_with_verses.cmt.mybible', 'REV_Appx.bok.mybible');
    $arfiles2 = array(50,51,52,53);
    $artitles= array('REV Bible', 'REV Commentary', 'REV Commentary w/verses', 'REV Appendices');
    //if($revws) {array_push($arfiles, 'REV_WS.bok.mybible');array_push($arfiles2, 86);array_push($artitles, 'REV Word Studies');}
    if($revinfo) {array_push($arfiles, 'REV_Info.bok.mybible');array_push($artitles, 'REV Information');}
    logview(42,0,0,0,0,'MySword');
    break;
  case 3: // BibleWorks
    $thepgm = 'BibleWorks&reg;';
    $arfiles = array('REV_Bibleworks.zip');
    $arfiles2 = array(54);
    $artitles= array('REV Bible and Commentary');
    logview(43,0,0,0,0,'Bibleworks');
    break;
  case 4: // theWord
    $thepgm = 'theWord&reg;';
    $arfiles = array('REV.ont.twzip', 'REV_Commentary.cmt.twm.twzip', 'REV_Commentary_with_verses.cmt.twm.twzip', 'REV_Appx.gbk.twm.twzip');
    $arfiles2 = array(55,56,57,58);
    $artitles= array('REV Bible', 'REV Commentary', 'REV Commentary with verses', 'REV Appendices');
    if($revws) {array_push($arfiles, 'REV_WS.gbk.twm.twzip');array_push($arfiles2, 87);array_push($artitles, 'REV Word Studies');}
    if($revinfo) {array_push($arfiles, 'REV_Info.gbk.twm.twzip');array_push($artitles, 'REV Information');}
    logview(44,0,0,0,0,'TheWord');
    break;
  case 5: // iBibleStudy
    $thepgm = 'iBible-Study HD&reg;';
    $arfiles = array('REV.ont', 'REV_Commentary.cmt.twm', 'REV_Commentary_with_verses.cmt.twm', 'REV_Appx.gbk.twm');
    $arfiles2 = array(60,61,62,63);
    $artitles= array('REV Bible', 'REV Commentary', 'REV Commentary with verses', 'REV Appendices');
    if($revws) {array_push($arfiles, 'REV_WS.gbk.twm.twzip');array_push($arfiles2, 88);array_push($artitles, 'REV Word Studies');}
    if($revinfo) {array_push($arfiles, 'REV_Info.gbk.twm.twzip');array_push($artitles, 'REV Information');}
    logview(59,0,0,0,0,'iBibleStudy');
    break;
  case 6: // Microsoft Word
    $thepgm = 'Microsoft Word';
    //$arfiles = array('REV_Bible.docx','REV_Commentary.docx','REV_Appendices.docx','REV_Information.docx');
    //$arfiles2 = array(80,81,82,84);
    //$artitles= array('REV Bible', 'REV Commentary', 'REV Appendices', 'REV Information');
    $arfiles = array('REV_Appendices.docx','REV_Information.docx');
    $arfiles2 = array(82,84);
    $artitles= array('REV Appendices', 'REV Information');
    if($revws) {array_push($arfiles, 'REV_WS.refi');array_push($arfiles2, 83);array_push($artitles, 'REV Word Studies');}
    //if($revinfo) {array_push($arfiles, 'REV_Info.refi');array_push($artitles, 'REV Information');}
    array_push($arfiles, 'REV_MSWord'.$estimestamp.'.zip');array_push($arfiles2, 89);array_push($artitles, 'REV Bible, Commentary, Appendices, Word Studies, and Bibliography in a single zip file');
    logview(79,0,0,0,0,'MSWord');
    break;
  case 7: // Swordsearcher
    $thepgm = 'Swordsearcher&reg;';
    $arfiles = array('REV_Swordsearcher.zip');
    $arfiles2 = array(64);
    $artitles= array('REV Bible, Commentary, and Appendices');
    logview(65,0,0,0,0,'Swordsearcher');
    break;
  case 8: // Accordance
    $thepgm = 'Accordance&reg;';
    $arfiles = array('REV_Accordance.zip');
    $arfiles2 = array(66);
    $artitles= array('REV Bible and Commentary');
    logview(67,0,0,0,0,'Accordance');
    break;
  case 9: // Logos
    $thepgm = 'Logos&reg;';
    $arfiles = array('REV_Logos.zip');
    $arfiles2 = array(69);
    $artitles= array('REV Bible and Commentary');
    logview(68,0,0,0,0,'Logos');
    break;
  }
  $proceed = 1;
}else{
  logview(45,0,0,0,0,'looking..');
}
$path = $docroot.'/export/expdown/';

?>
<div style="min-width:280px;max-width:560px;margin:auto;">
<h2>Export the REV</h2>
<?
if($inapp==1){
  print('<p>We apologize for the inconvenience, but due to technical problems the REV Export functions are not available within the REV App and the STF App. If you want to export the REV for use in another Bible program, please use a browser, either Safari or Chrome, and go to https://www.revisedenglishversion.com.<br /><br />Thank you, and God bless you.</p>');
}else{

// first loading, no choice
if($proceed==0){?>
<b>For information on exporting the REV to Bible programs, <a onclick="return scrolltopos('toptop', 'exprtinstructions');">see below</a>.</b>
<br /><br />

<form name="frm" method="post" action="/">
  Please select the program or app you're using.<br /><br />
  <table>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft1" value="1" checked="checked" /></td><td><label for="ft1">e-Sword&reg; (PC, X, HD, LT, Android)</label> <small><span style="color:red">(The PC version must be at least v11.)</span></small></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft2" value="2" /></td><td><label for="ft2">MySword&reg; (Android)</label></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft3" value="3" /></td><td><label for="ft3">BibleWorks&reg; (Windows PC)</label> <small><span style="color:red">(Includes Bible and Commentary)</span></small></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft4" value="4" /></td><td><label for="ft4">theWord&reg; (PC)</label></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft5" value="5" /></td><td><label for="ft5">iBible-Study HD&reg; (iPad)</label></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft7" value="7" /></td><td><label for="ft7">Swordsearcher&reg; (Windows PC)</label></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft8" value="8" /></td><td><label for="ft8">Accordance&reg;</label> (PC or Mac. <span style="color:red;font-size:80%;">PC version must be 13.1.7 or higher.</span>)</span></td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft9" value="9" /></td><td><label for="ft9">Logos&reg;</label> (PC or Mac.)</td></tr>
    <tr><td style="vertical-align:top;"><input type="radio" name="whchprgm" id="ft6" value="6" /></td><td><label for="ft6">Microsoft Word&reg;</label> <small>(any computer or device with MS Word installed)</small></td></tr>
  </table>
  <br />
  <input type="submit" name="btn" id="btn" value="Proceed" style="font-size:100%" onclick="document.frm.oper.value='go';" />
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="30" />
  <input type="hidden" name="qs" value="<?=$qs?>" />
  <input type="hidden" name="oper" value="" />
  </form>

<?
if($superman==1){
  print('<br />');
  if($userid==1) print('<span style="font-size:.7em;">details for '.$username.':</span><br />');
  print('<table style="font-size:.7em;border-spacing:0;border-collapse:separate;">');
  print('<tr><td>Program / File</td><td style="padding-left:5px;">Last Updated</td></tr>');

  $expfiles = array();
  $path = $docroot.'/export/expdown/';
  if ($handle = opendir($path)) {
    $nowdate = new DateTime(null ?? '', new DateTimeZone($timezone));
    if(!($serverTimeZone = date_default_timezone_get())) $serverTimeZone = 'UTC';
    $ni = 0;
    while (false !== ($file = readdir($handle))) {
      if(($file!='.' && $file!='..' && $file!='!readme.txt')){
        $processfile=1;
        $thefile = preg_replace('#(.*?)_\d*(\..*?)#', '$1$2', $file);
        switch($thefile){
          case 'REV_Bible.bbli':                 $idx=0;$fnam='e-Sword / REV Bible';break;
          case 'REV_Commentary.cmti':            $idx=1;$fnam='e-Sword / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmti':$idx=2;$fnam='e-Sword / REV Comm w/verse';break;
          case 'REV_Appx.refi':                  $idx=3;$fnam='e-Sword / REV Appendices';break;
          case 'REV_WS.refi':                    $idx=4;$fnam='e-Sword / REV Word Studies';break;
          case 'REV_Bible.bbl.mybible':          $idx=5;$fnam='MySword / REV Bible';break;
          case 'REV_Commentary.cmt.mybible':     $idx=6;$fnam='MySword / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmt.mybible':$idx=7;$fnam='MySword / REV Comm w/verse';break;
          case 'REV_Appx.bok.mybible':           $idx=8;$fnam='MySword / REV Appendices';break;
          case 'REV_WS.bok.mybible':             $idx=9;$fnam='MySword / REV Word Studies';break;
          case 'REV_Bibleworks.zip':             $idx=10;$fnam='BibleWorks / Bible &amp; Commentary';break;
          case 'REV.ont.twzip':                  $idx=11;$fnam='theWord / REV Bible';break;
          case 'REV_Commentary.cmt.twm.twzip':   $idx=12;$fnam='theWord / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmt.twm.twzip':$idx=13;$fnam='theWord / REV Comm w/verse';break;
          case 'REV_Appx.gbk.twm.twzip':         $idx=14;$fnam='theWord / REV Appendices';break;
          case 'REV_WS.gbk.twm.twzip':           $idx=15;$fnam='theWord / REV Word Studies';break;
          case 'REV.ont':                        $idx=16;$fnam='iBibleStudy / REV Bible';break;
          case 'REV_Commentary.cmt.twm':         $idx=17;$fnam='iBibleStudy / REV Commentary';break;
          case 'REV_Commentary_with_verses.cmt.twm':$idx=18;$fnam='iBibleStudy / REV Comm w/verse';break;
          case 'REV_Appx.gbk.twm':               $idx=19;$fnam='iBibleStudy / REV Appendices';break;
          case 'REV_WS.gbk.twm':                 $idx=20;$fnam='iBibleStudy / REV Word Studies';break;
          //case 'rev_sqlite.zip':                 $idx=21;$fnam='REV / SQLite';break;
          case 'REV_Swordsearcher.zip':          $idx=22;$fnam='Swordsearcher / Bible/Comm';break;
          case 'REV_Accordance.zip':             $idx=23;$fnam='Accordance / Bible/Comm';break;
          //case 'JSON_REV_timestamp.json':        $idx=24;$fnam='JSON / REV Timestamp';break;
          //case 'JSON_REV_bible.json':            $idx=25;$fnam='JSON / REV Bible';break;
          //case 'JSON_REV_commentary.json':       $idx=26;$fnam='JSON / REV Commentary';break;
          //case 'JSON_REV_appendices.json':       $idx=27;$fnam='JSON / REV Appendices';break;
          //case 'REV_Bible.docx':                 $idx=28;$fnam='MSW / REV Bible';break;
          //case 'REV_Commentary.docx':            $idx=29;$fnam='MSW / REV Commentary';break;
          case 'REV_Appendices.docx':            $idx=30;$fnam='MSW / REV Appendices';break;
          case 'REV_Information.docx':           $idx=31;$fnam='MSW / REV Information';break;
          case 'REV_Wordstudies.docx':           $idx=32;$fnam='MSW / REV Word Studies';break;
          case 'REV_MSWord.zip':                 $idx=33;$fnam='MSW / All / Zip';break;
          case 'REV_Logos.zip':                  $idx=34;$fnam='Logos / Bible';break;
          default: $processfile=0;
        }
        if($processfile==1){
          $timestamp = date('m/d/Y ga',filemtime($path.$file));
          $tsdate = new DateTime($timestamp ?? '', new DateTimeZone($serverTimeZone));
          $interval = $tsdate->diff($nowdate);
          $days = abs($interval->days);

          $timestamp = getuserdate($timestamp, $timezone);

          $expfiles[$ni] = array($idx, $fnam, $timestamp, $days);
          $ni++;
        }
      }
    }
    closedir($handle);
  }
  sort($expfiles);
  for($ni=0;$ni<sizeof($expfiles);$ni++){
    print('<tr style="line-height:12px;'.(($expfiles[$ni][3]>=14)?'background-color:#f66;':'').'"><td>'.$expfiles[$ni][1].'</td><td style="padding-left:5px;">'.$expfiles[$ni][2].'</td></tr>'.crlf);
  }
  print('</table>');
}

print('<br />&nbsp;<br /></div>');
print('<a id="exprtinstructions"></a>');
grabinfo(4); // this is an unpublished information article!!

}else{ // user has made their choice
if ($handle = opendir($path)) {
  $timestamp = 0;
  while (false !== ($file = readdir($handle))) {
    $filelastmodified = filemtime($path . $file);
    if(in_array($file, $arfiles) && ($filelastmodified > $timestamp)){
      $timestamp = $filelastmodified;
    }
  }
  closedir($handle);
  $timestamp = getuserdate(date('m/d/Y ga',$timestamp), $timezone);
}

?>

  <div id="divmodule" style="display:block">
  Please click the module you want to download for <?=$thepgm?>.<br />
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:.8em;color:gray;">(modules last updated <?=$timestamp?>.)</span><br />
<?

  // not sure which is best for mobile devices.
  //$target=(($ismobile)?'_blank':'_self');
  $target=(($inapp)?'_self':'_blank');
  //$target=(($ismobile)?'_self':'_self');
  for($ni=0;$ni<sizeof($arfiles2);$ni++){
    //print('<br /><a href="/export/expdown/'.$arfiles[$ni].'" target="'.$target.'">Click here</a> to download the '.$artitles[$ni].'.');
    print('<br /><a href="/jsondload.php?fil='.$arfiles2[$ni].'" target="'.$target.'">Click here</a> to download the '.$artitles[$ni].'.');
  }
  print('<br /><br /><strong>For instructions on how to install these files into '.$thepgm.':</strong>');
  switch($whchprgm){
  case 1: // eSword
    ?>

<ul>
  <li>If you have a Windows computer, <a href="/expinst/eSwordPC.html" target="_blank">click here</a>.</li>
  <li>If you have an Apple Mac computer, <a href="/expinst/eSwordX.html" target="_blank">click here</a>.</li>
  <li>If you have an Apple iPad, <a href="/expinst/eSwordiPad.html" target="_blank">click here</a>.</li>
  <li>If you have an Apple iPhone, <a href="/expinst/eSwordiPhone.html" target="_blank">click here</a>.</li>
  <li>If you have an Android device, <a href="/expinst/eSwordAndroid.html" target="_blank">click here</a>.</li>
</ul>
<?
    break;
  case 2: // MySword (android)
    ?>
<ul>
  <li>For all Android devices, <a href="/expinst/mysword.html" target="_blank">click here</a>.</li>
</ul>
    <?
    break;
  case 3: // bibleworks
    ?>
<ul>
  <li>For Windows computers, <a href="/expinst/bibleworks.html" target="_blank">click here</a>.</li>
</ul>
<?
    break;
  case 4: // theWord
  ?>
<ul>
  <li>For theWord on a Windows PC, <a href="/expinst/theword.html" target="_blank">click here</a>.</li>
</ul>
<?  break;
  case 5: // iBibleStudy
  ?>
<ul>
  <li>For iBible-Study HD on an iPad, <a href="/expinst/ibiblestudy.html" target="_blank">click here</a>.</li>
</ul>
<?  break;
  case 7: // Swordsearcher
  ?>
<ul>
  <li>For Swordsearcher on a Windows PC, <a href="/expinst/swordsearcher.html" target="_blank">click here</a>.</li>
</ul>
<?  break;
  case 8: // Accordance
  ?>
<ul>
  <li>For Accordance, <a href="/expinst/accordance.html" target="_blank">click here</a>.</li>
</ul>
<?  break;
  case 9: // Logos
  ?>
<ul>
  <li>For Logos, <a href="/expinst/logos.html" target="_blank">click here</a>.</li>
</ul>
<?  break;
  case 6: // MSWord
      ?>
<ul>
  <!--<li><span style="color:red;">Note that the REV Bible and Commentary are large files and they are very slow to open in newer versions of Microsoft Word.</span> You will probably do better to download the zip file (the last file listed above), which contains all REV content in separate Word documents. They will open quickly.</li>-->
  <li>The full REV Bible and Commentary are no longer available as Word docs. Those two files became extremely slow to open. We are working on a solution. In the meantime, you can download the zip file (the last file listed above), which contains all REV content in separate Word documents. They will open quickly.</li>
  <li>Assuming you have Microsoft Word installed on your computer or device, or a program that will open Word files such as <a href="https://www.libreoffice.org/download/download-libreoffice/" target="_blank">LibreOffice</a>, double-clicking or double-tapping on the file should open it.</li>
</ul>
  <?
    break;
  }
  print('<br />Note that some of these files are large, so depending on the speed of your internet connection, it may take several seconds to download the file. Please be patient.');
  print('<br /><br /><a href="/expt">Click here</a> to choose a different app or program.');
  ?>
  </div>
<?
}
} // end of $inapp
function getuserdate($date, $userTimeZone = 'America/New_York'){
  $format = 'n/j/Y ga';
  if(!($serverTimeZone = date_default_timezone_get())) $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime($date ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}


?>

