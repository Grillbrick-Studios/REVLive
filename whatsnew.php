<?php
if(!isset($page)) die('unauthorized access');

$stitle = 'What&rsquo;s New';
$msg = '';
?>
<span class="pageheader"><?=$stitle?></span>
  <table>
    <tr>
      <td>
        <p>Welcome to the &ldquo;What's New&rdquo; section of the REV website.
        <a onclick="toggleinfo('info');">More info <span id="moreless">&raquo;</span></a></p>
        <div id="info" style="height:0;overflow:hidden;transition:height .4s ease-in;">
        <p style="margin-top:0">Below you will see a list of some of the most recent edits and updates to the REV commentary.  If you click the &ldquo;Read More&rdquo; link at the end of each update, it will open the commentary page in a new browser window or tab.</p>
        <p>A blue dot <img src="/i/bluedot.png" height="18" width="18" alt="blue dot" /> appearing at the right of the &ldquo;What&rsquo;s New&rdquo; menu item indicates there has been an edit or addition since you last viewed this page.</p>
        <p>Each commentary edit or update is separated by a solid line and includes the time of entry, the book and verse reference, and a short statement about what has been added or changed in that commentary entry along with a preview of the commentary.</p>
        <p>We hope that this feature enables you to see the work that is currently being done on the REV commentary and to learn about God&rsquo;s Word along with us.</p>
        </div>
    </td></tr>
  <tr><td>
