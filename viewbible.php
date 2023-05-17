<?
if(!isset($page)) die('unauthorized access');
$stitle = (($ismobile)?'REV Bible':'Revised English Version');
if($inapp){
  $closebtn = (($glogid==1||$showclosetab==1)?'<button onclick="history.go(-1);" class="gobackbutton">Go Back</button>':'');
}else
  $closebtn = (($glogid==1||$showclosetab==1)?'<button onclick="window.close();" class="gobackbutton">Close Tab</button>':'');

print('<a id="top"></a>'.crlf);
if($closebtn!='') print('<div id="pagetop">'.$closebtn.'</div>'.crlf);
$chapcount=0;
$haveedwork=0;
$havepeerwork=0;
$canaddpeernote=0;
if($test != -1){
  if($book != 0){
    printbible();
    $toplink = '&nbsp;&nbsp;&nbsp;<a id="verybottom" onclick="return scrolltotop(\'verybottom\');">top</a>&nbsp;&nbsp;&nbsp;';
    print('<br /><table style="margin:0 auto;"><tr><td>'.str_replace('linkid', 'linkx', $prevlink).'</td><td>'.$toplink.'</td><td>'.str_replace('linkid', 'linkx', $nextlink).'</td></tr></table>');

    logview($page,$test,$book,$chap,$vers);
  }
  else{ //we have no book
    print('<br />Please click on an item.');
    if($userid==1){
      print('&nbsp;&nbsp;<a class="toplink" href="/exportsql.php?test='.$test.'&amp;book=0&amp;chap=0&amp;vers=0">sql</a>');
    }
    print('<br /><br />');
    $sql = 'select book, title from book where testament = '.$test.' and active = 1 order by sqn ';
    $books = dbquery($sql);
    while ($row = mysqli_fetch_array($books)) {
      switch($test){
      case 0:
      case 1:
        print('<a href="/'.str_replace(' ', '-',$row['title']).'" rel="nofollow">'.$row['title'].'</a><br />'.$mobilespc);
        break;
      case 2: //intro
        print('<a href="/info/'.$row['book'].'" rel="nofollow">'.$row['title'].'</a><br />'.$mobilespc);
        break;
      case 3: //appx
        print('<a href="/appx/'.$row['book'].'" rel="nofollow">'.$row['title'].'</a><br />'.$mobilespc);
        break;
      case 4: //ws
        print('<a href="/word/'.$row['book'].'" rel="nofollow">'.$row['title'].'</a><br />'.$mobilespc);
        break;
      }
    }
  }
}
else{
  if ($vnav==-2) {
    print('<div class="col1container"><noscan>');
    if(strtolower($srchbook)!='help' && strlen($srchbook)>2){
      //if($arqs[sizeof($arqs)-1] == 'all') array_pop($arqs);
      if(isset($arqs[1]) && $arqs[1] == 'all') array_pop($arqs);
      $srchbook = join('%20', $arqs);
      $href = '/srch/?srchtest=2&srchwhat=1&srchhow=1&srchtxt='.$srchbook;
      print('<h3 style="color:red">searching..</h3></div></div><script>location.href="'.$href.'"</script></body></html>');
    }else{
      print('<div style="text-align:center;"><div style="display:inline-block;text-align:left;">');
      print('<h3 style="text-align:center;">Help with Navigation by Typing</h3>');
      if(strlen($srchbook)<3)
        print('<span style="color:red">Sorry, unknown book abbreviation: &ldquo;'.$srchbook.'&rdquo;.</span><br /><br />');

    if($ismobile) print('Tap <a id="tobooks" onclick="return scrolltopos(this.id, \'booknames\');">here</a> to jump down to available book names.<br />&nbsp;<br />');
    ?>
    <small>Here's what you can do:</small>
    <table border="1" cellpadding="2" cellspacing="0" style="font-size:80%;">
      <tr><th style="text-align:left">To navigate to:</th><th style="text-align:left">Type this:</th><th style="text-align:left">Example:</th></tr>
      <tr><td>Help</td><td>h</td><td>&ldquo;h&rdquo;</td></tr>
      <tr><td>Bible chapter</td><td>abbr + chapter</td><td>&ldquo;jo 3&rdquo;</td></tr>
      <tr><td>Bible verse</td><td>abbr + chapter + verse</td><td>&ldquo;jo 3 16&rdquo;</td></tr>
      <tr><td>Verse commentary</td><td>abbr + chapter + verse + c</td><td>&ldquo;jo 3 16 c&rdquo;</td></tr>
      <tr><td>Browse commentary</td><td>c + abbr + chapter</td><td>&ldquo;c jo 3&rdquo;</td></tr>
      <tr><td>Browse commentary at verse</td><td>c + abbr + chapter + verse</td><td>&ldquo;c jo 3 16&rdquo;</td></tr>
      <tr><td>Book commentary</td><td>(bc or bk) + abbr</td><td>&ldquo;bc matt&rdquo;</td></tr>
      <tr><td>Appendix</td><td>a + appx#</td><td>&ldquo;a 8&rdquo;</td></tr>
      <tr><td>&nbsp;</td><td>a + word in title</td><td>&ldquo;a calvin&rdquo;</td></tr>
      <?if($revws==1 || ($userid>0 && $showdevitems==1)){?>
      <tr><td>Word study</td><td>w + word</td><td>&ldquo;w pneuma&rdquo;</td></tr>
      <tr><td>REV Blog</td><td>b</td><td>&ldquo;b&rdquo;</td></tr>
      <?}?>
      <tr><td>What&rsquo;s New</td><td>wn</td><td>&ldquo;wn&rdquo;</td></tr>
      <tr><td>Preferences</td><td>p</td><td>&ldquo;p&rdquo;</td></tr>
      <tr><td>Search</td><td>s</td><td>&ldquo;s&rdquo;</td></tr>
    </table>
    <small>After you have typed a command, press [ENTER] or [RETURN] or [GO],<br />whatever is appropriate for the computer or device you're using.</small>


    <p></p><a id="booknames"></a><small>Here are the available book abbreviations:<br/>(case does not matter)</small>
    <table cellpadding="0" cellspacing="0" style="font-size:80%;border:0;">
      <tr><th style="text-align:left">Book</th><th style="text-align:left;padding-left:6px;">Abbreviations</th></tr>

    <?
    $sql = 'select book, title, ifnull(aliases, abbr) from book where testament in (0,1) order by testament, book ';
    $books = dbquery($sql);
    while ($row = mysqli_fetch_array($books)) {
      if($row[0]==40) print('<tr><td colspan="2">&nbsp;</td></tr>');
      $araliases = explode('~', $row[2]);
      array_shift($araliases); // toss first empty element
      print('<tr><td style="vertical-align:top; white-space:nowrap;font-weight:bold;">'.$row[1].'</td><td style="padding-left:6px;">');
      array_pop($araliases); // pop the last empty element
      array_multisort(array_map('strlen', $araliases), $araliases);
      for ($ni=0, $size=count($araliases);$ni<$size;$ni++) {
        print($araliases[$ni]);
        if($ni<($size-1)) print(', ');
      }
      print('</td></tr>'.crlf);
    }
    print('</table>');
    print('<br />&nbsp;&nbsp;&nbsp;(<a id="helpbottom" onclick="scrolltotop(this.id);'.((!$ismobile)?' setTimeout(\'$(\\\'srchtext\\\').focus();\', 200)':'').';return false;">top</a>)</div></div></noscan></div>');
  }
  }else{
    mainmenu();
  }
}

