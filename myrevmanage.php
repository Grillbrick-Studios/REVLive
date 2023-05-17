<?
if(!isset($page)) die('unauthorized access');

require $docroot.'/includes/load_phpmailer.php';
require $docroot.'/includes/ReCaptcha/autoload.php';
$isweb = (($site=='revdev.test')?0:1);
switch($site){
  case 'www.revisedenglishversion.com':
    $rcpsitekey = '6LcoUTwbAAAAAFoZix3UjrHlpABbc7x3KOkCMsnW';
    $rcpsecret = '6LcoUTwbAAAAALavwOfZCbM3AB1lGojXAHnjqBG0';
    break;
  case 'www.revdevbible.com':
    $rcpsitekey = '6LcmPIskAAAAANhEGS_6fdXtRYkmgprxvZRZIP5D';
    $rcpsecret = '6LcmPIskAAAAAEaf6RJn1_Vi74vUfM1Ha8XgQwTp';
    break;
  case 'www.revdevbible2.com':
    $rcpsitekey = '6LcF8eAlAAAAAMLOl8Le1x1Ff3i0caAuUFfh0VDl';
    $rcpsecret = '6LcF8eAlAAAAAP2z-Fy_IEJYGA6ywipAHWc8K3df';
    break;
  case 'revdev.woodsware.com':  
    $rcpsitekey = '6LdkATwbAAAAAMndPKdITqTL6hOc0kLaEFip4dNr';
    $rcpsecret = '6LdkATwbAAAAAMAKg0gvS42_irc_1BlOOS3wV1dL';
    break;
  case 'revdev.test':
    $rcpsitekey = '';
    $rcpsecret = '';
    break;
}

$rtask = ((isset($_REQUEST['rtask']))?$_REQUEST['rtask']:'login');
if($myrevid>0) $rtask = 'manage';
if(isset($arqs[1]) && strlen($arqs[1])==50)
  $rtask = 'compreg';

$msg = '';

