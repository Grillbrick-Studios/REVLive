<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || $resedit==0) {print('<h3>unauthorized access</h3>');return;}
$topicid = ((isset($_REQUEST['topicid']))?$_REQUEST['topicid']:-1);

$oper = ((isset($_POST['oper']))?$_POST['oper']:'nada');

$msg = "";
$sqlerr='';
$edited=0;
if($oper=='deltopic'){
  $sql = 'delete from topic_assoc where topicid = '.$topicid.' ';
  $delete = dbquery($sql);
  $sql = 'delete from topic where topicid = '.$topicid.' ';
  $delete = dbquery($sql);
  ?>
    <script>
      setTimeout('olClose("/topic");', 500);
    </script>
  <?
  $topicid=0;
  if($sqlerr=='') $sqlerr = 'Deleted..';
}
if($oper=='savtopic'){
  $topic = processsqltext($_POST['topic'],  50, 0, 'missing!');
  $sqn = processsqlnumb(((isset($_POST['sqn']))?$_POST['sqn']:0),  999, 0, 0);
  $description   = processsqlcomm(((isset($_POST['description']))?left($_POST['description'], 1900):''), 1, '');
  $comment = processsqltext($_POST['comment'], 1000, 1, '');
  if($topicid==-1){ // new
    $sql = 'insert into topic(topic, sqn, comment, description) values
           ('.$topic. ','.$sqn.', '.$comment.', '.$description.') ';
    $update = dbquery($sql);
    $sql = 'select max(topicid) from topic ';
    $row = rs($sql);
    $topicid = $row[0];
  }else{
    $sql = 'update topic set
            topic = '.$topic.',
            sqn = '.$sqn.',
            comment = '.$comment.',
            description = '.$description.'
            where topicid = '.$topicid.' ';
    //print($sql);
    $update = dbquery($sql);
  }
  $edited=1;
  if($sqlerr=='') $sqlerr = datsav;

  $mcnt = $_POST['mcnt'];
  for($ni=0;$ni<$mcnt;$ni++){
    $parts = explode(',', $_POST['tassoc'.$ni]);
    if(isset($_POST['chkdel'.$ni]) && $_POST['chkdel'.$ni]==1){
      $sql = 'delete from topic_assoc
              where topicid = '.$topicid.'
              and resourceid = '.$parts[0].'
              and testament = '.$parts[1].'
              and book = '.$parts[2].'
              and chapter = '.$parts[3].'
              and verse = '.$parts[4].' ';
      $delete = dbquery($sql);

    }else{
      $tasqn = processsqlnumb(((isset($_POST['tasqn'.$ni]))?$_POST['tasqn'.$ni]:0),  99, 0, 99);
      $sql = 'update topic_assoc
              set sqn = '.$tasqn.'
              where topicid = '.$topicid.'
              and resourceid = '.$parts[0].'
              and testament = '.$parts[1].'
              and book = '.$parts[2].'
              and chapter = '.$parts[3].'
              and verse = '.$parts[4].' ';
      //print($sql.'<br />');
      $update = dbquery($sql);
    }
  }
  if(isset($_POST['newresource']) && $_POST['newresource']!==''){
    $resourceid = 0;
    $tmp = str_replace(':', ' ', $_POST['newresource']);
    $tmp = str_replace('.', '', $tmp);
    $parts = explode(' ', $tmp);
    switch($parts[0]){
    case 'i': $t=2; $b=((isset($parts[1]))?$parts[1]:1); $c=1; $v=1; break;
    case 'a': $t=3; $b=((isset($parts[1]))?$parts[1]:1); $c=1; $v=1; break;
    case 'w':
      $t=4;
      $b=((isset($parts[1]))?$parts[1]:1);
      if(intval($b)==0){
        $row = rs('select book from book where title like \'%'.$b.'%\';');
        if($row) $b=$row[0]; else $b=0;
      }
      $c=1;
      $v=1;
      break;
    case 'p':
      $resourceid = -$parts[1];
      $t=0;$b=0;$c=0;$v=0;
      break;
    default:
      if($tmp == preg_replace('/\D/', '', $tmp)){
        $resourceid = $parts[0];
        $t=0;$b=0;$c=0;$v=0;
      }else{
        $sql = 'select testament, book from book where aliases like \'%~'.$parts[0].'~%\' and testament in (0,1) ';
        $row = rs($sql);
        if($row){
          $t=$row[0];$b=$row[1];
          if(isset($parts[1]) && isset($parts[2])){$c=$parts[1];$v=$parts[2];}
          else{$c=0;$v=0;}
        }else{
          $t=1;$b=40;$c=99;$v=99;
        }
      }
      break;
    }
    $sql = 'select 1 from topic_assoc where topicid = '.$topicid.' and resourceid = '.$resourceid.' and testament = '.$t.' and book = '.$b.' and chapter = '.$c.' and verse = '.$v.' ';
    $row = rs($sql);
    if($row){
      $sqlerr = 'Reference already exists';
    }else{
      $sql = 'insert into topic_assoc (topicid, resourceid, testament, book, chapter, verse) values ('.
              $topicid.','.$resourceid.', '.$t.', '.$b.', '.$c.', '.$v.') ';
      $insert = dbquery($sql);
    }
  }
  $edited=1;
  if($sqlerr=='') $sqlerr = datsav;
}


