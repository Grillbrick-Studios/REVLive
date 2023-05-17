<?php
print('This page is no longer used');
return;

if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');
$showpass = ((isset($_POST['showpass']))?1:0);
$stitle = 'Manage Users';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
if($oper=='savusrs'){
  $ni=0;
  while(isset($_POST['edtuserid'.$ni])){
    $edtuserid   = processsqlnumb($_POST['edtuserid'.$ni],    999, 0, 999);
    if($edtuserid < 999){
      $edtusername = processsqltext($_POST['edtusername'.$ni],  20, 0, 'missing!');
      $edtfirstname= processsqltext($_POST['edtfirstname'.$ni], 20, 0, 'missing!');
      $edtlastname = processsqltext($_POST['edtlastname'.$ni],  20, 0, 'missing!');
      $edtpassword = processsqltext($_POST['edtpassword'.$ni],  20, 0, 'missing!');
      $edtsuper    = processsqlnumb(((isset($_POST['edtsuper'.$ni]))?$_POST['edtsuper'.$ni]:0),  20, 0, 0);
      $edtedit     = processsqlnumb(((isset($_POST['edtedit'.$ni]))?$_POST['edtedit'.$ni]:0),  20, 0, 0);
      $edtappx     = processsqlnumb(((isset($_POST['edtappx'.$ni]))?$_POST['edtappx'.$ni]:0),  20, 0, 0);
      $edtresedit  = processsqlnumb(((isset($_POST['edtresedit'.$ni]))?$_POST['edtresedit'.$ni]:0),  20, 0, 0);
      $edtchronedit= processsqlnumb(((isset($_POST['edtchronedit'.$ni]))?$_POST['edtchronedit'.$ni]:0),  20, 0, 0);
      $edtsqn      = processsqlnumb(((isset($_POST['edtsqn'.$ni]))?$_POST['edtsqn'.$ni]:99),  99, 0, 99);
      $edttimezone = processsqltext($_POST['edttimezone'.$ni],  40, 0, 'America/New_York');
      //$edttimezone = '\'America/New_York\'';
      $sql = 'update users set
              username = '.$edtusername.',
              firstname= '.$edtfirstname.',
              lastname = '.$edtlastname.',
              password = '.$edtpassword.',
              superman = '.$edtsuper.',
              edit     = '.$edtedit.',
              appxedit = '.$edtappx.',
              resourceedit= '.$edtresedit.',
              chronedit= '.$edtchronedit.',
              timezone = '.$edttimezone.',
              sqn      = '.$edtsqn.'
              where userid = '.$edtuserid.' ';
      $update = dbquery($sql);
      //$msg.=$sql.'<br>';
      if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      if(!isset($_POST['edtlogout'.$ni])){
        $sql = 'update users set cursession = \'public\' where userid = '.$edtuserid.' ';
        $update = dbquery($sql);
        //$msg.=$sql.'<br>';
        if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      }
      if(isset($_POST['edtdelete'.$ni])){
        $sql = 'delete from users where userid = '.$edtuserid.' ';
        $update = dbquery($sql);
        //$msg.=$sql.'<br>';
        if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      }
    }else{
      $edtusername = (($_POST['edtusername'.$ni]!='')?$_POST['edtusername'.$ni]:'empty');
      if($edtusername != 'empty'){
        $edtusername = processsqltext($_POST['edtusername'.$ni],  20, 0, 'missing!');
        $edtfirstname= processsqltext($_POST['edtfirstname'.$ni], 20, 0, 'missing!');
        $edtlastname = processsqltext($_POST['edtlastname'.$ni],  20, 0, 'missing!');
        $edtpassword = processsqltext($_POST['edtpassword'.$ni],  20, 0, 'missing!');
        $edtsuper    = processsqlnumb(((isset($_POST['edtsuper'.$ni]))?$_POST['edtsuper'.$ni]:0),  20, 0, 0);
        $edtedit     = processsqlnumb(((isset($_POST['edtedit'.$ni]))?$_POST['edtedit'.$ni]:0),  20, 0, 0);
        $edtappx     = processsqlnumb(((isset($_POST['edtappx'.$ni]))?$_POST['edtappx'.$ni]:0),  20, 0, 0);
        $edtresedit  = processsqlnumb(((isset($_POST['edtresedit'.$ni]))?$_POST['edtresedit'.$ni]:0),  20, 0, 0);
        $edtchronedit= processsqlnumb(((isset($_POST['edtchronedit'.$ni]))?$_POST['edtchronedit'.$ni]:0),  20, 0, 0);
        $edtsqn      = processsqlnumb(((isset($_POST['edtsqn'.$ni]))?$_POST['edtsqn'.$ni]:99),  99, 0, 99);
        $edttimezone = processsqltext($_POST['edttimezone'.$ni],  40, 0, 'America/New_York');
        //$edttimezone = '\'America/New_York\'';
        $sql = 'insert into users (username, password, firstname, lastname, superman, edit, appxedit, resourceedit, chronedit, timezone, sqn) values ('.
                $edtusername.','.
                $edtpassword.','.
                $edtfirstname.','.
                $edtlastname.','.
                $edtsuper.','.
                $edtedit.','.
                $edtappx.','.
                $edtresedit.','.
                $edtchronedit.','.
                $edttimezone.','.
                $edtsqn.')';
        $update = dbquery($sql);
        //$msg.=$sql.'<br>';
        if($sqlerr!=''){ $msg.= $sqlerr.'<br />'; $sqlerr='';}
      }
    }
    $ni++;
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
    <tr><td colspan="15">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td valign="bottom" style="color:<?=$colors[3]?>;font-size:80%;text-align:right;">id</td><td valign="bottom">User Name</td><td valign="bottom">Password</td><td valign="bottom">First Name</td><td valign="bottom">Last Name</td><td style="color:red">Supr<br />User</td><td>Bibl<br />Edit</td><td>Apx<br />Edit</td><td>Res<br />Edit</td><td>Chn<br />Edit</td><td>Time<br />Zone</td><td>Lg'd<br />in</td><td valign="bottom">sqn</td><td valign="bottom">Last Visit</td><td valign="bottom">Del</td></tr>
<?
$ni = 0;
$sql = 'select username, password, firstname, lastname, userid, superman, edit, appxedit, resourceedit, chronedit, ifnull(cursession,\'public\') sid, sqn, lastaccessed, timezone from users order by sqn ';
$usrs = dbquery($sql);
while($row = mysqli_fetch_array($usrs)){
?>
    <tr>
      <td style="color:<?=$colors[3]?>;font-size:80%;text-align:right;"><?=$row['userid']?></td>
      <td><input type="hidden" name="edtuserid<?=$ni?>" value="<?=$row['userid']?>" />
        <input type="hidden" name="edtsid<?=$ni?>" value="<?=$row['sid']?>">
        <input type="text" name="edtusername<?=$ni?>" value="<?=$row['username']?>" size="9" maxlength="20" /></td>
      <td><input type="<?=(($showpass)?'text':'password')?>" name="edtpassword<?=$ni?>" value="<?=$row['password']?>" size="9" maxlength="20" /></td>
      <td><input type="text" name="edtfirstname<?=$ni?>" value="<?=$row['firstname']?>" size="9" maxlength="20" /></td>
      <td><input type="text" name="edtlastname<?=$ni?>" value="<?=$row['lastname']?>" size="9" maxlength="20" /></td>
      <td><input type="checkbox" name="edtsuper<?=$ni?>" value="1"<?=fixchk($row['superman'])?> /></td>
      <td><input type="checkbox" name="edtedit<?=$ni?>" value="1"<?=fixchk($row['edit'])?> /></td>
      <td><input type="checkbox" name="edtappx<?=$ni?>" value="1"<?=fixchk($row['appxedit'])?> /></td>
      <td><input type="checkbox" name="edtresedit<?=$ni?>" value="1"<?=fixchk($row['resourceedit'])?> /></td>
      <td><input type="checkbox" name="edtchronedit<?=$ni?>" value="1"<?=fixchk($row['chronedit'])?> /></td>
      <td>
        <select name="edttimezone<?=$ni?>">
          <option value="America/New_York"<?=fixsel($row['timezone'], 'America/New_York')?>>EST</option>
          <option value="America/Chicago"<?=fixsel($row['timezone'], 'America/Chicago')?>>CST</option>
          <option value="America/Denver"<?=fixsel($row['timezone'], 'America/Denver')?>>MST</option>
          <option value="America/Los_Angeles"<?=fixsel($row['timezone'], 'America/Los_Angeles')?>>PST</option>
        </select>
      </td>
      <td><input type="checkbox" name="edtlogout<?=$ni?>" value="1"<?=fixchk((($row['sid']=='public')?0:1))?> /></td>
      <td><input type="text" name="edtsqn<?=$ni?>" value="<?=$row['sqn']?>" size="2" maxlength="3" /></td>
      <td><?=(($row['lastaccessed']===null)?'never':'<a onclick="olOpen(\'/logviews_ip.php?uid='.$row['userid'].'&amp;alias='.str_replace('\'', '\\\'', $row['username']).'&amp;daysback=3\', '.getdivdimensions().');return false;">'.converttouserdate($row['lastaccessed'], $timezone).'</a>')?></td>
      <td><input type="checkbox" name="edtdelete<?=$ni?>" value="1" /></td>
    </tr>
<?
  $ni++;
}
?>
    <tr>
      <td>&nbsp;</td>
      <td><input type="hidden" name="edtuserid<?=$ni?>" value="999" />
        <input type="hidden" name="edtsid<?=$ni?>" value="public">
        New User<br /><input type="text" name="edtusername<?=$ni?>"  value="" size="9" maxlength="20" /></td>
      <td valign="bottom"><input type="<?=(($showpass)?'text':'password')?>" name="edtpassword<?=$ni?>"  value="" size="9" maxlength="20" /></td>
      <td valign="bottom"><input type="text" name="edtfirstname<?=$ni?>" value="" size="9" maxlength="20" /></td>
      <td valign="bottom"><input type="text" name="edtlastname<?=$ni?>"  value="" size="9" maxlength="20" /></td>
      <td valign="bottom"><input type="checkbox" name="edtsuper<?=$ni?>"  value="1" /></td>
      <td valign="bottom"><input type="checkbox" name="edtedit<?=$ni?>"  value="1" /></td>
      <td valign="bottom"><input type="checkbox" name="edtappx<?=$ni?>"  value="1" /></td>
      <td valign="bottom"><input type="checkbox" name="edtresedit<?=$ni?>"  value="1" /></td>
      <td valign="bottom"><input type="checkbox" name="edtchronedit<?=$ni?>"  value="1" /></td>
      <td valign="bottom">
        <select name="edttimezone<?=$ni?>">
          <option value="America/New_York">EST</option>
          <option value="America/Chicago">CST</option>
          <option value="America/Denver">MST</option>
          <option value="America/Los_Angeles">PST</option>
        </select>
      </td>
      <td>&nbsp;<input type="hidden" name="edtlogout<?=$ni?>" value="0" /></td>
      <td valign="bottom"><input type="text" name="edtsqn<?=$ni?>" value="" size="2" maxlength="3" /></td>
      <td>&nbsp;<input type="hidden" name="edtdelete<?=$ni?>" value="0" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="15">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
        Show Passwords
        <input type="checkbox" name="showpass" value="1" onclick="document.frm.submit();"<?=fixchk($showpass)?>>
      </td>
    </tr>
    <tr>
      <td colspan="15">
        <p>
        All text fields are required.<br />
        <span style="color:red">
        Be careful. &nbsp;You don't want to accidentally log out or delete a user.<br />
        Also, do NOT accidentally log yourself out or make yourself a non-superuser!
        </span>
        </p>
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
<script>

  function validate(f){
    var haveset=0;
    for(var i=0;i<=<?=$ni?>;i++){
      f['edtusername'+i].value = trim(f['edtusername'+i].value);
      f['edtpassword'+i].value = trim(f['edtpassword'+i].value);
      f['edtfirstname'+i].value = trim(f['edtfirstname'+i].value);
      f['edtlastname'+i].value = trim(f['edtlastname'+i].value);
      if((!f['edtlogout'+i].checked && f['edtsid'+i].value != 'public') || f['edtdelete'+i].checked) haveset = 1;
    }
    if(haveset==1 && !confirm('Are you sure you want to log out / delete the selected users?\n\nThis is not undoable')) return false;
    f.oper.value = 'savusrs';
    return true;
  }

</script>
<?
function getdivdimensions(){
  global $ismobile, $screenwidth;
  return (($ismobile==1)?$screenwidth+20:600).', 500';
}
?>
