<?php
if(empty($userid) || ($userid==0)) die('unauthorized access');

// does not work
// ini_set('max_input_vars', 3000);

$daysback = ((isset($_POST['daysback']))?$_POST['daysback']:1);
$editfilter = ((isset($_POST['editfilter']))?$_POST['editfilter']:0);
$exp = 0;
$expuid=0;
$oper = ((isset($_POST['oper']))?$_POST['oper']:'none');
if($oper=='none' && isset($_POST['logid'])){
  $tmp = $_POST['logid'];
  if($tmp>0){
    $row = rs('select userid from editlogs where logid = '.$tmp.' ');
    $expuid = $row[0];
    $del = dbquery('delete from editlogs where logid = '.$tmp.' ');
    $del = dbquery('delete from editlogsviewed where logid = '.$tmp.' ');
    $exp=1;
  }
}
if($oper == 'usr'){
  $expuid = $_POST['logid'];
}
if($oper == 'del'){
  for ($ni=0;$ni<$_POST['logcount'];$ni++) {
    if(isset($_POST['logid'.$ni])){
      $logid = $_POST['logid'.$ni];
      $sql = 'delete from editlogs where logid = '.$logid.' ';
      $del = dbquery($sql);
      $sql = 'delete from editlogsviewed where logid = '.$logid.' ';
      $del = dbquery($sql);
    }
  }
}
if($oper == 'umrk'){
  for ($ni=0;$ni<$_POST['logcount'];$ni++) {
    if(isset($_POST['logid'.$ni])){
      $logid = $_POST['logid'.$ni];
      $loguid = $_POST['loguid'.$ni];
      $sql = 'delete from editlogsviewed where logid = '.$logid.' and loguserid = '.$loguid.' and userid = '.$userid.' and flagged = 0 ';
      $mrk = dbquery($sql);
    }
  }
}
if($oper == 'mrk'){
  for ($ni=0;$ni<$_POST['logcount'];$ni++) {
    if(isset($_POST['logid'.$ni])){
      $logid = $_POST['logid'.$ni];
      $loguid = $_POST['loguid'.$ni];
      $row = rs('select flagged from editlogsviewed where userid = '.$userid.' and loguserid = '.$loguid.' and logid = '.$logid.' ');
      $flagged = (($row)?$row[0]:0);
      $sql = 'delete from editlogsviewed where logid = '.$logid.' and loguserid = '.$loguid.' and userid = '.$userid.' ';
      $mrk = dbquery($sql);
      $sql = 'insert into editlogsviewed (userid, loguserid, logid, flagged) values ('.$userid.','.$loguid.','.$logid.', '.$flagged.');';
      $mrk = dbquery($sql);
    }
  }
}
if($oper == 'dlv'){
  $sql = 'delete FROM editlogsviewed WHERE flagged = 0 and logid in (select logid from editlogs where whatsnew = 0 and editdate < DATE_ADD(UTC_TIMESTAMP(),INTERVAL -200 DAY)) ';
  $mrk = dbquery($sql);
}

$noednotes = (($editorcomments==0)?' and page != 7 ':'');
$nopeernotes = (($peernotes==0)?' and page != 310 ':'');
$noblog    = (($revblog==1)?'':' and page != 27 ');
$sqladd = $noednotes.$nopeernotes.$noblog;

switch($editfilter){
case 1: //flagged
  $sql = 'select count(logid)
          from editlogsviewed
          where userid = '.$userid.'
          and flagged = 1';
  $cnt = rs($sql);
  $tot = $cnt[0];
  break;
case 2: // unread
  $sql = 'select count(*)
          from editlogs
          where editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)'.$sqladd.'
          and logid not in (select logid from editlogsviewed where userid = '.$userid.')
          limit 1';

  $cnt = rs($sql);
  $tot = $cnt[0];
  break;
