<?php
if(empty($userid) || $userid==0 || empty($superman) || $superman==0) {print('<h3>unauthorized access</h3>');return;}

$oper=((isset($_REQUEST['oper']))?$_REQUEST['oper']:'xx');
if($oper=='release'){
  $relcnt = ((isset($_REQUEST['relcnt']))?$_REQUEST['relcnt']:0);
  for($ni=0;$ni<$relcnt;$ni++){
    if(isset($_POST['release'.$ni])){
      $relkey = $_POST['releasekey'.$ni];
      $ar = explode('|', $relkey);
      $rel = dbquery('update verse set edituserid = 0, editsession = null, lockeduntil = null where testament = '.$ar[0].' and book = '.$ar[1].' and chapter = '.$ar[2].' and verse = '.$ar[3].' ');
    }
  }
}
if($oper=='releaseed'){
  $reledcnt = ((isset($_REQUEST['reledcnt']))?$_REQUEST['reledcnt']:0);
  for($ni=0;$ni<$reledcnt;$ni++){
    if(isset($_POST['releaseed'.$ni])){
      $relkey = $_POST['releaseedkey'.$ni];
      $ar = explode('|', $relkey);
      $rel = dbquery('update editnotes set edituserid = 0, editlockeduntil = null where testament = '.$ar[0].' and book = '.$ar[1].' and chapter = '.$ar[2].' and verse = '.$ar[3].' ');
    }
  }
}

$sortdir= ((isset($_REQUEST['sortdir']))?$_REQUEST['sortdir']:1); // default to asc
$sortfld= ((isset($_REQUEST['sortfld']))?$_REQUEST['sortfld']:'refr');

?>
<span class="pageheader">Manage SOPS Sessions</span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:90%;">
<p>&nbsp;</p>
<form name="frm" method="post" action="/">

<h3 style="margin:0;padding:0;">Verses</h3>
<table style="margin:0 auto;border-spacing:8;">
  <tr>
    <td style="font-weight:bold;<?=fixbg('refr')?>"><a onclick="dosort('refr',1);" class="comlink0">Reference</a><?=fixsort('refr')?></td>
    <td style="font-weight:bold;<?=fixbg('user')?>"><a onclick="dosort('user',1);" class="comlink0">User</a><?=fixsort('user')?></td>
    <td style="font-weight:bold;<?=fixbg('lock')?>"><a onclick="dosort('lock',1);" class="comlink0">Locked Until</a><?=fixsort('lock')?></td>
    <th>Rel <input type="checkbox"  onclick="chkall(this)" /></th>
  </tr>
<?
$sql = 'select v.testament, v.book, v.chapter, v.verse, ifnull(mru.revusername, concat(v.edituserid,\': ??\')) revusername, v.lockeduntil
        from verse v
        left join myrevusers mru on mru.userid = v.edituserid and mru.myrevid > 0
        where v.testament in (0,1)
        and v.edituserid > 0 ';



$ascdesc = (($sortdir==1)?'asc ':'desc ');
$orderby = 'order by ';
switch($sortfld){
case 'user':
  $orderby.= '5 '.$ascdesc; break;
case 'lock':
  $orderby.= '6 '.$ascdesc; break;
default:
  $orderby.= '1 '.$ascdesc.', 2 '.$ascdesc.', 3 '.$ascdesc.', 4 '.$ascdesc; break;
}

$sql.= $orderby;


$ni=0;
$verses = dbquery($sql);
while($row = mysqli_fetch_array($verses)){
  $btitle = getbooktitle($row['testament'],$row['book'], (($ismobile)?0:1));
  print('<tr>');
  print('<td style="text-align:left;">'.$btitle.' '.$row['chapter'].':'.$row['verse'].'</td>');
  print('<td style="text-align:left;">'.$row['revusername'].'</td>');
  print('<td style="text-align:right;">'.rtrim(date('n/j g:i:sa', strtotime(converttouserdate2($row['lockeduntil'], $timezone))), 'm').'</td>');
  print('<td style="text-align:right;"><input type="checkbox" name="release'.$ni.'" id="release'.$ni.'" value="1" />');
  print('<input type="hidden" name="releasekey'.$ni.'" value="'.$row['testament'].'|'.$row['book'].'|'.$row['chapter'].'|'.$row['verse'].'" /></td>');
  print('</tr>');
  $ni++;
}
if($ni==0)
  print('<tr><td colspan="4" style="color:red;">There are no locked verses.</td></tr>');
else
  print('<tr><td colspan="4" style="text-align:right;"><input type="submit" value="Release" onclick="return validate(document.frm);" /></td></tr>');


