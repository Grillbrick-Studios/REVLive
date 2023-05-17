<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $editorcomments==0) die('unauthorized access');

$lockmin = 15;

$loc  = ((isset($_REQUEST['loc']))?$_REQUEST['loc']:'1|40|1|1'); // default to Rob, Mat 1:1
$ar   = explode('|', $loc);
// determine if ed workspace
$isworkspc = (((int) $ar[0]===-1)?1:0);
//print('isworkspc: '.$isworkspc.'<br />');

$test = (int) ((isset($ar[1]))?$ar[1]:1);
$book = (int) ((isset($ar[2]))?$ar[2]:40);
$chap = (int) ((isset($ar[3]))?$ar[3]:1);
$vers = (int) ((isset($ar[4]))?$ar[4]:1);

$postcolors = ['#fee','#efe','#eef'];

$sqlerr = '';
$timessubmitted=((isset($_REQUEST['timessubmitted']))?$_REQUEST['timessubmitted']:0);
$oper=((isset($_REQUEST['oper']))?$_REQUEST['oper']:'xx');
$doupdate=1;

if($oper=='sav' || $oper=='del'){
  if($isworkspc==1){
    // dealing with editorworkspace
    $editdetails = $_POST['editdetails'];
    if(strlen($editdetails.'')>60000) $editdetails = truncateHtml($editdetails, 60000);
    $editdetails = processlocalsqlcomm($editdetails, 1, '');

    // get info for edit tracking
    $beforenote = '';
    $beforedtls = '';
    $row = rs('select ifnull(notes, \'\') editdetails
              from myrevusers
              where myrevid = -1 ');
    if($row){
      $beforedtls = $row[0];
      $beforedtls = processlocalsqlcomm($beforedtls, 1, '');
      $beforedtls = replacgreekhtml($beforedtls);
      if($beforedtls!='null') $beforedtls = substr($beforedtls, 1, strlen($beforedtls)-2);
      if($beforedtls=='null') $beforedtls = '';
    }

    $afterdtls = $editdetails;
    if($afterdtls!= 'null') $afterdtls = substr($afterdtls, 1, strlen($afterdtls)-2);
    if($afterdtls=='null') $afterdtls = '';
    if($beforedtls===$afterdtls) $dtlsdiff = null;
    else $dtlsdiff = htmlDiff($beforedtls, $afterdtls);

    $sql = 'select 1
            from myrevusers
            where myrevid = -1';
    $row = rs($sql);
    if($oper=='del' || $row){
      if($oper=='del' || $editdetails=='null'){
        $sql = 'update myrevusers
                set userid = '.$userid.', lastaccessed = UTC_TIMESTAMP, notes = null
                where myrevid = -1 ';
        $logid = logedit(7,$test,$book,$chap,$vers,$userid,'!Editor workspace cleared!', 0, '', $dtlsdiff, 'null', 'null');
      }else{
        $sql = 'update myrevusers set
                userid = '.$userid.', lastaccessed = UTC_TIMESTAMP, notes = '.$editdetails.'
                where myrevid = -1 ';

        // only log edit if something has changed
        if($dtlsdiff!==''){
          $logid = logedit(7,0,0,$chap,$vers,$userid,'Editor workspace updated', 0, '', $dtlsdiff, 'null', 'null');
        }
      }
    }else{ // this should only happen once...
      if($editdetails != 'null'){
        $sql = 'insert into myrevusers (myrevid, myrevname, myrevemail, password, userid, lastaccessed, notes) values ('.
                '-1, \'editorworkspace\', \'dummy\', \'dummy\', '.$userid.', UTC_TIMESTAMP, '.$editdetails.') ';

        // edit tracking
        $logid = logedit(7,$test,$book,$chap,$vers,$userid,'added editor workspace', 0, '', $dtlsdiff, 'null', 'null');

      }else $sqlerr = 'nothing to save';
    }
    $update = dbquery($sql);
    if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
    $timessubmitted += 1;

  }else{
    // dealing with editor marginnotes and details

    // get orig margin note
    $sql = 'select ifnull(edituserid, 0) edituserid,
                   ifnull(editlockeduntil, DATE_ADD(UTC_TIMESTAMP(),INTERVAL '.$lockmin.' MINUTE)) editlockeduntil,
                   ifnull(editnote, \'\') editnote
            from editnotes
            where testament = '.$test.'
            and book = '.$book.'
            and chapter = '.$chap.'
            and verse = '.$vers.' ';
    $row = rs($sql);
    if($row){
      $curutc = strtotime(gmdate('n/j/y g:ia'))+1; // now
      $curlock= strtotime($row['editlockeduntil'] ?? '');
      $curuserid = $row[0];
      if($curuserid==$userid || $curutc > $curlock){
        //print(' using post');
        $editnote = str_replace('~!~', '"', ((isset($_POST['editnote']))?$_POST['editnote']:''));
      }else{
        //print(' using db');
        $editnote = $row['editnote'];
      }
    }else $editnote = str_replace('~!~', '"', ((isset($_POST['editnote']))?$_POST['editnote']:''));

    $resolved = ((isset($_POST['resolved']))?1:0);
    //$editnote = ((isset($_POST['editnote']))?$_POST['editnote']:'');
    $editnote = processlocalsqlcomm($editnote, 1, '');
    if(strlen($editnote)>680) $editnote = substr($editnote, 0, 680).'\'';

    $editdetails = $_POST['editdetails'];
    if(strlen($editdetails.'')>60000) $editdetails = truncateHtml($editdetails, 60000);
    $editdetails = processlocalsqlcomm($editdetails, 1, '');

    // get info for edit tracking
    $beforenote = '';
    $beforedtls = '';
    $coloridx = 0;
    $author = -1;
    $oldresolved = 0;
    $row = rs('select ifnull(colorindex, 0) colorindex, resolved, ifnull(author, -1) author, ifnull(editnote, \'\') editnote, ifnull(editdetails, \'\') editdetails
               from editnotes
               where testament = '.$test.'
               and book = '.$book.'
               and chapter = '.$chap.'
               and verse = '.$vers.' ');
    if($row){
      $coloridx = $row[0];
      $oldresolved = $row[1];
      $author = $row[2];
      $beforenote = $row[3];
      $beforenote = processlocalsqlcomm($beforenote, 1, '');
      if($beforenote!='null') $beforenote = substr($beforenote, 1, strlen($beforenote)-2);
      if($beforenote=='null') $beforenote = '';

      $beforedtls = $row[4];
      $beforedtls = processlocalsqlcomm($beforedtls, 1, '');
      $beforedtls = replacgreekhtml($beforedtls);
      if($beforedtls!='null') $beforedtls = substr($beforedtls, 1, strlen($beforedtls)-2);
      if($beforedtls=='null') $beforedtls = '';
    }

    $afternote = $editnote;
    if($afternote!= 'null') $afternote = substr($afternote, 1, strlen($afternote)-2);
    if($afternote=='null') $afternote = '';
    if($beforenote===$afternote) $notediff = null;
    else $notediff = htmlDiff($beforenote, $afternote);
    if($oper=='del') $notediff = '<del>'.$beforenote.'</del>';

    $afterdtls = $editdetails;
    if($afterdtls!= 'null') $afterdtls = substr($afterdtls, 1, strlen($afterdtls)-2);
    if($afterdtls=='null') $afterdtls = '';

    if($isworkspc==0){
      $edittime = getcurrentdatetime($timezone, 1);
      $resolvediv    = '<div style="display:inline-block;color:#333;background-color:'.$postcolors[0].';margin:2px;padding:0 5px;line-height:1.5em;border:1px solid red;border-radius:4px;font-size:80%;">';
      $resolvedstr   = (($oldresolved==0 && $resolved==1)?$resolvediv.'Marked as resolved by '.$username. ' on '.$edittime.'</div><br />':'');
      $unresolvedstr = (($oldresolved==1 && $resolved==0)?$resolvediv.'Marked as un-resolved by '.$username. ' on '.$edittime.'</div><br />':'');
      if($afterdtls!=''){
        $tmp = preg_replace('/<p>/', '<p style="margin:0 0 7px 2px;">', $afterdtls);
        $afterdtls = '<p style="margin:6px 0 2px 2px;line-height:1em;"><small>'.(($author==-1)?'Initiated':'Added').' by '.$username.' on '.$edittime.'</small></p>';
        $newdtls   = $afterdtls.'<div style="line-height:1.2em;margin:0 0 0 21px;color:#333;background-color:'.$postcolors[$coloridx++].';">'.$tmp.'</div>';
        $afterdtls = $newdtls.$unresolvedstr.$beforedtls;
        if($coloridx==3) $coloridx=0;
      }else{
        $newdtls = '';
        $afterdtls = $unresolvedstr.$beforedtls;
      }
      $afterdtls = $resolvedstr.$afterdtls;
      //print($afterdtls);
      $editdetails = (($afterdtls=='')?'null':'\''.$afterdtls.'\'');
      if($oper=='del') $afterdtls='';
    }

    if($beforedtls===$afterdtls)
      $dtlsdiff = null;
    else{
      if($isworkspc==0)
        $dtlsdiff = (($oper=='del')?'<del>'.$beforedtls.'</del>':$resolvedstr.'<div style="padding:1px;background-color:#ddffdd;">'.$newdtls.$unresolvedstr.'</div><hr>'.$beforedtls);
      else
        $dtlsdiff = htmlDiff($beforedtls, $afterdtls);
    }

    $sql = 'select 1
            from editnotes
            where testament = '.$test.'
            and book = '.$book.'
            and chapter = '.$chap.'
            and verse = '.$vers.' ';
    $row = rs($sql);
    if($oper=='del' || $row){
      if($oper=='del' || ($editdetails=='null' && $editnote=='null')){
        $sql = 'delete from editnotes
                where testament = '.$test.'
                and book = '.$book.'
                and chapter = '.$chap.'
                and verse = '.$vers.' ';
        $logid = logedit(7,$test,$book,$chap,$vers,$userid,'!Editor note deleted!', 0, $notediff, $dtlsdiff, 'null', 'null');
      }else{
        $sql = 'update editnotes set
                lastauthor = '.$userid.',
                lastupdate = UTC_TIMESTAMP,
                addto = 1,
                resolved = '.$resolved.',
                colorindex = '.$coloridx.',
                editnote = '.$editnote.',
                editdetails = '.$editdetails.'
                where testament = '.$test.'
                and book = '.$book.'
                and chapter = '.$chap.'
                and verse = '.$vers.' ';
        if($notediff=='' && $dtlsdiff=='' && $resolved==$oldresolved){
          $doupdate = 0;
          $sqlerr = 'nothing to save';
        }

        // only log edit if something has changed
        if($notediff!='' || $dtlsdiff!='')
          $logid = logedit(7,$test,$book,$chap,$vers,$userid,'See changes', 0, $notediff, $dtlsdiff, 'null', 'null');

      }
    }else{
      if($editdetails != 'null' || $editnote != 'null'){
        $sql = 'insert into editnotes (testament, book, chapter, verse, author, lastauthor, notedate, lastupdate, addto, resolved, colorindex, editnote, editdetails) values ('.
                $test.', '.$book.', '.$chap.', '.$vers.', '.$userid.', '.$userid.', UTC_TIMESTAMP, UTC_TIMESTAMP, 1,'.$resolved.','.$coloridx.', '.$editnote.','.$editdetails.') ';

        // edit tracking
        $logid = logedit(7,$test,$book,$chap,$vers,$userid,'New editor note', 0, $notediff, $dtlsdiff, 'null', 'null');

      }else $sqlerr = 'nothing to save';
    }
    //print($sql);
    //die();
    if($doupdate==1) $update = dbquery($sql);
    if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
    $timessubmitted += 1;
  }
}

if($oper=='del'){?>
  <!DOCTYPE html>
    <script>
    try{
      var qry = '<?=$loc?>';
      parent.reloadeditnotes(qry);
    }catch(e){parent.document.location.reload()}
    try{parent.myrevhidePopup()}catch(e){};
    try{parent.goback+=timessubmitted}catch(e){};
    parent.rlbfadeout();
  </script>
  <?
  exit();
}

if($sqlerr=='') $sqlerr = '&nbsp;';

if($isworkspc==1){
  $stitle = 'Editor Workspace';
  // I should not be doing this..
  $sql = 'select ifnull(userid, 0) author,
                 ifnull(userid, 0) lastauthor,
                 0 resolved, 0 edituserid, null editlockeduntil,
                 lastaccessed notedate, lastaccessed lastupdate,
                 ifnull(notes, \'\') editdetails,
                 \'\' editnote,
                 \'This is the Editor Workspace.\' versetext
          from myrevusers
          where myrevid = -1 ';
}else{
  $btitle = getbooktitle($test,$book, (($screenwidth>=480)?0:1));
  $stitle = (($ismobile)?'Ed Note: ':'Editor Notes on ').$btitle.(($test<2)?' '.$chap.':'.$vers:'');
  $sql = 'select notedate, lastupdate, ifnull(en.author,-1) author,
                 ifnull(en.lastauthor, -1) lastauthor,
                 ifnull(en.resolved, 0) resolved,
                 ifnull(en.edituserid, 0) edituserid,
                 ifnull(en.editlockeduntil, UTC_TIMESTAMP()) editlockeduntil,
                 ifnull(en.editdetails, \'\') editdetails,
                 ifnull(en.editnote, \'\') editnote,
                 if(v.versetext=\'-\', v.commentary, v.versetext) versetext,
                 v.footnotes
          from verse v
          left join editnotes en on v.testament = en.testament and v.book = en.book and v.chapter = en.chapter and v.verse = en.verse
          where v.testament = '.$test.'
          and v.book = '.$book.'
          and v.chapter = '.$chap.'
          and v.verse = '.$vers.' ';
}
$row = rs($sql);
if(!$row){ // only on 1st call to ed workspace.
  $editdetails = '';
  $editnote = '';
  $author = -1;
  $resolved = 0;
  $lastauthor = -1;
  $notedate = 'unknown';
  $updatedate = 'unknown';
  $verse = 'This is the Editor Workspace.';
  $footnotes = '';
}else{
  $editdetails = $row['editdetails'];
  $editnote=$row['editnote'];
  $author = $row['author'];;
  $lastauthor = $row['lastauthor'];
  $edituserid = $row['edituserid'];
  //$editlockeduntil = rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row['editlockeduntil'], $timezone))), 'm');
  $editlockeduntil = $row['editlockeduntil'];
  $resolved = $row['resolved'];
  $notedate = rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row['notedate'], $timezone))), 'm');
  $updatedate = rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row['lastupdate'], $timezone))), 'm');
  $verse = $row['versetext'];
  $footnotes = '';
  if($isworkspc==0){
    $footnotes = $row['footnotes'];
    $oldcontent = (($editdetails=='')?'There is no previous content.':$editdetails);;
    $editdetails = '';
  }else $oldcontent = '';
}

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
$verse = str_replace('[mvs]', ' ', $verse);
$verse = str_replace('[mvh]', ' ', $verse);

