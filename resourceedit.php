<?php
if(empty($userid) || $userid==0 || $resedit==0) {print('<h3>unauthorized access</h3>');return;}
$resourceid=((isset($_REQUEST['resourceid']))?$_REQUEST['resourceid']:-1);
if($resourceid==-1 && isset($_REQUEST['temp'])){
  $resourceid = $_REQUEST['temp'];
  $sqlerr = 'Use history to go back where you came from. Sorry about any form resubmission notifications.';
}else $sqlerr='';

$sort=((isset($_REQUEST['sort']))?$_REQUEST['sort']:0);
$stitle = (($resourceid==-1)?'Add New':'Edit').' Resource';
$filterrt=((isset($_REQUEST['filterrt']))?$_REQUEST['filterrt']:3);
$filterpl=((isset($_REQUEST['filterpl']))?$_REQUEST['filterpl']:0);
$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$pagnum=((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$factive=((isset($_REQUEST['factive']))?$_REQUEST['factive']:0);
$linkedonly=((isset($_REQUEST['linkedonly']))?$_REQUEST['linkedonly']:0);
$ffinalized=((isset($_REQUEST['ffinalized']))?$_REQUEST['ffinalized']:0);

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
//$sqlerr='';
if($oper=='del'){
  $sql = 'delete from resourceassign where resourceid = '.$resourceid.' ';
  $delete = dbquery($sql);
  $sql = 'delete from topic_assoc where resourceid = '.$resourceid.' ';
  $delete = dbquery($sql);
  $sql = 'delete from resource where resourceid = '.$resourceid.' ';
  $delete = dbquery($sql);
  $resourcetype  = processsqlnumb($_POST['resourcetype'], 15, 0, 0);
  if($resourcetype==7 && $_POST['libfile'] != '' && file_exists($docroot.$_POST['libfile'])){
    // delete the old file
    unlink($docroot.$_POST['libfile']);
  }
  $resourceid=0;
  if($sqlerr=='') $sqlerr = 'Deleted.. returning to resource list';
}
if($oper=='savresource'){
  if($resourceid>-1){
    // grab original resourcetitle, description
    $row = rs('select title, description
              from resource
              where resourceid = '.$resourceid.' ');
    $beforetitl = $row[0];
    $beforetitl = processsqltext($beforetitl, 200, 0, 'missing title!');
    $beforetitl = substr($beforetitl, 1, strlen($beforetitl)-2);
    //print('beforetitl: '.$beforetitl.'<br />');

    $beforedesc = $row[1];
    $beforedesc = processsqlcomm($beforedesc, 1, '-');
    $beforedesc = replacgreekhtml($beforedesc);
    if($beforedesc!='null') $beforedesc = substr($beforedesc, 1, strlen($beforedesc)-2);
    if($beforedesc === '-' || $beforedesc=='null') $beforedesc = '';
  }else{
    $beforetitl = '';
    $beforedesc = '';
  }

  $resourcetype  = processsqlnumb($_POST['resourcetype'], 15, 0, 0); // twas bug... 15 is plenty
  $playlistid    = processsqlnumb(((isset($_POST['playlistid']))?$_POST['playlistid']:0), 999, 0, 0);
  $playlistsqn   = processsqlnumb(((isset($_POST['playlistsqn']))?$_POST['playlistsqn']:0), 99, 0, 0);
  $active        = isset($_POST['active'])?1:0;
  $finalized     = isset($_POST['finalized'])?1:0;
  $resourcetitle = processsqltext($_POST['resourcetitle'],  300, 0, 'missing title!');
  $identifier    = processsqltext(((isset($_POST['identifier']))?$_POST['identifier']:''),  200, 1, '');
  $publishedon   = processsqltext(((isset($_POST['publishedon']))?$_POST['publishedon']:''),  12, 1, '');
  $description   = processsqlcomm(((isset($_POST['description']))?left($_POST['description'], 2900):''), 1, '');
  $keywords      = processsqltext(((isset($_POST['keywords']))?$_POST['keywords']:''),  200, 1, '');
  $source        = processsqltext(((isset($_POST['source']))?$_POST['source']:''),  20, 1, '');
  $duration      = processsqltext(((isset($_POST['duration']))?$_POST['duration']:''),  10, 1, '');
  if($resourcetype==7){ // library
    if(isset($_FILES["libfilnam"]["name"]) && $_FILES["libfilnam"]["name"] !== ''){
      $target_dir = $docroot.'/export/library/';
      $target_file = $target_dir.basename($_FILES["libfilnam"]["name"]);
      $libFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

      // checking for file size in js on the client
      if($libFileType=='pdf' || $libFileType=='docx' || $libFileType=='xlsx') {
        if (!move_uploaded_file($_FILES["libfilnam"]["tmp_name"], $target_file)) {
          $sqlerr = 'Sorry, there was an error uploading your file.';
          $externalurl = processsqltext(((isset($_POST['libfile']))?$_POST['libfile']:''),  400, 1, '');
        }else{
          $externalurl = processsqltext('/export/library/'.basename($_FILES["libfilnam"]["name"]), 400, 1, '');
          if('/export/library/'.basename($_FILES["libfilnam"]["name"]) != $_POST['libfile'] && $_POST['libfile'] != '' && file_exists($docroot.$_POST['libfile'])){
            // delete the old file
            unlink($docroot.$_POST['libfile']);
          }
        }
      }else{
        $sqlerr = 'Sorry, only PDF, DOCX, and XLSX files are allowed for Library items.';
        $externalurl = processsqltext(((isset($_POST['libfile']))?$_POST['libfile']:''),  400, 1, '');
      }
    }else{
      $externalurl = processsqltext(((isset($_POST['libfile']))?$_POST['libfile']:''),  400, 1, '');
    }
  }else{
    $externalurl   = processsqltext(((isset($_POST['externalurl']))?$_POST['externalurl']:''),  400, 1, '');
  }
  $thumbnail     = processsqltext(((isset($_POST['thumbnail']))?$_POST['thumbnail']:''),  200, 1, '');
  $editcomment   = processsqltext(((isset($_POST['editcomment']))?$_POST['editcomment']:''),  200, 1, '');
  $content       = processsqlcomm(((isset($_POST['content']))?$_POST['content']:''), 1, '');


  $aftertitl = substr($resourcetitle, 1, strlen($resourcetitle)-2);
  //print('aftertitl: '.$aftertitl.'<br />');
  if($beforetitl===$aftertitl) $titldiff = null;
  else $titldiff = htmlDiff($beforetitl, $aftertitl);

  $afterdesc = $description;
  if($afterdesc!= 'null') $afterdesc = substr($afterdesc, 1, strlen($afterdesc)-2);
  if($afterdesc === '-' || $afterdesc=='null') $afterdesc = '';
  if($beforedesc===$afterdesc) $descdiff = null;
  else $descdiff = htmlDiff($beforedesc, $afterdesc);
  $descdiff = str_replace('\\', '\\\\', $descdiff??'');
  $descdiff = str_replace('\'', '\\\'', $descdiff);

  if($resourceid==-1){ // new
    $sql = 'select 1 from resource where identifier = '.$identifier.' ';
    $row = rs($sql);
    if($row){
      $sqlerr = 'resource already exists!';
    }else{
      $sql = 'insert into resource(resourcetype, title, identifier, publishedon, description, active, finalized, playlistid, playlistsqn, keywords, source, duration, externalurl, thumbnail, editcomment, edituserid, content) values
             ('.$resourcetype.', '.$resourcetitle.', '.$identifier.', '.$publishedon.', '.$description.', '.$active.', '.$finalized.', '.$playlistid.', '.$playlistsqn.', '.$keywords.', '.$source.', '.$duration.', '.$externalurl.', '.$thumbnail.', '.$editcomment.', '.$userid.', '.$content.') ';
      $update = dbquery($sql);
      $sql = 'select max(resourceid) from resource ';
      $row = rs($sql);
      $resourceid = $row[0];
    }
  }else{
    $sql = 'update resource set
            resourcetype = '.$resourcetype.',
            title = '.$resourcetitle.',
            identifier= '.$identifier.',
            publishedon = '.$publishedon.',
            description = '.$description.',
            active = '.$active.',
            finalized = '.$finalized.',
            playlistid = '.$playlistid.',
            playlistsqn = '.$playlistsqn.',
            keywords = '.$keywords.',
            source = '.$source.',
            duration = '.$duration.',
            externalurl = '.$externalurl.',
            thumbnail = '.$thumbnail.',
            editcomment = '.$editcomment.',
            edituserid = '.$userid.',
            content = '.$content.'
            where resourceid = '.$resourceid.' ';
    //print($sql);
    $update = dbquery($sql);

    if($resourcetype==7){
      // handle event categories
      $catlibcnt = $_POST['catlibcnt'];
      for($ni=0;$ni<=$catlibcnt;$ni++){
        if($ni<$catlibcnt){
          if(isset($_POST['catlib'.$ni]))
            $del = dbquery('delete from category_assoc where eventid = '.$resourceid.' and catid = '.$_POST['catlib'.$ni].' ');
        }else{
          if($_POST['catlib'.$catlibcnt]>0)
            $ins = dbquery('insert into category_assoc (eventid, catid) values ('.$resourceid.', '.$_POST['catlib'.$catlibcnt].') ');
        }
      }
    }

  }
  //$logid = logedit(37,0,0,0,0,$userid,$resourceid, 0, str_replace('\'', '\\\'', $titldiff), $descdiff, '', '');
  $logid = logedit(37,0,0,0,0,$userid,$resourceid, 0, $titldiff, $descdiff, '', '');

  if($sqlerr=='') $sqlerr = datsav;
}

if($resourceid>0){
  $sql = 'select resourceid, resourcetype, title, description, publishedon, source, duration, playlistid, playlistsqn, keywords,
          identifier, externalurl, thumbnail, active, finalized, editcomment, content
          from resource where resourceid = '.$resourceid.' ';
  $row = rs($sql);
  $resourcetype = $row['resourcetype'];
  $resourcetitle= $row['title'];
  $description  = $row['description'];
  $publishedon  = $row['publishedon'];
  $source       = $row['source'];
  $duration     = $row['duration'];
  $playlistid   = $row['playlistid'];
  $playlistsqn  = $row['playlistsqn'];
  $keywords     = $row['keywords'];
  $identifier   = $row['identifier'];
  $externalurl  = $row['externalurl'];
  $thumbnail    = $row['thumbnail'];
  $active       = $row['active'];
  $finalized    = $row['finalized'];
  $editcomment  = $row['editcomment'];
  $content      = $row['content'];
}else{
  $resourcetype = 0;
  $resourcetitle= 'New Resource Title';
  $description  = '';
  $publishedon  = '';
  $source       = '';
  $duration     = '';
  $playlistid   = 0;
  $playlistsqn  = 0;
  $keywords     = '';
  $identifier   = '';
  $externalurl  = '';
  $thumbnail    = '';
  $active       = 0;
  $finalized    = 0;
  $editcomment  = '';
  $content      = '';
}

?>
<span class="pageheader"><?=$stitle?></span>
<form name="frm" method="post" enctype="multipart/form-data" action="/">
  <table class="gridtable" style="font-size:80%;width:98%;max-width:800px;min-width:480px;">
    <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td style="width:22%;white-space:nowrap;">Resource Type</td><td style="width:78%;">
      <span style="color:red">*</span>
      <select name="resourcetype">
        <option value="0"<?=fixsel(0, $resourcetype);?>>-- select --</option>
        <option value="1"<?=fixsel(1, $resourcetype);?>>Youtube</option>
        <!--<option value="2"<?=fixsel(2, $resourcetype);?>>MP4</option>-->
        <option value="3"<?=fixsel(3, $resourcetype);?>>Audio</option>
        <option value="4"<?=fixsel(4, $resourcetype);?>>Seminar</option>
        <option value="5"<?=fixsel(5, $resourcetype);?>>Article</option>
        <!--<option value="6"<?=fixsel(6, $resourcetype);?>>Book Excerpt</option>-->
        <option value="7"<?=fixsel(7, $resourcetype);?>>Library (beta!)</option>
      </select>
    <?$needhiddenthumb=0;
      if($resourceid>0){
        print('&nbsp;&nbsp;&nbsp;&nbsp;Test:');
        if($resourcetype==5 || $resourcetype==7){
          print(' <a href="'.$externalurl.'" target="_blank"><img src="/i/assign.png" alt="view" width="22" /></a>');
        }else{
          print(' <a onclick="showresource('.$resourceid.');"><img src="/i/'.(($resourcetype==3 || $resourcetype==4)?'audio':(($resourcetype<4)?'video':'commentary'.$colors[0])).'.png" alt="play media" width="18" /></a>');
        }
      }else{
        print('&nbsp;&nbsp;<span style="color:red">&lt;&lt;&lt;&lt; choose first, then Submit!</span>');
      }?>
      &nbsp;&nbsp;&nbsp;&nbsp;Active: <input type="checkbox" name="active" value="1"<?=fixchk($active)?>>
      &nbsp;&nbsp;&nbsp;&nbsp;Finalized: <input type="checkbox" name="finalized" value="1"<?=fixchk($finalized)?>>
      &nbsp;&nbsp;&nbsp;&nbsp;ID: <?=$resourceid?>
      </td>
    </tr>
    <tr><td>Resource Title</td>
      <td><input type="text" name="resourcetitle" id="resourcetitle" value="<?=$resourcetitle?>" maxlength="300" autocomplete="off" style="width:90%;">
      <a onclick="doinput($('resourcetitle'),'&lsquo;','&rsquo;');" title="click to insert curly quotes">&lsquo;&rsquo;</a></td>
    </tr>

  <?

    if($resourceid>0){ // if we are editing an existing resource

      if($resourcetype<5){ // video and audio, link to external file?>
    <tr><td><span style="color:red">*</span><?=(($resourcetype<3)?'Identifier':'File URL')?></td>
      <td><input type="text" name="identifier" value="<?=$identifier?>" autocomplete="off" style="width:90%;"></td>
    </tr>
    <tr><td><span style="color:red">*</span>Source</td>
      <td>
        <input type="text" name="source" value="<?=$source?>" autocomplete="off" style="width:60%;">
        &nbsp;&nbsp;Duration:
        <input type="text" name="duration" value="<?=$duration?>" autocomplete="off" size="10">
      </td>
    </tr>
    <?}?>
    <?if($resourcetype > 2 && $resourcetype!=7){?>
    <tr><td><span style="color:red">*</span>External URL</td>
      <td><input type="text" name="externalurl" value="<?=$externalurl?>" autocomplete="off" style="width:90%;"></td>
    </tr>
    <?}?>
    <?if($resourcetype==7){ // library?>
    <tr><td><span style="color:red">*</span>New Library file</td>
      <td>
        <input type="file" name="libfilnam" style="width:90%;">
        <input type="hidden" name="libfile" value="<?=$externalurl?>">
      </td>
    </tr>
    <tr><td>Current Library file</td>
      <td><?=((file_exists($docroot.$externalurl))?$externalurl:'<span style="color:red;">Missing! </span>'.$externalurl)?></td>
    </tr>
    <?}?>

    <?$needhiddenthumb = 0; // (($resourcetype>1)?0:1);
      if($resourcetype>1 || 1==1){?>
    <tr><td><span style="color:red">*</span>Thumbnail</td>
      <td><input type="text" name="thumbnail" value="<?=$thumbnail?>" autocomplete="off" style="width:90%;"></td>
    </tr>
    <?

    }

    ?>
    <tr><td style="position:relative;text-align:left;vertical-align:top;">Description
<?if($resourcetype==7 && $resourceid>0){ // library categories?>
      <div style="position:absolute;left:0;right:0;bottom:0;border-top:1px solid <?=$colors[3]?>;background-color:<?=$colors[6]?>;font-size:80%;padding:2px;line-height:1.1em;">
        Library Category(s)<br />
        <?
        $sql = 'select c.catid, c.categoryname from category c where c.catid in (select catid from category_assoc ca where ca.eventid = '.$resourceid.' and ca.catid = c.catid) order by sqn, categoryname ';
        $cat = dbquery($sql);
        $ni=0;
        while($row = mysqli_fetch_array($cat)){
          print(left($row[1], 20).((strlen($row[1])>20)?'..':'').'<input type="checkbox" name="catlib'.$ni.'" value="'.$row[0].'" style="margin-bottom:0;padding-bottom:0;transform:scale(.9);" /><img src="/i/del.png" style="width:10px" /><br />');
          $ni++;
        }
        if($ni==0) print('- none -<br />');
        ?>
        <select name="catlib<?=$ni?>" style="transform:scale(.9);">
          <option value="0">-- none --</option>
          <?
            $sql = 'select c.catid, c.categoryname from category c where cattype = 1 and c.catid not in (select catid from category_assoc ca where ca.eventid = '.$resourceid.' and ca.catid = c.catid) order by sqn, categoryname ';
            $cat = dbquery($sql);
            while($row = mysqli_fetch_array($cat)){
              $tmp = left($row[1], 20).((strlen($row[1])>20)?'..':'');
              print('<option value="'.$row[0].'">'.$tmp.'</option>');
            }
          ?>
        </select>
        <input type="hidden" name="catlibcnt" value="<?=$ni?>">
      </div>
<?}?>
      </td>
      <td><textarea name="description" id="description" style="width:98%;height:120px;"><?=$description?></textarea></td>
    </tr>
    <?if($resourcetype==6){ // book excerpt, not currently used?>
    <tr><td>Book Excerpt</td>
      <td><textarea name="content" id="commentary" style="width:98%;height:120px;"><?=$content?></textarea></td>
    </tr>
    <?}?>
    <tr><td>Date published</td>
      <td><input type="text" name="publishedon" id="publishedon" value="<?=$publishedon?>" size="12" style="cursor:pointer"> <small>&lt;=click</small></td>
    </tr>
    <tr><td>Keywords</td>
      <td><input type="text" name="keywords" value="<?=$keywords?>" autocomplete="off" style="width:90%;"></td>
    </tr>
    <?if($resourcetype!=6){ // videos, audios, and articles can have playlists?>
    <tr><td><span style="color:red">*</span>Playlist</td>
      <td>
        <select name="playlistid">
          <option value="0"<?=fixsel(0, $playlistid);?>>- none -</option>
<?
switch($resourcetype){
case 1: // youtube
case 2: // MP4
  $plwhere = 'and pltypeid=1 ';break;
case 3: // Audio
  $plwhere = 'and pltypeid=3 ';break;
case 4: // Seminar
  $plwhere = 'and pltypeid=5 ';break;
case 5: // article
  $plwhere = 'and pltypeid=4 ';break;
case 7: // library
    $plwhere = 'and pltypeid=7 ';break;
  default:
  $plwhere = '';break;
}

$sql = 'select playlistid, playlisttitle from playlist where 1=1 '.$plwhere.' order by pltypeid, sqn, playlisttitle ';
$ply = dbquery($sql);
while($row = mysqli_fetch_array($ply)){
  print('<option value="'.$row[0].'"'.fixsel($row[0], $playlistid).'>'.$row[1].'</option>');
}
?>
        </select> &nbsp; Sqn
        <input type="text" name="playlistsqn" value="<?=$playlistsqn?>" size="3" autocomplete="off" />
        </td>
    </tr>
    <?} // end of playlists?>
    <tr>
      <td><span style="color:red">*</span>Referenced by: <a onclick="olOpen('/resourceassign.php?resourceid=<?=$resourceid?>',600, 600);" title="assign resource"><img src="/i/assign.png" width="18" alt="assign" /></a></td>
      <td><small>
<?
  $sql = 'select testament, book, chapter, verse
          from resourceassign
          where resourceid = '.$resourceid.'
          order by 1,2,3,4 ';
  $vss = dbquery($sql);
  $nj=0;
  while($rrow = mysqli_fetch_array($vss)){
    print(fixrow($rrow).'<br />');
    $nj++;
  }
  if($nj==0) print('<span style="color:red;">not referenced</span>');
?>
        </small>
      </td>
    </tr>

    <tr><td>Comment</td>
      <td><input type="text" name="editcomment" value="<?=$editcomment?>" autocomplete="off" style="width:90%;"></td>
    </tr>
<?} // end of resourceid>0?>
    <tr>
      <td colspan="2">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <?if($resourceid>0){?>
        <input type="submit" name="btndel" value="Delete" onclick="return valdel(document.frm)">
        <?}?>
        <input type="button" name="btnback" value="Back to Resources" onclick="document.frm.page.value=36;document.frm.submit();">
      </td>
    </tr>
  </table>
<?if($needhiddenthumb) print('<input type="hidden" name="thumbnail" value="'.$thumbnail.'" />');?>
  <input type="hidden" name="filter" value="<?=$filter?>" />
  <input type="hidden" name="sort" value="<?=$sort?>" />
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="resourceid" value="<?=$resourceid?>" />
  <input type="hidden" name="filterrt" value="<?=$filterrt?>">
  <input type="hidden" name="filterpl" value="<?=$filterpl?>">
  <input type="hidden" name="ffinalized" value="<?=$ffinalized?>">
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  <input type="hidden" name="factive" value="<?=$factive?>" />
  <input type="hidden" name="linkedonly" value="<?=$linkedonly?>" />
</form>

<?if($resourcetype>0 && $resourcetype<5){?>
<div style="width:98%;max-width:800px;min-width:480px;margin:0 auto;font-size:80%;">
  <p>Please do not edit the items with red asterisks (<span style="color:red">*</span>), or at least know what you are doing.</p>
  <p>The identifier for Youtube videos is the string of characters between &ldquo;?v=&rdquo; and the next ampersand (&amp;) (if present). It is highlighted in red:<br />
    https://www.youtube.com/watch?v=<span style="color:red">MHVbxHgbGC4</span>&rel=0</p>
    <!--<p>The identifier for MP4 videos is the full URL to the video file. For example:<br />
    <span style="color:red">https://www.stfvideocast.com/itunes/what_speaking_in_tongues_is.mp4</span></p>-->
    <p>The file URL for audios is the full URL to the audio file. For example:<br />
    <span style="color:red">https://mcdn.podbean.com/mf/web/ciqkj9/September_2017_-_What_is_Faith__.mp3</span><br />
    <span style="color:red">https://www.stfpodcast.com/Files/sep2013_jesus_and_nicodemus.mp3</span><br />
    <span style="color:red">https://episodes.castos.com/truthortradition/Ep-16-It-s-Good-to-Leave-an-Inheritance-13.22-.mp3</span></p>
</div>
<?}?>
<script>

  function validate(f){
    if(f.resourcetype.selectedIndex==0){
      alert('You must choose a resource type!');
      return false;
    }
<?if($resourceid>0){?>
    var dt = trim(f.publishedon.value);
    var dateformat = /^((((19|[2-9]\d)\d{2})\-(0[13578]|1[02])\-(0[1-9]|[12]\d|3[01]))|(((19|[2-9]\d)\d{2})\-(0[13456789]|1[012])\-(0[1-9]|[12]\d|30))|(((19|[2-9]\d)\d{2})\-02\-(0[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|(([1][26]|[2468][048]|[3579][26])00))\-02\-29))$/;
    if(!dt.match(dateformat)){
      alert('Invalid date format for \'Date published\'.\n\nMust be \'YYYY-MM-DD\'.');
      f.publishedon.focus();
      f.publishedon.select();
      return false;
    }
<?}
if($resourcetype==7){
?>
    if(!checkFileSize(f.libfilnam)){
      alert('file is too big! 20MB limit.');
      return false;
    }
<?}?>
    f.oper.value = 'savresource';
    return true;
  }

  function valdel(f){
    if(confirm('Are you sure you want to delete this resource?')){
      f.oper.value = 'del';
      f.submit();
    }else{
      return false;
    }

  }
  function setdirt(){} // necessary for function doinput()

  function checkFileSize(inputFile) {
    var max = 20 * 1024 * 1024; // 20MB
    if(inputFile.files && inputFile.files[0] && inputFile.files[0].size > max) {
      inputFile.value = null; // Clear the field.
      return false;
    }
    return true;
  }

<?if($resourceid==0){?>
  setTimeout('document.frm.page.value=36;document.frm.submit();', 1500);
<?}?>

</script>
<!--https://flatpickr.js.org/-->
<script src="/includes/datepicker.js"></script>
<link rel="stylesheet" href="/includes/datepicker.css" />

<?if($resourceid>0){?>
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
  flatpickr(document.frm.publishedon, {});
  </script>
<?if($resourcetype==6) require_once $docroot.'/includes/commentaryeditor.php';?>
<?
} // end of resourceid > 0

function fixrow($r){
  $t = $r[0];
  $b = $r[1];
  $c = $r[2];
  $v = $r[3];
  $ret=getbooktitle($t,$b, 0);
  $href='/'.$ret;
  if($t<2 && $c>0){$ret.=' '.$c.':'.$v;$href.='/'.$c.'/'.$v.'/1';}
  if($t<2 && $c==0){$ret.=' Bk Cmtry';$href='/book'.$href.'/ct';}
  if($t==2){$href='/info/'.$b.'/ct';}
  if($t==3){$href='/appx/'.$b.'/ct';}
  if($t==4){$ret='WordStudy: '.$ret;$href='/word'.$href.'/ct';}
  $ret='<a href="'.$href.'" target="_blank">'.$ret.'</a>';
  return $ret;
}
?>