function ucfirstletter($t){
  $art = str_split($t);
  if(is_numeric($art[0]))
    return $art[0].' '.ucfirst(substr($t,1));
  else
    return ucfirst($t);
}
?>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<!-- this is for the scripture references in footnotes -->
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  findvers.ignoreTags = ['h1', 'h2', 'h3', 'h4', 'noscan'];
  addLoadEvent(findvers.scan);

  if(vnav>0) setTimeout('scrolltopos(\'toptop\', \'c'+chap+'_v'+vnav+'\');', ((ismobile)?400:200));
  if(vhed>0) setTimeout('scrolltopos(\'toptop\', \'head_c'+chap+'_v'+vhed+'\');', ((ismobile)?400:200));

  function comtoggle(){
    setshowcommlinks(1-prfshowcommlinks);
    location.reload();
  }

  for(var idx=0;idx<<?=$chapcount?>;idx++){
    var dv = $('div_versesfor_'+idx);
    dv.style.height = dv.scrollHeight+'px';
    if(!prfviewversnav) dv.style.height=0;
  }

  var comtimer=0;
  var oldqry='';

  <?if($myrevid > 0){?>

  var canaddpeernote = <?=$canaddpeernote?>;
  function showhilightdiv(qry, clink, hvrevcom){
    event.stopPropagation();
    <?
      $row = rs('select if(length(ifnull(notes, \'\')) > 0, 1, 0) from myrevusers where myrevid = '.$myrevid.' ');
    ?>
    var hvmrws = <?=$row[0]?>;
    var hvedws = <?=(($haveedwork==1)?1:0)?>;
    var hvpnws = <?=(($havepeerwork==1)?1:0)?>;
    var rdiv = $('myrevdiv');
    if(rdiv.style.display=='block' && oldqry==qry){
      myrevhidePopup();
      if(comtimer==1) window.open(clink.slice(0,-2), ((prfcommnewtab==1)?'_blank':'_self')); //location.href=clink;
      setTimeout('comtimer=0', 370);
      return;
    }
    oldqry = qry;
    link = clink.replace(' ', '');
    if(comtimer==1) location.href=clink; // does this ever get hit?
    var classname = 'hl_'+qry;
    hlit = document.getElementsByClassName(classname)[0].getAttribute('data-hlite');

    rdiv.innerHTML=gethilightdivcontents(qry, hlit, clink, hvrevcom, hvmrws,0,<?=(($editorcomments==1)?1:0)?>,hvedws,<?=(($peernotes>0)?1:0)?>,hvpnws);
    rdiv.style.visibility='hidden';
    rdiv.style.display='block';
    var dims = gethilightdivcoords(rdiv);
    rdiv.style.top  = dims.top+'px';
    rdiv.style.left = dims.left+'px';
    rdiv.style.visibility='visible';
    rdiv.style.opacity=1;
    comtimer=1;
    setTimeout('comtimer=0', 370);
  }

  function reloadmyrevnotes(qry){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        var mnnote = ret.marginnote;
        try{
          var mndiv = $('mn_'+qry);
          mndiv.innerHTML = mnnote.replace(/\[br\]/g, '<br />');
          mndiv.style.display = ((mnnote=='')?'none':'block');
          var mrimg = $('mrimg_'+qry);
          mrimg.style.display=((ret.myrevnotes!='')?'inline':'none');
          mrimg.setAttribute('data-havemrnote', ((ret.myrevnotes!='')?1:0));
        }catch(e){}
      }
    }
    xmlhttp.open('GET', '/jsonmyrevtasks.php?task=data&ref='+qry,true);
    xmlhttp.send();
  }

  function reloadeditnotes(qry){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        var note = ret.editnote;
        var resolved = ret.resolved;
        try{
          var mnspan = $('bn_'+qry);
          mnspan.innerHTML = ((resolved==1)?'&reg; ':'')+note.replace(/\[br\]/g, '<br />');
          mnspan.style.display=(((ednotesshowall==1 || resolved==0) && note!='')?'block':'none');
          var edimg = $('edimg_'+qry);
          edimg.style.display=(((ednotesshowall==1 || resolved==0) && ret.editdetails!='')?'inline':'none');
          edimg.setAttribute('data-haveednote', ((ret.editdetails!='')?1:0));
        }catch(e){} //document.location.reload()}
      }
    }
    xmlhttp.open('GET', '/jsonmyrevtasks.php?task=edata&ref='+qry,true);
    xmlhttp.send();
  }

  function reloadpeernotes(qry){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        var note = ret.peernote;
        var resolved = ret.resolved;
        try{
          var pnspan = $('pn_'+qry);
          pnspan.innerHTML = ((resolved==1)?'&reg; ':'')+note.replace(/\[br\]/g, '<br />');
          pnspan.style.display=(((peernotesshowall==1 || resolved==0) && note!='')?'block':'none');
          var peerimg = $('peerimg_'+qry);
          peerimg.style.display=(((peernotesshowall==1 || resolved==0) && ret.peerdetails!='')?'inline':'none');
          peerimg.setAttribute('data-havepeernote', ((ret.peerdetails!='')?1:0));
        }catch(e){} //document.location.reload()}
      }
    }
    xmlhttp.open('GET', '/jsonmyrevtasks.php?task=pdata&ref='+qry,true);
    xmlhttp.send();
  }

  function disablelinkdblclick(){
    var links = document.getElementsByClassName('comlink<?=$commlinkstyl?>');
    for(var i=0; i<links.length; i++) {
      links[i].setAttribute('ondblclick', 'event.stopPropagation();event.preventDefault();');
    }
  }
  setTimeout('disablelinkdblclick()', 700);

  setTimeout('initmyrevpopup(\'myrevdiv\');', 500);

