<?php
if(empty($userid) || $userid==0 || $resedit==0) {print('<h3>unauthorized access</h3>');return;}
$pagnum    = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$filterrt=((isset($_REQUEST['filterrt']))?$_REQUEST['filterrt']:0);
$playlistid = ((isset($_REQUEST['playlistid']))?$_REQUEST['playlistid']:-1);
$stitle = 'Edit '.(($playlistid==-1)?'New ':'').'Playlist';
$pltype=((isset($_REQUEST['pltype']))?$_REQUEST['pltype']:0);

$oper = ((isset($_POST['oper']))?$_POST['oper']:'nada');

$msg = "";
$sqlerr='';
if($oper=='del'){
  $sql = 'update resource set playlistid = 0, playlistsqn = 0 where playlistid = '.$playlistid.' ';
  $delete = dbquery($sql);
  $sql = 'delete from playlist where playlistid = '.$playlistid.' ';
  $delete = dbquery($sql);
  $playlistid=0;
  if($sqlerr=='') $sqlerr = 'Deleted..';
}
if($oper=='savplaylist'){
  $pltypeid = processsqlnumb($_POST['pltypeid'], 10, 0, 0);
  $playlisttitle = processsqltext($_POST['playlisttitle'],  200, 0, 'missing!');
  $sqn = processsqlnumb($_POST['sqn'],  99, 0, 0);
  $thumbnail     = processsqltext(((isset($_POST['thumbnail']))?$_POST['thumbnail']:''),  200, 1, '');
  $description = processsqlcomm(left($_POST['description'], 2800), 1, '');
  if($playlistid==-1){ // new
    $sql = 'insert into playlist(pltypeid, playlisttitle, sqn, edituserid, thumbnail, description) values
           ('.$pltypeid.', '.$playlisttitle.', '.$sqn.', '.$userid.', '.$thumbnail.', '.$description.') ';
    $update = dbquery($sql);
    $sql = 'select max(playlistid) from playlist ';
    $row = rs($sql);
    $playlistid = $row[0];
  }else{
    $sql = 'update playlist set
            pltypeid = '.$pltypeid.',
            playlisttitle = '.$playlisttitle.',
            sqn= '.$sqn.',
            edituserid = '.$userid.',
            thumbnail = '.$thumbnail.',
            description = '.$description.'
            where playlistid = '.$playlistid.' ';
    //print($sql);
    $update = dbquery($sql);
  }
  if($sqlerr=='') $sqlerr = datsav;
}

if($playlistid>0){
  $sql = 'select pltypeid, playlisttitle, sqn, edituserid, thumbnail, description from playlist where playlistid = '.$playlistid.' ';
  $row = rs($sql);
  $pltypeid = $row['pltypeid'];
  $playlisttitle = $row['playlisttitle'];
  $sqn = $row['sqn'];
  $edituserid = $row['edituserid'];
  $thumbnail = $row['thumbnail'];
  $description = $row['description'];
}else{
  $pltypeid = '1';
  $playlisttitle = '';
  $sqn = 0;
  $edituserid = $userid;
  $thumbnail = '';
  $description = '';
}

?>
<span class="pageheader"><?=$stitle?></span>
<form name="frm" method="post" action="/">
  <table class="gridtable" style="width:<?=(($ismobile)?'600':'720')?>px;">
    <tr><td colspan="11">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>Type</td><td>
      <select name="pltypeid">
        <option value="1"<?=fixsel(1, $pltypeid);?>>Video</option>
        <option value="3"<?=fixsel(3, $pltypeid);?>>Audio</option>
        <option value="4"<?=fixsel(4, $pltypeid);?>>Article</option>
        <option value="5"<?=fixsel(5, $pltypeid);?>>Seminar</option>
        <option value="7"<?=fixsel(7, $pltypeid);?>>Library</option>
      </select>
      </td>
    </tr>
    <tr><td>Title</td>
      <td><input type="text" name="playlisttitle" id="playlisttitle" value="<?=$playlisttitle?>" autocomplete="off" style="width:<?=(($ismobile)?'440':'580')?>px;"></td>
    </tr>
<?//if($pltypeid==1){?>
    <tr><td>Thumbnail</td>
      <td><input type="text" name="thumbnail" value="<?=$thumbnail?>" autocomplete="off" style="width:90%;"></td>
    </tr>
<?//}?>
    <tr><td>Description</td>
      <td><textarea name="description" id="description" style="width:98%;height:120px;"><?=$description?></textarea></td>
    </tr>
<?if($edituserid>0){
    $sql = 'select ifnull(revusername, myrevname) from myrevusers where myrevid > 0 and userid = '.$edituserid.' ';
    $rrow = rs($sql);
    if($rrow)
      $edtuser = $rrow[0];
    else
      $edtuser = 'unknown';
}else{
    $edtuser = 'none';
}
    ?>
    <tr><td>sqn</td>
      <td><input type="text" name="sqn" value="<?=$sqn?>" autocomplete="off" style="width:20px;"></td>
    </tr>
    <tr><td colspan="2"><small>Last edited by: <?=$edtuser?></small></td></tr>
    <tr>
      <td colspan="2">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <?if($playlistid>0){?>
        <input type="submit" name="btndel" value="Delete" onclick="return valdel(document.frm)">
        <?}?>
        <input type="button" name="btnback" value="Back to Playlists" onclick="document.frm.page.value=38;document.frm.submit();">
      </td>
    </tr>
  </table>

  <input type="hidden" name="filter" value="<?=$filter?>" />
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="playlistid" value="<?=$playlistid?>" />
  <input type="hidden" name="filter" value="<?=$filter?>" />
  <input type="hidden" name="filterrt" value="<?=$filterrt?>" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  <input type="hidden" name="pltype" value="<?=$pltype?>" />
  <input type="hidden" name="oper" value="" />
</form>

<script>

  function validate(f){
    f.oper.value = 'savplaylist';
    return true;
  }

  function valdel(f){
    if(confirm('Are you sure you want to delete this playlist?')){
      f.oper.value = 'del';
      f.submit();
    }else{
      return false;
    }

  }

</script>
  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
  <script>
    CKEDITOR.replace( 'description',
    {
      toolbar :
      [
        { name: 'document', items : [ 'Source','AutoCorrect'] },
        { name: 'basicstyles', items : [ 'Bold','-','Italic' ] },
        { name: 'tools', items : ['SpecialChar' ] }
      ],
      height : '140',
      //enterMode : CKEDITOR.ENTER_BR,
      //shiftEnterMode : CKEDITOR.ENTER_P
    }
    );
  </script>

