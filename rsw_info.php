<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

checklogin();

if($userid==1) phpinfo();

?>
