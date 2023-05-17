<?php
if(empty($userid) || $userid==0) die('unauthorized access');

$msg = '';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper == 'exclude' || $oper == 'delete'){
  $ip = $_POST['ip'];
  $cnt= $_POST['cnt'];
  $dsc= '!!'.$_POST['dsc'];
  //$delete = dbquery('delete from viewlogs where remoteip = \''.$ip.'\';');
  $delete = dbquery('delete from viewlogs where remoteip like \''.str_replace('*', '', $ip).'%\';');
  // save these, less hits to ipstack
  //$delete = dbquery('delete from ipcrossref where ipaddress = \''.$ip.'\';');
  if($oper == 'exclude'){
    $row = rs('select 1 from logexcludeips where ipaddress = \''.$ip.'\';');
    if(!$row) $insert = dbquery('insert into logexcludeips (ipaddress, lastview, hits, ipcomment) values (\''.$ip.'\', UTC_TIMESTAMP, '.$cnt.', \''.$dsc.'\');');
  }else{
    $update = dbquery('update logexcludeips set hits = hits+'.$cnt.', lastview = UTC_TIMESTAMP() where ipaddress=\'0.0.0.0\' ');
  }
}

$top30 = intval((isset($_POST['top30']))?$_POST['top30']:30);
$showips = intval((isset($_POST['showips']))?$_POST['showips']:0);
$ipgroup = intval((isset($_REQUEST['ipgroup']))?$_REQUEST['ipgroup']:2);
$showipgroup = intval((isset($_REQUEST['showipgroup']))?$_REQUEST['showipgroup']:0);

if($top30<1 || $top30>200) $top30=30;

$sqlipphrase = (($showips==1)?'vl.remoteip':'ifnull(cr.iplocation, vl.remoteip)');
$sqlipjoin   = (($showips==1)?'':'left join ipcrossref cr on (cr.ipaddress = vl.remoteip)');

$daysback = ((isset($_POST['daysback']))?$_POST['daysback']:1);
$sql = 'select count(*)
        from viewlogs
        where 1 = 1
        and viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        limit 1';

$row = rs($sql);
$tot = $row[0];
$sql = 'select count(distinct remoteip)
        from viewlogs
        where 1 = 1
        and viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        limit 1';

$row = rs($sql);
$uniq = $row[0];

$ipurl = 'http://whatismyipaddress.com/ip/';

$stitle = 'REV Website Statistics';
//$msg = 'TZ: '.$timezone;
?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
<small><span style="color:red"><b><?=$msg?></b></span></small>
<form name="frm" method="post" action="/">
<small>
Past <select name="daysback" onchange="document.frm.submit()">
  <option value="1"<?=fixsel(1, $daysback)?>>1 day</option>
  <option value="3"<?=fixsel(3, $daysback)?>>3 days</option>
  <option value="7"<?=fixsel(7, $daysback)?>>7 days</option>
  <option value="30"<?=fixsel(30, $daysback)?>>30 days</option>
</select>
Top <select name="top30" onchange="document.frm.submit()">
  <option value="30"<?=fixsel(30, $top30)?>>30</option>
  <option value="60"<?=fixsel(60, $top30)?>>60</option>
  <option value="90"<?=fixsel(90, $top30)?>>90</option>
  <option value="200"<?=fixsel(200, $top30)?>>200</option>
</select>
IPs <input type="checkbox" name="showips" value="1"<?=fixchk($showips)?> onclick="document.frm.submit()">
<?if($superman==1){?>
IPGrp (<span style="color:red;">slow!</span>) <input type="checkbox" name="showipgroup" value="1"<?=fixchk($showipgroup)?> onclick="document.frm.submit()">
<?}?>
Page views: <?=$tot?> &nbsp;Unique visitors: <?=$uniq?>
</small>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="ip" value="" />
  <input type="hidden" name="ipgroup" value="<?=$ipgroup?>" />
  <input type="hidden" name="cnt" value="" />
  <input type="hidden" name="dsc" value="" />
</form><br />

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Last <?=$top30?> Views (desc)</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>When</td><td>What</td><td>Who</td><td>On</td></tr>
<?
$sql = 'select vl.remoteip, '.$sqlipphrase.' ip, b.title, b.tagline, b.abbr, vl.page, vl.testament, vl.book, vl.chapter, vl.verse, vl.viewtime, vl.mobile, vl.misc, vl.userid
        from viewlogs vl
        left join book b on (b.testament = vl.testament and b.book = vl.book)
        '.$sqlipjoin.'
        where vl.page < 500 '.(($editorcomments==0)?' and vl.page not in (307, 308, 309) ':'').(($revblog==0)?' and vl.page not in (25,26,27) ':'').'
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        order by vl.viewtime desc
        limit '.$top30;
