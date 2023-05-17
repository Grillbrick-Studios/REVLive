<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) die('unauthorized access');
$stitle = 'Upload and Run SQL';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";
$sqlerr='';
if($oper=='sav'){
  $target_dir = "export/";
  $target_file = $target_dir.basename($_FILES["sqlfile"]["name"]);
  $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  if($fileType==''){
    $msg = 'no file..';
  }else{
    if($fileType != "sql"){
      $msg = 'file must be .sql';
    }else if (move_uploaded_file($_FILES["sqlfile"]["tmp_name"], $target_file)) {
      $myfile = fopen($target_file, "r") or die("Unable to open file!");
      $sqlcount=0;
      $sqlmisscount = 0;
      while(!feof($myfile)) {
        $sql = fgets($myfile);
        $sql = trim($sql);
        //print('>'.substr(strip_tags($sql), 0, 40).'&lt;<br />');
        if(strlen($sql??'') > 0 && (
           strpos($sql, 'update verse set heading')===0 ||
           strpos($sql, 'update book set abbr')===0 ||
           strpos($sql, 'delete from outline')===0 ||
           strpos($sql, 'delete from footnotes')===0 ||
           strpos($sql, 'insert into outline')===0 ||
           strpos($sql, 'insert into footnotes')===0 ||
           strpos($sql, 'update verse set comfootnotes')===0)){
          $update = dbquery($sql);
          $sqlcount++;
        }else if(strlen($sql)>0 && strpos($sql, '--') === false) {$sqlmisscount++;};
      }
      fclose($myfile);
      unlink($target_file);
      if($sqlcount==0) $msg='Oops. No statements processed.';
    }else{
      $msg = "Sorry, there was an error uploading your file.";
    }
  }

  $sqlerr = $msg;
  if($sqlerr=='') $sqlerr = datsav.'<br />&nbsp;'.$sqlcount.' statements processed.<br />&nbsp;'.$sqlmisscount.' statements not processed.';
}

?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<form name="frm" method="post" action="/" enctype="multipart/form-data">
  <table border="1" cellpadding="4" cellspacing="0" style="font-size:90%" align="center">
    <tr><td colspan="9">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr><td>File</td><td><input type="file" name="sqlfile" id="sqlfile" value=""></td></tr>
    <tr>
      <td colspan="2">
        <input type="reset" name="btnreset" value="Reset">
        <input type="submit" name="btnsubmit" value="Submit" onclick="return validate(document.frm);">
      </td>
    </tr>
  </table>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
</form>
<script>

  function validate(f){
    f.oper.value = 'sav';
    return true;
  }

</script>

