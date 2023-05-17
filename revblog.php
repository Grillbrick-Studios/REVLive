<?php
if(!isset($page)) die('unauthorized access');
//
// Here, $test is the identifier for blogs in general: 9
// $book holds the blogid
// I SHOULD have made the blogs another testament, like appx's, info's, and ws's
// ..but I didn't
//
$stitle = 'REV Blog';
$msg = '';
?>
<span class="pageheader"><?=$stitle?></span>
  <table>
    <tr>
      <td>
        <p>Welcome to the &ldquo;REV Blog&rdquo; section of the REV website.
        <a onclick="toggleinfo('info');">More info <span id="moreless">&raquo;</span></a></p>
        <div id="info" style="height:0;overflow:hidden;transition:height .4s ease-in;">
        <p style="margin-top:0">The REV Bible has been an ongoing and developing project since work on it began in 2000.
          There is a lot of work done on the REV that is not indicated under the "What's New" section.
          But many meaningful changes are being made which readers and supporters might appreciate knowing
          about. Furthermore, this blog will provide updates on areas where current work is being done.
          It is a proper venue to describe the various translation decisions, perspectives on particular
          passages, and even struggles between multiple translation options. In addition, it will often
          include insights concerning why certain translation decisions have been made.</p>
        </div>
    </td></tr>
  <tr><td>
<?
  // this is the whatsnew code...
  $wncnt = ((isset($_REQUEST['temp']))?$_REQUEST['temp']:10);
  $wncnt = (($wncnt=='')?10:$wncnt);
  print('Select number of blog entries to view: ');
  ?>
  <select name="wncntx" id="wncntx" onchange="document.frmnav.temp.value=this[this.selectedIndex].value;document.frmnav.submit();">
    <!--<option value="5"<?=fixsel(5,$wncnt);?>>5</option>-->
    <option value="10"<?=fixsel(10,$wncnt);?>>10</option>
    <option value="25"<?=fixsel(25,$wncnt);?>>25</option>
    <option value="50"<?=fixsel(50,$wncnt);?>>50</option>
    <option value="75"<?=fixsel(75,$wncnt);?>>75</option>
    <!--<option value="999"<?=fixsel(999,$wncnt);?>>all</option>-->
  </select><br />
<?
   if($userid>0) print('<a onclick="return navigate(\'9\',\'27\', 9, -1, 0, 0);" title="New blog entry">New blog entry <img src="/i/edit.gif" alt="" /></a>');
?>
  &nbsp;<br />
  <?
  $sql = 'select blogid, blogdate, blogtitle, blogtext, active
          from revblog
          where '.(($userid>0)?'1=1 ':'active = 1 and blogtext != \'No blog text\' ').'
          order by blogdate desc ';
  $logs = dbquery($sql);
  print('<table>');
  if(!mysqli_num_rows($logs)) print('<tr><td colspan="4">&nbsp;<br/>Sorry, no blogs have been entered yet.</td></tr>');
  $ni=0;
  $charcut = (($ismobile || $inapp)?500:1250);
  while(($row = mysqli_fetch_array($logs)) && ($ni < $wncnt)){
    print('<tr><td style="border-top:1px solid '.$colors[1].';"><small>Date added: '.converttouserdate($row['blogdate'], $timezone).' '.gettimezoneabbr($timezone).'</small><br /><b><span class="findvers_ignore">'.$row['blogtitle'].'</span></b>');
    if($userid>0) print(editlink('elnk'.$ni,$showedit,$mitm,27,9,$row['blogid'],0,0).(($row['active'])?'':notifynotpublished));
    print('</td></tr>');
    $commentary = $row['blogtext'];
    if($commentary==null || $commentary=='') $commentary = 'No Blog text. ';
    $commentary = left(trim($commentary), 2000);
    if(left($commentary, 3)==='<p>')
      $commentary = '<p style="margin-top:0;padding-top:0;">'.substr($commentary, 3);

    if (strlen($commentary) >= $charcut) {
      $commentary = truncateHtml($commentary, $charcut,'...', false, true,'<a href="/blog/'.$row['blogid'].'/'.str_replace(' ', '-', $row['blogtitle']).'" title="click to read more" target="'.(($inapp)?'_self':'_blank').'">Read More</a>');
    }
    print('<tr><td>'.$commentary.((right($commentary, 4)=='</p>')?'':'<br />&nbsp;').'</td></tr>'.crlf);
    $ni++;
  }
  print('</table>');
  logview(25,0,0,0,0);

  $arwnblog   = explode(';', (isset($_COOKIE['rev_wnblog']))?$_COOKIE['rev_wnblog']:((time()-(3*86400)).';'.(time()-(3*86400)).';'.(time()-(3*86400))));
  if(sizeof($arwnblog)==2) array_push($arwnblog, (time()-(3*86400)));
  $arwnblog[1] = time();
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
<div style="margin:0 auto; text-align:center;"><small>(<a onclick="return scrolltotop()">top</a>)</small></div>

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

</script>