?>
</table>
<p>&nbsp;</p>
<h3 style="margin:0;padding:0;">Editnotes</h3>
<table style="margin:0 auto;border-spacing:8;">
  <tr>
    <td style="font-weight:bold;<?=fixbg('refr')?>"><a onclick="dosort('refr',1);" class="comlink0">Reference</a><?=fixsort('refr')?></td>
    <td style="font-weight:bold;<?=fixbg('user')?>"><a onclick="dosort('user',1);" class="comlink0">User</a><?=fixsort('user')?></td>
    <td style="font-weight:bold;<?=fixbg('lock')?>"><a onclick="dosort('lock',1);" class="comlink0">Locked Until</a><?=fixsort('lock')?></td>
    <th>Rel <input type="checkbox"  onclick="chkalled(this)" /></th>
  </tr>
<?
$sql = 'select e.testament, e.book, e.chapter, e.verse, ifnull(mru.revusername, concat(e.edituserid,\': ??\')) revusername, e.editlockeduntil
        from editnotes e
        left join myrevusers mru on mru.userid = e.edituserid and mru.myrevid > 0
        where e.testament in (0,1)
        and e.edituserid > 0 ';



$ascdesc = (($sortdir==1)?'asc ':'desc ');
$orderby = 'order by ';
switch($sortfld){
case 'user':
  $orderby.= '5 '.$ascdesc; break;
case 'lock':
  $orderby.= '6 '.$ascdesc; break;
default:
  $orderby.= '1 '.$ascdesc.', 2 '.$ascdesc.', 3 '.$ascdesc.', 4 '.$ascdesc; break;
}

$sql.= $orderby;


$nj=0;
$verses = dbquery($sql);
while($row = mysqli_fetch_array($verses)){
  $btitle = getbooktitle($row['testament'],$row['book'], (($ismobile)?0:1));
  print('<tr>');
  print('<td style="text-align:left;">'.$btitle.' '.$row['chapter'].':'.$row['verse'].'</td>');
  print('<td style="text-align:left;">'.$row['revusername'].'</td>');
  print('<td style="text-align:right;">'.rtrim(date('n/j g:i:sa', strtotime(converttouserdate2($row['editlockeduntil'], $timezone))), 'm').'</td>');
  print('<td style="text-align:right;"><input type="checkbox" name="releaseed'.$nj.'" id="releaseed'.$nj.'" value="1" />');
  print('<input type="hidden" name="releaseedkey'.$nj.'" value="'.$row['testament'].'|'.$row['book'].'|'.$row['chapter'].'|'.$row['verse'].'" /></td>');
  print('</tr>');
  $nj++;
}
if($nj==0)
  print('<tr><td colspan="4" style="color:red;">There are no locked editnotes.</td></tr>');
else
  print('<tr><td colspan="4" style="text-align:right;"><input type="submit" value="Release" onclick="return validateed(document.frm);" /></td></tr>');


?>
</table>
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="sortfld" value="<?=$sortfld?>" />
  <input type="hidden" name="sortdir" value="<?=$sortdir?>" />
  <input type="hidden" name="relcnt" value="<?=$ni?>" />
  <input type="hidden" name="reledcnt" value="<?=$nj?>" />
  <input type="hidden" name="oper" value="" />
</form>
  &nbsp;<br />&nbsp;<br /><small>SOPS sessions more than 6 hours old are automatically released.</small>
  </div>
  <script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
  <script>

  var ni = <?=$ni?>;
  var nj = <?=$nj?>;
  function validate(f){
    var haverel = 0;
    for(var i=0;i<ni; i++){
      if(f['release'+i].checked){
        haverel = 1;
        break;
      }
    }
    if(haverel==0){
      alert('no verses are checked');
      return false;
    }
    if(!confirm('Are you sure you want to release the checked verses?')) return false;
    f.oper.value='release';
    return true;
  }

  function validateed(f){
    var haverel = 0;
    for(var i=0;i<nj; i++){
      if(f['releaseed'+i].checked){
        haverel = 1;
        break;
      }
    }
    if(haverel==0){
      alert('no editnotes are checked');
      return false;
    }
    if(!confirm('Are you sure you want to release the checked editnotes?')) return false;
    f.oper.value='releaseed';
    return true;
  }

  function chkall(el){
    var checked=0;
    if(ni>0) checked = $('release0').checked;
    for(i=0;i<ni;i++){
      $('release'+i).checked = !checked;
    }
    el.checked = !checked;
  }

  function chkalled(el){
    var checked=0;
    if(nj>0) checked = $('releaseed0').checked;
    for(i=0;i<nj;i++){
      $('releaseed'+i).checked = !checked;
    }
    el.checked = !checked;
  }

  function dosort(fld,dflt){
    var f = document.frm;
    var sortfld = '<?=$sortfld?>';
    var sortdir = <?=$sortdir?>;
    f.sortfld.value = fld;
    f.sortdir.value = ((sortfld==fld)?1-sortdir:dflt);
    f.submit();
  }

  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);

  </script>
<?

function fixsort($sort){
  global $sortfld, $sortdir;
  $ret = '&nbsp;&nbsp;&nbsp;&nbsp;';
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


