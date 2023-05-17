<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $appxedit==0) {print('<h3>unauthorized access</h3>');return;}
$bibtype = (isset($_REQUEST['bibtype']))?$_REQUEST['bibtype']:-1;
$bibtype = (int) $bibtype;
if($bibtype!=0 && $bibtype!=1){
  print('bibtype: '.$bibtype);
  die('badbib');
}
$bibid = (isset($_REQUEST['bibid']))?$_REQUEST['bibid']:-1;
$fullform=0; // are we using everything?

if(1==2){
  print('bibtype: '.$bibtype.'<br />');
  print('bibid: '.$bibid.'<br />');
}

$stitle = 'Edit '.(($bibtype==0)?'Abbreviation':'Bibliography').' Entry';

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr = '';
$addnew=0;

$reloadparent=0;

if($oper=='delbib'){
  $row = rs('select bibauthor, ifnull(bibkey, \'\') bibkey, ifnull(bibentry, \'\') bibentry from bibliography where bibid = '.$bibid.' ');
  if($row){
    $authdiff = '<del>'.$row[0].'</del>';
    $bkeydiff = '<del>'.$row[1].'</del>';
    $entrydiff= '<del>'.$row[2].'</del>';
  }else{
    $authdiff = 'unknown!';
    $bkeydiff = '';
    $entrydiff= '';
  }
  $logid = logedit(51,$test,$bibid,$bibtype,0,$userid,'deleted: '.$authdiff, 0, $entrydiff, '', $bkeydiff, $authdiff);
  $qry = dbquery('delete from bibliography where bibid = '.$bibid.' ');
  print('<script>parent.location.reload();</script>');
  die();
}
if($oper=='savbib'){
  $bibkey = processsqlcomm(((isset($_POST['bibkey']))?$_POST['bibkey']:''), 1, '');
  $bibflagged = processsqlnumb(((isset($_POST['flagged']))?$_POST['flagged']:0), 1, 1, 0);
  $bibauthor = processsqltext($_POST['bibauthor'], 50, 0, 'missing author!');
  $bibentry = processsqlcomm($_POST['bibentry'], 0, 'missing main entry!');
  $bibcomment = processsqlcomm(((isset($_POST['bibcomment']))?$_POST['bibcomment']:''), 1, '');
  if(strlen($bibkey)>119) $bibkey = substr($bibkey, 0, 119).'\'';
  if(strlen($bibentry)>1990) $bibentry = substr($bibentry, 0, 1990).'\'';
  if(strlen($bibcomment)>1990) $bibcomment = substr($bibcomment, 0, 1990).'\'';

  if($bibid==-1){
    $beforeauth='';
    $beforebkey='';
    $beforeentry='';
    $upd = dbquery('insert into bibliography(bibtype, bibkey, flagged, bibauthor, bibentry, bibcomment) values('.$bibtype.','.$bibkey.','.$bibflagged.','.$bibauthor.','.$bibentry.','.$bibcomment.')');
    $row = rs('select max(bibid) from bibliography ');
    $bibid = $row[0];
    //$addnew=1;
  }else{
    $row = rs('select ifnull(bibkey, \'\') bibkey, bibauthor, bibentry from bibliography where bibid = '.$bibid.' ');
    $beforebkey = $row[0];
    $beforebkey = processsqltext($beforebkey, 20, 0, '');
    $beforebkey = substr($beforebkey, 1, strlen($beforebkey)-2);

    $beforeauth = $row[1];
    $beforeauth = processsqltext($beforeauth, 50, 0, '-');
    $beforeauth = substr($beforeauth, 1, strlen($beforeauth)-2);

    $beforeentry = $row[2];
    $beforeentry = processsqlcomm($beforeentry, 1, 'no commentary');
    $beforeentry = substr($beforeentry, 1, strlen($beforeentry)-2);
    $upd = dbquery('update bibliography
                    set
                    bibkey = '.$bibkey.',
                    flagged = '.$bibflagged.',
                    bibauthor = '.$bibauthor.',
                    bibentry = '.$bibentry.',
                    bibcomment = '.$bibcomment.'
                    where bibid = '.$bibid.' ');

  }
  if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
  $reloadparent=1;

  $afterbkey = (($bibkey!=='null')?substr($bibkey, 1, strlen($bibkey)-2):'');
  if($beforebkey===$afterbkey) $bkeydiff = '';
  else $bkeydiff = htmlDiff($beforebkey, $afterbkey);

  $afterauth = substr($bibauthor, 1, strlen($bibauthor)-2);
  if($beforeauth===$afterauth) $authdiff = '';
  else $authdiff = htmlDiff($beforeauth, $afterauth);

  $afterentry = $bibentry;
  $afterentry = substr($afterentry, 1, strlen($afterentry)-2);
  if($beforeentry===$afterentry) $entrydiff = '';
  else $entrydiff = htmlDiff($beforeentry, $afterentry);
  $entrydiff = str_replace('\\', '\\\\', $entrydiff);
  $entrydiff = str_replace('\'', '\\\'', $entrydiff);

  //print('bibkey: '.$bibkey.'<br />');
  //print('bkeydiff: '.$bkeydiff.'<br />');
  //print('authdiff: '.$authdiff.'<br />');
  //print('entrydiff: '.$entrydiff.'<br />');

  if($bkeydiff!='' || $authdiff!='' || $entrydiff!=''){
    $bibeditcomment = trim((isset($_POST['bibeditcomment']))?$_POST['bibeditcomment']:'');
    if($bibeditcomment=='') $bibeditcomment ='See Chngs';
    // arbitrarily chose 51
    $logid = logedit(51,$test,$bibid,$bibtype,0,$userid,$bibeditcomment, 0, $entrydiff, '', $bkeydiff, $authdiff);
  }
}

$disablesubmit=0;
if($bibid==-1){
  $bibauthor = '';
  $bibkey = '';
  $bibflagged = 0;
  $bibentry = '';
  $bibcomment = '';
}else{
  $sql = 'select bibtype, bibauthor, bibkey, flagged, bibentry, bibcomment from bibliography where bibid = '.$bibid.' ';
  $row= rs($sql);
  if($row){
    $bibauthor = $row['bibauthor'];
    $bibkey = $row['bibkey'];
    $bibflagged = $row['flagged'];
    $bibentry = $row['bibentry'];
    $bibcomment = $row['bibcomment'];
  }else{
    $sqlerr = '<span style="color:red;font.weight:bold;">The entry with Id: '.$bibid.' has been deleted. Close this editor screen.</span>';
    $bibauthor = '';
    $bibkey = '';
    $bibflagged = 0;
    $bibentry = '';
    $bibcomment = '';
    $bibid=-1;
    $disablesubmit=1;
  }
}


?>
<!DOCTYPE html>
<html>
<head>
  <title>edit bib/abbr</title>
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center"><?=$stitle?></h2>
<div style="margin:0 auto;text-align:center">
<form name="frm" method="post" action="/bibedit.php">
<?=$sqlerr?>
  <table class="gridtable" style="width:90%;max-width:800px;min-width:340px;font-size:90%;">
    <tr><td style="text-align:left;font-weight:bold;">Author:title <small>(for sorting)</small> <input type="text" name="bibauthor" id="bibauthor" value="<?=$bibauthor?>" autocomplete="off" onchange="dodirt();" /> <small>Only [a-z], [0-9], :</small></td></tr>
    <tr><td style="text-align:left;"><strong>Main Entry <small>(max 2000 characters)</small></strong>&nbsp;&nbsp;&nbsp; <small>Flagged</small> <input type="checkbox" name="flagged" value="1"<?=fixchk($bibflagged)?>><br /><textarea name="bibentry" id="bibentry"><?=$bibentry?></textarea></td></tr>
    <?if($fullform==1){?>
    <?if($bibtype==1){?>
    <tr><td style="text-align:left;">Short name <small>(to replace bibkey, max 120 characters, not necessary for now..)</small><br />
      <textarea name="bibkey" id="bibkey"><?=$bibkey?></textarea></td></tr>
    <?}?>
    <tr><td style="text-align:left;">Comment. <small>(max 2000 characters)</small><br /><textarea name="bibcomment" id="bibcomment"><?=$bibcomment?></textarea></td></tr>
    <?}?>
    <tr><td style="text-align:left;"><small>Edit Comment:</small><input type="text" name="bibeditcomment" id="bibeditcomment" value="" autocomplete="off" style="width:80%;" maxlength="200" /></td></tr>

    <tr>
      <td style="text-align:left;">
        <?if($bibid>-1){?>
        <input type="button" name="btndel" value="Delete" style="background-color:#fdd;border:2px solid #090;" onclick="return valdel(document.frm);">
        <?}
        if($addnew==1){?>
        <input type="button" name="btnnew" value="Another New Entry" style="background-color:#fdd;border:2px solid #090;" onclick="addnew(document.frm);">
        <?}
        if($disablesubmit==0){?>
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <?}?>
        <input type="button" name="btnback" value="Close" onclick="olClose(<?=$reloadparent?>);">
      </td>
    </tr>
  </table>
<input type="hidden" name="bibid" value="<?=$bibid?>">
<input type="hidden" name="bibtype" value="<?=$bibtype?>">
<input type="hidden" name="dirty" value="0">
<input type="hidden" name="oper" value="">
</form>
</div>
<script>

  function dodirt(){
    document.frm.dirty.value=1;
  }

  function addnew(f){
    if(!checkdirt()) return;
    f.bibid.value=-1;
    f.submit();
  }

  function validate(f){
    var msg = '', ctl='';
    f.bibauthor.value = trim(f.bibauthor.value);
    f.bibauthor.value = f.bibauthor.value.toLowerCase();
    //f.bibauthor.value = f.bibauthor.value.replace(/\s+/g, '');
    f.bibauthor.value = f.bibauthor.value.replace(/[^a-z0-9:]+/g, '');
    if(f.bibauthor.value==''){
      msg = 'Missing author!';
      ctl = f.bibauthor;
    }
    if(msg!=''){
      alert(msg);
      ctl.focus();
      return false;
    }
    f.oper.value='savbib';
    return true;
  }

  function valdel(f){
    if(confirm('\nAre you sure you want to delete this bib entry?\n\nIt cannot be undone.\n')){
      try{deleteall(CKEDITOR.instances.bibkey);}catch(e){}
      deleteall(CKEDITOR.instances.bibentry);
      try{deleteall(CKEDITOR.instances.bibcomment)}catch(e){};
      f.oper.value = "delbib";
      f.submit();
      return true;
    }else return false;
  }

  function deleteall(ckei){
    ckei.setData('', function(){ckei.resetDirty();});
  }

  function $(el) {return parent.document.getElementById(el);}

  function olClose(locn) {
    if(!checkdirt()) return;
    var ol = $("overlay");
    ol.style.display = 'none';
    if(locn==1) parent.document.location.reload();
    setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
  }

  <?if($fullform==1 && $bibtype==1){?>
  CKEDITOR.replace( 'bibkey',
  {
    //forcePasteAsPlainText: 'allow-word',
    entities_greek: false,
    toolbarCanCollapse: false,
    extraPlugins: 'colorbutton,button,panelbutton,panel,floatpanel,autocorrect,pastelongdash',
    toolbar :
    [
      { name: 'document', items : [ <?=(($userid==1)?'\'Source\', ':'')?>'AutoCorrect'] },
      { name: 'clipboard', items : [ 'Undo','Redo' ] },
      { name: 'tools', items : [ 'Maximize','SpecialChar','PasteLongdash' ] },
      { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Superscript','RemoveFormat' ] }
    ],
    height : '60',
    enterMode : CKEDITOR.ENTER_BR
  }
  );
  <?}?>

  CKEDITOR.replace( 'bibentry',
  {
    //forcePasteAsPlainText: 'allow-word',
    entities_greek: false,
    toolbarCanCollapse: false,
    extraPlugins: 'colorbutton,button,panelbutton,panel,floatpanel,autocorrect,pastelongdash',
    toolbar :
    [
      { name: 'document', items : [ <?=(($userid==1)?'\'Source\', ':'')?>'AutoCorrect'] },
      { name: 'clipboard', items : [ 'Undo','Redo' ] },
      { name: 'tools', items : [ 'Maximize','SpecialChar','PasteLongdash' ] },
      { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Superscript','RemoveFormat' ] }
    ],
    height : '120',
    enterMode : CKEDITOR.ENTER_BR
  }
  );

  <?if($fullform==1){?>
  CKEDITOR.replace( 'bibcomment',
  {
    extraPlugins: 'panel,autocorrect',
    toolbar :
    [
      { name: 'document', items : [ <?=(($userid==1)?'\'Source\', ':'')?>'AutoCorrect'] },
      { name: 'clipboard', items : [ 'Undo','Redo' ] },
      //{ name: 'tools', items : [ 'Maximize','SpecialChar' ] },
      { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Superscript','RemoveFormat' ] }
    ],
    height : '60',
    enterMode : CKEDITOR.ENTER_BR
  }
  );
  <?}?>

  function checkdirt(){
    var dirty = document.frm.dirty.value;
    try{if(CKEDITOR.instances.bibkey.checkDirty()) dirty = 1;}catch(e){}
    try{if(CKEDITOR.instances.bibentry.checkDirty()) dirty = 1;}catch(e){}
    try{if(CKEDITOR.instances.bibcomment.checkDirty()) dirty = 1;}catch(e){}
    if(dirty==1){
      if(confirm('\nYou have unsaved changes!\n\nDo you want to close anyway?\nTo save the changes you made, click "Cancel", then "Submit"\n\nIf you do not want to save the changes, click "OK".'))
        return true;
      else return false;
    }else return true;
  }

  parent.goback++;
</script>
</body>
</html>
<?if($oper=='nada') logview(211,0,0,$bibtype,0);
?>

