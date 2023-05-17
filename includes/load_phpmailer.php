<?php
  // include PHP Mailer
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  use PHPMailer\PHPMailer\OAuth;
  //Alias the League Google OAuth2 provider class
  use League\OAuth2\Client\Provider\Google;

  //Load dependencies from composer
  //If this causes an error, run 'composer install'
  require 'phpmailer/vendor/autoload.php';

function sendemail($to, $from, $subject, $message){
  global $userid, $docroot, $site;
  $mail = new PHPMailer();
  $mail->isSMTP(); // Use SMTP protocol

  //Enable SMTP debugging
  //SMTP::DEBUG_OFF = off (for production use)
  //SMTP::DEBUG_CLIENT = client messages
  //SMTP::DEBUG_SERVER = client and server messages
  $mail->SMTPDebug = SMTP::DEBUG_OFF;

  //Set the hostname of the mail server
  $mail->Host = 'smtp.gmail.com';
  //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
  $mail->Port = 587;
  //Set the encryption mechanism to use - STARTTLS or SMTPS
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  //Whether to use SMTP authentication
  $mail->SMTPAuth = true;
  $mail->AuthType = 'XOAUTH2';
  $mail->CharSet = 'UTF-8';


switch($site){
  case 'www.revisedenglishversion.com':
  case 'www.revdevbible.com':
  case 'www.revdevbible2.com':
    $email        = 'revisedenglishversion@gmail.com';
    $clientId     = '141752347047-shs5ggv57kvi1t6o3ac8aelbl5dg5qmq.apps.googleusercontent.com';
    $clientSecret = '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';
    $refreshToken = '1//09TLta6ljyt12CgYIARAAGAkSNwF-L9Ir07OJghJB72AaQ9okUGuD7LbqPAOhaOkGXrPEp9UqGl6OCu7do8A4BgINb8AHztc9KuE';
    break;
  case 'revdev.woodsware.com':
    $email        = 'rostwoods@gmail.com';
    $clientId     = '623603562289-no5ll7oss4rss40coa038sjl61u9v218.apps.googleusercontent.com';
    $clientSecret = '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';
    $refreshToken = '1//01LqjlkAzV9UDCgYIARAAGAESNwF-L9Irq5v1n4lOlPmnGRW6wCXTl5QzVsD7n2zZq8uccsZs4LfldR7LiOPv2fYlZJ506PiClOI';
    break;
  default:
    die('unknown site: '.$site);
}  

  //Create a new OAuth2 provider instance
  $provider = new Google(
    [
      'clientId' => $clientId,
      'clientSecret' => $clientSecret,
    ]);

  $mail->setOAuth(
    new OAuth(
      [
        'provider' => $provider,
        'clientId' => $clientId,
        'clientSecret' => $clientSecret,
        'refreshToken' => $refreshToken,
        'userName' => $email,
      ]
    )
  );

  //Set who the message is to be sent from
  //For gmail, this generally needs to be the same as the user you logged in as
  $mail->setFrom($email, 'Sender Name');
  $mail->setFrom($email, 'RevisedEnglishVersion.com'); // Mail to send at

  //Set who the message is to be sent to
  $mail->addAddress($to);
  $mail->addBCC('revisedenglishversion@gmail.com');

  // if you want to send email to multiple users, then add the email addresses you which you want to send.
  //$mail->addAddress('reciver2@gmail.com');
  //$mail->addAddress('reciver3@gmail.com');

  $mail->isHTML(true);

  //Set the subject line
  $mail->AddEmbeddedImage($docroot.'/i/sandt_rev_logo.png', 'sandt_rev_logo');
  $mail->Subject = $subject;
  $mail->Body = $message;

  //Replace the plain text body with one created manually
  // $mail->AltBody = 'This is a plain-text message body';

  //For Attachments
  //$mail->addAttachment('/var/tmp/file.tar.gz');  // Add attachments
  //$mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // You can specify the file name

  //send the message, check for errors
  //if (!$mail->send()) {
  //   echo 'Mailer Error: ' . $mail->ErrorInfo;
  //} else {
  //   echo 'Message sent!';
  //}
  if( !$mail->send() ){
    if($userid==1){
      // render error if it is
      $tab = array('error' => 'Mailer Error: '.$mail->ErrorInfo );
      echo json_encode($tab);
    }else{
      echo "Sorry, there was an error sending the email";
    }
    exit;
  }else{
    // return true if message is send
    return true;
  }
}

function sendsmtpemail($to, $from, $subject, $message){
  global $userid, $docroot;
  // SEND MAIL by PHP MAILER
  print('<h3 style="text-align:center;color:red;">!!cannot send email from local installation!!<br />Test on revdevbible.com</h3>');
  return true;

  $mail = new PHPMailer();
  $mail->CharSet = 'UTF-8';
  $mail->isSMTP(); // Use SMTP protocol
  $mail->SMTPDebug = SMTP::DEBUG_SERVER;
  //$mail->SMTPDebug  = 1;
  $mail->Mailer = "smtp";
  $mail->Host = ''; // Specify  SMTP server
  $mail->SMTPAuth = true; // Auth. SMTP
  $mail->Username = ''; // Mail who send by PHPMailer
  $mail->Password = ''; // your pass mail box
  $mail->SMTPSecure = 'tls'; // Accept SSL
  $mail->Port = 587; // port of your out server
  $mail->setFrom('revisedenglishversion@gmail.com'); // Mail to send at
  $mail->addAddress($to); // Add sender
  //$mail->addReplyTo('revisedenglishversion@gmail.com'); // Adress to reply
  $mail->addBCC('revisedenglishversion@gmail.com');
  $mail->isHTML(true); // use HTML message
  $mail->AddEmbeddedImage($docroot.'/i/sandt_rev_logo.png', 'sandt_rev_logo');
  $mail->Subject = $subject;
  $mail->Body = $message;

  // SEND
  if( !$mail->send() ){
    if($userid==1){
      // render error if it is
      $tab = array('error' => 'Mailer Error: '.$mail->ErrorInfo );
      echo json_encode($tab);
    }else{
      echo "Sorry, there was an error sending the email";
    }
    //exit;
  }else{
    // return true if message is send
    return true;
  }
}


