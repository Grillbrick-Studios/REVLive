<?php
if(!isset($page)) die('unauthorized access');
ini_set('memory_limit','256M');     //
//ini_set('max_execution_time', 480); //480 seconds = 8 minutes
set_time_limit(120); // 2 minutes

$stitle = (($ismobile)?'REV Search':'Search REV Bible Text or Commentary');

$srchtxt  = trim(((isset($_REQUEST['srchtxt']))?$_REQUEST['srchtxt']:''));
$srchtest = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['srchtest']))?$_REQUEST['srchtest']:2));
$srchwhat = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['srchwhat']))?$_REQUEST['srchwhat']:1));
$srchhow  = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['srchhow']))?$_REQUEST['srchhow']:1));
$srchcase = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['srchcase']))?$_REQUEST['srchcase']:0));
$showsql  = ((isset($_REQUEST['sql']))?1:0);
$pagnum   = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$kjv      = ((isset($_REQUEST['inckjv']))?$_REQUEST['inckjv']:0);

$sethistorycookie=0;
$dosearch=1;
$msg='';
if($srchtxt=='403.shtml'){
  $srchtxt='';
  logview(3,$srchwhat,$srchhow,0,0,'<span style="color:red;">403.shtml error: </span>'.str_replace('https://'.$site, '', $_SERVER['HTTP_REFERER']));
  $msg = 'There has been an error.<br />We are working on it.<br />In the meantime, be blessed! :-)';
}

//
// check for scripture ref.
// if it is, send them there
//
$breg = '#\b(?:Genesis|Ge|Gen|Exodus|Ex|Exo|Exod|Lev|Leviticus|Le|Numbers|Nu|Num|Deut|Deuteronomy|Dt|Deu|De|Joshua|Js|Jos|Josh|Judges|Jg|'.
        'Jud|Jdg|Ju|Jdgs|Judg|Ruth|Ru|Rut|1 Samuel|1Samuel|1 Sa|1Sa|1 Sam|1Sam|1 S|1S|2 Samuel|2Samuel|2 Sa|2Sa|2 Sam|2Sam|2 S|2S|'.
        '1 Kings|1Kings|1 Ki|1Ki|1 King|1King|1 Kin|1Kin|1 Kngs|1Kngs|1 K|1K|2 Kings|2Kings|2 Ki|2Ki|2 King|2King|2 Kin|2Kin|2 Kngs|2Kngs|2 K|2K|'.
        '1 Chron|1Chron|1 Chronicles|1Chronicles|1 Ch|1Ch|1 Chr|1Chr|2 Chron|2Chron|2 Chronicles|2Chronicles|2 Ch|2Ch|2 Chr|2Chr|Ezra|Ez|Ezr|'.
        'Nehemiah|Neh|Ne|Esther|Es|Est|Esth|Job|Jb|Psalms|Ps|Psa|Pss|Psalm|Proverbs|Pr|Prov|Pro|Ecc|Ecclesiastes|Ec|Eccl|Eccles|'.
        'Song|Song of Solomon|SoS|Song of Songs|Song of Sol|Sng|SS|Isaiah|Isa|Jeremiah|Jer|Je|Lam|Lamentations|La|Lament|Ezekiel|Ek|Ezek|Eze|'.
        'Daniel|Da|Dan|Dl|Dnl|Hosea|Ho|Hos|Joel|Jl|Joel|Joe|Amos|Am|Amos|Amo|Obadiah|Ob|Oba|Obd|Odbh|Obad|Jonah|Jh|Jon|Jnh|Micah|Mi|Mic|'.
        'Nahum|Na|Nah|Hab|Habakkuk|Hb|Hk|Habk|Zeph|Zephaniah|Zp|Zep|Ze|Haggai|Ha|Hag|Hagg|Zech|Zechariah|Zc|Zec|Malachi|Ml|Mal|Mlc|'.
        'Matthew|Mt|Matt|Mat|Ma|Mark|Mar|Mk|Mrk|Luke|Lk|Luk|Lu|John|Jn|Joh|Jo|Acts|Ac|Act|Romans|Ro|Rom|Rmn|Rmns|'.
        '1 Cor|1Cor|1 Corinthians|1Corinthians|1 Co|1Co|1 C|1C|2 Cor|2Cor|2 Corinthians|2Corinthians|2 Co|2Co|2 C|2C|Gal|Galatians|Ga|Gltns|'.
        'Eph|Ephesians|Ep|Ephn|Phil|Philippians|Phi|Ph|Col|Colossians|Co|Colo|Cln|Clns|1 Thess|1Thess|1 Thessalonians|1Thessalonians|1 Th|1Th|1 Thes|1Thes|1 T|1T|'.
        '2 Thess|2Thess|2 Thessalonians|2Thessalonians|2 Th|2Th|2 Thes|2Thes|2 T|2T|1 Tim|1Tim|1 Timothy|1Timothy|1 Ti|1Ti|'.
        '2 Tim|2Tim|2 Timothy|2Timothy|2 Ti|2Ti|Titus|Ti|Tit|Tt|Ts|Philemon|Pm|Phile|Philm|Phlm|Pm|Philem|Hebrews|He|Heb|Hw|James|Jm|Jam|Jas|Ja|'.
        '1 Peter|1Peter|1 Pe|1Pe|1 Pet|1Pet|1 P|1P|2 Peter|2Peter|2 Pe|2Pe|2 Pet|2Pet|2 P|2P|1 John|1John|1 Jo|1Jo|1 joh|1joh|1 Jn|1Jn|1 J|1J|'.
        '2 John|2John|2 Jo|2Jo|2 Jn|2Jn|2 J|2J|3 John|3John|3 Jo|3Jo|3 Jn|3Jn|3 J|3J|Jude|jd|jde|Rev|Revelation|Re|Rvltn)\.? '.
        '(?:\d{1,3}(?::\d+)?)\b#';

while (strpos($srchtxt, '  ')){$srchtxt=str_replace('  ',' ',$srchtxt);}
$srchtxt = trim($srchtxt);
if(strpos($srchtxt, "\xF0") !== false){
  $srchtxt = substr($srchtxt, 0, strpos($srchtxt, "\xF0"));
}
if($inapp) $srchtxt = str_replace('_', ' ', $srchtxt);
//print($srchtxt);