//print($sql);

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr>');
  print('<td>'.rtrim(date('g:i:sa', strtotime(converttouserdate2($row['viewtime'], $timezone))), 'm').'</td>');
  print('<td>'.fixwhat($row).'</td>');
  print('<td><a href="'.$ipurl.''.$row['remoteip'].'" target="_blank" title="(opens new window)">'.$row['ip'].'</a></td>');
  print('<td>'.(($row['mobile']==0)?'pc':'mo').'</td>');
  print('</tr>'.crlf);
  $tot++;
}
if($tot==0){print('<tr><td colspan="4">none, nobody at all</td></tr>'.crlf);}

function fixwhat($r){
  global $lexicon;
  $ret = '';
  $pg = $r['page'];
  //$pg=133; // test
  switch($pg){
  case 0: // bible
    $link = $r['abbr'].' '.(($r['chapter']>0)?$r['chapter']:'(all)');
    $href = '/'.str_replace(' ','',$r['abbr']).'/'.(($r['chapter']>0)?$r['chapter'].'/0':'all/0').'/1';
    $ret = 'Bible: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 1: // edit vers/comm
    $link = $r['abbr'].' '.$r['chapter'].':'.$r['verse'];
    $href = '/'.str_replace(' ','',$r['abbr']).'/'.$r['chapter'].'/'.$r['verse'].'/1';
    $ret = '<span style="color:red">EDIT</span>: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 3: $ret = 'Srch-'.(($r['testament']==1)?'B':'C').': "'.$r['misc'].'"';break;
  case 4: // commentary
    $link = $r['abbr'].' '.$r['chapter'];
    $href = '/comm/'.str_replace(' ','',$r['abbr']).'/'.$r['chapter'];
    $ret = 'Comtry: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 5: // verse commentary
    $link = $r['abbr'].' '.$r['chapter'].':'.$r['verse'];
    $href = '/'.str_replace(' ','',$r['abbr']).'/'.$r['chapter'].'/'.$r['verse'].'/1';
    $ret = 'Verse: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 6: // edit book commentary
    $link = $r['abbr'];
    $href = '/book/'.str_replace(' ', '_', $r['title']).'/1';
    $ret = '<span style="color:red">EDIT</span> BkCm: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 8: // edit appx/intro/WS
    if($r['testament']==2 || $r['testament']==3){
      $link = $r['title'];
      $href = (($r['testament']==2)?'/info/':'/appx/').$r['book'].'/1';
      $ret = '<span style="color:red">EDIT</span>: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    }
    if($r['testament']==4){
      $link = $r['title'];
      $href = '/word/'.str_replace('/', '_', str_replace(' ', '_', $r['title'])).'/1';
      $ret = '<span style="color:red">EDIT</span> WS: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    }
    break;
  case 9: // prefs
    $ret = 'Prefs';
    break;
  case 10: // book commentary
    $link = $r['abbr'];
    $href = '/book/'.str_replace(' ','',$r['abbr']).'/1';
    $ret = 'Intro: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 24: // book outline
    $link = $r['abbr'];
    $href = '/outline/'.str_replace(' ','',$r['abbr']);
    $ret = 'Outline: <a href="'.$href.'" target="_blank">'.$link.'</a>';
    break;
  case 14:
    if($r['testament']==2 || $r['testament']==3){
      $link = $r['title'];
      $href = (($r['testament']==2)?'/info/':'/appx/').$r['book'].'/1';
      $ret = '<a href="'.$href.'" target="_blank">'.$link.'</a>';
    }
    if($r['testament']==4){
      $link = 'WS: '.$r['title'];
      $href = '/word/'.str_replace('/','_',str_replace(' ', '_', $r['title'])).'/1';
      $ret = '<a href="'.$href.'" target="_blank">'.$link.'</a>';
    }
    break;
  // should make all these links..
  case 20:$ret = 'What\'s New';break;
  case 25:$ret = 'REV Blog List';break;
  case 26:$ret = 'REV Blog';break;
  case 27:$ret = '<span style="color:red">EDIT</span> REV Blog';break;
  case 29:$ret = 'Donate';break;
  case 30:$ret = 'About';break;
  case 32:$ret = '<span style="color:red;">!!REV backup!!</span>';break;
  case 33:
    if($r['chapter']==0) $ret = 'Topic List';
    else{
      $rrow = rs('select topic from topic where topicid = '.$r['chapter'].' ');
      if($rrow)
        $ret = 'Topic: '.$rrow[0];
      else
        $ret = 'Topic: unknown';
    }
    break;
  case 34:$ret = 'Chrn: '.$r['misc'];break;
  case 36:$ret = 'Resources';break;
  case 39:$ret = 'XMLExport';break;
  case 41:$ret = 'eSword Export Looker';break;
  case 42:$ret = 'MySword Export Looker';break;
  case 43:$ret = 'BWorks Export Looker';break;
  case 44:$ret = 'theWord Export Looker';break;
  case 45:$ret = 'Export Looker';break;
  case 46:$ret = 'DL ES Bible';break;
  case 47:$ret = 'DL ES Comm';break;
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
  case 68:$ret = 'Logos Expt Lookr';break;
  case 79:$ret = 'MSW Export Looker';break;
  case 80:$ret = 'DL MSW REV_Bible';break;
  case 81:$ret = 'DL MSW REV_Commentary';break;
  case 82:$ret = 'DL MSW REV_Appxs';break;
  case 83:$ret = 'DL MSW REV_Wordstudies';break;
  case 84:$ret = 'DL MSW REV_Information';break;
  case 89:$ret = 'DL MSW Full Zip';break;
  case 85:$ret = 'DL ES WordStudy';break;
  case 86:$ret = 'DL MS WordStudy';break;
  case 87:$ret = 'DL TW WordStudy';break;
  case 88:$ret = 'DL iBS WordStudy';break;
  case 90:$ret = 'MSW BookComm: '.$r['abbr'];break;
  case 91:$ret = 'MSW: '.$r['title'];break;
  case 92:$ret = 'MSW bible: '.$r['abbr'].' '.(($r['chapter']>0)?$r['chapter']:'(all)');break;
  case 93:$ret = 'MSW Comtry: '.$r['abbr'].' '.(($r['chapter']>0)?$r['chapter']:'(all)');break;
  case 94:$ret = 'MSW verse: '.$r['abbr'].' '.$r['chapter'].':'.$r['verse'];break;
  case 95:$ret = 'PDF BookComm: '.$r['abbr'];break;
  case 96:$ret = 'PDF: '.$r['title'];break;
  case 97:$ret = 'PDF bible: '.$r['abbr'].' '.(($r['chapter']>0)?$r['chapter']:'(all)');break;
  case 98:$ret = 'PDF Comtry: '.$r['abbr'].' '.(($r['chapter']>0)?$r['chapter']:'(all)');break;
  case 99:$ret = 'PDF verse: '.$r['abbr'].' '.$r['chapter'].':'.$r['verse'];break;
  case 200:$ret = 'JSON_timestamp';break;
  case 201:$ret = 'JSON_bible';break;
  case 202:$ret = 'JSON_commentary';break;
  case 203:$ret = 'JSON_appxs';break;
  case 210: // bib/abbrev
    $xxx = (($r['chapter']==0)?'Abbreviations':'Bibliography');
    $ret = '<a href="/'.strtolower($xxx).'" target="_blank">'.$xxx.'</a>';
    break;
  case 211: // bib/abbrev edit
    $xxx = (($r['chapter']==0)?'Abbrev':'Bibliog');
    $ret = '<a href="/'.strtolower($xxx).'" target="_blank">'.$xxx.'</a> <span style="color:red;">edit!</span>';
    break;
  case 300:$ret = 'MyREV';break;
  case 301:
    $ret = '<span style="color:red">MRE</span>: ';
    switch($r['testament']){
      case 0;
      case 1: // bible
        $ret.= $r['abbr'].' '.(($r['verse']>0)?$r['chapter'].':'.$r['verse']:'WkSpc');
        break;
      case 2: // info
        $link = $r['title'];
        $href = '/info/'.$r['book'].'/1';
        $ret.= '<a href="'.$href.'" target="_blank">'.$link.'</a>';
        break;
      case 3: // appx
        $link = $r['title'];
        $href = '/appx/'.$r['book'].'/1';
        $ret.= '<a href="'.$href.'" target="_blank">'.$link.'</a>';
        break;
      case 4: // wordstudy
        $link = 'WS: '.$r['title'];
        $href = '/word/'.str_replace('/','_',str_replace(' ', '_', $r['title'])).'/1';
        $ret.= '<a href="'.$href.'" target="_blank">'.$link.'</a>';
        break;
      default:
        $ret.= ' I\'m lost';
      }
    break;
  case 303:
    $ret = 'MRV: '.$r['abbr'].' '.(($r['verse']>0)?$r['chapter'].':'.$r['verse']:'WkSpc');
    break;
  case 302:$ret = 'MRv captions';break;

  // this needs reorganized
  case 307:$ret = 'Editor Notes';break;
  case 308: // editor note
  case 310: // reviewer note
    $prefix = (($r['page']==308)?'Ed':'Rv');
    $str=' target="_blank" title="Click to view.  Opens new window/tab"';
    switch($r['testament']){
    case 0:
    case 1:
      $ret = $prefix.'-note: <a href="/'.str_replace(' ', '', $r['abbr']).'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['abbr'].' '.$r['chapter'].':'.$r['verse'].'</a>'; break;
    case 3:
      $ret = $prefix.'-note: <a href="/appx/'.$r['book'].'/1"'.$str.'>'.$r['title'].'</a>'; break;
    case 4:
      $ret = $prefix.'-note: <a href="/word/'.$r['title'].'/1"'.$str.'>'.$r['title'].'</a>'; break;
    }
    break;
  case 309:$ret = 'Editor Workspc';break;
  case 311:$ret = 'Reviewer Workspc';break;
  case 312:$ret = 'Reviewer Notes';break;

  case 400:$ret = 'MRv JSON hit';break;
  case 320:$ret = 'JSON REV export';break;
  case 450:$ret = '<span style="color:red">PDF injection attempt</span>';break;
  case 451:$ret = '<span style="color:red">MSW injection attempt</span>';break;
  case 452:$ret = $r['misc'];break;
  default:$ret = 'unknown ('.$pg.')';break;
  }
  if($r['userid']=='-7') $ret='(<span style="color:red;">a</span>)'.$ret;
  return $ret;
}
?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Top <?=$top30?> Visitors</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>IP Address/Loc</td><td>Views</td><?if($superman) print('<td>Exc</td><td>del</td>');?></tr>
<?
$sql = 'select vl.remoteip, '.$sqlipphrase.' ip, count(vl.remoteip)
        from viewlogs vl
        '.$sqlipjoin.'
        where 1 = 1
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by vl.remoteip, '.$sqlipphrase.'
        order by 3 desc, 2
        limit '.$top30;

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td><a href="'.$ipurl.''.$row['remoteip'].'" target="_blank" title="(opens new window)">'.$row['ip'].'</a></td>');
  print('<td align="right">');
  if($superman)
    print('<a onclick="olOpen(\'/logviews_ip.php?ip='.$row[0].'&amp;alias='.str_replace('\'', '\\\'', $row['ip']).'&amp;daysback='.$daysback.'\', '.getdivdimensions().');return false;">'.$row[2].'</a>');
  else
    print($row[2]);
  print('</td>');
  if($superman){
    print('<td align="right"><a onclick="excludeIP(\''.$row[0].'\', '.$row[2].', \''.$row[1].'\');return false;" title="click to exclude (stop logging) this IP address">exc</a></td>');
    print('<td align="right"><a onclick="deleteIP(\''.$row[0].'\', '.$row[2].', \''.$row[1].'\');return false;" title="click to delete this IP\'s logs">del</a></td>');
  }
  print('</tr>'.crlf);
  $tot+=$row[2];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td><td colspan="3"></td></tr>'.crlf);

?>
</table>
<script>
  function excludeIP(ip, cnt, dsc){
    var f = document.frm;
    if(confirm('This will delete ALL logs for \''+ip+'\'\nand prevent future logging.\n\nAre you sure?')){
      f.oper.value = 'exclude';
      f.ip.value = ip;
      f.cnt.value= cnt;
      f.dsc.value= dsc;
      f.submit();
    }
  }
  function deleteIP(ip, cnt, dsc){
    var f = document.frm;
    if(confirm('This will delete ALL logs for \''+ip+'\'\n\nAre you sure?')){
      f.oper.value = 'delete';
      f.ip.value = ip;
      f.cnt.value= cnt;
      f.dsc.value= dsc;
      f.submit();
    }
  }
</script>
</div>

<?if($showipgroup==1 && $superman==1){
  // Too slow on the live server
?>
<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Top <?=$top30?> IP Groups</b>
<select onchange="document.frm.ipgroup.value=this.selectedIndex+1;document.frm.submit();">
  <option value="1"<?=fixsel(1, $ipgroup)?>>1</option>
  <option value="2"<?=fixsel(2, $ipgroup)?>>2</option>
  <option value="3"<?=fixsel(3, $ipgroup)?>>3</option>
</select><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>IP Address</td><td>Uniq</td><td>Views</td><td>Exc</td><td>del</td></tr>
<?
$sql = 'SELECT count(vl1.remoteip) tot,
        SUBSTRING_INDEX(vl1.remoteip, \'.\', '.$ipgroup.') ip,
        (select count(distinct vl2.remoteip)
         from viewlogs vl2
         where vl2.remoteip like concat(substring_index(vl1.remoteip, \'.\', '.$ipgroup.'),\'%\')
         and vl2.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        ) uniq
        FROM viewlogs vl1
        where vl1.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by 2, 3
        ORDER BY 1 desc
        limit '.$top30;

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td>'.$row['ip'].'.*</td>');
  print('<td align="right">'.$row['uniq'].'</td>');
  print('<td align="right">'.$row['tot'].'</td>');
  print('<td align="right"><a onclick="excludeIP(\''.$row['ip'].'.*\', '.$row['tot'].', \''.$row['ip'].'.*\');return false;" title="click to exclude (stop logging) this IP address">exc</a></td>');
  print('<td align="right"><a onclick="deleteIP(\''.$row['ip'].'.*\', '.$row['tot'].', \''.$row['ip'].'.*\');return false;" title="click to delete this IP\'s logs">del</a></td>');
  print('</tr>'.crlf);
  $tot+=$row['tot'];
}
print('<tr><td colspan="2">Total</td><td align="right">'.$tot.'</td><td colspan="2"></td></tr>'.crlf);

?>
</table>
</div>
<?}?>


<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Top <?=$top30?> Countries</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Country</td><td>Views</td></tr>
<?
$sql = 'select right(ip.iplocation, 2) ccod, count(vl.remoteip) cnum
        from ipcrossref ip
        inner join viewlogs vl on vl.remoteip = ip.ipaddress
        where instr(reverse(iplocation), \' \') = 3
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by right(ip.iplocation, 2)
        order by 2 DESC
        limit '.$top30;

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  //$link = $row['title'].' '.$row['chapter'].':'.$row['verse'];
  //$href = '/'.str_replace(' ','',$row['title']).'/'.$row['chapter'].'/'.$row['verse'].'/1';
  print('<tr><td>'.$row['ccod'].'</td><td align="right">');
  if($superman==1 && 1==2)
    print(printolopen(5,$row['testament'],$row['book'],$row['chapter'],$row['verse'],$daysback,$row['cnt']));
  else
    print($row['cnum']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnum'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Top 30 Chapters</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Book</td><td>Chap</td><td>Views</td></tr>
<?
$sql = 'select vl.testament, vl.book, b.bwabbr, vl.chapter, count(*) cnt
        from viewlogs vl
        left join book b on (b.testament = vl.testament and b.book = vl.book)
        where 1 = 1
        and vl.page = 0
        and vl.testament in (0,1)
        and vl.book > 0
        and vl.chapter > 0
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by 1,2,3,4
        order by 5 desc, 1, 2, 4
        limit 30';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td>'.$row['bwabbr'].'</td><td align="right">'.$row['chapter'].'</td><td align="right">');
  if($superman==1)
    print(printolopen(0,$row['testament'],$row['book'],$row['chapter'],-1,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td colspan="2">Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>NT Views</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Book</td><td>Views</td></tr>
<?
$sql = 'select vl.testament, vl.book, b.bwabbr, count(vl.viewtime) cnt
        from book b left join viewlogs vl
        on (b.testament = vl.testament and b.book = vl.book
        and vl.page = 0
        and vl.testament = 1
        and vl.book > 0
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY))
        where b.testament = 1
        group by 1,2,3
        order by b.book';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td>'.$row['bwabbr'].'</td><td align="right">');
  if($superman==1 && $row['cnt'] > 0)
    print(printolopen(0,$row['testament'],$row['book'],-1,-1,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>OT Views</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Book</td><td>Views</td></tr>
<?
$sql = 'select vl.testament, vl.book, b.bwabbr, count(vl.viewtime) cnt
        from book b left join viewlogs vl
        on (b.testament = vl.testament and b.book = vl.book
        and vl.page = 0
        and vl.testament = 0
        and vl.book > 0
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY))
        where b.testament = 0
        group by 1,2,3
        order by b.book';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td>'.$row['bwabbr'].'</td><td align="right">');
  if($superman==1 && $row['cnt'] > 0)
    print(printolopen(0,$row['testament'],$row['book'],-1,-1,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Top <?=$top30?> Commtry Views</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Reference</td><td>Views</td></tr>
<?
$sql = 'select vl.testament, vl.book, vl.chapter, vl.verse, b.title, count(*) cnt
        from viewlogs vl left join book b
        on (b.testament = vl.testament and b.book = vl.book)
        where 1 = 1
        and vl.page = 5
        and vl.testament in (0,1)
        and vl.book > 0
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by 1,2,3,4,5
        order by 6 desc, vl.book, vl.chapter, vl.verse
        limit '.$top30;

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  $link = $row['title'].' '.$row['chapter'].':'.$row['verse'];
  $href = '/'.str_replace(' ','',$row['title']).'/'.$row['chapter'].'/'.$row['verse'].'/1';
  print('<tr><td><a href="'.$href.'" target="_blank">'.$link.'</a></td><td align="right">');
  if($superman==1)
    print(printolopen(5,$row['testament'],$row['book'],$row['chapter'],$row['verse'],$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Nav Menu Usage</b><br /><small> (sans Bible nav..)</small><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Item</td><td>Views</td></tr>
<?
$sql = 'select testament, count(*) cnt
        from viewlogs vl
        where vl.page = 500
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by testament
        order by 2 desc ';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td>'.getfeature($row['testament']).'</td><td align="right">');
  if($superman==1 && $row['cnt'] > 0)
    print(printolopen(500,$row['testament'],0,0,0,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

function getfeature($itm){
  $ret='unknown';
  switch($itm){
  case -1: $ret = 'Initial';break;
  case  1: $ret = 'Bible Nav';break;
  case  2: $ret = 'Comm Nav';break;
  case  3: $ret = 'Appendix';break;
  case  4: $ret = 'Wordstudy';break;
  case  5: $ret = 'History';break;
  case  6: $ret = 'Information';break;
  case  8: $ret = 'QuickPrefs';break;
  case  9: $ret = 'Bookmarks';break;
  case 10: $ret = 'Bmk Notify';break;
  }
  return $ret;
}

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Information Views</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>What</td><td>Views</td></tr>
<?
$sql = 'select vl.book, ifnull(b.tagline, b.title) title, count(vl.viewtime) cnt
        from book b left join viewlogs vl
        on (b.testament = vl.testament
            and b.book = vl.book
            and vl.page = 14
            and vl.testament = 2
            and vl.book > 0
            and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
            )
        where b.testament = 2 and b.active = 1
        group by 1,2
        order by b.book';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
    print('<tr><td>'.$row['title'].'</td><td align="right">');
    if($superman==1 && $row['cnt'] > 0)
      print(printolopen(14,2,$row['book'],1,1,$daysback,$row['cnt']));
    else
      print($row['cnt']);
    print('</td></tr>'.crlf);
    $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Library Downloads</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Library file</td><td>dloads</td></tr>
<?
$sql = 'select vl.page libid, ifnull(r.externalurl, \'/missing!\') title, count(vl.viewtime) cnt
        from viewlogs vl left join resource r
        on (r.resourceid = vl.page
            and r.resourcetype = 7
            )
        where vl.testament = 0
        and vl.book = 0
        and vl.chapter = -1
        and vl.verse = -1
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by 1, 2
        order by 2 ';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  $tit = substr($row[1], strrpos($row[1], '/')+1);
  if(strlen($tit) > 27)
    $tit = substr($tit, 0, 25).'...';
  print('<tr><td>'.$tit.'</td><td align="right">');
  if($superman==1 && $row['cnt'] > 0)
    print(printolopen(300,$row['libid'],0,-1,-1,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Appendix Views</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Appendix</td><td>Views</td></tr>
<?
$sql = 'select vl.book, ifnull(b.tagline, b.title) title, count(vl.viewtime) cnt
        from book b left join viewlogs vl
        on (b.testament = vl.testament
            and b.book = vl.book
            and vl.page = 14
            and vl.testament = 3
            and vl.book > 0
            and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
            )
        where b.testament = 3
        group by 1,2
        order by b.book';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  $tit = cleanquotes(str_replace('Appendix ', 'Appx ', $row['title']));
  if(strlen($tit) > 27)
    $tit = substr($tit, 0, 25).'...';
  print('<tr><td>'.$tit.'</td><td align="right">');
  if($superman==1 && $row['cnt'] > 0)
    print(printolopen(14,3,$row['book'],1,1,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>

<?if($revws==1){?>
<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Wordstudy Views</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Wordstudy</td><td>Views</td></tr>
<?
$sql = 'select vl.book, ifnull(b.tagline, b.title) title, count(vl.viewtime) cnt
        from book b left join viewlogs vl
        on (b.testament = vl.testament
            and b.book = vl.book
            and vl.page = 14
            and vl.testament = 4
            and vl.book > 0
            and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
            )
        where b.testament = 4
        and b.active = 1
        group by 1,2
        order by b.sqn';

$qry = dbquery($sql);
$tot = 0;
while ($row = mysqli_fetch_array($qry)) {
  print('<tr><td>'.$row['title'].'</td><td align="right">');
  if($superman==1 && $row['cnt'] > 0)
    print(printolopen(14,4,$row['book'],1,1,$daysback,$row['cnt']));
  else
    print($row['cnt']);
  print('</td></tr>'.crlf);
  $tot+=$row['cnt'];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>
<?}?>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>What's New</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page = 20
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  print(printolopen(20,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Chronology</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page = 34
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  print(printolopen(34,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>

<?if($revblog==1){?>
<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Rev Blog</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page = 25
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  print(printolopen(25,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>
<?}?>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>XML Exports</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page = 39
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  print(printolopen(39,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>REV Backups</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page = 32
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  print(printolopen(32,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Platform</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Platform</td><td>Views</td><td align="center">Pct</td></tr>
<?
$sql = 'select count(*)
        from viewlogs
        where viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = rs($sql);
$tot = (($row[0]==0)?1:$row[0]);

$sql = 'select count(*)
        from viewlogs
        where userid >= 0
        and mobile = 0
        and viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = rs($sql);
$url = (($superman==1)?printolopen(101,-2,0,0,0,$daysback,$row[0]):$row[0]);
print('<tr><td>computer</td><td align="right">'.$url.'</td><td align="right">'.round(($row[0]*100)/$tot, 2).'%</td></tr>'.crlf);

$sql = 'select count(*)
        from viewlogs
        where userid >= 0
        and mobile = 1
        and viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = rs($sql);
$url = (($superman==1)?printolopen(101,-1,0,0,0,$daysback,$row[0]):$row[0]);
print('<tr><td>mobile</td><td align="right">'.$url.'</td><td align="right">'.round(($row[0]*100)/$tot, 2).'%</td></tr>'.crlf);

$sql = 'select count(*)
        from viewlogs
        where userid = -7
        and viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = rs($sql);
$url = (($superman==1)?printolopen(101,-3,0,0,0,$daysback,$row[0]):$row[0]);
print('<tr><td>from App</td><td align="right">'.$url.'</td><td align="right">'.round(($row[0]*100)/$tot, 2).'%</td></tr>'.crlf);

print('<tr><td>Total</td><td align="right">'.$tot.'</td><td>100.00%</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>PDF Exports</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page in (88,95,96,97,98,99)
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  // the first param is special: -2 = PDFs, page 95-99
  print(printolopen(-2,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>MSW Exports</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page in (89,90,91,92,93,94)
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
if($superman==1 && $row['cnt'] > 0)
  // the first param is special: -3 = MSWs, page 90-94
  print(printolopen(-3,-1,-1,-1,-1,$daysback,$row['cnt']));
else
  print($row['cnt']);
print('</td></tr>'.crlf);

?>
</table>
</div>

<?if($superman==1){?>
<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b><span style="color:red">Hack Atmpts</span></b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Num</td><td>Views</td></tr>
<?
$sql = 'select count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page in (452)
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)';

$row = mysqli_fetch_array(dbquery($sql));
$tot=$row[0];
print('<tr><td>Total</td><td align="right">');
print(printolopen(452,-1,-1,-1,-1,$daysback,$row['cnt']));
print('</td></tr>'.crlf);

?>
</table>
</div>
<?}?>

<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>REV Exports</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>Program</td><td>dloads</td></tr>
<?
$sql = 'select vl.page, count(vl.viewtime) cnt
        from viewlogs vl
        where vl.page in (41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,79,80,81,82,83,84,85,86,87,88,89,200,201,202,203)
        and vl.viewtime > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
        group by vl.page
        order by vl.page';

$qry = dbquery($sql);
$tot = 0;
$expstuff = Array();
while ($row = mysqli_fetch_array($qry)) {
  $whch = $row[0];
  switch($whch){

  case 45: $expstuff[] = array(1,  45, '<b>Export Looker</b>', $row['cnt']);break;
  case 41: $expstuff[] = array(2,  41, '<b>e-Sword looker</b>',$row['cnt']);break;
  case 46: $expstuff[] = array(3,  46, 'DL ES Bible',          $row['cnt']);break;
  case 47: $expstuff[] = array(4,  47, 'DL ES Comm',           $row['cnt']);break;
  case 48: $expstuff[] = array(5,  48, 'DL ES CommV',          $row['cnt']);break;
  case 49: $expstuff[] = array(6,  49, 'DL ES Appx',           $row['cnt']);break;
  case 42: $expstuff[] = array(7,  42, '<b>MySword looker</b>',$row['cnt']);break;
  case 50: $expstuff[] = array(8,  50, 'DL MS Bible',          $row['cnt']);break;
  case 51: $expstuff[] = array(9,  51, 'DL MS Comm',           $row['cnt']);break;
  case 52: $expstuff[] = array(10, 52, 'DL MS CommV',          $row['cnt']);break;
  case 53: $expstuff[] = array(11, 53, 'DL MS Appx',           $row['cnt']);break;
  case 43: $expstuff[] = array(12, 43, '<b>BiblWrks lookr</b>',$row['cnt']);break;
  case 54: $expstuff[] = array(13, 54, 'DL BibleWks',          $row['cnt']);break;
  case 44: $expstuff[] = array(14, 44, '<b>theWord looker</b>',$row['cnt']);break;
  case 55: $expstuff[] = array(15, 55, 'DL TW Bible',          $row['cnt']);break;
  case 56: $expstuff[] = array(16, 56, 'DL TW Comm',           $row['cnt']);break;
  case 57: $expstuff[] = array(17, 57, 'DL TW CommV',          $row['cnt']);break;
  case 58: $expstuff[] = array(18, 58, 'DL TW Appx',           $row['cnt']);break;
  case 59: $expstuff[] = array(19, 59, '<b>iBS Looker</b>',    $row['cnt']);break;
  case 60: $expstuff[] = array(20, 60, 'DL iBS Bible',         $row['cnt']);break;
  case 61: $expstuff[] = array(21, 61, 'DL iBS Comm',          $row['cnt']);break;
  case 62: $expstuff[] = array(22, 62, 'DL iBS CommV',         $row['cnt']);break;
  case 63: $expstuff[] = array(23, 63, 'DL iBS Appx',          $row['cnt']);break;
  case 65: $expstuff[] = array(24, 65, '<b>SSrchr looker</b>', $row['cnt']);break;
  case 64: $expstuff[] = array(25, 64, 'DL SSrchr.zip',        $row['cnt']);break;
  case 67: $expstuff[] = array(26, 67, '<b>ACC Looker</b>',    $row['cnt']);break;
  case 66: $expstuff[] = array(27, 66, 'DL Accordnce',         $row['cnt']);break;
  case 68: $expstuff[] = array(33, 68, '<b>Logos looker</b>',         $row['cnt']);break;
  case 69: $expstuff[] = array(33, 69, 'DL Logos',             $row['cnt']);break;
  case 79: $expstuff[] = array(28, 79, '<b>MSW looker</b>',    $row['cnt']);break;
  case 80: $expstuff[] = array(29, 80, 'DL MSW REV_Bible',     $row['cnt']);break;
  case 81: $expstuff[] = array(30, 81, 'DL MSW REV_Commentary',$row['cnt']);break;
  case 82: $expstuff[] = array(31, 82, 'DL MSW REV_Appxs',     $row['cnt']);break;
  case 83: $expstuff[] = array(31, 83, 'DL MSW REV_WS',        $row['cnt']);break;
  case 84: $expstuff[] = array(31, 84, 'DL MSW REV_Info',      $row['cnt']);break;
  case 89: $expstuff[] = array(31, 89, 'DL MSW All Zip',       $row['cnt']);break;

  case 85: $expstuff[] = array(6,  85, 'DL ES WordStudy',      $row['cnt']);break;
  case 86: $expstuff[] = array(11, 86, 'DL MS WordStudy',      $row['cnt']);break;
  case 87: $expstuff[] = array(18, 87, 'DL TW WordStudy',      $row['cnt']);break;
  case 88: $expstuff[] = array(23, 88, 'DL iBS WordStudy',     $row['cnt']);break;

  case 200: $expstuff[] = array(32, 200, 'JSON_Timestamp',     $row['cnt']);break;
  case 201: $expstuff[] = array(32, 201, 'JSON_Bible',         $row['cnt']);break;
  case 202: $expstuff[] = array(32, 202, 'JSON_Commentary',    $row['cnt']);break;
  case 203: $expstuff[] = array(32, 203, 'JSON_Appendices',    $row['cnt']);break;
  }
}

// sort the array
array_multisort($expstuff);

for($ni=0;$ni<sizeof($expstuff);$ni++){
  print('<tr><td>'.$expstuff[$ni][2].'</td><td align="right">');
  if($superman==1 && $expstuff[$ni][3] > 0)
    print(printolopen($expstuff[$ni][1],-1,-1,-1,-1,$daysback,$expstuff[$ni][3]));
  else
    print($expstuff[$ni][3]);
  print('</td></tr>'.crlf);
  $tot+=$expstuff[$ni][3];
}
print('<tr><td>Total</td><td align="right">'.$tot.'</td></tr>'.crlf);

?>
</table>
</div>


<div style="float:left;margin:5px;font-size:80%;border:1px solid <?=$colors[1]?>">
<b>Last <?=$top30?> Searches</b><br>
<table border="0" cellpadding="2" cellspacing="0">
  <tr style="background-color:<?=$colors[6]?>;font-weight:bold;"><td>When</td><td>What</td><td>How</td><td>Text</td><td>Who</td><td>On</td></tr>
<?
$sql = 'select vl.remoteip, '.$sqlipphrase.' ip, vl.testament, vl.book, vl.chapter, vl.verse, vl.viewtime, vl.mobile, vl.misc, vl.userid
        from viewlogs vl
        '.$sqlipjoin.'
        where vl.page = 3
        order by vl.viewtime desc
        limit '.$top30;

$qry = dbquery($sql);
while ($row = mysqli_fetch_array($qry)) {
  print('<tr>');
  print('<td>'.rtrim(date('n/j g:ia', strtotime(converttouserdate2($row['viewtime'], $timezone))), 'm').'</td>');
  print('<td>'.(($row['testament']=='1')?'bibl':'com').(($row['userid']==-7)?'(<span style="color:red;">a</span>)':'').'</td>');
  print('<td>'.srchhow($row['book']).'</td>');
  print('<td>&quot;'.$row['misc'].'&quot;</td>');
  print('<td><a href="'.$ipurl.''.$row['remoteip'].'" target="_blank" title="(opens new window)">'.$row['ip'].'</a></td>');
  print('<td>'.(($row['mobile']=='1')?'mo':'pc').'</td>');
  print('</tr>'.crlf);
}

function srchhow($idx){
  switch($idx){
  case 1: return 'any'; break;
  case 2: return 'extw'; break;
  case 3: return 'extp'; break;
  case 4: return 'ref'; break;
  default: return 'unk';
  }
}

function getdivdimensions(){
  global $ismobile, $screenwidth;
  return (($ismobile==1)?$screenwidth+20:600).', 500';
}

function printolopen($p,$t,$b,$c,$v,$d,$n){
  return '<a onclick="olOpen(\'/logdetail.php?pag='.$p.'&amp;test='.$t.'&amp;book='.$b.'&amp;chap='.$c.'&amp;vers='.$v.'&amp;daysback='.$d.'\', '.getdivdimensions().');return false;">'.$n.'</a>';

}

?>
</table>
</div>


<div style="display:block;clear:both;left:0;right:0;margin:auto;white-space:nowrap;">
<span style="color:red">More to come...(maybe)</span>
</div>