$vsfncnt = 0;
$arfn    = array();
$arcomfn = array();
$fncnt   = 0;
$verse = processfootnotes($arfn, $verse, $footnotes, $fncnt, $chap, $vers);

$verse = str_replace('~', '', $verse);
if($test>1 && strlen($verse) > 500) $verse = truncateHtml($verse, 400);

$curutc = strtotime(gmdate('n/j/y g:ia'))+1;
$curlock= strtotime($editlockeduntil ?? '');

if($edituserid==0 || $edituserid==$userid || $curutc > $curlock){
  $qry = dbquery('update editnotes
                  set edituserid = '.$userid.',
                      editlockeduntil = DATE_ADD(UTC_TIMESTAMP(),INTERVAL '.$lockmin.' MINUTE)
                  where testament = '.$test.'
                  and book = '.$book.'
                  and chapter = '.$chap.'
                  and verse = '.$vers.' ');
  $edituserid = $userid;
  $mnlocked=0;
}else{
  $mnlocked = 1;
  $rrw = rs('select ifnull(revusername, myrevname) from myrevusers where myrevid > 0 and userid = '.$edituserid.' ');
  $edusername = $rrw[0];
  $lockeduntil = rtrim(date('n/j/y g:ia', strtotime(converttouserdate($editlockeduntil, $timezone))), 'm');
}
if($timessubmitted==0)
  logview((($isworkspc==1)?309:308),$test,$book,$chap,$vers);


?>
<!DOCTYPE html>
<html>
<head>
  <title>Editor Note Editor</title>
  <link rel="stylesheet" type="text/css" href="/includes/style.min.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
      print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
  <script>
<?
  print('var ismobile = \''.$ismobile.'\';'.crlf);
?>
    var cookieexpiredays = 180;
    var prffontsize   = <?=$fontsize?>;
    var prflineheight = <?=$lineheight?>;
    var prffontfamily ='<?=$fontfamily?>';
    var hdrheight=0;
    var toffset=0;
    var prfscrollynav=0;
  </script>
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/myrevjs.js?v=<?=$fileversion?>"></script>
</head>
<body style="font-family:'<?=$fontfamily?>', 'times new roman', serif;font-size:<?=$fontsize?>em;line-height:1em;background-color:<?=$colors[2]?>;opacity:0;transition:opacity .2s;">

<!--wrapper-->
<div id="wrapper" style="overflow:hidden; height:100%; width:100%;">
<form name="frm" method="post" action="/editnote.php" style="padding:0;margin:0;">

<!--header-->
<div id="myheader" style="position:absolute;top:0;left:0;right:0;text-align:center;padding:7px 0;">
<h3 style="display:inline-block;width:70%;text-align:center;margin:0;"><?=$stitle?></h3>
<span style="display:inline-block;float:right;cursor:pointer;" onclick="olClose('');"><img src="/i/redx.png" style="width:20px;" alt="" /></span>
<?
    print('<div id="pverse" style="margin:8px 0 0 0;font-size:90%;text-align:left;">'.$verse);
    displayfootnotes($fncnt, $arfn, 0, $chap);
    print('</div>');
?>
</div><!--end header-->

<!--body-->
<div id="mynotes" style="position:absolute;overflow-y:auto;left:0;right:0;top:50px;bottom:50px;margin-top:15px;padding:0;font-size:100%;line-height:<?=$lineheight?>em;">
<?
    print(printsqlerr($sqlerr));
    if($isworkspc==1){
      print('<input type="hidden" name="editnote" value="" /><input type="hidden" name="resolved" value="0" />');
    }else{
      if($mnlocked==1)
        print('<br /><small>Margin note (<span style="color:red;">locked by '.$edusername.' until '.$lockeduntil.'</span>):</small>
               <div class="editnote" style="margin-left:0;">'.$editnote.'</div>
               <input type="hidden" name="editnote" value="'.str_replace('"', '~!~', $editnote).'" />');
      else
        print('<br /><small>Margin note:</small><div id="enwrapper" style="border-bottom:1px solid '.$colors[7].';"><textarea name="editnote" id="editnote">'.$editnote.'</textarea></div>');
    }
    print('<small>Details:</small><textarea name="editdetails" id="editdetails">'.$editdetails.'</textarea>');
    if($isworkspc==0) print('<small>Previous content:</small><div id="oldcontent" style="overflow-y:auto;border:1px solid '.$colors[1].';font-family:auto;font-size:96%;">'.$oldcontent.'</div>');
?>
<span style="display:inline-block;font-size:70%;line-height:1.1em;">
<?=(($isworkspc==0)?'Entered by: '.getuser($author).' on '.$notedate.' | ':'')?>
Updated by: <?=getuser($lastauthor)?> on <?=$updatedate?>
</span>
</div><!--end body-->

<!--footer-->
<div style="position:absolute;bottom:0;left:0;right:0;height:50px;">

  <p style="text-align:center;margin:9px 0;">
    <?if($isworkspc==0 && $author!=-1){
        print('&nbsp;<input type="checkbox" name="resolved" id="resolved" value="1"'.fixchk($resolved).' onclick="document.frm.dirt.value=1;"><small>Resolved</small> ');
      }?>
    <input type="submit" name="btnsubmit" class="gobackbutton" style="cursor:pointer;width:<?=(($ismobile==1)?60:80)?>px;" value="Save" onclick="return validate(document.frm);" />
    <input type="button" name="btnclosee" class="gobackbutton" style="cursor:pointer;width:<?=(($ismobile==1)?60:80)?>px;" value="Close" onclick="olClose('');">
    <?if($isworkspc==0 && $superman==1){  // only SU can delete ednotes
        print('&nbsp;<a onclick="return valdel(document.frm)" title="Delete"><img src="/i/myrev_trash'.$colors[0].'.png" style="width:1.7em;margin-bottom:-8px;" alt="Delete" /></a>');
    }else{
        print('');
    }
    ?>
</p>


</div><!--end footer-->

  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="loc" value="<?=$loc?>" />
  <input type="hidden" name="timessubmitted" value="<?=$timessubmitted?>">
  <input type="hidden" name="oper" value="" />
</form>
</div><!--end wrapper-->

<?if($isworkspc==0){?>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<?}?>

  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
  <script>

     var screenwidth = <?=$screenwidth?>;
     var loc = '<?=$loc?>';
     var timessubmitted = <?=$timessubmitted?>;
     var isworkspc = <?=$isworkspc?>;
     var resolved = <?=$resolved?>;

     function $(el) {return parent.document.getElementById(el);}
     function $$(el){return document.getElementById(el);}

     function addLoadEvent(func) {
       //https://gist.github.com/dciccale/4087856
       var b=document,c='addEventListener';
       b[c]?b[c]('DOMContentLoaded',func):window.attachEvent('onload',func);
     }

     function olClose(locn) {
       var msg = checkdirt('clos');
       if(msg) {if(!confirm(msg)) return;}
       if(timessubmitted>0){
         try{
           var qry = loc;
           parent.reloadeditnotes(qry);
         }catch(e){parent.document.location.reload()}
       }
       if(<?=$userid?>==<?=$edituserid?>) releaselock();
       try{parent.myrevhidePopup()}catch(e){};
       try{parent.goback+=timessubmitted}catch(e){};
       parent.rlbfadeout();
     }

     function releaselock(){
       var xmlhttp = new XMLHttpRequest();
       xmlhttp.onreadystatechange=function(){
         if (xmlhttp.readyState==4 && xmlhttp.status==200){
           var ret = JSON.parse(xmlhttp.responseText);
           // do I need to do anything?
         }
       }
       //alert('/jsonmyrevtasks.php?task=relednote&ref='+loc);
       xmlhttp.open('GET', '/jsonmyrevtasks.php?task=relednote&ref='+loc,true);
       xmlhttp.send();
     }

     var checkforchanges = true;

     function valdel(f){
       if(confirm('\nYou are about to completely remove the editor notes for this verse.\nIt cannot be undone.\n\nAre you sure you want to do this?')){
         deleteall(CKEDITOR.instances.editdetails);
         f.editnote.value='';
         f.oper.value = "del";
         f.submit();
         return true;
       }else return false;
     }

     function validate(f){
       checkforchanges = false;
       if(isworkspc==0 && resolved==1 && $$('resolved').checked==true && CKEDITOR.instances.editdetails.getData()!==''){
         if(!confirm('This note is already marked as \'resolved.\'\nIf you want to add content without \'un-resolving\' it, click OK.\nOtherwise, click \'Cancel\' and uncheck the \'Resolved\' checkbox.')) return false;
       }
       f.oper.value = "sav";
       return true;
     }

     function checkdirt(what){
       if(!checkforchanges) return;
       var f = document.frm;
       var dirty = 0;
       if(f.dirt.value==1) dirty = 1;
       for (var i in CKEDITOR.instances) {
         if(CKEDITOR.instances[i].checkDirty()) dirty = 1;
       }
       msg = 'You have unsaved changes.\nIf you click \'OK\' those changes will be LOST.\n\nIf you WANT TO SAVE your notes, click Cancel, then the \'Save\' button.\n';
       if(dirty==1)return msg;
       else return '';
     }

     function setdirt(){
       var f = document.frm;
       f.dirt.value = 1;
       try{parent.sopschanges=1;}
       catch(e){}
     }

     var xdim,mnhit=109;
     function resizeditor(){
       ydim = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
       ydim-= $$('pverse').clientHeight+<?=(($isworkspc==1)?30:150)?>;
       ydim = Math.max(ydim, 400);
       if(isworkspc==0) ydim = ydim/2;
       xdim = $$('pverse').clientWidth-((ismobile==1)?11:11);  // messing..
       $$('mynotes').style.top = ($$('myheader').clientHeight-20) + 'px';
       try{
         CKEDITOR.instances.editdetails.resize(xdim, Math.max((ydim-140), 160));
       }catch(e){setTimeout('resizeditor()', 100)}
       try{
         //CKEDITOR.instances.editnote.resize(xdim, mnhit);
         $$('enwrapper').style.width = xdim+'px';
       }catch(e){}
       try{
         $$('oldcontent').style.width = (xdim-2)+'px';
       }catch(e){}
       if(isworkspc==0){
         $$('oldcontent').style.maxHeight = (ydim-40)+'px';
       }
       document.body.style.opacity = 1;
     }

  <?if(!$isworkspc && ($mnlocked==0)){?>
    CKEDITOR.replace( 'editnote',
    {
      extraPlugins: 'colorbutton,button,panelbutton,panel,floatpanel,autocorrect, autogrow',
      toolbar :
      [
        { name: 'basicstyles', items : [<?=(($superman==1)?'\'Source\', ':'')?> 'AutoCorrect','-','RemoveFormat','Bold','Italic','Underline','Strike','-','TextColor','BGColor' ] },
      ],
      removePlugins : 'elementspath, resize',
      resize_enabled : false,
      height : '48',
      autoGrow_minHeight : 48,
      autoGrow_maxHeight : 200,
      autoGrow_bottomSpace : 0,
      enterMode : CKEDITOR.ENTER_BR
    }
    );
    CKEDITOR.instances.editnote.on('change', function(){setdirt();});
    //CKEDITOR.instances.editnote.on('focus', function(){CKEDITOR.instances.editnote.resize(xdim, CKEDITOR.instances.editnote.container.$.clientHeight);});
    //CKEDITOR.instances.editnote.on('focus', function(){CKEDITOR.instances.editnote.resize(xdim, mnhit);});
    //CKEDITOR.instances.editnote.on('blur', function(){mnhit=CKEDITOR.instances.editnote.container.$.clientHeight;CKEDITOR.instances.editnote.resize(xdim, 48, true);});
    //CKEDITOR.instances.editnote.on('blur', function(){mnhit=CKEDITOR.instances.editnote.container.$.clientHeight;});
  <?}?>

    CKEDITOR.replace( 'editdetails',
    {
      forcePasteAsPlainText: 'allow-word',
      entities_greek: false,
      toolbarCanCollapse: false,
      extraPlugins: 'colorbutton,button,panelbutton,panel,floatpanel,autocorrect,smallcapify',
      toolbar :
      [
<?if($screenwidth>=480){?>
        { name: 'document',    items : [ <?=(($superman==1)?'\'Source\',':'')?>'AutoCorrect'] },
        { name: 'clipboard',   items : [ 'Undo','Redo' ] },
<?}?>
        { name: 'tools',       items : [ 'Maximize','Symbol' ] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','smallcapify','TextColor','-','BGColor','RemoveFormat' ] },
        { name: 'paragraph',   items : [ 'JustifyLeft','JustifyCenter' ] },
        { name: 'lists',       items : [ 'NumberedList','BulletedList','Outdent','Indent' ] },
<?if($screenwidth>=480){?>
        { name: 'styles',      items : [ 'Format'] },
        { name: 'links',       items : [ 'Image','Link','Unlink' ] },
<?}?>
      ],
      height : '340'
    }
    );
    CKEDITOR.instances.editdetails.on('change', function(){setdirt();});

    window.onload=setTimeout('resizeditor()', 100);
    window.onresize=function(){resizeditor();};

    function endfocus(ckei){
      var range = ckei.createRange();
      range.moveToElementEditEnd(range.root);
      ckei.getSelection().selectRanges([range]);
      ckei.focus();
    }

    function deleteall(ckei){
      ckei.setData('', function(){ckei.resetDirty();});
    }

    var ismobile = <?=$ismobile?>, cursorX, cursorY;
    var chngtxt = ((parent.chngtxt==1)?1:0);

<?if($isworkspc==0){?>

  prfcommnewtab = 1;
  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId  = 'view';
  addLoadEvent(findcomm.scan);

  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);
<?}?>
  setTimeout('try{endfocus(CKEDITOR.instances.editdetails)}catch(e){}', 300);

  function updatesops(){
    try{parent.extendfrompopup()}catch(e){};
  }

  addLoadEvent(updatesops);


</script>
</body>
</html>
<?
function getuser($uid){
  $row = rs('select ifnull(revusername, myrevname) author from myrevusers where myrevid > 0 and userid = '.$uid.' ');
  if($row)
    return $row[0];
  else
    return 'unknown';
}

function processlocalsqlcomm($com, $allownull, $default){
  global $site;
  $ret = trim($com);
  if($ret){
    $ret = preg_replace('#(\r\n)+#', ' ', $ret);    // replace crlf
    $ret = preg_replace('#<span dir="rtl"(.*?)>#i', '<span dir="rtl">', $ret);     // remove style from spandir tags
    $ret = preg_replace('#<span lan(.*?)>#', '<span>', $ret);     // remove language from span tags
    //$ret = preg_replace('#<span sty(.*?)>#', '<span>', $ret);     // remove style from span tags
    $ret = preg_replace('#<span style="font(.*?)>#', '<span>', $ret);     // remove font styles from span tags
    while(strpos($ret, '<span>')!== false){
      $ret = preg_replace('#<span>(.*?)</span>#', '$1', $ret);      // remove empty span tags
    }
    //$ret = preg_replace('#<div sty(.*?)>#', '<div>', $ret);       // remove style from div tags
    $ret = preg_replace('#<div>(.*)</div>#', '$1', $ret);         // remove empty div tags
    $ret = preg_replace('#<meta(.*?)>#', '', $ret);               // remove meta tags
    $ret = preg_replace('#<blockquote sty(.*?)>#', '<blockquote>', $ret); // remove style from blockquote tags
    $ret = preg_replace('#<ul sty(.*?)>#', '<ul>', $ret);         // remove style from ul tags
    $ret = preg_replace('#<ol sty(.*?)>#', '<ol>', $ret);         // remove style from ol tags
    $ret = preg_replace('#<li sty(.*?)>#', '<li>', $ret);         // remove style from li tags
    $ret = preg_replace('#<strong sty(.*?)>#', '<strong>', $ret); // remove style from strong tags
    $ret = preg_replace('#<em sty(.*?)>#', '<em>', $ret);         // remove style from em tags
    $ret = preg_replace('#<p style="fo(.*?)>#', '<p>', $ret);     // remove font style from p tags
    // 20151118 the next statement was added to handle pastes of indented text from Word.
    //$ret = preg_replace('#<p style="margin-left(.*?)>(.*?)</p>#', '<blockquote> <p>$2</p> </blockquote>', $ret);
    //$ret = preg_replace('#<p style="mar(.*?)>#', '<p>', $ret);    // remove remaining margin styles from p tags
    $ret = str_replace("“", "&ldquo;", $ret);
    $ret = str_replace("”", "&rdquo;", $ret);
    $ret = str_replace("‘", "&lsquo;", $ret);
    $ret = str_replace("’", "&rsquo;", $ret);

    $sdelim = '!~!';
    $ret = preg_replace('#&nbsp;+#', $sdelim, $ret);                       // replace hard spaces with placeholder
    $ret = preg_replace('#(<br />\s+'.$sdelim.')+#', '<br />&nbsp;', $ret);// put back hard space after <br />
    $ret = preg_replace('#('.$sdelim.')+#', ' ', $ret);                      // replace space placeholder
    $ret = preg_replace('#\s+#', ' ', $ret);        // replace repeating spaces
    $ret = preg_replace('#<p> #', '<p>', $ret);     // remove space following <p>, should be a better way
    $ret = preg_replace('#</p>\s+<p>#', '</p><p>', $ret);     // remove space between </p> <p>, should be a better way
    $ret = preg_replace('#<i>(.*?)</i>#', '<em>$1</em>', $ret);   // replace <i> tags with <em>
    $ret = preg_replace('#<b>(.*?)</b>#', '<strong>$1</strong>', $ret); // replace <b> tags with <strong>
    $ret = str_replace('</strong> <strong>', ' ', $ret); // remove unnecessary tags
    $ret = str_replace('</strong><strong>', '', $ret); // remove unnecessary tags
    //* Tidy
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
    $ret = str_replace(PHP_EOL, ' ', $tidy);
    $ret = str_replace(crlf, ' ', $ret);
    $ret = trim($ret);                              // re-trim
    if(strlen($ret)==0){                            // everything has been removed
      if($allownull) return 'null';
      else $ret = $default;
    }
    $ret = preg_replace('#\'#', '\\\'', $ret);      // escape single quotes
    return '\''.$ret.'\'';
  }else{
    if($allownull) return 'null';
    else return '\''.$default.'\'';
  }
}
function getcurrentdatetime($userTimeZone = 'America/New_York', $inctime = 0){
  switch($inctime){
  case 1:
    $format = 'n/j/y \a\t g:i a T'; break;
  case 2:
    $format = '_Ymd_Hi'; break;
  default:
    $format = '_Ymd'; break;
  }
  $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime(null ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}


