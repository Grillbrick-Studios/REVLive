<?php
if(empty($userid) || $userid==0 || $resedit==0) {print('<h3>unauthorized access</h3>');return;}
$pagnum    = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$pltype=((isset($_REQUEST['pltype']))?$_REQUEST['pltype']:0);
$filterrt=((isset($_REQUEST['filterrt']))?$_REQUEST['filterrt']:0);
$stitle = 'Manage Playlists';

$oper = ((isset($_POST['oper']))?$_POST['oper']:'nada');
if($oper=='savresource'){
  for($ni=0;$ni<$_REQUEST['nummed'];$ni++){
    $upd = dbquery('update playlist set sqn = '.processsqlnumb(nvl($_REQUEST['plsqn'.$ni], 0), 99, 0, 0).' where playlistid = '.$_REQUEST['plid'.$ni].' ');
  }
}

?>
<span class="pageheader"><?=$stitle?></span>
<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:90%;">
<form name="frm" method="post" action="/">
Playlist Type: <select name="pltype" onchange="document.frm.submit()" style="margin:8px 0;">
  <option value="0"<?=fixsel(0, $pltype);?>>all</option>
  <option value="1"<?=fixsel(1, $pltype);?>>Video</option>
  <option value="3"<?=fixsel(3, $pltype);?>>Audio</option>
  <option value="4"<?=fixsel(4, $pltype);?>>Article</option>
  <option value="5"<?=fixsel(5, $pltype);?>>Seminar</option>
  <option value="7"<?=fixsel(7, $pltype);?>>Library</option>
</select>


  <table class="gridtable" style="padding:0;max-width:720px;" style="text-align:left;">
    <tr>
      <td colspan="6" style="text-align:left;"><a onclick="editplst(document.frm,-1);">New playlist</a>
    </tr>
    <tr><td>ID</td><td>Type</td><td>Title</td><td>sqn</td><td>Edit</td></tr>
<?
$ni = 0;
$sql = 'select playlistid, pltypeid, playlisttitle, sqn from playlist ';
if($pltype>0)
  $sql.= 'where pltypeid = '.$pltype.' ';
$sql.= 'order by 2, 4, 3 ';
$ply = dbquery($sql);
while($row = mysqli_fetch_array($ply)){
?>
    <tr>
      <td><?=$row['playlistid']?></td>
      <td><?=getplaylisttypename($row['pltypeid'])?></td>
      <td style="text-align:left;"><?=$row['playlisttitle']?></td>
      <td><input type="hidden" name="plid<?=$ni?>" value="<?=$row[0]?>" /><input type="text" name="plsqn<?=$ni?>" value="<?=$row['sqn']?>" style="width:16px;text-align:right;" /></td>
      <td><a onclick="editplst(document.frm,<?=$row['playlistid']?>)"><img src="/i/edit.gif" width="14" /></a>
        <input type="hidden" name="playlistid<?=$ni?>" value="<?=$row['playlistid']?>">
      </td>
    </tr>
<?
  $ni++;
}
if($ni==0) print('<tr><td colspan="6"><span style="color:red;">There are no playlists</span></td></tr>');
?>
    <tr>
      <td colspan="6" style="text-align:left;"><a onclick="editplst(document.frm,-1);">New playlist</a></td>
    </tr>
    <tr>
      <td colspan="5">
<?if($pltype>0){?>
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
<?}?>
        <input type="button" name="btnback" value="Back to Resources" onclick="document.frm.page.value=36;document.frm.submit();">
      </td>
    </tr>
  </table>

  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  <input type="hidden" name="filter" value="<?=$filter?>" />
  <input type="hidden" name="filterrt" value="<?=$filterrt?>" />
  <input type="hidden" name="playlistid" value="" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="nummed" value="<?=$ni?>">
</form>
</div>
<script>
  function validate(f){
    f.oper.value = 'savresource';
    return true;
  }

  function editplst(f, id){
    f.page.value=39; // playlistedit.php
    f.playlistid.value=id;
    f.submit();
  }

</script>

