<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');
$stitle = 'Manage REV Users';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$myrvid = (isset($_POST['myrvid']))?$_POST['myrvid']:0;
$unreg = (isset($_POST['unreg']))?$_POST['unreg']:0;
$mnote = (isset($_POST['mnote']))?$_POST['mnote']:0;
$perms = (isset($_POST['perms']))?$_POST['perms']:0;
$srchtxt = (isset($_POST['srchtxt']))?$_POST['srchtxt']:'';
$msg = "";
$sqlerr='';
if($oper=='delmruser'){
  $sql = 'delete from myrevdata where myrevid = '.$myrvid.' ';
  $del = dbquery($sql);
  $sql = 'delete from myrevusers where myrevid = '.$myrvid.' ';
  $del = dbquery($sql);
  $myrvid = 0; // reset to show list
  $sqlerr = $msg;
  if($sqlerr=='') $sqlerr = 'MyREV user deleted';
  $sqlerr.= '<br />';
}
if($oper=='delmritems'){
  for($ni=0;$ni<$_REQUEST['numitms'];$ni++){
    if(isset($_REQUEST['delitm'.$ni]) && $_REQUEST['delitm'.$ni]==1){
      $aritm = explode('|', $_REQUEST['itm'.$ni]);
      $sql = 'delete from myrevdata
              where myrevid = '.$myrvid.'
              and testament = '.$aritm[0].'
              and book = '.$aritm[1].'
              and chapter = '.$aritm[2].'
              and verse = '.$aritm[3].' ';
      $del = dbquery($sql);
    }
  }
  $sqlerr = $msg;
  if($sqlerr=='') $sqlerr = datsav;
  $sqlerr.= '<br />';
}
$pagnum = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$sortdir= ((isset($_REQUEST['sortdir']))?$_REQUEST['sortdir']:1); // default to desc
$sortfld= ((isset($_REQUEST['sortfld']))?$_REQUEST['sortfld']:'user');

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center;margin-bottom:6px;"><small><?=adminmenu()?></small></div>
<?}?>

<form name="frm" method="post" action="/" style="margin-top:20px;">