default: // all
  $sql = 'select count(*)
          from editlogs
          where (editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)'.$sqladd.'
          and logid not in (select logid from editlogsviewed where userid = '.$userid.' and flagged = 1))
          or logid in (select logid from editlogsviewed where userid = '.$userid.' and flagged = 1)
          limit 1';

  $cnt = rs($sql);
  $tot = $cnt[0];
  break;
}
$sql = 'select count(*) from editlogs ';
$cnt = rs($sql);
$ttot = $cnt[0];

$sql = 'select count(*)
        from editlogs
        where (editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)'.$sqladd.'
        and logid not in (select logid from editlogsviewed where userid = '.$userid.'))
        or logid in (select logid from editlogsviewed where userid = '.$userid.' and flagged = 1)';

$row = rs($sql);
$etot = $row[0];

?>
<span class="pageheader">REV Edit Tracking</span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>

<div style="width:100%;max-width:720px;text-align:left;padding:0;margin:0 auto;font-size:90%;">
<form name="frm" method="post" action="/">
<table style="border:0;">
  <tr><td style="font-size:90%;">&nbsp;<br /><span style="color:red;"><span style="font-size:120%"><?=$etot?></span> unread and/or flagged edit(s).</span><br /><?=$ttot?> total edits in system.</td></tr>
  <tr><td>
<small>For the past:
  <select name="daysback" onchange="document.frm.submit()">
    <option value="1"<?=fixsel(1, $daysback)?>>1 day</option>
    <option value="3"<?=fixsel(3, $daysback)?>>3 days</option>
    <option value="7"<?=fixsel(7, $daysback)?>>7 days</option>
    <option value="21"<?=fixsel(21, $daysback)?>>21 days</option>
    <option value="90"<?=fixsel(90, $daysback)?>>90 days</option>
    <option value="180"<?=fixsel(180, $daysback)?>>180 days</option>
  </select>&nbsp;&nbsp;&nbsp;
  Filter:
  <select name="editfilter" id="editfilter" onchange="document.frm.submit()">
    <option value="0"<?=fixsel($editfilter, 0)?>>None</option>
    <option value="2"<?=fixsel($editfilter, 2)?>>Unread</option>
    <option value="1"<?=fixsel($editfilter, 1)?>>Flagged</option>
  </select>&nbsp;&nbsp;&nbsp;
  Edits: <?=$tot?>
</small>

  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="logid" value="0" />


<table style="width:700px;font-size:90%;border-collapse:separate;border-spacing:5px;">
<?

// no more sqn for users...
// John is userid 2, so hardcode check for that.
// Also, userid for myrev user editorcomments (myrevid -1) contains the userid of the person who last updated the ednote workspace.
switch($editfilter){
case 1: // flagged
  $sql = 'select userid, ifnull(revusername, myrevname), if(userid=2, 1, 2)
          from myrevusers
          where myrevid > 0 and (userid in (select distinct loguserid from editlogsviewed elv where elv.userid = '.$userid.' and flagged = 1))
          order by 3,2 ';
  break;
case 2: // unread
  $sql = 'select userid, ifnull(revusername, myrevname), if(userid=2, 1, 2)
          from myrevusers
          where myrevid > 0 and userid in (select distinct e.userid from editlogs e where e.editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)'.$sqladd.'
                                           and e.logid not in (select elv.logid from editlogsviewed elv where elv.userid = '.$userid.'))
          order by 3,2 ';

  break;
default: // All
  $sql = 'select userid, ifnull(revusername, myrevname), if(userid=2, 1, 2)
          from myrevusers
          where myrevid > 0 and (userid in (select distinct e.userid from editlogs e where e.editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)'.$sqladd.')
                              or userid in (select distinct loguserid from editlogsviewed elv where elv.userid = '.$userid.' and flagged = 1))
          order by 3,2 ';
  break;
}