//
//
//
//
//
switch($rtask){
case 'login':
$stitle = "MyREV Log in";
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$stopprocess=0;
if($oper=='log1n'){
  if($isweb==1) $recaptcha = new \ReCaptcha\ReCaptcha($rcpsecret);
  if(isset($_POST['g-recaptcha-response']) || $site=='revdev.test'){
    if($isweb==1) $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                      ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
    if($isweb==0 || $resp->isSuccess()){
      $myrevemail = ((isset($_POST['myrevemail']))?$_POST['myrevemail']:'x-x');
      $sql = 'select myrevname, myrevemail, password, myrevid, cursession, ifnull(regcode, \'x1\') regcode from myrevusers where myrevemail = \''.$myrevemail.'\' ';
      $row = rs($sql);
      if($row){
        if($row['password']==((isset($_POST['password']))?$_POST['password']:'x-x')){
          if($row['regcode']=='x1'){
            $myrevid = $row['myrevid'];
            $myrevsid = (($row['cursession']!=null && $row['cursession']!='public')?$row['cursession']:keygen(30));
            $sql = 'update myrevusers set cursession = \''.$myrevsid.'\' where myrevid = '.$myrevid.' ';
            dbquery($sql);
            ?>
            <script>
              setCookie('myrevsid','<?=$myrevsid?>',cookieexpiredays);
              setTimeout('location.href="/bcuk";',200);
            </script>
            <?
            exit();
          }else{
            $msg = 'Sorry, your MyREV registration is incomplete.<br />You must complete your registration by<br />clicking the link in the email you received.<br /><br />';
            $msg.= 'Another email has been sent.<br />If you do not receive it within a few minutes,<br />please check your spam folder.';
            // send the email
            $myrevname= $row['myrevname'];
            $myrevemail= $row['myrevemail'];
            $regcode = $row['regcode'];
            $emlbody = getemailbody();
            $emlbody = str_replace('[content]', '<p>'.$myrevname.',</p>
                      <p>Thank you for registering for a MyREV account with the REV website!</p>
                      <p>Please click <a href="https://'.$site.'/login/'.$regcode.'">this link</a> to complete your MyREV registration:</p>
                      <p>If the above link is not clickable, please copy and paste the following URL into your browser&rsquo;s address bar:</p>
                      <p>https://'.$site.'/login/'.$regcode.'</p>
                      <p>Thanks, and God bless you!</p>', $emlbody);

            // sendemail($to, $from, $subject, $message)
            if($isweb==1)
              sendemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Registration', $emlbody);
            else
              sendsmtpemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Registration', $emlbody);
            $stopprocess=1;
          }
        }else{
          $msg = 'Sorry, unknown email address or password<br />If you have forgotten your password,<br />click "Forgot password".';
        }
      }else{
        $msg = 'Sorry, unknown email or password';
      }
    }else{
      $msg = 'Sorry, you must<br />click the "I am not a Robot" checkbox.';
    }
  }else{
    $msg = 'Sorry, you must<br />click the "I am not a Robot" checkbox.';
  }
}

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;max-width:640px;">
  <div style="text-align:center;margin:12px  auto;">
    <a onclick="expandcollapsediv('myrevinst',1)">A little help <span id="moreless1">&raquo;</span></a>
    <div id="myrevinst" style="text-align:left;height:0;padding:3px;margin:0 auto;overflow:hidden;transition:height .4s ease-in;max-width:640px;font-size:90%;border-bottom:1px solid <?=$colors[3]?>;">
      <p>
      If you have a MyREV account, enter your email address and password here.
      If you have a MyREV account but have forgotten your password, click the &ldquo;Forgot password&rdquo; link.
      If you do not have an account but would like one, click the &ldquo;Register&rdquo; link.<br /><br />
      If you would like to see a short tutorial on how to use MyREV, <a onclick="rlightbox('tut','');">click here</a>.
      </p>
    </div>
  </div>

  <form name="frm" method="post" action="/">
    <table style="margin:0 auto;">
      <tr><td colspan="2"><?=printsqlerr($msg)?></td></tr>
<?
  if($stopprocess==1){
      print('</table></form>');
  }else{
?>
      <tr>
        <td>Email Address:</td>
        <td><input type="text" name="myrevemail" value="<?=(isset($_POST['myrevemail']))?$_POST['myrevemail']:''?>" maxlength="60"></td>
      </tr>
      <tr>
        <td>Password:</td>
        <td><input type="password" name="password" value="" maxlength="20"></td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:12px;"><div class="g-recaptcha" data-callback="recaptcha_callback" data-sitekey="<?=$rcpsitekey?>"></div></td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:12px;">
        <input type="submit" name="btnsubmit" id="btnsubmit" value="Log in" onclick="return validate(document.frm);" <?if($isweb==1){?>disabled="disabled" <?}?>class="gobackbutton" style="color:<?=$colors[3]?>;" />
        </td>
      </tr>
      <tr>
        <td colspan="2"style="padding-top:20px;">
        <a onclick="register(document.frm)">Register</a>
        &nbsp;|&nbsp;
        <a onclick="forgot(document.frm)">Forgot password</a>
        </td>
      </tr>
    </table>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <input type="hidden" name="mitm" value="<?=$mitm?>" />
    <input type="hidden" name="page" value="40">
    <input type="hidden" name="origpage" value="<?=(isset($_POST['temp']))?$_POST['temp']:''?>">
    <input type="hidden" name="test" value="<?=$test?>" />
    <input type="hidden" name="book" value="<?=$book?>" />
    <input type="hidden" name="chap" value="<?=$chap?>" />
    <input type="hidden" name="vers" value="<?=$vers?>" />
    <input type="hidden" name="rtask" value="login" />
    <input type="hidden" name="oper" value="">
  </form>
<?}?>
  <div style="text-align:center;margin:40px  auto;">
    <p style="text-align:center;font-weight:bold;margin-bottom:0;"><a onclick="expandcollapsediv('whatismyrev',2)">What is MyREV? <span id="moreless2">&raquo;</span></a></p>
    <div id="whatismyrev" style="text-align:left;height:0;padding:3px;margin:0 auto;overflow:hidden;transition:height .4s ease-in;max-width:640px;font-size:90%;border-bottom:1px solid <?=$colors[3]?>;">
      <p>MyREV is a feature of the REV website that enables you to highlight verses in the Bible with one of several different colors, and keep your own notes on those verses.</p>
      <p>You can be signed in to MyREV from several devices, and any verses you highlight or any notes you have will appear on all your devices. You should only have to log in from each device one time.</p>
      <p>You can assign captions to the different colors, such as &ldquo;Favorite,&rdquo; &ldquo;Important,&rdquo; &ldquo;Question,&rdquo; &ldquo;Teaching,&rdquo; etc., or anything you like.</p>
      <p>From the MyREV section of the site, you can filter your highlighted verses by color, and sort them by color, canon, or arrange them in any order you want. You can also export your verses to Microsoft Word or as a PDF.</p>
      <p>Soon we hope to have a video explaining the features of MyREV and how to make the best use of them.</p>
      <p>Thanks, and God bless you!</p>
    </div>
  </div>
</div>
  <script>
     // VALIDATE EMAIL!!
    function validate(f){
      var msg = '';
      var ctl = '';
      f.myrevemail.value  = trim(f.myrevemail.value);
      f.password.value  = trim(f.password.value);
      if(msg=='' && !isValidEmail(f.myrevemail,1)){
        msg = 'A valid email address is required';
        ctl = f.myrevemail;
      }
      if(msg=='' && (f.password.value=='')){
        msg = 'You must enter a password to continue';
        ctl = f.password;
      }
      if(msg != ''){
       alert(msg);
       ctl.focus();
       ctl.select();
       return false;
      }
      f.rtask.value = 'login';
      f.oper.value = 'log1n';
      return true;
    }
    function forgot(f){
      f.rtask.value = 'forgot';
      f.submit();
    }
    function register(f){
      f.rtask.value = 'register';
      f.submit();
    }
    function isValidEmail(ctl,r) {
      var stmp = trim(ctl.value);
      if (r==0 && stmp=='') return true;
      var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9])+$/;
      if(stmp != '') return pattern.test(stmp);
      else return false;
    }
    function recaptcha_callback(){
      //alert("callback working");
      $('btnsubmit').disabled = false;
      $('btnsubmit').style.color = colors[1];
      $('btnsubmit').style.cursor = 'pointer';
    }
    function expandcollapsediv(id,idx){
      excoldiv(id); // in misc.js
      var div = $(id);
      if(div.style.height=='0px'){
        $('moreless'+idx).innerHTML='&raquo;';
      }else{
        $('moreless'+idx).innerHTML='&laquo;';
      }
    }

    setTimeout('document.frm.myrevemail.focus()', 300);
    //-->
  </script>

