<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
$page=-1;  // don't set page in functions.php line 397, causes history to fire
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || ($canedit==0 && $appxedit==0)) die('unauthorized access');

$test = ((isset($_REQUEST['test']))?$_REQUEST['test']:1);
$book = ((isset($_REQUEST['book']))?$_REQUEST['book']:40);
$chap = ((isset($_REQUEST['chap']))?$_REQUEST['chap']:1);
$bname = getbooktitle($test, $book, 0);
$stitle = 'Manage '.$bname.' Outline';
$sqlerr ='&nbsp;';$msg='';
$dirty=0;

if(isset($_REQUEST['oper']) && $_REQUEST['oper']=='sav'){
  $itmcnt = $_REQUEST['itmcnt'];
  $qry = dbquery('delete from outline where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' ');
  for($ni=0;$ni<$itmcnt;$ni++){
    if(!isset($_REQUEST['del'.$ni])){
      $vers = processsqlnumb($_REQUEST['vers'.$ni], 180, 0, 1);
      $levl = processsqlnumb($_REQUEST['level'.$ni], 1, 0, 0);
      $head = processsqltext($_REQUEST['heading'.$ni], 290, 0, 'missing heading');
      $refr = processsqltext($_REQUEST['vsref'.$ni], 20, 0, $chap.':'.$vers);
      $link = processsqlnumb(((isset($_REQUEST['link'.$ni]))?$_REQUEST['link'.$ni]:0), 1, 0, 0);
      $outl = processsqlnumb(((isset($_REQUEST['outl'.$ni]))?$_REQUEST['outl'.$ni]:0), 1, 0, 0);
      $sql = 'insert into outline (testament, book, chapter, verse, heading, reference, level, link, inoutline)
              values
              ('.$test.','.$book.','.$chap.','.$vers.','.$head.','.$refr.','.$levl.','.$link.','.$outl.') ';
      $qry = dbquery($sql);
      $msg.= $sqlerr;$sqlerr='';
    }
  }
  if(isset($_REQUEST['vers'.$ni]) && trim($_REQUEST['vers'.$ni]) !=''){
    $vers = processsqlnumb($_REQUEST['vers'.$ni], 180, 0, 1);
    $levl = processsqlnumb($_REQUEST['level'.$ni], 1, 0, 0);
    $head = processsqltext($_REQUEST['heading'.$ni], 290, 0, 'missing heading');
    $refr = processsqltext($_REQUEST['vsref'.$ni], 20, 0, $chap.':'.$vers);
    $link = processsqlnumb(((isset($_REQUEST['link'.$ni]))?$_REQUEST['link'.$ni]:0), 1, 0, 0);
    $outl = processsqlnumb(((isset($_REQUEST['outl'.$ni]))?$_REQUEST['outl'.$ni]:0), 1, 0, 0);
    $sql = 'insert into outline (testament, book, chapter, verse, heading, reference, level, link, inoutline)
            values
            ('.$test.','.$book.','.$chap.','.$vers.','.$head.','.$refr.','.$levl.','.$link.','.$outl.') ';
    $qry = dbquery($sql);
  }
  $msg.= $sqlerr;$sqlerr=$msg;
  if($sqlerr=='' || $sqlerr=='&nbsp;') $sqlerr = datsav;
  $dirty=1;
}

$pagination = pagination($test, $book, $chap);


?>
<!DOCTYPE html>
<html>
<head>
  <title>Edits</title>
  <meta charset="utf-8">
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <link rel="stylesheet" type="text/css" href="/includes/style.min.css?v=<?=$fileversion?>" />
<?if($colortheme>0){?>
  <link rel="stylesheet" type="text/css" href="/includes/style<?=$colors[0]?>.css?v=<?=$fileversion?>" />'.crlf);
<?}?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;text-align:center;">

<form name="frm" action="/manageheadings.php" method="post">

<h2><?=$stitle?></h2>
  <a href="/outline/<?=$bname?>" target="_blank">Outline</a><small>(new tab)</small>
  <?=$pagination?>
  <table style="font-size:90%;padding:4px;margin:0 auto;">
    <tr><td colspan="7" style="text-align:left;"><?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td style="vertical-align:bottom;">Vers</td>
      <td style="vertical-align:bottom;">Level</td>
      <td style="vertical-align:bottom;">Heading</td>
      <td style="vertical-align:bottom;">Vs Ref</td>
      <td style="vertical-align:bottom;">In<br />Bible</td>
      <td style="vertical-align:bottom;">In<br />Outln</td>
      <td style="vertical-align:bottom;">Del</td>
    </tr>
<?
$sql = 'select verse, reference, level, heading, link, inoutline
        from outline
        where testament = '.$test.'
        and book = '.$book.'
        and chapter = '.$chap.'
        order by testament, book, chapter, verse, level ';
$heads = dbquery($sql);
$ni=0;
while($row = mysqli_fetch_array($heads)){
  print('<tr>');
  print('<td><input type="text" name="vers'.$ni.'" id="vers'.$ni.'" value="'.$row['verse'].'" style="width:20px;text-align:right;" autocomplete="off" onchange="setdirt();" /></td>');
  print('<td><input type="radio" name="level'.$ni.'" id="level'.$ni.'" value="0"'.fixrad($row['level']==0).' onclick="setdirt();" /><input type="radio" name="level'.$ni.'" value="1"'.fixrad($row['level']==1).' onclick="setdirt();" /></td>');
  print('<td><input type="text" name="heading'.$ni.'" id="heading'.$ni.'" value="'.$row['heading'].'" style="width:350px;" autocomplete="off" onchange="setdirt();" />');
  print('<a onclick="doinput($$(\'heading'.$ni.'\'),\'&rsquo;\',\'\');" title="click to insert apostrophe"> &rsquo; </a>');
  print('<a onclick="doinput($$(\'heading'.$ni.'\'),\'&ldquo;\',\'&rdquo;\');" title="click to insert smart quotes">&ldquo;&rdquo;</a></td>');
  print('<td><input type="text" name="vsref'.$ni.'" value="'.$row['reference'].'" style="width:50px;" autocomplete="off" onchange="setdirt();" /></td>');
  print('<td><input type="checkbox" name="link'.$ni.'" value="1"'.fixchk($row['link']).' onclick="setdirt();" /></td>');
  print('<td><input type="checkbox" name="outl'.$ni.'" value="1"'.fixchk($row['inoutline']).' onclick="setdirt();" /></td>');
  print('<td><input type="checkbox" name="del'.$ni.'" value="1" onclick="setdirt();" /></td>');
  print('</tr>'.crlf);

  $ni++;
}

//print('<tr><td colspan="7" style="text-align:left;font-size:80%;">new</td></tr>'.crlf);
print('<tr>');
print('<td style="padding-top:9px;"><input type="text" name="vers'.$ni.'" id="vers'.$ni.'" value="" style="width:20px;text-align:right;" autocomplete="off" onchange="setdirt();" /></td>');
print('<td style="padding-top:9px;"><input type="radio" name="level'.$ni.'" id="level'.$ni.'" value="0" onclick="setdirt();" /><input type="radio" name="level'.$ni.'" id="level'.$ni.'" value="1" checked="checked" onclick="setdirt();" /></td>');
print('<td style="padding-top:9px;"><input type="text" name="heading'.$ni.'" id="heading'.$ni.'" value="" style="width:350px;" autocomplete="off" onfocus="chkfocus(this)" onblur="chkblur(this)" onchange="setdirt();" />');
print('<a onclick="doinput($$(\'heading'.$ni.'\'),\'&rsquo;\',\'\');" title="click to insert apostrophe"> &rsquo; </a>');
print('<a onclick="doinput($$(\'heading'.$ni.'\'),\'&ldquo;\',\'&rdquo;\');" title="click to insert smart quotes">&ldquo;&rdquo;</a></td>');
print('<td style="padding-top:9px;"><input type="text" name="vsref'.$ni.'" value="" style="width:50px;" autocomplete="off" onchange="setdirt();" /></td>');
print('<td style="padding-top:9px;"><input type="checkbox" name="link'.$ni.'" value="1" checked="checked" onclick="setdirt();" /></td>');
print('<td style="padding-top:9px;"><input type="checkbox" name="outl'.$ni.'" value="1" checked="checked" onclick="setdirt();" /></td>');
print('<td style="padding-top:9px;"><input type="hidden" name="del'.$ni.'" value="0" />&nbsp;</td>');
print('</tr>'.crlf);

?>
    <tr>
      <td colspan="7">
        <input type="reset" name="btnreset" value="Reset" onclick="document.frm.dirt.value=0;" />
        <input type="submit" name="btnsbmt" value="Submit" onclick="return validate(document.frm);">
        <input type="button" name="btnclose2" value="Close" onclick="olClose('');">
      </td>
    </tr>
    <tr><td colspan="7">&nbsp;</td></tr>
    <tr><td colspan="7">&nbsp;</td></tr>
    <tr><td colspan="7" style="text-align:left;">
      Some instructions:<br />
      To add a line break (&lt;br />) use [br].<br />
      To make a heading grayed out (not in text, see <a href="/mark/16/head9" target="_blank">Mark 16:9</a>), begin it with a ~ (tilde).
    </td></tr>
    <tr><td colspan="7" style="text-align:left;">&nbsp;<br />Questions:<br />should links in the outline open in a new tab?</td></tr>

  </table>
<input type="hidden" name="test" value="<?=$test?>">
<input type="hidden" name="book" value="<?=$book?>">
<input type="hidden" name="chap" value="<?=$chap?>">
<input type="hidden" name="oper" value="">
<input type="hidden" name="itmcnt" value="<?=$ni?>">
<input type="hidden" name="dirt" value="0">
</form>

<script>
  var itmcnt = <?=$ni?>;
  var dirty = <?=$dirty?>;

  function olClose(locn) {
    if(document.frm.dirt.value == 1){
      if(!confirm('You have unsaved changes.\nIf you continue those changes will be LOST.\n\nIf you want to save your changes, click Cancel.\nIf you do not want to save your changes, click OK.')) return false;
    }
    var ol = $("overlay");
    ol.style.display = 'none';
    try{
      if(dirty==1){
        parent.checkforchanges = false;
        parent.document.frm.oper.value = 'savvrs';
        parent.document.frm.submit();
      }
    }catch(e){if(dirty==1) parent.document.location.reload();}
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }

  function updatesops(){
    try{parent.extendfrompopup()}catch(e){};
  }
  addLoadEvent(updatesops);

  function validate(f){
    var arr = [];
    for(var i=0;i<itmcnt;i++){
      var aval = trim($$('vers'+i).value)+'_';
      if(document.getElementsByName('level'+i)[0].checked) aval+='0';
      else aval+='1';
      arr.push(aval);
    }
    aval = $$('vers'+itmcnt).value+'_';
    if(document.getElementsByName('level'+itmcnt)[0].checked) aval+='0';
    else aval+='1';

    for(var i=0;i<itmcnt;i++){
      if(aval == arr[i]){
        var tmp = arr[i];
        var atmp = tmp.split('_');
        $$('vers'+itmcnt).focus();
        $$('vers'+itmcnt).select();
        alert('You have two verses at the same level for verse '+atmp[0]);
        return false;
      }
    }

    f.oper.value = 'sav';
    return true;
  }

  function dochap(c){
    document.frm.chap.value=c;
    document.frm.submit();
  }

  function chkfocus(ctl){
    ctl.value = trim(ctl.value);
    if(ctl.value==defhead) ctl.value='';
  }
  function chkblur(ctl){
    ctl.value = trim(ctl.value);
    if(ctl.value=='') ctl.value=defhead;
  }

  function trim(s)  { return ltrim(rtrim(s)) }
  function ltrim(s) { return s.replace(/^\s+/g, "") }
  function rtrim(s) { return s.replace(/\s+$/g, "") }
  function $(el) {return parent.document.getElementById(el);}
  function $$(el) {return document.getElementById(el);}
  function setdirt(){
    var f = document.frm;
    f.dirt.value = 1;
    try{parent.sopschanges=1;}
    catch(e){}
  }


  try{parent.goback+=1}
  catch(e){}

  var defhead = 'New heading, make sure to enter vers..';
  setTimeout("$$('heading'+itmcnt).value = defhead", 200);

</script>

</body>
</html>
<?
function pagination($t,$b,$c){
  global $colors;
  $row = rs('select chapters from book where testament = '.$t.' and book = '.$b.' ');
  $numchaps = $row[0];
  $ret='<div style="text-align:center;margin:8px auto;font-size:90%;">';
  if($c-1 > 0){
    $ret.= ' <a onclick="dochap('.($c-1).');">&laquo;prev</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">&laquo;prev</span> ';
  }
  $ret.= 'Chapter ';
  $ret.= '<select onchange="dochap(this.selectedIndex+1);">';
  for($ni=1;$ni<=$numchaps;$ni++){
    $ret .= '<option'.fixsel($ni, $c).'>'.$ni.'</option>';
  }
  $ret.= '</select>';
  $ret.= ' of '.$numchaps;
  if($c+1 <= $numchaps){
    $ret.= ' <a onclick="dochap('.($c+1).');">next&raquo;</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">next&raquo;</span> ';
  }
  $ret.= '</div>';
  return $ret;
}


?>
