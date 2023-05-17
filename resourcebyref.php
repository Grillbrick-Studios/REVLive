<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0) die('unauthorized access');
$navstr=((isset($_REQUEST['navstr']))?$_REQUEST['navstr']:'1,40,1,1');
$rloadp=((isset($_REQUEST['rloadp']))?$_REQUEST['rloadp']:0);
$rchngd = 0;
$stitle = 'Assigned Resources';

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';

$parts = explode(',',$navstr);
$mtest = $parts[0];
$mbook = $parts[1];
$mchap = $parts[2];
$mvers = $parts[3];
$sqlerr= '';

if($oper=='savresource'){
  $mcnt = $_POST['mcnt'];
  for($ni=0;$ni<$mcnt;$ni++){
    if(isset($_POST['chkdel'.$ni]) && $_POST['chkdel'.$ni]==1){
      $sql = 'delete from resourceassign
              where resourceid = '.$_POST['resource'.$ni].'
              and testament = '.$mtest.'
              and book = '.$mbook.'
              and chapter = '.$mchap.'
              and verse = '.$mvers.' ';
      $delete = dbquery($sql);
    }else{
      $sql = 'update resourceassign
              set sqn = '.$_POST['sqn'.$ni].'
              where resourceid = '.$_POST['resource'.$ni].'
              and testament = '.$mtest.'
              and book = '.$mbook.'
              and chapter = '.$mchap.'
              and verse = '.$mvers.' ';
      $update = dbquery($sql);
    }
  }
  if($_POST['newresource'] != ''){
    $nrid = $_POST['newresource'];
    $nrid = (int) preg_replace('/\D/', '', $nrid);
    if($nrid!==0){
      $sql = 'select 1 from resourceassign where testament = '.$mtest.' and book = '.$mbook.' and chapter = '.$mchap.' and verse = '.$mvers.' and resourceid = '.$nrid.' ';
      $row = rs($sql);
      if($row){
        $sqlerr = 'Resource already exists';
      }else{
        $sql = 'insert into resourceassign (testament, book, chapter, verse, resourceid) values (
               '.$mtest.', '.$mbook.', '.$mchap.', '.$mvers.', '.$nrid.') ';
        $insert = dbquery($sql);
      }
    }
  }
  if($sqlerr=='') $sqlerr = datsav;
  $rchngd = 1;
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

<table align="center">
  <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
  <tr>
    <td>
      <form name="frm" method="post" action="/resourcebyref.php">
      <table class="gridtable">
        <tr><td>Resource</td><td>Sqn</td><td>Del</td></tr>

<?
$sql = 'select r.resourceid, r.resourcetype, r.title, ra.sqn
        from resource r join resourceassign ra on (ra.resourceid = r.resourceid)
        where ra.testament = '.$mtest.'
        and ra.book = '.$mbook.'
        and ra.chapter = '.$mchap.'
        and ra.verse = '.$mvers.'
        order by ra.sqn, 1 ';
$med = dbquery($sql);
$ni=0;
while($row = mysqli_fetch_array($med)){
  print('<tr><td style="text-align:left"><small>('.$row[0].')</small> '.(($row[1]<3)?'Video':(($row[1]<5)?'Audio':(($row[1]==7)?'Library':'Article'))).': '.$row['title'].'<input type="hidden" name="resource'.$ni.'" value="'.$row[0].'" /></td>');
  print('<td><input type="text" name="sqn'.$ni.'" value="'.$row['sqn'].'" style="width:20px;text-align:right;" onchange="setdirt();" /></td>');
  print('<td><input type="checkbox" name="chkdel'.$ni.'" value="1" onclick="setdirt();" /></td></tr>');
  $ni++;
}
print('<tr><td colspan="3">New Resource ID <input type="text" name="newresource" value="" size="3" onchange="setdirt();" /></td></tr>');
print('<tr><td colspan="3"><input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);"> <input type="button" name="btnclose" value="Close" onclick="olClose('.(($rloadp==1 && $rchngd==1)?1:0).');"></td></tr>');

print('</table>');
?>
  <input type="hidden" name="mcnt" value="<?=$ni?>" />
  <input type="hidden" name="navstr" value="<?=$navstr?>" />
  <input type="hidden" name="rloadp" value="<?=$rloadp?>" />
  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="oper" value="" />
</form>
<p style="width:400px;text-align:left;">This screen shows the resources that are assigned to this scripture or Appendix. You can order them or delete them here.
To assign a new resource, if you know the resourceID, enter it into the textbox. It might be easier to do so from the "Resources" section of the site.</p>
    </td>
  </tr>
</table>
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
      if(!confirm('Are you sure you want to unassociate the checked resources?')){return false;}
    }
    parent.goback+=1;
    f.oper.value = 'savresource';
    return true;
  }

  function setdirt(){
    var f = document.frm;
    f.dirt.value=1;
    try{parent.sopschanges=1;}
    catch(e){}
  }

  function $(el) {return parent.document.getElementById(el);}

  function olClose(locn) {
    if(document.frm.dirt.value==1){
      if(!confirm('You have unsaved changes.\nAre you sure you want to continue?')) return false;
    }
    var ol = $("overlay");
    ol.style.display = 'none';
    if(locn!='') parent.document.location.reload();
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }

  function updatesops(){
    try{parent.extendfrompopup()}catch(e){};
  }
  setTimeout('updatesops();',300);

  parent.goback+=1;
  </script>
</body>
</html>

