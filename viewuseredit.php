<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0) die('unauthorized access');

$logid = ((isset($_REQUEST['logid']))?$_REQUEST['logid']:0);
$idx = ((isset($_REQUEST['idx']))?$_REQUEST['idx']:-1);
$curtain = ((isset($_REQUEST['curtain']))?$_REQUEST['curtain']:0);
$stitle = 'View Changes for Edit';

$sql = 'select b.title, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid,
               ifnull(e.comment, \'-none-\') comment, e.whatsnew, ifnull(u.userid, 0) userid, ifnull(ifnull(u.revusername, u.myrevname), \'unknown\') username,
               ifnull(e.versdiff, \'-\') versdiff, ifnull(e.footdiff, \'-\') footdiff, ifnull(e.commfootdiff, \'-\') commfootdiff, ifnull(e.commdiff, \'-\') commdiff
        from editlogs e
        left join myrevusers u on (u.userid = e.userid and u.myrevid > 0)
        left join book b on (b.testament = e.testament and b.book = e.book)
        where e.logid = '.$logid.' ';


$row = rs($sql);
$comment  = $row['comment'];
if(left($comment,1)=='!') $comment = '<span style="color:red;">'.$comment.'</span>';
$whatsnew = $row['whatsnew'];
$versdiff = $row['versdiff'];
$footdiff = $row['footdiff'];
$commfootdiff = $row['commfootdiff'];
$commdiff = $row['commdiff'];
$edituser = $row['userid'];
$what     = fixedit($row);
$context  = fixview($row);

$roww = rs('select flagcomment from editlogsviewed where logid = '.$logid.' and userid = '.$userid.' ');
if($roww) $flagcomment = $roww[0];
else $flagcomment = '';

if($curtain==1){
  $versdiff = lookbehind($versdiff);
  $commdiff = lookbehind($commdiff);
  $footdiff = lookbehind($footdiff);
  $commfootdiff = lookbehind($commfootdiff);
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Edits</title>
  <meta charset="utf-8">
  <style>
    /* for highlighting diffs */
    del {text-decoration: line-through;color:#EA3C18;}
    ins {text-decoration:underline;color:#17922A;}
  </style>
  <?if($colortheme>0){
      print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<form name="frm" action="/viewuseredit.php" method="post">

<h2><?=$stitle?></h2>
  <input type="button" name="btnclose" value="Close" onclick="olClose('');">
  <small><input type="checkbox" name="curtain" id="curtain" value="1"<?=fixchk($curtain);?> onclick="document.frm.submit();"> <label for="curtain">View behind HTML</label>
  <br />
  <table style="width:96%;font-size:100%;padding:2px;">
    <tr>
      <td>
        <?=$what.$context?>
        Edit date: <?=converttouserdate($row['editdate'], $timezone)?><br />
        Edit By: <?=$row['username']?><br />
        Comment: <?=$comment?></small>
      </td>
      <td>
        Leave a comment for this edit.<br />It will automatically be flagged.<br />
        <textarea name="flagcomment" id="flagcomment" style="width:80%;height:2.3em;"><?=$flagcomment?></textarea><br />
        <input type="button" name="btns" value="save" onclick="handleflag(<?=$logid.','.$idx?>);" />
        &nbsp;<a onclick="$$('flagcomment').select();" style="color:red;cursor:pointer;"><strong>X</strong></a>&nbsp;
        <span id="flagresult" style="color:green"></span>
      </td>
    </tr>
    </table>
  <table style="width:96%;font-size:100%;padding:2px;">
<?

$finddiff = '<br /><a onclick="findins();" style="cursor:pointer;color:#17922A;">&lt;ins></a><br ><a onclick="finddel();" style="cursor:pointer;color:#EA3C18;">&lt;del>';

$padstr = '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;';
// Rob, make this better!
switch($row['page']){
case 1: // editverscomm
    $tdtxt = (($ismobile)?'Verse<br />Diffs':'Verse Text<br />Differences');
    $fttxt = (($ismobile)?'Ftnot<br />Diffs':'Vs Footnote<br />Differences');
    if($versdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;">'.$versdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    if($footdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.$fttxt.'</td><td style="border:1px solid gray;">'.str_replace('~~','<br />', $footdiff).'</td></tr>'.crlf);
    }
    $tdtxt = (($ismobile)?'Comm<br />Diffs':'Commentary<br />Differences').(($commdiff!='-')?$finddiff:'');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div id="diff" style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    if($commfootdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.(($ismobile)?'Ftnot<br />Diffs':'Footnote<br />Differences').'</td><td style="border:1px solid gray;">'.str_replace('~~','<br />', $commfootdiff).'</td></tr>'.crlf);
    }
    break;
case 6: // editbook
    $tdtxt = (($ismobile)?'BComm<br />Diffs':'Book Commentary<br />Differences').(($commdiff!='-')?$finddiff:'');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div id="diff" style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    if($commfootdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.(($ismobile)?'Ftnot<br />Diffs':'Footnote<br />Differences').'</td><td style="border:1px solid gray;">'.str_replace('~~','<br />', $commfootdiff).'</td></tr>'.crlf);
    }
    break;
case 7: // editor notes
    $tdtxt = (($ismobile)?'mnote<br />Diffs':'Margin note<br />Differences');
    if($versdiff!=='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;">'.$versdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    $tdtxt = (($ismobile)?'Dtls<br />Diffs':'Note Detail<br />Differences');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    break;
case 8: // editappxintro
    $tdtxt = (($ismobile)?'App/Int<br />Diffs':'Appendix/Intro<br />Differences').(($commdiff!='-')?$finddiff:'');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div id="diff" style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    if($commfootdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.(($ismobile)?'Ftnot<br />Diffs':'Footnote<br />Differences').'</td><td style="border:1px solid gray;">'.str_replace('~~','<br />', $commfootdiff).'</td></tr>'.crlf);
    }
    break;
case 34:// chronedit
    $tdtxt = (($ismobile)?'Titl<br />Diffs':'Event Title<br />Differences');
    if($versdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;">'.$versdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    $tdtxt = (($ismobile)?'LDesc<br />Diffs':'Long Description<br />Differences').(($commdiff!='-')?$finddiff:'');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div id="diff" style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    if($commfootdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.(($ismobile)?'Ftnot<br />Diffs':'Footnote<br />Differences').'</td><td style="border:1px solid gray;">'.str_replace('~~','<br />', $commfootdiff).'</td></tr>'.crlf);
    }
    break;
case 37:// resource
    $tdtxt = (($ismobile)?'Titl<br />Diffs':'Event Title<br />Differences');
    if($versdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;">'.$versdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    $tdtxt = (($ismobile)?'Desc<br />Diffs':'Description<br />Differences');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    if($commfootdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.(($ismobile)?'Ftnot<br />Diffs':'Footnote<br />Differences').'</td><td style="border:1px solid gray;">'.str_replace('~~','<br />', $commfootdiff).'</td></tr>'.crlf);
    }
    break;
case 51:// bibliography
    $tdtxt = (($ismobile)?'Author<br />Diffs':'Author<br />Differences');
    if($commfootdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;width:80%;">'.$commfootdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    /*
    $tdtxt = (($ismobile)?'Short<br />Diffs':'Shortname<br />Differences');
    if($footdiff!='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;width:80%;">'.$footdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    */
    $tdtxt = (($ismobile)?'Entry<br />Diffs':'Entry<br />Differences');
    if($versdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;width:80%;">'.$versdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    break;
case 27: // blog
    print('<tr><td style="vertical-align:top;">REV Blog</td><td style="vertical-align:top;color:red;border:1px solid gray;">Sorry, the system is not currently tracking edits for the REV Blog.</td></tr>'.crlf);
    break;
case 310: // reviewer notes
    $tdtxt = (($ismobile)?'mnote<br />Diffs':'Margin note<br />Differences');
    if($versdiff!=='-'){
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="border:1px solid gray;">'.$versdiff.'</td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">None</td></tr>'.crlf);
    }
    $tdtxt = (($ismobile)?'Dtls<br />Diffs':'Note Detail<br />Differences');
    if($commdiff!='-'){
      print('<tr><td style="vertical-align:top;padding-top:1em;">'.$tdtxt.'</td><td style="border:1px solid gray;"><div style="width:100%;max-height:300px;overflow:auto;">'.$commdiff.$padstr.'</div></td></tr>'.crlf);
    }else{
      print('<tr><td style="vertical-align:top;">'.$tdtxt.'</td><td style="vertical-align:top;color:red;border:1px solid gray;">No changes</td></tr>'.crlf);
    }
    break;
default:
    print('<tr><td style="vertical-align:top;">Lost</td><td style="vertical-align:top;color:red;border:1px solid gray;">I\'m lost</td></tr>'.crlf);
}



?>


  </table>
<input type="button" name="btnclose2" value="Close" onclick="olClose('');">
<input type="hidden" name="logid" value="<?=$logid?>">
<p><small>(Note: There are occasionally situations where the differences highlighted are not accurate. Try checking the "View behind HTML" checkbox.)</small></p>
</form>

<script>
  function $(el) {return parent.document.getElementById(el);}
  function $$(el){return document.getElementById(el);}
  function olClose(locn) {
    var ol = $("overlay");
    ol.style.display = 'none';
    if(locn!='') parent.document.location.href=locn;
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }
  <?$gobak=(($curtain==0)?2:1);?>
  try{parent.goback+=<?=$gobak?>}
  catch(e){}

  function handleflag(logid,idx){
    //alert(idx);
    var frm = document.frm;
    var cmt = frm.flagcomment.value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        var flg = ret.flagged
    try{
          var td = $('eflag'+idx);
          var tdc= ((flg==0)?'transparent':'#ffdddd');
          td.style.backgroundColor=tdc;
          var fg = $('iflag'+idx);
          var sfg= ((flg==1)?'100':'30');
          fg.style.opacity=sfg+'%';
      }catch(e){}
        $$('flagresult').innerHTML = 'success!';
      }
    }
    xmlhttp.open('GET','/jsonflagedit.php?id='+logid+'&cmt='+encodeURI(cmt)+'&doocmt=1',true);
    xmlhttp.send();
  }

  var insidx = 0;
  var delidx = 0;
  try{
    var inss = $$('diff').getElementsByTagName("ins");
    var arins = Array.prototype.slice.call(inss);
    var dels = $$('diff').getElementsByTagName("del");
    var ardel = Array.prototype.slice.call(dels);
  } catch(e){}

  function findins(){
    try{
      arins[insidx].scrollIntoView({block:'start', behavior:'smooth'});
      insidx++;
      //if(insidx==arins.length){insidx=0;setTimeout("alert('Last insertion.');", 999)}
      if(insidx==arins.length) insidx=0;
    } catch(e){alert('There are no insertions.');}
  }
  function finddel(){
    try{
      ardel[delidx].scrollIntoView({block:'start', behavior:'smooth'});
      delidx++;
      //if(delidx==ardel.length){delidx=0;setTimeout("alert('Last deletion.');", 999)}
      if(delidx==ardel.length) delidx=0;
    } catch(e){alert('There are no deletions.');}
  }

</script>

</body>
</html>
<?
$row = rs('select \'x\' from editlogsviewed where userid = '.$userid.' and loguserid = '.$edituser.' and logid = '.$logid.' ');
if(!$row) $q = dbquery('insert into editlogsviewed (userid, loguserid, logid) values ('.$userid.','.$edituser.','.$logid.') ');

function fixedit($r){
  $str=' target="_blank" title="Click to view.  Opens new window/tab"';
  $title = str_replace(' ', '-', $r['title']??'');
  switch($r['page']){
  case 1: // verse/commentary
    $ret = 'Commentary for <a href="/'.$title.'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['title'].' '.$r['chapter'].':'.$r['verse'].'</a>';
    break;
  case 6: // book/commentary
    $ret = 'book/commentary for <a href="/book/'.$title.'/1"'.$str.'>'.$r['title'].'</a>';
    break;
  case 8: // appx/intro
    $ret = 'appx/intro/ws for <a href="/'.(($r['testament']==2)?'info':(($r['testament']==3)?'appx':'word')).'/'.$r['book'].'/1"'.$str.'>'.$r['title'].'</a>';
    break;
  case 27: // blog
    $ret = '<a href="/blog/'.$r['book'].'/1"'.$str.'>Blog Entry</a>';
    break;
  case 34: // chronology
    $ret = '<a href="/chronology/'.$r['comment'].'"'.$str.'>Chronology Entry for '.friendlyyear($r['comment']).'</a>';
    break;
  case 37: // resource
    $ret = '<a href="/resource?factive=0&filter=~'.$r['comment'].'"'.$str.'>Resource: '.$r['comment'].'</a>';
    break;
  case 51: // bib
    if($r['chapter']==1)
      $ret = '<a href="/bibliography"'.$str.'>Bibliography</a>';
    else
      $ret = '<a href="/abbreviations"'.$str.'>Abbreviations</a>';
    break;
  case 7:
    if($r['testament']==0 && $r['book']==0) $ret = '<span style="color:red;">Editor Workspace</span>';
    else $ret = 'Editor note for <a href="/'.$title.'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['title'].' '.$r['chapter'].':'.$r['verse'].'</a>';
    break;
  case 310:
    if($r['testament']==0 && $r['book']==0) $ret = '<span style="color:red;">Reviewer Workspace</span>';
    else $ret = 'Reviewer note for <a href="/'.$title.'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['title'].' '.$r['chapter'].':'.$r['verse'].'</a>';
    break;
  default:
    $ret = 'unknown';
  }
  return $ret.'<br />';
}

function fixview($r){
  $str=' target="_blank" title="Click to view.  Opens new window/tab"';
  switch($r['page']){
  case 1: // verse/commentary
  case 7: // editor note
    if($r['testament']==0 && $r['book']==0) $ret = '';
    else $ret = 'Verse context for <a href="/'.str_replace(' ', '-', $r['title']).'/'.$r['chapter'].'/nav'.$r['verse'].'/1"'.$str.'>'.$r['title'].' '.$r['chapter'].':'.$r['verse'].'</a><br />';
    break;
  default:
    $ret = '';
  }
  return $ret;
}

function lookbehind($txt){
  $txt = str_replace('</p>', '</p>[[mybr2]]', $txt);
  //$txt = str_replace('<ol', '[[mybr2]]<ol', $txt);
  $txt = str_replace('<li>', '[[mybr]]<li>', $txt);
  $txt = str_replace('<ins>', '[[ins]]', $txt);
  $txt = str_replace('</ins>', '[[/ins]]', $txt);
  $txt = str_replace('<del>', '[[del]]', $txt);
  $txt = str_replace('</del>', '[[/del]]', $txt);
  $txt = str_replace('<', '&lt;', $txt);
  $txt = str_replace('[[mybr2]]', '<br /><br />', $txt);
  $txt = str_replace('[[mybr]]', '<br />', $txt);
  $txt = str_replace('[[ins]]', '<ins>', $txt);
  $txt = str_replace('[[/ins]]', '</ins>', $txt);
  $txt = str_replace('[[del]]', '<del>', $txt);
  $txt = str_replace('[[/del]]', '</del>', $txt);
  return $txt;
}
function friendlyyear($yr){
  if(trim($yr)=='') return '';
  if($yr<0) $ret = abs($yr).' BC';
  else $ret = abs($yr).' AD';
  return $ret;
}

?>
