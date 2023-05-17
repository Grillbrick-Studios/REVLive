<?php
// 20190502 added next three lines due to problems with hostmonster server upgrade
//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");

$site=((isset($_SERVER['HTTP_HOST']))?strtolower($_SERVER['HTTP_HOST']):'nohost');
if($site=='nohost') die('no host');
$time_start = microtime(true);
$dbhits = 0;

if($site=='www.revisedenglishversion.com'){ // live site
  $dbserv = 'localhost';
  $dbuser = 'revengli_revuser';
  $dbpass = 'rev312englishv3r';
  $dbname = 'revengli_rev';
  error_reporting(0);
}else if($site=='www.revdevbible.com'){ // server dev site
  $dbserv = 'localhost';
  $dbuser = 'revdevbible_revdbuser';
  $dbpass = 'rev312dbuser';
  $dbname = 'revdevbible_revbible';
  error_reporting(E_ALL);
}else if($site=='www.revdevbible2.com'){ // server dev site
  $dbserv = 'localhost';
  $dbuser = 'revdevbible2_revdbuser';
  $dbpass = 'rev312dbuser';
  $dbname = 'revdevbible2_revbible';
  error_reporting(E_ALL);
}else{ // woodsware
  $dbserv = 'localhost';
  $dbuser = 'revuser';
  $dbpass = 'rev312user';
  $dbname = 'rev';
  error_reporting(E_ALL);
  //error_reporting(0);
}
?>
