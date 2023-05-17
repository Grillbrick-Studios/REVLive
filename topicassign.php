<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $resedit==0) die('unauthorized access');
$resourceid=((isset($_REQUEST['resourceid']))?$_REQUEST['resourceid']:'0');

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
$row=rs('select title from resource where resourceid = '.$resourceid.' ');
$stitle = '&ldquo;'.$row[0].'&rdquo;<br />is in the following topics:';
$reloadparent=0;

if($oper=='savtopics'){
  $mcnt = $_POST['mcnt'];
  for($ni=0;$ni<$mcnt;$ni++){
    if(isset($_POST['chkdel'.$ni]) && $_POST['chkdel'.$ni]==1){
      $tid = $_POST['resource'.$ni];
      $sql = 'delete from topic_assoc
              where resourceid = '.$resourceid.'
              and topicid = '.$tid.' ';
      //print($sql);
      $delete = dbquery($sql);

    }
  }
  if($_POST['newtopic'] != 0){
    $sql = 'insert into topic_assoc (topicid, resourceid) values ('.$_POST['newtopic'].', '.$resourceid.') ';
    $upd = dbquery($sql);
  }

  if($sqlerr=='') $sqlerr = datsav;
  $reloadparent=1;
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

<h2 style="text-align:center"><?=$stitle?></h2>
<div style="margin:0 auto;text-align:center">

<form name="frm" method="post" action="/topicassign.php">
<table class="gridtable">
  <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
        <tr><th style="text-align:left;">&nbsp;Topic</th><th>Del</th></tr>

<?
$sql = 'select t.topicid, t.topic, (select count(*) from topic_assoc ta2 where ta2.topicid = t.topicid) tcount
        from topic_assoc ta
        join topic t on t.topicid = ta.topicid
        where ta.resourceid = '.$resourceid.'
        order by 2 ';
$med = dbquery($sql);
$ni=0;
while($row = mysqli_fetch_array($med)){
  print('<tr><td style="text-align:left"><a href="/topic/'.$row['topicid'].'/'.str_replace(' ', '_', $row['topic']).'" target="_blank">'.$row['topic'].'</a> ('.$row['tcount'].')<input type="hidden" name="resource'.$ni.'" value="'.$row['topicid'].'" /></td>');
  print('<td><input type="checkbox" name="chkdel'.$ni.'" value="1" /></td></tr>');
  $ni++;
}
print('<tr><td colspan="2">New: ');
print('<select name="newtopic" id="newtopic">');
print('<option value="0">none</option>');
$sql = 'select topicid, topic, (select count(*) from topic_assoc ta where ta.topicid = topic.topicid) tcount
        from topic
        where topicid not in (select topicid from topic_assoc where resourceid = '.$resourceid.')
        order by 2 ';
$tps = dbquery($sql);
while($row = mysqli_fetch_array($tps)){
  print('<option value="'.$row[0].'">'.$row[1].' ('.$row[2].')</option>');
}
print('</select></td></tr>');
print('<tr><td colspan="2"><input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);"> ');
print('<input type="button" name="btnclose" value="Close" onclick="olClose(\''.$reloadparent.'\');"></td></tr>');

print('</table>');
?>
  <input type="hidden" name="mcnt" value="<?=$ni?>" />
  <input type="hidden" name="resourceid" value="<?=$resourceid?>" />
  <input type="hidden" name="oper" value="" />
</form>
<div style="width:320px;margin:10px auto;text-align:left;">
</div>
</div>
<script>

  function validate(f){
    var havedel = 0;
    for(var i=0;i<<?=$ni?>; i++){
      if(f['chkdel'+i].checked){
        havedel = 1;
        break;
      }
    }
    if(havedel==1){
      if(!confirm('Are you sure you want to unassociate the checked topic(s)?')){return false;}
    }
    f.oper.value = 'savtopics';
    return true;
  }

  function $(el) {return parent.document.getElementById(el);}

  function olClose(locn) {
    var ol = $("overlay");
    ol.style.display = 'none';
    //if(locn==1) parent.document.frm.submit();
    if(locn==1) parent.document.location.reload();
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }
  //document.frm.newresource.focus();
  </script>
</body>
</html>

