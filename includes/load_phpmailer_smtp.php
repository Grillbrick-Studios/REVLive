<?php
  // include PHP Mailer
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  require_once $docroot.'/includes/phpmailer/PHPMailer.php';
  require_once $docroot.'/includes/phpmailer/SMTP.php';
  require_once $docroot.'/includes/phpmailer/Exception.php';

   /*
    *
    * Function send_mail_by_PHPMailer($to, $from, $subject, $message);
    * send a mail by PHPMailer method
    * @Param $to -> mail to send
    * @Param $from -> sender of mail
    * @Param $subject -> suject of mail
    * @Param $message -> html content with datas
    * @Return true if success / Json encoded error message if error
    * !! need -> classes/Exception.php - classes/PHPMailer.php - classes/SMTP.php
    *
    */
function sendemail($to, $from, $subject, $message){
  global $userid, $docroot;
  // SEND MAIL by PHP MAILER
  $mail = new PHPMailer();
  $mail->CharSet = 'UTF-8';
  $mail->isSMTP(); // Use SMTP protocol
  //$mail->SMTPDebug  = 1;
  $mail->Mailer = "smtp";
  $mail->Host = 'smtp.gmail.com'; // Specify  SMTP server
  $mail->SMTPAuth = true; // Auth. SMTP
  $mail->Username = 'revisedenglishversion@gmail.com'; // Mail who send by PHPMailer
  $mail->Password = 'revtfore2##L'; // your pass mail box
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
    exit;
  }else{
    // return true if message is send
    return true;
  }
}


