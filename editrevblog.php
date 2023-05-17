<?php
if(empty($userid) || $userid==0) die('unauthorized access');

$goback = ((isset($_REQUEST['goback']))?$_REQUEST['goback']:1);

//
// Here, $test is the identifier for blogs in general: 9
// $book holds the blogid
// I SHOULD have made the blogs another testament, like appx's, info's, and ws's
// ..but I didn't
//

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=="saveblog"){
  $msg='';
  $edttitle   = processsqltext($_POST['edttitle'], 50, 0, 'missing blog title!');
  $commentary = processsqlcomm($_POST['commentary'], 0, 'no blog text');
  $active     = processsqlnumb(((isset($_POST['active']))?$_POST['active']:0), 1, 0, 0);
  if($book>-1){
    $sql = 'update revblog set
            blogdate = UTC_TIMESTAMP(),
            blogtitle = '.$edttitle.',
            active = '.$active.',
            blogtext = '.$commentary.'
            where blogid = '.$book.' ';
    $update = dbquery($sql);
  }else{
    $sql = 'insert into revblog (blogdate, blogtitle, active, blogtext) values (
            UTC_TIMESTAMP(),'.$edttitle.','.$active.','.$commentary.') ';
    $insert = dbquery($sql);
    if($sqlerr==''){
      $row = rs('select max(blogid) from revblog ');
      $book = $row[0];
    }

  }
  $msg.= $sqlerr;$sqlerr = '';
  $sqlerr = $msg;
  $logid = logedit($page,9,$book,0,0,$userid,isset($_POST['comment'])?$_POST['comment']:'', 0, null, '<span style="color:red">..not tracking blog differences</span>', null);

  if($sqlerr=='') $sqlerr = datsav;
  if(isset($_POST['chkclearlog'])){
    $clr = dbquery('delete from editlogs where testament = 9 and book = '.$book.' and chapter = 0 and verse = 0 and whatsnew=0 ');
  }
  if(isset($_POST['chkclearlogempty'])){
    $clr = dbquery('delete from editlogs where testament = 9 and book = '.$book.' and chapter = 0 and verse = 0 and (comment is null or comment = \'formatting\') ');
  }
  logview($page,$test,$book,$chap,$vers);
  $goback++;
}
if($oper=='del'){
  $sql = 'delete from revblog where blogid = '.$book.' ';
  $delete = dbquery($sql);
  $sql = 'delete from editlogs where page = 27 and testament = 9 and book = '.$book.' ';
  $delete = dbquery($sql);
  if($sqlerr=='') {
    $book = -1;
    $sqlerr = 'Blog entry deleted';
    $goback++;
  }
}

if($book>-1){
  $row = rs('select blogtitle, active, blogtext
            from revblog
            where blogid = '.$book.' ');
  $edttitle  = $row['blogtitle'];
  $active = $row['active'];
  $commentary= $row['blogtext'];
  $commentary= preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $commentary);
}else{
  $edttitle  = 'New Blog Entry';
  $active = 0;
  $commentary= '';
}
$stitle = 'Editing Blog Entry: '.$edttitle;

?>
<form name="frm" action="/" method="post">

  <span class="pageheader"><?=$stitle?></span>
  <table style="width:96%;font-size:90%;border-collapse:separate;border-spacing:5px;">
    <tr><td>&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td>Blog Title <small>(plain text only)</small><br />
        <input type="text" name="edttitle" size="60" value="<?=$edttitle?>" onchange="setdirt();">
      </td></tr>
    <tr>
      <td>
        Published
        <input type="checkbox" name="active" value="1"<?=fixchk($active)?> onclick="setdirt();">
      </td></tr>
    <tr>
      <td>Blog text
        <textarea name="commentary"><?=$commentary?></textarea>
        <button onclick="history.go(-<?=$goback?>);return false;" style="text-align:center;font-size:80%">Back</button>
        <input type="submit" name="xc" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />
        <?if($book>-1){?>
        Check this box to delete this blog entry
        <input type="checkbox" name="chkdel" value="1">
        <?}else{?>
        <input type="hidden" name="chkdel" value="1">
        <?}?>
      </td></tr>
  </table>
  <small>Comment <input type="text" id="txtcomment" name="comment" value="" size="60" maxlength="200" style="margin-top:2px">
  <a onclick="doinput($('txtcomment'),'&ldquo;','&rdquo;');" title="click to insert smart quotes">&ldquo;&rdquo;</a></small>

<?
if($book > 0){
  print(displayedits($page,9,$book,0,0));
  if($superman){
    print('<input type="checkbox" name="chkclearlog" value="1" onclick="setdirt()"><small>check to clear edit log.</small><br />');
    print('<input type="checkbox" name="chkclearlogempty" value="1" onclick="setdirt()"><small>check to clear empty logs.</small><br />');
  }
}
?>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="goback" value="<?=$goback?>" />
  </form>
  <script>
    var checkforchanges = true;

    function validate(f){
      if(f.chkdel.checked){
        if (confirm('Are you sure you want to delete this blog entry?')) {
          f.oper.value = 'del';
        }
      }else{
        checkforchanges = false;
<?if($superman==1 && $book>0){?>
        if(f.chkclearlog.checked){
          if(!confirm('Are you sure you want to delete ALL the edit logs?')) return false;
        }
<?}?>
        f.oper.value = "saveblog";
      }
      return true;
    }
    function checkdirt(){
      if(!checkforchanges) return;
      var f = document.frm;
      var dirty = 0;
      if(f.dirt.value==1) dirty = 1;
      for (var i in CKEDITOR.instances) {
        if(CKEDITOR.instances[i].checkDirty()) dirty = 1;
      }
      if(dirty == 1)return 'You have unsaved changes.\nIf you continue those changes will be LOST.';
      else return;
    }

    function setdirt(){
      var f = document.frm;
      f.dirt.value = 1;
    }

    window.onbeforeunload = checkdirt;
    //
    // Used if an editor is viewing the page
    // They can manage their flags
    //
    function handleflag(logid, ni, donone){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var don = ret.donone;
          var flg = ret.flagged;
          //alert('don: '+don);
          //alert('flg: '+flg);
          if(don==1){
            $('td'+ni).style.backgroundColor=colors[6];
          }else{
            if($('td'+ni).innerHTML.includes('None'))
              $('td'+ni).style.backgroundColor=colors[6];
            var td = $('eflag'+ni);
            var tdc= ((flg==0)?'transparent':'#ffdddd');
            td.style.backgroundColor=tdc;
            var fg = $('iflag'+ni);
            var sfg= ((flg==0)?'30':'100');
            fg.style.opacity=sfg+'%';
          }
        }
      }
      var qs = '?id='+logid+'&doocmt=0&donone='+donone;
      //alert(qs);
      xmlhttp.open('GET','/jsonflagedit.php'+qs,true);
      xmlhttp.send();
    }
  </script>
  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
  <?require_once $docroot.'/includes/commentaryeditor.php';?>

