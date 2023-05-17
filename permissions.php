<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || empty($superman) || $superman==0) {print('<h3>unauthorized access</h3>');return;}
$localmyrevid = ((isset($_REQUEST['localmyrevid']))?$_REQUEST['localmyrevid']:-1);
if($localmyrevid == -1) die('bad myrevid');

$oper = ((isset($_POST['oper']))?$_POST['oper']:'nada');

$edited=0;
$sqlerr='';
if($oper=='savuser'){
  $haspermissions = ((isset($_REQUEST['haspermissions']))?1:0);
  if($haspermissions==1){
    $localuserid = processsqlnumb(((isset($_POST['localuserid']))?$_POST['localuserid']:0),  999, 0, 0);
    if($localuserid==0){
      $row = rs('select max(userid) from myrevusers');
      $localuserid = $row[0]+1;
    }
    $perms[0] = processsqlnumb(((isset($_POST['localsuperman']))?1:0),  1, 0, 0);
    $perms[1] = processsqlnumb(((isset($_POST['localbibledit']))?1:0),  1, 0, 0);
    $perms[2] = processsqlnumb(((isset($_POST['localappxedit']))?1:0),  1, 0, 0);
    $perms[3] = processsqlnumb(((isset($_POST['localresedit']))?1:0),  1, 0, 0);
    $perms[4] = processsqlnumb(((isset($_POST['localchronedit']))?1:0),  1, 0, 0);
    $perms[5] = processsqlnumb(((isset($_POST['localeditorcomments']))?1:0),  1, 0, 0);
    $perms[6] = processsqlnumb(((isset($_POST['localpeernotes']))?$_POST['localpeernotes']:0),  2, 0, 0);
    $permissions = join(':', $perms);
  }else{
    $localuserid = 0;
    $permissions = '';
  }
  $permissions = processsqltext($permissions, 20, 1, '');
  $revusername = processsqltext(((isset($_POST['revusername']))?$_POST['revusername']:''), 20, 1, '');
  $qry = dbquery('update myrevusers set userid = '.$localuserid.', permissions = '.$permissions.', revusername = '.$revusername.' where myrevid = '.$localmyrevid.' ');
  if($sqlerr=='') $sqlerr = datsav;
  $edited=1;
}


$sql = 'select myrevname, userid, ifnull(permissions, \'0:0:0:0:0:0:0\') permissions, revusername from myrevusers where myrevid = '.$localmyrevid.' ';
$row = rs($sql);
$myrevname = $row['myrevname'];
$localuserid = $row['userid'];
$perms = explode(':',$row['permissions']);
// 0 = superman
// 1 = canedit
// 2 = appxedit
// 3 = resourceedit
// 4 = chronedit
// 5 = editorcomments
// 6 = peernotes
$revusername = $row['revusername'];