$usrs = dbquery($sql);
$ni=0;
$nj=0;
while ($row = mysqli_fetch_array($usrs)) {
  $uid = $row[0];
  switch($editfilter){
  case 1: // flagged
    $sql = 'select count(logid)
            from editlogsviewed
            where userid = '.$userid.'
            and loguserid = '.$uid.'
            and flagged = 1';
    if($revblog==0) $sql.= ' and logid not in (select logid from editlogs where page = 27)';
    $cnt = rs($sql);
    $tot = $cnt[0];
    $last = 0;
    break;
  case 2: // unread
    $sql = 'select count(*), ifnull(max(editdate), 0)
            from editlogs
            where userid = '.$uid.'
            and editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
            and logid not in (select logid from editlogsviewed where userid = '.$userid.' and loguserid = '.$uid.')
            '.$sqladd.' ';

    $cnt = rs($sql);
    $tot = $cnt[0];
    $last= $cnt[1];
    break;
  default: // All
    $sql = 'select count(*), ifnull(max(editdate), 0)
            from editlogs
            where userid = '.$uid.'
            and editdate > DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY)
            and logid not in (select logid from editlogsviewed where userid = '.$userid.' and loguserid = '.$uid.' and flagged = 1)
            '.$sqladd.' ';

    $cnt = rs($sql);
    $tot = $cnt[0];
    $last= $cnt[1];
    $sql = 'select count(logid)
            from editlogsviewed
            where userid = '.$userid.'
            and loguserid = '.$uid.'
            and flagged = 1';
    if($revblog==0) $sql.= ' and logid not in (select logid from editlogs where page = 27)';
    $cnt = rs($sql);
    $tot+= $cnt[0];
    break;
  }

  $sql = 'select ifnull(max(editdate), 0)
          from editlogs
          where userid = '.$uid.$sqladd.'
          and logid in (select logid from editlogsviewed where userid = '.$userid.' and loguserid = '.$uid.' and flagged = 1)';
  $cnt = rs($sql);
  if($cnt[0] > $last) $last = $cnt[0];

  if($uid==$expuid){
    print('<tr><td><a class="comlink0" onclick="togltbl('.$uid.')"><img id="img'.$uid.'" src="/i/tbl_hide.png" alt="" border="0" style="width:'.((!$ismobile)?'1.0em':'.9em').';" title="expand" />&nbsp;<b>'.$row[1].'</b></a>&nbsp;Edits: '.$tot.'; Last: '.rtrim(date('n/j g:ia', strtotime(converttouserdate($last, $timezone))), 'm').'</td></tr>');
    print('<tr><td><div id="edts'.$uid.'" style="overflow:hidden;transition:height .6s;text-align:left;"><table class="gridtable" style="width:700px;">');
    print('<tr><td>what</td><td>comment</td><td>Chngs</td><td>WN</td><td>Com</td><td>Flg</td><td>when</td>');
    print('<td><input type="checkbox" name="chkdel'.$uid.'" onclick="chkall(this, '.$ni.','.$tot.')" /> chk</td>');
    print('</tr>');

    // contains blog..
    if($revblog==1){
    $sql = 'select b.title, b.abbr, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid, ifnull(elv.userid, 0) logviewed, ifnull(elv.flagged, 0) flagged,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, length(ifnull(v.commentary, \'\')) commlen,
                   ifnull(e.versdiff, \'-\') versdiff, ifnull(e.footdiff, \'-\') footdiff, ifnull(e.commfootdiff, \'-\') commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            inner join book b on (b.testament = e.testament and b.book = e.book)
            left join verse v on (v.testament = e.testament and v.book = e.book and v.chapter = e.chapter and v.verse = e.verse)
            left join editlogsviewed elv on (elv.userid = '.$userid.' and elv.loguserid = '.$uid.' and elv.logid = e.logid)
            where e.userid = '.$uid.'
            and e.page != 51
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv.flagged = 1)
            UNION ALL
            select \'x\' title, \'x\' abbr, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid, ifnull(elv.userid, 0) logviewed, ifnull(elv.flagged, 0) flagged,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, 0 commlen,
                   ifnull(e.versdiff, \'-\') versdiff, \'-\' footdiff, \'-\' commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            left join editlogsviewed elv on (elv.userid = '.$userid.' and elv.loguserid = '.$uid.' and elv.logid = e.logid)
            where e.userid = '.$uid.'
            and (e.page in (34, 37) or (e.page=7 and e.testament=0 and e.book=0) or (e.page in (310, 311) and e.testament=0 and e.book=0))
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv.flagged = 1)
            UNION ALL
            select b.blogtitle, \'x\' abbr, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid, ifnull(elv2.userid, 0) logviewed, ifnull(elv2.flagged, 0) flagged,
            ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, \'0\' commlen,
            \'-\' versdiff, \'-\' footdiff, \'-\' commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            inner join revblog b on (e.testament = 9 and b.blogid = e.book)
            left join editlogsviewed elv2 on (elv2.userid = '.$userid.' and elv2.loguserid = '.$uid.' and elv2.logid = e.logid)
            where e.userid = '.$uid.'
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv2.flagged = 1)
            UNION ALL
            select bb.bibauthor, \'x\' abbr, e.page, 0 testament, e.page book, e.chapter, 0 verse, e.editdate, e.logid, ifnull(elv2.userid, 0) logviewed, ifnull(elv2.flagged, 0) flagged,
            ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, \'0\' commlen,
            ifnull(e.versdiff, \'-\') versdiff, ifnull(e.footdiff, \'-\') footdiff, ifnull(e.commfootdiff, \'-\') commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            left join bibliography bb on (bb.bibid = e.page and bb.bibid = e.book and e.testament = 0 and e.chapter in (0,1) and e.verse = 0)
            left join editlogsviewed elv2 on (elv2.userid = '.$userid.' and elv2.loguserid = '.$uid.' and elv2.logid = e.logid)
            where e.userid = '.$uid.'
            and e.page = 51
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv2.flagged = 1)
            order by 8 desc ';   // 8 is e.editdate

    }else{
    $sql = 'select b.title, b.abbr, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid, ifnull(elv.userid, 0) logviewed, ifnull(elv.flagged, 0) flagged,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, length(ifnull(v.commentary, \'\')) commlen,
                   ifnull(e.versdiff, \'-\') versdiff, ifnull(e.footdiff, \'-\') footdiff, ifnull(e.commfootdiff, \'-\') commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            inner join book b on (b.testament = e.testament and b.book = e.book)
            left join verse v on (v.testament = e.testament and v.book = e.book and v.chapter = e.chapter and v.verse = e.verse)
            left join editlogsviewed elv on (elv.userid = '.$userid.' and elv.loguserid = '.$uid.' and elv.logid = e.logid)
            where e.userid = '.$uid.$sqladd.'
            and e.page not in (27, 51)
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv.flagged = 1)
            UNION ALL
            select \'x\' title, \'x\' abbr, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid, ifnull(elv.userid, 0) logviewed, ifnull(elv.flagged, 0) flagged,
                   ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, 0 commlen,
                   ifnull(e.versdiff, \'-\') versdiff, \'-\' footdiff, \'-\' commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            left join editlogsviewed elv on (elv.userid = '.$userid.' and elv.loguserid = '.$uid.' and elv.logid = e.logid)
            where e.userid = '.$uid.$sqladd.'
            and (e.page in (34, 37) or (e.page=7 and e.testament=0 and e.book=0) or (e.page in (310, 311) and e.testament=0 and e.book=0))
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv.flagged = 1)
            UNION ALL
            select bb.bibauthor, \'x\' abbr, e.page, 0 testament, e.page book, e.chapter, 0 verse, e.editdate, e.logid, ifnull(elv2.userid, 0) logviewed, ifnull(elv2.flagged, 0) flagged,
            ifnull(e.comment, \'-none-\') comment, e.whatsnew, e.logid, \'0\' commlen,
            ifnull(e.versdiff, \'-\') versdiff, ifnull(e.footdiff, \'-\') footdiff, ifnull(e.commfootdiff, \'-\') commfootdiff, ifnull(e.commdiff, \'-\') commdiff
            from editlogs e
            left join bibliography bb on (bb.bibid = e.page and bb.bibid = e.book and e.testament = 0 and e.chapter in (0,1) and e.verse = 0)
            left join editlogsviewed elv2 on (elv2.userid = '.$userid.' and elv2.loguserid = '.$uid.' and elv2.logid = e.logid)
            where e.userid = '.$uid.$sqladd.'
            and e.page = 51
            and (e.editdate >= DATE_ADD(UTC_TIMESTAMP(),INTERVAL -'.$daysback.' DAY) or elv2.flagged = 1)
            order by 8 desc ';   // 8 is e.editdate
    }
    //print('<br /><span style="display:block;color:'.$colors[7].';font-size:.7em;line-height:1.2em;">'.str_replace(crlf, '<br />', str_replace('<', '&lt;', str_replace('&', '&amp;', $sql))).'</span>');

    $logs = dbquery($sql); // this is the slow query...
    if(!mysqli_num_rows($logs)) print('<tr><td colspan="7">-none-</td></tr>');
    $ctr=1;
    while($row = mysqli_fetch_array($logs)){
      $important = ((left($row['comment'],1)=='!')?' bgcolor="#ffdddd"':'');
      if($editfilter==0 || ($editfilter==1 && $row['flagged']==1) || ($editfilter==2 && $row['logviewed']==0)){
        print('<tr>');
        print('<td style="white-space:nowrap;">'.$ctr++.') '.fixedit($row).'</td>');
        print('<td'.$important.'>'.(($row['page']==34 || $row['page']==37)?'-':$row['comment']).'</td>');

        $bgc = (($row['logviewed']>0)?'background-color:'.$colors[6].';':'');
        if($row['versdiff'] != '-' || $row['footdiff'] != '-' || $row['commdiff'] != '-' || $row['commfootdiff'] != '-'){
          print('<td style="text-align:center;'.$bgc.'" id="td'.$ni.'"><a onclick="olOpen(\'/viewuseredit.php?logid='.$row['logid'].'&idx='.$ni.'\',800, 600, '.(($ismobile)?1:0).');$(\'td'.$ni.'\').style.backgroundColor=\''.$colors[6].'\';" title="details"><img src="/i/magglass32.png" border="0" style="width:21px" alt="details" /></a></td>');
        }else{
          print('<td id="td'.$ni.'" style="text-align:center;'.$bgc.'"><a onclick="handleflag('.$row['logid'].','.$ni.',1);" class="comlink0" style="cursor:pointer;">None</a></td>');
        }

        print('<td style="text-align:center;">'.(($row['whatsnew']==1)?'<img src="/i/checkmark.png" width="20" alt="" />':'-').'</td>');
        print('<td style="text-align:center;">'.(($row['commlen']>0)?'<img src="/i/checkmark.png" width="20" alt="" />':'-').'</td>');

        $flgd = (($row['flagged']==1)?'background-color:#ffdddd;':'');
        print('<td id="eflag'.$ni.'" style="text-align:center;cursor:pointer;'.$flgd.'" onclick="handleflag('.$row['logid'].','.$ni.',0);"><img src="/i/flagedit.png" id="iflag'.$ni.'" style="width:'.((!$ismobile)?'1.0em':'.9em').';opacity:'.(($row['flagged']==1)?'100':'30').'%;" alt="" /></td>');
        print('<td style="white-space:nowrap;">'.rtrim(date('n/j g:ia', strtotime(converttouserdate($row['editdate'], $timezone))), 'm').'</td>');
        print('<td style="white-space:nowrap;"><input type="checkbox" name="logid'.$ni.'" id="logid'.$ni.'" value="'.$row['logid'].'" />');
        print('<input type="hidden" name="loguid'.$ni.'" id="loguid'.$ni.'" value="'.$uid.'" />');
        if($canedit==1){
          if($row['page']!=34 && $row['page']!=37)
            print('&nbsp;&nbsp;&nbsp;<a onclick="document.frmnav.temp.value='.$row['logid'].';return navigate('.$mitm.',19,'.$navstring.');" title="Click to edit"><img src="/i/edit.gif" style="width:'.((!$ismobile)?'1.0em':'.9em').';" alt="" /></a>');
          print('&nbsp;&nbsp;&nbsp;<a onclick="if(confirm(\'Are you sure you want to delete this edit?\')) {document.frm.logid.value='.$row['logid'].';document.frm.submit();}" title="Click to delete"><img src="/i/del.png" style="width:'.((!$ismobile)?'1.0em':'.9em').';" alt="" /></a>');
        }
        print('</td></tr>'.crlf);
        $ni++;
      }
    }
    print('<tr><td colspan="8">With checked: ');
    if($superman==1) print('<input type="submit" name="btnx" value="Delete" onclick="return validate(document.frm, \'del\');">');
    print('&nbsp;&nbsp;<small>Mark as:</small>');
    print('<input type="submit" name="btny" value="Unread" onclick="return validate(document.frm, \'umrk\');">');
    print('<input type="submit" name="btnz" value="Read" onclick="return validate(document.frm, \'mrk\');">');
    print('</td></tr>');

    print('</table></div></td></tr>');
  }else
    print('<tr><td style="padding-bottom:6px;"><a class="comlink0" onclick="showtbl('.$row[0].')"><img id="img'.$row[0].'" src="/i/tbl_show.png" alt="" border="0" style="width:'.((!$ismobile)?'1.0em':'.9em').';" title="expand" />&nbsp;<b>'.$row[1].'</b></a>&nbsp;Edits: '.$tot.'; Last: '.rtrim(date('n/j g:ia', strtotime(converttouserdate($last, $timezone))), 'm').'</td></tr>');

  $nj++;
}
if($nj==0) print('<tr><td colspan="8" style="color:red;">&nbsp;<br /><small>No edits meet your filtering criteria.</small></td></tr>');