<?
if($myrvid==0){
  $row=rs('select count(*) from myrevdata');
  print('<div style="margin:0 auto;text-align:center;font-size:80%;">MyREV data: '.$row[0].' records.</div>');

  $where = '';
  if($srchtxt!='')
    $where.= 'and mru.myrevname like \'%'.$srchtxt.'%\' or mru.myrevemail like \'%'.$srchtxt.'%\' ';
  if($unreg==1)
    $where.= 'and regcode is not null ';
  if($mnote==1)
    $where.= 'and mru.myrevid in (select myrevid from myrevdata where marginnote is not null) ';
  if($perms==1)
    $where.= 'and mru.userid > 0 ';

  $ascdesc = (($sortdir==1)?'asc ':'desc ');
  $orderby = 'order by ';
  switch($sortfld){
  case 'user':
    $orderby.= 'mru.myrevname '.$ascdesc; break;
  case 'email':
    $orderby.= 'mru.myrevemail '.$ascdesc; break;
  case 'items':
    $orderby.= 'items '.$ascdesc.', mru.myrevid '; break;
  case 'lastv':
    $orderby.= 'mru.lastaccessed '.$ascdesc; break;
  case 'reg':
    $orderby.= 'regcode '.$ascdesc.', mru.myrevid '; break;
  case 'regdt':
    $orderby.= 'mru.regdate '.$ascdesc.', mru.myrevid '; break;
  case 'ipadr':
    $orderby.= 'mru.ipaddress '.$ascdesc; break;
  case 'lastu':
    $orderby.= 'lupdt '.$ascdesc; break;
  default:
    $orderby.= 'mru.regdate '.$ascdesc.', mru.myrevid '; break;
  }

  $pagitmcnt = 30;
  $sql = 'select count(*) from myrevusers mru
          where myrevid > 0 '.$where.' ';
  $row = rs($sql);
  $rsss= $row[0];
  $pagtot = ceil($rsss/$pagitmcnt);
  $limit  = 'limit '.(($pagnum-1)*$pagitmcnt).', '.$pagitmcnt.' ';

  $pagination = pagination($pagnum, $pagitmcnt, $pagtot);

  ?>

  <table class="gridtable" style="font-size:80%;">
    <tr><td colspan="5" style="vertical-align:bottom;">
      <?=printsqlerr($sqlerr)?>
      Search <input type="text" name="srchtxt" size="12" value="<?=$srchtxt?>" onfocus="this.select();" autocomplete="off" />&nbsp;&nbsp;&nbsp;&nbsp;
      Incomp Reg
      <input type="checkbox" name="unreg" value="1"<?=fixchk($unreg)?> />&nbsp;&nbsp;&nbsp;&nbsp;
      MNotes
      <input type="checkbox" name="mnote" value="1"<?=fixchk($mnote)?> />&nbsp;&nbsp;&nbsp;&nbsp;
      Perms
      <input type="checkbox" name="perms" value="1"<?=fixchk($perms)?> />&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" value="Go" onclick="resetpage(document.frm, 0);"/>
      </td>
      <td colspan="4" style="text-align:center;vertical-align:bottom;">
      <?=$pagination?>
    </td></tr>
    <tr>
      <td style="font-weight:bold;<?=fixbg('user')?>"><a onclick="dosort('user',1 );" class="comlink0">Username</a><?=fixsort('user')?></td>
      <td style="font-weight:bold;">Perms</td>
      <td style="font-weight:bold;<?=fixbg('email')?>"><a onclick="dosort('email',1);" class="comlink0">Email</a><?=fixsort('email')?></td>
      <td style="font-weight:bold;<?=fixbg('items')?>"><a onclick="dosort('items',0);" class="comlink0">Items</a><?=fixsort('items')?></td>
      <td style="font-weight:bold;<?=fixbg('lastv')?>"><a onclick="dosort('lastv',0);" class="comlink0">Last Visit</a><?=fixsort('lastv')?></td>
      <td style="font-weight:bold;<?=fixbg('lastu')?>"><a onclick="dosort('lastu',0);" class="comlink0">Last Updt</a><?=fixsort('lastu')?></td>
      <td style="font-weight:bold;<?=fixbg('reg')?>"><a onclick="dosort('reg',1  );" class="comlink0">Reg?</a><?=fixsort('reg')?></td>
      <td style="font-weight:bold;<?=fixbg('regdt')?>"><a onclick="dosort('regdt',0);" class="comlink0">Reg Date</a><?=fixsort('regdt')?></td>
      <td style="font-weight:bold;<?=fixbg('ipadr')?>"><a onclick="dosort('ipadr',1);" class="comlink0">Last IP</a><?=fixsort('ipadr')?></td>
    </tr>
<?
  $ni = 0;
  $nj=0;
  $numitms = 0;
  $sql = 'select mru.myrevid, mru.myrevname, mru.myrevemail, if(mru.regcode is null, 1, 0) regcode, mru.lastaccessed,
          ifnull(mru.regdate, \'2021-07-15 04:00\') regdate, userid, length(ifnull(mru.notes, \'\')) wplen, ifnull(mru.ipaddress, \'unknown\') ipaddress,
          (select count(*) from myrevdata mrd where mrd.myrevid = mru.myrevid) items,
          (select ifnull(max(ifnull(lastupdate,\'2021-07-15 04:00\')), 0) from myrevdata mrd2 where mrd2.myrevid = mru.myrevid) lupdt
          from myrevusers mru
          where mru.myrevid > 0 ';
  $sql.= $where;
  $sql.= $orderby;
  $sql.= $limit;
  //print($sql);
  $usrs = dbquery($sql);
  while($row = mysqli_fetch_array($usrs)){
    print('<tr>');
    print('<td><input type="hidden" name="myrvid'.$ni.'" value="'.$row['myrevid'].'" />'.$row['myrevname'].' <small>['.$row['myrevid'].']</small></td>');
    //print('<td style="text-align:center;background-color:'.(($row['userid']>0)?'#fdd':'transparent').';"><a onclick="olOpen(\'/permissions.php?localmyrevid='.$row['myrevid'].'\',600,500,1);" title="permissions"><img src="/i/edit.gif" style="margin:0;padding:0;width:1.1em;" alt="" />'.(($row['userid']>0)?$row['userid']:'').'</a></td>');
    print('<td style="text-align:center;background-color:'.(($row['userid']>0)?'#fdd':'transparent').';"><a onclick="olOpen(\'/permissions.php?localmyrevid='.$row['myrevid'].'\',600,500,1);" title="permissions"><img src="/i/edit.gif" style="margin:0;padding:0;width:1.1em;" alt="" /></a></td>');
    print('<td><a onclick="olOpen(\'/myrevitemdetail.php?myrvid='.$row['myrevid'].'&amp;test=0&amp;book=0&amp;chap=0&amp;vers=0\',600,500,1);" title="workspace"><img src="/i/myrev_workspace'.$colors[0].(($row['wplen']>0)?'_DOT':'').'.png" style="width:1.6em;margin-bottom:-5px;" alt="Workspace" /></a> <a href="mailto:'.$row['myrevemail'].'"><img src="/i/email_icon'.$colors[0].'.png" style="width:1.5em;margin-bottom:-3px;" /></a> <a onclick="detail(document.frm, '.$row['myrevid'].')" class="comlink0">'.$row['myrevemail'].'</a></td>');
    print('<td style="text-align:right;">'.$row['items'].'</td>');
    print('<td>'.rtrim(date('n/j/y g:ia', strtotime(converttouserdate2($row['lastaccessed'], $timezone))), 'm').'</td>');
    //print('<td>'.rtrim(date('n/j/y g:ia', strtotime(converttouserdate2($row['lupdt'], $timezone))), 'm').'</td>');
    print('<td>'.(($row['lupdt']==0)?'-':rtrim(date('n/j/y g:ia', strtotime(converttouserdate2($row['lupdt'], $timezone))), 'm')).'</td>');
    print('<td style="text-align:center;"><img src="/i/'.(($row['regcode']==1)?'checkmark':'flagedit').'.png" style="width:1em;" /></td>');
    print('<td>'.rtrim(date('n/j g:ia', strtotime(converttouserdate2($row['regdate'], $timezone))), 'm').'</td>');
    if($row['ipaddress'] != 'unknown')
      print('<td><a href="http://whatismyipaddress.com/ip/'.$row['ipaddress'].'" target="_blank" class="comlink0">'.$row['ipaddress'].'</a></td>');
    else
      print('<td>-</td>');
    print('</tr>');
    $numitms+= $row['items'];
    $ni++;
  }
  if($ni==0)
    print('<tr><td colspan="9" style="text-align:center;">No results</td></tr>');
?>
    <tr>
      <td style="text-align:right;"><?=(($pagnum-1)*$pagitmcnt)+$ni?></td>
      <td colspan="2">&nbsp;</td>
      <td style="text-align:right;"><?=$numitms?></td>
      <td colspan="5" style="text-align:center;"><?=$pagination?></td>
    </tr>
  </table>
<?}else{ // show a users myrev items

  print('<table class="gridtable" style="font-size:90%;">');
  print('<tr>');
  print('<td><a onclick="goback(document.frm, 0);">back</a></td>');
  print('<td colspan="3">&nbsp;'.printsqlerr($sqlerr).'</td>');
  print('<td><a onclick="valdel(document.frm);"><img src="/i/redx.png" style="width:1.6em;margin-bottom:-4px;"</a></td>');
  print('</tr>');
  $ni = 0;
  $numitms = 0;
  $sql = 'select mru.myrevid, mru.myrevname, mru.myrevemail, mru.password, mru.lastaccessed, ifnull(mru.myrevkeys, \'~~~~~\') myrevkeys, length(ifnull(mru.notes, \'\')) wplen,
         (select count(*) from myrevdata mrd where mrd.myrevid = mru.myrevid) items
         from myrevusers mru
         where mru.myrevid = '.$myrvid.' ';
  $row = rs($sql);
  print('<tr>');
  print('<td>'.$row['myrevname'].'</td>');
  print('<td><a onclick="olOpen(\'/myrevitemdetail.php?myrvid='.$myrvid.'&amp;test=0&amp;book=0&amp;chap=0&amp;vers=0\',600,500,1);" title="workspace"><img src="/i/myrev_workspace'.$colors[0].(($row['wplen']>0)?'_DOT':'').'.png" style="width:1.6em;margin-bottom:-5px;" alt="Workspace" /></a> <a href="mailto:'.$row['myrevemail'].'"><img src="/i/email_icon'.$colors[0].'.png" style="width:1.5em;margin-bottom:-3px;" /></a> '.$row['myrevemail'].'</td>');
  print('<td>'.$row['password'].'</td>');
  print('<td>'.(($row['lastaccessed']===null)?'never':rtrim(date('n/j g:ia', strtotime(converttouserdate2($row['lastaccessed'], $timezone))), 'm')).'</td>');
  print('<td style="text-align:right;">'.$row['items'].'</td>');
  print('</tr>');
  print('</table>');

  $mrkeys = explode('~', $row['myrevkeys']);

  $sortdir= ((isset($_REQUEST['sortdir']))?$_REQUEST['sortdir']:1); // default to asc
  $ascdesc = (($sortdir==1)?'asc ':'desc ');
  $orderby = 'order by ';
  $sortfld= ((isset($_REQUEST['sortfld']))?$_REQUEST['sortfld']:'canon');

//print('sortdir: '.$sortdir.'<br />');
//print('sortfld: '.$sortfld.'<br />');

  switch($sortfld){
  case 'canon':
    $orderby.= 'mrd.testament '.$ascdesc.', mrd.book '.$ascdesc.', mrd.chapter '.$ascdesc.', mrd.verse '.$ascdesc; break;
  case 'hilite':
      $orderby.= 'mrd.highlight '.$ascdesc.', mrd.testament, mrd.book, mrd.chapter, mrd.verse '.$ascdesc; break;
  case 'lastup':
    $orderby.= 'lastupdate '.$ascdesc.', mrd.testament, mrd.book, mrd.chapter, mrd.verse '; break;
  default:
    $orderby.= 'mrd.testament '.$ascdesc.', mrd.book '.$ascdesc.', mrd.chapter '.$ascdesc.', mrd.verse '.$ascdesc; break;
  }

  print('<table class="gridtable" style="margin-top:8px;max-width:768px;font-size:80%;">');
  print('<tr>');
  print('<td colspan="2" style="font-weight:bold;'.fixbg('canon').'"><a onclick="dosort(\'canon\',1 );" class="comlink0">Canon</a>'.fixsort('canon').'</td>');
  print('<td style="font-weight:bold;'.fixbg('hilite').'"><a onclick="dosort(\'hilite\',1 );" class="comlink0">Hilite</a>'.fixsort('hilite').'</td>');
  print('<td style="font-weight:bold;'.fixbg('lastup').'"><a onclick="dosort(\'lastup\',0 );" class="comlink0">LastUpdt</a>'.fixsort('lastup').'</td>');
  print('<td>del</td>');
  print('</tr>');
  $nj = 0;
  $cutoff = 750;
  $sql = 'select mrd.testament, mrd.book, mrd.chapter, mrd.verse, mrd.highlight, ifnull(mrd.lastupdate, \'2021-07-15 04:00\') lastupdate, ifnull(mrd.marginnote, \'\') marginnote,
         ifnull(mrd.myrevnotes, \'-\') myrevnotes,
         if(bk.abbr=\'-\', bk.title, bk.abbr) btitle
         from myrevdata mrd
         join book bk on (bk.testament = mrd.testament and bk.book = mrd.book)
         where mrd.myrevid = '.$myrvid.' '.$orderby.' ';
  //print($sql);
  $dat = dbquery($sql);
  while($row = mysqli_fetch_array($dat)){
    print('<tr>');
    print('<td colspan="2" style="vertical-align:top;white-space:nowrap;">'.fixdata($row).'</td>');
    print('<td style="vertical-align:top;white-space:nowrap;background-color:'.$hilitecolors[$row['highlight']].';">'.$mrkeys[$row['highlight']].'&nbsp;</td>');
    print('<td style="vertical-align:top;white-space:nowrap;">'.rtrim(date('n/j g:ia', strtotime(converttouserdate2($row['lastupdate'], $timezone))), 'm').'&nbsp;</td>');
    $itmid = $row['testament'].'|'.$row['book'].'|'.$row['chapter'].'|'.$row['verse'];
    print('<td style="vertical-align:top;"><input type="checkbox" name="delitm'.$nj.'" id="delitm'.$nj.'" value="1" /><input type="hidden" name="itm'.$nj.'" id="itm'.$nj.'" value="'.$itmid.'" /></td>');
    print('</tr>');
    $marginnote = '';
    if($row['marginnote'] != ''){
      $marginnote = str_replace('[br]', '<br />', $row['marginnote']);
      $marginnote = '<div class="marginnote" style="margin-left:0;margin-top:0;">'.$marginnote.'</div>';
    }
    $notes = $row['myrevnotes'];
    if(substr($notes, 0, 3)=='<p>'){
      $notes = '<p class="spc" style="margin-top:0;margin-bottom:0;">'.substr($notes,3);
    }
    if(strlen($notes)>$cutoff){
       $notes = truncateHtml($notes, $cutoff);
       $notes.= ' <a onclick="olOpen(\'/myrevitemdetail.php?myrvid='.$myrvid.'&amp;test='.$row['testament'].'&amp;book='.$row['book'].'&amp;chap='.$row['chapter'].'&amp;vers='.$row['verse'].'\',600,500,1);" title="click to read more">read the rest..</a>';
    }
    if($marginnote != '' || $notes != '-')
      print('<tr style="border-left:none;"><td style="width:20px;border-left:none;">&nbsp;</td><td colspan="4" style="font-size:90%;">'.$marginnote.$notes.'</td></tr>');
    $nj++;
  }
  if($nj==0)
    print('<tr><td colspan="5" style="color:red;">This MyREV user has no items...</td></tr>');
  else
    print('<tr><td colspan="5" style="text-align:right;"><input type="submit" value="Del.." onclick="return valdelitems(document.frm);" /></td></tr>');
  print('</table>');

}
?>

  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="numitms" value="<?=$nj?>" />
  <input type="hidden" name="myrvid" value="<?=$myrvid?>" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  <input type="hidden" name="sortfld" value="<?=$sortfld?>" />
  <input type="hidden" name="sortdir" value="<?=$sortdir?>" />
  <input type="hidden" name="oper" value="" />
</form>
<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>

<script>
  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);

  function valdelitems(f){
    var haveset=0;
    for(var i=0;i<<?=$nj?>;i++){
      if(f['delitm'+i].checked) haveset = 1;
    }
    if(haveset==0){
      alert('No items are checked..');
      return false;
    }
    if(haveset==1 && !confirm('Are you sure you want to delete the checked MyREV user\'s items?\n\nThis is not undoable!')) return false;
    f.oper.value = 'delmritems';
    return true;
  }

  function valdel(f){
    if(!confirm('Are you sure you want to delete this MyREV user?\n\nThis is not undoable!!')) return false;
    f.oper.value = 'delmruser';
    f.submit();
  }

  function dosort(fld,dflt){
    var f = document.frm;
    var sortfld = '<?=$sortfld?>';
    var sortdir = <?=$sortdir?>;
    f.sortfld.value = fld;
    f.sortdir.value = ((sortfld==fld)?1-sortdir:dflt);
    f.submit();
  }

  function detail(f, id){
    f.myrvid.value = id;
    f.sortfld.value = 'canon';
    f.sortdir.value = 1;
    f.submit();
  }
  function goback(f, id){
    f.myrvid.value = id;
    f.sortfld.value = 'lastv';
    f.sortdir.value = 0;
    f.submit();
  }
  function dopage(pnum){
    document.frm.pagnum.value=pnum;
    document.frm.submit();
  }

  function resetpage(f, check){
    if(check){
      filt = trim(f.filter.value);
      //if(filt.length>0 && filt.length<3){
      //  alert('Please enter at least three characters to search for.');
      //  f.filter.focus();
      //  f.filter.select();
      //  return false;
      //}
    }
    f.pagnum.value=1;
    f.submit();
  }

</script>
<?
function fixdata($r){
  $ret = '';
  if($r['testament']<2)
    $ret = $r['btitle'].' '.$r['chapter'].':'.$r['verse'];
  else{
    switch($r['testament']){
    case 3: // appx
      $ret = '<a href="/appx/'.$r['book'].'" target="_blank">'.$r['btitle'].'</a>';
      break;
    case 4: // wordstudy
      $ret = '<a href="/word/'.$r['btitle'].'" target="_blank">'.$r['btitle'].'</a>';
      break;
    default:
      $ret = $r['btitle'];
    }
  }
  return $ret;
}

function pagination($pnum, $pitmcnt, $ptot){
  global $colors;
  $ret='';
  if($pnum-1 > 0){
    $ret.= ' <a onclick="dopage('.($pnum-1).');">&laquo;prev</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">&laquo;prev</span> ';
  }
  $ret.= 'Page ';
  $ret.= '<select onchange="dopage(this.selectedIndex+1);">';
  for($ni=1;$ni<=$ptot;$ni++){
    $ret .= '<option'.fixsel($ni, $pnum).'>'.$ni.'</option>';
  }
  $ret.= '</select>';
  $ret.= ' of '.$ptot;
  if($pnum+1 <= $ptot){
    $ret.= ' <a onclick="dopage('.($pnum+1).');">next&raquo;</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">next&raquo;</span> ';
  }
  return $ret;
}

function fixsort($sort){
  global $sortfld, $sortdir;
  $ret = '';
  if ($sort==$sortfld) {
    $ret = '<span style="color:red;">&nbsp;'.(($sortdir==1)?'&uarr;':'&darr;').'</span>';
  }
  return $ret;
}
function fixbg($sort){
  global $sortfld, $sortdir, $colors;
  $ret = '';
  if ($sort==$sortfld) {
    $ret = 'background-color:'.$colors[6].';';
  }
  return $ret;
}


?>