<?}?>
  var toffset = 0;  // used in scrolltopos()

</script>


<?
function printbible(){
  global $mitm, $page, $test, $book, $chap, $vers, $edit, $vnav;
  global $viewcols, $parachoice, $useoefirst, $chapcount;
  global $userid, $myrevid, $canedit, $appxedit, $versebreak, $ismobile, $commnewtab;
  global $arfn, $fncnt, $vsfncnt, $colors, $showcommlinks, $ucaseot, $hilitecolors;
  global $commlinkstyl, $viewversnav, $showpdflinks, $versnavwhat, $linkcommentary;
  global $myrevclick, $myrevkeys, $showdevitems, $editorcomments, $ednotesshowall, $viewedcomments, $viewmrcomments, $haveedwork;
  global $peernotes, $viewpeernotes, $peernotesshowall, $havepeerwork, $canaddpeernote;

  $showedit = (($edit==1)?"inline":"none");
  $arfn = array();
  $nv = 0;
  $fncnt = 0;
  $row = rs('select chapters, outlinepublished from book where testament = '.$test.' and book = '.$book.' ');
  $numchaps = $row[0];
  $outlinepublished = $row[1];
  $oldchap = -1;
  $btitle = getbooktitle($test,$book, 0);
  $babbr = getbooktitle($test,$book, 1);
  $bhabbr= str_replace(' ', '', $babbr);
  $haveedwork=0;
  $havepeerwork=0;
  $canaddpeernote=0;
  if($myrevid==0){
    $sql = 'select v.chapter, v.verse, v.versetext, ifnull(v.heading,\'noscript\') superscript,
            (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
            v.paragraph, v.style, v.footnotes,
            if(length(ifnull(v.commentary, \'\')) > 0,1 ,0) haverevcom,
            0 myrevhighlightcolor,
            \'\' myrevmarginnote,
            0 havemyrevnote,
            \'\' ednotemarginnote,
            0 haveednotenote,
            0 resolved,
            \'\' peernotemarginnote,
            0 havepeernote,
            0 peerresolved
            from verse v
            where v.testament = '.$test.' and v.book = '.$book.' ';
    if($chap>0) $sql .= 'and v.chapter = '.$chap.' ';
    $sql .= 'order by v.chapter, v.verse ';
  }else{
    $sql = 'select v.chapter, v.verse, v.versetext, ifnull(v.heading,\'noscript\') superscript,
                   (select count(*) from outline oln where oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1) headcount,
                   v.paragraph, v.style, v.footnotes, if(length(ifnull(v.commentary, \'\')) > 0, 1, 0) haverevcom,
                   ifnull(rd.highlight, 0) myrevhighlightcolor,
                   ifnull(rd.marginnote,\'\') myrevmarginnote,
                   if(length(ifnull(rd.myrevnotes, \'\')) > 0, 1, 0) havemyrevnote,
                   ifnull(en.editnote,\'\') ednotemarginnote,
                   if(length(ifnull(en.editdetails, \'\')) > 0, 1, 0) haveednotenote,
                   ifnull(en.resolved, 0) resolved,
                   ifnull(pn.editnote,\'\') peernotemarginnote,
                   if(length(ifnull(pn.editdetails, \'\')) > 0, 1, 0) havepeernote,
                   ifnull(pn.resolved, 0) peerresolved
            from verse v
            left join myrevdata rd on (rd.myrevid = '.$myrevid.' and rd.testament = v.testament and rd.book = v.book and rd.chapter = v.chapter and rd.verse = v.verse)
            left join editnotes en on (en.testament = v.testament and en.book = v.book and en.chapter = v.chapter and en.verse = v.verse'.(($ednotesshowall==1)?'':' and en.resolved = 0 ').')
            left join peernotes pn on (pn.testament = v.testament and pn.book = v.book and pn.chapter = v.chapter and pn.verse = v.verse'.(($peernotesshowall==1)?'':' and pn.resolved = 0 ').')
            where v.testament = '.$test.' and v.book = '.$book.' ';
    if($chap>0) $sql .= 'and v.chapter = '.$chap.' ';
    $sql .= 'order by v.chapter, v.verse ';
    if($editorcomments==1){
      $row = rs('select length(ifnull(notes,\'\')) from myrevusers where myrevid = -1 ');
      $haveedwork = (($row[0]>0)?1:0);
    }
    if($peernotes>0){
      $row = rs('select length(ifnull(peerworknotes,\'\')) from myrevusers where myrevid = '.$myrevid.' ');
      $havepeerwork = (($row[0]>0)?1:0);
      $row = rs('select 1 from book_peer where testament = '.$test.' and book = '.$book.' and userid = '.$userid.' ');
      $canaddpeernote = (($row)?1:0);
    }
  }
  //print($sql.'<br />');
  $verses = dbquery($sql);
  $prevstyle=0;
  $inlistbegin=0;
  $needclosingptag=0;
  $comimg = '<img src="/i/commentary'.$colors[0].'.png" style="width:'.(($ismobile)?'1':'.8').'rem;" />';

  $bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');
  $arbmks = explode(';', $bookmarks??'');

  $htm='';
  while($row = mysqli_fetch_array($verses)){
    $chapter = $row['chapter'];
    $versnum = $row['verse'];
    $havepara = $row['paragraph'];
    $style = $row['style'];
    $footnotes = $row['footnotes'];
    //$footnotes = getfootnotes($test, $book, $chapter, $versnum, 'vrs');
    if($chapter != $oldchap){
      //
      // starting a new chapter
      //
      if($oldchap != -1){
        $htm.=(($prevstyle==1 && $needclosingptag==1)?'</p>':'').'</div>'.crlf;
        print(tidify($htm));
        if($viewcols>1) print('<div style="margin:0 auto;text-align:center;"><a id="navchapbot'.$oldchap.'" onclick="return scrolltopos(this.id, \'chap'.$oldchap.'\');" class="toplink">top of '.(($book==19)?'Psalm':'chapter').'</a></div>');
        displayfootnotes($fncnt, $arfn, 0, $oldchap);
        print('<hr />'.crlf);
        $htm='';
      }
      if($chapter == 1){
        //
        $sql = 'select ifnull(tagline,concat(\'The Book of \', title)) tagline, length(ifnull(commentary, \'\')) commlength, outlinepublished from book where testament = '.$test.' and book = '.$book.' ';
        $roww = rs($sql);
        $htm.='<span class="tagline">'.$roww['tagline'].'</span>';
        $infolink = '';
        if($roww['commlength'] > 0 || $canedit==1){
          $infolink.='<a href="/book/'.str_replace(' ', '_',$btitle).'" title="Click for Book Introduction" target="'.(($commnewtab==1)?'_blank':'_self').'">Introduction</a>';
        }
        if($roww['outlinepublished']==1 || ($canedit==1 || $appxedit==1)){
          $infolink.=(($infolink=='')?'':' | ').'<a href="/outline/'.str_replace(' ', '_', $btitle).'">Outline</a>';
        }
        if($userid>0 && $canedit==1) $htm.=editlink('elnk'.$nv,$showedit,$mitm,6,$test,$book,$chapter,$versnum);
        if($showpdflinks){
          $htm.=getexportlinks('bible',$test,$book,0,0, 1);
        }
        $nv++;
        $htm.=crlf;
        $htm.= (($infolink=='')?'':'<br /><small>'.$infolink.'</small>');
        if($chap<1 && $numchaps>1){
          $htm.='<br /><span style="font-size:80%">'.(($book==19)?'Psalm':'Chapter').':'.(($ismobile)?'</span>':'').'<br />';
          for($ni=1;$ni<=$numchaps;$ni++){$htm.='|<a id="navchap'.$ni.'" onclick="scrolltopos(this.id, \'chap'.$ni.'\');">'.substr('00'.$ni, (($book==19)?-3:-2)).'</a> ';}
          $htm.='|'.(($ismobile)?'':'</span>').crlf;
        }
      }
      $htm.='<h1 class="chaphead" style="margin-bottom:10px"><a id="chap'.$chapter.'"></a>'; // for top nav to a chapter
      $htm.='<a class="comlink'.$commlinkstyl.'" onclick="excoldiv(\'div_versesfor_'.$chapcount.'\');">';
      $htm.=(($book==19)?'Psalm '.$chapter:$btitle.(($numchaps>1)?' Chapter '.$chapter:''));
      $htm.='</a>';
      if($ismobile&&$linkcommentary==1) $htm.='&nbsp;<a onclick="comtoggle();" title="toggle commentary icons"><img src="/i/commentary'.(($showcommlinks)?'':'_offf').$colors[0].'.png" alt="" style="width:18px;" /></a>';
      if($chap<1&&$numchaps>1){
        $htm.='&nbsp;&nbsp;<a id="top_chap'.$chapter.'" class="toplink" onclick="scrolltotop(this.id);">top</a>';
      }
      if($showpdflinks){
        $htm.=getexportlinks('bible',$test,$book,$chapter,0, 1);
      }
      $htm.='</h1>'.crlf;

      $sql = 'select count(verse) from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chapter.' ';
      $roww = rs($sql);
      $numverses = $roww[0];

      $htm.='<div id="div_versesfor_'.$chapcount.'" style="'.(($viewversnav)?'':'height:0;').'overflow:hidden;transition:height .4s ease-in;">';
      if($versnavwhat>0 && $numchaps>1){
        $htm.='<span style="font-size:80%">Go to '.(($book==19)?'Psalm':'Chapter').':'.(($ismobile)?'</span>':'').'<br />';
        if($chap>0) $htm.='|<a href="/'.$bhabbr.'/all">all</a> '; else $htm.='|<a href="/'.$bhabbr.'/'.$chapter.'">'.(($book==19)?'Ps ':'Ch ').$chapter.'</a> ';
        for($ni=1;$ni<=$numchaps;$ni++){
          if($chap<1){
            $htm.='|<a id="navc'.$chapter.'chap'.$ni.'" onclick="scrolltopos(this.id, \'chap'.$ni.'\');">'.substr('00'.$ni, (($book==19)?-3:-2)).'</a> ';
          }else{
            $htm.='|<a href="/'.$bhabbr.'/'.$ni.'"'.(($ni==$chap)?' class="bghilite"':'').' rel="nofollow">'.substr('00'.$ni, (($book==19)?-3:-2)).'</a> ';
          }
        }
        $htm.='|'.(($ismobile)?'':'</span>').'<br />'.crlf;
      }
      if($versnavwhat!=1){
        $htm.='<span style="font-size:80%">Go to verse:'.(($ismobile)?'</span>':'').'<br />';
        for($ni=1;$ni<=$numverses;$ni++){$htm.='|<a id="nav_c'.$chapter.'_v'.$ni.'" onclick="scrolltopos(this.id, \'c'.$chapter.'_v'.$ni.'\');">'.substr('00'.$ni, (($chapter==119)?-3:-2)).'</a> ';}
        $htm.='|'.(($ismobile)?'':'</span>').'<br />'.crlf;
      }
      $htm.='<span style="font-size:80%">Go to <a href="/comm/'.$bhabbr.'/'.$chapter.'">Commentary on '.$babbr.' '.$chapter.'</a></span><br />&nbsp;'.crlf;
      $htm.='</div>';

      $oldchap = $chapter;
      $htm.=crlf.'<div id="bblviewcols'.$chapter.'" class="col'.$viewcols.'container">'.crlf;
      $chapcount++;
    } // end of starting a new chapter

    // initialize everything
    $pvhead='';$pvpara='';$pvvnum='';$pvvimg='';$pvedit='';$pvvnav='';$pvvers='';$pvpost='';$pvprinted=0;
    $pvmyrevnotedot='';$pvmyrevmarginnote='';
    $pvpeernotenotedot='';$pvpeernotemarginnote='';
    $pvednotenotedot='';$pvednotemarginnote='';
    $versbr='';$beginstub='';$endstub='';$havevnum=0;$prevstyleflag=0;$vsfncnt=0;

    $pvvnav='<a id="c'.$chapter.'_v'.$versnum.'"></a>';
    $verse = trim($row['versetext']);
    $verse = str_replace('<em> </em>', ' ', $verse);
    $ismdash = (($versebreak!=1 && right($verse, 7)=='&mdash;')?1:0);

    $haverevcom    = $row['haverevcom'];                                           // does a verse have commentary associated with it?
    $blnshowcomlink= (($linkcommentary==1 && ($haverevcom==1 || $myrevid>0))?1:0); // do we have content for the commlink icon at the end of verses?
    $blnlinkverse  = (($showcommlinks==0 && $blnshowcomlink)?1:0);                 // does a verse have something to link to?
    $havemyrevnote = (($showcommlinks==0 && $linkcommentary==1 && $myrevid>0 && $row['havemyrevnote']==1)?1:0); // does a verse have a myrev note?
    $haveednotenote= (($linkcommentary==1 && $row['haveednotenote'])?1:0);
    $havepeernote  = (($linkcommentary==1 && $row['havepeernote'])?1:0);
    $myrevhighlightcolor= (($linkcommentary==1)?$row['myrevhighlightcolor']:0);

    // for handling myrev, peernotes, and ednotes
    $loc = $myrevid.'|'.$test.'|'.$book.'|'.$chapter.'|'.$versnum;

    // this one disables colors if the view myrev notes toggle is off
    $hlspan= (($myrevid>0)?'<span class="hlspan hl_'.$loc.'" style="background-color:'.(($viewmrcomments==1)?$hilitecolors[$myrevhighlightcolor]:'transparent').';" data-hlite="'.$myrevhighlightcolor.'">':'<span>');
    //$hlspan= (($myrevid>0)?'<span class="hlspan hl_'.$loc.'" style="background-color:'.$hilitecolors[$myrevhighlightcolor].';" data-hlite="'.$myrevhighlightcolor.'">':'<span>');

    //if($myrevid>0 && $myrevclick==1 && ($viewedcomments==1 || $viewmrcomments==1)){
    if($myrevid>0 && $myrevclick==1){
      $jscomlink = '/'.$bhabbr.'/'.$chapter.'/'.$versnum.'/1';
      $comlink = '<a onclick="showhilightdiv(\''.$loc.'\',\''.$jscomlink.'\','.$haverevcom.');" class="comlink0" title="'.(($myrevhighlightcolor>0)?$myrevkeys[$myrevhighlightcolor]:'').'">';
    }else{
      $comlink = '<a href="/'.str_replace(' ', '-',$btitle).'/chapter'.$chapter.'/'.$versnum.'" class="comlink'.$commlinkstyl.'" target="'.(($commnewtab==1)?'_blank':'_self').'" title="Click for Commentary">';
    }
    if($versebreak==1){
      $verse = str_replace('[pg]', ' ', $verse);
      $verse = str_replace('[hp]', ' ', $verse);
      $verse = str_replace('[hpbegin]', ' ', $verse);
      $verse = str_replace('[hpend]', ' ', $verse);
      $verse = str_replace('[lb]', ' ', $verse);
      $verse = str_replace('[listbegin]', ' ', $verse);
      $verse = str_replace('[listend]', ' ', $verse);
      $verse = str_replace('[bq]', ' ', $verse);
      $verse = str_replace('[/bq]', ' ', $verse);
      $verse = str_replace('[br]', ' ', $verse);
      $verse = str_replace('[separator]', ' ', $verse);
      $style=1;
    }

    //
    // myrev dot and margin note
    // also isbookmarked
    //
    if($linkcommentary==1 && $myrevid>0){
      if($row['myrevmarginnote'] != ''){
        $pvmyrevmarginnote = str_replace('[br]', '<br />', $row['myrevmarginnote']);
        $pvmyrevmarginnote = '<span id="mn_'.$loc.'" class="marginnote mnotedarr" style="display:'.(($viewmrcomments==1)?'block':'none').';cursor:pointer;" onclick="rlightbox(\'note\',\''.$loc.'\',1);">'.$pvmyrevmarginnote.'</span>';
      }else $pvmyrevmarginnote = '<span id="mn_'.$loc.'" class="marginnote mnotedarr" style="display:none;cursor:pointer;" onclick="rlightbox(\'note\',\''.$loc.'\',1);"></span>';
      $bmk = $page.','.$test.','.$book.','.$chapter.',nav'.$versnum;
      $isbmked = ((in_array($bmk, $arbmks))?1:0);
      $pvmyrevnotedot = '<img id="mrimg_'.$loc.'" data-havemrnote="'.$havemyrevnote.'" data-isbmked="'.$isbmked.'" src="/i/skinnybluedot.png" style="display:'.(($havemyrevnote==1 && $viewmrcomments==1)?'inline':'none').';width:.4em;" alt="You have a note for this verse." />';
    }

    //
    // peernote dot and margin note
    //
    if($linkcommentary==1 && $myrevid>0 && $peernotes>0){
      if($row['peernotemarginnote'] != ''){
        $pvpeernotemarginnote = (($row['peerresolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $row['peernotemarginnote']);
        $pvpeernotemarginnote = '<span id="pn_'.$loc.'" class="peernote peerdarr" style="display:'.(($viewpeernotes==1)?'block':'none').';cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);">'.$pvpeernotemarginnote.'</span>';
      }else $pvpeernotemarginnote = '<span id="pn_'.$loc.'" class="peernote peerdarr" style="display:none;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);"></span>';
      $pvpeernotenotedot = '<img id="peerimg_'.$loc.'" data-havepeernote="'.$havepeernote.'" src="/i/skinnyyeldot.png" style="display:'.(($havepeernote==1 && $viewpeernotes==1)?'inline':'none').';width:.4em;" alt="You have a reviewer note for this verse." />';
    }

    //
    // ednote dot and margin note
    //
    if($linkcommentary==1 && $myrevid>0 && $editorcomments==1){
      if($row['ednotemarginnote'] != ''){
        $pvednotemarginnote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $row['ednotemarginnote']);
        $pvednotemarginnote = '<span id="bn_'.$loc.'" class="editnote enotedarr" style="display:'.(($viewedcomments==1)?'block':'none').';cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);">'.$pvednotemarginnote.'</span>';
      }else $pvednotemarginnote = '<span id="bn_'.$loc.'" class="editnote enotedarr" style="display:none;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);"></span>';
      $pvednotenotedot = '<img id="edimg_'.$loc.'" data-haveednote="'.$haveednotenote.'" src="/i/skinnyreddot.png" style="display:'.(($haveednotenote==1 && $viewedcomments==1)?'inline':'none').';width:.4em;" alt="You have a note for this verse." />';
    }

    // handle heading
    // this should be redone. Maybe
    if($outlinepublished==1 && $row['headcount'] > 0){
      $sql = 'select heading, level, reference from outline where testament = '.$test.' and book = '.$book.' and chapter = '.$chapter.' and verse = '.$versnum.' and link=1 order by level ';
      $heds = dbquery($sql);
      $hdcnt=0;$head='';
      while($rrow = mysqli_fetch_array($heds)){
        //if($hdcnt>0) $head.= '[br]&nbsp;&nbsp;&nbsp;';
        if($hdcnt>0) $head.= '[br]';
        $head.= $rrow[0];
        if($rrow['level']==0) $head.= ' ('.$rrow['reference'].')';
        $hdcnt++;
      }
      $head = str_replace('[br]', '<br />', $head);
      //$head = str_replace('[separator]', '<hr class="divider" style="text-align:left;" />', $head);
      $mvhcnt = substr_count($verse, '[mvh]');
      $arhead = explode('~~', $head);
      $idx = 0;
      if($mvhcnt < sizeof($arhead)){ // a heading goes before the verse
        $pvhead = ((($versnum>1 && ($style==1 || $prevstyle==1)) && !($prevstyle>1))?'</p>':'');  // do we need to close a paragraph?
        $pvhead.='<span class="heading'.(($versnum==1)?'first':'').'"><a id="head_c'.$chapter.'_v'.$versnum.'"></a>'.fixverse($arhead[0]).'</span>';
        $pvhead = processfootnotes($arfn, $pvhead, $footnotes, $fncnt, $chapter, $versnum);
        if($versnum!=1 && ($style<2 || (strpos($verse, '[hpbegin]')!==false))) $pvpara.= '<p style="margin-top:0">';
        //$havepara = 0; // don't need a new paragraph, the heading class has a top space
        $havepara = (($versnum==1)?0:1);
        $idx = 1;
      }
      while($idx < sizeof($arhead)){
        $pos = strpos($verse, '[mvh]');
        if($pos){
          $replace = '[mvhmark]'.(($blnlinkverse)?'</a>':'').'</p><a id="head_c'.$chapter.'_v'.$versnum.'"></a><span class="heading">'.fixverse($arhead[$idx]).
                     '</span>'.(($style==1)?'<p style="margin-top:0;">'.$hlspan.(($blnlinkverse)?$comlink:''):'<p class="hp">'.(($blnlinkverse)?$comlink:'').
                     '<span class="hpv">&nbsp;</span>'.$hlspan);
          $verse = substr_replace($verse, $replace, $pos, 5);
        }
        $idx++;
      }
    }

    // handle superscript
    if($row['superscript'] != 'noscript'){
      $sscript = str_replace('[br]', '<br />', $row['superscript']);
      // try to handle multiple headings, mainly for Song of Songs 5
      $mvscnt = substr_count($verse, '[mvs]');
      $arhead = explode('~~', $sscript);
      $idx = 0;
      if($mvscnt < sizeof($arhead)){
        $pvhead.= ((($versnum>1 && ($style==1 || $prevstyle==1)) && !($prevstyle>1))?'</p>':'');  // do we need to close a paragraph?
        $pvhead.='<span class="microheading'.(($versnum==1)?'first':'').'">'.fixverse($arhead[0]).'</span>';
        $pvhead = processfootnotes($arfn, $pvhead, $footnotes, $fncnt, $chapter, $versnum);
        if($versnum!=1 && ($style<2 || (strpos($verse, '[hpbegin]')!==false))) $pvpara.= '<p style="margin-top:0">';
        $havepara = 0; // don't need a new paragraph, the heading class has a top space
        $idx = 1;
      }
      while($idx < sizeof($arhead)){
        $pos = strpos($verse, '[mvs]');
        if($pos){
          $replace = '[mvsmark]'.(($blnlinkverse)?'</a>':'').'</p><span class="microheading">'.fixverse($arhead[$idx]).
                     '</span>'.(($style==1)?'<p style="margin-top:0;">'.$hlspan.(($blnlinkverse)?$comlink:''):'<p class="hp">'.(($blnlinkverse)?$comlink:'').
                     '<span class="hpv">&nbsp;</span>'.$hlspan);
          $verse = substr_replace($verse, $replace, $pos, 5);
        }
        $idx++;
      }
    }

    if(strpos($verse, '[separator]')!==false){
      if(strpos($verse, '[separator]')===0){
        //if($pvhead=='') $pvhead = '<hr class="divider" style="text-align:left;" />';
        $pvhead = '<hr class="divider" style="text-align:left;" />'.$pvhead;
        $verse = str_replace('[separator]', '', $verse);
      }else{
        $verse = str_replace('[separator]', '<hr class="divider" style="text-align:left;" />', $verse);
      }
    }

    $notintext = 0;
    if(substr($verse,0,1) == '~')  {$verse = substr($verse,1); $notintext = 1;}
    // get and format versnum
    if($versnum==1 && $versebreak!=1 && $useoefirst==1 && $style < 6){
      // this might need work

      //$notintext = 0;
      if(substr($verse,0,2) == '[['){$verse = substr($verse,2);}
      if(substr($verse,0,1) == '[') {$verse = substr($verse,1);}
      if(substr($verse,0,2) == '"\'')     $verse = substr($verse,2); // for OT verses
      if(substr($verse,0,7) == '&ldquo;') $verse = substr($verse,7);
      if(substr($verse,0,2) == '[[')      $verse = substr($verse,2,1).'[['.substr($verse,3);
      if(substr($verse,0,1) == '"')       $verse = substr($verse,1);
      if(substr($verse,0,4) == '<em>')    $verse = substr($verse,4,1).'<em>'.substr($verse,5);
      $tmp = strtolower(substr($verse,0,1));
      $verse = substr($verse,1);
      if(left($verse, 1)==' ' && $tmp!= 'i') $verse = '&nbsp;'.substr($verse,1);
      $verse = (($notintext==1)?'~':'').$pvmyrevnotedot.$pvpeernotenotedot.$pvednotenotedot.$verse;
      $pvvimg = '<img src="/i/letters/'.$tmp.$colors[0].'.png" alt="'.$tmp.'" style="float:left;margin-top:-1px;margin-left:4px;height:2.1em;border:0;" />'.$pvvnav;
    }else{
      if($versebreak<2) // not in reading mode
        $pvvnum = '<sup class="versenum'.(($linkcommentary==1 && $row['haverevcom']==1)?'comm':'').'">'.$versnum.$pvvnav.'</sup>'.$pvmyrevnotedot.$pvpeernotenotedot.$pvednotenotedot;
      else
        $pvvnum=$pvvnav.$pvmyrevnotedot.$pvpeernotenotedot.$pvednotenotedot;
    }
    if($userid>0 && $canedit==1) $pvedit=editlink('elnk'.$nv,$showedit,$mitm,1,$test,$book,$chapter,$versnum);

    switch($style){
    case 1: // prose
      if($versnum == 1) $pvpara.= '<p style="margin-top:0;'.(($useoefirst)?'text-indent:0;':'').'">';
      if($pvhead=='') $pvpara.= (($havepara)?'<p>':'');
      if(left($verse, 4) == '[bq]'){ // to make versnum appear at the beginning of the verse
        $versbr = '<blockquote><p>';
        $verse = substr($verse, 4, 2000);
      }
      $verse = str_replace('[pg]', (($blnlinkverse)?'</a></p><p>'.$comlink:'</p><p>'), $verse);
      $verse = str_replace('[bq]', (($blnlinkverse)?'</a></p>':'').'<blockquote><p>'.(($blnlinkverse)?$comlink:''), $verse);
      if(strpos($verse, '[/bq]')!==false) $verse = str_replace('[/bq]',printcommlink($blnshowcomlink,$pvprinted,$bhabbr,$chapter,$versnum,$verse,$comimg,$test,$book,$myrevhighlightcolor,$haverevcom).'</p></blockquote>', $verse);
      $verse = $hlspan.$verse.'</span>';
      $pvvnum= $versbr.$pvvnum.$pvedit;
      $inlistbegin=0;
      break;
    case 2:  // poetry
    case 3:  // poetry_NB
    case 4:  // BR_poetry
    case 5:  // BR_poetry_NB
      $versbr2 = '';
      if($prevstyle>1 && (left($verse, 4) == '[br]' || $style == 4 || $style == 5)){
        $versbr = '<br />';
        $versbr2 = '<p>';
        if(left($verse, 4) == '[br]') $verse = substr($verse, 4, 2000);
      }
      $lines = 0;
      $idx = strpos($verse, '[hpbegin]');
      if($idx !== false){
        if($havepara) $versbr2='<p>';
        if($versnum==1) $versbr2='<p style="margin-top:0;'.(($useoefirst)?'text-indent:0;':'').'">';
        $beginstub = $versbr2.$pvvnum.$pvedit;
        if($blnlinkverse) $beginstub.= $comlink;
        $beginstub.= $hlspan;
        $beginstub .= left($verse, $idx);
        $beginstub.= '</span>';
        if($blnlinkverse) $beginstub.= '</a>';
        $beginstub.= ((strpos($beginstub, '[mvhmark]')<0 ||strpos($beginstub, '[mvsmark]')<0)?'':'</p>');
        $beginstub = str_replace('[pg]', "</p><p>", $beginstub);
        $havevnum = 1;
        $verse = substr($verse, $idx+9, 2000);
        $lines = 2;
      }
      $idx = strpos($verse, '[hpend]');
      if($idx !== false){
        $endstub = '<p>';
        if($blnlinkverse) $endstub.= $comlink;
        $endstub.= $hlspan;
        $endstub .= substr($verse, $idx+7, 2000);
        $endstub.= '</span>';
        if($blnlinkverse) $endstub.= '</a>';
        $verse = left($verse, $idx, 2000);
        $prevstyleflag=1; // to populate $prevstyle
      }
      $ar = explode('[hp]', $verse);
      $verse = '';
      for($ni=0, $siz=sizeof($ar);$ni<$siz;$ni++){
        if(trim($ar[$ni]) != ''){
          $idx = (($versnum==1 && $useoefirst && (($ni + $lines) < 2))?'1':'');
          $verse.= '<p class="hp'.$idx.'"><span class="hpv'.$idx.'">&nbsp;'.(($ni==0&&$havevnum==0)?$versbr.$pvvnum.'&nbsp;':'').'</span>';
          $verse.= (($ni==0&&$havevnum==0)?$pvedit:'');
          if($blnlinkverse) $verse.= $comlink;
          $verse.= $hlspan;
          $verse.= str_replace('[br]', '<br />&nbsp;', trim($ar[$ni]));
          $verse.= '</span>';
          if($blnlinkverse) $verse.= '</a>';
          if($ni==($siz-1) && $endstub=='' && $showcommlinks==1) $verse.=printcommlink($blnshowcomlink,$pvprinted,$bhabbr,$chapter,$versnum,$verse,$comimg,$test,$book,$myrevhighlightcolor,$haverevcom);
          $verse.= '</p>';
        }
      }
      $verse = str_replace('[pg]', '<br /><br />', $verse); // this is primarily for Matt 1:6
      $verse = $beginstub.$verse.$endstub.((($style==2 || $style == 4) && $endstub == '')?'<span class="vspc">&nbsp;</span>':'');
      $pvvnum = '';$blnlinkverse = 0;
      break;
    case 6: // list
    case 7: // list_END
    case 8: // BR_list
    case 9: // BR_list_END
      $idx = strpos($verse, '[listbegin]');
      if($idx !== false){
        $beginstub = (($prevstyle>1 && $prevstyle<6)?'</table>':'');
        if($versnum==1) $beginstub .= '<p style="margin-top:0;">'.$pvvnum.$pvedit;
        else            $beginstub .= (($havepara)?'<p style="margin-top:16px">':'').$pvvnum.$pvedit;
        if($blnlinkverse) $beginstub.= $comlink;
        $beginstub.= $hlspan;
        $beginstub .= left($verse, $idx);
        $beginstub .= '</span>';
        if($blnlinkverse) $beginstub.= '</a>';
        $beginstub = str_replace('[pg]', '</p><p>', $beginstub);
        $beginstub.= (($prevstyle<2)?'</p>':'').'<p class="lst"><span class="lstv">&nbsp;</span><span style="display:table-cell;">';
        $havevnum = 1;
        $verse = substr($verse, $idx+11, 2000);
        $inlistbegin=1;
      }
      $idx = strpos($verse, '[listend]');
      if($idx !== false){
        $endstub = '</span></p><p>';
        if($blnlinkverse) $endstub.= $comlink;
        $endstub.= $hlspan;
        $endstub .= substr($verse, $idx+9, 2000);
        $endstub .= '</span>';
        if($blnlinkverse) $endstub.= '</a>';
        $verse = left($verse, $idx, 2000);
        if($inlistbegin==1){
          $beginstub='</span></p>';
          $inlistbegin=0;
        }
        $prevstyleflag=1; // to populate $prevstyle
      }
      if($prevstyle==1 && $beginstub=='') $beginstub='</p>';
      $tmpv = '';
      if($style==8 || $style==9 || ($havevnum==0 && $versnum==1)){ // br
        $tmpv.= (($havepara && $prevstyle!=1)?'<br />'.(($prevstyle>5)?'':''):'').'<p class="lst"><span class="lstv">'.$pvvnum.$pvedit.'</span><span style="display:table-cell;">';
        $havevnum = 1;
      }
      if($havevnum==0) $tmpv.= $pvvnum.$pvedit;
      if($blnlinkverse) $tmpv.= $comlink;
      $tmpv.= $hlspan;
      $tmpv.= $verse;
      $tmpv = str_replace('[lb]', '<br />', $tmpv);
      $verse = $tmpv;
      $verse.= '</span>';
      if($blnlinkverse) $verse.= '</a>';
      $verse = $beginstub.$verse;
      if($endstub=='' && $showcommlinks==1) $verse.= printcommlink($blnshowcomlink,$pvprinted,$bhabbr,$chapter,$versnum,$verse,$comimg,$test,$book,$myrevhighlightcolor,$haverevcom);
      $verse .= ((($style==7 || $style==9))?'</span></p>':'').$endstub;
      if($endstub!='' && $showcommlinks==1) $verse.= printcommlink($blnshowcomlink,$pvprinted,$bhabbr,$chapter,$versnum,$verse,$comimg,$test,$book,$myrevhighlightcolor,$haverevcom);
      $pvvnum = '';$blnlinkverse = 0;
      break;
    }

    $verse = processfootnotes($arfn, $verse, $footnotes, $fncnt, $chapter, $versnum);
    if($notintext) $verse = '~'.$verse;
    if($blnlinkverse) $pvvers = $comlink.fixverse($verse).'</a>';
    else $pvvers = fixverse($verse);
    if($showcommlinks==1) $pvvers.=printcommlink($blnshowcomlink,$pvprinted,$bhabbr,$chapter,$versnum,$verse,$comimg,$test,$book,$myrevhighlightcolor,$haverevcom);
    $pvpost = (($versebreak==1 && $style==1 && right($verse, 13) != '</blockquote>')?'<br />':'').(($style==1 && $versebreak==1 && right($verse, 13)!='</blockquote>')?'<span class="vspc">&nbsp;</span>':'').crlf;
    if($ismdash==1) $pvpost='';

    $htm.=$pvhead.$pvmyrevmarginnote.$pvpeernotemarginnote.$pvednotemarginnote.$pvvimg.$pvpara.$pvvnum.$pvvers.$pvpost;
    $nv++;
    $prevstyle = (($prevstyleflag==0)?$style:$prevstyleflag);
    $needclosingptag = 0;
    if($style==1 && right($verse, 13) != '</blockquote>') $needclosingptag = 1;
  }
  if($needclosingptag==1) $htm.='</p>';
  $htm.=crlf.'</div>'.crlf;
  $htm = str_replace('<p style="margin-top:0"></p>', '', $htm);  // some cleanup
  $htm = tidify($htm);  // sigh...  I can't seem to get it perfect, have to tidify it
  $htm = str_replace('&nbsp; ', '&nbsp;', (string) $htm);
  print($htm);
  if($viewcols>1) print('<div style="margin:0 auto;text-align:center;"><a id="navchapbot'.$chapter.'" onclick="return scrolltopos(this.id, \'chap'.$chapter.'\');" class="toplink">top of '.(($book==19)?'Psalm':'chapter').'</a></div>');
  displayfootnotes($fncnt, $arfn, 0, $chapter);
}

//
// trying it out..
//
function tidify($htm){
  //* Tidy
  $config = array(
                  'indent'           => false,
                  'output-xhtml'     => true,
                  'wrap'             => 99999,
                  'preserve-entities'=> true,
                  'show-body-only'   => true
                 );
  $tidy = new tidy;
  $tidy->parseString($htm, $config, 'utf8');
  $tidy->cleanRepair();
  // why does tidy insert a space after a span?
  $htm = str_replace('/></a></span> ', '/></a></span>', (string) $tidy);
  return $htm;
}

function printcommlink($hvcomx,&$pvprinted,$bk,$ch,$v,$vrse,$comimg, $tst, $bok, $hlit, $hvrevcom){
  // this function returns the comlink at the end of a verse
  // if the user is mobile and is showing the commlinks (a preference)
  // and it has commentary OR the user is a myrev user
  global $ismobile, $showcommlinks, $myrevid, $myrevclick;
  if($ismobile==0 || $showcommlinks==0 || $hvcomx==0 || $pvprinted==1) return '';
  if(strpos($vrse, '[/bq]')!==false && strrpos($vrse,'[/bq]')<(strlen($vrse)-5)) return '';
  if($myrevid>0 && $myrevclick==1){
    $jscomlink = '/'.$bk.'/'.$ch.'/'.$v.'/1';
    $lnk = '<a onclick="showhilightdiv(\''.$myrevid.'|'.$tst.'|'.$bok.'|'.$ch.'|'.$v.'\', \''.$jscomlink.'\','.$hvrevcom.');">'.$comimg.'</a>&nbsp;';
  }else
    $lnk = '<a href="/'.$bk.'/'.$ch.'/'.$v.'" title="click to view commentary" rel="nofollow">'.$comimg.'</a>&nbsp;';
  $pvprinted=1;
  return $lnk;
}


?>

