<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";
if(empty($userid) || $userid==0 || $canedit==0) die('unauthorized access');

$bstest = ((isset($_REQUEST['bstest']))?$_REQUEST['bstest']:1);
$btitle = 'REV Bible Book Status';
?>

<!--wrapper-->
<div id="wrapper" style="opacity:100;transition:opacity .4s;overflow:hidden; height:100%; width:100%;">
  <form name="frm" action="/" method="post" style="text-align:center;">
  <span class="pageheader"><?=$btitle?></span>
  <div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
  <?if($superman==1){?>
  <div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
  <?}?>
<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:90%;">
  <a onclick="expandcollapsediv('resinst')" style="display:block;margin-top:10px;">What is this? <span id="moreless">&raquo;</span></a>
  <div id="resinst" style="text-align:left;height:0;padding:3px;margin:0;overflow:hidden;transition:height .4s ease-in;">
    <h3 style="text-align:center;">Welcome to REV Bible Book Status!</h3>
    <img src="/i/underconstruction.png" alt="under construction" style="border:0;width:104px;float:left;">
    <p style="margin-bottom:0;">This page provides information concerning each Bible book&rsquo;s introduction, outline, and locked status. You can&rsquo;t change anything on this page directly, but links are provided where these items can be viewed and changed.</p>
  </div>
</div>
  <select name="bstest" onchange="document.frm.submit();">
    <option value="0"<?=fixsel(0,$bstest)?>>Old Testament</option>
    <option value="1"<?=fixsel(1,$bstest)?>>New Testament</option>
    <option value="3"<?=fixsel(3,$bstest)?>>Appendices</option>
    <option value="4"<?=fixsel(4,$bstest)?>>Word Studies</option>
  </select><br />
  <?
  if($bstest>1) print('<span style="color:red;"><small>Only for assigning reviewers...</small></span>');
  ?>
  <table border="0" cellpadding="2" cellspacing="0" align="center" style="font-size:80%">
    <tr><td colspan="6"><?=printsqlerr($sqlerr)?></td></tr>
    <tr><th style="vertical-align:top;">Book</th><th style="vertical-align:top;">Intro</th><th>Outline<br />Data/Pub/Finl</th><th style="vertical-align:top;">Locked</th><th style="vertical-align:top;">Review</th></tr>
<?
  $ni = 0;
  $sql = 'select b.testament, b.book, b.abbr, if(b.testament>1,
          ifnull(b.tagline, b.title), b.title) title, if(commentary is null,0,1) commentary,
          (select count(*) from outline o where o.testament = b.testament and o.book = b.book) data,
          b.outlinepublished, b.outlinefinalized, b.bookfinalized,
          group_concat(ifnull(ifnull(mu.revusername, mu.myrevname), \'-\') order by 1 separator \', \') peers
          from book b
            left join book_peer bp on bp.testament = b.testament and bp.book = b.book
            left join myrevusers mu on (mu.userid = bp.userid and mu.myrevid > 0)
          where b.testament = '.$bstest.'
          group by 1, 2, 3, 4, 5, 6, 7, 8, 9
          order by 1, 2 ';

  $ctt = dbquery($sql);
  $stryes = '<img src="/i/checkmark.png" style="width:1em;" alt="" />';
  $strno  = '<img src="/i/redx.png" style="width:1em;" alt="" />';
  while($row = mysqli_fetch_array($ctt)){
    $navstr = $mitm.',6,'.$bstest.','.$row['book'].',0,0';
    $tit = $row['title'];
    if(strlen($tit) > 27) $tit = substr($tit, 0, 25).'...';
    switch($bstest){
    case 0: // old testament
    case 1: // new testament
      $edlink = '<a onclick="return localnav('.$navstr.');" title="Edit book"><img src="/i/edit.gif" alt="" /></a>';
      $vwlink = '<a href="/'.str_replace(' ', '', $row['abbr']).'/1" target="_blank">'.$tit.'</a>';
      break;
    case 3: // appendices
      $edlink = '';
      $vwlink = '<a href="/appx/'.$row['book'].'/1" target="_blank">'.$tit.'</a>';
      break;
    case 4: // wordstudies
      $edlink = '';
      $vwlink = '<a href="/word/'.$row['title'].'/1" target="_blank">'.$tit.'</a>';
      break;
    }
?>
    <tr>
      <td style="text-align:left;"><?=$edlink.' '.$vwlink?></td>
      <td><?=(($row['commentary']==1)?$stryes:$strno)?></td>
      <td><?=(($row['data']>0)?$stryes:$strno)?> / <?=(($row['outlinepublished']==1)?$stryes:$strno)?> / <?=(($row['outlinefinalized']==1)?$stryes:$strno)?></td>
      <td><?=(($row['bookfinalized']==1)?$stryes:$strno)?></td>

<?
    $editpeers = (($superman==1)?'<a onclick="olOpen(\'/bookpeerassign.php?testament='.$row['testament'].'&amp;book='.$row['book'].'\', 600, 500);return false;"><img src="/i/edit.gif" alt="" /></a>&nbsp;':'');
    print('<td style="text-align:left;">'.$editpeers.$row['peers'].'</td>');
}
print('</tr>');
?>
  <tr>
    <td colspan="4" style="text-align:center;"><br />
      <input type="button" name="xc" value="Refresh" onclick="location.reload()" style="text-align:center;font-size:80%" />
    </td>
  </tr>
  </table>

  <input type="hidden" name="page" value="<?=$page?>">
  <input type="hidden" name="mitm" value="<?=$mitm?>">
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  </form>

</div>

  <script>

  function $(el) {return parent.document.getElementById(el);}
  function $$(el){return document.getElementById(el);}

  setTimeout('$$(\'wrapper\').style.visibility=\'visible\';$$(\'wrapper\').style.opacity = 1;', 100);


  function expandcollapsediv(id){
    excoldiv(id); // in misc.js
    var div = $(id);
    if(div.style.height=='0px'){
      $('moreless').innerHTML='&raquo;';
    }else{
      $('moreless').innerHTML='&laquo;';
    }
  }

  function localnav(m,p,t,b,c,v){
    var f = document.frmnav;
    f.mitm.value = m;
    f.page.value = p;
    f.test.value = t;
    f.book.value = b;
    f.chap.value = c;
    f.vers.value = v;
    f.target = '_blank';
    f.submit();
    return false;
    }


  </script>