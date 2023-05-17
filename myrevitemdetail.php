<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || empty($superman) || $superman==0) die('unauthorized access');

$myrvid = (int) ((isset($_REQUEST['myrvid']))?$_REQUEST['myrvid']:-1);
$test = (int) ((isset($_REQUEST['test']))?$_REQUEST['test']:1);
$book = (int) ((isset($_REQUEST['book']))?$_REQUEST['book']:40);
$chap = (int) ((isset($_REQUEST['chap']))?$_REQUEST['chap']:1);
$vers = (int) ((isset($_REQUEST['vers']))?$_REQUEST['vers']:1);

if ($myrvid==-1) {die('bad myrvid');}

$stitle = 'Item detail';


?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center"><?=$stitle?></h2>
  <table border="1" cellpadding="1" cellspacing="0" style="font-size:80%" align="center">
    <tr><td colspan="7" align="center"><input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
    <tr><td>
<?
if($test==0 && $book==0)
  $sql = 'select notes from myrevusers where myrevid = '.$myrvid.' ';
else
  $sql = 'select myrevnotes
          from myrevdata
          where myrevid = '.$myrvid.'
          and testament = '.$test.'
          and book = '.$book.'
          and chapter = '.$chap.'
          and verse = '.$vers.' ';

$row = rs($sql);
print($row[0]);
?>
    </td></tr>
    <tr><td colspan="7" align="center"><input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
  </table>
  <script>

     function $(el) {return parent.document.getElementById(el);}

     function olClose(locn) {
       var ol = $("overlay");
       ol.style.display = 'none';
       if(locn!='') parent.document.location.href=locn;
       setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
     }

  </script>
</body>
</html>