if($topicid>0){
  $sql = 'select topic, sqn, comment, description from topic where topicid = '.$topicid.' ';
  $row = rs($sql);
  $topic = $row['topic'];
  $sqn = $row['sqn'];
  $comment = $row['comment'];
  $description = $row['description'];
}else{
  $topic = '';
  $comment = '';
  $description = '';
  $sqn = 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Topic Edit</title>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<h2 style="text-align:center"><?=(($topicid>0)?'Edit':'New')?> Topic</h2>

<form name="frm" method="post" action="/topicedit.php">
  <table class="gridtable" style="width:90%;">
    <tr><td colspan="2">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>Topic</td>
      <td><input type="text" name="topic" id="topic" value="<?=$topic?>" autocomplete="off" style="width:<?=(($ismobile)?'240':'380')?>px;"></td>
    </tr>
    <!--
    <tr><td>sqn</td>
      <td><input type="text" name="sqn" value="<?=$sqn?>" autocomplete="off" style="width:20px;"></td>
    </tr>-->
    <tr><td>Optional definition, description, or links to other topics.</td>
      <td><textarea name="description" id="description" style="width:98%;height:120px;"><?=$description?></textarea></td>
    </tr>
    <tr><td>Comment (opt)</td>
      <td><input type="text" name="comment" value="<?=$comment?>" autocomplete="off" style="width:<?=(($ismobile)?'240':'380')?>px;"></td>
    </tr>

<?$ni=0;
  if($topicid>0){
    print('<tr><td style="padding:0;text-align:right;" colspan="2"><table align="right" style="width:96%;">');
    print('<tr><td style="text-align:left;padding:0 2px;margin:0;width:90%;"><b>Items</b></td><td style="text-align:left;padding:0 2px;margin:0;width:5%;"><b>Sqn</b></td><td style="text-align:left;padding:0 2px;margin:0;width:5%;"><b>Del</b></td></tr>');
    $sql = 'select ta.resourceid, ta.testament, ta.book, ta.chapter, ta.verse, ta.sqn, rs.title
            from topic_assoc ta
              left join resource rs on rs.resourceid = ta.resourceid
            where ta.topicid = '.$topicid.'
            order by ta.sqn,1,2,3,4 ';
    $med = dbquery($sql);
    while($row = mysqli_fetch_array($med)){
      $navstr = $row[0].','.$row[1].','.$row[2].','.$row[3].','.$row[4];
      print('<tr><td style="text-align:left;padding:0 2px;margin:0;">'.fixrow($row).'<input type="hidden" name="tassoc'.$ni.'" value="'.$navstr.'" /></td>');
      print('<td style="padding:0;margin:0;vertical-align:top;"><input type="text" name="tasqn'.$ni.'" value="'.$row['sqn'].'" style="width:20px;text-align:right;" autocomplete="off" /></td>');
      print('<td style="padding:0;margin:0;vertical-align:top;"><input type="checkbox" name="chkdel'.$ni.'" value="1" /></td></tr>');
      $ni++;
    }
    print('<tr><td colspan="3" style="text-align:left;padding:2px;margin:0;white-space:nowrap;">New <input type="text" name="newresource" value="" autocomplete="off" /></td></tr>');
    print('</table></td></tr>');
  }
?>
    <tr>
      <td colspan="2">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <?if($topicid>0){?>
        <input type="submit" name="btndel" value="Delete" onclick="return valdel(document.frm)">
        <?}?>
        <input type="button" name="btnback" value="Close" onclick="olClose('');parent.document.location.reload();">
      </td>
    </tr>
  </table>
<?
  if($topicid>0){?>

<div style="width:480px;margin:10px auto;text-align:left;">
To assign video or audio resource, enter its ID: "123"<br />
To assign Scripture ref, IE: "matt 1:1"<br />
To assign Appendix: "a 1" assigns Appx 1.<br />
To assign Playlist: "p 12" assigns playlist 12.<br />
<!--Information: "i 1" assigns "About".<br />-->
<?if($revws==1){?>
Word Study: "w sozo" assigns ws on "sozo".<br />
<?}?>
<br />
If you type an unrecognized reference it will be appear as Matt 99:99. If that happens, delete the reference.
</div>
<?}?>
  <input type="hidden" name="mcnt" value="<?=$ni?>" />
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="topicid" value="<?=$topicid?>" />
  <input type="hidden" name="oper" value="" />
</form>

<script>
  function validate(f){
    f.topic.value = f.topic.value.replace(/"/g, '');
    var havedel = 0;
    for(var i=0;i<<?=$ni?>; i++){
      if(f['chkdel'+i].checked){
        havedel = 1;
        break;
      }
    }
    if(havedel==1){
      if(!confirm('Are you sure you want to delete the checked items?')){return false;}
    }
    f.oper.value = 'savtopic';
    return true;
  }

  function $(el) {return document.getElementById(el);}
  function $$(el) {return parent.document.getElementById(el);}

  function olClose(locn) {
    var ol = $$("overlay");
    ol.style.display = 'none';
    if(locn!='') parent.document.location.href=locn;
    setTimeout('$$("ifrm").src="/includes/empty.htm"', 200);
  }
  //parent.goback+=1;

  function valdel(f){
    if(confirm('Are you sure you want to delete this topic?')){
      f.oper.value = 'deltopic';
      f.submit();
    }else{
      return false;
    }

  }
  setTimeout('$(\'topic\').focus();', 500);
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
      height : '80',
      //enterMode : CKEDITOR.ENTER_BR,
      //shiftEnterMode : CKEDITOR.ENTER_P
    }
    );
  </script>

</body></html>
<?
   function fixrow($r){
     $id= $r[0];
     $t = $r[1];
     $b = $r[2];
     $c = $r[3];
     $v = $r[4];
     $tit=$r[6];
     if($id==0){
       $ret=getbooktitle($t,$b, 0);
       $href='/'.$ret;
       if($t<2 && $c>0){$ret.=' '.$c.':'.$v;$href.='/'.$c.'/'.$v.'/1';}
       if($t<2 && $c==0){$ret.=' Book Commentary';$href='/book'.$href.'/ct';}
       if($t==2){$href='/info/'.$b.'/ct';}
       if($t==3){$href='/appx/'.$b.'/ct';}
       if($t==4){$ret='WordStudy: '.$ret;$href='/word'.$href.'/ct';}
       //$ret='<a href="'.$href.'" target="_blank">'.$ret.'</a>';
       return $ret;
     }else{
       if($id<0)
         return 'playlist: '.-$id;
       else
         return $tit.' <small>(ID: '.$id.')</small>';
     }
   }

