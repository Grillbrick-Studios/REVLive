<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');
$stitle = 'Manage Non-Logged IPs';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
if($oper=='sav'){
  $ni=0;
  while(isset($_POST['edtip'.$ni])){
    $ip = $_POST['edtip'.$ni];
    if(isset($_POST['edtdelete'.$ni]) && $_POST['edtdelete'.$ni]==1){
      $ipid = $_POST['edtipid'.$ni];
      $row = rs('select ifnull(hits, 0) from logexcludeips where ipid = \''.$ipid.'\' ');
      $hits = $row[0];
      $update = dbquery('update logexcludeips set hits = hits+'.$hits.', lastview = UTC_TIMESTAMP() where ipaddress=\'0.0.0.0\' ');
      $sql = 'delete from logexcludeips where ipid = \''.$ipid.'\' ';
      $update = dbquery($sql);
      if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
    }else if(isset($_POST['edtblock'.$ni]) && $_POST['edtblock'.$ni]==1){
      $qry = dbquery('insert into blockedips(ipaddress, reason, comment, hitcount, lasthit) values (\''.$ip.'\', 3, \'blocked from NLGd IPs\', '.$_POST['edtcnt'.$ni].', UTC_TIMESTAMP); ');
      if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      $qry = dbquery('delete from logexcludeips where ipid = \''.$_POST['edtipid'.$ni].'\' ');
      if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
    }else{
      $ipid = $_POST['edtipid'.$ni];
      $ip = ((isset($_POST['edtip'.$ni]))?$_POST['edtip'.$ni]:'!none!');
      $ipc= ((isset($_POST['edtipcmt'.$ni]))?$_POST['edtipcmt'.$ni]:'');
      if($ipid==-1 && $ip != 'new ip here')
        $update = dbquery('insert into logexcludeips (ipaddress, ipcomment, lastview) values('.processsqltext($ip,  20, 0, '!none!').', '.processsqltext($ipc,  99, 1, '').', UTC_TIMESTAMP); ');
      else
        $update = dbquery('update logexcludeips set ipaddress = '.processsqltext($ip,  20, 0, '!none!').', ipcomment = '.processsqltext($ipc,  99, 1, '').' where ipid = \''.$ipid.'\'; ');
      $row = rs('select count(*) from viewlogs where remoteip like \''.str_replace('*', '', $ip).'%\' ');
      if($row)
        $update = dbquery('update logexcludeips set hits = hits + '.$row[0].' where ipaddress = \'0.0.0.0\' ');
      $update = dbquery('delete from viewlogs where remoteip like \''.str_replace('*', '', $ip).'%\'');
      //$update = dbquery('delete from ipcrossref where ipaddress like \''.str_replace('*', '', $ip).'%\'');
    }
    $ni++;
  }
  if(isset($_POST['chkzero']) && $_POST['chkzero']==1){
    $update = dbquery('update logexcludeips set hits = 0, lastview = null ');
    $update = dbquery('update settings set sometime = UTC_TIMESTAMP where settingname = \'ipslastzeroed\'');
  }
  $sqlerr = $msg;
  if($sqlerr=='') $sqlerr = datsav;
}

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
<form name="frm" method="post" action="/">
  <table class="gridtable">
    <tr><td colspan="9">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>IP Address</td><td>Comment</td><td>Hits</td><td>Last Visit</td><td>Blk</td><td>Del</td></tr>
<?
$ni = 0;
$sql = 'select ipid, ipaddress, ipcomment, hits, ifnull(lastview, \'9/9/1999\') from logexcludeips order by ipcomment, ipaddress ';
$ips = dbquery($sql);
$tothits=0;
while($row = mysqli_fetch_array($ips)){
?>
    <tr>
      <td>
        <input type="hidden" name="edtipid<?=$ni?>" value="<?=$row[0]?>" />
        <input type="text" name="edtip<?=$ni?>" value="<?=$row[1]?>" />
        </td>
      <td>
        <input type="text" name="edtipcmt<?=$ni?>" value="<?=$row[2]?>" size="34" />
        </td>
      <td style="text-align:right"><?=$row[3]?><input type="hidden" name="edtcnt<?=$ni?>" value="<?=$row[3]?>" /></td>
      <td style="text-align:right"><?=(($row[4]=='9/9/1999')?'<span style="color:red;">never</span>':rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row[4], $timezone))), 'm'))?></td>
      <td><input type="checkbox" name="edtblock<?=$ni?>" value="1" /></td>
      <td><input type="checkbox" name="edtdelete<?=$ni?>" value="1" /></td>
    </tr>
<?
  $tothits+=$row[3];
  $ni++;
}
?>
    <tr>
      <td>
        <input type="hidden" name="edtipid<?=$ni?>" value="-1" />
        <input type="text" name="edtip<?=$ni?>" value="new ip here" />
        </td>
      <td>
        <input type="text" name="edtipcmt<?=$ni?>" value="" size="34" />
        </td>
      <td style="text-align:right">-</td>
      <td style="text-align:center">-</td>
      <td style="text-align:center">-</td>
      <td style="text-align:center"><input type="hidden" name="edtblock<?=$ni?>" value="0" /><input type="hidden" name="edtdelete<?=$ni?>" value="0" />-</td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
        &nbsp;Zero out hits. <input type="checkbox" name="chkzero" value="1">
        <?
          $lastzeroed = getsettingvalue('ipslastzeroed', 'time');
          print(' <small>(last zeroed: '.converttouserdate($lastzeroed, $timezone).')</small>');
        ?>
      </td>
      <td style="text-align:right"><?=$tothits?></td>
      <td colspan="3"></td>
    </tr>
  </table>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
</form>
<script>

  function validate(f){
    var haveset=0, haveblk=0;
    for(var i=0;i<<?=$ni?>;i++){
      if(f['edtdelete'+i].checked) haveset = 1;
      if(f['edtblock'+i].checked)  haveblk = 1;
    }
    if(haveset==1 && !confirm('Are you sure you want to remove the checked IP\'s?\n\nThis is not undoable.')) return false;
    if(haveblk==1 && !confirm('Are you sure you want to block the checked IP\'s?\n\nThey can be unblocked...')) return false;
    if(f.chkzero.checked && !confirm('Are you sure you want to zero the Hits?')) return false;
    f.oper.value = 'sav';
    return true;
  }

</script>