?>
</table>

<input type="hidden" name="logcount" value="<?=$ni?>">
<!--<br />
<small><span style="color:red">(non-whatsnew logs older than 180 days are automatically deleted)</span></small>-->
    </td>
  </tr>
</table>
</form>
  </div>
<script>
  function validate(f, job){
    var havedel = 0;
    if(job != 'delv'){
      for(var i=0;i<<?=$ni?>; i++){
        if(f['logid'+i].checked){
          havedel = 1;
          break;
        }
      }
      if(havedel==0){
        alert('no edits are checked.');
        return false;
      }
    }
    switch(job){
    case 'del':
      if(confirm('Are you sure you want to delete the checked logs?')){
        f.oper.value = 'del';
        return true;
      }else
        return false;
      break;
    case 'umrk': // unmark
      if(confirm('Are you sure you want to \'unread\' the checked logs?')){
        f.oper.value = 'umrk';
        return true;
      }else
        return false;
      break;
    case 'mrk': // mark
      if(confirm('Are you sure you want to mark the checked logs as read?')){
        f.oper.value = 'mrk';
        return true;
      }else
        return false;
      break;
    case 'delv': // delete old editviewlogs
      if(confirm('Are you sure you want to delete old editviewlogs?')){
        f.oper.value = 'dlv';
        return true;
      }else
        return false;
      break;
    default:
      return false;
    }
  }

  function showtbl(id){
    document.frm.oper.value = 'usr';
    document.frm.logid.value = id;
    document.frm.submit();
  }

  var expuid = <?=$expuid?>;
  if(expuid>0){
    var thediv = $('edts'+expuid);
    thediv.style.height=(thediv.scrollHeight)+'px';
  }

  function togltbl(id){
    var tbl = $('edts'+id);
    var el = $('img'+parseInt(id));
    excoldiv('edts'+id);
    if(tbl.style.height == '0px'){
      el.src = '/i/tbl_show.png';
      el.title = 'expand';
    }else{
      el.src = '/i/tbl_hide.png';
      el.title = 'collapse';
    }
  }

  function chkall(el, strt, length){
    var checked = $('logid'+strt).checked;
    for(i=strt;i<(strt+length);i++){
      $('logid'+i).checked = !checked;
    }
    el.checked = !checked;
  }

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

  try{parent.goback+=2}
  catch(e){}
  //-->