<? break;
//
//
//
//
//
case 'compreg':
  // Complete Registration
  $stitle = 'MyREV Registration';
  $regcode = $arqs[1];
  $row = rs('select myrevid, myrevname, myrevemail from myrevusers where regcode = \''.$regcode.'\' ');
  if($row){
    // success
    $myrevid = $row[0];
    $sesscode = keygen(30);
    $upd = dbquery('update myrevusers set regcode = null, cursession = \''.$sesscode.'\' where myrevid = '.$myrevid.' ');
    $msg = 'Your MyREV registration is successful!<br /><br />You are now logged in.<br /><br />Click <a href="/bcuk">here</a> to continue.';
    $msg.= '<br /><br />If you would like to see a short tutorial on how to use MyREV, <a onclick="rlightbox(\'tut\',\'\');">click here</a>.';
    print('<script>setCookie(\'myrevsid\', \''.$sesscode.'\', cookieexpiredays);</script>');
  }else{
    $msg = 'Sorry, it appears that you have already registered using that link.<br /><br />If you have forgotten your password, click <a href="/login">here</a> and then "Forgot password".';
  }
  print('<span class="pageheader">'.$stitle.'</span><br /><table border="0" cellpadding="2" cellspacing="0" align="center" style="font-size:90%">');
  print('<tr><td colspan="6">'.$msg.'</td></tr></table>');

  break;