$editingself=(($userid==$localuserid)?1:0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>User Permissions</title>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center">Permissions for <?=$myrevname?></h2>

<form name="frm" method="post" action="/permissions.php">
  <table class="gridtable" style="width:400px;">
    <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>Has Permissions</td>
      <td>
        <input type="checkbox" name="haspermissions" id="haspermissions" value="1" onclick="setdirt()"<?=fixchk($localuserid)?><?=(($editingself)?' disabled':'')?> />
        <?if($editingself==1){?>
        <input type="hidden" name="haspermissions" id="haspermissions" value="1" />
        <?}?>
        <input type="hidden" name="localuserid" id="localuserid" value="<?=$localuserid?>" />
        <span style="font-size:80%;color:<?=$colors[3]?>;">(userid: <?=$localuserid?>)</span>
      </td>
    </tr>
<?
  $data=0;
  if($localuserid > 0){
    $row = rs('select count(*) from editlogs where userid = '.$localuserid.' ');
    $data+= $row[0];
    $row = rs('select count(*) from editnotes where author = '.$localuserid.' ');
    $data+= $row[0];
    $row = rs('select count(*) from peernotes where author = '.$localuserid.' ');
    $data+= $row[0];
    //$data=1; // test
  ?>  
    <tr><td>REV Username</td>
      <td><input type="text" name="revusername" id="revusername" value="<?=$revusername?>" autocomplete="off" onchange="setdirt()"></td>
    </tr>
    <tr><td>Superman</td>
      <td><input type="checkbox" name="localsuperman" id="localsuperman" value="1"<?=fixchk($perms[0]).(($editingself)?' disabled':'')?> onclick="setdirt()" /></td>
    </tr>
    <tr><td>Bible Edit</td>
      <td><input type="checkbox" name="localbibledit" id="localbibledit" value="1"<?=fixchk($perms[1])?> onclick="setdirt()" /></td>
    </tr>
    <tr><td>Appx Edit</td>
      <td><input type="checkbox" name="localappxedit" id="localappxedit" value="1"<?=fixchk($perms[2])?> onclick="setdirt()" /></td>
    </tr>
    <tr><td>Resource Edit</td>
      <td><input type="checkbox" name="localresedit" id="localresedit" value="1"<?=fixchk($perms[3])?> onclick="setdirt()" /></td>
    </tr>
    <tr><td>Chronology Edit</td>
      <td><input type="checkbox" name="localchronedit" id="localchronedit" value="1"<?=fixchk($perms[4])?> onclick="setdirt()" /></td>
    </tr>
    <tr><td>Editor Comments</td>
      <td><input type="checkbox" name="localeditorcomments" id="localeditorcomments" value="1"<?=fixchk(((isset($perms[5]))?$perms[5]:0))?> onclick="setdirt()" /></td>
    </tr>
    <tr><td>Reviewer Notes</td>
      <td><small>
        None<input type="radio" name="localpeernotes" id="localpeernotes" value="0"<?=fixrad(((isset($perms[6]))?$perms[6]:0)==0)?> onclick="setdirt()" />
        Assigned<input type="radio" name="localpeernotes" id="localpeernotes" value="1"<?=fixrad(((isset($perms[6]))?$perms[6]:0)==1)?> onclick="setdirt()" />
        Global<input type="radio" name="localpeernotes" id="localpeernotes" value="2"<?=fixrad(((isset($perms[6]))?$perms[6]:0)==2)?> onclick="setdirt()" />
        </small>
      </td>
    </tr>
<?}?>    
    <tr>
      <td colspan="2">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <input type="button" name="btnback" value="Close" onclick="olClose(<?=$edited?>)">
      </td>
    </tr>
  </table>
  <?if($editingself) print('<input type="hidden" name="localsuperman" value="1" />');?>

  <input type="hidden" name="localmyrevid" value="<?=$localmyrevid?>" />
  <input type="hidden" name="origlocaluserid" value="<?=$localuserid?>" />
  <input type="hidden" name="datacount" value="<?=$data?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirty" value="0" />
</form>

  <ul style="font-size:80%;">
    <li>Has Permissions: Does this user have permissions on the REV system?</li>
    <li>REV Username: If the person's myrev name is too long or does not identify them, enter a short name here, like "BillS,", "Jerry," "John," etc.</li>
    <li>Superman: This gives full permissions to the person. Assign carefully!</li>
    <li>Bible Edit: gives the person permission to edit the REV Bible text and Commentary.</li>
    <li>Appx Edit: gives the person permission to edit appendices, word studies, and information docs.</li>
    <li>Resource Edit: gives the person permission to edit Resources.</li>
    <li>Chronology Edit: gives the person permission to edit the Chronology.</li>
    <li>Editor Comments: gives the person permission to view and edit editor comments.</li>
    <li>Reviewer Notes: gives the person permission to view and edit reviewer notes.</li>
  </ul>

<?
if($editingself)
  print('<div style="text-align:center;margin:0 auto;color:red;"><h4>You are editing your own account..</h4></div>');
?>

<script>
  function validate(f){
    if(!f.haspermissions.checked && f.origlocaluserid.value > 0 && f.datacount.value > 0 && <?=$editingself?> == 0){
      if(!confirm('!!!This user has data associated with them!!!\n\nYou can instead remove all the individual permissions.\n\nAre you sure you want to continue?')) return false;
    }
    f.oper.value = 'savuser';
    return true;
  }

  function setdirt(){
    document.frm.dirty.value=1;
  }

  function $(el) {return document.getElementById(el);}
  function $$(el) {return parent.document.getElementById(el);}

  function olClose(reload) {
    if(document.frm.dirty.value==1){
      if(!confirm('\nThere are unsaved changes!\n\nClick \'Cancel\' if you want to save them.')) return false;
    }
    var ol = $$("overlay");
    ol.style.display = 'none';
    //if(locn!='') parent.document.location.href=locn;
    //if(locn!='') parent.document.frm.submit();
    if(reload==1) parent.document.location.reload();
    setTimeout('$$("ifrm").src="/includes/empty.htm"', 200);
  }
  </script>

</body></html>
