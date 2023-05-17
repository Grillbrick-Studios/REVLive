<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');
$stitle = 'Blocked IPs';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
if($oper=='sav'){
  $ni=0;
  while(isset($_POST['blockip'.$ni])){
    if($_POST['dirty'.$ni] == 1){
      $ip = $_POST['blockip'.$ni];
      if(isset($_POST['blockdelete'.$ni]) && ($_POST['blockdelete'.$ni] > 0)){
        $cnt = $_POST['cnt'.$ni];
        $update = dbquery('update logexcludeips set hits = hits+'.$cnt.', lastview = UTC_TIMESTAMP() where ipaddress=\'0.0.0.0\' ');
        $sql = 'delete from blockedips where id = \''.$_POST['blockipid'.$ni].'\' ';
        $update = dbquery($sql);
        if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      }else{
        $ipid= $_POST['blockipid'.$ni];
        $ip  = processsqltext($_POST['blockip'.$ni], 20, 0, '!none!');
        $resn= $_POST['reason'.$ni];
        $cmt = processsqltext($_POST['blockipcomment'.$ni], 99, 1, '');
        if($ipid>0)
          $update = dbquery('update blockedips set ipaddress = '.$ip.', reason = '.$resn.', comment = '.$cmt.' where id = \''.$ipid.'\'; ');
        else
          $insert = dbquery('insert into blockedips(ipaddress, reason, comment, lasthit) values ('.$ip.', '.$resn.', '.$cmt.', UTC_TIMESTAMP); ');
      }
    }
    $ni++;
  }
  $sqlerr = $msg;

  savesettingvalue('autoblock', 'num', $_POST['autoblock']);

  if($sqlerr=='') $sqlerr = datsav;
}
$autoblock = getsettingvalue('autoblock', 'num');

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<h3 style="text-align:center">These IPs are not allowed access to the website</h3>
<form name="frm" method="post" action="/">
  <table class="gridtable">
    <tr><td colspan="9">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td colspan="6">
        <input type="reset" name="btnresetx" value="Reset">
        <input type="submit" name="btnsubmitx" value="Submit" onclick="return validate(document.frm);">
        <span style="font-size:80%;color:red;">&nbsp;wildcards(*) are allowed, but be careful!</span>
      </td>
    </tr>
    <tr><td>IP Address</td><td>Cnt</td><td>Last <span style="color:red;">&darr;</span></td><td>Reason</td><td>Comment</td><td><a onclick="chkall()">Del</a></td></tr>
<?
$ni = 0;
$sql = 'select id, ipaddress, hitcount, lasthit, reason, comment from blockedips order by 4 desc ';
$tatt=0;
$ips = dbquery($sql);
while($row = mysqli_fetch_array($ips)){
?>
    <tr>
      <td>
        <input type="hidden" name="blockipid<?=$ni?>" value="<?=$row['id']?>" />
        <input type="hidden" name="dirty<?=$ni?>" value="0" />
        <input type="hidden" name="cnt<?=$ni?>" value="<?=$row['hitcount']?>" />
        <input type="text" name="blockip<?=$ni?>" value="<?=$row['ipaddress']?>" size="12" onchange="setdirt(<?=$ni?>)" />
      </td>
      <td style="text-align:right"><?=$row['hitcount']?></td>
      <td style="text-align:right">
        <?=rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row['lasthit'], $timezone))), 'm')?>
      </td>
      <td>
        <select name="reason<?=$ni?>" onchange="setdirt(<?=$ni?>);">
          <option value="3"<?=fixsel(3, $row['reason'])?>>because</option>
          <option value="0"<?=fixsel(0, $row['reason'])?>>hammer</option>
          <option value="1"<?=fixsel(1, $row['reason'])?>>injection</option>
          <option value="2"<?=fixsel(2, $row['reason'])?>>svg</option>
        </select>
      </td>
      <td>
        <input type="text" name="blockipcomment<?=$ni?>" value="<?=$row['comment']?>" onchange="setdirt(<?=$ni?>)" />
      </td>
      <td><input type="checkbox" name="blockdelete<?=$ni?>" value="1" onclick="setdirt(<?=$ni?>)" /></td>
    </tr>
<?
  $tatt+= $row['hitcount'];
  $ni++;
}
?>
    <tr>
      <td>
        <input type="hidden" name="blockipid<?=$ni?>" value="-1" />
        <input type="hidden" name="dirty<?=$ni?>" value="0" />
        <input type="text" name="blockip<?=$ni?>" value="" size="12" onchange="setdirt(<?=$ni?>)" />
      </td>
      <td colspan="2"><?=$tatt.' total';?></td>
      <td>
        <select name="reason<?=$ni?>" onchange="setdirt(<?=$ni?>);">
          <option value="3">because</option>
          <option value="0">hammer</option>
          <option value="1">injection</option>
          <option value="2">svg</option>
        </select>
      </td>
      <td>
        <input type="text" name="blockipcomment<?=$ni?>" value="" onchange="setdirt(<?=$ni?>)" />
      </td>
      <td colspan="2"><input type="hidden" name="blockdelete<?=$ni?>" value="0" /></td>
    </tr>
    <tr>
      <td colspan="6">
        Autoblock is
        <select name="autoblock" style="background-color:<?=(($autoblock==0)?'pink':'lightgreen')?>">
          <option value="0" style="background-color:pink"<?=fixsel(0, $autoblock)?>>Off</option>
          <option value="1" style="background-color:lightgreen"<?=fixsel(1, $autoblock)?>>On!!</option>
        </select> <small>If on, autoblock IPs who try injection attack.</small>
      </td>
    </tr>
    <tr>
      <td colspan="6">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
      </td>
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

<div style="margin:0 auto;text-align:center">
  <br />
  <span style="color:red">
  If the REV website is being maliciously hammered by an IP address,<br />you can enter it here and their access to the site will be blocked.
  </span>
</div>

<script>

  function setdirt(ni){
    document.frm['dirty'+ni].value = 1;
  }

  function validate(f){
    var haveset=0;
    for(var i=0;i<<?=$ni?>;i++){
      if(f['blockdelete'+i].checked) haveset = 1;
    }
    if(haveset==1 && !confirm('Are you sure you want to remove the checked IP\'s?\n\nThis is not undoable')) return false;
    f.oper.value = 'sav';
    return true;
  }
  function chkall(){
    var f = document.frm;
    var chk = (!f.blockdelete0.checked);
    for(var i=0;i<<?=$ni?>;i++){
      f['blockdelete'+i].checked = chk;
      f['dirty'+i].value = ((chk)?1:0);
    }
  }

</script>

