<?php
if(empty($userid) || $userid==0 || empty($superman) || $superman==0) die('unauthorized access');

$stitle = 'REV Website Settings';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=='go'){
  for ($ni=0;$ni<$_POST['nset'];$ni++) {
    $settingid = $_POST['settingid'.$ni];
    if(isset($_POST['del'.$ni]) && $_POST['del'.$ni]==1){
       $result = dbquery('delete from settings where id = '.$settingid.' ');
    }else{
      $settingvalu = ((isset($_POST['settingvalu'.$ni]))?$_POST['settingvalu'.$ni]:0);
      $settingdesc = substr($_POST['settingdesc'.$ni], 0, 29);
      $sql = 'update settings set
              somenumber = \''.$settingvalu.'\',
              settingdesc = \''.$settingdesc.'\'
              where id = '.$settingid.' ';
      $result = dbquery($sql);
    }
  }
  if(trim($_POST['settingname'.$ni]) !== ''){
    $settingname = $_POST['settingname'.$ni];
    $settingtype = $_POST['settingtype'.$ni];
    $settingvalu = ((isset($_POST['settingvalu'.$ni]))?1:0);
    $settingdesc = $_POST['settingdesc'.$ni];
    $sql = 'insert into settings (settingtype, settingname, somenumber, settingdesc) values (
            \''.$settingtype.'\',\''.$settingname.'\','.$settingvalu.',\''.$settingdesc.'\') ';
    $result = dbquery($sql);
  }
  $msg='saved.';
  if($sqlerr=='') $sqlerr = $msg;
}
?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<form name="frm" method="post" action="/">
  <p style="text-align:center;color:red;font-size:90%">Careful..  There is no error checking.<br />You could really mess things up.<br />Variable values MUST be numeric.</p>
  <table border="0" cellpadding="2" cellspacing="0" style="font-size:90%;" align="center">
    <tr><td colspan="4">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td>Variable</td><td style="text-align:right;">on/val</td><td style="text-align:center;">What izzit?</td><td>Del</td>
    </tr>
<?
$ni=0;
$result = dbquery('select id, settingtype, settingname, settingdesc, somenumber from settings where settingtype in (\'variable\', \'switch\') order by settingname ');
while($row = mysqli_fetch_array($result)) {
  print('<tr>');
  print('<td><small>$'.$row['settingname'].'</small></td>');
  if($row['settingtype']=='switch')
    print('<td style="text-align:right;"><input type="checkbox" name="settingvalu'.$ni.'" value="1"'.fixchk($row['somenumber']).' /></td>');
  else
    print('<td style="text-align:right;"><input type="text" name="settingvalu'.$ni.'" value="'.$row['somenumber'].'" size="4" autocomplete="off" style="text-align:right;" /></td>');
  print('<td><input type="text" name="settingdesc'.$ni.'" value="'.$row['settingdesc'].'" size="20" autocomplete="off" /><input type="hidden" name="settingid'.$ni.'" value="'.$row['id'].'" /></td>');
  print('<td><input type="checkbox" name="del'.$ni.'" value="1"/></td>');
  print('</tr>'.crlf);
  $ni++;
}
print('<tr><td colspan="3"><small>New setting, no $.</small></td></tr>');
print('<tr>');
print('<td><input type="text" name="settingname'.$ni.'" value="" size="12" autocomplete="off" /></td>');
print('<td><select name="settingtype'.$ni.'"><option value="switch">switch</option><option value="variable">variable</option></select></td>');
print('<td><input type="text" name="settingdesc'.$ni.'" value="" size="20" autocomplete="off" /><input type="hidden" name="settingid'.$ni.'" value="99" /></td>');
print('<td>&nbsp;<input type="hidden" name="del'.$ni.'" value="0"/></td>');
print('</tr>'.crlf);

?>
    <tr>
      <td colspan="4">
        <input type="submit" name="btnsubmit" id="btnsubmit" value="Go" onclick="return validate(document.frm);">
      </td>
    </tr>
  </table>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="nset" value="<?=$ni?>" />
  <input type="hidden" name="oper" value="" />
</form>

<script>

  function validate(f){
    f.oper.value = 'go';
    return true;
  }

</script>

<?

?>
