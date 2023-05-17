<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $resedit==0) die('unauthorized access');
$resourceid=((isset($_REQUEST['resourceid']))?$_REQUEST['resourceid']:'0');

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
$row=rs('select title from resource where resourceid = '.$resourceid.' ');
$stitle = '&ldquo;'.$row[0].'&rdquo;<br />is referenced by:';
$reloadparent=0;

if($oper=='savresource'){
  $mcnt = $_POST['mcnt'];
  for($ni=0;$ni<$mcnt;$ni++){
    if(isset($_POST['chkdel'.$ni]) && $_POST['chkdel'.$ni]==1){
      $parts = explode(',', $_POST['resource'.$ni]);
      $sql = 'delete from resourceassign
              where resourceid = '.$resourceid.'
              and testament = '.$parts[0].'
              and book = '.$parts[1].'
              and chapter = '.$parts[2].'
              and verse = '.$parts[3].' ';
      $delete = dbquery($sql);

    }
  }
  if($_POST['newresource']!==''){
    $tmp = str_replace(':', ' ', $_POST['newresource']);
    $tmp = str_replace('.', '', $tmp);
    $parts = explode(' ', $tmp);
    switch($parts[0]){
    case 'i': $t=2; $b=((isset($parts[1]))?$parts[1]:1); $c=1; $v=1; break;
    case 'a': $t=3; $b=((isset($parts[1]))?$parts[1]:1); $c=1; $v=1; break;
    case 'w':
      $t=4;
      $b=((isset($parts[1]))?$parts[1]:1);
      if(intval($b)==0){
        $row = rs('select book from book where title like \'%'.$b.'%\';');
        if($row) $b=$row[0]; else $b=0;
      }
      $c=1;
      $v=1;
      break;
    case 'b':
      $row = rs('select testament, book from book where aliases like \'%~'.$parts[1].'~%\' and testament in (0,1) ');
      if($row){$t=$row[0];$b=$row[1];$c=0;$v=0;}
      else{$t=1;$b=40;$c=99;$v=99;};
      break;
    default:
      $sql = 'select testament, book from book where aliases like \'%~'.$parts[0].'~%\' and testament in (0,1) ';
      $row = rs($sql);
      if($row){
        $t=$row[0];$b=$row[1];
        if(isset($parts[1]) && isset($parts[2])){$c=$parts[1];$v=$parts[2];}
        else{$c=0;$v=0;}
      }else{
        $t=1;$b=40;$c=99;$v=99;
      }
      break;
    }
    $sql = 'select 1 from resourceassign where testament = '.$t.' and book = '.$b.' and chapter = '.$c.' and verse = '.$v.' and resourceid = '.$resourceid.' ';
    $row = rs($sql);
    if($row){
      $sqlerr = 'Reference already exists';
    }else{
      $sql = 'insert into resourceassign (resourceid, testament, book, chapter, verse) values ('.
              $resourceid.', '.$t.', '.$b.', '.$c.', '.$v.') ';
      $insert = dbquery($sql);
    }
  }
  if($sqlerr=='') $sqlerr = datsav;
  $reloadparent=1;
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

<h2 style="text-align:center"><?=$stitle?></h2>
<div style="margin:0 auto;text-align:center">

<form name="frm" method="post" action="/resourceassign.php">
<table class="gridtable">
  <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
        <tr><th style="text-align:left;">&nbsp;Reference</th><th>Del</th></tr>

<?
$sql = 'select testament, book, chapter, verse
        from resourceassign
        where resourceid = '.$resourceid.'
        order by 1,2,3,4 ';
$med = dbquery($sql);
$ni=0;
while($row = mysqli_fetch_array($med)){
  $navstr = $row[0].','.$row[1].','.$row[2].','.$row[3];
  print('<tr><td style="text-align:left">'.fixrow($row).'<input type="hidden" name="resource'.$ni.'" value="'.$navstr.'" /></td>');
  print('<td><input type="checkbox" name="chkdel'.$ni.'" value="1" /></td></tr>');
  $ni++;
}
print('<tr><td colspan="2">New <input type="text" name="newresource" value="" autocomplete="off" /></td></tr>');
print('<tr><td colspan="2"><input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);"> ');
print('<input type="button" name="btnclose" value="Close" onclick="olClose(\''.$reloadparent.'\');"></td></tr>');

print('</table>');
?>
  <input type="hidden" name="mcnt" value="<?=$ni?>" />
  <input type="hidden" name="resourceid" value="<?=$resourceid?>" />
  <input type="hidden" name="oper" value="" />
</form>
<div style="width:320px;margin:10px auto;text-align:left;">
To assign resource:<br /><br />
Scripture ref, IE: "matt 1:1"<br />
Appendix: "a 1" assigns to Appx 1.<br />
Information: "i 1" assigns to "About".<br />
<?if($revws==1){?>
Word Study: "w sozo" assigns to ws on "sozo".<br />
<?}?>
<br />
If you type an unrecognized reference it will be appear as Matt 99:99. If that happens, delete the reference.
</div>
</div>
<script>

  function validate(f){
    var havedel = 0;
    for(var i=0;i<<?=$ni?>; i++){
      if(f['chkdel'+i].checked){
        havedel = 1;
        break;
      }
    }
    if(havedel==1){
      if(!confirm('Are you sure you want to delete the checked reference?')){return false;}
    }
    f.oper.value = 'savresource';
    return true;
  }

  function $(el) {return parent.document.getElementById(el);}

  function olClose(locn) {
    var ol = $("overlay");
    ol.style.display = 'none';
    if(locn==1) parent.document.frm.submit();
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }
  document.frm.newresource.focus();
  </script>
</body>
</html>
<?
function fixrow($r){
  $t = $r[0];
  $b = $r[1];
  $c = $r[2];
  $v = $r[3];
  $ret=getbooktitle($t,$b, 0);
  $href='/'.$ret;
  if($t<2 && $c>0){$ret.=' '.$c.':'.$v;$href.='/'.$c.'/'.$v.'/1';}
  if($t<2 && $c==0){$ret.=' Book Commentary';$href='/book'.$href.'/ct';}
  if($t==2){$href='/info/'.$b.'/ct';}
  if($t==3){$href='/appx/'.$b.'/ct';}
  if($t==4){$ret='WordStudy: '.$ret;$href='/word'.$href.'/ct';}
  $ret='<a href="'.$href.'" target="_blank">'.$ret.'</a>';
  return $ret;
}

?>

