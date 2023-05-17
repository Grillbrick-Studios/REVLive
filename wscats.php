<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $canedit==0) die('unauthorized access');

$sqlerr = '';
$submitted=0;
$oper=((isset($_REQUEST['oper']))?$_REQUEST['oper']:'xx');
$ccnt=((isset($_REQUEST['ccnt']))?$_REQUEST['ccnt']:0);

if($oper=='sav'){
  for($ni=0;$ni<=$ccnt;$ni++){
    $wscatid = processsqlnumb($_REQUEST['wscatid'.$ni], 99,0,0);
    if(isset($_REQUEST['del'.$ni]) && $_REQUEST['del'.$ni]==1){
      $upd = dbquery('update book set wscatid = 0 where testament = 4 and wscatid = '.$wscatid.' ');
      $upd = dbquery('delete from wscats where wscatid = '.$wscatid.' ');
    }else{
      $wscat   = processsqltext($_REQUEST['wscat'.$ni], 20,0,'missing!');
      $sqn     = processsqlnumb($_REQUEST['sqn'.$ni], 99,0,99);
      if($ni < $ccnt){
        $sql = 'update wscats set wscat = '.$wscat.', sqn = '.$sqn.' where wscatid = '.$wscatid.' ';
        $upd = dbquery($sql);
      }else{
        if($wscatid==-1 && trim($_REQUEST['wscat'.$ni]) != ''){
          $sql = 'insert into wscats (wscat, sqn) values ('.$wscat.', '.$sqn.') ';
          $upd = dbquery($sql);
        }
      }
    }
  }
  if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
  $submitted=1;
}
$stitle = 'Study Categories';

?>
<!DOCTYPE html>
<html>
<head>
  <title>Study Categories</title>
  <link rel="stylesheet" type="text/css" href="/includes/style<?=$colors[0]?>.css?v='.$fileversion.'" />
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<div style="text-align:center;padding:7px 0;">
<h3 style="display:inline-block;width:70%;text-align:center;margin:0;"><?=$stitle?></h3>
<span style="float:right;cursor:pointer;margin-right:8px;" onclick="olClose(0);"><img src="/i/redx.png" style="width:20px;" alt="" /></span>
</div>
<div style="text-align:center;margin:12px  auto;">
<?=printsqlerr($sqlerr.'<br />')?>
</div>
<form name="frm" method="post" action="/wscats.php">
<?

print('<table style="text-align:center;margin:10px auto;">');
print('<tr>');
print('<td>Category</td>');
print('<td>Sqn</td>');
print('<td>Use</td>');
print('<td>Del</td>');
print('</tr>'.crlf);
$sql = 'select wscatid, wscat, sqn from wscats order by sqn ';
$sql = 'select wscatid, wscat, sqn, (select count(*) from book b where b.wscatid = wsc.wscatid) cnt from wscats wsc order by sqn ';

$cats = dbquery($sql);
$ni=0;
while($row = mysqli_fetch_array($cats)){
  print('<tr>');
  print('<td><input type="hidden" name="wscatid'.$ni.'" value="'.$row[0].'">');
  print('<input type="text" name="wscat'.$ni.'" value="'.$row[1].'" size="12" maxlength="20" autocomplete="off" onchange="document.frm.dirty.value=1;"></td>');
  print('<td><input type="text" name="sqn'.$ni.'" value="'.$row[2].'" size="2" maxlength="2" autocomplete="off" onchange="document.frm.dirty.value=1;"></td>');
  print('<td>'.$row[3].'</td>');
  print('<td><input type="checkbox" name="del'.$ni.'" id="del'.$ni.'" value="1" onclick="document.frm.dirty.value=1;"></td>');
  print('</tr>'.crlf);
  $ni++;
}
  print('<tr>');
  print('<td><input type="hidden" name="wscatid'.$ni.'" value="-1">');
  print('<input type="text" name="wscat'.$ni.'" value="" size="12" maxlength="20" autocomplete="off" onchange="document.frm.dirty.value=1;"></td>');
  print('<td><input type="text" name="sqn'.$ni.'" value="" size="2" maxlength="2" autocomplete="off" onchange="document.frm.dirty.value=1;"></td>');
  print('<td colspan="2">&nbsp;<input type="hidden" name="del'.$ni.'" value="0"></td>');
  print('</tr>'.crlf);
print('</table>');
?>

  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirty" value="" />
  <input type="hidden" name="ccnt" value="<?=$ni?>" />

  <p style="text-align:center;margin:9px 0;">
    <input type="submit" name="btnsubmit" class="gobackbutton" style="cursor:pointer;width:80px;" value="Save" onclick="return validate(document.frm);" />
    <input type="button" name="btnclosee" class="gobackbutton" style="cursor:pointer;width:80px;" value="Done" onclick="olClose(0);">&nbsp;&nbsp;
  </p>
</form>
<script>

  var submitted = <?=$submitted?>;
  function $$(el) {return parent.document.getElementById(el);}
  function olClose(locn) {
    var msg = checkdirt(document.frm);
    if(msg) {if(confirm(msg)) return;}
    if(submitted==0){
      var ol = $$("overlay");
      ol.style.display = 'none';
      setTimeout('$$("ifrm").src="/includes/empty.htm"', 200);
    }else parent.document.frm.submit();
  }

  function validate(f){
    var havedel=0;
    for(var i=0;i<<?=$ni?>;i++){
      if($('del'+i).checked) {havedel=1;break}
    }
    if(havedel==1){
      if(!confirm('Are you sure you want to delete the checked categorie(s)')) return false;
    }
    f.oper.value = "sav";
    return true;
  }
  function checkdirt(f){
    if(f.dirty.value==1){
      return '\nYou have unsaved changes!\nIf you want to save them, click \'OK\', then \'Save\'.\n';
    }
    return '';
  }

</script>

</body>
</html>


