<?php
if(empty($userid) || $userid==0) die('unauthorized access');

$logid = ((isset($_REQUEST['temp']))?$_REQUEST['temp']:0);
$stitle = 'Editing User Edit';

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=="savedit"){
  $comment    = processsqltext($_POST['comment'], 200, 0, '-');
  $whatsnew   = processsqlnumb(((isset($_POST['whatsnew']))?$_POST['whatsnew']:0), 1, 0, 0);

  $sql = 'update editlogs set
          comment = '.$comment.',
          whatsnew = '.$whatsnew.'
          where logid = '.$logid.' ';
  $update = dbquery($sql);
  if($sqlerr=='') $sqlerr = datsav;
}

$sql = 'select testament from editlogs where logid = '.$logid.' ';
$row = rs($sql);
switch($row[0]){
  case 9:
    $sql = 'select b.blogtitle title, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, ifnull(ifnull(u.revusername, u.myrevname), \'unknown\') username
            from editlogs e
            inner join revblog b on (e.testament = 9 and b.blogid = e.book)
            left join myrevusers u on (u.userid = e.userid and u.myrevid > 0)
            where e.logid = '.$logid.' ';
    break;
  case -1:
    $sql = 'select b.bibauthor title, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, ifnull(ifnull(u.revusername, u.myrevname), \'unknown\') username
            from editlogs e
            left join bibliography b on (b.bibid = e.page and b.bibid = e.book and e.testament = 0 and e.chapter in (0,1) and e.verse = 0)
            left join myrevusers u on (u.userid = e.userid and u.myrevid > 0)
            where e.logid = '.$logid.' ';
    break;
  default:
    $sql = 'select b.title, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, ifnull(ifnull(u.revusername, u.myrevname), \'unknown\') username
            from editlogs e
            inner join book b on (b.testament = e.testament and b.book = e.book)
            left join myrevusers u on (u.userid = e.userid and u.myrevid > 0)
            where e.logid = '.$logid.' ';
    break;
}
//print($sql);

$row = rs($sql);
$comment  = $row['comment'];
$whatsnew = $row['whatsnew'];
$what = fixedit($row);
?>
<form name="frm" action="/" method="post">

<span class="pageheader"><?=$stitle?></span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>
  <?=printsqlerr($sqlerr)?><br />

  <small>
  <?=$what?><br />
  Edit date: <?=converttouserdate($row['editdate'], $timezone)?><br />
  By: <?=$row['username']?><br />
  ID: <?=$logid?></small><br />
  <table width="96%" cellpadding="4" style="font-size:90%">
     <tr>
       <td>Comment</td>
       <td>
        <input type="text" name="comment" value="<?=$comment?>" size="60" maxlength="200" style="margin-top:2px">
        <a onclick="document.frm.comment.value=document.frm.comment.value+'&ldquo;&rdquo;';return false;" title="click to add smart quotes">&ldquo;&rdquo;</a>
      </td>
     </tr>
     <tr>
       <td>&nbsp;</td>
       <td><input type="checkbox" name="whatsnew" value="1"<?=fixchk($whatsnew)?>> <small>Flag for "What's New"</small></td>
     </tr>
     <tr><td colspan="2">
       <input type="submit" name="xc" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />&nbsp;&nbsp;
       <a onclick="return navigate(<?=$mitm?>,18,<?=$navstring?>);">Back to Manage Edits</a><br />
     </td></tr>
  </table>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="temp" value="<?=$logid?>" />
  <input type="hidden" name="oper" value="" />
  </form>
  <script>
    function validate(f){
      f.oper.value = "savedit";
      return true;
    }
  </script>
<?
function fixedit($r){
  $str=' target="_blank" title="Click to view.  Opens new window/tab"';
  switch($r['page']){
  case 1: // verse/commentary
    $ret = 'verse/commentary for <a href="/'.$r['title'].'/'.$r['chapter'].'/'.$r['verse'].'"'.$str.'>'.$r['title'].' '.$r['chapter'].':'.$r['verse'].'</a>';
    break;
  case 6: // book/commentary
    $ret = 'book/commentary for <a href="/book/'.$r['title'].'"'.$str.'>'.$r['title'].'</a>';
    break;
  case 8: // appx/intro
    $ret = 'appx/intro for <a href="/'.(($r['testament']==2)?'intro':'appx').'/'.$r['book'].'"'.$str.'>'.$r['title'].'</a>';
    break;
  case 27: // blog
    $ret = 'blog entry for <a href="/blog/'.$r['book'].'"'.$str.'>'.$r['title'].'</a>';
    break;
  case 51:
    $ret = '<a onclick="olOpen(\'/bibedit.php?bibid='.$r['book'].'&bibtype='.$r['chapter'].'\', 600, 500, 1);">'.(($r['chapter']==1)?'Bibliography':'Abbreviations').'</a>';
    //if($r['chapter']==1)
    //  $ret = '<a href="/bibliography"'.$str.'>Bibliography</a>';
    //else
    //  $ret = '<a href="/abbreviations"'.$str.'>Abbreviations</a>';
    break;
  default:
    $ret = 'unknown: '.$r['page'];
  }
  return $ret;
}
?>