//
//
//
//
//
case 'register':
  //print('Register');
    $stitle = 'MyREV Registration';
    $myrevname = '';
    $myrevemail= '';
    $password   = '';
    $oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
    if($oper=='register'){
      $recaptcha = new \ReCaptcha\ReCaptcha($rcpsecret);
      if(isset($_POST['g-recaptcha-response'])){
        $myrevname = processsqltext($_POST['myrevname'],  20, 0, 'missing username');
        $myrevemail= processsqltext($_POST['myrevemail'], 60, 0, 'missing email');
        $password   = processsqltext($_POST['password'],  20, 0, 'missing password');
        $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                          ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if($resp->isSuccess()){
          $row = rs('select \'x1x\' from myrevusers where myrevemail = '.$myrevemail.' ');
          if(!$row){
            $regcode = keygen(50);
            $sql = 'insert into myrevusers (myrevname, myrevemail, password, regcode, lastaccessed, regdate, ipaddress) values (
                   '.$myrevname.', '.$myrevemail.', '.$password.',\''.$regcode.'\', UTC_TIMESTAMP(), UTC_TIMESTAMP(), \''.((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'unknown').'\') ';
            $update = dbquery($sql);
            $msg = $sqlerr;
            if($msg==''){
              // send the email
              $myrevname = substr($myrevname, 1, strlen($myrevname)-2);
              $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
              $emlbody = getemailbody();
              $emlbody = str_replace('[content]', '<p>'.$myrevname.',</p>
                        <p>Thank you for registering for a MyREV account with the REV website!</p>
                        <p>Please click <a href="https://'.$site.'/login/'.$regcode.'">this link</a> to complete your MyREV registration:</p>
                        <p>If the above link is not clickable, please copy and paste the following URL into your browser&rsquo;s address bar:</p>
                        <p>https://'.$site.'/login/'.$regcode.'</p>
                        <p>Thanks, and God bless you!</p>', $emlbody);

              // sendemail($to, $from, $subject, $message)
              if($isweb==1)
                sendemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Registration', $emlbody);
              else
                sendsmtpemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Registration', $emlbody);

              ?>
              <span class="pageheader"><?=$stitle?></span><br />
              <div style="font-size:90%;width:480px;max-width:96%;margin:auto;">
                An email has been sent to <?=$myrevemail?>. You should receive it momentarily. If you do not receive it within a few minutes, please check your spam folder.
                There will be a link in the email that you must click to complete your MyREV registration.<br /><br />
                <?
                if($inapp==1)
                  print('It looks like you are in the REV App. Note that when you click the link in the email, your browser will open, and not the REV App.
                        This will complete the registration process, but you will still need to come back here and <a href="/myrev">log in</a>.<br /><br />');
                ?>
                Thanks, and God bless you!
              </div>
              <?
              exit(0);
            }
          }else{
            $msg = 'That email address is already in use.<br />Please <a href="/login">go to the login page</a> and click "Forgot password".';
            $myrevname = substr($myrevname, 1, strlen($myrevname)-2);
            $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
            $password   = substr($password, 1, strlen($password)-2);
          }
        }else{
          $msg = 'Sorry, you must<br />click the "I am not a Robot" checkbox.';
          $myrevname = substr($myrevname, 1, strlen($myrevname)-2);
          $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
          $password   = substr($password, 1, strlen($password)-2);
        }
      }else{
        $msg = 'Sorry, you must<br />click the "I am not a Robot" checkbox.';
        $myrevname = substr($myrevname, 1, strlen($myrevname)-2);
        $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
        $password   = substr($password, 1, strlen($password)-2);
      }
    }
    ?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;max-width:640px;">
  <div style="text-align:center;margin:12px  auto;">
    <a onclick="expandcollapsediv('myrevinst')">A little help <span id="moreless">&raquo;</span></a>
    <div id="myrevinst" style="text-align:left;height:0;padding:3px;margin:0 auto;overflow:hidden;transition:height .4s ease-in;max-width:640px;font-size:90%;border-bottom:1px solid <?=$colors[3]?>;">
      <p>
      To register, you need a username, a valid email address, and a password. Your username can be anything you like: your first name, a pseudonym, etc.
      Your email address must be valid, as when you click the &ldquo;Register&rdquo; button, an email will be sent to that email address containing a link that must be clicked to complete the registration process.
      Also required is a password, which can be anything you like.
      </p>
    </div>
  </div>

    <form name="frm" method="post" action="/">
      <table style="margin:0 auto;font-size:90%">
        <tr><td colspan="6"><?=printsqlerr($msg)?></td></tr>
        <tr>
          <td>Username:</td>
          <td><input type="text" name="myrevname" value="<?=$myrevname?>" maxlength="20"></td>
        </tr>
        <tr>
          <td>Email:</td>
          <td><input type="text" name="myrevemail" value="<?=$myrevemail?>" maxlength="60"></td>
        </tr>
        <tr>
          <td>Password:</td>
          <td><input type="password" name="password" value="<?=$password?>" maxlength="20"></td>
        </tr>
        <tr>
        <td colspan="2" style="padding-top:12px;"><div class="g-recaptcha" data-callback="recaptcha_callback" data-sitekey="<?=$rcpsitekey?>"></div></td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:12px;">
          <input type="submit" name="btnsubmit" id="btnsubmit" value="Register" onclick="return validate(document.frm);" disabled="disabled" class="gobackbutton" style="color:<?=$colors[3]?>;" />
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:20px;">
          <a onclick="forgot(document.frm)">Forgot password</a>
        </tr>
        <tr>
          <td colspan="2">
          <a onclick="location.href='/login'">Log in</a>
        </tr>
      </table>
      <script src="https://www.google.com/recaptcha/api.js" async defer></script>
      <input type="hidden" name="mitm" value="<?=$mitm?>" />
      <input type="hidden" name="page" value="40">
      <input type="hidden" name="test" value="<?=$test?>" />
      <input type="hidden" name="book" value="<?=$book?>" />
      <input type="hidden" name="chap" value="<?=$chap?>" />
      <input type="hidden" name="vers" value="<?=$vers?>" />
      <input type="hidden" name="rtask" value="register" />
      <input type="hidden" name="oper" value="">
    </form>
    <script>
    function validate(f){
      var msg = '';
      var ctl = '';
      f.myrevname.value  = trim(f.myrevname.value);
      f.myrevemail.value  = trim(f.myrevemail.value);
      f.password.value  = trim(f.password.value);
      if(msg=='' && f.myrevname.value.length<3){
       msg = 'User name is required, and must be at least three characters long.';
       ctl = f.myrevname;
      }
      if(msg=='' && !isValidEmail(f.myrevemail,1)){
       msg = 'A valid email address is required';
       ctl = f.myrevemail;
      }
      if(msg=='' && (f.password.value=='')){
       msg = 'You must enter a password to continue';
       ctl = f.password;
      }

      var tmp = f.password.value;
      var schar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_#$%&()!';
      for(var i=0;i<tmp.length;i++){
        if(schar.indexOf(tmp.charAt(i))<0){
          msg = 'Invalid character in Password. \n\nOnly letters, numbers, and _ # $ % & ( ) ! are permitted.\n\nNo spaces are allowed.';
          ctl = f.password;
          break;
        }
      }
      if(msg != ''){
        alert(msg);
        ctl.focus();
        ctl.select();
        return false;
      }
      f.oper.value = 'register';
      $('btnsubmit').value='Please wait..';
      setTimeout('$(\'btnsubmit\').disabled=true;', 50);
      return true;
    }

    function isValidEmail(ctl,r) {
      var stmp = trim(ctl.value);
      if (r==0 && stmp=='') return true;
      var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9])+$/;
      if(stmp != '') return pattern.test(stmp);
      else return false;
    }
    function recaptcha_callback(){
      //alert("callback working");
      $('btnsubmit').disabled = false;
      $('btnsubmit').style.color = colors[1];
      $('btnsubmit').style.cursor = 'pointer';
    }
    function forgot(f){
      f.rtask.value = 'forgot';
      f.submit();
    }
    function expandcollapsediv(id){
      excoldiv(id); // in misc.js
      var div = $(id);
      if(div.style.height=='0px'){
        $('moreless').innerHTML='&raquo;';
      }else{
        $('moreless').innerHTML='&laquo;';
      }
    }

    </script>
      <?
  break;
//
//
//
//
//
case 'forgot':
  // forgot password
  $stitle = 'Forgot Password';
  $stopprocess=0;
  $myrevemail='';
  $oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
  $msg = '';
  if($oper=='forgot'){
    $recaptcha = new \ReCaptcha\ReCaptcha($rcpsecret);
    if(isset($_POST['g-recaptcha-response'])){
      $myrevemail= processsqltext($_POST['myrevemail'], 60, 0, 'missing email');
      $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                        ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
      if($resp->isSuccess()){
        $sql = 'select myrevname, myrevemail, password from myrevusers where myrevemail = '.$myrevemail.' ';
        //print($sql);
        $row = rs($sql);
        if($row){
          // send the email
          $myrevname= $row['myrevname'];
          $myrevemail= $row['myrevemail'];
          $msg = $myrevname.', your password has been sent to '.$myrevemail.'.';
          $emlbody = getemailbody();
          $emlbody = str_replace('[content]', '<p>'.$myrevname.',</p>
                    <p>The password for your MyREV account on the REV website is: '.$row['password'].'</p>
                    <p>Click <a href="https://'.$site.'/login">here</a> to log in.</p>
                    <p>Thanks, and God bless you!</p>', $emlbody);

          // sendemail($to, $from, $subject, $message)
          if($isweb==1)
            sendemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Registration', $emlbody);
          else
            sendsmtpemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Registration', $emlbody);

          $stopprocess=1;
        }else{
          $msg = 'Sorry, we do not have that email address in our system.';
          $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
        }
      }else{
        $msg = 'Sorry, you must<br />click the "I am not a Robot" checkbox.';
        $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
      }
    }else{
      $msg = 'Sorry, you must<br />click the "I am not a Robot" checkbox.';
      $myrevemail= substr($myrevemail, 1, strlen($myrevemail)-2);
    }
  }
  ?>
  <span class="pageheader"><?=$stitle?></span>
  <div style="margin:0 auto;max-width:640px;">
  <p>If you have a MyREV account but have forgotten your password, enter the email address that you registered with, check the captcha, and click &ldquo;Submit&rdquo;.</p>
  <form name="frm" method="post" action="/">
    <table style="margin:0 auto;">
      <tr><td colspan="6"><?=printsqlerr($msg)?></td></tr>
<?
  if($stopprocess==1){
      print('<tr><td colspan="2" style="padding-top:20px;text-align:left;"> You should receive the email momentarily. If you do not receive it within a few minutes, please check your spam folder.<br /><br /><a href="/myrev">Click here</a> to log in.</td></tr>');
      print('</table></form></div></body></html>');
      exit(0);
  }
?>
      <tr>
        <td>Email Address:</td>
        <td><input type="text" name="myrevemail" value="<?=$myrevemail?>" maxlength="60"></td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:12px;"><div class="g-recaptcha" data-callback="recaptcha_callback" data-sitekey="<?=$rcpsitekey?>"></div></td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:12px;">
        <input type="submit" name="btnsubmit" id="btnsubmit" value="Submit" onclick="return validate(document.frm);" disabled="disabled" class="gobackbutton" style="color:<?=$colors[3]?>;" />
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding-top:20px;">
        <a onclick="location.href='/myrev'">Log in</a>
      </tr>
      <tr>
        <td colspan="2">
        <a onclick="register(document.frm)">Register</a>
      </tr>
    </table>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <input type="hidden" name="mitm" value="<?=$mitm?>" />
    <input type="hidden" name="page" value="40">
    <input type="hidden" name="test" value="<?=$test?>" />
    <input type="hidden" name="book" value="<?=$book?>" />
    <input type="hidden" name="chap" value="<?=$chap?>" />
    <input type="hidden" name="vers" value="<?=$vers?>" />
    <input type="hidden" name="rtask" value="forgot" />
    <input type="hidden" name="oper" value="">
  </form>
    </div>
  <script>
  function validate(f){
    var msg = '';
    var ctl = '';
    f.myrevemail.value  = trim(f.myrevemail.value);
    if(msg=='' && !isValidEmail(f.myrevemail,1)){
     msg = 'A valid email address is required';
     ctl = f.myrevemail;
    }
    if(msg != ''){
      alert(msg);
      ctl.focus();
      ctl.select();
      return false;
    }
    f.oper.value = 'forgot';
    $('btnsubmit').value='Please wait..';
    setTimeout('$(\'btnsubmit\').disabled=true;', 50);
    return true;
  }

  function register(f){
    f.rtask.value = 'register';
    f.submit();
  }

  function isValidEmail(ctl,r) {
    var stmp = trim(ctl.value);
    if (r==0 && stmp=='') return true;
    var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9])+$/;
    if(stmp != '') return pattern.test(stmp);
    else return false;
  }
  function recaptcha_callback(){
    //alert("callback working");
    $('btnsubmit').disabled = false;
    $('btnsubmit').style.color = colors[1];
    $('btnsubmit').style.cursor = 'pointer';
  }
  </script>
  <?
  break;
