<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

$finishyear = 2000;

if(empty($userid) || $userid==0 || $chronedit==0) {print('<h3>unauthorized access</h3>');return;}
$beginyear=((isset($_REQUEST['beginyear']))?$_REQUEST['beginyear']:0);
if($beginyear==0) exit('missing beginyear!!');
$id=((isset($_REQUEST['id']))?$_REQUEST['id']:-1);
//print('id: '.$id.'<br />');

$stitle = (($id>0)?'Edit':'New').' Chronology Event for: '.friendlyyear($beginyear);

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";

$reloadparent=0;

if($oper=='savchron'){

  if($id>-1){
    // grab original eventtitle, longdesc
    $row = rs('select eventtitle, longdesc
              from chronevent
              where id = '.$id.' ');
    $beforetitl = $row[0];
    $beforetitl = processsqlcomm($beforetitl, 1, '-');
    $beforetitl = substr($beforetitl, 1, strlen($beforetitl)-2);

    $beforedesc = $row[1];
    $beforedesc = processsqlcomm($beforedesc, 1, '-');
    $beforedesc = replacgreekhtml($beforedesc);
    if($beforedesc!='null') $beforedesc = substr($beforedesc, 1, strlen($beforedesc)-2);
    if($beforedesc === '-' || $beforedesc=='null') $beforedesc = '';
  }else{
    $beforetitl = '';
    $beforedesc = '';
  }

  $sqn       = processsqlnumb(((isset($_POST['sqn']))?$_POST['sqn']:1), 99, 0, 1);
  $onebased  = processsqlnumb(((isset($_POST['onebased']))?$_POST['onebased']:0), 1, 0, 0);
  $beginyear = processsqlnumb(((isset($_POST['beginyear']))?fixbeginyear($_POST['beginyear'], 0):-3961), $finishyear, 0, -3961);
  $endyear   = processsqlnumb(((isset($_POST['endyear']))?fixendyear($_POST['endyear'], $onebased):$beginyear), $finishyear, 0, $beginyear);
  $eventtitle= processsqlcomm(((isset($_POST['eventtitle']))?left($_POST['eventtitle'], 700):''), 0, 'missing title');
  $colorindex= processsqlnumb(((isset($_POST['colorindex']))?$_POST['colorindex']:1), 12, 0, '1');
  $tooltip   = processsqltext(((isset($_POST['tooltip']))?left($_POST['tooltip'], 300):''), 300, 1, '');
  $picfilnam = processsqltext(((isset($_POST['hidpicfilnam']))?left($_POST['hidpicfilnam'], 200):''), 200, 1, '');
  $piccaption= processsqltext(((isset($_POST['piccaption']))?left($_POST['piccaption'], 300):''), 300, 1, '');
  $location  = processsqltext(((isset($_POST['location']))?left($_POST['location'], 20):''), 20, 1, '');
  $longdesc  = processsqlcomm(((isset($_POST['longdesc']))?$_POST['longdesc']:''), 1, '');
  if(isset($_FILES["picfilnam"]["name"]) && $_FILES["picfilnam"]["name"] !== ''){
    $target_dir = $docroot.'/i/chronologyimages/';
    $target_file = $target_dir.$id.'_'.strtolower(basename($_FILES["picfilnam"]["name"]));
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // should ck for file size
    if($imageFileType=='jpg' || $imageFileType=='png' || $imageFileType=='jpeg' || $imageFileType=='gif') {
      if (!move_uploaded_file($_FILES["picfilnam"]["tmp_name"], $target_file)) {
        $sqlerr = 'Sorry, there was an error uploading your file.';
      }else $picfilnam = processsqltext($id.'_'.strtolower(basename($_FILES["picfilnam"]["name"])), 200, 1, '');
    } else $sqlerr = 'Sorry, only jpg, png, jpeg, and gif files are allowed for the event picture.';
  }
  if((isset($_POST['delpic']) && $_POST['delpic']==1)){
    unlink($docroot.'/i/chronologyimages/'.str_replace('\'', '', $picfilnam));
    $picfilnam = processsqltext('', 200, 1, '');
    $piccaption= processsqltext('', 200, 1, '');
  }

  $aftertitl = substr($eventtitle, 1, strlen($eventtitle)-2);
  if($beforetitl===$aftertitl) $titldiff = null;
  else $titldiff = htmlDiff($beforetitl, $aftertitl);

  $afterdesc = $longdesc;
  if($afterdesc!= 'null') $afterdesc = substr($afterdesc, 1, strlen($afterdesc)-2);
  if($afterdesc === '-' || $afterdesc=='null') $afterdesc = '';
  if($beforedesc===$afterdesc) $descdiff = null;
  else $descdiff = htmlDiff($beforedesc, $afterdesc);
  $descdiff = str_replace('\\', '\\\\', $descdiff??'');
  $descdiff = str_replace('\'', '\\\'', $descdiff??'');

  if($id>0){
    $sql = 'update chronevent set
            sqn       = '.$sqn.',
            onebased  = '.$onebased.',
            beginyear = '.$beginyear.',
            endyear   = '.$endyear.',
            eventtitle= '.$eventtitle.',
            colorindex= '.$colorindex.',
            tooltip   = '.$tooltip.',
            picfilnam = '.$picfilnam.',
            piccaption= '.$piccaption.',
            location  = '.$location.',
            lastedituserid='.$userid.',
            lastedited= utc_timestamp(),
            longdesc  = '.$longdesc.'
            where id  = '.$id.' ';
    //print($sql);
    $update = dbquery($sql);

    // handle event categories
    $catevtcnt = $_POST['catevtcnt'];
    for($ni=0;$ni<=$catevtcnt;$ni++){
      if($ni<$catevtcnt){
        if(isset($_POST['catevt'.$ni]))
          $del = dbquery('delete from category_assoc where eventid = '.$id.' and catid = '.$_POST['catevt'.$ni].' ');
      }else{
        if($_POST['catevt'.$catevtcnt]>0)
          $ins = dbquery('insert into category_assoc (eventid, catid) values ('.$id.', '.$_POST['catevt'.$catevtcnt].') ');
      }

    }
  }else{
    $sql = 'insert into chronevent (sqn, onebased, beginyear, endyear, eventtitle, colorindex, tooltip, picfilnam, piccaption, location, lastedituserid, lastedited, longdesc) values ('.
           $sqn.', '.$onebased.', '.$beginyear.', '.$endyear.', '.$eventtitle.', '.$colorindex.', '.$tooltip.', '.$picfilnam.', '.$piccaption.', '.$location.', '.$userid.', utc_timestamp(), '.$longdesc.') ';
    //print($sql);
    $insert = dbquery($sql);
    $row = rs('select max(id) from chronevent ');
    $id = $row[0];
  }

  $logid = logedit(34,0,0,0,0,$userid,$beginyear, 0, str_replace('\'', '\\\'', $titldiff??''), $descdiff, '', '');

  if($sqlerr=='') $sqlerr = datsav;
  $reloadparent=1;
}

if($id!=-1){
  $sql = 'select ce.sqn, ce.onebased, ce.beginyear, ce.endyear, ce.eventtitle, ce.colorindex, ce.tooltip, ce.piccaption,
            ce.picfilnam, ce.location, ifnull(ifnull(mru.revusername, mru.myrevname), \'unknown\') username, ce.lastedited, ce.longdesc
          from chronevent ce
          left join myrevusers mru on (ce.lastedituserid = mru.userid and mru.myrevid > 0)
          where ce.id = '.$id.' ';
  $row = rs($sql);
  $sqn       = $row['sqn'];
  $onebased  = $row['onebased'];
  $beginyear = $row['beginyear'];
  $endyear   = $row['endyear'];
  $eventtitle= $row['eventtitle'];
  $colorindex= $row['colorindex'];
  $tooltip   = $row['tooltip'];
  $picfilnam = $row['picfilnam'];
  $piccaption= $row['piccaption'];
  $location  = $row['location'];
  $lasteditor= $row['username'];
  $lastedited= converttouserdate($row['lastedited'], $timezone);
  $longdesc  = $row['longdesc'];
}else{
  $sqn       = 1;
  $onebased  = 0;
  $beginyear = $beginyear;
  $endyear   = '';
  $eventtitle= 'New event';
  $colorindex= '#fee';
  $tooltip   = '';
  $picfilnam = '';
  $piccaption= '';
  $location  = '';
  $lasteditor= '-';
  $lastedited= '-';
  $longdesc  = '';
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>edit event</title>
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center"><?=$stitle?></h2>
<div style="margin:0 auto;text-align:center">
<form name="frm" method="post" enctype="multipart/form-data" action="/chronologyedit.php">

  <table class="gridtable" style="width:90%;min-width:480px;">
    <tr><td colspan="2" style="text-align:left;">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td colspan="2" style="text-align:left;">
      Sqn       <input type="text" name="sqn" style="width:1em;" value="<?=$sqn?>">&nbsp;&nbsp;
      One-based <input type="checkbox" name="onebased" value="1"<?=fixchk($onebased)?>>&nbsp;&nbsp;
      Color
      <select name="colorindex" style="background-color:<?=$eventcolors[$colorindex]?>" onchange="this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor;">
      <?
        for($ni=1;$ni<sizeof($eventcolors);$ni++){
          print('<option value="'.$ni.'" style="background-color:'.$eventcolors[$ni].'"'.fixsel($ni, $colorindex).'>'.$eventcolors[$ni].'</option>');
        }
      ?>
      </select>
      </td>
    </tr>
    <tr><td style="text-align:left;">Beg/End Year</td>
      <td style="text-align:left;">
      <input type="text" name="beginyear" size="6" value="<?=friendlyyear($beginyear)?>" maxlength="12" autocomplete="off">
      &nbsp;/&nbsp;
      <input type="text" name="endyear" size="6" value="<?=friendlyyear($endyear)?>" maxlength="12" autocomplete="off"> <small>(xxx bc, xxx ad, or xxx for duration)</small>
      </td>
    </tr>
    <tr><td style="position:relative;text-align:left;vertical-align:top;">Event
      <?if($id>0){?>
      <div style="position:absolute;left:0;right:0;bottom:0;border-top:1px solid <?=$colors[3]?>;background-color:<?=$colors[6]?>;font-size:80%;padding:2px;line-height:1.1em;">
        Category(s)<br />
        <?
        $sql = 'select c.catid, c.categoryname from category c where c.catid in (select catid from category_assoc ca where ca.eventid = '.$id.' and ca.catid = c.catid) order by sqn, categoryname ';
        $cat = dbquery($sql);
        $ni=0;
        while($row = mysqli_fetch_array($cat)){
          print(left($row[1], 14).((strlen($row[1])>14)?'..':'').'<input type="checkbox" name="catevt'.$ni.'" value="'.$row[0].'" style="margin-bottom:0;padding-bottom:0;transform:scale(.9);" /><img src="/i/del.png" style="width:10px" /><br />');
          $ni++;
        }
        if($ni==0) print('- none -<br />');
        ?>
        <input type="hidden" name="catevtcnt" value="<?=$ni?>">
        <select name="catevt<?=$ni?>" style="transform:scale(.9);">
          <option value="0">-- none --</option>
          <?
            $sql = 'select c.catid, c.categoryname from category c where cattype = 0 and c.catid not in (select catid from category_assoc ca where ca.eventid = '.$id.' and ca.catid = c.catid) order by sqn, categoryname ';
            $cat = dbquery($sql);
            while($row = mysqli_fetch_array($cat)){
              print('<option value="'.$row[0].'">'.$row[1].'</option>');
            }
          ?>
        </select>
      </div>
      <?}?>
      </td>
      <td><textarea name="eventtitle" id="eventtitle" style="width:98%;height:70px;"><?=$eventtitle?></textarea></td>
    </tr>
    <tr><td style="text-align:left;">Tooltip</td>
      <td style="text-align:left;"><input type="text" name="tooltip" size="50" value="<?=$tooltip?>" autocomplete="off"> <span style="font-size:74%;">use ~~~ to count up, --- to count down, !!! for x<sup>th</sup></span></td>
    </tr>
<?if($id>0){?>
    <tr><td style="text-align:left;">Picture</td>
      <td style="text-align:left;">
<?
  if($picfilnam!='' && file_exists($docroot.'/i/chronologyimages/'.$picfilnam))
    print('Del <input type="checkbox" name="delpic" value="1">&nbsp;&nbsp;&nbsp;<a href="/i/chronologyimages/'.$picfilnam.'" target="_blank">View</a>');
  else{
    print('<input type="file" name="picfilnam" />');
    print(' <span style="color:red;"><small>(must be under 2MB)</small></span>');
  }
?>
      <input type="hidden" name="hidpicfilnam" value="<?=$picfilnam?>">
      </td>
    </tr>
    <tr><td style="text-align:left;">Pic Caption</td>
      <td style="text-align:left;"><input type="text" name="piccaption" size="50" value="<?=$piccaption?>" autocomplete="off"></td>
    </tr>
<?}?>
    <!--
    <tr><td style="text-align:left;">Location</td>
      <td style="text-align:left;"><input type="text" name="location" size="20" value="<?=$location?>"><small> ..not sure if/how this will be used</small></td>
    </tr>
    -->
    <tr>
      <td colspan="2" style="text-align:left;padding-bottom:30px;">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <?if($id>0){?>
        <input type="button" name="btndelete" value="Delete" style="background-color:#fdd;border:2px solid #090;" onclick="return valdel(document.frm);">
        <?}?>
        <input type="reset" name="btnreset" value="Reset">
        <input type="button" name="btnback" value="Close" onclick="olClose(<?=$reloadparent?>);">
        Last edited by <?=$lasteditor?> on <?=$lastedited?>.
      </td>
    </tr>
    <tr><td colspan="2" style="text-align:left;">Long Description (optional, might be best to copy/paste from MS Word.)<br />
      <textarea name="longdesc" id="longdesc" style="width:98%;height:270px;"><?=$longdesc?></textarea></td>
    </tr>
  </table>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="id" value="<?=$id?>" />
  <input type="hidden" name="oper" value="" />
</form></div>

<script>

  function validate(f){
    f.oper.value = 'savchron';
    return true;
  }

  function valdel(f){
    if(confirm('Are you sure you want to delete this event?')){
      f.oper.value = 'delchron';
      f.submit();
    }
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

</script>
<script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
<script>
  CKEDITOR.replace( 'eventtitle',
  {
    toolbar :
    [
      { name: 'document', items : [ 'Source','AutoCorrect'] },
      { name: 'basicstyles', items : [ 'Bold','-','Italic','smallcapify' ] },
      { name: 'tools', items : ['SpecialChar' ] },
      { name: 'links', items : ['Link','Unlink']},
    ],
    height : '70',
    enterMode : CKEDITOR.ENTER_BR,
    startupFocus : 'end'
    //shiftEnterMode : CKEDITOR.ENTER_P
  }
  );
  CKEDITOR.replace( 'longdesc',
  {
    entities_greek: false,
    toolbarCanCollapse: false,
    toolbar :
    [
    { name: 'document',   items : ['Source','AutoCorrect']},
    { name: 'clipboard',  items : ['PasteFromWord','-','Undo','Redo']},
    { name: 'tools',      items : ['Maximize','Symbol','RTL','InsertWhatsNewMarker','PasteFootnote']},
    { name: 'basicstyles',items : ['Bold','Italic','Underline','Strike','Superscript','smallcapify','RemoveFormat']},
    { name: 'paragraph',  items : ['Blockquote','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'lists',      items : ['NumberedList','BulletedList']},
    { name: 'styles',     items : ['Format']},
    { name: 'links',      items : ['Link','Unlink','-','PasteTOC','PasteTOCD']},
    ],
    extraAllowedContent: 'noparse',
    width : '100%',
    height : '270'
  }
  );

<?
if($oper=='delchron'){
  //print('picfilnam: '.$picfilnam.'<br />');
  if($picfilnam != '')
    unlink($docroot.'/i/chronologyimages/'.str_replace('\'', '', $picfilnam));
  $delete = dbquery('delete from category_assoc where eventid = '.$id.' ');
  $delete = dbquery('delete from chronevent where id = '.$id.' ');
  ?>
  setTimeout('olClose(1)', 300);
  <?}?>
</script>
</body>
</html>
<?
function friendlyyear($yr){
 if(trim($yr)=='') return '';
 if($yr<0) $ret = abs($yr).' BC';
 else $ret = abs((int) $yr).' AD';
 return $ret;
}

function fixendyear($yr, $ob){
  global $beginyear, $finishyear;
  $yr = (($yr=='')?0:$yr);
  $ret = '';
  $yr = preg_replace('/\W/', '', $yr);
  if(strtolower(right($yr, 2))=='bc' || strtolower(right($yr, 1))=='b'){
    // handle BC input
    $ret = (int)-preg_replace('/\D/', '', $yr);
  }else if(strtolower(right($yr, 2))=='ad' || strtolower(right($yr, 1))=='a'){
    // handle AD input
    $ret = (int) preg_replace('/\D/', '', $yr);
  }else{
    // add to beginyear
    $ret = $beginyear + (int) preg_replace('/\D/', '', $yr)-$ob;
  }
  if($ret<$beginyear) $ret = $beginyear;
  if($ret>($finishyear-1)) $ret = ($finishyear-1);
  return $ret;
}
function fixbeginyear($yr){
  global $finishyear;
  $yr = (($yr=='')?-3961:$yr);
  $ret = '';
  $yr = preg_replace('/\W/', '', $yr);
  if(strtolower(right($yr, 2))=='bc' || strtolower(right($yr, 1))=='b'){
    // handle BC input
    $ret = (int)-preg_replace('/\D/', '', $yr);
  }else if(strtolower(right($yr, 2))=='ad' || strtolower(right($yr, 1))=='a'){
    // handle AD input
    $ret = (int) preg_replace('/\D/', '', $yr);
  }else{
    // assume BC
    $ret = (int)-preg_replace('/\D/', '', $yr);
  }
  if($ret<-3962) $ret = -3962;
  if($ret>($finishyear-1)) $ret = ($finishyear-1);
  return $ret;
}
?>