<?
  // this is the whatsnew code...
  $wncnt = ((isset($_REQUEST['temp']))?$_REQUEST['temp']:10);
  $wncnt = (($wncnt=='')?10:$wncnt);
  print('Select number of items'.(($ismobile==1)?'<br />':' ').'to view: ');

  // next form is necessary for android to show 'Go' button on keyboard
  ?>
  <form style="display:inline-block">
  <select name="wncntx" id="wncntx" onchange="document.frmnav.temp.value=this[this.selectedIndex].value;document.frmnav.submit();">
    <!--<option value="5"<?=fixsel(5,$wncnt);?>>5</option>-->
    <option value="10"<?=fixsel(10,$wncnt);?>>10</option>
    <option value="25"<?=fixsel(25,$wncnt);?>>25</option>
    <option value="50"<?=fixsel(50,$wncnt);?>>50</option>
    <option value="75"<?=fixsel(75,$wncnt);?>>75</option>
    <!--<option value="999"<?=fixsel(999,$wncnt);?>>all</option>-->
  </select></form><br />&nbsp;<br />
  <?
  $sql = 'select b.title, b.tagline, e.page, e.testament, e.book, e.chapter, e.verse, e.editdate, e.logid,
          ifnull(e.comment, \'-none-\') comment, v.commentary vcommentary, b.commentary bcommentary
          from editlogs e
          inner join book b on (b.testament = e.testament and b.book = e.book)
          left join verse v on (v.testament = e.testament and v.book = e.book and v.chapter = e.chapter and v.verse = e.verse)
          where e.whatsnew = 1 '.
          (($revws==0 && ($userid==0 || ($userid>0 && $showdevitems==0)))?'and e.testament != 4 ':'').'
          and e.comment != \'-\'
          order by e.editdate desc ';
  $logs = dbquery($sql);
  if(!mysqli_num_rows($logs)) print('<tr><td colspan="4">-none-</td></tr>');
  print('<table>');
  $ni=0;
  $charcut = (($ismobile)?500:1250);
  while(($row = mysqli_fetch_array($logs)) && ($ni < $wncnt)){
    print('<tr><td style="border-top:1px solid '.$colors[1].';">');
    if($row['testament']==4 && $revws==0 && $userid>0)
      print('<small><span style="color:red">This is not live. Only logged in users can see it</span></small><br />');
    print('<small>Date added or revised: '.converttouserdate($row['editdate'], $timezone).' '.gettimezoneabbr($timezone).'</small><br />');
    print('<em>'.fixedit($row).'</em> <b>[<span>'.$row['comment'].'</span>]</b></td></tr>');
    $commentary = (($row['page']==6)?$row['bcommentary']:$row['vcommentary']);
    if($commentary==null || $commentary=='') $commentary = 'No Commentary ... yet. ';
    $commentary = str_replace('[fn]', '', $commentary);
    $commentary = preg_replace('#<a id="toc(.*?)">(.*?)</a>#', '$2', $commentary); // remove toc markers

    $markeridx = strpos($commentary, '<a name="marker'.$row['logid'].'"></a>');
    // phptidy automatically adds an id attrbiute...  must also check for that.
    if($markeridx==false)
        $markeridx = strpos($commentary, '<a id="marker'.$row['logid'].'" name="marker'.$row['logid'].'"></a>');
    if($markeridx==false)
        $markeridx = strpos($commentary, '<a name="marker'.$row['logid'].'" id="marker'.$row['logid'].'"></a>');
    if($markeridx) $commentary = '<p>'.substr($commentary, $markeridx, 2000);
    else $commentary = left(trim($commentary), 2000);

    $commentary = processcommfordisplay($commentary, 1);

    $comlen = strlen($commentary);
    if ($comlen > ($charcut+1)) {
      $commentary = truncateHtml($commentary, $charcut,'...', false, true,'<a href="'.geturl($row, $markeridx).'" target="'.(($inapp)?'_self':'_blank').'" title="click to read more">Read More</a>');
    }
    print('<tr><td>'.$commentary.((right($commentary, 4)=='</p>')?'':'<br />&nbsp;').'</td></tr>'.crlf);
    $ni++;
  }
  print('</table>');
  logview(20,0,0,0,0);

  $arwnblog   = explode(';', (isset($_COOKIE['rev_wnblog']))?$_COOKIE['rev_wnblog']:((time()-(3*86400)).';'.(time()-(3*86400)).';'.(time()-(3*86400))));
  if(sizeof($arwnblog)==2) array_push($arwnblog, (time()-(3*86400)));
  $arwnblog[0] = time();
  $arwnblog = join(';', $arwnblog);

?>
    </td></tr>
  <tr><td>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" /></td></tr></table>
  <table style="font-size:90%">
  <tr><td colspan="2">&nbsp;</td></tr>
</table>
<div style="margin:0 auto;text-align:center;"><small>(<a onclick="scrolltotop()">top</a>)</small></div>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script>
  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId = 'view';
</script>

<script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
<script>
  findappx.startNodeId = 'view';
  findappx.apxidx = [<?=loadapxids()?>];
</script>

<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
</script>

<script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
<script>
  findstrongs.startNodeId = 'commtext';
  findstrongs.ignoreTags.push('noparse');
  findstrongs.lexicon = prflexicon;
</script>

<script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
<script>
  findwordstudy.startNodeId = 'view';
</script>

<script>
  addLoadEvent(findcomm.scan);
  addLoadEvent(findappx.scan);
  addLoadEvent(findvers.scan);
  addLoadEvent(findstrongs.scan);
  addLoadEvent(findwordstudy.scan);

  function toggleinfo(id){
    excoldiv(id);
    var div = $(id);
    if(div.style.height=='0px'){
      $('moreless').innerHTML='&raquo;';
    }else{
      $('moreless').innerHTML='&laquo;';
    }
  }

  setCookie('rev_wnblog', '<?=$arwnblog?>', cookieexpiredays);

  var toffset=0;
</script>

<?
   function fixedit($r){
     global $inapp;
     if($inapp)
       $str=' target="_self" title="Click to view."';
     else
       $str=' target="_blank" title="Click to view.  Opens new window/tab"';
     switch($r['page']){
     case 1: // verse/commentary
       $ret = 'Commentary for <a href="/'.str_replace(' ', '-', $r['title']).'/'.$r['chapter'].'/'.$r['verse'].'/1"'.$str.'>'.$r['title'].' '.$r['chapter'].':'.$r['verse'].'</a>';
       break;
     case 6: // book/commentary
       $ret = 'Commentary for book: <a href="/book/'.str_replace(' ', '-', $r['title']).'/1"'.$str.'>'.$r['title'].'</a>';
       break;
     case 8: // appx/info/word
       $ret = '<a href="/'.(($r['testament']==2)?'info':(($r['testament']==3)?'appx':'wordstudy')).'/'.str_replace(' ', (($r['testament']==4)?'_':'-'), (($r['testament']!=4)?$r['book']:$r['title'])).'/1"'.$str.'>'.(($r['tagline']==null)?$r['title']:$r['tagline']).'</a>';
       break;
     default:
       $ret = 'unknown';
     }
     return $ret;
   }

   function geturl($r, $idx){
     switch($r['page']){
     case 1: // verse/commentary
       $ret = '/'.str_replace(' ', '-', $r['title']).'/'.$r['chapter'].'/'.$r['verse'];
       break;
     case 6: // book/commentary
       $ret = '/book/'.str_replace(' ', '-', $r['title']);
       break;
     case 8: // appx/info/word
       $ret = '/'.(($r['testament']==2)?'info':(($r['testament']==3)?'appx':'word')).'/'.str_replace(' ', (($r['testament']==4)?'_':'-'), (($r['testament']==4)?$r['title']:$r['book']));
       break;
     default:
       $ret = 'unknown';
     }
     if($idx) $ret.='/'.$r['logid']; else $ret.= '/1';
     return $ret;
   }

?>

