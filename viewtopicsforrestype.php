<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $resedit==0) die('unauthorized access');

$restype = ((isset($_REQUEST['restype']))?$_REQUEST['restype']:'unknown');

switch($restype){
case 'comapx':
  $stitle = 'REV Commentary/Appendices';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid = 0) cnt from topic t where t.topicid in (select topicid from topic_assoc where resourceid = 0) order by 1';
  break;
case 'sandtvid':
  $stitle = 'SandT Videos';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where source = \'spiritandtruth\')) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'spiritandtruth\')) order by 1';
  break;
case 'sandtvfvid':
  $stitle = 'SandT_VF Videos';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where source = \'spiritandtruth_vf\')) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'spiritandtruth_vf\')) order by 1';
  break;
case 'buvid':
  $stitle = 'BU Videos';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where source = \'biblicalunitarian\')) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'biblicalunitarian\')) order by 1';
  break;
case 'podbean':
  $stitle = 'Podbean Audios';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where source = \'podbean\')) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'podbean\')) order by 1';
  break;
case 'castos':
  $stitle = 'Castos Audios';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where source = \'castos\')) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'castos\')) order by 1';
  break;
case 'seminar':
  $stitle = 'REV Seminar Episodes';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where resourcetype = 4)) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where resourcetype = 4)) order by 1';
  break;
case 'article':
  $stitle = 'Articles';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where resourcetype = 5)) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where resourcetype = 5)) order by 1';
  break;
case 'library':
  $stitle = 'Library Items';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid > 0 and ta.resourceid in (select resourceid from resource where resourcetype = 7)) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where resourcetype = 7)) order by 1';
  break;
case 'playlist':
  $stitle = 'REV Playlists';
  $sql = 'select t.topic, (select count(*) from topic_assoc ta where ta.topicid = t.topicid and ta.resourceid < 0) cnt from topic t where topicid in (select topicid from topic_assoc where resourceid < 0) order by 1';
  break;
default:
  exit('unknown restype: '.$restype);
  break;
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

<h3 style="text-align:center"><span id="totcnt">x</span> Topics contain <?=$stitle?></h3>
<form name="frm" method="post" action="/viewtopicsforrestype.php">
  <table border="0" cellpadding="4" cellspacing="0" style="font-size:80%" align="center">
    <tr><td align="center"><input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
<?

$ni=0;
$tops = dbquery($sql);
while($row = mysqli_fetch_array($tops)){
  print('<tr><td><a href="/topic/'.str_replace(' ', '_', $row[0]).'" target="_blank">'.$row[0].' ('.$row[1].')'.'</a></td></tr>');
  $ni++;
}?>
    <tr><td align="center"><input type="button" name="btnclose" value="Close" onclick="olClose('');"></td></tr>
  </table>
  <input type="hidden" name="restype" value="<?=$restype?>">
</form>
  <script>

     function $(el) {return parent.document.getElementById(el);}

     function olClose(locn) {
       var ol = $("overlay");
       ol.style.display = 'none';
       if(locn!='') parent.document.location.href=locn;
       setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
     }

     document.body.addEventListener("load", popcnt());

     function popcnt(){
       document.getElementById('totcnt').innerHTML='<?=$ni?>';
     }

     //setTimeout("document.getElementById('totcnt').innerHTML='<?=$ni?>'", 100);

  </script>
</body>
</html>
<?


?>