if(($srchwhat==1) && preg_match($breg.'im', $srchtxt, $matches, PREG_OFFSET_CAPTURE)){
  if($matches[0][1]==0){
    $srchtxt = str_replace('\'', '', $srchtxt);
    $srchtxt = str_replace('.', '', $srchtxt);
    $srchtxt = str_replace('-', ' ', $srchtxt);
    $arsh = explode(' ', $srchtxt);
    if(is_numeric($arsh[0])){
      $tmp = $arsh[0];
      $arsh = array_slice($arsh, 1);
      $arsh[0] = $tmp.$arsh[0];
    }
    if(sizeof($arsh)>=3 && strpos($arsh[1], ':')===FALSE){$arsh[1] .= ':'.$arsh[2];$arsh = array_slice($arsh, 0,2);}
    $bk = '/'.$arsh[0];
    $aar = explode(':', $arsh[1]);
    $ch = '/'.$aar[0];
    $vs = ((sizeof($aar)==2)?'/nav'.$aar[1]:'');
    logview(3,$srchwhat,$srchhow,0,0,'nav: '.$srchtxt);
    print('<script>');
    print('olOpen(\'/includes/navnotice.php?loc='.$bk.$ch.$vs.'\', 280, 300, 0);');
    print('</script></div></body></html>');
    exit();
  }
}

//
// if searching commentary and srchtxt is a scripture ref, try and find all permutations
//
if($srchwhat==2 && $srchhow==4){
  $srchtxt = str_replace('_', ' ', $srchtxt);
  if(preg_match($breg.'im', $srchtxt, $matches, PREG_OFFSET_CAPTURE)){
    if($matches[0][1]==0){
      $refregex = $srchtxt;
      $refregex = str_replace('\'', '', $refregex);
      $refregex = str_replace('.', '', $refregex);
      $refregex = str_replace('-', ' ', $refregex);
      $refregex = str_replace(':', ' ', $refregex);
      $refregex = trim($refregex);
      $arsh = explode(' ', $refregex);
      if(is_numeric($arsh[0])){
        // make 1 cor, etc, into 1cor
        $tmp = $arsh[0];
        $arsh = array_slice($arsh, 1);
        $arsh[0] = $tmp.$arsh[0];
      }
      $arsh = array_slice($arsh, 0, 3); // three elements max: book, ch, vs
      $row = rs('select aliases from book where aliases like \'%~'.$arsh[0].'~%\'');
      if($row){
        $refregex = substr($row[0], 1, (strlen($row[0])-2));
        $refregex = preg_replace('#(\d)#', "$1 ", $refregex);
        $otherchvs = '(?:[0-9]{1,3}:[0-9]{1,3}(?:a|b|ff)?(?:(?:-[0-9]{1,3}(?:a|b|ff)?)|(?:, [0-9]{1,3}(?:a|b|ff)?)*)*(?:; |; and | and )?)*';
        if(sizeof($arsh)==2){
          $refregex = '[[:<:]]((?:'.str_replace('~', '|', $refregex).')[[:>:]]\\.?) (?:'.$otherchvs.'([[:<:]]'.$arsh[1].'[[:>:]]))';
          $srchtxt = join(' ', $arsh);
        }else{
          $exactchvs = '('.$arsh[1].':'.$arsh[2].'(?:a|b|ff)?)';
          $exactchlatevs = '(?:('.$arsh[1].':)(?:[0-9]{0,3}(?:a|b|ff)?(?:, |-))+)+('.$arsh[2].'(?:a|b|ff)?)';
          $refregex = '[[:<:]]((?:'.str_replace('~', '|', $refregex).')[[:>:]]\\.?) '.$otherchvs.'(?:'.$exactchvs.'|'.$exactchlatevs.')[[:>:]]';
          if($site=='www.revisedenglishversion.com' || $site=='www.revdevbible.com' || $site=='www.revdevbible2.com') $refregex = str_replace('(?:', '(', $refregex);
          $srchtxt = $arsh[0].' '.$arsh[1].':'.$arsh[2];
        }
        // the live site does not handle non-capturing groups
        if($site=='www.revisedenglishversion.com' || $site=='www.revdevbible.com' || $site=='www.revdevbible2.com') $refregex = str_replace('(?:', '(', $refregex);
      }else{
        $msg = '&ldquo;'.$srchtxt.'&rdquo; is not a valid scripture reference.';
        $dosearch=0;
      }
    }else{
      $msg = '&ldquo;'.$srchtxt.'&rdquo; is not a valid scripture reference.';
      $dosearch=0;
    }
  }else{
    $msg = '&ldquo;'.$srchtxt.'&rdquo; is not a valid scripture reference.';
    $dosearch=0;
  }
}

// old way
if(1==2 && $srchwhat==2 && $srchhow==4){
  $srchtxt = str_replace('_', ' ', $srchtxt);
  if(preg_match($breg.'im', $srchtxt, $matches, PREG_OFFSET_CAPTURE)){
    if($matches[0][1]==0){
      $refregex = $srchtxt;
      $refregex = str_replace('\'', '', $refregex);
      $refregex = str_replace('.', '', $refregex);
      $refregex = str_replace('-', ' ', $refregex);
      $arsh = explode(' ', $refregex);
      if(is_numeric($arsh[0])){
        $tmp = $arsh[0];
        $arsh = array_slice($arsh, 1);
        $arsh[0] = $tmp.$arsh[0];
      }
      if(sizeof($arsh)>=3 && strpos($arsh[1], ':')===FALSE){$arsh[1] .= ':'.$arsh[2];}
      $arsh = array_slice($arsh, 0,2);
      $row = rs('select aliases from book where aliases like \'%~'.$arsh[0].'~%\'');
      if($row){
        $refregex = substr($row[0], 1, (strlen($row[0])-2));
        $refregex = preg_replace('#(\d)#', "$1 ", $refregex);
        $refregex = '[[:<:]]('.str_replace('~', '|', $refregex).')[[:>:]].? [[:<:]]'.$arsh[1].'[[:>:]]';
      }
      $srchtxt = join(' ', $arsh);
      //$srchtxt = $refregex;
      //print_r($arsh);
    }else{
      $msg = '&ldquo;'.$srchtxt.'&rdquo; is not a valid scripture reference.';
      $dosearch=0;
    }
  }else{
    $msg = '&ldquo;'.$srchtxt.'&rdquo; is not a valid scripture reference.';
    $dosearch=0;
  }
}

if($userid==0){ // non-logged-in users
  $srchtxt = str_replace(';','', $srchtxt);
  $srchtxt = str_replace('>','', $srchtxt);
  $srchtxt = str_replace('<','', $srchtxt);
}
$srchtxt = str_replace('%',' ', $srchtxt);
$srchtxt = str_replace('&','', $srchtxt);
$srchtxt = str_replace('"','', $srchtxt);
$srchtxt = str_replace('\\','', $srchtxt);
$srchtxt = str_replace('~',' ', $srchtxt);
//$srchtxt = str_replace(',','', $srchtxt);
$srchtxt = str_replace('...','&hellip;', $srchtxt);
$srchtxt = str_replace('_',' ', $srchtxt);

// allow searching on apostrophe "can't", etc
$srchtxt = str_replace('\'','&rsquo;', $srchtxt);
$srchtxt = str_replace("’", "&rsquo;", $srchtxt);