//
//
//
//
//
case 'manage':
  //print('manage account');
  $oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
  if($oper=='update'){

    $myrevname = processsqltext($_POST['myrevname'],  20, 0, 'missing username');
    //$myrevemail= processsqltext($_POST['myrevemail'], 60, 0, 'missing email');
    $password = $_POST['password'];
    $oldpassword = $password;
    $newpass1 = $_POST['newpassword1'];
    if($newpass1 != '') $password = $newpass1;
    $password = processsqltext($password,  20, 0, 'missing password');

    $row = rs('select password from myrevusers where myrevid = '.$myrevid.' ');
    if($row[0] === $oldpassword){
      $sql = 'update myrevusers set
              myrevname = '.$myrevname.',
              password = '.$password.'
              where myrevid = '.$myrevid.' ';
      $update = dbquery($sql);
      $msg = $sqlerr;
      if($msg=='') $msg = datsav;
    }else{
      $msg = 'The password you entered is incorrect.';
    }
    $testemail = ((isset($_POST['testemail']))?$_POST['testemail']:0);
    if($superman==1 && $testemail==1){
      $sql = 'select myrevname, myrevemail from myrevusers where myrevid = '.$myrevid.' ';
      $row = rs($sql);
      if($row){
        // send the email
        $myrevname= $row['myrevname'];
        $myrevemail= $row['myrevemail'];
        $msg = $myrevname.', a test email has been sent to '.$myrevemail.'.';
        $emlbody = getemailbody();
        $emlbody = str_replace('[content]', '<p>'.$myrevname.',</p>
                  <p>The MyREV email system is  working properly.</p>
                  <p>Thanks, and God bless you!</p>', $emlbody);
        if($isweb==1)
          sendemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV test email', $emlbody);
        else
          sendsmtpemail($myrevemail, 'revisedenglishversion@gmail.com', 'MyREV Test Email', $emlbody);

      }

    }

  }
  $stitle = 'Manage My REV Account';
  $sql = 'select myrevname, myrevemail, password from myrevusers where myrevid = '.$myrevid.' ';
  $row = rs($sql);
  ?>
  <form name="frm" method="post" action="/">
  <span class="pageheader"><?=$stitle?></span><br />
    <table border="0" cellpadding="2" cellspacing="0" align="center" style="font-size:90%">
      <tr><td colspan="6"><?=printsqlerr($msg)?></td></tr>
      <tr>
        <td>Username:</td>
        <td><input type="text" name="myrevname" value="<?=$row['myrevname']?>" maxlength="20"></td>
      </tr>
      <tr>
        <td>Email:</td>
        <td><?=$row['myrevemail']?></td>
      </tr>
      <tr>
        <td>Password:</td>
        <td><input type="password" name="password" value="<?=$row['password']?>" maxlength="20"></td>
      </tr>
      <tr>
        <td>New Password:</td>
        <td><input type="password" name="newpassword1" value="" maxlength="20"></td>
      </tr>
      <tr>
        <td>Repeat:</td>
        <td><input type="password" name="newpassword2" value="" maxlength="20"></td>
      </tr>
    <?if($superman==1){?>
      <tr>
        <td style="color:red;">Superman:</td>
        <td>Submit test email <input type="checkbox" name="testemail" value="1"></td>
      </tr>
    <?}?>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" name="asd" value="Submit" onclick="return validate(document.frm);"></td>
      </tr>
      <tr>
        <td colspan="2">
        <a href="/bcuk" onclick="return logout();">Log out</a>
        </td>
      </tr>
      <tr>
        <td colspan="2">
        &nbsp;<br />
        Go to <a href="/myrev">My REV</a>.
        </td>
      </tr>
    </table>
    <input type="hidden" name="mitm" value="<?=$mitm?>" />
    <input type="hidden" name="page" value="40">
    <input type="hidden" name="test" value="<?=$test?>" />
    <input type="hidden" name="book" value="<?=$book?>" />
    <input type="hidden" name="chap" value="<?=$chap?>" />
    <input type="hidden" name="vers" value="<?=$vers?>" />
    <input type="hidden" name="rtask" value="manage" />
    <input type="hidden" name="oper" value="">
  </form>
  <script>
  function validate(f){
    var msg = '';
    var ctl = '';
    f.myrevname.value  = trim(f.myrevname.value);
    //f.myrevemail.value  = trim(f.myrevemail.value);
    f.password.value  = trim(f.password.value);
    f.newpassword1.value = trim(f.newpassword1.value);
    f.newpassword2.value = trim(f.newpassword2.value);
    if(msg=='' && f.myrevname.value.length<3){
     msg = 'User name is required, and must be at least three characters long.';
     ctl = f.myrevname;
    }
    //if(msg=='' && !isValidEmail(f.myrevemail,1)){
    // msg = 'A valid email address is required';
    // ctl = f.myrevemail;
    //}
    if(msg=='' && (f.password.value=='')){
     msg = 'You must enter your current password to continue';
     ctl = f.password;
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
      var schar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_#$%&()!';
      for(var i=0;i<tmp.length;i++){
        if(schar.indexOf(tmp.charAt(i))<0){
          msg = 'Invalid character in Password. \n\nOnly letters, numbers, and _ # $ % & ( ) ! are permitted.\n\nNo spaces are allowed.';
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
    f.oper.value = 'update';
    return true;
  }

  function isValidEmail(ctl,r) {
    var stmp = trim(ctl.value);
    if (r==0 && stmp=='') return true;
    var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9])+$/;
    if(stmp != '') return pattern.test(stmp);
    else return false;
  }

  function logout(){
    if(confirm('Are you sure you want to log out?')){
      setCookie('myrevsid','public',cookieexpiredays);
    }else{return false;}
  }
  </script>
    <?
  break;
default:
  print('I\'m lost');

  break;
}
function getemailbody(){
  $ret = '<html><head></head><body style="width:640px;border:1px solid black;padding:0;">';
  $ret.= '<img src="cid:sandt_rev_logo" />';
  $ret.= '<div style="padding:8px;">[content]</div>';
  $ret.= '<p style="text-align:center;font-size:80%;color:#aaa;">Copyright &copy; '.date('Y').' Spirit & Truth<br />PO Box 1737, Martinsville, IN 46151 US</p>';
  $ret.= '</body></html>';
  return $ret;
}
