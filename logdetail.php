<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || empty($superman) || $superman==0) die('unauthorized access');
$stitle = 'View Log Detail';

$pag  = (int) ((isset($_REQUEST['pag']))?$_REQUEST['pag']:0);
$test = (int) ((isset($_REQUEST['test']))?$_REQUEST['test']:1);
$book = (int) ((isset($_REQUEST['book']))?$_REQUEST['book']:40);
$chap = (int) ((isset($_REQUEST['chap']))?$_REQUEST['chap']:1);
$vers = (int) ((isset($_REQUEST['vers']))?$_REQUEST['vers']:1);

$daysback = ((isset($_REQUEST['daysback']))?$_REQUEST['daysback']:1);
//print('<small>pag='.$pag.'<br />test='.$test.'<br />book='.$book.'<br />chap='.$chap.'<br />vers='.$vers.'</small>');

switch($pag){
  case -2:$btitle = 'PDF Exports';break;
  case -3:$btitle = 'MSW Exports';break;
  case 20:$btitle = 'What\'s New';break;
  case 24:$btitle = 'BW Export';break;
  case 25:
  case 26:$btitle = 'REV Blog';break;
  case 32:$btitle = 'REV Backups';break;
  case 34:$btitle = 'Chronology';break;
  case 41:$btitle = 'eSword Export';break;
  case 42:$btitle = 'MySword Export';break;
  case 43:$btitle = 'BibleWorks Export';break;
  case 44:$btitle = 'theWord Export';break;
  case 45:$btitle = 'Export Looker';break;
  case 46:$btitle = 'DL ES Bible';break;
  case 47:$btitle = 'DL ES Comm';break;
  case 48:$btitle = 'DL ES CommV';break;
  case 49:$btitle = 'DL ES Appx';break;
  case 50:$btitle = 'DL MS Bible';break;
  case 51:$btitle = 'DL MS Comm';break;
  case 52:$btitle = 'DL MS CommV';break;
  case 53:$btitle = 'DL MS Appx';break;
  case 54:$btitle = 'DL BibleWks';break;
  case 55:$btitle = 'DL TW Bible';break;
  case 56:$btitle = 'DL TW Comm';break;
  case 57:$btitle = 'DL TW CommV';break;
  case 58:$btitle = 'DL TW Appx';break;
  case 59:$btitle = 'iBS Looker';break;
  case 60:$btitle = 'DL iBS Bible';break;
  case 61:$btitle = 'DL iBS Comm';break;
  case 62:$btitle = 'DL iBS CommV';break;
  case 63:$btitle = 'DL iBS Appx';break;
  case 65:$btitle = 'SwordSrchr looker';break;
  case 64:$btitle = 'DL SwordSrchr';break;
  case 66:$btitle = 'DL Accordance';break;
  case 67:$btitle = 'Accordance Looker';break;
  case 80:$btitle = 'DL MSW REV_Bible';break;
  case 81:$btitle = 'DL MSW REV_Commentary';break;
  case 82:$btitle = 'DL MSW REV_Appxs';break;
  case 89:$btitle = 'DL MSW All Zip';break;

  //case 46:$btitle = 'parsetest';break;
  //case 47:$btitle = 'Strongs';break;
  case 29:$btitle = 'Donate';break;
  case 39:$btitle = 'XML Export';break;
  case 101:
    switch($test){
    case -1:
      $btitle = 'Platform: mobile';break;
    case -2:
      $btitle = 'Platform: PC';break;
    case -3:
      $btitle = 'Platform: App';break;
    }
    break;
  case 200:
  case 201:
  case 202:
  case 203: $btitle = 'JSON_REV';break;
  case 300: $btitle = getlibrarytitle($test);break;
  case 450:
  case 451:
  case 452:$btitle = 'Hack Attempts';break;
  case 500: $btitle = 'Nav Menu Usage';break;
  default:$btitle = getbooktitle($test, $book, 0);
}

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
if($oper=='sav'){
  $ni=0;
  while(isset($_POST['edtlogid'.$ni])){
    if(isset($_POST['edtdelete'.$ni])){
      $sql = 'delete from viewlogs where logid = \''.$_POST['edtlogid'.$ni].'\' ';
      $update = dbquery($sql);
      if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
    }
    $ni++;
  }
  $sqlerr = $msg;
  if($sqlerr=='') $sqlerr = datsav;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
      print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center;"><?=$stitle?></h2>
<div style="margin:0 auto;text-align:center">
  For: &ldquo;<?=$btitle.(($test<2 && $chap>0)?' '.$chap.(($vers>0)?':'.$vers:''):'')?>&rdquo; &nbsp;Daysback: <?=$daysback?>
  </div>
<form name="frm" method="post" action="/logdetail.php">
  <table border="1" cellpadding="1" cellspacing="0" style="font-size:80%" align="center">
    <tr><td colspan="4"><?=(($pag==500)?getfeature($test):'&nbsp;')?><?=printsqlerr($sqlerr)?></td><td><a onclick="chkall()">all</a></td></tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="4">
        <input type="reset" name="btnreset2" value="Reset">
        <input type="submit" name="btnsubmit2" value="Submit" onclick="return validate(document.frm);">
        <input type="button" name="btnclose2" value="Close" onclick="olClose('');">
      </td>
    </tr>
    <tr><td>cnt</td><td>IP Address</td><td>Viewtime</td><td>Misc</td><td>Del</td></tr>
<?
$ni = 0;
$ipurl = 'http://whatismyipaddress.com/ip/';
$sql = 'select vl.logid, ifnull(cr.iplocation, vl.remoteip) loc, vl.remoteip, vl.viewtime, ifnull(vl.misc,\'-\') misc
        from viewlogs vl
        left join ipcrossref cr on (cr.ipaddress = vl.remoteip)
        where 1 = 1 ';
if($pag==-2) // PDF
  $sql .= 'and vl.page in (88,95,96,97,98,99) ';
else if($pag==-3) // MSW
  $sql .= 'and vl.page in (89,90,91,92,93,94) ';
else if($pag==300){ // library
  $sql .= 'and vl.page = '.$test.' ';
  //$test = 0;
  //$book = 0;
}else if($pag==101){ // platform
  switch($test){
  case -1: // mobile
    $sql .= 'and vl.userid >= 0 and vl.mobile=1 '; break;
  case -2: // pc
    $sql .= 'and vl.mobile=0 '; break;
  case -3: // from app
    $sql .= 'and vl.userid=-7 '; break;
  }
}else $sql .= 'and vl.page = '.$pag.' ';

if($pag==500 && $test != 0) $sql.= 'and vl.testament = '.$test.' ';
else if($pag!=300 && $test > 0) $sql.= 'and vl.testament = '.$test.' ';
if($pag!=300 && $book > 0) $sql.= 'and vl.book = '.$book.' ';
if($chap > 0) $sql.= 'and vl.chapter = '.$chap.' ';
if($vers > 0) $sql.= 'and vl.verse = '.$vers.' ';
$sql.= 'and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        order by vl.viewtime desc
        ';
//print($sql);
$logs = dbquery($sql);
while($row = mysqli_fetch_array($logs)){
  $theurl = '<a href="'.$ipurl.''.$row['remoteip'].'" target="_blank">'.$row['loc'].'</a>';
?>
    <tr>
      <td><?=$ni+1?></td>
      <td>
        <input type="hidden" name="edtlogid<?=$ni?>" value="<?=$row['logid']?>" />
        <?=$theurl?>
      </td>
      <td><?=converttouserdate($row['viewtime'], $timezone)?></td>
      <td><?=$row['misc']?></td>
      <td><input type="checkbox" name="edtdelete<?=$ni?>" value="1" /></td>
    </tr>
<?
  $ni++;
}
?>
    <tr>
      <td>&nbsp;</td>
      <td colspan="4">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
        <input type="button" name="btnclose" value="Close" onclick="olClose('');">
      </td>
    </tr>
  </table>
  <input type="hidden" name="pag" value="<?=$pag?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="daysback" value="<?=$daysback?>" />
  <input type="hidden" name="oper" value="" />
</form>
  <script>

     function validate(f){
       var haveset=0;
       for(var i=0;i<<?=$ni?>;i++){
         if(f['edtdelete'+i].checked) haveset = 1;
       }
       if(haveset==1 && !confirm('Are you sure you want to remove the checked log\'s?\n\nThis is not undoable')) return false;
       f.oper.value = 'sav';
       return true;
     }

     function $(el) {return parent.document.getElementById(el);}

     function olClose(locn) {
       var ol = $("overlay");
       ol.style.display = 'none';
       if(locn!='') parent.document.location.href=locn;
       setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
     }

     function chkall(){
       var f = document.frm;
       var chk = (!f.edtdelete0.checked);
       for(var i=0;i<<?=$ni?>;i++){
         f['edtdelete'+i].checked = chk;
       }
     }

  </script>
</body>
</html>
<?
function getlibrarytitle($id){
  $ret='missing: '.$id;
  $row = rs('select externalurl from resource where resourcetype = 7 and resourceid = '.$id.' ');
  if($row) $ret = substr($row[0], strrpos($row[0], '/')+1);
  return $ret;
}

function getfeature($itm){
  $ret='unknown';
  switch($itm){
  case -1: $ret = 'Initial';break;
  case  1: $ret = 'Bible Nav';break;
  case  2: $ret = 'Comm Nav';break;
  case  3: $ret = 'Appendix';break;
  case  4: $ret = 'Wordstudy';break;
  case  5: $ret = 'History';break;
  case  6: $ret = 'Information';break;
  case  8: $ret = 'QuickPrefs';break;
  case  9: $ret = 'Bookmarks';break;
  case 10: $ret = 'Notification';break;
  }
  return ' for <span style="color:red;">'.$ret.'</span>';
}
?>
