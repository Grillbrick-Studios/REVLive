<?
if(!isset($page)) die('unauthorized access');

$topicid = $book;

if($userid>0 && $resedit==1 && $resshowedit==1 && (isset($_REQUEST['delres']) && $_REQUEST['delres'] != 0)){
  $delres = $_REQUEST['delres'];
  if(strpos($delres, '~')>0){
    $parts = explode('~', $delres);
    $sql = 'delete from topic_assoc where topicid='.$topicid.' and testament = '.$parts[0].' and book = '.$parts[1].' and chapter = '.$parts[2].' and verse = '.$parts[3].' ';
  }else
    $sql = 'delete from topic_assoc where topicid='.$topicid.' and resourceid='.$delres.' ';
  //print($sql);
  $delete = dbquery($sql);
}

$stitle = 'REV Topics';

print('<a id="top"></a>'.crlf);
print('<span class="pageheader">'.$stitle.'</span>');

if($resedit==1 && $topicid==0){
  print('<p style="text-align:center;margin-bottom:0;color:'.$colors[7].'"><small>(Hi, '.$username.'. &nbsp; Edit <input type="checkbox" name="resshowedit" value="1"'.fixchk($resshowedit).' onclick="setSessionCookie(\'rev_resedit\', ((this.checked)?1:0));location.reload();">');
  print(' <a href="topicexport.php" title="Export Topic List"><img src="/i/docx'.$colors[0].'.png" alt="MSWord" style="border:0;width:14px;" /></a>)');
  if($resshowedit==1){
    print(' New Topic'.edittopiclink('elnkx',-1));
    print('<br /><span style="display:inline-block;text-align:right;">');

    $row = rs('select count(*) from topic where topicid in (select topicid from topic_assoc)');
    $tot = $row[0];
    print('Topics with linked items: '.$row[0].'</a><br />');

    $row = rs('select count(*) from topic where topicid not in (select topicid from topic_assoc) and (description like \'%topic on%\' or description like \'%topics on%\' or description like \'%topic of%\' or description like \'%topics of%\')');
    $tot+= $row[0];
    print('Empty but linked Topics: '.$row[0].'</a><br />');

    $row = rs('select count(*) from topic where topicid not in (select topicid from topic_assoc) and description is null or (description not like \'%topic on%\' and description not like \'%topics on%\' and description not like \'%topic of%\' and description not like \'%topics of%\')');
    $tot+= $row[0];
    print('Unpopulated Topics: '.$row[0].'</a><br />');

    print('Total Topics: '.$tot.'</a><br />');
    $ollink = '<a onclick="olOpen(\'/viewtopicsforrestype.php?restype=[[rt]]\','.(($ismobile==1)?$screenwidth+20:600).', 500);" title="details">';
    $row = rs('select count(*) from topic_assoc where resourceid = 0');
    print('Comtry/Appx entries: '.str_replace('[[rt]]', 'comapx', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'spiritandtruth\')');
    print('SandT videos: '.str_replace('[[rt]]', 'sandtvid', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'spiritandtruth_vf\')');
    print('SandT VF videos: '.str_replace('[[rt]]', 'sandtvfvid', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'biblicalunitarian\')');
    print('BU videos: '.str_replace('[[rt]]', 'buvid', $ollink).$row[0].'</a><br />');
    //$row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'podbean\')');
    //print('Podbean audios: '.str_replace('[[rt]]', 'podbean', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where source = \'castos\')');
    print('Castos audios: '.str_replace('[[rt]]', 'castos', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where resourcetype = 4)');
    print('Seminar teachings: '.str_replace('[[rt]]', 'seminar', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where resourcetype = 5)');
    print('Articles: '.str_replace('[[rt]]', 'article', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid > 0 and resourceid in (select resourceid from resource where resourcetype = 7)');
    print('Library items: '.str_replace('[[rt]]', 'library', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc where resourceid < 0');
    print('Playlists: '.str_replace('[[rt]]', 'playlist', $ollink).$row[0].'</a><br />');
    $row = rs('select count(*) from topic_assoc');
    print('Total items associated: '.$row[0].'<br />');
  }
  print('</span></small></p>');
}

if($topicid==0){
  print('<div style="width:100%;max-width:840px;text-align:center;padding:0;margin:0 auto;font-size:96%;">');
  print('<p style="margin-bottom:0;text-align:center;">Welcome to the Topics section of the REV website. ');
  print('<a onclick="expandcollapsediv(\'topinst\')">More <span id="moreless">&raquo;</span></a></p>
  <div id="topinst" style="text-align:left;height:0;padding:3px;margin:0;overflow:hidden;transition:height .4s ease-in;">
    <h3 style="text-align:center;">Welcome to the REV Topical Index!</h3>
    <p>Below is an alphabetical listing of topics that pertain to the Christian faith, the Bible, and life. By clicking on a topic, you will then be able to see a list of audio/video teachings and commentary entries in the REV Bible that relate to the topic. We hope that this topical index will help you in your study of the Bible, in fellowships and teaching, and in sharing the truth with friends, family, and people you meet.</p></div>');
  // <p style="text-align:center"><img src="/i/underconstruction.png" alt="under construction" border="0" style="width:84px;"></p>
  // <p>Please pardon our dust as we are currently working to update this section of the site.</p>

  print('<p style="text-indent:0;font-size:120%;text-align:center;margin:6px 0 0 0;">');

  // get list of first letter of all topics
  $sql = 'select distinct substr(topic, 1, 1) letter from topic order by 1 ';
  $let = dbquery($sql);
  $str = '';
  while($row = mysqli_fetch_array($let)){
    $str.= strtoupper($row[0]);
  }
  //print($str);
  //$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  // print nav menu
  for ($i=0; $i<strlen($str); $i++) {
    $chr = substr($str, $i, 1);
    print('<a id="start_'.$chr.'" onclick="scrolltopos(this.id, \'dest_'.$chr.'\');">&nbsp;'.$chr.'&nbsp;</a> ');
  }
  print('</p>&nbsp;<div id="topics" class="col'.(($screenwidth>800)?3:(($screenwidth>470)?2:1)).'container" style="width:96%;margin:0 auto;text-align:left;">');

  $sql = 'select t.topicid, t.topic, ifnull(t.description, \'\') description, t.topviews,
          (select count(*) from topic_assoc ta where ta.topicid = t.topicid) cnt
          from topic t order by t.sqn, t.topic ';

  $tpx = dbquery($sql);
  $ni=0;
  $lastletter='-';
  while($row = mysqli_fetch_array($tpx)){
    $chr = strtoupper(substr($row['topic'], 0, 1));
    if($chr!=$lastletter){
      if($chr!='-' && $chr!='A') print('</div>');
      print('<div style="page-break-inside: avoid;">');
      print('<span style="display:block;'.(($chr=='A')?'':'margin-top:12px;').'font-size:120%;border-bottom:1px solid '.$colors[7].';"><a id="dest_'.$chr.'" onclick="scrolltopos(this.id, \'start_'.$chr.'\');">&nbsp;'.$chr.'&nbsp;</a></span>');
      $lastletter = $chr;
    }
    if($userid>0 && $resedit==1 && $resshowedit==1){
      print(edittopiclink('elnk'.$ni,$row['topicid']));
      if($row['cnt']>0 || $row['description']!='') print('<a href="/topic/'.$row['topicid'].'/'.str_replace(' ', '_', $row['topic']).'">');
      print($row['topic']);
      if($row['cnt']>0 || $row['description']!='') print('</a>');
      print(' <span style="font-size:86%;color:'.$colors[7].'">('.$row['cnt'].')</span>');
      print(' <span style="font-size:86%;color:'.$colors[7].'">(v:'.$row['topviews'].')</span><br />');
      $ni++;
    }else if($row['cnt'] > 0 || $row['description']!=''){
      print('<a href="/topic/'.$row['topicid'].'/'.str_replace(' ', '_', $row['topic']).'">'.$row['topic'].'</a>');
      if($row['cnt'] > 0) print(' <span style="font-size:86%;color:'.$colors[7].'">('.$row['cnt'].')</span>');
      print('<br />');
      $ni++;
    }
  }
  print('</div></div>');
  logview($page,0,0,0,0);
}else{
  // we have a topicid, show results
  print('<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:96%;">');
  print('<form name="fte2" method="post" action="/">');
  print('<p style="text-indent:0;margin:5px 0;text-align:center;"><a href="/topics">Back to Topic List</a>');
  if($resedit==1) print(' <small>edt</small><input type="checkbox" name="resshowedit" value="1"'.fixchk($resshowedit).' onclick="setSessionCookie(\'rev_resedit\', ((this.checked)?1:0));location.reload();">');
  print('</p>');
  $row = rs('select topic, description from topic where topicid = '.$topicid.' ');
  $topic = (($row)?$row[0]:'unknown');
  $descr = (($row)?$row[1]:'');
  print('<h3 style="font-size:130%;margin-bottom:0;">Topic: &ldquo;'.$topic.'&rdquo;'.(($resedit==1 && $resshowedit==1)?' '.edittopiclink('elnkx',$topicid):'').'</h3>');
  if($descr!='') print('<div id="topicdescr" style="text-align:left;">'.$descr.'</div>');
  // rob, you need to handle if resource is active...
  $sql = 'select topicid, resourceid, testament, book, chapter, verse
          from topic_assoc
          where topicid = '.$topicid.'
          and resourceid in (select resourceid from resource where active = 1 )
          order by sqn, 2,3,4,5,6';
  $sql = 'select topicid, resourceid, testament, book, chapter, verse
          from topic_assoc
          where topicid = '.$topicid.'
          order by sqn, 2,3,4,5,6';
  $tpx = dbquery($sql);
  $row = rs('select FOUND_ROWS();');
  if($row[0]==0)
    print('');
  else
    print('<p>Following are relevant sections of the REV Commentary and/or STF video and audio teachings.</p>');
  $ni=0;$nj=0;
  $imginitsiz = (($screenwidth<520)?160:210);
  while($row = mysqli_fetch_array($tpx)){
    if($row[1]<0){
      // show playlist
      $sql = 'select playlisttitle, description, pltypeid, ifnull(thumbnail, \'\') thumbnail from playlist where playlistid = '.-$row[1].' ';
      $rpl = rs($sql);
      print('<table class="gridtable" style="border-top:3px double '.$colors[3].';width:100%;text-align:left;margin:0 0 20px 0;">');
      print('<tr><td>');
      print('<p style="margin-top:0;text-indent:0;margin-bottom:0;">Playlist: <strong><a href="/play/'.str_replace(' ', '_', $rpl['playlisttitle']).'" target="_blank">'.$rpl['playlisttitle'].'</a></strong>');
      if($userid>0 && $resedit==1 && $resshowedit==1) print(' <a onclick="if(confirm(\'Are you sure you want to disassociate\nthis playlist from this topic?\')) {document.fte2.delres.value='.$row[1].';document.fte2.submit();}" title="delete this playlist from this topic"><img src="/i/del.png" style="width:1.0em;" alt="" /></a>');
      print('</td></tr><tr><td>');
      if($rpl['thumbnail']!='')
        print('<img src="'.$rpl['thumbnail'].'" style="width:'.$imginitsiz.'px;max-width:100%;float:left;margin:0 5px 0 0;" alt="playlist thumbnail" />');
      else
        print('<img src="/i/stf_audio.png" style="width:60px;max-width:100%;float:left;margin:0 5px 0 0;" alt="resource thumbnail" />');
      $desc = ((left($rpl['description'], 3)=='<p>')?'<p style="margin-top:0">'.substr($rpl['description'], 3):$rpl['description']);
      print($desc);
      print('</td></tr></table>');
    }else if($row[1]>0){
      // show resource
      $sql = 'select publishedon, resourceid, resourcetype, title, description,
             source, playlistid, keywords, identifier, externalurl, duration, resviews,
             ifnull(thumbnail, \'nopic\') thumbnail, active, finalized, editcomment, edituserid, content
             from resource where resourceid = '.$row[1].' ';

      $rrow = rs($sql);
      if($rrow){
        print('<hr style="margin:0 0 1px 0;padding:0;" />');
        print(assembleresource($rrow, $nj, $page));
      }else{
        print('Resource not found for resourceid: '.$row[1]);
      }
    }else{
      // show beginning of commentary/appx
      $sql = 'select b.title, b.tagline, v.commentary vcommentary, b.commentary bcommentary
              from book b
              left join verse v on (v.testament = '.$row['testament'].' and v.book = '.$row['book'].(($row['chapter']>0)?' and v.chapter = '.$row['chapter'].' and v.verse = '.$row['verse']:'').')
              where b.testament = '.$row['testament'].' and b.book = '.$row['book'].(($row['chapter']>0)?' and v.chapter = '.$row['chapter'].' and v.verse = '.$row['verse']:'').' '.
              (($revws==0 && ($userid==0 || ($userid>0 && $showdevitems==0)))?'and b.testament != 4 ':'');

      $rrw = rs($sql);
      print('<table class="gridtable" style="width:100%;margin:0 0 20px 0;">');
      $charcut = (($ismobile)?250:500);
      if($rrw){
        print('<tr><td style="border-top:3px double '.$colors[3].';border-bottom:1px solid '.$colors[3].';text-align:left;">'.getsummary($row));
        if($userid>0 && $resedit==1 && $resshowedit==1){
          $sref = $row['testament'].'~'.$row['book'].'~'.$row['chapter'].'~'.$row['verse'];
          print(' <a onclick="if(confirm(\'Are you sure you want to disassociate\nthis commentary/appx from this topic?\')) {document.fte2.delres.value=\''.$sref.'\';document.fte2.submit();}" title="delete this item from this topic"><img src="/i/del.png" style="width:1.0em;" alt="" /></a>');
        }
        print('</td></tr>');
        $commentary = (($row['chapter']==0 && $row['verse']==0)?$rrw['bcommentary']:$rrw['vcommentary']);
        if($commentary==null || $commentary=='') $commentary = 'No Commentary ... yet. ';
        $commentary = str_replace('[fn]', '', $commentary);
        $commentary = preg_replace('#<a id="toc(.*?)">(.*?)</a>#', '$2', $commentary); // remove toc markers
        $commentary = processcommfordisplay($commentary, 1);

        if (strlen($commentary) >= $charcut) {
          $commentary = truncateHtml($commentary, $charcut,'...', false, true, getsummary($row, 1));
        }
        print('<tr><td><img src="/i/biblecommentary.jpg" style="float:left;width:'.$imginitsiz.'px;margin:0 5px 0 0;" alt="thumbnail" />'.$commentary.((right($commentary, 4)=='</p>')?'':'<br />&nbsp;').'</td></tr>'.crlf);
        $ni++;
      }else{
        print('<tr><td><span style="color:red;">no data..</span></td></tr>');
      }
      print('</table>');
    }
    $nj++;
  }
  if($descr=='' && $nj==0) print('<tr><td><span style="color:red;">&nbsp;<br />Sorry, no items are associated with this topic.</span></td></tr>');
?>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="book" value="<?=$topicid?>" />
  <input type="hidden" name="delres" value="0" />
  <input type="hidden" name="temp" value="" />
</form>
<?
  logview($page,0,0,$topicid,0);
  if($userid==0) $update = dbquery('update topic set topviews = topviews+1 where topicid = '.$topicid.' ');
}
print('</div>');
?>
<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script>
  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId = 'view';
</script>

<script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
<script>
  findbcom.startNodeId  = 'view';
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

<script src="/includes/findtopic.min.js?v=<?=$fileversion?>"></script>
<script>
  findtopics.startNodeId = 'topicdescr';
</script>

<script>
  addLoadEvent(findcomm.scan);
  addLoadEvent(findbcom.scan);
  addLoadEvent(findappx.scan);
  addLoadEvent(findvers.scan);
  addLoadEvent(findstrongs.scan);
  addLoadEvent(findtopics.scan);

  function expandcollapsediv(id){
    excoldiv(id); // in misc.js
    var div = $(id);
    if(div.style.height=='0px'){
      $('moreless').innerHTML='&raquo;';
    }else{
      $('moreless').innerHTML='&laquo;';
    }
  }
  var goback=0;

</script>
<?

//
//
//
function edittopiclink($idx,$tid){
  global $ismobile;
  return '<input type="image" src="/i/edit.gif" class="edtlinkon'.(($ismobile)?' edtlinkmob':' edtlinkpc').'" onclick="olOpen(\'/topicedit.php?topicid='.$tid.'\',600, 600);return false;" alt="edit" />';
}

function getsummary($r, $readmore=0){
  global $inapp;
  $title = getbooktitle($r['testament'], $r['book'], 0);
  if($r['testament'] < 2){
    if($r['chapter']==0 && $r['verse']==0){ // book commentary
      $htitle = $title;
      $href='/book/'.str_replace(' ','',$title);
      $pref = 'Commentary on the book of ';
    }else{
      $htitle = $title.' '.$r['chapter'].':'.$r['verse'];
      $href='/'.str_replace(' ','',$title).'/'.$r['chapter'].'/'.$r['verse'];
      $pref = 'Commentary on ';
    }
  }else{
    $htitle = $title;
    switch($r['testament']){
    case 2:
      $href='/intro/'.$r['book'];
      $pref = 'Introduction: ';
      break;
    case 3:
      $href='/appendix/'.$r['book'];
      $pref = 'Appendix: ';
      break;
    case 4:
      $href='/wordstudy/'.str_replace(' ', '_', $title);
      $pref = 'Word Study: ';
      break;
    }
  }
  $href.='/1'; // adding $gedit for "Close Window" button.
  if($readmore==1)
    $ret = '<a href="'.$href.'" target="'.(($inapp)?'_self':'_blank').'">Read more..</a>';
  else
    $ret = $pref.'<a href="'.$href.'" target="'.(($inapp)?'_self':'_blank').'">'.$htitle.'</a>';
  return $ret;

}

?>