while (strpos($srchtxt, '  ')) {
  $srchtxt = str_replace('  ',' ', $srchtxt);
}
$srchtxt = trim($srchtxt);
if($srchtxt != ''){
  if(strlen($srchtxt)<3 && !is_numeric($srchtxt) && $srchtxt !='-'){
    $msg = 'Search words must be at least three characters long.';
  }else{
    switch($srchhow){
    case 3: // phrase
    case 4: // script ref
      $arsrch = array($srchtxt);
      break;
    default:
      $arcommon = array('the','for','and');
      $arsrch = explode(' ', $srchtxt);
      $arsrch = array_unique($arsrch);
      $arsrch = array_values($arsrch); // re-index
      $tmp = sizeof($arsrch);
      $words = '';$wc=0;
      for ($ni=0;$ni<$tmp;$ni++) {
        $arsrch[$ni] = str_replace('+','',$arsrch[$ni]);
        if($userid==0 || $srchwhat==2){
          $arsrch[$ni] = str_replace('>','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace('<','',$arsrch[$ni]);
        }
        if($srchhow==2){ // exact words
          if(strpos($arsrch[$ni], '(')!==false ||
             strpos($arsrch[$ni], ')')!==false ||
             strpos($arsrch[$ni], '#')!==false ||
             strpos($arsrch[$ni], '[')!==false ||
             strpos($arsrch[$ni], ',')!==false ||
             strpos($arsrch[$ni], '.')!==false ||
             strpos($arsrch[$ni], ':')!==false ||
             //strpos($arsrch[$ni], ';')!==false ||
             strpos($arsrch[$ni], ']')!==false
             )
            $msg.= 'When searching for exact words, the characters \'(\', \')\', \'#\', \'[\', and \']\' and other punctuation marks are not allowed. They have been removed.';
          $arsrch[$ni] = str_replace('(','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace(')','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace('#','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace('[','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace(']','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace(',','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace('.','',$arsrch[$ni]);
          $arsrch[$ni] = str_replace(':','',$arsrch[$ni]);
          //$arsrch[$ni] = str_replace(';','',$arsrch[$ni]);
        }
        $arsrch[$ni] = str_replace('*','',$arsrch[$ni]);
        if((strlen($arsrch[$ni])<3 && $arsrch[$ni]!='-' && !is_numeric($arsrch[$ni])) || in_array($arsrch[$ni], $arcommon)){
          $words.= '\'<i>'.$arsrch[$ni]. '</i>\', ';
          $wc++;
          unset($arsrch[$ni]);
        }
      }
      if($words != ''){
        $words = substr(trim($words), 0, -1);
        $msg.= 'The word'.(($wc==1)?' ':'s ').$words. ' '.(($wc==1)?'was':'were').' removed from your search.&nbsp; Unless you are searching for a phrase, each word must be at least three characters long.&nbsp; If you want to search for a phrase, make sure to select &ldquo;Find phrase&rdquo; from the &ldquo;Search How&rdquo; dropdown.<br />';
      }
      $arsrch = array_values($arsrch);
      $words = '';$wc=0;
      while (sizeof($arsrch)>5){
        $words.= '\'<i>'.$arsrch[(sizeof($arsrch)-1)]. '</i>\', ';
        $wc++;
        unset($arsrch[(sizeof($arsrch)-1)]);
      }
      if($words != ''){
        $words = substr(trim($words), 0, -1);
        $msg.= 'The word'.(($wc==1)?' ':'s ').$words. ' '.(($wc==1)?'was':'were').' removed from your search.&nbsp; You are limited to five words in your search. ';
      }
      $arsrch = array_values($arsrch);
      $srchtxt = trim(implode(" ", $arsrch));
      break;
    }
  }
}
$srchphrase = 'enter word or phrase';
if($srchtxt=='') $srchtxt = $srchphrase;
// something to do with safari validating an app
if(strpos($srchtxt, 'appleappsiteassociation')!==false) exit(0);
$unescaped = str_replace('\\[', '[', $srchtxt);
$unescaped = str_replace('\\]', ']', $unescaped);
$unescaped = str_replace('&hellip;', '...', $unescaped);
?>
  <span class="pageheader"><?=$stitle?></span>
  <form name="frm" method="post" action="/" autocomplete="off">
  <table style="font-size:80%">
  <?if($screenwidth>480 && !$inapp && 1==2){ // Google search?>
  <tr><td colspan="2">Search the REV using Google:
    <div style="margin:0;padding:0;max-width:360px;">
    <script async src="https://cse.google.com/cse.js?cx=694affd63ca64a4c1"></script>
    <div class="gcse-search"></div>
    </div>
    <style>.gsc-control-cse{padding:0 !important;}</style>
    Or use the native REV search:
  </td></tr>
  <?}?>
  <tr><td style="vertical-align:top;"><?=(($ismobile)?'':'Search ')?>What:</td><td>
    <select name="srchwhat" style="font-size:100%" onchange="setparms(this);">
    <option value="1"<?=fixsel($srchwhat, 1)?>>Bible Text</option>
    <option value="2"<?=fixsel($srchwhat, 2)?>>REV Commentary</option>
  </select>
  <span id="kjv" style="visibility:<?=(($srchwhat==1)?'visible':'hidden')?>"><input type="checkbox" name="inckjv" id="inckjv" value="1"<?=fixchk($kjv)?>><label for="inckjv">Inc<?=((!$ismobile)?'lude':'')?> KJV</label></span>
  </td></tr>
  <tr><td style="white-space:nowrap"><?=(($ismobile)?'':'Search ')?>Where:</td><td style="width:90%"><select name="srchtest" style="font-size:100%">
    <option value="2"<?=fixsel($srchtest, 2)?>>Whole Bible</option>
    <option value="0"<?=fixsel($srchtest, 0)?>>Old Testament</option>
    <option value="1"<?=fixsel($srchtest, 1)?>>New Testament</option>
    <option value="3"<?=fixsel($srchtest, 3)?>>Church Epistles</option>
  </select></td></tr>
  <tr><td><?=(($ismobile)?'':'Search ')?>How:</td><td style="width:90%"><select name="srchhow" id="srchhow" style="font-size:100%" onchange="setcase(this);">
    <option value="1"<?=fixsel($srchhow, 1)?>>Find all matches</option>
    <option value="2"<?=fixsel($srchhow, 2)?>>Find exact word(s)</option>
    <option value="3"<?=fixsel($srchhow, 3)?>>Find phrase</option>
    <?if($srchwhat==2){?>
    <option value="4"<?=fixsel($srchhow, 4)?>>Find scripture ref</option>
    <?}?>
  </select>
  <span id="spansrchcase" style="visibility:<?=(($srchhow!=4)?'visible':'hidden')?>;"><input type="checkbox" name="srchcase" id="srchcase" value="1"<?=fixchk($srchcase==1)?> onclick="srchcase=1-srchcase;" /> <label for="srchcase">Case</label></span>
  </td></tr>
  <tr><td><?=(($ismobile)?'':'Search ')?>For:</td><td><input type="text" name="srchtxt" id="srchtxt" value="<?=$unescaped?>" maxlength="32" style="font-size:100%;color:<?=(($srchtxt==$srchphrase)?'#aaa':'black')?>;width:170px;"  onfocus="if(this.value=='<?=$srchphrase?>') {this.value='';this.style.color='black'}" onblur="if(this.value=='') {this.value='<?=$srchphrase?>';this.style.color='#aaa'}" /></td></tr>
  <tr><td><?=(($superman==1 && $showpdflinks==1 && !$inapp && !$ismobile)?'<span style="color:'.$colors[7].';">show sql <input type="checkbox" name="sql" value="1"'.fixchk($showsql==1).' /></span>':'')?>&nbsp;</td><td>
    <?if(!$inapp){?>
    <input type="submit" name="btns" id="btns" value="Search" style="font-size:100%" onclick="return validate($('srchtxt'));" />
    <?}else{?>
    <input type="button" name="btng" value="Search" style="font-size:100%" onclick="redirect(document.frm);" />
    <?}?>
    <small>(see below for instructions)</small>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="qs" value="<?=$qs?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  </td></tr>
  </table>
  </form>
  <script>
  function validate(el){
    if(el.value=='<?=$srchphrase?>'){
      alert('Please enter something to search for.');
      return false;
    }
    document.frm.pagnum.value=1;
    $('btns').value = 'Pls wait..';
    if($('srchhow').selectedIndex==3)
      setTimeout('$(\'btns\').disabled = \'true\'', 200);
    return true;
  }
  function redirect(f){
    if(f.srchtxt.value=='<?=$srchphrase?>'){
      alert('Please enter something to search for.');
      return false;
    }else{
      document.frm.pagnum.value=1;
      location.href='/srch/?srchtest='+f.srchtest.value+'&srchwhat='+f.srchwhat.value+'&srchhow='+f.srchhow.value+'&srchtxt='+f.srchtxt.value.replace(/ /g,'_')+'&inckjv='+((f.inckjv.checked)?1:0);
    }
  }
  function setparms(ctl){
    $('kjv').style.visibility=(ctl.selectedIndex==0)?'visible':'hidden';
    $('inckjv').checked=(ctl.selectedIndex==0)?($('inckjv').checked):false;
    $('spansrchcase').style.visibility='visible';

    var iscomm = ((ctl.selectedIndex==1)?1:0);
    if(iscomm==0 && $('srchhow').selectedIndex==3) $('srchhow').selectedIndex=0;
    if(iscomm==0)
      $('srchhow').options.length = 3;
    else{
      var opt = document.createElement('option');
      opt.value = 4;
      opt.innerHTML = 'Find scripture ref';
      $('srchhow').appendChild(opt);
    }
  }

  var srchcase = <?=$srchcase?>;
  function setcase(ctl){
    $('spansrchcase').style.visibility=(ctl.selectedIndex<3)?'visible':'hidden';
    if(ctl.selectedIndex==3 && (site=='www.revisedenglishversion.com' || site=='www.revdevbible.com' || site=='www.revdevbible2.com')){
      alert('\nBe aware that searching for scripture references is slow.\nIt may take 15 to 30 seconds.');
    }
    $('srchtxt').focus();
    $('srchtxt').select();
  }

  </script>
  <table style="font-size:90%">
  <tr><td colspan="2">&nbsp;</td></tr>

<?
//print($srchtxt);
if(strlen($srchtxt)<3 && $srchtxt!='-' && !is_numeric($srchtxt)){$srchtxt = '';}
if($dosearch==1 && $srchtxt!='' && $srchtxt!=$srchphrase){

  $srchfld = (($srchwhat==1)?'concat(v.versetext, v.footnotes)':'concat(v.commentary, ifnull(v.comfootnotes, \'\'))');
  $srchbinary = (($srchcase==1)?'binary ':'');
  switch($srchhow){
  case 2: // match exact words
    $sqlwhat = '(replace(replace(replace(replace('.$srchfld.', \'<em>\', \'\'), \'</em>\', \'\'), \'<strong>\', \'\'), \'</strong>\', \'\') rlike '.$srchbinary.'\'[[:<:]]~~~srch~~~[[:>:]]\')';
    break;
  case 3: // match phrase
    if($srchwhat==1)
      $sqlwhat = '(replace(replace(replace(replace(replace('.$srchfld.', \'&hellip;\', \'...\'), \'<em>\', \'\'), \'</em>\', \'\'), \'<strong>\', \'\'), \'</strong>\', \'\') like '.$srchbinary.'\'%~~~srch~~~%\')';
    else
      $sqlwhat = '(replace(replace(replace(replace(replace(replace('.$srchfld.', \'&hellip;\', \'...\'), \'="marker\', \'\'), \'<em>\', \'\'), \'</em>\', \'\'), \'<strong>\', \'\'), \'</strong>\', \'\') like '.$srchbinary.'\'%~~~srch~~~%\')';
    break;
  case 4: // script ref
    $sqlwhat = '('.$srchfld.' rlike \'~~~srch~~~\')';
    break;
  default: // match any
    if($srchwhat==1) // bible
      $sqlwhat = '(replace(replace(replace(replace(replace('.$srchfld.', \'&hellip;\', \'...\'), \'<em>\', \'\'), \'</em>\', \'\'), \'<strong>\', \'\'), \'</strong>\', \'\') like '.$srchbinary.'\'%~~~srch~~~%\')';
    else
      // the live server does not support REGEXP_REPLACE()
      //$sqlwhat = '(replace(replace(replace(replace(REGEXP_REPLACE('.$srchfld.', \'<a id="marker(.*?)</a>\' ,\'\'), \'<em>\', \'\'), \'</em>\', \'\'), \'<strong>\', \'\'), \'</strong>\', \'\') like \'%~~~srch~~~%\')';
      $sqlwhat = '(replace(replace(replace(replace(replace(replace('.$srchfld.', \'&hellip;\', \'...\'), \'="marker\' ,\'\'), \'<em>\', \'\'), \'</em>\', \'\'), \'<strong>\', \'\'), \'</strong>\', \'\') like '.$srchbinary.'\'%~~~srch~~~%\')';
    break;
  }


  if($msg) print('<tr><td colspan="2" style="color:red; font-size:90%;padding:12px;background-color:#ffcccc;">'.$msg.'</td></tr>');
  $sqlhead = 'select if(b.abbr=\'-\', b.title, ifnull(b.abbr, b.title)) title, '.crlf.'v.testament, v.book, v.chapter, v.verse'.(($kjv)?','.crlf.'replace(k.versetext, \'\\\'\', \'&rsquo;\') kjv':'').', '.
             (($srchwhat==1)?crlf.'if(left(footnotes,2)!=\'~~\', concat(v.versetext, \'|||\', replace(v.footnotes,\'~~\',\' | \')), v.versetext)':
              crlf.'if(left(v.comfootnotes,2)!=\'~~\', concat(v.commentary, \'|||\', replace(v.comfootnotes, \'~~\', \' | \')), v.commentary)').' versetext';
  if($srchwhat==1 && $kjv==1){
    for($ni=0;$ni<sizeof($arsrch);$ni++){
      $arsrch[$ni] = trim($arsrch[$ni]);
      $sqlhead.=', '.str_replace('~~~srch~~~', $arsrch[$ni], crlf.$sqlwhat).' fld'.$ni;
    }
  }
  $sql = crlf.'from verse v
          inner join book b on (b.testament = v.testament and b.book = v.book) ';
  if($kjv)
    $sql.= crlf.'join kjv k on (k.testament = v.testament and k.book = v.book and k.chapter = v.chapter and k.verse = v.verse)';
  $sql.=crlf.'where active = 1 ';
  if($srchtest < 2) // srch by testament/appx
    $sql.= crlf.'and (v.testament in ('.$srchtest.', 3'.(($revws==1 || $showdevitems==1)?', 4':'').'))';
  if($srchtest == 2) // srch whole bible/appx
    $sql.= crlf.'and (v.testament in (0,1,3'.(($revws==1 || $showdevitems==1)?', 4':'').'))';
  if($srchtest == 3) // church epistles/appx
    $sql.= crlf.'and ((v.testament = 1 and v.book between 45 and 53) or v.testament in (3'.(($revws==1 || $showdevitems==1)?', 4':'').'))';
  $sql.= crlf.'and(';
  if($srchhow==4){
    $sql.= str_replace('~~~srch~~~', $refregex, $sqlwhat);
  }else{
    for($ni=0;$ni<sizeof($arsrch);$ni++){
      $arsrch[$ni] = trim($arsrch[$ni]);
      $sql.= str_replace('~~~srch~~~', $arsrch[$ni], $sqlwhat);
      if($ni < (sizeof($arsrch)-1)) $sql.= crlf.'and ';
    }
  }
  if($kjv){
    $sql.= crlf.'or(';
    for($ni=0;$ni<sizeof($arsrch);$ni++){
      $arsrch[$ni] = trim($arsrch[$ni]);
      switch($srchhow){
        case 2: // exact words
          $sql.= '(replace(k.versetext, \'\\\'\', \'&rsquo;\') rlike '.$srchbinary.'\'[[:<:]]'.$arsrch[$ni].'[[:>:]]\')';
          break;
        case 3: // match phrase
          $sql.= '(replace(k.versetext, \'\\\'\', \'$rsquo;\') like '.$srchbinary.'\'%'.$arsrch[$ni].'%\')';
          break;
        default: // match any
          $sql.= '(replace(k.versetext, \'\\\'\', \'&rsquo;\') like '.$srchbinary.'\'%'.$arsrch[$ni].'%\')';
          break;
      }
      if($ni < (sizeof($arsrch)-1)) $sql.= crlf.'and ';
    }
    $sql.= ')';
  }
  $sqlkjv='';
  if($srchwhat==2){ // include book commentary
    $sqlkjv = 'b.title, b.testament, b.book, -1, -1, if(left(b.comfootnotes,2)!=\'~~\', concat(b.commentary, \'|||\', replace(b.comfootnotes, \'~~\', \' | \')), b.commentary) versetext';
    $sql.=')'.crlf.'UNION ALL'.crlf.'select sqlkjv'.crlf.'from book b'.crlf.'where active = 1 ';
    if($srchtest < 2) // srch by testament
      $sql.= crlf.'and (b.testament in ('.$srchtest.'))';
    if($srchtest == 2) // srch whole bible
      $sql.= crlf.'and (b.testament in (0,1))';
    if($srchtest == 3) // church epistles
      $sql.= crlf.'and ((b.testament = 1 and b.book between 45 and 53))';
    $sql.= crlf.'and(';

    if($srchhow==4){
      $sql.= str_replace('~~~srch~~~', $refregex, str_replace('v.com', 'b.com', $sqlwhat));
    }else{
      for($ni=0;$ni<sizeof($arsrch);$ni++){
        $arsrch[$ni] = trim($arsrch[$ni]);
        $sql.= str_replace('~~~srch~~~', $arsrch[$ni], str_replace('v.com', 'b.com', $sqlwhat));
        if($ni < (sizeof($arsrch)-1)) $sql.= crlf.'and ';
      }
    }
  }
  $sql.= ') ';
  $sqlorder = crlf.'order by 2, 3, 4, 5 ';

  //
  if($showsql==1) print('<br /><span style="display:block;color:'.$colors[7].';font-size:.7em;line-height:1.2em;">'.str_replace(crlf, '<br />', str_replace('<', '&lt;', str_replace('&', '&amp;', $sqlhead.str_replace('sqlkjv', $sqlkjv, $sql).$sqlorder))).'</span>');
  //

  $pagitmcnt = 50;
  $row = rs('select count(*) from (select \'x\' '.str_replace('sqlkjv', '\'x\'', $sql).') totcount');
  $numrows = $row[0];

  $pagtot = ceil($numrows/$pagitmcnt);
  $limit  = 'limit '.(($pagnum-1)*$pagitmcnt).', '.$pagitmcnt.' ';
  $res = dbquery($sqlhead.str_replace('sqlkjv', $sqlkjv, $sql).$sqlorder.$limit);

  print('<tr><td colspan="2" style="color:red;">Your search found '.$numrows.' results..</td></tr>');
  $pagination = pagination($pagnum, $pagitmcnt, $pagtot);
  if($pagtot>1) print('<tr><td colspan="2">'.$pagination.'</td></tr>');
  $vcount=0;
  while($row=mysqli_fetch_array($res)){
    if($srchwhat==1){ // bible
      $htitle = $row['title'].' '.$row['chapter'].':'.$row['verse'];
      $href='/'.str_replace(' ','',$row['title']).'/'.$row['chapter'].'/nav'.$row['verse'];
      $href.='/1'; // adding $gedit for "Close Window" button.
    }
    else{            // commentary
      if($row['testament'] < 2){
        if($row['chapter']==-1 && $row['verse']==-1){ // book commentary
          $htitle = $row['title'].'(c)';
          $href='/book/'.str_replace(' ','',$row['title']);
        }else{
          $htitle = $row['title'].' '.$row['chapter'].':'.$row['verse'];
          $href='/'.str_replace(' ','',$row['title']).'/'.$row['chapter'].'/'.$row['verse'];
        }
      }else{
        $htitle = $row['title'];
        switch($row['testament']){
        case 2: $href='/intro/'.$row['book'];break;
        case 3: $href='/appendix/'.$row['book'];break;
        case 4: $href='/wordstudy/'.str_replace(' ', '_', $row['title']);break;
        }
      }
      $href.='/1'; // adding $gedit for "Close Window" button.
    }
    $showkjv=0;
    if($kjv==1){
      for($ni=0;$ni<sizeof($arsrch);$ni++){
        if($row['fld'.$ni]==0) $showkjv=1;
      }
    }
    if($ismobile || $inapp){
      print('<tr><td colspan="2" style="border-top:1px dashed #999999;padding:0;margin:0"><a href="'.$href.'" target="'.(($inapp)?'_self':'_blank').'">'.$htitle.'</a></td></tr>');
      print('<tr><td style="width:1em">&nbsp;</td><td>'.hilite(clean($row['versetext']), $showkjv).'</td></tr>');
      if($showkjv==1){
        print('<tr><td colspan="2" style="color:'.$colors[7].';"><small>(KJV)</small></td></tr>');
        print('<tr><td style="width:1em">&nbsp;</td><td style="color:'.$colors[7].';font-size:90%;">'.hilite($row['kjv'], 1).'</td></tr>');
      }
    }else{
      print('<tr>');
      print('<td style="vertical-align:top;white-space:nowrap;border-top:1px dashed #999999;"><a href="'.$href.'" target="_blank">'.$htitle.'</a></td>');
      print('<td style="border-top:1px dashed #999999;">'.hilite(clean($row['versetext']), $showkjv).'</td>');
      print('</tr>');
      if($showkjv==1){
        print('<tr><td style="vertical-align:top;color:'.$colors[7].';"><small>(KJV)</small></td>');
        print('<td style="color:'.$colors[7].';font-size:90%;">'.hilite($row['kjv'], 1).'</td>');
        print('</tr>');
      }
    }
    $vcount++;
  }
  if($pagtot>1) print('<tr><td colspan="2">'.$pagination.'</td></tr>');
  if($vcount>5) print('<tr><td colspan="2" style="text-align:center;">&nbsp;<br /><small>(<a onclick="return scrolltotop()">top</a>)</small></td></tr>');
  if($pagnum==1) logview(3,$srchwhat,$srchhow,0,0,$srchtxt);
  if($msg=='') $sethistorycookie=1;
} else if($msg) print('<tr><td colspan="2" style="color:red; font-size:90%;padding:12px;background-color:#ffcccc;">'.$msg.'</td></tr>');
print('</table>');
printhelp();
?>
<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);

  function dopage(pnum){
    document.frm.pagnum.value=pnum;
    document.frm.submit();
  }

</script>
<?
if($sethistorycookie==1 && $pagnum==1){
  $cuk = processhistory('3:'.$srchtest.':'.$srchwhat.':'.$srchhow.':'.str_replace(':', '^', str_replace(' ', '_', $srchtxt)).':'.$kjv.':'.$srchcase, 1);
  print($cuk);
}

//
//
//
function hilite($v, $skjv){
  global $arsrch, $srchwhat, $srchhow, $refregex, $userid, $colors, $srchcase, $site;
  $dbg=0;
  if($userid==0){
    $v = str_replace('[noparse]','', $v);
    $v = str_replace('[/noparse]','', $v);
  }
  $v = str_replace('&hellip;','...', $v);
  $ret = $v;
  if($srchcase==1) $regexmod = ''; // case sensitive
  else $regexmod = 'i'; // case insensitive
  if($srchwhat==1){ // bible
    $arwrds = explode(' ', $arsrch[0]);
    $wrds = sizeof($arwrds);
    if($srchhow==3 && $wrds>1){ // match phrase, need to work around <em> and <strong>
      $breg = '('.$arwrds[0].')';
      for($nx=1;$nx<$wrds;$nx++){
        $breg.= '(\s?|\s?<\/?em>\s?|\s?<\/?strong>\s?)('.$arwrds[$nx].')';
      }
      $test = $v;
      $ret='';
      $last=$v;
      while(preg_match('#'.$breg.'#'.$regexmod, $test, $matches, PREG_OFFSET_CAPTURE)){
        $nm = sizeof($matches);
        $start = $matches[0][1];
        $end   = $matches[($nm-1)][1] + strlen($matches[($nm-1)][0]);
        $begin = substr($test, 0, $start);
        $last  = substr($test, $end);
        $words = '';
        for($nx=1;$nx<$nm;$nx++){
          $mod = $nx%2;
          $words.= (($mod==1)?'<span style="color:red">':'').$matches[$nx][0].(($mod==1)?'</span>':'');
        }
        $ret.= $begin.$words;
        $test = substr($test, $end);
      }
      if($last==$v) $last.=' <span style="color:'.$colors[7].';font-style:italic;font-size:80%;">(unable to highlight)</span>';
      $ret.= $last;
    }else if($srchhow==2){ // exact word(s)
      for($nx=0;$nx<sizeof($arsrch);$nx++){
        $ret = preg_replace('#\\b('.fixforregex($arsrch[$nx]).')\\b#'.$regexmod, '<span style="color:red">$1</span>', $ret);
      }
    }else{
      for($nx=0;$nx<sizeof($arsrch);$nx++){
        $ret = preg_replace('#('.fixforregex($arsrch[$nx]).')#'.$regexmod, '<span style="color:red">$1</span>', $ret);
      }
    }
  }else{ // searching commentary
    $ret = str_replace('<br />',' ', $ret);
    $ret = str_replace('<p>',' ', $ret);
    $ret = str_replace('</p>',' ', $ret);
    $ret = str_replace('<ol>',' ', $ret);
    $ret = str_replace('</ol>',' ', $ret);
    $ret = str_replace('<ul>',' ', $ret);
    $ret = str_replace('</ul>',' ', $ret);
    $ret = str_replace('<li>',' ', $ret);
    $ret = str_replace('</li>',' ', $ret);
    $ret = str_replace('<table>',' ', $ret);
    $ret = str_replace('</table>',' ', $ret);
    $ret = str_replace('<tr>',' ', $ret);
    $ret = str_replace('</tr>',' ', $ret);
    $ret = str_replace('<td>',' ', $ret);
    $ret = str_replace('</td>',' ', $ret);
    $ret = str_replace('<blockquote>',' ', $ret);
    for($nx=0, $size=count($arsrch);$nx<$size;$nx++){
      if($arsrch[$nx] == 'strong') $ret = strip_tags($ret);
    }
    $rret = '';
    switch($srchhow){
    case 4: // scripture ref
      if($site=='www.revisedenglishversion.com' || $site=='www.revdevbible.com' || $site=='www.revdevbible2.com'){
        // live site, no non-capture groups, so hilite the whole phrase
        preg_match_all('#\b'.$refregex.'\b#'.'im', $ret, $matches, PREG_OFFSET_CAPTURE);
        for($nx=0;$nx<sizeof($matches[0]);$nx++){
          $idx = $matches[0][$nx][1];
          $start = (($idx>=20)?20:$idx);
          while (($idx-$start-1) > 0 && charat($ret, ($idx-$start-1)) != ' ') $start++;
          $end = (50+strlen($matches[0][$nx][0]));
          while (($idx-$start+$end+1) < strlen($ret) && charat($ret, ($idx-$start+$end+1)) != ' ') $end++;
          $tmpstr = '...'.substr($ret, ($idx-$start), ($end+1)).'... ';
          $tmpstr = preg_replace('#('.$matches[0][$nx][0].')#i', '<span style="color:red"><noparse>$1</noparse></span>', $tmpstr);
          $rret.= dotidy($tmpstr);
        }
      }else{
        preg_match_all('#\b'.$refregex.'\b#'.'im', $ret, $matches, PREG_OFFSET_CAPTURE);
        if($dbg==1){
          print('<pre>');
          print_r($matches);
          print('</pre>');
        }
        for($nx=0;$nx<sizeof($matches[0]);$nx++){
          $idx = $matches[0][$nx][1];
          $theref = $matches[0][$nx][0];
          $reflen = strlen($theref);
          if($dbg==1){
            print('<br />');
            print('<br />sizeof: '.sizeof($matches));
            print('<br />idx: '.$idx);
          }
          $start = (($idx>=20)?20:$idx);
          while (($idx-$start-1) > 0 && charat($ret, ($idx-$start-1)) != ' ') $start++;
          $startstr = substr($ret, ($idx-$start), $start);

          $end = $idx+$reflen+20;
          while (($end+1) < strlen($ret) && charat($ret, ($end+1)) != ' ') $end++;
          $end = $end-$idx-$reflen;
          $endstr = substr($ret, ($idx+$reflen), $end+1);

          if($dbg==1){
            print('<br />startstr: '.$startstr);
            print('<br />theref: '.$theref);
            print('<br />endstr: '.$endstr);
          }

          $theref = hiliteref($theref, $nx, $idx, $matches);
          $tmpstr = '...'.$startstr.$theref.$endstr.'... ';
          $rret.= dotidy($tmpstr);
        }
      }
      break;
    case 3: // phrase
      $arwrds = explode(' ', $arsrch[0]); // there will only be one search "word"
      $wrds = sizeof($arwrds);
      if($wrds>1){
        // handle separately
        $ret = replacediacritics($ret); // allows searching for Greek words ie: sozo
        $breg = '('.fixforregex($arwrds[0]).')';
        for($nx=1;$nx<$wrds;$nx++){
          $breg.= '(\s?|\s?<\/?em>\s?|\s?<\/?strong>\s?)('.fixforregex($arwrds[$nx]).')';
        }
        $test = $ret;
        $ret2='';
        $initoffset = 0;
        while(preg_match('#'.$breg.'#'.$regexmod, $test, $matches, PREG_OFFSET_CAPTURE)){
          $nm = sizeof($matches);
          $initoffset += $matches[0][1];
          $endcut= $matches[($nm-1)][1] + strlen($matches[($nm-1)][0]);
          $words = '';
          for($nx=1;$nx<$nm;$nx++){
            $mod = $nx%2;
            $words.= (($mod==1)?'<span style="color:red">':'').$matches[$nx][0].(($mod==1)?'</span>':'');
          }

          $startlen = (($initoffset>=20)?20:$initoffset);
          $start = $initoffset-$startlen;
          while(($start-1)>0 && charat($ret, ($start-1)) != ' '){
            $start--; $startlen++;
          }
          $startstr = substr(substr($ret, 0, $initoffset), -$startlen);

          $initoffset+= strlen($matches[0][0]);
          $endlen = 20;
          $endpos = $initoffset + 20;
          while(($endpos+1) < strlen($ret) && charat($ret, ($endpos+1)) != ' '){
            $endpos++; $endlen++;
          }
          $endstr = substr($ret, $initoffset, $endlen+1);
          $ret2.= dotidy('...'.$startstr.$words.$endstr.'</em></strong>...').' ';
          $test = substr($test, $endcut);
        }
        $rret = $ret2;
        break;
      }
      // passthru if $wrds = 1
    default: // 1, 2, maybe 3
      $regexwrap = (($srchhow==2)?'\\b':'');
      $ret = replacediacritics($ret); // allows searching for Greek words ie: sozo
      for($nx=0, $size=count($arsrch);$nx<$size;$nx++){
        $ret2 = '';
        $gidx = 0;
        $srchterm = $arsrch[$nx];
        while(preg_match('#'.$regexwrap.fixforregex($srchterm).$regexwrap.'#'.$regexmod, $ret, $matches, PREG_OFFSET_CAPTURE, $gidx)){
          $initoffset = $matches[0][1];
          $srchword = substr($ret, $initoffset, strlen($srchterm));

          $startlen = (($initoffset>=20)?20:$initoffset);
          $start = $initoffset-$startlen;
          while(($start-1)>0 && charat($ret, ($start-1)) != ' '){
            $start--;
            $startlen++;
          }
          $startstr = substr(substr($ret, 0, $initoffset), -$startlen);

          $endlen = 20;
          $end = $initoffset + strlen($srchterm)+20;
          while(($end+1) < strlen($ret) && charat($ret, ($end+1)) != ' '){
            $end++;
            $endlen++;
          }
          $endstr = substr($ret, ($initoffset+strlen($srchterm)), $endlen+1);

          $tmp = $startstr.'<span style="color:red">'.$srchword.'</span>'.$endstr;
          $ret2.= dotidy('...'.$tmp.'</em></strong>...').' ';
          $gidx = $initoffset + strlen($srchterm);
        }
        $rret.= $ret2;
      }
      break;
    }
    $ret = $rret;
  }
  $ret = str_replace('[[','<span class="rNotInText">[', $ret);
  $ret = str_replace(']]',']</span>', $ret);
  if(strpos($ret, '|||')!==false)
    $ret = str_replace('|||','<br /><span style="font-size:90%;"><em>footnotes:</em> ', $ret).'</span>';
  if($ret=='') $ret=(($srchwhat==1)?$v:getsample($v, 160).'...').' <span style="color:'.$colors[7].';font-style:italic;font-size:80%;">(sorry, unable to highlight)</span>';
  return $ret;
}

function hiliteref($ref, $nx, $idx, $mch){
  $ret='';
  $startpos = 0;
  $lastlen = 0;
  for($ni=1;$ni<sizeof($mch);$ni++){
    $offset = $mch[$ni][$nx][1];
    $part   = $mch[$ni][$nx][0];
    if($offset>-1){
      $idx2 = $offset-$idx;
      $ret.= substr($ref, $startpos, $idx2-$lastlen).'<span style="color:red;">'.$part.'</span>';
      $startpos = $idx2+strlen($part);
      $lastlen+= strlen($mch[$ni-1][$nx][0]);
    }
  }
  return $ret;
}

function dotidy($ret){
  $tcfg = array(
               'new-inline-tags'  => 'noparse',
               'indent'           => false,
               'output-xhtml'     => true,
               'wrap'             => 99999,
               'preserve-entities'=> 1,
               'show-body-only'   => 1
               );
  $tidy = new tidy;
  $tidy->parseString($ret, $tcfg, 'utf8');
  $tidy->cleanRepair();
  $ret = str_replace(PHP_EOL, ' ', (string) $tidy);
  return $ret;
}

function getsample($str, $start){
  $str = str_replace('<br />',' ', $str);
  $str = str_replace('<p>',' ', $str);
  $str = str_replace('</p>',' ', $str);
  if(strlen($str)<=$start) return $str;
  $ret = substr($str, 0, $start);
  while(charat($str.' ', $start)!=' '){
    $ret.= charat($str, $start);
    $start++;
  }
  return $ret;
}

function fixforregex($str){
  $ret = $str;
  $ret = str_replace('"', '', $ret);
  $ret = str_replace('\'', '', $ret);
  $ret = str_replace('+', '', $ret);
  $ret = str_replace('-', '\\-', $ret);
  $ret = str_replace('#', '\\#', $ret);
  $ret = str_replace('(', '\\(', $ret);
  $ret = str_replace(')', '\\)', $ret);
  $ret = str_replace('[', '\\[', $ret);
  $ret = str_replace(']', '\\]', $ret);
  return $ret;
}

function clean($v){
  $ret = str_replace('[mvh]','[pg]', $v);
  $ret = str_replace('[mvs]','[pg]', $v);
  $ret = str_replace('[pg]', '<br /><br />', $ret);
  if(left($ret, 4)=='[bq]') $ret = substr($ret, 4);
  $ret = str_replace('[bq]', '<br /><br />', $ret);
  $ret = str_replace('[/bq]', ' ', $ret);
  $ret = str_replace('[br]', '', $ret);
  $ret = str_replace('[fn]', ' ', $ret);
  $ret = str_replace('[hpbegin]', '<br />', $ret);
  $ret = str_replace('[hpend]', '<br />', $ret);
  $ret = str_replace('[hp]', '<br />', $ret);
  $ret = str_replace('[listbegin]', '<br />', $ret);
  $ret = str_replace('[listend]', '<br />', $ret);
  $ret = str_replace('[lb]', '<br />', $ret);
  if(substr($ret,0,1) == '~'){
    $ret = '<span class="rNotInText">'.substr($ret, 1).'</span>';
  }
  return $ret;
}

function charat($str, $pos){
  return $str[$pos];}

function printhelp(){
  global $colors;
?>
  <table>
  <tr><td colspan="2" style="height:10px;border-top:1px solid <?=$colors[1]?>"></td></tr>
  <tr><td colspan="2" style="font-size:80%">
    You may search the REV Bible text or the commentary. You may choose to search the entire bible, either testament, or the church epistles.<br /><br />

    Many people are very familiar with the King James Version (KJV). You may want to locate verses containing a phrase or words that you know are in the KJV, but those exact words are not in the REV.
    An example is the word &ldquo;<span style="color:red">dispensation</span>.&rdquo; If you search the REV Bible text for &ldquo;<span style="color:red">dispensation</span>,&rdquo; you will not get any results. We have added the option to &ldquo;Include KJV.&rdquo;
    If you check the &ldquo;Include KJV&rdquo; checkbox and search for &ldquo;<span style="color:red">dispensation</span>,&rdquo; the search results will contain verses where the word is found in the KJV.<br /><br />

    You may search for multiple words by separating them with a space. Do not enter double (") quotes, or semi-colons (;). Search words must be at least three (3) characters long. Also, common words like &ldquo;and,&rdquo; &ldquo;for,&rdquo; and &ldquo;the&rdquo; are not permitted unless they are part of a phrase you are searching for, such as &ldquo;day of the lord.&rdquo;<br /><br />

    There are three ways you can search: &ldquo;Find all matches,&rdquo; &ldquo;Find exact word(s),&rdquo; and &ldquo;Find phrase.&rdquo; They work like this:
    Say you search for &ldquo;<span style="color:red">utter words</span>.&rdquo; If &ldquo;Find all matches&rdquo; is selected (the default), the results will contain verses with the words &ldquo;<span style="color:red">utter</span>,&rdquo; &ldquo;<span style="color:red">utter</span>ed,&rdquo; &ldquo;<span style="color:red">utter</span>ly,&rdquo; and &ldquo;<span style="color:red">words</span>,&rdquo; &ldquo;s<span style="color:red">words</span>,&rdquo; etc.
    If &ldquo;Find exact word(s)&rdquo; is selected, results will only contain verses with the exact words &ldquo;<span style="color:red">utter</span>&rdquo; and &ldquo;<span style="color:red">words</span>&rdquo; in them. If &ldquo;Find phrase&rdquo; is selected, results will only contain verses with the phrase &ldquo;<span style="color:red">utter words</span>&rdquo; in them.<br /><br />

    By default, searches are not case sensitive. For example, searching for &ldquo;messiah&rdquo; will return results with &ldquo;messiah&rdquo; or &ldquo;Messiah&rdquo; in them.
    <!--Check the &ldquo;Case&rdquo; checkbox if you want to see, for example, only &ldquo;Messiah&rdquo; or only &ldquo;messiah.&rdquo;-->
    If you want to match exactly what you're looking for, check the &ldquo;Case&rdquo; checkbox, and the search will return exact matches.
    <br /><br />

    You may also search the commentary for scripture references. For &ldquo;Search What,&rdquo; select &ldquo;REV Commentary,&rdquo; then for &ldquo;Search How,&rdquo; select &ldquo;Find scripture ref.&rdquo;
    Then enter a valid scripture reference, and click &ldquo;Search.&rdquo; The system will find all occurrences of that scripture reference in the REV Commentary, whether the book name is abbreviated or not.<br /><br />

    You may search for Strong&rsquo;s numbers by simply searching the commentary for the number.<br /><br />
    Links in search results will open in a new window or tab.
  </td></tr>
  </table>

<?
}

function pagination($pnum, $pitmcnt, $ptot){
  global $colors;
  $ret='<div style="text-align:left;margin:8px auto;font-size:90%;">';
  if($pnum-1 > 0){
    $ret.= ' <a onclick="dopage('.($pnum-1).');">&laquo;prev</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">&laquo;prev</span> ';
  }
  $ret.= 'Page ';
  $ret.= '<select onchange="dopage(this.selectedIndex+1);">';
  for($ni=1;$ni<=$ptot;$ni++){
    $ret .= '<option'.fixsel($ni, $pnum).'>'.$ni.'</option>';
  }
  $ret.= '</select>';
  $ret.= ' of '.$ptot;
  if($pnum+1 <= $ptot){
    $ret.= ' <a onclick="dopage('.($pnum+1).');">next&raquo;</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">next&raquo;</span> ';
  }
  $ret.= '</div>';
  return $ret;
}

?>