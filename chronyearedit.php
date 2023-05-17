<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $chronedit==0) {print('<h3>unauthorized access</h3>');return;}
$byear = (isset($_REQUEST['edityear']))?$_REQUEST['edityear']:'-9999';
if($byear==-9999) {print('<h3>missing year</h3>');return;}

$stitle = 'Edit '.fixbyear($byear).' Offset';

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";

$reloadparent=0;

if($oper=='savyear'){
  $bumpbcad=$_POST['bumpbcad'];
  $upd = dbquery('update chronology set bumpbcad = '.$bumpbcad.' where bibleyear = '.$byear.' ');
  $reloadparent=1;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>edit event</title>
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center"><?=$stitle?></h2>
<div style="margin:0 auto;text-align:center">
<form name="frm" method="post" action="/chronyearedit.php">
  <p style="text-align:left;padding:10px;"><small>Enter a number from 0 to 10. The higher the number, the higher the BC/AD year will appear above the AH year.</small></p>

  <table class="gridtable" style="width:90%;max-width:800px;min-width:340px;">
    <tr>
      <td>Offset</td>
      <td>
<?
        $sql = 'select bumpbcad from chronology where bibleyear = '.$byear.' ';
        $row= rs($sql);
        print('<input type="text" name="bumpbcad" id="bumpbcad" value="'.$row[0].'" size="2" autocomplete="off">')
?>
      </td></tr>

    <tr>
      <td colspan="3" style="text-align:left;">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <input type="reset" name="btnreset" value="Reset">
        <input type="button" name="btnback" value="Close" onclick="olClose(<?=$reloadparent?>);">
      </td>
    </tr>
  </table>
<input type="hidden" name="edityear" value="<?=$byear?>">
<input type="hidden" name="oper" value="">
</form>
  <p>Play with it, you'll figure it out...</p>
</div>
<script>

  function validate(f){
    var bbcad = f.bumpbcad;
    var bcad = bbcad.value;
    if(bcad=='') bbcad.value = 0;
    if(isNaN(bcad)){
      bbcad.focus();
      bbcad.select();
      alert('must be a number from 0 to 10!');
      return false;
    }
    if(bcad<0 || bcad>10){
      bbcad.focus();
      bbcad.select();
      alert('must be a number from 0 to 10!');
      return false;
    }
    f.oper.value='savyear';
    return true;
  }

  function $(el) {return parent.document.getElementById(el);}

  function olClose(locn) {
    var ol = $("overlay");
    ol.style.display = 'none';
    if(locn==1) parent.document.location.reload();
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }

</script>
</body>
</html>
<?
function fixbyear($yr){
  if($yr<0) $ret = abs($yr).' BC';
  else $ret = abs($yr).' AD';
  return $ret;
}
?>
