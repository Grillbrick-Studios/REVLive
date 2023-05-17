<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($userid) || $userid==0 || ($chronedit==0 && $resedit==0)) {print('<h3>unauthorized access</h3>');return;}

$cattype = ((isset($_REQUEST['cattype']))?$_REQUEST['cattype']:0);
switch($cattype){
case 0:$stitle = 'Edit Chronology Categories';break;
case 1:$stitle = 'Edit Library Categories';break;
}

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$msg = "";

$reloadparent=0;

if($oper=='savcats'){
  $catcount=$_POST['catcount'];
  for($ni=0;$ni<$catcount;$ni++){
    $catid = $_POST['catid'.$ni];
    if(isset($_POST['delcat'.$ni]) && $_POST['delcat'.$ni]==1){
      $del = dbquery('delete from category_assoc where catid = '.$catid.' ');
      $del = dbquery('delete from category where catid = '.$catid.' ');
    }else{
      $sqn    = processsqlnumb(((isset($_POST['sqn'.$ni]))?$_POST['sqn'.$ni]:1), 99, 0, 99);
      $catnam = processsqltext(((isset($_POST['catnam'.$ni]))?left($_POST['catnam'.$ni], 50):'missing catname!'), 50, 0, 'missing catname!');
      //print($catnam.'<br />');
      $upd = dbquery('update category set categoryname = '.$catnam.', sqn = '.$sqn.' where catid = '.$catid.' ');
    }
  }
  if(trim($_POST['catnam'.$catcount])!=''){
    $catnam = processsqltext($_POST['catnam'.$catcount], 50, 0, 'missing catname!');
    $sqn    = processsqlnumb(((isset($_POST['sqn'.$catcount]))?$_POST['sqn'.$catcount]:1), 99, 0, 99);
    $ins = dbquery('insert into category (categoryname, cattype, sqn) values ('.$catnam.', '.$cattype.', '.$sqn.') ');
  }
  $reloadparent=1;
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
<form name="frm" method="post" enctype="multipart/form-data" action="/categoryedit.php">

  <table class="gridtable" style="width:90%;max-width:800px;min-width:440px;">
    <tr><th>Category</th><th>Sqn</th><th>Del</th></tr>
<?
  $ni=0;
  $sql = 'select cec.catid, cec.sqn, cec.categoryname, (select count(1) from category_assoc cec2 where cec2.catid = cec.catid) ccnt from category cec where cattype = '.$cattype.' order by cec.sqn, cec.categoryname';
  $cats= dbquery($sql);
  while($row = mysqli_fetch_array($cats)){
    print('<tr>');
    print('<td style="text-align:left;padding:2px;"><input type="text" name="catnam'.$ni.'" value="'.$row['categoryname'].'" autocomplete="off">');
    print('<input type="hidden" name="catid'.$ni.'" value="'.$row['catid'].'"> <small>('.$row['ccnt'].' '.(($cattype==0)?'events':'Library items').')</small></td>');
    print('<td style="padding:2px;"><input type="text" name="sqn'.$ni.'" value="'.$row['sqn'].'" autocomplete="off" style="width:20px;"></td>');
    print('<td style="padding:2px;"><input type="checkbox" name="delcat'.$ni.'" value="1"></td>');
    print('</tr>');
    $ni++;
  }
  print('<tr>');
  print('<td style="text-align:left;padding:2px;"><input type="text" name="catnam'.$ni.'" value="" autocomplete="off"><input type="hidden" name="catid'.$ni.'" value="-1"> &lt;&lt; new</td>');
  print('<td style="padding:2px;"><input type="text" name="sqn'.$ni.'" value="" autocomplete="off" style="width:20px;"></td>');
  print('<td style="padding:2px;"><input type="hidden" name="delcat'.$ni.'" value="0">&nbsp</td>');
  print('</tr>');

?>

    <tr>
      <td colspan="3" style="text-align:left;">
        <input type="submit" name="btnsubmit" value="Submit" style="background-color:#dfd;border:2px solid #090;" onclick="return validate(document.frm);">
        <input type="reset" name="btnreset" value="Reset">
        <input type="button" name="btnback" value="Close" onclick="olClose(<?=$reloadparent?>);">
      </td>
    </tr>
  </table>
<input type="hidden" name="catcount" value="<?=$ni?>">
<input type="hidden" name="cattype" value="<?=$cattype?>">
<input type="hidden" name="oper" value="">
</form>
</div>
<script>

  function validate(f){
    f.oper.value='savcats';
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
</body>
</html>