</script>
<?
function fixedit($r){
  $str=' target="_blank" title="Click to view.  Opens new window/tab"';
  switch($r['page']){
  case 1: // verse/commentary
    $ret = '<span style="color:red;">vs/com:</span> <a href="/'.str_replace(' ', '', $r['abbr']).'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['abbr'].' '.$r['chapter'].':'.$r['verse'].'</a>';
    break;
  case 6: // book/commentary
    $ret = 'bk/com: <a href="/book/'.str_replace(' ', '', $r['title']).'/1"'.$str.'>'.$r['title'].'</a>';
    break;
  case 8: // appx/intro/wordstudy
    switch($r['testament']){
    case 2: // info
      $ret = 'info: <a href="/info/'.$r['book'].'/1"'.$str.'>'.$r['title'].'</a>';
      break;
    case 3: // appx
      $ret = 'appx: <a href="/appx/'.$r['book'].'/1"'.$str.'>'.$r['title'].'</a>';
      break;
    case 4: // wordstudy
      $ret = 'wordstudy: <a href="/word/'.str_replace(' ', '_', $r['title']).'/1"'.$str.'>'.$r['title'].'</a>';
      break;
    };
    break;
  case 27:
    $ret = 'blog: <a href="/blog/'.$r['book'].'/1"'.$str.'>'.left($r['title'], 15).'..</a>';
    break;
  case 34:
    $ret = 'Chrono: <a href="/chronology/'.$r['comment'].'"'.$str.'>'.friendlyyear($r['comment']).'</a>';
    break;
  case 37:
    $ret = 'Resource: <a href="/resources?factive=0&filter=~'.$r['comment'].'"'.$str.'>'.$r['comment'].'</a>';
    break;
  case 51:
    if($r['chapter']==1)
      $ret = '<a href="/bibliography"'.$str.'>Bibliography</a>';
    else
      $ret = '<a href="/abbreviations"'.$str.'>Abbreviations</a>';
    break;
  case 7:
  case 310:
  case 311:
    if($r['testament']==0 && $r['book']==0) $ret = (($r['page']==7)?'Editor':'Reviewer').' Wkspc';
    else{
      $prefix = (($r['page']==7)?'Ed':'Rv');
      switch($r['testament']){
      case 0:
      case 1:
        $ret = $prefix.'-note: <a href="/'.str_replace(' ', '', $r['abbr']).'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['abbr'].' '.$r['chapter'].':'.$r['verse'].'</a>'; break;
      case 3:
        $ret = $prefix.'-note: <a href="/appx/'.$r['book'].'/1"'.$str.'>'.$r['title'].'</a>'; break;
      case 4:
        $ret = $prefix.'-note: <a href="/word/'.str_replace(' ', '_', $r['title']).'/1"'.$str.'>'.$r['title'].'</a>'; break;
      }
    }
    break;
  default:
    $ret = 'unknown: '.$r['page'];
  }
  //print('<pre>');
  //print_r($r);
  //print('</pre>');
  return $ret;
}

function friendlyyear($yr){
  if(trim($yr)=='') return '';
  if($yr<0) $ret = abs($yr).' BC';
  else $ret = abs($yr).' AD';
  return $ret;
}



?>











