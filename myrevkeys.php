<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

if(empty($myrevid) || $myrevid==0) die('unauthorized access');

$sqlerr = '';
$submitted=0;
$oper=((isset($_REQUEST['oper']))?$_REQUEST['oper']:'xx');
$ccnt= sizeof($hilitecolors);

if($oper=='sav'){
  $keys='';
  for($ni=0;$ni<$ccnt;$ni++){
    if($ni>0) $keys.='~';
    $keys.=processsqlkey($_REQUEST['txtkey_'.$ni], 20);
  }
  $update = dbquery('update myrevusers set myrevkeys = \''.$keys.'\' where myrevid = '.$myrevid.' ');
  if($sqlerr=='') $sqlerr = datsav.'&nbsp;';
  $submitted = 1;
}
$stitle = $myrevname.'&rsquo;s MyREV Captions';

?>
<!DOCTYPE html>
<html>
<head>
  <title>MyREV Keys</title>
  <link rel="stylesheet" type="text/css" href="/includes/style<?=$colors[0]?>.css?v='.$fileversion.'" />
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/myrevjs.js?v=<?=$fileversion?>"></script>
</head>
<body style="font-family:<?=$fontfamily?>, times new roman; font-size:<?=$fontsize?>em; line-height:<?=$lineheight?>em;">

<div style="text-align:center;padding:7px 0;">
<h3 style="display:inline-block;width:70%;text-align:center;margin:0;"><?=$stitle?></h3>
<span style="float:right;cursor:pointer;" onclick="olClose('');"><img src="/i/redx.png" style="width:20px;" alt="" /></span>
</div>
<div style="text-align:center;margin:12px  auto;">
<a onclick="expandcollapsediv('resinst')">What is this? <span id="moreless">&raquo;</span></a>
  <div id="resinst" style="text-align:left;height:0;padding:3px;margin:0 auto;overflow:hidden;transition:height .4s ease-in;max-width:640px;font-size:90%;">
    <h3 style="text-align:center;">What are MyREV Color Captions?!</h3>
    <p>Here is where you can assign captions, or labels, to the highlighting colors.
      The captions should be short and descriptive.
      For example, you can assign one of the colors to &ldquo;Favorite,&rdquo; another to &ldquo;Study,&rdquo; one to &ldquo;Question,&rdquo; &ldquo;Important&rdquo; and so forth.
    </p><p>
      If you're putting together a teaching, you could have a caption &ldquo;Teaching,&rdquo; then highlight all the verses you want to use in your teaching with that color, then on the MyREV page,
      filter by &ldquo;Teaching,&rdquo; change the Sort to &ldquo;Custom,&rdquo; arrange the verses in the order you're going to teach them, and export the list of verses to MS Word or as a PDF.
    </p>
  </div>
<?=printsqlerr($sqlerr.'<br />')?>
</div>
<form name="frm" method="post" action="/myrevkeys.php">
<?
// need to reload here..
$sql = 'select myrevkeys from myrevusers where myrevid = '.$myrevid.' ';
$row = rs($sql);
$tmp = $row[0];
if($tmp==null) $tmp = 'Clear'.substr('~~~~~~~~~~',0,$ccnt);
$myrevkeys = explode('~',$tmp);

print('<table style="text-align:center;margin:10px auto;">');
print('<tr>');
print('<td>Current</td>');
print('<td>New Caption</td>');
print('</tr>'.crlf);
for($ni=0;$ni<$ccnt;$ni++){
  print('<tr>');
  print('<td style="color:'.$colors[7].';background-color:'.$hilitecolors[$ni].';font-size:80%;text-align:left;padding:2px 4px 0 4px;min-width:100px;border:1px solid '.$colors[3].';border-radius:4px;">'.$myrevkeys[$ni].'</td>');
  print('<td><input type="text" name="txtkey_'.$ni.'" value="'.$myrevkeys[$ni].'" size="12" maxlength="16" autocomplete="off" onchange="document.frm.dirty.value=1;"></td>');
  print('</tr>'.crlf);
}
print('</table>');
?>

  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirty" value="" />

  <p style="text-align:center;margin:9px 0;">
    <input type="submit" name="btnsubmit" class="gobackbutton" style="cursor:pointer;width:80px;" value="Save" onclick="return validate(document.frm);" />
    <input type="button" name="btnclosee" class="gobackbutton" style="cursor:pointer;width:80px;" value="Done" onclick="olClose('');">&nbsp;&nbsp;
  </p>
</form>
<script>
  var submitted = <?=$submitted?>;
  var prfscrollynav = <?=$scrollynav?>;

  function olClose(locn) {
    var msg = checkdirt(document.frm);
    if(msg) {if(confirm(msg)) return;}
    if(submitted==1)
      parent.location.reload();
    else
      parent.rlbfadeout();
  }

  function validate(f){
   f.oper.value = "sav";
   return true;
  }
  function checkdirt(f){
    if(f.dirty.value==1){
      return '\nYou have unsaved changes!\nIf you want to save them, click \'OK\', then \'Save\'.\n';
    }
    return '';
  }
  function expandcollapsediv(id){
    excoldiv(id); // in misc.js
    var div = $(id);
    if(div.style.height=='0px'){
      $('moreless').innerHTML='&raquo;';
    }else{
      $('moreless').innerHTML='&laquo;';
    }
  }

</script>

</body>
</html>
<?
  logview(302,0,0,0,0);


function processsqlkey($txt, $siz){
  $ret = trim($txt);
  if($ret){
    $ret = str_replace('"', '', $ret);          // remove double quotes
    $ret = str_replace('\'', '', $ret);         // remove double quotes
    $ret = str_replace('~', '', $ret);          // remove tilde's
    $ret = strip_tags($ret);                        // remove all html tags
    if(strlen($ret)==0){                            // everything has been removed
      return '';
    }
    $ret = substr($ret, 0, $siz);                   // check length
    $ret = preg_replace('#\'#', '\\\'', $ret);      // escape single quotes
    return $ret;
  }else{
    return '';
  }
}
