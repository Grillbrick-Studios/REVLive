<?php
print('This page is no longer used');
return;

if(empty($userid) || $userid==0) die('unauthorized access');
$stitle = 'Manage my login';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=='saveuser'){
  $edtusername = processsqltext($_POST['edtusername'],  20, 0, 'missing username');
  $edtfirstname= processsqltext($_POST['edtfirstname'], 20, 0, 'missing firstname');
  $edtlastname = processsqltext($_POST['edtlastname'],  20, 0, 'missing lastname');
  $edtpassword = $_POST['edtpassword'];
  $oldpassword = $edtpassword;
  $edtnewpass1 = $_POST['newpassword1'];
  if($edtnewpass1 != '') $edtpassword = $edtnewpass1;
  $edtpassword = processsqltext($edtpassword,  20, 0, 'missing password');
  //$edttimezone = processsqltext($_POST['edttimezone'], 40, 0, 'America/New_York');

  $row = rs('select password from users where userid = '.$userid.' ');
  if($row[0] === $oldpassword){
    $sql = 'update users set
            username = '.$edtusername.',
            firstname = '.$edtfirstname.',
            lastname = '.$edtlastname.',
            password = '.$edtpassword.'
            where userid = '.$userid.' ';
    $update = dbquery($sql);
    if($sqlerr=='') $sqlerr = datsav;
  }else{
    $sqlerr = 'The password you entered is incorrect.';
  }

}

$sql = 'select username, password, firstname, lastname, timezone from users where userid = '.$userid.' ';
$row = rs($sql);
$edtusername = $row['username'];
$edtpassword = $row['password'];
$edtfirstname= $row['firstname'];
$edtlastname = $row['lastname'];
$edttimezone = $row['timezone'];

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
<br />
<form name="frm" method="post" action="/">
  <table class="gridtable">
    <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>&nbsp;</td><td><a href="/bcuk" onclick="return logout();">Log out</a></td></tr>
    <tr><td>User Name</td>
      <td><input type="text" name="edtusername" value="<?=$edtusername?>" size="12" maxlength="20" /></td>
    </tr>
    <tr><td>First Name</td>
      <td><input type="text" name="edtfirstname" value="<?=$edtfirstname?>" size="12" maxlength="20" /></td>
    </tr>
    <tr><td>Last Name</td>
      <td><input type="text" name="edtlastname" value="<?=$edtlastname?>" size="12" maxlength="20" /></td>
    </tr>
    <tr><td>Current Password</td>
      <td><input type="password" name="edtpassword"  value="" maxlength="20" size="12" /></td>
    </tr>
    <tr><td>New Password</td>
      <td><input type="password" name="newpassword1" value="" maxlength="20" size="12" /></td>
    </tr>
    <tr><td>Repeat</td>
      <td><input type="password" name="newpassword2" value="" maxlength="20" size="12" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
    </tr>
  <?if($userid==1){?>
    <tr><td colspan="2"><span style="font-size:10px;color:red;">for TZ, see functions.php:331 and pagebot.php:42</span></td></tr>
  <?}?>
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
    var msg = '';
    var ctl = '';
    f.edtusername.value  = trim(f.edtusername.value);
    f.edtfirstname.value = trim(f.edtfirstname.value);
    f.edtlastname.value  = trim(f.edtlastname.value);
    f.edtpassword.value  = trim(f.edtpassword.value);
    f.newpassword1.value = trim(f.newpassword1.value);
    f.newpassword2.value = trim(f.newpassword2.value);
    if(msg=='' && f.edtusername.value.length<6){
     msg = 'User name is required, and must be at least six characters long.';
     ctl = f.edtusername;
    }
    if(msg=='' && f.edtfirstname.value==''){
     msg = 'First name is required';
     ctl = f.edtfirstname;
    }
    if(msg=='' && f.edtlastname.value==''){
     msg = 'Last name is required';
     ctl = f.edtfirstname;
    }
    if(msg=='' && (f.edtpassword.value=='')){
     msg = 'You must enter your current password to continue';
     ctl = f.edtpassword;
    }

    if(msg=='' && (f.newpassword1.value!='' || f.newpassword2.value!='')){
      if(f.newpassword1.value != f.newpassword2.value){
        msg = 'If you are changing your password,\nboth password entries must match.\n\nIf you do not want to change your password\nleave both \'new\' and \'repeat\' boxes blank.';
        ctl = f.newpassword1;
      }
      var tmp = f.newpassword1.value;
      if(msg == '' && (tmp.length < 6 || tmp.length > 20)){
        msg = 'Your new password needs to be between 6 and 20 characters long.';
        ctl = f.newpassword1;
      }
      var schar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_#$%&()';
      for(var i=0;i<tmp.length;i++){
        if(schar.indexOf(tmp.charAt(i))<0){
          msg = 'Invalid character in Password. \n\nOnly letters, numbers, and _ # $ % & ( ) are permitted.\n\nNo spaces are allowed.';
          ctl = f.newpassword1;
          break;
        }
      }
    }
    if(msg != ''){
      alert(msg);
      ctl.focus();
      ctl.select();
      return false;
    }
    f.oper.value = 'saveuser';
    return true;
  }

  function logout(){
    if(confirm('Are you sure you want to log out?')){
      setCookie('myrevsid','public',cookieexpiredays);
    }else{return false;}
  }
</script>

<?

?>