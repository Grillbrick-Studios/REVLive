<?php

print('This page is no longer used');
return;



if(!isset($page)) die('unauthorized access');
$stitle = "Log in";
include("simple-php-captcha.php");
$msg = '';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=='log1n'){
  if($_POST['captcha'] == $_SESSION['captcha']['code']){
    $sql = 'select username, password, userid, cursession from users where username = \''.((isset($_POST['username']))?$_POST['username']:'x-x').'\' ';
    $row = rs($sql);
    if($row){
      if($row['password']==((isset($_POST['password']))?$_POST['password']:'x-x')){
        $userid = $row['userid'];
        $sid = (($row['cursession']!=null && $row['cursession']!='public')?$row['cursession']:keygen(30));
        $sql = 'update users set cursession = \''.$sid.'\' where userid = '.$userid.' ';
        dbquery($sql);
        ?>
        <script>
          setCookie('sid','<?=$sid?>',cookieexpiredays);
          setTimeout('location.href="/bcuk";',200);
        </script>
        <?
        exit();
      }else{
        $msg = 'sorry, unknown username or password<br />If you have forgotten your password, you will need to contact <a href="mailto:rswoods@swva.net">Rob</a> or <a href="mailto:jerry@spiritandtruthonline.org">Jerry</a>.';
      }
    }else{
      $msg = 'sorry, unknown username or password';
    }
  }else{
    $msg = 'sorry, incorrect captcha code';
  }
}
$_SESSION['captcha'] = simple_php_captcha();
?>
  <form name="frm" method="post" action="/">
  <span class="pageheader"><?=$stitle?></span><br />
    <table border="0" cellpadding="2" cellspacing="0" align="center" style="font-size:90%">
      <tr><td colspan="6"><?=printsqlerr($msg)?></td></tr>
      <tr>
        <td>Username:</td>
        <td><input type="text" name="username" value="<?=(isset($_POST['username']))?$_POST['username']:''?>" maxlength="20"></td>
      </tr>
      <tr>
        <td>Password:</td>
        <td><input type="password" name="password" value="" maxlength="20"></td>
      </tr>
      <tr>
        <td><?='<img src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">'?></td>
        <td>Enter<br />captcha<br /><input type="text" name="captcha" value="" size="7"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" name="asd" value="Submit" onclick="return validate(document.frm);"></td>
      </tr>
    </table>
    <input type="hidden" name="mitm" value="<?=$mitm?>" />
    <input type="hidden" name="page" value="7">
    <input type="hidden" name="origpage" value="<?=(isset($_POST['temp']))?$_POST['temp']:''?>">
    <input type="hidden" name="test" value="<?=$test?>" />
    <input type="hidden" name="book" value="<?=$book?>" />
    <input type="hidden" name="chap" value="<?=$chap?>" />
    <input type="hidden" name="vers" value="<?=$vers?>" />
    <input type="hidden" name="oper" value="">
  </form>
  <script>
    <!--
    function validate(f){
      f.oper.value = 'log1n';
      return true;
    }
    setTimeout('document.frm.username.focus()', 300);
    //-->
  </script>
