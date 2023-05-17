<?php
if(empty($userid) || $userid==0) die('unauthorized access');

$goback = ((isset($_REQUEST['goback']))?$_REQUEST['goback']:1);
$btitle = 'Manage '.(($mitm==1)?'Information':(($mitm==6)?'Appendices':'Word Studies'));
$newid = (($mitm==1)?'Intro ID':(($mitm==6)?'Appendix ID':'Word Study ID'));

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=="savcont"){
  $ni=0;
  while(isset($_POST['book'.$ni])){
    $bok = processsqlnumb($_POST['book'.$ni], 100, 0, 99);
    if(isset($_POST['del'.$ni]) && ($_POST['del'.$ni]==1)){
      $sql = 'delete from editlogs where testament = '.$test.' and book = '.$bok.' and chapter = 1 and verse = 1 ';
      $delete = dbquery($sql);
      $sql = 'delete from verse where testament = '.$test.' and book = '.$bok.' and chapter = 1 and verse = 1 ';
      $delete = dbquery($sql);
      $sql = 'delete from book where testament = '.$test.' and book = '.$bok.' ';
      $delete = dbquery($sql);
    }else{
      $sqn = processsqlnumb((isset($_POST['sqn'.$ni])?$_POST['sqn'.$ni]:99), 100, 0, 99);
      $act = processsqlnumb((isset($_POST['act'.$ni])?$_POST['act'.$ni]:0), 1, 0, 0);

      $sql = 'update book set
              sqn = '.$sqn.',
              active = '.$act.'
              where testament = '.$test.'
              and book = '.$bok.' ';
      $update = dbquery($sql);
    }
    $ni++;
  }
  $nbk = $_POST['newbook'];
  if(is_numeric($nbk)){
    $chk = dbquery('select 1 from book where testament = '.$test.' and book = '.$nbk,' ');
    if(mysqli_num_rows($chk)){
      $sqlerr.= $newid.' '.$nbk.' already exists..';
    }else{
      $sql = 'insert into book(book,testament,title,abbr,chapters,tagline,sqn,active,bwabbr,commentary)values('.
             $nbk.','.$test.',\''.(($test==2)?'My new intro':(($test==3)?'Appendix '.$nbk:'Word_Study_'.$nbk)).'\',\'-\',1,null,'.$nbk.',0,\'-\',null)';
      $insert = dbquery($sql);
      $sql = 'insert into verse(testament,book,chapter,verse,heading,paragraph,versetext,footnotes,commentary)values('.
             $test.','.$nbk.',1,1,null,0,\'-\',\'~~~~~~~~\',\'<p>My new '.(($test==2)?'intro':(($test==3)?'appendix':' word study '.$nbk)).' content</p>\')';
      $insert = dbquery($sql);
    }
  }
  if($sqlerr=='') $sqlerr = datsav;
  $goback++;
}

?>
  <form name="frm" action="/" method="post">
  <span class="pageheader"><?=$btitle?></span><br />
  <table border="0" cellpadding="2" cellspacing="0" align="center" style="font-size:90%">
    <tr><td colspan="6"><?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>ID</td><td><?=(($test==4)?'Word':'Title');?></td><?=(($test==4)?'':'<td>Sqn</td>')?><td>Edit</td><td>Public</td><td>Delete</td></tr>
<?
  $ni = 0;
  $ctt = dbquery('select book, title, sqn, active from book where testament = '.$test.' order by '.(($test==4)?'title':'sqn').' ');
  while($row = mysqli_fetch_array($ctt)){
?>
    <tr>
      <td><input type="hidden" name="book<?=$ni?>" value="<?=$row['book']?>"><?=$row['book']?></td>
      <td><?=$row['title']?></td>
      <?if($test!=4){?>
      <td><input type="text" name="sqn<?=$ni?>" value="<?=$row['sqn']?>" size="2" onchange="setdirt();"></td>
      <?}?>
      <td><?print(editlink('notimportanthere'.$ni,'inline',$mitm,8,$test,$row['book'],1,1));?></td>
      <td><input type="checkbox" name="act<?=$ni?>" value="1"<?=fixchk($row['active'])?> onclick="setdirt();"></td>
      <td>
        <input type="checkbox" name="del<?=$ni?>" value="1" onclick="setdirt();">
        <?if($test==4){?>
        <input type="hidden" name="sqn<?=$ni?>" value="<?=$row['sqn']?>"
        <?}?>
      </td>
    </tr>
<?  $ni++;
  }
  $row = rs('select max(book) from book where testament = '.$test.' ');
  $newbook = $row[0] + 1;

?>
  <tr>
    <td>
    <input type="text" name="newbook" id="newbook" value="" size="2" maxlength="3" onchange="setdirt();">
    </td>
    <td colspan="4">&lt;&lt;new <?=$newid.' (<a onclick="$(\'newbook\').value=(($(\'newbook\').value==\'\')?'.$newbook.':\'\');">suggest: '.$newbook.'</a>)'?></td>
  </tr>
  <tr>
    <td colspan="6"><br />
      <input type="submit" name="xc" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />
      <button onclick="history.go(-<?=$goback?>);return false;" style="text-align:center;font-size:80%">Back</button>
    </td>
  </tr>
  </table>

  <input type="hidden" name="mitm" value="<?=$mitm?>">
  <input type="hidden" name="page" value="11" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="goback" value="<?=$goback?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirt" value="0" />
  </form>
  <script>
    var checkforchanges = true;

    function validate(f){
      checkforchanges = false;
      var havedel=0;
      for(var i=0;i<<?=$ni?>;i++){
        if(f['del'+i].checked) havedel = 1;
      }
      if(havedel==1){
        if(!confirm('Are you CERTAIN you want to delete the checked item(s)?\n\nThis is NOT undoable.')) return false;
      }
      f.oper.value = "savcont";
      return true;
    }
    function checkdirt(){
      if(!checkforchanges) return;
      var f = document.frm;
      var dirty = 0;
      if(f.dirt.value==1) dirty = 1;
      if(dirty == 1)return 'You have unsaved changes.\nIf you continue those changes will be LOST.';
      else return;
    }

    function setdirt(){
      var f = document.frm;
      f.dirt.value = 1;
    }

    window.onbeforeunload = checkdirt;
  </script>

