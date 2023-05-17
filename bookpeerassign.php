<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || empty($superman) || $superman==0) die('unauthorized access');

$test = ((isset($_REQUEST['testament']))?$_REQUEST['testament']:1);
$book = ((isset($_REQUEST['book']))?$_REQUEST['book']:40);
$oper = ((isset($_REQUEST['oper']))?$_REQUEST['oper']:'nope');
$sqlerr = '';
$saved = 0;
if($oper == 'sav'){
  //print('saving..');
  $qry = dbquery('delete from book_peer where testament = '.$test.' and book = '.$book.' ');
  $pcount = $_REQUEST['pcount'];
  for($ni=0; $ni<$pcount; $ni++){
    if(isset($_REQUEST['pedit'.$ni]))
      $qry = dbquery('insert into book_peer (testament, book, userid) values ('.$test.', '.$book.', '.$_REQUEST['uid'.$ni].') ');
  }
  $saved=1;
  if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
}

$sql = 'select title, ifnull(tagline, title) tagline from book where testament = '.$test.' and book = '.$book.' ';
$row = rs($sql);
switch($test){
case 0:
case 1:
  $stitle = $row['title']; break;
case 3:
case 4:
  $stitle = $row['tagline']; break;
}

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

<h3 style="text-align:center;margin-bottom:0;">Reviewer Assignment for<br />&ldquo;<?=$stitle?>&rdquo;</h3>
<form name="frm" method="post" action="/bookpeerassign.php">
  <table border="0" cellpadding="4" cellspacing="0" style="font-size:90%" align="center">
    <tr><td colspan="2"><?=printsqlerr($sqlerr)?>&nbsp;</td></tr>
    <tr><th style="text-align:left;">Who</th><th>Assign</th></tr>
<?

$sql = 'select userid, ifnull(revusername, myrevname) uname, permissions from myrevusers where userid > 0 and myrevid > 0 order by 2 ';
$ni=0;
$qry = dbquery($sql);
while($row = mysqli_fetch_array($qry)){
  $perms = explode(':', $row['permissions']);
  $localpeernotes= (int) isset($perms[6])?$perms[6]:0;
  //print('lpn: '.$localpeernotes.'<br />');
  if($localpeernotes>0){
    $sql = 'select 1 from book_peer where testament = '.$test.' and book = '.$book.' and userid = '.$row['userid'].' ';
    $rrw = rs($sql);
    if($rrw) $ispeer = 1;
    else $ispeer = 0;
    print('<tr>');
    print('<td>'.$row['uname'].'<input type="hidden" name="uid'.$ni.'" value="'.$row['userid'].'" /></td>');
    print('<td style="text-align:center;"><input type="checkbox" name="pedit'.$ni.'" value="1"'.fixchk($ispeer).' /></td>');
    print('</tr>');
    $ni++;
  }
}
?>
    <tr><td align="center"><input type="submit" value="Submit" onclick="document.frm.oper.value='sav';" /> <input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
  </table>
  <input type="hidden" name="testament" value="<?=$test?>">
  <input type="hidden" name="book" value="<?=$book?>">
  <input type="hidden" name="pcount" value="<?=$ni?>">
  <input type="hidden" name="oper" value="">
</form>
  <script>

     function $(el) {return parent.document.getElementById(el);}

     function olClose(locn) {
       var ol = $("overlay");
       ol.style.display = 'none';
       <?if($saved==1){?>
       parent.document.location.reload();
       <?}?>
       setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
     }

  </script>
</body>
</html>
<?


?>
