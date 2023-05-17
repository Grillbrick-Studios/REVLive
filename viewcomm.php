<?php
if(!isset($page)) die('unauthorized access');

//
// This is page 4, used for viewing bible commentary
//
$stitle = 'REV Commentary';

print('<a id="top"></a>');

$showedit = (($edit==1)?'inline':'none');
if($test != -1){
  if($book != 0 && $chap>0){
    $sql = 'select count(*) from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' ';
    $row = rs($sql);
    $numverses = $row[0];
    $nv = 0;
    $btitle = getbooktitle($test,$book,0);
    $bshortabbr = getshortbookabbr($test,$book);
    $babbr = getbooktitle($test,$book,1);
    $bhabbr= str_replace(' ', '', $babbr);
    print('<table style="width:96%;margin:0 auto;border-collapse:separate;border-spacing:5px;"><tbody>');
    if($chap==1){
      // provide links to export commentary for entire book
      $sql = 'select ifnull(tagline,concat(\'The Book of \', title)) tagline from book where testament = '.$test.' and book = '.$book.' ';
      $roww = rs($sql);
      print('<tr><td colspan="2"><span class="tagline">'.$roww['tagline'].'</span>');
      if($showpdflinks) print(getexportlinks('comm',$test,$book,0,0, 0));
      print('</td></tr>');
    }
    print('<tr><td colspan="2"><h3 style="margin-bottom:0;">');

    $sql = 'select chapters from book where testament = '.$test.' and book = '.$book.' ';
    $row = rs($sql);
    $numchaps = $row[0];
    //print('numchaps: '.$numchaps.' ');

    if($versnavwhat>0 && $numchaps>1){
      $htm ='<a class="comlink'.$commlinkstyl.'" onclick="excoldiv(\'div_chapters_x\');">';
      $htm.=(($book==19)?'Psalm '.$chap:$btitle.(($numchaps>1)?' Chapter '.$chap:''));
      $htm.='</a>';
    }else{
      $htm = (($book==19)?'Psalm '.$chap:$btitle.(($numchaps>1)?' Chapter '.$chap:'')); //$btitle.' Chapter '.$chap;
    }
    print($htm);

    if($showpdflinks) print(getexportlinks('comm',$test,$book,$chap,0, 0));
    print('</h3></td></tr>');

    if($versnavwhat>0 && $numchaps>1){
      $htm='<tr><td colspan="2"><div id="div_chapters_x" style="'.(($viewversnav)?'':'height:0;').'overflow:hidden;transition:height .4s ease-in;">';
      $htm.='<span style="font-size:80%">Go to '.(($book==19)?'Psalm':'Chapter').':'.(($ismobile)?'</span>':'').'<br />';
      for($ni=1;$ni<=$numchaps;$ni++){
        $htm.='|<a href="/comm/'.$bhabbr.'/'.$ni.'"'.(($ni==$chap)?' class="bghilite"':'').'>'.substr('00'.$ni, (($book==19)?-3:-2)).'</a> ';
      }
      $htm.='|'.(($ismobile)?'':'</span>').'<br />'.crlf;
      //$htm.='<span style="font-size:80%">Bible: <a href="/'.$bhabbr.'/'.$chap.'">'.$btitle.' '.$chap.'</a></span><br />';
      $htm.='</div></td></tr>';
      print($htm);
    }

    print('<tr><td colspan="2"><span style="font-size:80%">Go to verse:'.(($ismobile)?'</span>':'').'<br />');
    for($ni=1;$ni<=$numverses;$ni++){print('|<a id="navchap'.$ni.'" onclick="scrolltopos(this.id, \'c'.$chap.'_v'.$ni.'\');">'.substr('00'.$ni, (($chap==119)?-3:-2)).'</a> ');}
    print('|'.(($ismobile)?'':'</span>').'<br /><span style="font-size:80%">Go to Bible: <a href="/'.$bhabbr.'/'.$chap.'">'.$btitle.' '.$chap.'</a></span><br />&nbsp;</td></tr>'.crlf);
    //print('|'.(($ismobile)?'':'</span>').'<br />&nbsp;</td></tr>'.crlf);

    $sql = 'select verse, comfootnotes, commentary from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' order by chapter, verse ';
    $com = dbquery($sql);
    while($row = mysqli_fetch_array($com)){
      $commentary = $row['commentary'];
      $commentary = (($commentary)?$commentary:'-');
      $arcomfn = array();
      $comfncnt= 0;
      $comfootnotes= $row['comfootnotes'];
      // handle new footnotes
      $comfootnotes = getfootnotes($test, $book, $chap, $row['verse'], 'com');
      //

      $commentary = preg_replace('#<p><strong>([^<]*?)</strong><br />#', '<h5 style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:25px;">$1</h5><p style="margin-top:0;padding-top:0">', $commentary);
      $commentary = processcommfordisplay($commentary, 1);
      if(substr($commentary, 0, 3) == '<p>'){
        // modify first <p> tag so there's no top margin
        $commentary = '<p style="margin-top:0;padding-top:0;">'.substr($commentary, 3);
      }
      $commentary = processcomfootnotes($arcomfn, $commentary, $comfootnotes, $comfncnt, $row['verse']);

      $toplink = '<a class="toplink" id="top_c'.$chap.'_v'.$row['verse'].'" onclick="scrolltotop(this.id);">(top)</a>';
      $nocom=0;
      if($commentary=='-') $nocom=1;
      $havefootnotes = ($comfncnt>0);
      $elink = (($userid>0 && $canedit==1)?editlink('elnk'.$nv,$showedit,$mitm,1,$test,$book,$chap,$row['verse']):'');

      if($ismobile){
        print('<tr>');
        print('<td colspan="2" style="text-align:left;font-size:95%"><a id="c'.$chap.'_v'.$row['verse'].'"></a>'.$babbr.' '.$chap.':'.$row['verse'].(($nocom)?' '.$elink.$commentary.' '.$toplink:' '.$elink).'</td>');
        print('</tr>');
        if(!$nocom){
          print('<tr><td style="width:3%;">&nbsp;</td><td style="text-align:left;width:97%;max-width:300px;">'.$commentary);
          displaycomfootnotes($comfncnt, $arcomfn, $row['verse']);
          if($havefootnotes==0 && $nocom==0 && right($commentary, 4) != '</p>') print('<br />');
          print(appendresources($test, $book, $chap, $row['verse']));
          print('&nbsp;'.$toplink.'</td></tr>');
        }
      }else{
        print('<tr>');
        print('<td style="text-align:left;vertical-align:top;white-space:nowrap;"><a id="c'.$chap.'_v'.$row['verse'].'"></a><small>'.$bshortabbr.' '.$chap.':'.$row['verse'].'</small></td>');
        print('<td style="text-align:left;vertical-align:top;max-width:300px;">'.$elink.$commentary); // weird max_width...
        displaycomfootnotes($comfncnt, $arcomfn, $row['verse']);
        if($havefootnotes==0 && $nocom==0 && right($commentary, 4) != '</p>') print('<br />');
        if($nocom==0) print('&nbsp;');
        print(appendresources($test, $book, $chap, $row['verse']));
        print('&nbsp;'.$toplink);
        print('</td></tr>');
      }
      print(crlf);
      $nv++;
    }
    print('<tr><td>&nbsp;</td><td style="width:100%">&nbsp;</td></tr>');
    print('</tbody></table>');
    print('<br /><div style="margin:0 auto;text-align:center;">'.str_replace('prevlinkid', 'prvlnkid', $prevlink).'&nbsp;&nbsp;&nbsp;<a id="verybottom" onclick="scrolltotop(\'verybottom\');">top</a>&nbsp;&nbsp;&nbsp;'.str_replace('nextlinkid', 'nxtlnkid', $nextlink).'</div>');
    logview($page,$test,$book,$chap,$vers);
  }else{
    if($test>-1){
      if($book!=0){
        $stitle = getbooktitle($test,$book,0);
        print('<h3>'.$stitle.'</h3>Please click on a chapter:<br /><br />');
        $sql = 'select chapters from book where testament = '.$test.' and book = '.$book.' ';
        $row = rs($sql);
        $chaps = $row[0];
        for($ni=1;$ni<=$chaps;$ni++){
          print('<a href="/Commentary/'.str_replace(' ','-',$stitle).'/Chapter'.$ni.'">Chapter '.$ni.'</a><br />'.$mobilespc.crlf);
        }
      }else{
        print('<br />Please click on a book:<br /><br />');
        $sql = 'select book, title from book where testament = '.$test.' and active = 1 order by sqn ';
        $apx = dbquery($sql);
        while($row = mysqli_fetch_array($apx)){
          print('<a href="/Commentary/'.str_replace(' ','-',$row['title']).'">'.$row['title'].'</a><br />'.$mobilespc);
        }
      }
    }else{
    print('<br />Please click on "Introduction" or a book.');
    }
  }
}else{
  mainmenu();
}

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
    findbcom.startNodeId = 'view';
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
    findstrongs.startNodeId = 'view';
    findstrongs.ignoreTags.push('noparse');
    findstrongs.lexicon = prflexicon;
  </script>

  <script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
  <script>
    findwordstudy.startNodeId = 'view';
  </script>

<script>
  addLoadEvent(findcomm.scan);
  addLoadEvent(findbcom.scan);
  addLoadEvent(findappx.scan);
  addLoadEvent(findvers.scan);
  addLoadEvent(findstrongs.scan);
  addLoadEvent(findwordstudy.scan);

  if(<?=$vnav?>>0)
    setTimeout('scrolltopos(\'toptop\', \'c<?=$chap?>_v<?=$vnav?>\');', <?=(($ismobile)?1000:400)?>);
  else
    if(1==2) setTimeout('$("srchtext").focus()', 180);   // disabled...annoying

<?if($versnavwhat>0 && $numchaps>1){?>

  var dv = $('div_chapters_x');
  dv.style.height = dv.scrollHeight+'px';
  if(!prfviewversnav) dv.style.height=0;

<?}?>
  var toffset = 0; // top offset for scrolltopos()
  var goback=0;

</script>


