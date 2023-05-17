<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || empty($superman) || $superman==0) die('unauthorized access');

$ip   = ((isset($_REQUEST['ip']))?$_REQUEST['ip']:'unknown');
$uid  = ((isset($_REQUEST['uid']))?$_REQUEST['uid']:'0');
if($ip=='unknown' && $uid>0) $mode='usr'; else $mode='ip';

$stitle = 'Views by '.(($mode=='ip')?'IP':'User');


$alias= ((isset($_REQUEST['alias']))?$_REQUEST['alias']:'unknown');

$daysback = ((isset($_REQUEST['daysback']))?$_REQUEST['daysback']:1);

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
<div style="margin:0 auto;text-align:center">
  <b>For: &ldquo;<?=$alias?>&rdquo; &nbsp;Daysback: <?=$daysback?></b>
</div>
<form name="frm" method="post" action="/logviews_ip.php">
  <table border="1" cellpadding="1" cellspacing="0" style="font-size:80%" align="center">
    <tr><td colspan="7" align="center"><input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
    <tr><td>cnt</td><td>Page</td><td>Test</td><td>Book</td><td>Chap</td><td>Vers</td><td>When</td></tr>
<?

$sql = 'select page, testament, book, chapter, verse, viewtime, misc, userid
        from viewlogs ';
if($mode=='ip')
  $sql .= 'where remoteip = \''.$ip.'\'';
else
  $sql .= 'where userid = '.$uid.' ';
$sql .= 'and viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        order by viewtime ';
//print($sql);

$logs = dbquery($sql);
$ni=1;
while($row = mysqli_fetch_array($logs)){
?>
    <tr>
      <td><?=$ni++;?></td>
      <td><?=getpage($row)?></td>
      <td><?=(($row['testament'] > -1)?$row['testament']:'-')?></td>
      <td><?=(($row['page']==3 || $row['page']==34 || ($row['page']>41 && $row['page']<45))?$row['misc']:getbooktitle($row['testament'], $row['book'], 0))?></td>
      <td><?=(($row['chapter'] > 0)?$row['chapter']:'-')?></td>
      <td><?=(($row['verse'] > 0)?$row['verse']:'-')?></td>
      <td><?=converttouserdate($row['viewtime'], $timezone)?></td>
    </tr>
<?}?>
    <tr><td colspan="7" align="center"><input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
  </table>
</form>
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
<?
  function getpage($r){
      $p = $r['page'];
      $content = array(       // page
        "viewbible.php",      // 0  // for viewing the bible
        "editverscomm.php",   // 1  // for editing a bible verse and its commentary
        "intro.php",          // 2  // !!!no longer used!!!
        "srch.php",           // 3  // search the REV/commentary
        "viewcomm.php",       // 4  // for viewing commentary
        "viewverscomm.php",   // 5  // for viewing a verse and its commentary
        "editbook.php",       // 6  // for editing a bible book and its commentary
        "login.php",          // 7  // for logging in, duh
        "editappxintro.php",  // 8  // for editing appendices and introductions
        "prefs.php",          // 9  // preferences
        "viewbookcomm.php",   // 10 // for viewing book commentary
        "manageappxintro.php",// 11 // for creating intros/appx's and activating/ordering them
        "user.php",           // 12 // user prefs
        "users.php",          // 13 // for superusers to manage users
        "viewappxintro.php",  // 14 // for viewing appendices and introductions
        "stats.php",          // 15 // website statistics, for logged in users only
        "manageips.php",      // 16 // for superusers to manage non-logged ips
        "sopsmanage.php",     // 17 // manage locked sops sessions
        "useredits.php",      // 18 // user edits
        "edituseredit.php",   // 19 // edit user edit
        "whatsnew.php",       // 20 // what's new
        "mapips.php",         // 21 // for mapping known ips
        "exports.php"        // 22 // for exporting to MS Word
        );
    if($p<23)
      $ret = $content[$p];
    else{
      $ret = '';
      switch($p){
      case 90:$ret = 'MSW BookComm';break;
      case 91:$ret = 'MSW appx/intro';break;
      case 92:$ret = 'MSW bible';break;
      case 93:$ret = 'MSW Comtry';break;
      case 94:$ret = 'MSW verse';break;
      case 95:$ret = 'PDF BookComm';break;
      case 96:$ret = 'PDF appx/intro';break;
      case 97:$ret = 'PDF bible';break;
      case 98:$ret = 'PDF Comtry';break;
      case 99:$ret = 'PDF verse';break;
      case 20:$ret = 'WhatsNew';break;
      case 25:$ret = 'REV Blog';break;
      case 26:$ret = 'View REV Blog';break;
      case 27:$ret = 'EDIT REV Blog';break;
      case 28:$ret = 'eS/MS Export';break;
      case 29:$ret = 'Donate';break;
      case 33:$ret = 'Topics';break;
      case 34:$ret = 'Chronology';break;
      case 36:$ret = 'Resources';break;
      case 39:$ret = 'XML Export';break;
      case 41:$ret = 'eSword Export';break;
      case 42:$ret = 'MySword Export';break;
      case 43:$ret = 'BibleWorks Export';break;
      case 44:$ret = 'theWord Export';break;
      case 45:$ret = 'Export Looking';break;
      case 46:$ret = 'parsetest.php';break;
      case 47:$ret = 'Strongs: '.$r['misc'];break;
      case 48:$ret = 'DL ES CommV';break;
      case 49:$ret = 'DL ES Appx';break;
      case 50:$ret = 'DL MS Bible';break;
      case 51:$ret = 'DL MS Comm';break;
      case 52:$ret = 'DL MS CommV';break;
      case 53:$ret = 'DL MS Appx';break;
      case 54:$ret = 'DL BibleWks';break;
      case 55:$ret = 'DL TW Bible';break;
      case 56:$ret = 'DL TW Comm';break;
      case 57:$ret = 'DL TW CommV';break;
      case 58:$ret = 'DL TW Appx';break;
      case 59:$ret = 'iBS Export Looker';break;
      case 60:$ret = 'DL iBS Bible';break;
      case 61:$ret = 'DL iBS Comm';break;
      case 62:$ret = 'DL iBS CommV';break;
      case 63:$ret = 'DL iBS Appx';break;
      case 64:$ret = 'DL REV_Swordsearcher';break;
      case 65:$ret = 'SSrchr Export Looker';break;
      case 66:$ret = 'DL REV_Accordance';break;
      case 67:$ret = 'Accordance Expt Lookr';break;
      case 200:$ret='JSON_REV_timestamp';break;
      case 201:$ret='JSON_REV_Bible';break;
      case 202:$ret='JSON_REV_Commentary';break;
      case 203:$ret='JSON_REV_Appendices';break;
      case 300:$ret = 'MyREV';break;
      case 301:$ret = 'MRv <span style="color:red">edit</span>note';break;
      case 302:$ret = 'MRv captions';break;
      case 303:
        //$ret = 'MRV: '.$r['testament'].'|'.$r['book'].'|'.$r['chapter'].'|'.$r['verse'];
        $ret = 'MRv: viewnote';
        break;
      case 307:$ret = 'Editor Notes';break;
      case 320:$ret = 'JSON REV export';break;
      case 400:$ret = 'MRv JSON hit';break;
      case 450:
      case 451:
      case 452:$ret = $r['misc'];break;
      case 500:$ret = 'Nav menu';break;
      default:
        $ret = 'unknown page: '.$p;
      }
    }
    if($r['userid']=='-7') $ret='(<span style="color:red;">a</span>)'.$ret;
    return $ret;
  }




?>
