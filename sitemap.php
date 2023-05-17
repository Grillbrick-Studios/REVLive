<?php
if(empty($userid) || $userid==0 || empty($superman) || $superman==0) die('unauthorized access');

set_time_limit(120); // 2 minutes
$stitle = 'Generate SiteMap';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=='go'){
  $loc = $jsonurl; // from functions.php

  // gotta build this..
  //$timstamp = '2020-04-25T02:32:56+00:00';
  $timstamp = str_replace('~', 'T', date('Y-m-d~H:i:s+00:00'));
  $filname = $docroot.'/sitemap.xml';

  file_put_contents($filname, '');
  printtt('<?xml version="1.0" encoding="UTF-8"?>');
  printtt('<urlset'.crlf.
          '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.crlf.
          '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.crlf.
          '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.crlf.
          '    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'.crlf);

  $sql = 'select testament, book, chapters, title, length(ifnull(commentary,\'\')) lcom
          from book where active = 1 order by 1, 2 ';
  $tests= dbquery($sql);
  $ni=0;
  while($row = mysqli_fetch_array($tests)){
    $title = str_replace(' ', '-', $row['title']);
    switch($row['testament']){
    case 0:
    case 1:
      if($row['lcom']>0){
        printt($loc.'/book/'.$title, '0.70');
        $ni++;
      }
      for($nj=1;$nj<=$row['chapters'];$nj++){
        printt($loc.'/'.$title.'/chapter'.$nj, '1.00');
        $sql = 'select verse
                from verse
                where testament = '.$row['testament'].'
                and book = '.$row['book'].'
                and chapter = '.$nj.'
                and length(ifnull(commentary, \'\')) > 0
                order by 1 ';
        $vss= dbquery($sql);
        $nk=0;
        while($rrow = mysqli_fetch_array($vss)){
          printt($loc.'/'.$title.'/chapter'.$nj.'/'.$rrow['verse'], '0.90');
          $ni++;$nk++;
        }
        if($nk>0){
          printt($loc.'/comm/'.$title.'/chapter'.$nj, '0.85');
          $ni++;
        }
        $ni++;
      }
      break;

    case 2: // info
      printt($loc.'/info/'.$row['book'], '0.80');
      break;

    case 3: // appx
      printt($loc.'/appx/'.$row['book'], '0.80');
      break;

    case 4: // wordstudy
      printt($loc.'/word/'.$title, '0.80');
      break;

    default:
      break;
    }
    $ni++;
  }
  printtt('</urlset>');
  $msg='DONE. '.$ni.' URLs generated.';
  if($sqlerr=='') $sqlerr = $msg;

}
?>
<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<form name="frm" method="post" action="/">
  <table border="0" cellpadding="4" cellspacing="0" style="font-size:90%;margin-top:20px;" align="center">
    <tr><td colspan="9">&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td colspan="2">
        <input type="submit" name="btnsubmit" id="btnsubmit" value="Go" onclick="return validate(document.frm);">
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
    f.oper.value = 'go';
    setTimeout('disable()', 300);
    return true;
  }

  function disable(){
    $('btnsubmit').value='please wait..';
    $('btnsubmit').disabled=true;
  }

</script>

<?

function printt($txt, $priority='1.00'){
  global $filname, $timstamp;
  $str = '<url>'.crlf.'  <loc>'.$txt.'</loc>'.crlf;
  $str.= '  <lastmod>'.$timstamp.'</lastmod>'.crlf;
  $str.= '  <priority>'.$priority.'</priority>'.crlf;
  $str.= '</url>'.crlf;
  file_put_contents($filname, $str, FILE_APPEND);
}

function printtt($txt){
  global $filname;
  file_put_contents($filname, $txt.crlf, FILE_APPEND);
}

?>
